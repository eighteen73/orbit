<?php
/**
 * Environment-gated Site Editor.
 *
 * Block theme templates, template parts and global styles are version
 * controlled in the theme. Outside of local/development environments the
 * Site Editor is disabled entirely so nothing can be saved to the database,
 * and any database overrides that already exist are ignored so the theme
 * files are always the source of truth.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\BlockEditor;

use Eighteen73\Orbit\Environment;
use Eighteen73\Orbit\Singleton;
use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_Theme_JSON;
use WP_Theme_JSON_Data;

/**
 * Environment-gated Site Editor.
 */
class SiteEditor {

	use Singleton;
	use Environment;

	/**
	 * Environments where the Site Editor is enabled by default.
	 */
	private const ENABLED_ENVIRONMENTS = [ 'local', 'development' ];

	/**
	 * Post types that store Site Editor customisations.
	 */
	private const TEMPLATE_POST_TYPES = [ 'wp_template', 'wp_template_part' ];

	/**
	 * REST routes that write Site Editor customisations.
	 */
	private const PROTECTED_ROUTE_PATTERN = '#^/wp/v2/(templates|template-parts|global-styles)(/|$)#';

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'admin_menu', [ $this, 'remove_menu_items' ], 999 );
		add_action( 'admin_bar_menu', [ $this, 'remove_toolbar_items' ], 999 );
		add_action( 'load-site-editor.php', [ $this, 'block_site_editor' ] );
		add_action( 'load-index.php', [ $this, 'remove_welcome_panel' ] );
		add_filter( 'block_editor_settings_all', [ $this, 'disable_template_mode' ] );
		add_filter( 'rest_pre_dispatch', [ $this, 'block_rest_writes' ], 10, 3 );
		add_filter( 'posts_pre_query', [ $this, 'ignore_database_templates' ], 10, 2 );
		add_filter( 'wp_theme_json_data_user', [ $this, 'ignore_user_global_styles' ] );
	}

	/**
	 * Whether the Site Editor is enabled for this environment.
	 *
	 * Evaluated lazily so themes can filter it.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		$enabled = in_array( $this->environment(), self::ENABLED_ENVIRONMENTS, true );

		/**
		 * Filters whether the Site Editor is enabled.
		 *
		 * When disabled, the Site Editor UI is removed, template/template part/global
		 * styles REST writes are rejected and existing database overrides are ignored.
		 *
		 * @param bool $enabled True on local and development environments.
		 */
		return (bool) apply_filters( 'orbit_enable_site_editor', $enabled );
	}

	/**
	 * Remove Site Editor entries (Editor, Design, Patterns) from the Appearance menu.
	 *
	 * @return void
	 */
	public function remove_menu_items(): void {
		global $submenu;

		if ( $this->is_enabled() || empty( $submenu['themes.php'] ) ) {
			return;
		}

		foreach ( $submenu['themes.php'] as $item ) {
			if ( isset( $item[2] ) && str_starts_with( $item[2], 'site-editor.php' ) ) {
				remove_submenu_page( 'themes.php', $item[2] );
			}
		}
	}

	/**
	 * Remove the "Edit site" toolbar link.
	 *
	 * @param \WP_Admin_Bar $admin_bar Toolbar instance.
	 * @return void
	 */
	public function remove_toolbar_items( $admin_bar ): void {
		if ( $this->is_enabled() ) {
			return;
		}

		$admin_bar->remove_node( 'site-editor' );
	}

	/**
	 * Remove the dashboard welcome panel, which links block themes to the Site Editor.
	 *
	 * @return void
	 */
	public function remove_welcome_panel(): void {
		if ( ! $this->is_enabled() ) {
			remove_action( 'welcome_panel', 'wp_welcome_panel' );
		}
	}

	/**
	 * Prevent direct access to the Site Editor.
	 *
	 * @return void
	 */
	public function block_site_editor(): void {
		if ( $this->is_enabled() ) {
			return;
		}

		wp_die(
			esc_html__( 'The Site Editor is disabled on this environment. Templates and styles are managed in the theme.', 'orbit' ),
			esc_html__( 'Site Editor disabled', 'orbit' ),
			[
				'response'  => 403,
				'back_link' => true,
			]
		);
	}

	/**
	 * Stop the post editor offering template editing.
	 *
	 * @param array $settings Block editor settings.
	 * @return array
	 */
	public function disable_template_mode( array $settings ): array {
		if ( ! $this->is_enabled() ) {
			$settings['supportsTemplateMode'] = false;
		}

		return $settings;
	}

	/**
	 * Reject REST writes to templates, template parts and global styles.
	 *
	 * @param mixed           $result  Response to replace the requested version with.
	 * @param \WP_REST_Server $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return mixed
	 */
	public function block_rest_writes( mixed $result, $server, WP_REST_Request $request ): mixed {
		if ( null !== $result || $this->is_enabled() ) {
			return $result;
		}

		if ( in_array( $request->get_method(), [ 'GET', 'HEAD', 'OPTIONS' ], true ) ) {
			return $result;
		}

		if ( ! preg_match( self::PROTECTED_ROUTE_PATTERN, $request->get_route() ) ) {
			return $result;
		}

		return new WP_Error(
			'orbit_site_editor_disabled',
			__( 'Templates, template parts and global styles cannot be changed on this environment.', 'orbit' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * Ignore database templates and template parts so theme files always win.
	 *
	 * Core falls back to the theme file whenever no matching post is found, for
	 * both template resolution and the template part block. WP-CLI is left alone
	 * so developers can inspect and remove stale overrides.
	 *
	 * @param array|null $posts Posts to short-circuit the query with.
	 * @param WP_Query   $query The query.
	 * @return array|null
	 */
	public function ignore_database_templates( ?array $posts, WP_Query $query ): ?array {
		if ( null !== $posts || ( defined( 'WP_CLI' ) && \WP_CLI ) ) {
			return $posts;
		}

		$post_types = (array) $query->get( 'post_type' );

		if ( empty( array_intersect( $post_types, self::TEMPLATE_POST_TYPES ) ) ) {
			return $posts;
		}

		if ( $this->is_enabled() ) {
			return $posts;
		}

		$query->found_posts   = 0;
		$query->max_num_pages = 0;

		return [];
	}

	/**
	 * Ignore global styles saved to the database.
	 *
	 * @param WP_Theme_JSON_Data $theme_json User theme.json data.
	 * @return WP_Theme_JSON_Data
	 */
	public function ignore_user_global_styles( $theme_json ) {
		if ( $this->is_enabled() || ! class_exists( WP_Theme_JSON_Data::class ) ) {
			return $theme_json;
		}

		return new WP_Theme_JSON_Data(
			[ 'version' => WP_Theme_JSON::LATEST_SCHEMA ],
			'custom'
		);
	}
}
