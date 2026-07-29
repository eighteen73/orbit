<?php
/**
 * Interface for third-party plugin non-live site safeguards.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Support;

/**
 * Contract for applying safeguards to third-party plugins on non-live sites.
 */
interface NonliveSafeguards {

	/**
	 * Check if non-live site safeguards should run.
	 *
	 * @return bool
	 */
	public function is_safeguard_applicable(): bool;


	/**
	 * Apply safeguards when the site is identified as a non-live site.
	 *
	 * @return void
	 */
	public function apply_safeguards(): void;

	/**
	 * Run safeguard initialisation.
	 *
	 * @return void
	 */
	public function setup_safeguards(): void;
}
