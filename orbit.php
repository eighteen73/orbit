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

use Carbon_Fields\Carbon_Fields;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

add_action(
	'after_setup_theme',
	function() {
		if ( ! defined( 'Carbon_Fields\URL' ) ) {
			define( 'Carbon_Fields\URL', home_url( '/app/mu-plugins/orbit/vendor/htmlburger/carbon-fields' ) );
			Carbon_Fields::boot();
		}
	}
);

Forms\Options::instance()->setup();
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
		OtherFilters::instance()->setup();
	}
);
