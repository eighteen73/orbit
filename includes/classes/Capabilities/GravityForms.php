<?php
/**
 *  Add Gravity Forms capabilities to editors and shop managers
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Capabilities;

use Eighteen73\Orbit\Singleton;

/**
 * Add Gravity Forms capabilities to editors and shop managers
 */
class GravityForms {
	use Singleton;

	/**
	 * Setup module
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'admin_init', [ $this, 'manage_gravity_forms_caps' ] );
	}

	/**
	 * Add or remove Gravity Forms capabilities based on a filter.
	 *
	 * @return void
	 */
	public function manage_gravity_forms_caps(): void {
		$disable_gf_access = ! apply_filters( 'orbit_enable_gravity_forms_access', true );

		$roles = [
			'editor',
			'shop_manager',
		];

		$caps = [
			'gravityforms_create_form',
			'gravityforms_edit_forms',
			'gravityforms_view_entries',
			'gravityforms_edit_entries',
			'gravityforms_export_entries',
			'gravityforms_view_entry_notes',
			'gravityforms_edit_entry_notes',
			'gravityforms_view_settings',
			'gravityforms_edit_settings',
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
				} elseif ( ! $role->has_cap( $cap ) ) {
						$role->add_cap( $cap );
				}
			}
		}
	}
}
