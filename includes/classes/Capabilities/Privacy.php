<?php
/**
 * Add site editor capabilities to editors and shop managers
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Capabilities;

use Eighteen73\Orbit\Singleton;

/**
 * Add privacy capabilities to editors and shop managers
 */
class Privacy {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'admin_init', [ $this, 'manage_privacy_caps' ] );
	}

	/**
	 * Add or remove privacy capabilities based on a filter.
	 */
	public function manage_privacy_caps(): void {
		$disable_user_caps = ! apply_filters( 'orbit_enable_privacy_caps_access', true );

		$roles = [
			'editor',
			'shop_manager',
		];

		$caps = [
			'manage_privacy_options',
			'manage_options',
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
