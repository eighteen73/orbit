<?php
/**
 * Add privacy capabilities to editors and shop managers
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
	public function setup(): void {
		add_filter( 'map_meta_cap', [ $this, 'remove_manage_options_requirement' ], 10, 3 );
	}

	/**
	 * Removes the 'manage_options' capability requirement for certain user roles
	 * when accessing the privacy options page.
	 *
	 * @param array  $caps    The capabilities required for the requested capability.
	 * @param string $cap     The capability being checked (meta capability).
	 * @param int    $user_id The user ID for which capabilities are being checked.
	 *
	 * @return array The modified list of capabilities.
	 */
	public function remove_manage_options_requirement( $caps, $cap, $user_id ): array {
		$disable_user_caps = ! apply_filters( 'orbit_enable_privacy_page_access', true );

		if ( $disable_user_caps ) {
			return $caps;
		}

		$roles = [
			'editor',
			'shop_manager',
		];

		if ( $cap === 'manage_privacy_options' ) {
			$user = get_userdata( $user_id );

			if ( ! $user ) {
				return $caps;
			}

			if ( array_intersect( $user->roles, $roles ) ) {
				$caps = array_filter(
					$caps,
					function ( $capability ) {
						return $capability !== 'manage_options';
					}
				);
			}
		}

		return $caps;
	}
}
