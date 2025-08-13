<?php
/**
 * Modifications to WooCommerce.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\ThirdParty;

use Eighteen73\Orbit\Singleton;

/**
 * Modifications to WooCommerce.
 */
class WooCommerce {
	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {

		// Force WooCommerce tracking to always be disabled.
		// This setting loads additional patterns from PTK.
		add_filter( 'option_woocommerce_allow_tracking', '__return_false' );
	}
}
