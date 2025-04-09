<?php
/**
 * Fast 404 response on static assets
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Performance;

use Eighteen73\Orbit\Singleton;
use Roots\WPConfig\Config;
use Roots\WPConfig\Exceptions\UndefinedConfigKeyException;

/**
 * Fast 404 response on static assets
 */
class Fast404 {
	use Singleton;

	private ?string $filename = null;
	private ?bool $enable_fast_404 = null;
	private $error_message = "404 (Not Found)";

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup(): void {
		$this->enable_fast_404 = $this->get_enable_fast_404();
		dump($this->enable_fast_404);
		if ( ! $this->enable_fast_404 ) {
			return;
		}

		add_action( $this->get_hook_point(), [ $this, 'serve_404' ], 99 );
	}

	/**
	 * Determine the earliest hook to attach the 404 handler to.
	 *
	 * @return string The hook name to use.
	 */
	private function get_hook_point(): string {
		return ! did_action( 'muplugins_loaded' ) ? 'muplugins_loaded' : 'plugins_loaded';
	}

	/**
	 * Serve a fast 404 response for invalid static asset requests.
	 *
	 * @return void
	 */
	public function serve_404(): void {
		$request_uri = $_SERVER['REQUEST_URI'] ?? '';
		if ( $request_uri === '' || str_ends_with( $request_uri, '/' ) ) {
			return;
		}

        $this->filename = wp_parse_url( $request_uri, PHP_URL_PATH );
		if ( ! $this->filename || $this->filename === '' || ltrim( $this->filename, '/' ) === 'favicon.ico' ) {
			return;
		}

        $req_ext = $this->get_request_extension();
        if ( ! $req_ext || ! in_array( $req_ext, $this->get_extensions(), true ) ) {
            return;
        }

        http_response_code( 404 );

        die( $this->error_message );
    }

	/**
	 * Extracts the file extension from the current request URI.
	 *
	 * @return string|false The matched file extension, or false if none found.
	 */
	private function get_request_extension(): string|false {
		if ( ! $this->filename || strpos( $this->filename, '.' ) === false ) {
			return false;
		}

		$mimes = wp_get_mime_types();
		unset( $mimes['swf'], $mimes['exe'], $mimes['msi'], $mimes['msp'], $mimes['msm'], $mimes['html'] );

		foreach ( $mimes as $ext_preg => $mime ) {
			if ( preg_match( '!\.(' . $ext_preg . ')$!i', $this->filename, $matches ) ) {
				return $matches[1];
			}
		}

		return false;
	}

	/**
	 * Returns a flattened list of file extensions considered for static 404 handling.
	 *
	 * @return array List of static file extensions eligible for fast 404s.
	 */
	static function get_extensions(): array {
		$wp_ext_types = wp_get_ext_types();
		$extensions   = [];

		foreach ( $wp_ext_types as $ext_group ) {
			$extensions = array_merge( $extensions, $ext_group );
		}

		unset( $extensions['html'], $extensions['htm'], $extensions['php'] );

		return $extensions;
	}

	/**
	 * Retrieves the `ORBIT_ENABLE_FAST_404` config value as a boolean.
	 *
	 * @return bool|null True to enable fast 404s, false to enable, or null if undefined.
	 */
	private function get_enable_fast_404(): ?bool {
		try {
			return filter_var( Config::get( 'ORBIT_ENABLE_FAST_404' ), FILTER_VALIDATE_BOOLEAN );
		} catch ( UndefinedConfigKeyException $e ) {
			return null;
		}
	}
}
