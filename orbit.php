<?php
/**
 * Plugin Name:     Orbit
 * Plugin URI:      https://code.orphans.co.uk/packages/wordpress/orbit
 * Description:     Opinionated WordPress behaviour overrides
 * Author:          Orphans Web Team
 * Author URI:      https://orphans.co.uk/websites
 * Text Domain:     orbit
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Orbit
 */

namespace Orbit;

spl_autoload_register(
	function( $class_name ) {
		$path_parts = explode( '\\', $class_name );

		if ( ! empty( $path_parts ) ) {
			$package = $path_parts[0];

			unset( $path_parts[0] );

			if ( 'Orbit' === $package ) {
				require_once __DIR__ . '/src/classes/' . implode( '/', $path_parts ) . '.php';
			}
		}
	}
);

Admin\Settings::instance()->setup();
Mail\Mail::instance()->setup();
