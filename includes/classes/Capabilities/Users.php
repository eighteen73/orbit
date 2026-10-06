<?php
/**
 * Add user management capabilities to editors and shop managers
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Capabilities;

use Eighteen73\Orbit\Singleton;

/**
 * Add user management capabilities to editors and shop managers
 */
class Users {
	use Singleton;

	/**
	 * Setup module
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'admin_init', [ $this, 'manage_user_caps' ] );
		add_filter( 'map_meta_cap', [ $this, 'allow_create_users_on_multisite' ], 10, 3 );
	}

	/**
	 * Add or remove user-related capabilities based on a filter.
	 *
	 * @return void
	 */
	public function manage_user_caps(): void {
		$disable_user_caps = ! apply_filters( 'orbit_enable_user_caps_access', true );

		$roles = [
			'editor',
			'shop_manager',
		];

		$caps = [
			'list_users',
			'create_users',
			'edit_users',
		];

		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );

			if ( ! $role ) {
				continue;
			}

			if ( $role->has_cap( 'delete_users' ) ) {
				$role->remove_cap( 'delete_users' );
			}

			foreach ( $caps as $cap ) {
				$has_cap = $role->has_cap( $cap );

				if ( $disable_user_caps && $has_cap ) {
					$role->remove_cap( $cap );
				} elseif ( ! $disable_user_caps && ! $has_cap ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	/**
	 * Let editors and shop managers create users on Multisite.
	 *
	 * WordPress maps create_users to do_not_allow unless the user is a super
	 * admin or the network add_new_users option is enabled. Orbit already
	 * grants the primitive cap, so restore it for the same roles.
	 *
	 * @param array  $caps    Primitive capabilities required for the check.
	 * @param string $cap     Capability being checked.
	 * @param int    $user_id User ID being checked.
	 * @return array
	 */
	public function allow_create_users_on_multisite( $caps, $cap, $user_id ): array {
		if ( $cap !== 'create_users' || ! is_multisite() ) {
			return $caps;
		}

		if ( ! apply_filters( 'orbit_enable_user_caps_access', true ) ) {
			return $caps;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return $caps;
		}

		$roles = [
			'editor',
			'shop_manager',
		];

		if ( ! array_intersect( $user->roles, $roles ) ) {
			return $caps;
		}

		return [ 'create_users' ];
	}
}
