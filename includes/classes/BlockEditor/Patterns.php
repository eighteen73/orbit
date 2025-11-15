<?php
/**
 * Modification to patterns.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\BlockEditor;

use Eighteen73\Orbit\Singleton;

/**
 * Patterns class.
 */
class Patterns {

	use Singleton;

	/**
	 * Primary constructor
	 *
	 * @return void
	 */
	public function setup(): void {
		if ( ! apply_filters( 'orbit_enable_disable_external_patterns', true ) ) {
			return;
		}

		add_action( 'init', [ $this, 'remove_woocommerce_patterns' ], 15 );
		add_filter( 'rest_dispatch_request', [ $this, 'filter_woocommerce_patterns_rest' ], 10, 4 );
	}

	/**
	 * Remove WooCommerce patterns early in the init process.
	 *
	 * @return void
	 */
	public function remove_woocommerce_patterns(): void {
		$patterns = $this->get_registered_patterns();

		if ( ! empty( $patterns ) ) {
			foreach ( $patterns as $pattern ) {
				if ( $this->is_woocommerce_pattern( $pattern ) ) {
					unregister_block_pattern( $pattern['name'] );
				}
			}
		}
	}

	/**
	 * Get all registered patterns, using cache if available.
	 *
	 * WordPress core does not provide a public global pattern cache:
	 * - WP_Block_Patterns_Registry has no internal caching (in-memory registry only)
	 * - WP_Theme::get_pattern_cache() is private and only caches theme patterns
	 * - WooCommerce BlockPatterns cache is plugin-specific and not publicly accessible
	 *
	 * Therefore, we use our own transient cache to avoid repeatedly calling
	 * get_all_registered() which, while lightweight, is still unnecessary overhead.
	 *
	 * @return array Array of registered patterns.
	 */
	private function get_registered_patterns(): array {
		$cache_key = 'orbit_registered_patterns';
		$patterns  = get_transient( $cache_key );

		if ( false === $patterns ) {
			$patterns = \WP_Block_Patterns_Registry::get_instance()->get_all_registered();
			set_transient( $cache_key, $patterns, 300 );
		}

		return $patterns;
	}

	/**
	 * Filter WooCommerce patterns from REST API responses.
	 *
	 * @param mixed            $dispatch_result Dispatch result, will be used if not empty.
	 * @param \WP_REST_Request $request         Request used to generate the response.
	 * @param string           $route           Route matched for the request.
	 * @param array            $handler         Route handler used for the request.
	 * @return mixed
	 */
	public function filter_woocommerce_patterns_rest( $dispatch_result, $request, $route, $handler ): mixed {
		// Check if this is a block patterns request
		if ( strpos( $route, '/wp/v2/block-patterns/patterns' ) !== 0 ) {
			return $dispatch_result;
		}

		// If we already have a result, filter it
		if ( ! empty( $dispatch_result ) && is_array( $dispatch_result ) ) {
			return $this->filter_patterns_from_response( $dispatch_result );
		}

		return $dispatch_result;
	}

	/**
	 * Check if a pattern is a WooCommerce pattern.
	 *
	 * @param array $pattern The pattern data.
	 * @return bool
	 */
	private function is_woocommerce_pattern( array $pattern ): bool {
		// Check by category
		if ( ! empty( $pattern['categories'] ) && in_array( 'woo-commerce', $pattern['categories'], true ) ) {
			return true;
		}

		// Check by pattern name/slug
		$woo_prefixes = [
			'woocommerce-blocks/',
			'woocommerce/',
			'woo/',
			'wc-',
		];

		foreach ( $woo_prefixes as $prefix ) {
			if ( strpos( $pattern['name'], $prefix ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Filter WooCommerce patterns from a REST response.
	 *
	 * @param array $response The response data.
	 * @return array
	 */
	private function filter_patterns_from_response( array $response ): array {
		if ( isset( $response['data'] ) && is_array( $response['data'] ) ) {
			$response['data'] = array_filter(
				$response['data'],
				function ( $pattern ) {
					return ! $this->is_woocommerce_pattern( $pattern );
				}
			);

			// Re-index the array to maintain proper JSON structure
			$response['data'] = array_values( $response['data'] );
		}

		return $response;
	}
}
