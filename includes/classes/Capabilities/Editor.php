<?php
/**
 * Add site editor capabilities to editors and shop managers
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
	 */
	public function setup() {
		add_action( 'admin_init', [ $this, 'manage_editor_caps' ] );
	}

	/**
	 * Add or remove site editor capabilities based on a filter.
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
