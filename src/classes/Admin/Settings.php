<?php

namespace Orbit\Admin;

use Orbit\Singleton;

class Settings extends Singleton {

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Register admin pages with output callbacks
	 */
	public function register_admin_pages() {
		add_options_page(
			'Orbit Settings',
			'Orbit Settings',
			'manage_options',
			'orbit-settings',
			[ $this, 'settings_page' ]
		);
	}

	/**
	 * Output options screens
	 */
	public function settings_page() {
		?>
		<h1>Orbit Settings</h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'orbit_security_settings' );
			do_settings_sections( 'orbit_settings_page' );
			?>
			<input type="submit" name="submit" class="button button-primary" value="Save" />
		</form>
		<?php
	}

	public function register_settings() {
		register_setting(
			'orbit_security_settings',
			'orbit_security_settings',
			[ $this, 'validate_orbit_security_settings' ]
		);
		add_settings_section(
			'security_section',
			'Security Settings',
			[ $this, 'setting_security_intro' ],
			'orbit_settings_page'
		);
		add_settings_field(
			'orbit_security_xmlrpc',
			'Disable XML RPC',
			[ $this, 'setting_security_xmlrpc' ],
			'orbit_settings_page',
			'security_section'
		);
		add_settings_field(
			'orbit_security_userapi',
			'Disable users API endpoints',
			[ $this, 'setting_security_userapi' ],
			'orbit_settings_page',
			'security_section'
		);
	}

	public function validate_orbit_security_settings( $input ) {
		$output['xmlrpc']  = isset( $input['xmlrpc'] ) && $input['xmlrpc'] === '1';
		$output['userapi'] = isset( $input['userapi'] ) && $input['userapi'] === '1';
		return $output;
	}

	public function setting_security_intro() {
		echo '<p>These settings are recommended to be enabled for security but there may be occasions when some need to be switched off to enable some website requirements.</p>';
	}

	public function setting_security_xmlrpc() {
		$options = get_option( 'orbit_security_settings' );
		printf(
			'<input type="checkbox" name="%s" value="1" %s>',
			esc_attr( 'orbit_security_settings[xmlrpc]' ),
			esc_attr( isset( $options['xmlrpc'] ) && $options['xmlrpc'] ? 'checked' : '' )
		);
	}

	public function setting_security_userapi() {
		$options = get_option( 'orbit_security_settings' );
		printf(
			'<input type="checkbox" name="%s" value="1" %s>',
			esc_attr( 'orbit_security_settings[userapi]' ),
			esc_attr( isset( $options['userapi'] ) && $options['userapi'] ? 'checked' : '' )
		);
	}
}
