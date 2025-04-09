<?php
/**
 * Hide update options for non-admin users
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Admin;

use Eighteen73\Orbit\Singleton;
use WP_Admin_Bar;

/**
 * Hide update options for non-admin users
 *
 * Inspired by https://wordpress.org/plugins/hide-updates/
 */
class EnvironmentName {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup() {
		if ( ! $this->is_allowed() ) {
			return;
		}

		add_action( 'admin_bar_menu', [ $this, 'add_environment_name_to_toolbar' ], 0 );
		add_action( 'admin_head', [ $this, 'add_admin_styles' ] );
		add_action( 'wp_head', [ $this, 'add_admin_styles' ] );
	}

	/**
	 * Determine whether it's enabled or not
	 *
	 * @return bool
	 */
	public function is_allowed(): bool {

		if ( apply_filters( 'orbit_enable_disable_admin_environment_name', false ) ) {
			return false;
		}

		// It's enabled, so check the user's role
		$current_user       = wp_get_current_user();
		$current_user_roles = $current_user->roles;
		if ( in_array( 'administrator', $current_user_roles, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add the environment name to the admin toolbar
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function add_environment_name_to_toolbar( WP_Admin_Bar $wp_admin_bar ) {
		$env = wp_get_environment_type();

		$wp_admin_bar->add_node( [
			'id'    => 'orbit-environment-name',
			'title' => strtoupper( $env ),
			'meta'  => [
				'class' => 'orbit-env-toolbar',
				'title' => 'Current environment: ' . $env,
			],
		] );
	}

	public function add_admin_styles() {
		$env = wp_get_environment_type();

		$colors = [
			'production' => '#d63638',
			'staging'    => '#eab308',
			'development'=> '#16a34a',
		];

		$color = $colors[ $env ] ?? '#64748b';

		echo '<style>
			#wp-admin-bar-orbit-environment-name > .ab-item {
				background-color: ' . esc_attr( $color ) . ';
				color: #fff !important;
				font-weight: bold;

			}
		</style>';
	}
}
