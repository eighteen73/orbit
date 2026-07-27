<?php
/**
 * Modifications to WooCommerce.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\ThirdParty;

use Eighteen73\Orbit\Singleton;
use Eighteen73\Orbit\Support\AbstractNonliveSafeguard;

/**
 * Modifications to WooCommerce.
 */
class WooCommerce extends AbstractNonliveSafeguard {

	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup(): void {
		// Force WooCommerce tracking to always be disabled.
		// This setting loads additional patterns from PTK.
		add_filter( 'option_woocommerce_allow_tracking', '__return_false' );

		if ( did_action( 'plugins_loaded' ) ) {
			$this->maybe_setup_safeguards();
		} else {
			add_action( 'plugins_loaded', [ $this, 'maybe_setup_safeguards' ] );
		}
	}

	/**
	 * Setup safeguards if WooCommerce is active.
	 *
	 * @return void
	 */
	public function maybe_setup_safeguards(): void {
		if ( class_exists( 'WooCommerce' ) ) {
			$this->setup_safeguards();
		}
	}

	/**
	 * Apply safeguards for non-live site mode.
	 *
	 * @return void
	 */
	public function apply_safeguards(): void {

		// Force WooCommerce Subscriptions into Staging / Duplicate Site Mode.
		add_filter( 'woocommerce_subscriptions_is_duplicate_site', '__return_true' );
		add_filter( 'wcs_is_duplicate_site', '__return_true' );

		// Prevent outgoing WooCommerce webhooks on non-live sites unless explicitly enabled.
		if ( ! apply_filters( 'orbit_woocommerce_allow_webhooks_on_nonlive_site', false ) ) {
			add_filter( 'woocommerce_webhook_should_deliver', '__return_false' );
		}
	}
}
