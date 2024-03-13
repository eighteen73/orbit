<?php
/**
 * Endpoint for a simple website health check
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit;

use WP_REST_Request;

/**
 * Adds an endpoint (/wp-json/orbit/up) for load balancers and other website monitors to use as a health check
 */
class HealthCheck {
	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'rest_api_init', [ $this, 'add_endpoint' ] );
	}

	/**
	 * Add the API endpoint
	 *
	 * @return void
	 */
	public function add_endpoint(): void {
		register_rest_route(
			'orbit',
			'/up',
			[
				'methods' => 'GET',
				'callback' => [ $this, 'run_heathcheck' ],
			]
		);
	}

	/**
	 * The fact that the website was able to get this far is really all we need to know. It confirms that the DB
	 * connection works and the website isn't in maintenance mode.
	 *
	 * @param WP_REST_Request $data The request data
	 * @return string
	 */
	public function run_heathcheck( $data ) {
		return 'ok';
	}
}
