<?php
/**
 * Amend security headers
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;

/**
 * Add a sensible baseline set of security headers to the response.
 */
class Headers {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup() {
		add_filter( 'wp_headers', [ $this, 'set_security_headers' ], 99, 1 );
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

			// Permissive CSP (websites should customise this, ideally)
			'Content-Security-Policy' => implode(
				'; ',
				[
					"default-src 'self' https:",
					"img-src 'self' https: data: blob:",
					"script-src 'self' https: 'unsafe-inline' 'unsafe-eval'",
					"style-src 'self' https: 'unsafe-inline'",
					"frame-ancestors 'self'",
				]
			),
		];

		// Only if SSL
		if ( is_ssl() ) {
			$default_security_headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
			$default_security_headers['Content-Security-Policy'] .= '; upgrade-insecure-requests';
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
