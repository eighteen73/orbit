<?php
/**
 * Orbit helper functions
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit;

/**
 * Get an Orbit setting.
 *
 * Handles retrieving settings saved by the new React interface.
 *
 * @param string      $option_key The base key of the option (e.g., 'disable_toolbar_items').
 * @param string|null $item_key   Optional. If the option is an array (like multichecks),
 *                                the specific item to check for within the array (e.g., 'wordpress_updates').
 * @param mixed       $default    The default value to return if the setting isn't found.
 * @return mixed The setting value, or boolean if item_key is specified, or the default value.
 */
function get_setting( string $option_key, ?string $item_key = null, $default = null ) {
	$option_name = 'orbit_' . $option_key; // Options are prefixed with 'orbit_'
	$value       = get_option( $option_name );

	// If the option doesn't exist in the database at all
	if ( $value === false ) {
		return $default;
	}

	// If we're checking for a specific item within an array setting
	if ( $item_key !== null ) {
		// Ensure the value is an array before checking
		if ( is_array( $value ) ) {
			return in_array( $item_key, $value, true );
		}
		// If it's not an array, we can't find the item key
		return false;
	}

	// Otherwise, return the entire option value
	return $value;
}
