<?php

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;

/**
 * Disable the REST API features
 */
class DisableAPI extends Singleton {

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		if ( carbon_get_theme_option( 'orbit_security_api_users' ) === true ) {
			add_filter( 'rest_endpoints', [ $this, 'disable_users' ] );
		}
	}

	/**
	 * Disable the /wp-json/wp/v2/users endpoint
	 *
	 * @param array $endpoints API endpoints
	 * @return array
	 */
	public function disable_users( array $endpoints ): array {
		if ( isset( $endpoints['/wp/v2/users'] ) ) {
			unset( $endpoints['/wp/v2/users'] );
		}
		if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
			unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
}
