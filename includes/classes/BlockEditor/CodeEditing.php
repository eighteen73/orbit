<?php
/**
 * Code editing in the block editor.
 *
 * Editing a block's HTML, or the whole post in the Code editor, is a
 * developer tool: a client following a "paste this code" guide can break a
 * section without seeing it. Core's `codeEditingEnabled` editor setting
 * removes both (the block menu's "Edit as HTML" and the Code editor), so it
 * is turned off for anyone who can't manage the site. Change who keeps it
 * with the `orbit_enable_code_editing` filter.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\BlockEditor;

use Eighteen73\Orbit\Singleton;

/**
 * Code editing in the block editor.
 */
class CodeEditing {

	use Singleton;

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public function setup(): void {
		add_filter( 'block_editor_settings_all', [ $this, 'filter_settings' ] );
	}

	/**
	 * Whether the current user can edit blocks as HTML.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		/**
		 * Filters whether the current user can edit blocks as HTML and use the
		 * Code editor.
		 *
		 * @param bool $enabled True for administrators (`manage_options`).
		 */
		return (bool) apply_filters( 'orbit_enable_code_editing', current_user_can( 'manage_options' ) );
	}

	/**
	 * Turn off code editing for users who can't manage the site.
	 *
	 * @param array $settings Block editor settings.
	 * @return array
	 */
	public function filter_settings( array $settings ): array {
		if ( ! $this->is_enabled() ) {
			$settings['codeEditingEnabled'] = false;
		}

		return $settings;
	}
}
