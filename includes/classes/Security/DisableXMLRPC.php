<?php
/**
 * Completely disable XML-RPC
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;

/**
 * Completely disable XML-RPC
 */
class DisableXMLRPC {
	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		add_filter( 'xmlrpc_enabled', '__return_false' );
		remove_action( 'wp_head', 'rsd_link' );
	}

	/**
	 * Detects whether this feature's setting is enabled
	 *
	 * @return bool True is this class should take effect
	 */
	public function is_enabled(): bool {
		return carbon_get_theme_option( 'orbit_security_xmlrpc' ) === true;
	}
}
