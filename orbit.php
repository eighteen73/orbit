<?php
/**
 * Plugin Name:     Orbit
 * Plugin URI:      https://github.com/eighteen73/orbit
 * Description:     Opinionated WordPress behaviour overrides
 * Author:          Orphans Web Team
 * Author URI:      https://orphans.co.uk/websites
 * Text Domain:     orbit
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Orbit
 */

namespace Eighteen73\Orbit;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Useful global constants.
define( 'ORBIT_URL', plugin_dir_url( __FILE__ ) );
define( 'ORBIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ORBIT_INC', ORBIT_PATH . 'includes/' );

require_once ORBIT_PATH . 'vendor/autoload.php';

Forms\Options::instance()->setup();
DisallowIndexing\DisallowIndexing::instance()->setup();

add_action(
	'init',
	function () {
		Admin\CleanUI::instance()->setup();
		Admin\HideUpdates::instance()->setup();
		Security\DisableAPI::instance()->setup();
		Security\DisableXMLRPC::instance()->setup();
		Security\HideVersion::instance()->setup();
		Security\RemoveHeadLinks::instance()->setup();
		OtherFilters::instance()->setup();
	}
);
