<?php
/**
 * An environment reader trait for wide installation support
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit;

/**
 * An environment reader trait for wide installation support
 */
trait Environment {

	/**
	 * Adds a fallback to wp_get_environment_type() so this plugin can be used
	 * on older websites that have Bedrock's WP_ENV instead
	 *
	 * @return array|false|string
	 */
	private function environment() {
		if ( defined( 'WP_ENV' ) ) {
			return getenv( 'WP_ENV' );
		}

		return wp_get_environment_type();
	}

	/**
	 * Determine if the current site is a non-live site (cloned, staging, dev, or non-production environment)
	 *
	 * @return bool
	 */
	public function is_nonlive_site(): bool {
		$env_override = getenv( 'ORBIT_IS_NONLIVE_SITE' );
		if ( false === $env_override ) {
			$env_override = getenv( 'ORBIT_IS_NONLIVE' );
		}
		if ( false === $env_override ) {
			$env_override = getenv( 'ORBIT_IS_DUPLICATE_SITE' );
		}
		if ( false === $env_override ) {
			$env_override = getenv( 'ORBIT_IS_DUPLICATE' );
		}
		if ( false !== $env_override ) {
			$val = strtolower( trim( (string) $env_override ) );
			if ( in_array( $val, [ '1', 'true', 'yes' ], true ) ) {
				return (bool) apply_filters( 'orbit_is_nonlive_site', true );
			}
			if ( in_array( $val, [ '0', 'false', 'no' ], true ) ) {
				return (bool) apply_filters( 'orbit_is_nonlive_site', false );
			}
		}

		$host = '';
		if ( isset( $_SERVER['HTTP_HOST'] ) && is_string( $_SERVER['HTTP_HOST'] ) ) {
			$host = strtolower( trim( $_SERVER['HTTP_HOST'] ) );
		} elseif ( function_exists( 'get_option' ) ) {
			$siteurl = get_option( 'siteurl' );
			if ( is_string( $siteurl ) && '' !== $siteurl ) {
				$parsed = wp_parse_url( $siteurl, PHP_URL_HOST );
				if ( is_string( $parsed ) ) {
					$host = strtolower( trim( $parsed ) );
				}
			}
		}

		if ( '' !== $host ) {
			if ( in_array( $host, [ '127.0.0.1', '::1', 'localhost' ], true ) ) {
				return (bool) apply_filters( 'orbit_is_nonlive_site', true );
			}

			$suffixes = [ '.test', '.local', '.localhost', '.dev' ];
			foreach ( $suffixes as $suffix ) {
				if ( str_ends_with( $host, $suffix ) ) {
					return (bool) apply_filters( 'orbit_is_nonlive_site', true );
				}
			}

			if ( str_starts_with( $host, 'beta.' ) || str_starts_with( $host, 'staging.' ) || str_ends_with( $host, '.kinsta.cloud' ) ) {
				return (bool) apply_filters( 'orbit_is_nonlive_site', true );
			}
		}

		if ( $this->environment() !== 'production' ) {
			return (bool) apply_filters( 'orbit_is_nonlive_site', true );
		}

		$primary_domain = getenv( 'ORBIT_PRIMARY_DOMAIN' );
		if ( ! $primary_domain && defined( 'ORBIT_PRIMARY_DOMAIN' ) ) {
			$primary_domain = ORBIT_PRIMARY_DOMAIN;
		}
		if ( ! $primary_domain ) {
			$primary_domain = getenv( 'ORBIT_PRODUCTION_DOMAIN' );
			if ( ! $primary_domain && defined( 'ORBIT_PRODUCTION_DOMAIN' ) ) {
				$primary_domain = ORBIT_PRODUCTION_DOMAIN;
			}
		}

		if ( ! empty( $primary_domain ) && '' !== $host && strtolower( (string) $primary_domain ) !== $host ) {
			return (bool) apply_filters( 'orbit_is_nonlive_site', true );
		}

		return (bool) apply_filters( 'orbit_is_nonlive_site', false );
	}
}
