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
	 * Roles that are given access to privacy features.
	 *
	 * @var string[]
	 */
	private array $roles = [
		'editor',
		'shop_manager',
	];

	/**
	 * Setup module
	 */
	public function setup(): void {
		add_filter( 'map_meta_cap', [ $this, 'remove_manage_options_requirement' ], 10, 3 );
		add_filter( 'map_meta_cap', [ $this, 'allow_privacy_tools' ], 10, 3 );
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
		if ( $cap !== 'manage_privacy_options' ) {
			return $caps;
		}

		if ( ! apply_filters( 'orbit_enable_privacy_page_access', true ) ) {
			return $caps;
		}

		if ( ! $this->user_has_role( $user_id ) ) {
			return $caps;
		}

		return $this->without_manage_options( $caps );
	}

	/**
	 * Gives certain user roles access to the Tools > Export Personal Data and
	 * Tools > Erase Personal Data screens.
	 *
	 * WordPress maps both capabilities to 'manage_options', and erasure also
	 * requires 'delete_users'. Orbit removes 'delete_users' from these roles,
	 * so it is only granted while performing a personal data erasure and does
	 * not allow user accounts to be deleted.
	 *
	 * @param array  $caps    The capabilities required for the requested capability.
	 * @param string $cap     The capability being checked (meta capability).
	 * @param int    $user_id The user ID for which capabilities are being checked.
	 *
	 * @return array The modified list of capabilities.
	 */
	public function allow_privacy_tools( $caps, $cap, $user_id ): array {
		$privacy_caps = [
			'export_others_personal_data',
			'erase_others_personal_data',
		];

		$is_erasure_delete_users = $cap === 'delete_users' && $this->is_erasure_request();

		if ( ! in_array( $cap, $privacy_caps, true ) && ! $is_erasure_delete_users ) {
			return $caps;
		}

		if ( ! apply_filters( 'orbit_enable_privacy_tools_access', true ) ) {
			return $caps;
		}

		if ( ! $this->user_has_role( $user_id ) ) {
			return $caps;
		}

		if ( $is_erasure_delete_users ) {
			return is_multisite() ? $caps : [];
		}

		return $this->without_manage_options( $caps );
	}

	/**
	 * Whether the current request is the erase personal data screen or the
	 * AJAX request that performs an erasure.
	 *
	 * @return bool
	 */
	private function is_erasure_request(): bool {
		global $pagenow;

		if ( is_admin() && $pagenow === 'erase-personal-data.php' ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- The nonce is verified by the core AJAX handler.
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

		return wp_doing_ajax() && $action === 'wp-privacy-erase-personal-data';
	}

	/**
	 * Whether the user has one of the roles given access to privacy features.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return bool
	 */
	private function user_has_role( $user_id ): bool {
		$user = get_userdata( $user_id );

		return $user && array_intersect( $user->roles, $this->roles );
	}

	/**
	 * Removes 'manage_options' from a list of required capabilities.
	 *
	 * @param array $caps The capabilities required for the requested capability.
	 *
	 * @return array
	 */
	private function without_manage_options( array $caps ): array {
		return array_values(
			array_filter(
				$caps,
				function ( $capability ) {
					return $capability !== 'manage_options';
				}
			)
		);
	}
}
