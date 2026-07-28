<?php
/**
 * Filterable SVG tag allowlist with Orbit's stricter defaults.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Media;

use Eighteen73\Orbit\Dependencies\enshrined\svgSanitize\data\AllowedTags;

/**
 * Allowed SVG tags for sanitization.
 */
class SvgTags extends AllowedTags {

	/**
	 * Tags removed from the library defaults unless re-added via filters.
	 *
	 * @var string[]
	 */
	public const DEFAULT_DISALLOWED = [
		'a',
		'style',
	];

	/**
	 * Returns an array of tags.
	 *
	 * @return array
	 */
	public static function getTags(): array {
		$tags = parent::getTags();

		$disallowed = array_map(
			'strtolower',
			(array) apply_filters( 'orbit_svg_disallowed_tags', self::DEFAULT_DISALLOWED )
		);

		$tags = array_values(
			array_filter(
				$tags,
				static function ( $tag ) use ( $disallowed ) {
					return ! in_array( strtolower( (string) $tag ), $disallowed, true );
				}
			)
		);

		return apply_filters( 'orbit_svg_allowed_tags', $tags );
	}
}
