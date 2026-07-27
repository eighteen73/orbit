<?php
/**
 * Abstract class for third-party non-live site safeguards.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Support;

use Eighteen73\Orbit\Environment;

/**
 * Abstract base class for third-party plugin non-live site safeguards.
 */
abstract class AbstractNonliveSafeguard implements NonliveSafeguards {

	use Environment;

	/**
	 * Tracks if the non-live safeguard admin notice has been displayed.
	 *
	 * @var bool
	 */
	private static bool $notice_shown = false;

	/**
	 * Check if non-live site safeguards should run.
	 *
	 * @return bool
	 */
	public function is_safeguard_applicable(): bool {
		return $this->is_nonlive_site();
	}

	/**
	 * Run safeguard initialisation.
	 *
	 * @return void
	 */
	public function setup_safeguards(): void {
		if ( $this->is_safeguard_applicable() ) {
			$this->apply_safeguards();
			add_action( 'admin_notices', [ $this, 'show_nonlive_site_notice' ] );
		}
	}

	/**
	 * Display an admin warning notice when non-live site safeguards are active.
	 *
	 * @return void
	 */
	public function show_nonlive_site_notice(): void {
		if ( self::$notice_shown || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::$notice_shown = true;

		$message = sprintf(
			/* translators: 1: Plugin name */
			__( '%1$s Non-live site safeguards are active to prevent live transactions or external service calls on this instance.', 'orbit' ),
			'<strong>Orbit Safeguard:</strong>'
		);

		echo wp_kses(
			"<div class='notice notice-warning'><p>{$message}</p></div>",
			[
				'div'    => [ 'class' => [] ],
				'p'      => [],
				'strong' => [],
			]
		);
	}

	/**
	 * Register safeguard hooks and filters.
	 *
	 * @return void
	 */
	abstract public function apply_safeguards(): void;
}
