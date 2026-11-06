<?php
/**
 * Font Library.
 *
 * Fonts are a design decision managed in the theme (theme.json), so core's
 * Font Library is turned off: no Appearance > Fonts page, no font management
 * in the editor's styles panel, and no installing or uploading fonts through
 * the REST API. Re-enable it with the `orbit_enable_font_library` filter.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\BlockEditor;

use Eighteen73\Orbit\Singleton;
use WP_Error;
use WP_REST_Request;

/**
 * Font Library.
 */
class FontLibrary {

	use Singleton;

	/**
	 * REST routes that install, upload or remove fonts.
	 */
	private const PROTECTED_ROUTE_PATTERN = '#^/wp/v2/font-families(/|$)#';

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'admin_menu', [ $this, 'remove_menu_item' ], 999 );
		add_action( 'load-font-library.php', [ $this, 'block_page' ] );
		add_filter( 'block_editor_settings_all', [ $this, 'disable_in_editor' ] );
		add_filter( 'rest_pre_dispatch', [ $this, 'block_rest_writes' ], 10, 3 );
	}

	/**
	 * Whether the Font Library is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		/**
		 * Filters whether core's Font Library is available.
		 *
		 * @param bool $enabled False by default: fonts are managed in the theme.
		 */
		return (bool) apply_filters( 'orbit_enable_font_library', false );
	}

	/**
	 * Remove Appearance > Fonts.
	 *
	 * @return void
	 */
	public function remove_menu_item(): void {
		if ( ! $this->is_enabled() ) {
			remove_submenu_page( 'themes.php', 'font-library.php' );
		}
	}

	/**
	 * Prevent direct access to the Fonts page.
	 *
	 * @return void
	 */
	public function block_page(): void {
		if ( $this->is_enabled() ) {
			return;
		}

		wp_die(
			esc_html__( 'Fonts are managed in the theme.', 'orbit' ),
			esc_html__( 'Fonts disabled', 'orbit' ),
			[
				'response'  => 403,
				'back_link' => true,
			]
		);
	}

	/**
	 * Hide font management in the editor's styles panel.
	 *
	 * @param array $settings Block editor settings.
	 * @return array
	 */
	public function disable_in_editor( array $settings ): array {
		if ( ! $this->is_enabled() ) {
			$settings['fontLibraryEnabled'] = false;
		}

		return $settings;
	}

	/**
	 * Reject REST writes to font families and faces.
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
			'orbit_font_library_disabled',
			__( 'Fonts are managed in the theme.', 'orbit' ),
			[ 'status' => 403 ]
		);
	}
}
