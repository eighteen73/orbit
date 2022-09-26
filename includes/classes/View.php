<?php
/**
 * Base class for creating custom settings views.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit;

/**
 * Settings view base class.
 *
 * @access public
 */
abstract class View {

	/**
	 * Name/ID for the group.
	 *
	 * @access protected
	 * @var    string
	 */
	public $name = '';

	/**
	 * Internationalized text label for the group.
	 *
	 * @access protected
	 * @var    string
	 */
	public $label = '';

	/**
	 * Priority (order) the control should be output.
	 *
	 * @access public
	 * @var    int
	 */
	public $priority = 10;

	/**
	 * A user role capability required to show the control.
	 *
	 * @access public
	 * @var    string|array
	 */
	public $capability = 'manage_options';

	/**
	 * Magic method to use in case someone tries to output the object as a string.
	 * We'll just return the name.
	 *
	 * @access public
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}

	/**
	 * Register a new object.
	 *
	 * @access public
	 * @param  string  $name
	 * @param  array   $args  {
	 *     @type string  $label        Internationalized text label.
	 *     @type string  $icon         Dashicon icon in the form of `dashicons-icon-name`.
	 *     @type string  $callback     Callback function for outputting the content for the view.
	 * }
	 * @return void
	 */
	public function __construct( $name, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) )
				$this->$key = $args[ $key ];
		}

		$this->name = sanitize_key( $name );
	}

	/**
	 * Runs on the `load-{$page}` hook
	 *
	 * @access public
	 * @return void
	 */
	public function load() {}

	/**
	 * Enqueue scripts/styles for the control.
	 *
	 * @access public
	 * @return void
	 */
	public function enqueue() {}

	/**
	 * Register settings for the view.
	 *
	 * @access public
	 * @return void
	 */
	public function register_settings() {}

	/**
	 * Add help tabs for the view.
	 *
	 * @access public
	 * @return void
	 */
	public function add_help_tabs() {}

	/**
	 * Output the content for the view.
	 *
	 * @access public
	 * @return void
	 */
	public function template() {}

	/**
	 * Checks if the control should be allowed at all.
	 *
	 * @access public
	 * @return bool
	 */
	public function check_capabilities() {

		if ( $this->capability && ! call_user_func_array( 'current_user_can', (array) $this->capability ) )
			return false;

		return true;
	}
}
