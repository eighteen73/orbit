<?php
/**
 * Amend security headers
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;

/**
 * Completely disable XML-RPC
 */
class Headers {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'wp_headers', [ $this, 'set_security_headers' ], 99, 1 );
	}

	/**
	 * Set security headers
	 *
	 * @param array $headers Headers
	 * @return array
	 */
	public function set_security_headers( $headers ) {

		$default_security_headers = [
			'X-Frame-Options' => 'SAMEORIGIN', // Prevent clickjacking
		];

		$security_headers = apply_filters( 'orbit_default_security_headers', $default_security_headers );

		foreach ( $security_headers as $header => $value ) {
			if ( ! empty( $value ) ) {
				$headers[ $header ] = $value;
			}
		}

		return $headers;
	}
}
