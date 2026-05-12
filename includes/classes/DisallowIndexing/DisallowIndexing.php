<?php
/**
 * Disallow robot indexing in non-production environments
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\DisallowIndexing;

use Eighteen73\Orbit\Environment;
use Eighteen73\Orbit\Singleton;

/**
 * Disallow robot indexing in non-production environments
 */
class DisallowIndexing {
	use Environment;
	use Singleton;

	/**
	 * Integrate into WordPress
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'pre_option_blog_public', [ $this, 'disallow' ] );
		add_action( 'admin_notices', [ $this, 'show_notice' ] );
	}

	/**
	 * Determine whether it's enabled or not
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return $this->environment() !== 'production';
	}

	/**
	 * Return the relevant pre_option_blog_public value
	 *
	 * @return int
	 */
	public function disallow(): int {
		if ( ! $this->is_enabled() ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Display an admin message to warn when enabled
	 *
	 * @return void|null
	 */
	public function show_notice() {
		if ( ! $this->is_enabled() ) {
			return null;
		}

		$message = sprintf(
			/* translators: 1: Plugin name, 2: Current environment. */
			__( '%1$s Search engine indexing has been discouraged because the current environment is %2$s.', 'orbit' ),
			'<strong>Orbit:</strong>',
			'<code>' . esc_html( $this->environment() ) . '</code>'
		);

		echo wp_kses(
			"<div class='notice notice-warning'><p>{$message}</p></div>",
			[
				'div'    => [ 'class' => [] ],
				'p'      => [],
				'strong' => [],
				'code'   => [],
			]
		);
	}
}
