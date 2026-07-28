<?php
/**
 * Allow SVG uploads with sanitization and media library support.
 *
 * Inspired by https://github.com/10up/safe-svg
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Media;

use Eighteen73\Orbit\Singleton;
use Throwable;

/**
 * Enable SVG uploads, sanitize them, and fix media library usage.
 */
class Svg {

	use Singleton;

	/**
	 * The sanitizer.
	 *
	 * @var SvgSanitizer
	 */
	protected SvgSanitizer $sanitizer;

	/**
	 * Setup module.
	 *
	 * @return void
	 */
	public function setup(): void {
		if ( ! apply_filters( 'orbit_enable_svg_uploads', true ) ) {
			return;
		}

		$this->sanitizer = new SvgSanitizer();

		// Allow SVG uploads from specific contexts Orbit can sanitize.
		add_action( 'load-upload.php', [ $this, 'allow_svg_from_upload' ] );
		add_action( 'load-post-new.php', [ $this, 'allow_svg_from_upload' ] );
		add_action( 'load-post.php', [ $this, 'allow_svg_from_upload' ] );
		add_action( 'load-site-editor.php', [ $this, 'allow_svg_from_upload' ] );
		add_action( 'load-media_page_enable-media-replace/enable-media-replace', [ $this, 'allow_svg_from_upload' ] );

		// Hook into media JS API load points so uploads via the media modal allow SVGs.
		add_filter(
			'media_upload_tabs',
			function ( $tabs ) {
				$this->allow_svg_from_upload();
				return $tabs;
			}
		);

		add_filter( 'wp_handle_sideload_prefilter', [ $this, 'check_for_svg' ] );
		add_filter( 'wp_handle_upload_prefilter', [ $this, 'check_for_svg' ] );
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'fix_admin_preview' ], 10, 3 );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'one_pixel_fix' ], 10, 4 );
		add_filter( 'admin_post_thumbnail_html', [ $this, 'featured_image_fix' ], 10, 3 );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_custom_admin_style' ] );
		add_filter( 'get_image_tag', [ $this, 'get_image_tag_override' ], 10, 6 );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'skip_svg_regeneration' ], 10, 2 );
		add_filter( 'wp_get_attachment_metadata', [ $this, 'metadata_error_fix' ], 10, 2 );
		add_filter( 'wp_calculate_image_srcset_meta', [ $this, 'disable_srcset' ], 10, 4 );
	}

	/**
	 * Allow SVG uploads from known admin upload screens.
	 *
	 * @return void
	 */
	public function allow_svg_from_upload(): void {
		add_filter( 'upload_mimes', [ $this, 'allow_svg' ] );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'fix_mime_type_svg' ], 75, 4 );
	}

	/**
	 * Allow SVG MIME types for users who can upload files.
	 *
	 * @param array $mimes Mime types keyed by file extension regex.
	 * @return array
	 */
	public function allow_svg( array $mimes ): array {
		if ( current_user_can( 'upload_files' ) ) {
			$mimes['svg'] = 'image/svg+xml';

			if ( $this->sanitizer->svgz_uploads_enabled() ) {
				$mimes['svgz'] = 'image/svg+xml';
			}
		}

		return $mimes;
	}

	/**
	 * Correct SVG filetype detection.
	 *
	 * @param array|null    $data     Values for the extension, mime type, and corrected filename.
	 * @param string|null   $file     Full path to the file.
	 * @param string|null   $filename The name of the file.
	 * @param string[]|null $mimes    Array of mime types keyed by their file extension regex.
	 * @return array|null
	 */
	public function fix_mime_type_svg( $data = null, $file = null, $filename = null, $mimes = null ) {
		$ext = isset( $data['ext'] ) ? $data['ext'] : '';
		if ( strlen( $ext ) < 1 && is_string( $filename ) ) {
			$exploded = explode( '.', $filename );
			$ext      = strtolower( end( $exploded ) );
		}

		if ( 'svg' === $ext ) {
			$data['type'] = 'image/svg+xml';
			$data['ext']  = 'svg';
		} elseif ( 'svgz' === $ext && $this->sanitizer->svgz_uploads_enabled() ) {
			$data['type'] = 'image/svg+xml';
			$data['ext']  = 'svgz';
		}

		return $data;
	}

	/**
	 * Check if the file is an SVG and sanitize it.
	 *
	 * Temporary MIME filters are kept only for successful SVG uploads so WordPress
	 * can finish filetype checks, then removed on `pre_move_uploaded_file`.
	 * On rejection or non-SVG uploads they are cleaned up immediately so they do
	 * not leak for the rest of a multi-file request.
	 *
	 * @param array $file An array of data for a single file.
	 * @return array
	 */
	public function check_for_svg( array $file ): array {
		if ( ! isset( $file['tmp_name'] ) ) {
			return $file;
		}

		$this->add_temporary_mime_filters();

		$keep_mime_filters = false;

		try {
			$file_name   = isset( $file['name'] ) ? $file['name'] : '';
			$wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file_name );
			$type        = ! empty( $wp_filetype['type'] ) ? $wp_filetype['type'] : '';

			if ( 'image/svg+xml' !== $type ) {
				return $file;
			}

			if ( ! current_user_can( 'upload_files' ) ) {
				$file['error'] = __( 'Sorry, you are not allowed to upload SVG files.', 'orbit' );
				return $file;
			}

			if ( ! $this->sanitize( $file['tmp_name'] ) ) {
				$file['error'] = __( "Sorry, this file couldn't be sanitized so for security reasons wasn't uploaded", 'orbit' );
				return $file;
			}

			add_filter( 'pre_move_uploaded_file', [ $this, 'pre_move_uploaded_file' ] );
			$keep_mime_filters = true;

			return $file;
		} catch ( Throwable ) {
			$file['error'] = __( "Sorry, this file couldn't be sanitized so for security reasons wasn't uploaded", 'orbit' );
			return $file;
		} finally {
			if ( ! $keep_mime_filters ) {
				$this->remove_temporary_mime_filters();
			}
		}
	}

	/**
	 * Add temporary MIME filters for the current upload attempt.
	 *
	 * @return void
	 */
	protected function add_temporary_mime_filters(): void {
		add_filter( 'upload_mimes', [ $this, 'allow_svg' ] );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'fix_mime_type_svg' ], 75, 4 );
	}

	/**
	 * Remove temporary MIME filters added for the current upload attempt.
	 *
	 * @return void
	 */
	protected function remove_temporary_mime_filters(): void {
		remove_filter( 'wp_check_filetype_and_ext', [ $this, 'fix_mime_type_svg' ], 75 );
		remove_filter( 'upload_mimes', [ $this, 'allow_svg' ] );
	}

	/**
	 * Remove temporary MIME filters after the file has been processed.
	 *
	 * @param string|null $move_new_file Whether to short-circuit moving the file.
	 * @return string|null
	 */
	public function pre_move_uploaded_file( $move_new_file ) {
		$this->remove_temporary_mime_filters();
		remove_filter( 'pre_move_uploaded_file', [ $this, 'pre_move_uploaded_file' ] );

		return $move_new_file;
	}

	/**
	 * Sanitize the SVG file contents.
	 *
	 * @param string $file Temp file path.
	 * @return bool
	 */
	protected function sanitize( string $file ): bool {
		return $this->sanitizer->sanitize_file( $file );
	}

	/**
	 * Add sizes data for SVG attachments in the media modal.
	 *
	 * @param array      $response   Array of prepared attachment data.
	 * @param int|object $attachment Attachment ID or object.
	 * @param array      $meta       Array of attachment meta data.
	 * @return array
	 */
	public function fix_admin_preview( array $response, $attachment, $meta ): array {
		if ( empty( $response['mime'] ) || 'image/svg+xml' !== $response['mime'] ) {
			return $response;
		}

		$dimensions = $this->svg_dimensions( $attachment->ID );

		if ( $dimensions ) {
			$response = array_merge( $response, $dimensions );
		}

		$possible_sizes = apply_filters(
			'image_size_names_choose',
			[
				'full'      => __( 'Full Size' ),
				'thumbnail' => __( 'Thumbnail' ),
				'medium'    => __( 'Medium' ),
				'large'     => __( 'Large' ),
			]
		);

		$sizes = [];

		foreach ( $possible_sizes as $size => $label ) {
			$default_height = 2000;
			$default_width  = 2000;

			if ( 'full' === $size && $dimensions ) {
				$default_height = $dimensions['height'];
				$default_width  = $dimensions['width'];
			}

			$sizes[ $size ] = [
				'height'      => get_option( "{$size}_size_w", $default_height ),
				'width'       => get_option( "{$size}_size_h", $default_width ),
				'url'         => $response['url'],
				'orientation' => 'portrait',
			];
		}

		$response['sizes'] = $sizes;
		$response['icon']  = $response['url'];

		return $response;
	}

	/**
	 * Fix zero/one-pixel dimensions for SVG image sources.
	 *
	 * @param array|false  $image         Image data or false.
	 * @param int          $attachment_id Attachment ID.
	 * @param string|array $size          Image size.
	 * @param bool         $icon          Whether the image should be treated as an icon.
	 * @return array|false
	 */
	public function one_pixel_fix( $image, int $attachment_id, $size, bool $icon ) {
		if ( ! is_array( $image ) || get_post_mime_type( $attachment_id ) !== 'image/svg+xml' ) {
			return $image;
		}

		$dimensions = $this->svg_dimensions( $attachment_id );

		if ( $dimensions ) {
			$image[1] = $dimensions['width'];
			$image[2] = $dimensions['height'];
		} else {
			$image[1] = 100;
			$image[2] = 100;
		}

		return $image;
	}

	/**
	 * Wrap featured SVG images so admin CSS can size them.
	 *
	 * @param string   $content      Admin post thumbnail HTML markup.
	 * @param int      $post_id      Post ID.
	 * @param int|null $thumbnail_id Thumbnail attachment ID.
	 * @return string
	 */
	public function featured_image_fix( string $content, int $post_id, $thumbnail_id = null ): string {
		if ( 'image/svg+xml' === get_post_mime_type( $thumbnail_id ) ) {
			$content = sprintf( '<span class="svg">%s</span>', $content );
		}

		return $content;
	}

	/**
	 * Load admin CSS for SVG featured image display.
	 *
	 * @return void
	 */
	public function load_custom_admin_style(): void {
		wp_enqueue_style(
			'orbit-svg',
			ORBIT_URL . 'css/svg.css',
			[],
			null
		);
	}

	/**
	 * Override default 1x1 dimensions on SVG image tags.
	 *
	 * @param string       $html  HTML content for the image.
	 * @param int          $id    Attachment ID.
	 * @param string       $alt   Alternate text.
	 * @param string       $title Attachment title.
	 * @param string       $align Part of the class name for aligning the image.
	 * @param string|array $size  Size of image.
	 * @return string
	 */
	public function get_image_tag_override( string $html, int $id, string $alt, string $title, string $align, $size ): string {
		if ( 'image/svg+xml' !== get_post_mime_type( $id ) ) {
			return $html;
		}

		if ( is_array( $size ) ) {
			$width  = $size[0];
			$height = $size[1];
		} elseif ( 'full' === $size ) {
			$dimensions = $this->svg_dimensions( $id );
			$width      = $dimensions ? $dimensions['width'] : false;
			$height     = $dimensions ? $dimensions['height'] : false;
		} else {
			$width  = get_option( "{$size}_size_w", false );
			$height = get_option( "{$size}_size_h", false );
		}

		if ( $height && $width ) {
			$html = str_replace( 'width="1" ', sprintf( 'width="%s" ', $width ), $html );
			$html = str_replace( 'height="1" ', sprintf( 'height="%s" ', $height ), $html );
		} else {
			$html = str_replace( 'width="1" ', '', $html );
			$html = str_replace( 'height="1" ', '', $html );
		}

		return str_replace( '/>', ' role="img" />', $html );
	}

	/**
	 * Skip regenerating raster sizes for SVGs; store vector dimensions instead.
	 *
	 * @param array $metadata      Attachment meta data.
	 * @param int   $attachment_id Attachment ID.
	 * @return array
	 */
	public function skip_svg_regeneration( array $metadata, int $attachment_id ): array {
		if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
			return $metadata;
		}

		$additional_image_sizes = wp_get_additional_image_sizes();
		$svg_path               = get_attached_file( $attachment_id );
		$upload_dir             = wp_upload_dir();
		$relative_path          = str_replace( trailingslashit( $upload_dir['basedir'] ), '', $svg_path );
		$filename               = basename( $svg_path );
		$dimensions             = $this->svg_dimensions( $attachment_id );

		if ( ! $dimensions ) {
			return $metadata;
		}

		$metadata = [
			'width'  => (int) $dimensions['width'],
			'height' => (int) $dimensions['height'],
			'file'   => $relative_path,
		];

		$sizes = [];
		foreach ( get_intermediate_image_sizes() as $s ) {
			$sizes[ $s ] = [
				'width'     => '',
				'height'    => '',
				'crop'      => false,
				'file'      => $filename,
				'mime-type' => 'image/svg+xml',
			];

			if ( isset( $additional_image_sizes[ $s ]['width'] ) ) {
				$sizes[ $s ]['width'] = (int) $additional_image_sizes[ $s ]['width'];
			} else {
				$sizes[ $s ]['width'] = get_option( "{$s}_size_w" );
			}

			if ( isset( $additional_image_sizes[ $s ]['height'] ) ) {
				$sizes[ $s ]['height'] = (int) $additional_image_sizes[ $s ]['height'];
			} else {
				$sizes[ $s ]['height'] = get_option( "{$s}_size_h" );
			}

			if ( isset( $additional_image_sizes[ $s ]['crop'] ) ) {
				$sizes[ $s ]['crop'] = (int) $additional_image_sizes[ $s ]['crop'];
			} else {
				$sizes[ $s ]['crop'] = get_option( "{$s}_crop" );
			}
		}

		$metadata['sizes'] = $sizes;

		return $metadata;
	}

	/**
	 * Regenerate metadata when WordPress returns a WP_Error.
	 *
	 * @param array|false|\WP_Error $data    Attachment meta data.
	 * @param int                   $post_id Attachment ID.
	 * @return array|false|\WP_Error
	 */
	public function metadata_error_fix( $data, int $post_id ) {
		if ( is_wp_error( $data ) ) {
			$data = wp_generate_attachment_metadata( $post_id, get_attached_file( $post_id ) );
			wp_update_attachment_metadata( $post_id, $data );
		}

		return $data;
	}

	/**
	 * Get SVG dimensions from width/height or viewBox.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array|false
	 */
	protected function svg_dimensions( int $attachment_id ) {
		$short_circuit = apply_filters( 'orbit_svg_pre_dimensions', false, $attachment_id );

		if ( false !== $short_circuit ) {
			return $short_circuit;
		}

		if ( ! function_exists( 'simplexml_load_file' ) ) {
			return false;
		}

		$svg      = get_attached_file( $attachment_id );
		$metadata = wp_get_attachment_metadata( $attachment_id );
		$width    = 0;
		$height   = 0;

		if ( $svg && ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
			$width  = floatval( $metadata['width'] );
			$height = floatval( $metadata['height'] );
		} elseif ( $svg ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$xml = @simplexml_load_file( $svg );

			if ( ! $xml ) {
				return false;
			}

			$attributes = $xml->attributes();

			$viewbox_width  = null;
			$viewbox_height = null;
			$attr_width     = null;
			$attr_height    = null;

			if ( isset( $attributes->viewBox ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$sizes = explode( ' ', (string) $attributes->viewBox ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( isset( $sizes[2], $sizes[3] ) ) {
					$viewbox_width  = floatval( $sizes[2] );
					$viewbox_height = floatval( $sizes[3] );
				}
			}

			if (
				isset( $attributes->width, $attributes->height )
				&& is_numeric( (float) $attributes->width )
				&& is_numeric( (float) $attributes->height )
				&& ! str_ends_with( (string) $attributes->width, '%' )
				&& ! str_ends_with( (string) $attributes->height, '%' )
			) {
				$attr_width  = floatval( $attributes->width );
				$attr_height = floatval( $attributes->height );
			}

			$use_width_height = (bool) apply_filters( 'orbit_svg_use_width_height_attributes', false, $svg, $attachment_id );

			if ( $use_width_height ) {
				if ( isset( $attr_width, $attr_height ) ) {
					$width  = $attr_width;
					$height = $attr_height;
				} elseif ( isset( $viewbox_width, $viewbox_height ) ) {
					$width  = $viewbox_width;
					$height = $viewbox_height;
				}
			} elseif ( isset( $viewbox_width, $viewbox_height ) ) {
				$width  = $viewbox_width;
				$height = $viewbox_height;
			} elseif ( isset( $attr_width, $attr_height ) ) {
				$width  = $attr_width;
				$height = $attr_height;
			}

			if ( ! $width && ! $height ) {
				return false;
			}
		}

		$dimensions = [
			'width'       => $width,
			'height'      => $height,
			'orientation' => ( $width > $height ) ? 'landscape' : 'portrait',
		];

		return apply_filters( 'orbit_svg_dimensions', $dimensions, $svg, $attachment_id );
	}

	/**
	 * Disable srcset generation for SVG images.
	 *
	 * @param array  $image_meta    The image meta data.
	 * @param int[]  $size_array    Requested width and height.
	 * @param string $image_src     The src of the image.
	 * @param int    $attachment_id The image attachment ID.
	 * @return array
	 */
	public function disable_srcset( $image_meta, $size_array, $image_src, $attachment_id ) {
		if ( $attachment_id && 'image/svg+xml' === get_post_mime_type( $attachment_id ) && is_array( $image_meta ) ) {
			$image_meta['sizes'] = [];
		}

		return $image_meta;
	}
}
