<?php
/**
 * Disable the REST API features
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;
use function Eighteen73\Orbit\get_setting;

/**
 * Disable the REST API features
 */
class DisableAPI {
	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		if ( get_setting( 'enable_user_endpoints', false ) ) {
			return;
		}
		add_filter( 'rest_endpoints', [ $this, 'disable_users' ] );
	}

	/**
	 * Disable the /wp-json/wp/v2/users endpoint
	 *
	 * @param array $endpoints API endpoints
	 * @return array
	 */
	public function disable_users( array $endpoints ): array {
		if ( ! is_user_logged_in() && ! is_admin() ) {
			if ( isset( $endpoints['/wp/v2/users'] ) ) {
				unset( $endpoints['/wp/v2/users'] );
			}
			if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
				unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
			}
		}
		return $endpoints;
	}
}
