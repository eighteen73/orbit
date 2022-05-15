<?php

namespace Eighteen73\Orbit\Forms;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Eighteen73\Orbit\Singleton;

/**
 * The options form for this plugin
 */
class Options extends Singleton {

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'after_setup_theme', [ $this, 'crb_load' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'carbon_fields_register_fields' ] );
	}

	/**
	 * Init the options form
	 *
	 * @return void
	 */
	public function crb_load() {
		define( 'Carbon_Fields\URL', home_url( '/vendor/htmlburger/carbon-fields' ) );
		Carbon_Fields::boot();
	}

	/**
	 * Define the options form fields
	 *
	 * @return void
	 */
	public function carbon_fields_register_fields() {
		Container::make( 'theme_options', __( 'Orbit Options' ) )
			->set_page_parent( 'options-general.php' )

			/*
			 * Tab: Security
			 */
			->add_tab(
				__( 'Security' ),
				[

					// Intro
					Field::make( 'html', 'orbit_security_intro', __( 'Section Description' ) )
						->set_html( '<p>We highy encourage all of these options to be left at the default value (checked) unless this website has very specific reason to re-enable a feature.</p>' ),
					Field::make( 'checkbox', 'orbit_security_api_users', __( 'Disable user endpoints in REST API' ) )
						->set_help_text( 'You should disable the user endpoints if not needed. This helps user privacy, hides usernames from hackers, and adds a layer of protection in case some other code opens up a vulnerability in user management.' )
						->set_default_value( true ),
					Field::make( 'checkbox', 'orbit_security_xmlrpc', __( 'Disable XML RPC' ) )
						->set_help_text( 'This outdated way of communicating with WordPress leaves websites open to brute force and DDoS attacks. If you <strong>must</strong> enable this, please try to limit it to necessary functioanlity and put request rate limiting in place.' )
						->set_default_value( true ),
					Field::make( 'checkbox', 'orbit_security_version', __( 'Hide WordPress version' ) )
						->set_help_text( 'This could act as an hint for hackers to target the website with known vulnerabilities.' )
						->set_default_value( true ),
				]
			)

			/*
			 * Tab: UI Cleanup
			 */
			->add_tab(
				__( 'UI Cleanup' ),
				[

					// Intro
					Field::make( 'html', 'orbit_ui_intro', __( 'Section Description' ) )
						->set_html( '<p>Orbit automatically removes a lot of UI elements that are rarely used and can confuse some CMS users. The items below are a few that can be toggled on/off as needed.</p><p>Note this doesn\'t disable functionality so do not rely on it as a security feature. It only removes menu links.</p>' ),

					// Menu items
					Field::make( 'separator', 'separator_menu', __( 'Menu items' ) ),
					Field::make( 'checkbox', 'orbit_ui_menu_dashboard', __( 'Show dashboard' ) )
						->set_default_value( true ),
					Field::make( 'checkbox', 'orbit_ui_menu_posts', __( 'Show posts' ) )
						->set_default_value( true ),
					Field::make( 'checkbox', 'orbit_ui_menu_pages', __( 'Show pages' ) )
						->set_default_value( true ),
					Field::make( 'checkbox', 'orbit_ui_menu_comments', __( 'Show comments' ) ),
					Field::make( 'checkbox', 'orbit_ui_menu_updates', __( 'Show updates' ) ),

					// Toolbar items
					Field::make( 'separator', 'separator_toolbar', __( 'Toolbar items' ) ),
					Field::make( 'checkbox', 'orbit_ui_toolbar_newcontent', __( 'Show new content button' ) ),

					// Login screen
					Field::make( 'separator', 'separator_login_logo', __( 'Login screen' ) ),
					Field::make( 'image', 'orbit_ui_login_logo', __( 'Login logo' ) ),
				]
			)

			/*
			 * Tab: Email Config
			 */
			->add_tab(
				__( 'Email Config' ),
				[
					Field::make( 'checkbox', 'orbit_email', __( 'Use Orbit SMTP settings' ) ),
					Field::make( 'rich_text', 'crb_content', 'Content' )
					->set_conditional_logic(
						[
							[
								'field' => 'orbit_email',
								'value' => true,
							],
						]
					),

				]
			);
	}
}
