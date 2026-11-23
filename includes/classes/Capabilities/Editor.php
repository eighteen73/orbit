<?php
/**
 * Add site editor capabilities to editors and shop managers, without the
 * Themes page that comes with them.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Capabilities;

use Eighteen73\Orbit\Singleton;

/**
 * Add site editor capabilities to editors and shop managers
 */
class Editor {
	use Singleton;

	/**
	 * Setup module
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'admin_init', [ $this, 'manage_editor_caps' ] );
		add_action( 'admin_menu', [ $this, 'remove_themes_page' ], 999 );
		add_action( 'load-themes.php', [ $this, 'redirect_themes_page' ] );
	}

	/**
	 * Hide Appearance > Themes from users who can't switch themes.
	 *
	 * `edit_theme_options` (needed for Menus) is enough for core to show the
	 * Themes page, though it only describes the active theme. Appearance then
	 * opens on its first remaining item.
	 *
	 * @return void
	 */
	public function remove_themes_page(): void {
		if ( ! current_user_can( 'switch_themes' ) ) {
			remove_submenu_page( 'themes.php', 'themes.php' );
		}
	}

	/**
	 * Send users who can't switch themes from the Themes page to Menus.
	 *
	 * @return void
	 */
	public function redirect_themes_page(): void {
		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_safe_redirect( admin_url( 'nav-menus.php' ) );
			exit;
		}
	}

	/**
	 * Add or remove site editor capabilities based on a filter.
	 *
	 * @return void
	 */
	public function manage_editor_caps(): void {
		$disable_user_caps = ! apply_filters( 'orbit_enable_editor_caps_access', true );

		$roles = [
			'editor',
			'shop_manager',
		];

		$caps = [
			'edit_theme_options',
		];

		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );

			if ( ! $role ) {
				continue;
			}

			foreach ( $caps as $cap ) {
				if ( $disable_user_caps ) {
					if ( $role->has_cap( $cap ) ) {
						$role->remove_cap( $cap );
					}
				} elseif ( ! $role->has_cap( $cap ) ) {
						$role->add_cap( $cap );
				}
			}
		}
	}
}
