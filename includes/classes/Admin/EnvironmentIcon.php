<?php
/**
 * Add an icon to the toolbar to indicate the environment
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
class EnvironmentIcon {
	use Singleton;

	/**
	 * Setup module
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize module
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ! $this->is_allowed() ) {
			return;
		}

		add_action( 'admin_bar_menu', [ $this, 'add_environment_name_to_toolbar' ], 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_plugin_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_plugin_styles' ] );
	}

	/**
	 * Determine whether it's enabled or not
	 *
	 * @return bool
	 */
	public function is_allowed(): bool {

		if ( ! apply_filters( 'orbit_enable_admin_environment_name', true ) ) {
			return false;
		}

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
	 * @param \WP_Admin_Bar $wp_admin_bar WordPress Admin Bar instance.
	 */
	public function add_environment_name_to_toolbar( \WP_Admin_Bar $wp_admin_bar ) {
		$env = wp_get_environment_type();

		$wp_admin_bar->add_node(
			[
				'id'    => 'orbit-environment-icon',
				'title' => '<span class="ab-icon dashicons-before dashicons-info" title="Environment: ' . esc_attr( ucfirst( $env ) ) . '"></span>',
				'parent' => 'top-secondary',
				'meta'  => [
					'class' => 'orbit-env-toolbar orbit-env-' . esc_attr( $env ),
				],
			]
		);
	}

	/**
	 * Enqueue plugin stylesheet to color the icon.
	 *
	 * @return void
	 */
	public function enqueue_plugin_styles(): void {
		wp_enqueue_style( 'orbit-environment-icon-css', WPMU_PLUGIN_URL . '/orbit/css/environment-icon.css' );
	}
}
