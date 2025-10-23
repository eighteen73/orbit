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
	 * Setup module
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize module
	 *
	 * @return void
	 */
	public function init(): void {
		if ( apply_filters( 'orbit_enable_xmlrpc', false ) ) {
			return;
		}

		add_filter( 'xmlrpc_enabled', '__return_false' );
		remove_action( 'wp_head', 'rsd_link' );
	}
}
