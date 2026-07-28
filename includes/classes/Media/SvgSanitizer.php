<?php
/**
 * Fail-closed SVG sanitization helpers.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Media;

use Eighteen73\Orbit\Dependencies\enshrined\svgSanitize\Sanitizer;
use DOMDocument;
use DOMElement;
use Throwable;

/**
 * Sanitize SVG markup and persist cleaned bytes atomically.
 */
class SvgSanitizer {

	/**
	 * Default maximum uncompressed size when decoding gzipped SVGs (10 MiB).
	 */
	public const DEFAULT_MAX_DECOMPRESSED_BYTES = 10485760;

	/**
	 * Default maximum decompression ratio (uncompressed / compressed).
	 */
	public const DEFAULT_MAX_COMPRESSION_RATIO = 100;

	/**
	 * The underlying library sanitizer.
	 *
	 * @var Sanitizer
	 */
	protected Sanitizer $sanitizer;

	/**
	 * Constructor.
	 *
	 * @param Sanitizer|null $sanitizer Optional sanitizer instance.
	 */
	public function __construct( ?Sanitizer $sanitizer = null ) {
		$this->sanitizer = $sanitizer ?? new Sanitizer();
		$this->sanitizer->minify( true );
		$this->sanitizer->removeRemoteReferences( true );
	}

	/**
	 * Sanitize a file on disk. Returns true only when cleaned bytes were written.
	 *
	 * @param string $file Absolute path to the temporary upload.
	 * @return bool
	 */
	public function sanitize_file( string $file ): bool {
		try {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$dirty = file_get_contents( $file );

			if ( false === $dirty ) {
				return false;
			}

			$is_zipped = $this->is_gzipped( $dirty );

			if ( $is_zipped ) {
				if ( ! $this->svgz_uploads_enabled() ) {
					return false;
				}

				$dirty = $this->gunzip_bounded( $dirty );

				if ( false === $dirty ) {
					return false;
				}
			}

			$clean = $this->sanitize_markup( $dirty );

			if ( false === $clean ) {
				return false;
			}

			if ( $is_zipped ) {
				$clean = gzencode( $clean );

				if ( false === $clean ) {
					return false;
				}
			}

			return $this->write_atomically( $file, $clean );
		} catch ( Throwable ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			return false;
		}
	}

	/**
	 * Sanitize SVG markup string and enforce Orbit's strict href/CSS policy.
	 *
	 * @param string $dirty Dirty SVG markup.
	 * @return string|false Clean markup or false on failure.
	 */
	public function sanitize_markup( string $dirty ) {
		try {
			$this->sanitizer->setAllowedTags( new SvgTags() );
			$this->sanitizer->setAllowedAttrs( new SvgAttributes() );

			$clean = $this->sanitizer->sanitize( $dirty );

			if ( false === $clean || '' === $clean ) {
				return false;
			}

			return $this->enforce_strict_policy( $clean );
		} catch ( Throwable ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			return false;
		}
	}

	/**
	 * Whether gzipped SVG (.svgz) uploads are allowed.
	 *
	 * @return bool
	 */
	public function svgz_uploads_enabled(): bool {
		return (bool) apply_filters( 'orbit_enable_svgz_uploads', false );
	}

	/**
	 * Maximum allowed uncompressed size when decoding gzip.
	 *
	 * @return int
	 */
	public function max_decompressed_bytes(): int {
		return (int) apply_filters(
			'orbit_svg_max_decompressed_bytes',
			self::DEFAULT_MAX_DECOMPRESSED_BYTES
		);
	}

	/**
	 * Maximum allowed uncompressed/compressed ratio.
	 *
	 * @return float
	 */
	public function max_compression_ratio(): float {
		return (float) apply_filters(
			'orbit_svg_max_compression_ratio',
			self::DEFAULT_MAX_COMPRESSION_RATIO
		);
	}

	/**
	 * Check if the contents are gzipped.
	 *
	 * @param string $contents Content to check.
	 * @return bool
	 */
	public function is_gzipped( string $contents ): bool {
		// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
		return 0 === strpos( $contents, "\x1f" . "\x8b" . "\x08" );
	}

	/**
	 * Stream-decode gzip with hard size and ratio limits.
	 *
	 * @param string $compressed Gzipped bytes.
	 * @return string|false
	 */
	public function gunzip_bounded( string $compressed ) {
		$max_bytes = $this->max_decompressed_bytes();
		$max_ratio = $this->max_compression_ratio();
		$compressed_length = strlen( $compressed );

		if ( $compressed_length < 1 || $max_bytes < 1 ) {
			return false;
		}

		$context = inflate_init( ZLIB_ENCODING_GZIP );

		if ( false === $context ) {
			return false;
		}

		$inflated   = '';
		$offset     = 0;
		$chunk_size = 8192;

		while ( $offset < $compressed_length ) {
			$chunk  = substr( $compressed, $offset, $chunk_size );
			$offset += $chunk_size;

			$decoded = inflate_add( $context, $chunk );

			if ( false === $decoded ) {
				return false;
			}

			$inflated .= $decoded;

			if ( strlen( $inflated ) > $max_bytes ) {
				return false;
			}
		}

		$decoded = inflate_add( $context, '', ZLIB_FINISH );

		if ( false === $decoded ) {
			return false;
		}

		$inflated .= $decoded;

		if ( strlen( $inflated ) > $max_bytes ) {
			return false;
		}

		if ( $max_ratio > 0 && ( strlen( $inflated ) / $compressed_length ) > $max_ratio ) {
			return false;
		}

		return $inflated;
	}

	/**
	 * Whether an href value is safe under Orbit's default policy.
	 *
	 * Allows empty values, fragment identifiers, and known image data URIs.
	 * Rejects protocol-relative URLs, http(s), javascript:, and root-relative paths.
	 *
	 * @param string $value Attribute value.
	 * @return bool
	 */
	public function is_href_safe( string $value ): bool {
		$value = trim( $value );

		if ( '' === $value ) {
			return true;
		}

		if ( '#' === $value[0] ) {
			return true;
		}

		// Protocol-relative URLs are absolute in browsers.
		if ( str_starts_with( $value, '//' ) ) {
			return false;
		}

		$allow_root_relative = (bool) apply_filters( 'orbit_svg_allow_root_relative_hrefs', false );
		if ( '/' === $value[0] ) {
			return $allow_root_relative;
		}

		$allow_external = (bool) apply_filters( 'orbit_svg_allow_external_hrefs', false );
		if ( $allow_external && ( str_starts_with( $value, 'https://' ) || str_starts_with( $value, 'http://' ) ) ) {
			return true;
		}

		if ( preg_match( '#^data:image/(?:png|gif|jpe?g|webp|pjpeg)[;,]#i', $value ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Strip remote/network-capable references the library may leave behind.
	 *
	 * @param string $svg Sanitized SVG markup.
	 * @return string|false
	 */
	public function enforce_strict_policy( string $svg ) {
		$allow_style = (bool) apply_filters( 'orbit_svg_allow_style', false );

		$document = new DOMDocument();
		$previous = libxml_use_internal_errors( true );

		$loaded = $document->loadXML( $svg, LIBXML_NONET );

		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$document_element = $document->documentElement;

		if ( ! $loaded || ! $document_element ) {
			return false;
		}

		$elements = $document->getElementsByTagName( '*' );

		// Walk backwards so removals do not skip nodes.
		for ( $i = $elements->length - 1; $i >= 0; $i-- ) {
			$element = $elements->item( $i );

			if ( ! $element instanceof DOMElement ) {
				continue;
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$tag = strtolower( $element->tagName );

			if ( ! $allow_style && 'style' === $tag ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$parent_node = $element->parentNode;
				if ( $parent_node ) {
					$parent_node->removeChild( $element );
				}
				continue;
			}

			if ( ! $element->hasAttributes() ) {
				continue;
			}

			for ( $x = $element->attributes->length - 1; $x >= 0; $x-- ) {
				$attribute = $element->attributes->item( $x );
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$node_name = $attribute->nodeName;
				$name      = strtolower( $node_name );
				$value     = $attribute->value;

				if ( ! $allow_style && 'style' === $name ) {
					$element->removeAttribute( $node_name );
					continue;
				}

				if ( false !== stripos( $name, 'href' ) && ! $this->is_href_safe( $value ) ) {
					$element->removeAttribute( $node_name );
					continue;
				}

				if ( $this->attribute_has_remote_url( $value ) ) {
					$element->removeAttribute( $node_name );
				}
			}
		}

		$clean = $document->saveXML( $document_element );

		return false === $clean ? false : $clean;
	}

	/**
	 * Detect remote url(...) references in attribute values (including CSS-like values).
	 *
	 * @param string $value Attribute value.
	 * @return bool
	 */
	public function attribute_has_remote_url( string $value ): bool {
		if ( ! preg_match( '/url\s*\(\s*[\'"]?\s*([^\'")\s]+)\s*[\'"]?\s*\)/i', $value, $match ) ) {
			return false;
		}

		$target = trim( $match[1] );

		return (bool) preg_match( '#^(?:(?:https?|ftp|file):)?//#i', $target );
	}

	/**
	 * Write contents via a temporary sibling file, then replace the target.
	 *
	 * @param string $file     Destination path.
	 * @param string $contents Bytes to write.
	 * @return bool
	 */
	public function write_atomically( string $file, string $contents ): bool {
		$directory = dirname( $file );

		if ( ! is_dir( $directory ) || ! is_writable( $directory ) ) {
			return false;
		}

		$temp = tempnam( $directory, 'orbit-svg-' );

		if ( false === $temp ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$written = file_put_contents( $temp, $contents );

		if ( false === $written || $written !== strlen( $contents ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $temp );
			return false;
		}

		if ( @rename( $temp, $file ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return true;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy
		$copied = @copy( $temp, $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		@unlink( $temp );

		return (bool) $copied;
	}
}
