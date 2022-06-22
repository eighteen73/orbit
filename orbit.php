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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

DisallowIndexing\DisallowIndexing::instance()->setup();
Forms\Settings::instance()->setup();

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
