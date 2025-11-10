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
			// Cross-origin hardening
			'Cross-Origin-Opener-Policy'   => 'same-origin',
			'Cross-Origin-Resource-Policy' => 'same-origin',

			// Sensible privacy default
			'Referrer-Policy' => 'strict-origin-when-cross-origin',

			// Stops MIME sniffing
			'X-Content-Type-Options' => 'nosniff',

			// Prevent clickjacking inside iframes (legacy)
			'X-Frame-Options' => 'SAMEORIGIN',

			// Good default for non-sensitive resources
			'Cache-Control' => 'Cache-Control: no-cache',
		];

		$default_csp = [
			'upgrade-insecure-requests',
			"default-src 'self'",
		];
		$default_security_headers['Content-Security-Policy'] = trim( implode( '; ', $default_csp ) );

		// Only if SSL
		if ( is_ssl() ) {
			$default_security_headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
		}

		$security_headers = apply_filters( 'orbit_default_security_headers', $default_security_headers );

		foreach ( $security_headers as $header => $value ) {
			if ( ! empty( $value ) ) {
				$headers[ $header ] = $value;
			}
		}

		return $headers;
	}
}
