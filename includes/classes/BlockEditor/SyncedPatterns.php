<?php
/**
 * Sync theme patterns marked Synced into wp_block posts.
 *
 * Theme pattern files remain the source of truth. Orbit upserts matching
 * synced pattern posts so instances (core/block refs) receive layout updates
 * while pattern overrides keep per-instance content.
 *
 * Discovery is fingerprint-cached (file mtimes/sizes), similar to core's theme
 * pattern cache: a full upsert only runs when pattern files change.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\BlockEditor;

use Eighteen73\Orbit\Singleton;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_REST_Request;

/**
 * Synced theme patterns syncer.
 */
class SyncedPatterns {

	use Singleton;

	/**
	 * Post meta: full theme pattern slug (e.g. pulsar/example-synced-pattern).
	 */
	public const META_SLUG = '_orbit_theme_pattern_slug';

	/**
	 * Post meta: md5 hash of last synced file content.
	 */
	public const META_HASH = '_orbit_pattern_content_hash';

	/**
	 * Post meta: marks the wp_block as Orbit-managed from a theme file.
	 */
	public const META_MANAGED = '_orbit_managed_theme_pattern';

	/**
	 * Option key for fingerprint + registration cache.
	 */
	public const OPTION_CACHE = 'orbit_synced_theme_patterns_cache';

	/**
	 * Map of pattern slug => wp_block post ID for the current request.
	 *
	 * @var array<string, int>
	 */
	private array $synced_pattern_ids = [];

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public function setup(): void {
		if ( ! apply_filters( 'orbit_enable_synced_theme_patterns', true ) ) {
			return;
		}

		// After core registers theme patterns (init priority 10).
		add_action( 'init', [ $this, 'sync_theme_patterns' ], 20 );
		add_action( 'after_switch_theme', [ $this, 'clear_sync_cache' ] );
		add_filter( 'pre_render_block', [ $this, 'render_pattern_block_as_synced_ref' ], 10, 2 );
		add_filter( 'rest_request_after_callbacks', [ $this, 'block_managed_pattern_updates' ], 10, 3 );
		add_action( 'init', [ $this, 'register_meta' ], 5 );
	}

	/**
	 * Register post meta used for sync bookkeeping.
	 *
	 * @return void
	 */
	public function register_meta(): void {
		$args = [
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => static function (): bool {
				return current_user_can( 'edit_posts' );
			},
		];

		register_post_meta( 'wp_block', self::META_SLUG, $args );
		register_post_meta( 'wp_block', self::META_HASH, $args );
		register_post_meta( 'wp_block', self::META_MANAGED, $args );
	}

	/**
	 * Clear the fingerprint cache (forces a full sync on the next request).
	 *
	 * @return void
	 */
	public function clear_sync_cache(): void {
		delete_option( self::OPTION_CACHE );
	}

	/**
	 * Upsert synced theme patterns and re-register them as block refs.
	 *
	 * Uses a file fingerprint cache so unchanged themes skip file includes and DB writes.
	 *
	 * @return void
	 */
	public function sync_theme_patterns(): void {
		$fingerprint = $this->get_files_fingerprint();
		$cache       = get_option( self::OPTION_CACHE, null );

		if ( $this->is_cache_valid( $cache, $fingerprint ) ) {
			$this->register_from_cache( $cache );
			return;
		}

		$this->run_full_sync( $fingerprint );
	}

	/**
	 * Whether the stored cache matches the current theme file fingerprint.
	 *
	 * @param mixed  $cache       Option value.
	 * @param string $fingerprint Current fingerprint.
	 * @return bool
	 */
	private function is_cache_valid( $cache, string $fingerprint ): bool {
		if ( ! is_array( $cache ) ) {
			return false;
		}

		if ( ( $cache['fingerprint'] ?? '' ) !== $fingerprint ) {
			return false;
		}

		if ( ( $cache['stylesheet'] ?? '' ) !== get_stylesheet() ) {
			return false;
		}

		if ( ! isset( $cache['patterns'] ) || ! is_array( $cache['patterns'] ) ) {
			return false;
		}

		foreach ( $cache['patterns'] as $entry ) {
			$post_id = (int) ( $entry['post_id'] ?? 0 );
			if ( $post_id <= 0 || ! get_post( $post_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Re-register synced patterns from cache without reading theme files.
	 *
	 * @param array $cache Cached sync data.
	 * @return void
	 */
	private function register_from_cache( array $cache ): void {
		foreach ( $cache['patterns'] as $slug => $entry ) {
			$post_id = (int) $entry['post_id'];
			$this->synced_pattern_ids[ $slug ] = $post_id;
			$this->register_pattern_ref( $slug, $post_id, $entry );
		}
	}

	/**
	 * Full discover + upsert + cache write.
	 *
	 * @param string $fingerprint Current files fingerprint.
	 * @return void
	 */
	private function run_full_sync( string $fingerprint ): void {
		$patterns = $this->get_synced_patterns_from_themes();
		$cache    = [
			'fingerprint' => $fingerprint,
			'stylesheet'  => get_stylesheet(),
			'patterns'    => [],
		];

		foreach ( $patterns as $pattern ) {
			$post_id = $this->upsert_synced_pattern( $pattern );

			if ( ! $post_id ) {
				continue;
			}

			$this->synced_pattern_ids[ $pattern['slug'] ] = $post_id;

			$entry = $this->pattern_to_cache_entry( $pattern, $post_id );
			$cache['patterns'][ $pattern['slug'] ] = $entry;

			$this->register_pattern_ref( $pattern['slug'], $post_id, $entry );
		}

		update_option( self::OPTION_CACHE, $cache, true );
	}

	/**
	 * Build a cache/registration entry from pattern data.
	 *
	 * @param array $pattern Pattern data.
	 * @param int   $post_id wp_block post ID.
	 * @return array<string, mixed>
	 */
	private function pattern_to_cache_entry( array $pattern, int $post_id ): array {
		$entry = [
			'post_id' => $post_id,
			'title'   => $pattern['title'],
		];

		foreach ( [ 'description', 'categories', 'keywords', 'blockTypes', 'postTypes', 'templateTypes', 'viewportWidth' ] as $key ) {
			if ( ! empty( $pattern[ $key ] ) ) {
				$entry[ $key ] = $pattern[ $key ];
			}
		}

		return $entry;
	}

	/**
	 * Unregister the theme copy and register a core/block ref for the slug.
	 *
	 * @param string               $slug    Pattern slug.
	 * @param int                  $post_id wp_block ID.
	 * @param array<string, mixed> $entry   Registration metadata.
	 * @return void
	 */
	private function register_pattern_ref( string $slug, int $post_id, array $entry ): void {
		$registry = \WP_Block_Patterns_Registry::get_instance();

		if ( $registry->is_registered( $slug ) ) {
			$registry->unregister( $slug );
		}

		$registration = [
			'title'    => $entry['title'] ?? $slug,
			'slug'     => $slug,
			'content'  => sprintf( '<!-- wp:block {"ref":%d} /-->', $post_id ),
			'inserter' => false,
		];

		foreach ( [ 'description', 'categories', 'keywords', 'blockTypes', 'postTypes', 'templateTypes' ] as $key ) {
			if ( ! empty( $entry[ $key ] ) ) {
				$registration[ $key ] = $entry[ $key ];
			}
		}

		if ( ! empty( $entry['viewportWidth'] ) ) {
			$registration['viewportWidth'] = (int) $entry['viewportWidth'];
		}

		$registry->register( $slug, $registration );
	}

	/**
	 * Fingerprint theme pattern files (stylesheet + parent + mtime/size per file).
	 *
	 * Cheap to compute every request; changes only when files are added/removed/edited.
	 *
	 * @return string
	 */
	private function get_files_fingerprint(): string {
		$theme  = wp_get_theme();
		$parts  = [
			'stylesheet:' . $theme->get_stylesheet(),
			'template:' . $theme->get_template(),
		];
		$themes = [ $theme ];

		if ( $theme->parent() ) {
			$themes[] = $theme->parent();
		}

		$file_parts = [];

		foreach ( $themes as $theme_obj ) {
			$dir = $theme_obj->get_stylesheet_directory() . '/patterns';

			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$files = glob( $dir . '/*.php' );

			if ( empty( $files ) ) {
				continue;
			}

			foreach ( $files as $file ) {
				$mtime = filemtime( $file );
				$size  = filesize( $file );

				$file_parts[] = sprintf(
					'%s:%s:%s:%s',
					$theme_obj->get_stylesheet(),
					basename( $file ),
					false === $mtime ? '0' : (string) $mtime,
					false === $size ? '0' : (string) $size
				);
			}
		}

		sort( $file_parts );
		$parts = array_merge( $parts, $file_parts );

		/**
		 * Filter the fingerprint inputs for synced theme pattern caching.
		 *
		 * @param string[] $parts Fingerprint segments.
		 */
		$parts = apply_filters( 'orbit_synced_theme_patterns_fingerprint_parts', $parts );

		return md5( implode( '|', $parts ) );
	}

	/**
	 * When a core/pattern block references a synced theme slug and carries
	 * override attributes, render via core/block so overrides apply.
	 *
	 * @param string|null $pre_render   Pre-rendered content, or null to continue.
	 * @param array       $parsed_block Parsed block.
	 * @return string|null
	 */
	public function render_pattern_block_as_synced_ref( $pre_render, array $parsed_block ) {
		if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/pattern' ) {
			return $pre_render;
		}

		$attrs = $parsed_block['attrs'] ?? [];
		$slug  = $attrs['slug'] ?? '';

		if ( '' === $slug || empty( $this->synced_pattern_ids[ $slug ] ) ) {
			return $pre_render;
		}

		$override_attrs = $attrs;
		unset( $override_attrs['slug'] );

		if ( empty( $override_attrs ) ) {
			return $pre_render;
		}

		$block_attrs = array_merge(
			[ 'ref' => (int) $this->synced_pattern_ids[ $slug ] ],
			$override_attrs
		);

		return do_blocks(
			sprintf(
				'<!-- wp:block %s /-->',
				wp_json_encode( $block_attrs )
			)
		);
	}

	/**
	 * Prevent Site Editor / REST updates from overwriting theme-managed patterns.
	 *
	 * @param mixed           $response Result to send to the client.
	 * @param array           $handler  Route handler used for the request.
	 * @param WP_REST_Request $request  Request used to generate the response.
	 * @return mixed
	 */
	public function block_managed_pattern_updates( $response, $handler, WP_REST_Request $request ) {
		$route = $request->get_route();

		if ( ! preg_match( '#^/wp/v2/blocks/(\d+)$#', $route, $matches ) ) {
			return $response;
		}

		if ( 'PUT' !== $request->get_method() && 'PATCH' !== $request->get_method() ) {
			return $response;
		}

		$post = get_post( (int) $matches[1] );

		if ( ! $post instanceof WP_Post || 'wp_block' !== $post->post_type ) {
			return $response;
		}

		if ( '1' !== (string) get_post_meta( $post->ID, self::META_MANAGED, true ) ) {
			return $response;
		}

		return new WP_Error(
			'orbit_cannot_update_theme_synced_pattern',
			__( 'This synced pattern is managed by the theme. Update the theme pattern file instead.', 'orbit' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * Create or update a wp_block post from a theme pattern file.
	 *
	 * @param array $pattern Pattern data including file path and content.
	 * @return int Post ID, or 0 on failure.
	 */
	private function upsert_synced_pattern( array $pattern ): int {
		$content = $pattern['content'];
		$hash    = md5( $content );
		$post    = $this->find_managed_pattern( $pattern['slug'] );

		if ( $post instanceof WP_Post ) {
			$existing_hash = (string) get_post_meta( $post->ID, self::META_HASH, true );

			if ( $existing_hash === $hash && $post->post_title === $pattern['title'] ) {
				$this->sync_pattern_terms( $post->ID, $pattern );
				return (int) $post->ID;
			}

			$updated = wp_update_post(
				[
					'ID'           => $post->ID,
					'post_title'   => $pattern['title'],
					'post_content' => $content,
					'post_status'  => 'publish',
				],
				true
			);

			if ( is_wp_error( $updated ) ) {
				return 0;
			}

			update_post_meta( $post->ID, self::META_HASH, $hash );
			update_post_meta( $post->ID, self::META_MANAGED, '1' );
			update_post_meta( $post->ID, self::META_SLUG, $pattern['slug'] );
			delete_post_meta( $post->ID, 'wp_pattern_sync_status' );
			$this->sync_pattern_terms( $post->ID, $pattern );

			return (int) $post->ID;
		}

		$post_id = wp_insert_post(
			[
				'post_title'     => $pattern['title'],
				'post_name'      => $this->post_name_from_slug( $pattern['slug'] ),
				'post_content'   => $content,
				'post_type'      => 'wp_block',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'meta_input'     => [
					self::META_SLUG    => $pattern['slug'],
					self::META_HASH    => $hash,
					self::META_MANAGED => '1',
				],
			],
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return 0;
		}

		delete_post_meta( $post_id, 'wp_pattern_sync_status' );
		$this->sync_pattern_terms( (int) $post_id, $pattern );

		return (int) $post_id;
	}

	/**
	 * Find an Orbit-managed wp_block by theme pattern slug.
	 *
	 * @param string $slug Theme pattern slug.
	 * @return WP_Post|null
	 */
	private function find_managed_pattern( string $slug ): ?WP_Post {
		$query = new WP_Query(
			[
				'post_type'              => 'wp_block',
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
				'meta_query'             => [
					'relation' => 'AND',
					[
						'key'   => self::META_MANAGED,
						'value' => '1',
					],
					[
						'key'   => self::META_SLUG,
						'value' => $slug,
					],
				],
			]
		);

		if ( ! empty( $query->posts[0] ) && $query->posts[0] instanceof WP_Post ) {
			return $query->posts[0];
		}

		$by_name = get_posts(
			[
				'post_type'      => 'wp_block',
				'post_status'    => 'any',
				'name'           => $this->post_name_from_slug( $slug ),
				'posts_per_page' => 1,
			]
		);

		if ( empty( $by_name[0] ) || ! $by_name[0] instanceof WP_Post ) {
			return null;
		}

		if ( '1' === (string) get_post_meta( $by_name[0]->ID, self::META_MANAGED, true ) ) {
			return $by_name[0];
		}

		return null;
	}

	/**
	 * Assign pattern categories on the wp_block post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $pattern Pattern data.
	 * @return void
	 */
	private function sync_pattern_terms( int $post_id, array $pattern ): void {
		if ( empty( $pattern['categories'] ) || ! is_array( $pattern['categories'] ) ) {
			return;
		}

		if ( ! taxonomy_exists( 'wp_pattern_category' ) ) {
			return;
		}

		wp_set_object_terms( $post_id, $pattern['categories'], 'wp_pattern_category' );
	}

	/**
	 * Collect Synced theme patterns from the active (and parent) theme.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_synced_patterns_from_themes(): array {
		$themes   = [];
		$theme    = wp_get_theme();
		$themes[] = $theme;

		if ( $theme->parent() ) {
			$themes[] = $theme->parent();
		}

		$patterns = [];
		$seen     = [];

		foreach ( $themes as $theme_obj ) {
			$dir = $theme_obj->get_stylesheet_directory() . '/patterns';

			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$files = glob( $dir . '/*.php' );

			if ( empty( $files ) ) {
				continue;
			}

			foreach ( $files as $file ) {
				$pattern = $this->parse_synced_pattern_file( $file );

				if ( ! $pattern ) {
					continue;
				}

				if ( isset( $seen[ $pattern['slug'] ] ) ) {
					continue;
				}

				$seen[ $pattern['slug'] ] = true;
				$patterns[]               = $pattern;
			}
		}

		/**
		 * Filter synced theme patterns discovered by Orbit.
		 *
		 * @param array $patterns Pattern data arrays.
		 */
		return apply_filters( 'orbit_synced_theme_patterns', $patterns );
	}

	/**
	 * Parse a pattern PHP file if it opts into Synced.
	 *
	 * @param string $file Absolute path.
	 * @return array<string, mixed>|null
	 */
	private function parse_synced_pattern_file( string $file ): ?array {
		$headers = get_file_data(
			$file,
			[
				'title'         => 'Title',
				'slug'          => 'Slug',
				'description'   => 'Description',
				'viewportWidth' => 'Viewport Width',
				'inserter'      => 'Inserter',
				'categories'    => 'Categories',
				'keywords'      => 'Keywords',
				'blockTypes'    => 'Block Types',
				'postTypes'     => 'Post Types',
				'templateTypes' => 'Template Types',
				'synced'        => 'Synced',
			]
		);

		if ( ! $this->is_synced_header( $headers['synced'] ?? '' ) ) {
			return null;
		}

		if ( empty( $headers['slug'] ) || empty( $headers['title'] ) ) {
			return null;
		}

		$content = $this->render_pattern_file( $file );

		if ( '' === trim( $content ) ) {
			return null;
		}

		$pattern = [
			'title'   => $headers['title'],
			'slug'    => $headers['slug'],
			'content' => $content,
			'file'    => $file,
		];

		if ( ! empty( $headers['description'] ) ) {
			$pattern['description'] = $headers['description'];
		}

		if ( ! empty( $headers['viewportWidth'] ) ) {
			$pattern['viewportWidth'] = (int) $headers['viewportWidth'];
		}

		foreach ( [ 'categories', 'keywords', 'blockTypes', 'postTypes', 'templateTypes' ] as $list_key ) {
			if ( empty( $headers[ $list_key ] ) ) {
				continue;
			}

			$pattern[ $list_key ] = array_filter(
				array_map( 'trim', explode( ',', $headers[ $list_key ] ) )
			);
		}

		return $pattern;
	}

	/**
	 * Whether a Synced header value opts the pattern in.
	 *
	 * @param string $value Header value.
	 * @return bool
	 */
	private function is_synced_header( string $value ): bool {
		$value = strtolower( trim( $value ) );

		return in_array( $value, [ 'true', 'yes', '1' ], true );
	}

	/**
	 * Render pattern file content (supports PHP in pattern files).
	 *
	 * @param string $file Absolute path.
	 * @return string
	 */
	private function render_pattern_file( string $file ): string {
		ob_start();
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Theme pattern path from stylesheet directory.
		include $file;
		return (string) ob_get_clean();
	}

	/**
	 * Convert a namespaced pattern slug into a valid post_name.
	 *
	 * @param string $slug Pattern slug (may include /).
	 * @return string
	 */
	private function post_name_from_slug( string $slug ): string {
		return sanitize_title( str_replace( '/', '-', $slug ) );
	}
}
