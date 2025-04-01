<?php
/**
 * REST API endpoints for Orbit settings
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\API;

use Eighteen73\Orbit\Singleton;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API endpoints for Orbit settings
 */
class Settings {
	use Singleton;

	/**
	 * Initialize the class
	 */
	public function setup(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes(): void {
		register_rest_route(
			'orbit/v1',
			'/settings',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'update_settings' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);
	}

	/**
	 * Check if the user has permission to access settings
	 *
	 * @return bool|WP_Error
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access these settings.', 'orbit' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}
		return true;
	}

	/**
	 * Get current settings
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings(): WP_REST_Response {
		$settings = [
			'disable_menu_items'    => get_option( 'orbit_disable_menu_items', [] ),
			'disable_toolbar_items' => get_option( 'orbit_disable_toolbar_items', [] ),
			'login_logo'            => get_option( 'orbit_login_logo', '' ),
			'general'               => get_option( 'orbit_general', [] ),
			'rest_api'              => get_option( 'orbit_rest_api', [] ),
		];

		return new WP_REST_Response( $settings );
	}

	/**
	 * Update settings
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_settings( WP_REST_Request $request ) {
		$params = $request->get_params();

		if ( isset( $params['disable_menu_items'] ) ) {
			update_option( 'orbit_disable_menu_items', $params['disable_menu_items'] );
		}

		if ( isset( $params['disable_toolbar_items'] ) ) {
			update_option( 'orbit_disable_toolbar_items', $params['disable_toolbar_items'] );
		}

		if ( isset( $params['login_logo'] ) ) {
			update_option( 'orbit_login_logo', $params['login_logo'] );
		}

		if ( isset( $params['general'] ) ) {
			update_option( 'orbit_general', $params['general'] );
		}

		if ( isset( $params['rest_api'] ) ) {
			update_option( 'orbit_rest_api', $params['rest_api'] );
		}

		return $this->get_settings();
	}
}
