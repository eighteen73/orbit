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

	const STORE_API_PER_MINUTE = 15;
	const CLASSIC_AJAX_PER_MINUTE = 5;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup(): void {

		// Force WooCommerce tracking to always be disabled.
		// This setting loads additional patterns from PTK.
		add_filter( 'option_woocommerce_allow_tracking', '__return_false' );

		/**
		 * The remaining filters and actions harden WooCommerce Store API and Classic Checkout AJAX.
		 * Including rate-limiting, nonce validation, same-origin checks, and session validation.
		 */

		if ( (bool) apply_filters( 'orbit_enable_woocommerce_checkout_hardening', true ) ) {

			// Store API: nonce + same-origin + rate limit
			add_filter( 'rest_pre_dispatch', [ $this, 'harden_store_api' ], 10, 3 );

			// Store API: native rate limiter (for good measure)
			add_filter( 'woocommerce_store_api_rate_limit_options', [ $this, 'native_store_api_rate_limit' ] );
			add_filter( 'woocommerce_store_api_rate_limit_id', [ $this, 'native_store_api_rate_limit_fingerprint' ] );

			// Classic checkout AJAX: nonce + same-origin + rate limit
			add_action( 'init', [ $this, 'harden_classic_checkout' ] );

			// Classic checkout AJAX: honeypot
			add_filter( 'woocommerce_checkout_fields', [ $this, 'classic_checkout_honeypot' ] );
			add_action( 'woocommerce_after_checkout_validation', [ $this, 'classic_checkout_honeypot_validation' ], 10, 2 );

		}
	}

	/*********************
	 * Actions & Filters
	 *********************/

	/**
	 * Enhances security for WooCommerce Store API requests by enforcing various validations and custom rate-limiting.
	 *
	 * @param mixed           $result The initial result of the API request filter.
	 * @param WP_REST_Server  $server The REST Server instance handling the request.
	 * @param WP_REST_Request $request The REST API request being processed.
	 *
	 * @return mixed The unchanged result if not blocked.
	 */
	public function harden_store_api( $result, $server, $request ) {
		$route  = $request->get_route();
		$method = $request->get_method();

		if ( $method === 'POST' && preg_match( '#^/wc/store(?:/v1)?/checkout$#', $route ) ) {

			// Per-IP rate limit
			if ( $this->rate_limited( 'store_checkout', self::STORE_API_PER_MINUTE, 60 ) ) {
				$this->block( 'rate_limited', 'Too many requests', 429 );
			}

			// Require Store API Nonce header (current name "Nonce"; older "X-WC-Store-API-Nonce")
			$nonce = ( $_SERVER['HTTP_NONCE'] ?? $_SERVER['HTTP_X_WC_STORE_API_NONCE'] ) ?? '';
			if ( empty( $nonce ) ) {
				$this->block( 'missing_nonce', 'Missing Store API nonce' );
			}

			// Same-origin referer
			if ( ! $this->same_origin() ) {
				$this->block( 'bad_referer', 'Invalid referer' );
			}

			// Woo session cookie present
			if ( ! $this->has_wc_session() ) {
				$this->block( 'no_session', 'Missing WooCommerce session' );
			}
		}

		return $result;
	}

	/**
	 * Sets rate limit settings for the native store API.
	 *
	 * @return array An associative array containing the rate limit settings, including:
	 *               - 'enabled' (bool): Indicates if rate limiting is enabled.
	 *               - 'proxy_support' (bool): Specifies if proxy support is enabled.
	 *               - 'limit' (int): The maximum number of requests allowed.
	 *               - 'seconds' (int): The time window in seconds for the rate limit.
	 */
	public function native_store_api_rate_limit() {
		return [
			'enabled'       => true,
			'proxy_support' => true,
			'limit'         => self::STORE_API_PER_MINUTE,
			'seconds'       => 60,
		];
	}

	/**
	 * Generates a reasonably unique fingerprint for the native API rate limiter.
	 *
	 * The fingerprint is created using the user's agent, the best-determined IP address,
	 * and the `HTTP_ACCEPT_LANGUAGE` header to uniquely identify a request.
	 *
	 * @return string A hashed string representing the unique fingerprint for rate limiting.
	 */
	public function native_store_api_rate_limit_fingerprint() {
		$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
		return md5( wc_get_user_agent() . '|' . $this->best_ip() . '|' . $lang );
	}

	/**
	 * Secures the WooCommerce classic checkout process by enforcing various validations and rate-limiting.
	 *
	 * @return void
	 */
	public function harden_classic_checkout() {
		if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		$action = $_GET['wc-ajax'] ?? '';
		if ( ! in_array( $action, [ 'checkout', 'update_order_review' ], true ) ) {
			return;
		}

		// Per-IP rate limit
		if ( $this->rate_limited( 'classic_' . $action, self::CLASSIC_AJAX_PER_MINUTE, 60 ) ) {
			$this->block( 'rate_limited', 'Too many requests', 429 );
		}

		// Same-origin referer
		if ( ! $this->same_origin() ) {
			$this->block( 'bad_referer', 'Invalid referer' );
		}

		// Woo session cookie present
		if ( ! $this->has_wc_session() ) {
			$this->block( 'no_session', 'Missing WooCommerce session' );
		}

		// Nonce checks:
		// checkout uses 'woocommerce-process_checkout' nonce in _wpnonce
		// update_order_review uses 'update-order-review' nonce in security
		if ( $action === 'checkout' ) {
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocommerce-process_checkout' ) ) {
				$this->block( 'bad_nonce', 'Invalid checkout nonce' );
			}
		} elseif ( $action === 'update_order_review' ) {
			$sec = $_REQUEST['security'] ?? '';
			if ( empty( $sec ) || ! wp_verify_nonce( $sec, 'update-order-review' ) ) {
				$this->block( 'bad_nonce', 'Invalid order review nonce' );
			}
		}
	}

	/**
	 * Adds a honeypot field to the checkout fields to detect and deter basic bots.
	 *
	 * @param array $fields The existing checkout fields.
	 *
	 * @return array The modified checkout fields with the added honeypot field.
	 */
	public function classic_checkout_honeypot( $fields ) {
		// Hidden field that humans will leave empty but basic bots may fill
		$fields['billing']['orbit_hp'] = [
			'type'     => 'text',
			'label'    => 'Leave blank',
			'required' => false,
			'class'    => [ 'form-row-wide' ],
			'priority' => 1,
		];
		return $fields;
	}

	/**
	 * Validates a honeypot field during the checkout process to detect potential fraudulent activity.
	 *
	 * @param array    $data The data submitted during the checkout process.
	 * @param WP_Error $errors An object to add validation errors if the honeypot check fails.
	 *
	 * @return void
	 */
	public function classic_checkout_honeypot_validation( $data, $errors ) {
		if ( ! empty( $data['orbit_hp'] ?? '' ) ) {
			error_log( sprintf( 'wc-carding honeypot ip=%s path=%s', $this->best_ip(), $_SERVER['REQUEST_URI'] ?? '' ) );
			$errors->add( 'honeypot', __( 'Validation error. Please try again.' ) );
		}
	}

	/*********************
	 * Helper methods
	 *********************/

	/**
	 * Determines the best possible client IP address by checking a prioritised list of server variables.
	 *
	 * @return string The client's IP address
	 */
	private function best_ip(): string {
		$candidates = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		];
		foreach ( $candidates as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$raw = explode( ',', $_SERVER[ $key ] )[0];
				return trim( $raw );
			}
		}
		return '0.0.0.0';
	}

	/**
	 * Checks if the HTTP referer is from the same origin as the site's host.
	 *
	 * @return bool True if the referer's host matches the site's host
	 */
	private function same_origin(): bool {
		$referer = wp_get_raw_referer();
		if ( ! $referer ) {
			return false;
		}
		$referer_host  = wp_parse_url( $referer, PHP_URL_HOST );
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		return $referer_host && $site_host && strtolower( $referer_host ) === strtolower( $site_host );
	}

	/**
	 * Checks if a WooCommerce session exists by searching for a specific cookie pattern.
	 *
	 * @return bool True if a WooCommerce session cookie is found
	 */
	private function has_wc_session(): bool {
		foreach ( $_COOKIE as $k => $v ) {
			if ( strpos( $k, 'wp_woocommerce_session_' ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Generates a unique rate limit key based on the provided scope, client IP, and user agent.
	 *
	 * @param string $scope The scope for which the rate limit key is being generated.
	 * @return string A unique, hashed string representing the rate limit key.
	 */
	private function rate_limit_key( string $scope ): string {
		return 'wc_rl_' . md5( $scope . '|' . $this->best_ip() . '|' . ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	}

	/**
	 * Determines whether a given operation is rate-limited based on the specified scope, limit, and time frame. Usage
	 * is tracked via transient storage.
	 *
	 * @param string $scope The scope used to identify the rate-limited operation.
	 * @param int    $limit The maximum allowed number of operations within the specified time frame.
	 * @param int    $seconds The duration, in seconds, for which the rate limit applies.
	 * @return bool True if the operation exceeds the specified limit within the time frame, otherwise false.
	 */
	private function rate_limited( string $scope, int $limit, int $seconds ): bool {
		$key   = $this->rate_limit_key( $scope );
		$count = (int) get_transient( $key );
		$count++;
		set_transient( $key, $count, $seconds );
		return $count > $limit;
	}

	/**
	 * Blocks a request by logging the details and sending a JSON error response.
	 *
	 * @param string $code The error code to identify the type of block.
	 * @param string $msg The error message to provide additional information.
	 * @param int    $status The HTTP status code for the response. Defaults to 403.
	 *
	 * @return void
	 */
	private function block( string $code, string $msg, int $status = 403 ) {
		$ip   = $this->best_ip();
		$path = $_SERVER['REQUEST_URI'] ?? '';
		error_log( sprintf( 'wc-carding block ip=%s code=%s path=%s ua="%s"', $ip, $code, $path, $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
		status_header( $status );
		wp_send_json_error(
			[
				'error' => $code,
				'message' => $msg,
			],
			$status
		);
	}
}
