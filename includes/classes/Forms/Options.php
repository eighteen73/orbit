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
			'orbit',
			100,
		);

		// Section: UI.
		$settings->add_section(
			[
				'id'    => 'orbit_ui',
				'title' => __( 'UI Settings', 'orbit' ),
				'desc'  => __( 'Orbit automatically removes a lot of UI elements that are rarely used and can confuse some CMS users. The items below are a few that can be toggled on/off as needed.<br><br>Note: this doesn\'t disable functionality so do not rely on it as a security feature; it only removes menu links for cleanliness.', 'orbit' ),
			]
		);

		// Section: Security.
		$settings->add_section(
			[
				'id'    => 'orbit_security',
				'title' => __( 'Security Settings', 'orbit' ),
				'desc'  => __( 'We highy encourage all of these options to be left at the default value (unchecked) unless this website has very specific reason to re-enable a feature.', 'orbit' ),
			]
		);

		// Field: Disable menu items.
		$settings->add_field(
			'orbit_ui',
			[
				'id'      => 'disable_menu_items',
				'type'    => 'multicheck',
				'name'    => __( 'Disable menu items', 'orbit' ),
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

		// Field: General security items.
		$settings->add_field(
			'orbit_security',
			[
				'id'      => 'general',
				'type'    => 'multicheck',
				'name'    => __( 'General', 'orbit' ),
				'options' => [
					'expose_wordpress_version' => [
						'label' => __( 'Display the WordPress version', 'orbit' ),
						'desc'  => __( 'This could act as an hint for hackers to target a website with known vulnerabilities.', 'orbit' ),
					],
					'enable_xmlrpc'            => [
						'label' => __( 'Enable XML-RPC', 'orbit' ),
						'desc'  => __( 'This outdated way of communicating with WordPress leaves websites open to brute force and DDoS attacks.<br>If you must enable this, please try to limit it to necessary functionality and put request rate limiting in place elsewhere.', 'orbit' ),
					],
				],
			],
		);

		// Field: General security items.
		$settings->add_field(
			'orbit_security',
			[
				'id'      => 'rest_api',
				'type'    => 'multicheck',
				'name'    => __( 'REST API', 'orbit' ),
				'options' => [
					'enable_user_endpoints' => [
						'label' => __( 'Enable user endpoints in the REST API', 'orbit' ),
						'desc'  => __( 'You should allow Orbit to disable the user endpoints if not needed.<br>This helps user privacy, hides usernames from hackers, and adds a layer of protection in case some other code opens up a vulnerability in user management.', 'orbit' ),
					],
				],
			],
		);
	}

	/**
	 * This supports opttion that are nested inside an array of options. They can be accessed
	 * via dot notation. E.g. Options::get_option('appearance.colours.primary')
	 *
	 * @param string $name The option name
	 * @param mixed  $default The default value if the the option doesn't exist
	 * @return mixed
	 */
	public static function get_option( string $name, $default = null ) {
		$option_path = explode( '.', $name );
		$option      = get_option( $option_path[0], null );

		// No such option
		if ( $option === null ) {
			return $default;
		}

		$num_options = count( $option_path );
		for ( $i = 1; $i < $num_options; $i++ ) {

			// No such sub-option
			if ( ! isset( $option[ $option_path[ $i ] ] ) ) {
				return $default;
			}

			$option = $option[ $option_path[ $i ] ];
		}

		return $option;
	}
}
