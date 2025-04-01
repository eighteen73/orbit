<?php
/**
 * Plugin setup and initialization
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit;

use Eighteen73\Orbit\Singleton;

/**
 * Plugin setup and initialization
 */
class Setup {
	use Singleton;

	/**
	 * Initialize the class
	 */
	public function setup(): void {
		API\Settings::instance()->setup();
		DisallowIndexing\DisallowIndexing::instance()->setup();

		// Initialize features
		add_action( 'init', [ $this, 'init_features' ] );

		// Initialize admin
		add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Initialize all plugin features
	 */
	public function init_features(): void {
		Admin\CleanUI::instance()->setup();
		Admin\HideUpdates::instance()->setup();
		Security\DisableAPI::instance()->setup();
		Security\DisableXMLRPC::instance()->setup();
		Security\HideAuthor::instance()->setup();
		Security\HideVersion::instance()->setup();
		Security\RemoveHeadLinks::instance()->setup();
		OtherFilters::instance()->setup();
		HealthCheck::instance()->setup();
	}

	/**
	 * Register the settings page
	 */
	public function register_settings_page(): void {
		add_options_page(
			__( 'Orbit Settings', 'orbit' ),
			__( 'Orbit', 'orbit' ),
			'manage_options',
			'orbit',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render the settings page container
	 */
	public function render_settings_page(): void {
		printf(
			'<div class="wrap" id="orbit-settings-root">%s</div>',
			esc_html__( 'Loading…', 'orbit' )
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( string $hook ): void {
		if ( 'settings_page_orbit' !== $hook ) {
			return;
		}

		$asset_file = include ORBIT_PATH . 'build/index.asset.php';

		wp_enqueue_script(
			'orbit-admin',
			ORBIT_URL . 'build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			[
				'in_footer' => true,
			],
		);

		wp_enqueue_style( 'wp-components' );
	}
}
