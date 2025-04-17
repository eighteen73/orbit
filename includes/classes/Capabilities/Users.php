<?php
/**
 * Add user management capabilities to editors and shop managers
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Capabilities;

use Eighteen73\Orbit\Singleton;

class Users {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'admin_init', [ $this, 'manage_user_caps' ] );
	}

	/**
	 * Add or remove user-related capabilities based on a filter.
	 */
	public function manage_user_caps(): void {
		$disable_user_caps = apply_filters( 'orbit_enable_disable_user_caps_access', false );

		$roles = [
			'editor',
			'shop_manager',
		];

		$caps = [
			'list_users',
		];

		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );

			if ( ! $role ) {
				continue;
			}

			foreach ( $caps as $cap ) {
				$role->remove_cap( 'delete_users' );

				if ( $disable_user_caps ) {
					if ( $role->has_cap( $cap ) ) {
						$role->remove_cap( $cap );
					}
				} else {
					if ( ! $role->has_cap( $cap ) ) {
						$role->add_cap( $cap );
					}
				}
			}
		}
	}
}
