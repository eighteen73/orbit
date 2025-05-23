<?php
/**
 * Removes WordPress' version number from various places in the markup
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;

/**
 * Removes WordPress' version number from various places in the markup
 */
class HideVersion {
	use Singleton;

	const VERSIONED_URL_REGEX = '/(.+\?(?:ver|version)=)([^&]+)(.*)/';

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		if ( apply_filters( 'orbit_enable_expose_wordpress_version', false ) ) {
			return;
		}
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
		add_filter( 'style_loader_src', [ $this, 'obfuscate_script_or_style_version' ], 20000 );
		add_filter( 'script_loader_src', [ $this, 'obfuscate_script_or_style_version' ], 20000 );
	}

	/**
	 * Converts the WordPress version number into a hash
	 *
	 * @param string $target_url The script or stylesheet URL
	 * @return string
	 */
	public function obfuscate_script_or_style_version( string $target_url ): string {
		if ( ! preg_match( self::VERSIONED_URL_REGEX, $target_url, $matches ) ) {
			return $target_url;
		}

		$new_version = substr( md5( $matches[2] ), 0, 6 );

		return preg_replace( self::VERSIONED_URL_REGEX, '${1}' . $new_version . '${3}', $target_url );
	}
}
