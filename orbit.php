<?php
/**
 * Plugin Name:     Orbit
 * Plugin URI:      https://code.orphans.co.uk/packages/wordpress/orbit
 * Description:     Opinionated WordPress behaviour overrides
 * Author:          Orphans Web Team
 * Author URI:      https://orphans.co.uk/websites
 * Text Domain:     orbit
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Orbit
 */

namespace Eighteen73\Orbit;

// Exit if accessed directly
use Eighteen73\Orbit\Forms\SimpleSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	function ( $class_name ) {
		$path_parts = explode( '\\', $class_name );

		if ( ! empty( $path_parts ) ) {
			$package = $path_parts[0];

			unset( $path_parts[0] );

			if ( 'Orbit' === $package ) {
				require_once __DIR__ . '/includes/classes/' . implode( '/', $path_parts ) . '.php';
			}
		}
	}
);

DisallowIndexing\DisallowIndexing::instance()->setup();

add_action(
	'init',
	function () {
		Mail\Mail::instance()->setup();
		Admin\CleanUI::instance()->setup();
		Admin\HideUpdates::instance()->setup();
		Security\DisableAPI::instance()->setup();
		Security\DisableXMLRPC::instance()->setup();
		Security\HideVersion::instance()->setup();
		Security\RemoveHeadLinks::instance()->setup();
	}
);

$settings = new SimpleSettings( 'orbit', 'Orbit' );

$section_ui = $settings->add_section(
	'UI Cleanup',
	[
		'Orbit automatically removes a lot of UI elements that are rarely used and can confuse some CMS users. The items below are a few that can be toggled on/off as needed.',
		'Note this doesn\'t disable functionality so do not rely on it as a security feature. It only removes menu links.',
	]
);
$settings->add_checkbox_group(
	$section_ui,
	'menu',
	'Menu Items',
	[
		'dashboard' => [
			'label' => 'Show dashboard',
		],
		'posts'     => [
			'label' => 'Show posts',
		],
		'pages'     => [
			'label' => 'Show pages',
		],
		'comments'  => [
			'label' => 'Show comments',
		],
		'updates'   => [
			'label' => 'Show updates',
		],
	]
);
$settings->add_checkbox_group(
	$section_ui,
	'toolbar',
	'Toolbar Items',
	[
		'new_content' => [
			'label' => 'Show new content button',
		],
	]
);

$section_security = $settings->add_section(
	'Security',
	'We highy encourage all of these options to be left at the default value (checked) unless this website has very specific reason to re-enable a feature.'
);
$settings->add_checkbox_group(
	$section_security,
	'security',
	'Features',
	[
		'api_users'  => [
			'label' => 'Disable user endpoints in REST API',
			'hint' => 'You should disable the user endpoints if not needed. This helps user privacy, hides usernames from hackers, and adds a layer of protection in case some other code opens up a vulnerability in user management.',
		],
		'xmlrpc' => [
			'label' => 'Disable XML RPC',
			'hint' => 'This outdated way of communicating with WordPress leaves websites open to brute force and DDoS attacks. If you must enable this, please try to limit it to necessary functioanlity and put request rate limiting in place.',
		],
		'version' => [
			'label' => 'Hide WordPress version',
			'hint' => 'This could act as an hint for hackers to target the website with known vulnerabilities.',
		],
	]
);

$section_login = $settings->add_section(
	'Login Screen',
	'Enter the URL of a company logo that you want to see on the CMS login screen.'
);
$settings->add_text_field(
	$section_login,
	'login_image',
	'Image URL',
);

$section_email = $settings->add_section(
	'Email Config',
	[
		'Orbit can override the website\'s mail settings to send emails via SMTP. In most cases this is preferable over using an different plugin but if you need another plugin to manage emails you can disable this feature entirely.',
		'Please note that if you are using our Nebula framework it will automatically override these (or any other) email settings in development environments to save emails from leaving your computer.'
	]
);
$settings->add_checkbox_group(
	$section_email,
	'mail',
	'Orbit SMTP',
	[
		'enabled' => [
			'label' => 'Use Orbit for email configuration',
		],
	]
);
$settings->add_text_field(
	$section_email,
	'from_name',
	'From name',
);
$settings->add_text_field(
	$section_email,
	'from_email',
	'From email',
);
$settings->add_text_field(
	$section_email,
	'host_address',
	'SMTP host',
);
$settings->add_text_field(
	$section_email,
	'host_port',
	'SMTP port',
);
$settings->add_radio_group(
	$section_email,
	'encryption',
	'Encryption',
	[
		'none'  => [
			'label' => 'None',
		],
		'ssl' => [
			'label' => 'SSL/TSL',
		],
		'starttls' => [
			'label' => 'STARTTLS',
		],
	]
);
$settings->add_checkbox_group(
	$section_email,
	'authentication',
	'Authentication',
	[
		'enabled' => [
			'label' => 'Requires SMTP authentication',
		],
	]
);
$settings->add_text_field(
	$section_email,
	'username',
	'SMTP username',
);
$settings->add_text_field(
	$section_email,
	'password',
	'SMTP password',
);

$settings->build();
