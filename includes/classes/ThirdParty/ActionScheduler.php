<?php
/**
 * Modifications to ActionScheduler.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\ThirdParty;

use Eighteen73\Orbit\Singleton;

/**
 * Modifications to Altcha.
 */
class ActionScheduler {
	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		add_filter( 'action_scheduler_retention_period', [ $this, 'set_action_scheduler_retention_period' ] );
		add_filter( 'action_scheduler_default_cleaner_statuses', [ $this, 'set_action_scheduler_default_cleaner_statuses' ] );
		add_filter( 'action_scheduler_cleanup_batch_size', [ $this, 'set_action_scheduler_cleanup_batch_size' ] );
	}

	/**
	 * Set the retention period for completed actions.
	 *
	 * @param int $period Current retention period in seconds.
	 * @return int Modified retention period in seconds.
	 */
	public function set_action_scheduler_retention_period( int $period ): int {
		return apply_filters( 'orbit_action_scheduler_retention_period', WEEK_IN_SECONDS * 2 );
	}

	/**
	 * Set the default statuses cleaned up by Action Scheduler.
	 *
	 * @param array<int, string> $statuses List of statuses to be cleaned up.
	 * @return array<int, string> Modified list of statuses.
	 */
	public function set_action_scheduler_default_cleaner_statuses( array $statuses ): array {
		return apply_filters( 'orbit_action_scheduler_default_cleaner_statuses', [ 'complete', 'canceled', 'failed' ] );
	}

	/**
	 * Set the cleanup batch size for Action Scheduler.
	 *
	 * @param int $size Number of actions to process per cleanup batch.
	 * @return int Modified batch size.
	 */
	public function set_action_scheduler_cleanup_batch_size( int $size ): int {
		return apply_filters( 'orbit_action_scheduler_cleanup_batch_size', 1000 );
	}
}
