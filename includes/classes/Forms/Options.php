<?php
/**
 * The options form for this plugin
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Forms;

use Eighteen73\Orbit\Singleton;
use Eighteen73\SettingsApi\SettingsApi;

/**
 * The options form for this plugin
 */
class Options {
	use Singleton;

	/**
	 * Create the settings form
	 *
	 * @return void
	 */
	public function setup(): void {

		$settings = new SettingsApi(
			'Orbit',
			'Orbit',
			'manage_options',
			'orbit'
		);

		// Section: UI.
		$settings->add_section(
			[
				'id'    => 'orbit_ui',
				'title' => __( 'UI Settings', 'orbit' ),
			]
		);

		// Section: Security.
		$settings->add_section(
			[
				'id'    => 'orbit_security',
				'title' => __( 'Security Settings', 'orbit' ),
			]
		);

		// Field: Disable menu items.
		$settings->add_field(
			'orbit_ui',
			[
				'id'      => 'disable_menu_items',
				'type'    => 'multicheck',
				'name'    => __( 'Disable menu items', 'orbit' ),
				'desc'    => __( 'Disable menu items', 'orbit' ),
				'options' => [
					'dashboard' => 'Dashboard',
					'posts'     => 'Posts',
					'comments'  => 'Comments',
				],
			],
		);

		// Field: Disable menu items.
		$settings->add_field(
			'orbit_ui',
			[
				'id'      => 'disable_toolbar_items',
				'type'    => 'multicheck',
				'name'    => __( 'Disable toolbar items', 'orbit' ),
				'desc'    => __( 'Disable toolbar items', 'orbit' ),
				'options' => [
					'new_content'       => 'New content',
					'wordpress_updates' => 'WordPress updates',
					'comments'          => 'Comments',
				],
			],
		);

		// Field: Login logo.
		$settings->add_field(
			'orbit_ui',
			[
				'id'      => 'login_logo',
				'type'    => 'image',
				'name'    => __( 'Login logo', 'orbit' ),
				'desc'    => __( 'Sets a logo for the WordPress login screen.', 'orbit' ),
				'options' => [
					'button_label' => __( 'Choose Image', 'orbit' ),
				],
			],
		);
	}
}
