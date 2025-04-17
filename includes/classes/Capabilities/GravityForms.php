<?php
/**
 * Add permissions for editors to access key site areas
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Capabilities;

use Eighteen73\Orbit\Singleton;

class GravityForms {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'admin_init', [ $this, 'manage_gravity_forms_caps' ] );
	}

	/**
	 * Add or remove Gravity Forms capabilities based on a filter.
	 */
	public function manage_gravity_forms_caps(): void {
		$disable_gf_access = apply_filters( 'orbit_enable_disable_gravity_forms_access', false );

		$roles = [
			'editor',
			'shop_manager'
		];
		$caps = [
			'gform_full_access'
		];

		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );

			if ( ! $role ) {
				continue;
			}

			foreach ( $caps as $cap ) {
				if ( $disable_gf_access ) {
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
