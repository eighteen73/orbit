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
		if ( defined( 'ORBIT_ERROR_REPORTING' ) ) {
			$error_level = ORBIT_ERROR_REPORTING;
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
				$error_level = $error_level & ~E_NOTICE & ~E_USER_NOTICE & ~E_WARNING & ~E_USER_WARNING & ~E_DEPRECATED;
			}
		}

		error_reporting( $error_level );
	}
}
