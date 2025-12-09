<?php
/**
 * Configures rate-limiting where available.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;

/**
 * Configures rate-limiting where available.
 */
class RateLimits {
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
		add_filter( 'woocommerce_store_api_rate_limit_options', [ $this, 'woocommerce_checkout' ] );
	}

	/**
	 * Rate Limiting for Store API endpoints
	 * Ref. https://developer.woocommerce.com/docs/apis/store-api/rate-limiting/#rate-limiting-options-filter
	 *
	 * @return array
	 */
	public function woocommerce_checkout(): array {
		return [
			'enabled' => true,
			'proxy_support' => true,
			'limit' => 20,
			'seconds' => 60,
		];
	}
}
