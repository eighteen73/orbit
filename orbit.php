<?php
/**
 * Plugin Name:     Orbit
 * Plugin URI:      https://github.com/eighteen73/orbit
 * Description:     Opinionated WordPress behaviour overrides
 * Author:          eighteen73
 * Author URI:      https://eighteen73.co.uk
 * Text Domain:     orbit
 * Domain Path:     /languages
 * Version:         2.3.4
 * Update URI:      https://github.com/eighteen73/orbit
 *
 * @package         Orbit
 */

namespace Eighteen73\Orbit;

defined( 'ABSPATH' ) || exit;

// Useful global constants.
define( 'ORBIT_URL', plugin_dir_url( __FILE__ ) );
define( 'ORBIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ORBIT_INC', ORBIT_PATH . 'includes/' );

require_once 'autoload.php';

// Initialise the plugin.
Plugin::instance()->setup();
