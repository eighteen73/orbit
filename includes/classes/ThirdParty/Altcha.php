<?php
/**
 * Modifications to Altcha.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\ThirdParty;

use Eighteen73\Orbit\Singleton;

/**
 * Modifications to Altcha.
 */
class Altcha {
	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup(): void {
		add_filter( 'altcha_challenge_url', [ $this, 'altcha_challenge_url' ] );
	}

	/**
	 * Override the Altcha challenge URL
	 * We want to ensure the url is never cached, so we add a query string to ensure it's always unique.
	 *
	 * @param string $url The Altcha challenge URL
	 * @return string
	 */
	public function altcha_challenge_url( string $url ): string {
		return $url . '?r=' . wp_rand( 1000, 9999 );
	}
}
