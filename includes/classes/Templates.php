<?php
/**
 * Template functions for Orbit.
 *
 * @package         Orbit
 */

namespace Eighteen73\Orbit;

/**
 * This class is built upon BE Media from Production so all due credit to those authors.
 * http://www.github.com/billerickson/be-media-from-production
 */
class Templates {

	use Environment;
	use Singleton;

	/**
	 * Function to get the template file from the theme if it exists, otherwise from the plugin.
	 *
	 * @param string $template_name The name of the template file.
	 * @return string The path to the template file.
	 */
	private static function get_template_part( $template_name ) {
		$theme_template = get_stylesheet_directory() . '/template-parts/' . $template_name;

		if ( file_exists( $theme_template ) ) :
			return $theme_template;
		endif;

		return ORBIT_PATH . 'template-parts/' . $template_name;
	}

	/**
	 * Function to include the template file from the get_template_part function.
	 *
	 * @param string $template_name The name of the template file.
	 * @param array  $args          Optional. Associative array of variables to make available in the template.
	 */
	public static function include_template_part( string $template_name, array $args = [] ) {

		$template_path = self::get_template_part( $template_name );

		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}
}
