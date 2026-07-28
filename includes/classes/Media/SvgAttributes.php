<?php
/**
 * Filterable SVG attribute allowlist with Orbit's stricter defaults.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Media;

use Eighteen73\Orbit\Dependencies\enshrined\svgSanitize\data\AllowedAttributes;

/**
 * Allowed SVG attributes for sanitization.
 */
class SvgAttributes extends AllowedAttributes {

	/**
	 * Attributes removed from the library defaults unless re-added via filters.
	 *
	 * @var string[]
	 */
	public const DEFAULT_DISALLOWED = [
		'style',
	];

	/**
	 * Returns an array of attributes.
	 *
	 * @return array
	 */
	public static function getAttributes(): array {
		$attrs = parent::getAttributes();

		$disallowed = array_map(
			'strtolower',
			(array) apply_filters( 'orbit_svg_disallowed_attributes', self::DEFAULT_DISALLOWED )
		);

		$attrs = array_values(
			array_filter(
				$attrs,
				static function ( $attr ) use ( $disallowed ) {
					return ! in_array( strtolower( (string) $attr ), $disallowed, true );
				}
			)
		);

		return apply_filters( 'orbit_svg_allowed_attributes', $attrs );
	}
}
