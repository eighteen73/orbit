<?php
/**
 * Disallow robot indexing in non-production environments
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\DisallowIndexing;

use Eighteen73\Orbit\Singleton;

/**
 * Disallow robot indexing in non-production environments
 */
class DisallowIndexing extends Singleton {

	/**
	 * Whether this feature is enabled or not
	 *
	 * @var bool
	 */
	private bool $enabled;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->enabled = $this->is_enabled();
	}

	/**
	 * Integrate into WordPress
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'pre_option_blog_public', [ $this, 'disallow' ] );
		add_action( 'admin_notices', [ $this, 'showNotice' ] );
	}

	/**
	 * Determine whether it's enabled or not
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return wp_get_environment_type() !== 'production';
	}

	/**
	 * Return the relevant pre_option_blog_public value
	 *
	 * @return int
	 */
	public function disallow(): int {
		if ( ! $this->enabled ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Display an admin message to warn when enabled
	 *
	 * @return void|null
	 */
	public function showNotice() {
		if ( ! $this->enabled ) {
			return null;
		}

		$message = sprintf(
			/* translators: 1: Plugin name, 2: Current environment. */
			__( '%1$s Search engine indexing has been discouraged because the current environment is %2$s.', 'orbit' ),
			'<strong>Orbit:</strong>',
			'<code>' . wp_get_environment_type() . '</code>'
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
