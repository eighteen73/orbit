<?php
/**
 * Overrides WordPress' default error_reporting because that's too verbose for non-development environments.
 *
 * @package         Orbit
 */

namespace Eighteen73\Orbit\Utilities;

use Eighteen73\Orbit\Environment;
use Eighteen73\Orbit\Singleton;

/**
 * Overrides WordPress' default error_reporting
 */
class ErrorReporting {

	use Environment;
	use Singleton;

	/**
	 * Holds the previous error handler.
	 *
	 * @var callable|null
	 */
	private $previous_handler = null;

	/**
	 * Setup module
	 *
	 * @return void
	 */
	public function setup(): void {

		if ( defined( 'ORBIT_ERROR_REPORTING' ) && ORBIT_ERROR_REPORTING === false ) {
			return;
		}

		add_action( 'muplugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Initialising is done early enough that other plugins can override it when needed.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( defined( 'ORBIT_ERROR_REPORTING' ) && ! empty( ORBIT_ERROR_REPORTING ) ) {
			$error_level = (int) ORBIT_ERROR_REPORTING;
		} else {
			$error_level = E_ERROR
						   + E_WARNING
						   + E_NOTICE
						   + E_PARSE
						   + E_DEPRECATED
						   + E_CORE_ERROR
						   + E_CORE_WARNING
						   + E_COMPILE_ERROR
						   + E_USER_ERROR
						   + E_USER_WARNING
						   + E_USER_NOTICE
						   + E_RECOVERABLE_ERROR;
			if ( $this->environment() !== 'development' ) {
				$error_level = $error_level & ~E_NOTICE & ~E_USER_NOTICE & ~E_WARNING & ~E_USER_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED;
			}
		}

		error_reporting( $error_level );

		// Register the custom deprecation/warning filter on production environments.
		if ( $this->environment() === 'production' ) {
			$this->register_error_handler();

			// Re-register at various stages of WP lifecycle to jump back ahead of plugins that mess with handlers.
			add_action( 'plugins_loaded', [ $this, 'register_error_handler' ], 9999 );
			add_action( 'setup_theme', [ $this, 'register_error_handler' ], 9999 );
			add_action( 'after_setup_theme', [ $this, 'register_error_handler' ], 9999 );
			add_action( 'init', [ $this, 'register_error_handler' ], 9999 );
			add_action( 'wp_loaded', [ $this, 'register_error_handler' ], 9999 );
		}
	}

	/**
	 * Register our error handler, ensuring it doesn't cause loops and chains correctly.
	 *
	 * @return void
	 */
	public function register_error_handler(): void {
		// Safely check the currently active handler without permanently altering the handler stack.
		$current_handler = set_error_handler( function () {} );
		restore_error_handler();

		// If the active handler is already this instance, do nothing.
		if ( is_array( $current_handler ) && $current_handler[0] === $this ) {
			return;
		}

		// Register this instance's handler and store the previous handler.
		$this->previous_handler = set_error_handler( [ $this, 'handle_error' ] );
	}

	/**
	 * The error handler callback. Intercepts and silences warnings, notices, and deprecations.
	 *
	 * @param int    $errno   The level of the error raised.
	 * @param string $errstr  The error message.
	 * @param string $errfile The filename that the error was raised in.
	 * @param int    $errline The line number the error was raised in.
	 *
	 * @return bool
	 */
	public function handle_error( int $errno, string $errstr, string $errfile, int $errline ): bool {
		// If the error was silenced using the @ operator, let PHP's default handler or previous handler deal with it.
		if ( ! ( error_reporting() & $errno ) ) {
			if ( $this->previous_handler && is_callable( $this->previous_handler ) ) {
				return (bool) call_user_func( $this->previous_handler, $errno, $errstr, $errfile, $errline );
			}
			return false;
		}

		// Silence warnings, notices, deprecations, and strict standards by default.
		$silenced_errors = E_STRICT;

		if ( ! defined( 'ORBIT_LOG_WARNING' ) || ! ORBIT_LOG_WARNING ) {
			$silenced_errors |= E_WARNING | E_USER_WARNING;
		}

		if ( ! defined( 'ORBIT_LOG_NOTICE' ) || ! ORBIT_LOG_NOTICE ) {
			$silenced_errors |= E_NOTICE | E_USER_NOTICE;
		}

		if ( ! defined( 'ORBIT_LOG_DEPRECATED' ) || ! ORBIT_LOG_DEPRECATED ) {
			$silenced_errors |= E_DEPRECATED | E_USER_DEPRECATED;
		}

		if ( $errno & $silenced_errors ) {
			return true;
		}

		// For all other errors, forward to the previous handler if it exists.
		if ( $this->previous_handler && is_callable( $this->previous_handler ) ) {
			return (bool) call_user_func( $this->previous_handler, $errno, $errstr, $errfile, $errline );
		}

		// Fallback to PHP's standard error logger.
		return false;
	}
}
