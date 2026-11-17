<?php
/**
 * The block directory in the block editor's inserter.
 *
 * When a search finds no block, the inserter offers plugins to install from
 * WordPress.org ("Available to install") and, with none, a link for
 * developers ("Interested in creating your own block?"). Sites built on
 * Orbit don't add blocks that way, and for a client both are dead ends, so
 * the directory is off. Turn it back on with the `orbit_enable_block_directory`
 * filter.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\BlockEditor;

use Eighteen73\Orbit\Singleton;

/**
 * The block directory in the block editor's inserter.
 */
class BlockDirectory {

	use Singleton;

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'init', [ $this, 'disable' ] );
	}

	/**
	 * Remove the directory's editor assets, unless the filter keeps them.
	 *
	 * @return void
	 */
	public function disable(): void {
		/**
		 * Filters whether the block editor's inserter offers blocks to install
		 * from the block directory.
		 *
		 * @param bool $enabled False.
		 */
		if ( apply_filters( 'orbit_enable_block_directory', false ) ) {
			return;
		}

		remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
	}
}
