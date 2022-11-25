<?php
/**
 * Completely disable XML-RPC
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Forms\Options;
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
		if ( Options::get_option( 'orbit_security.general.enable_xmlrpc', false ) ) {
			return;
		}
		add_filter( 'xmlrpc_enabled', '__return_false' );
		remove_action( 'wp_head', 'rsd_link' );
	}
}
