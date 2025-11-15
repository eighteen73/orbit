<?php
/**
 * Add branding email templates to site emails.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Branding;

use Exception;
use Eighteen73\Orbit\Singleton;
use Eighteen73\Orbit\Environment;
use Eighteen73\Orbit\Utilities\Templates;
use Eighteen73\Orbit\Dependencies\Pelago\Emogrifier\CssInliner;

/**
 * Branded Emails class.
 */
class BrandedEmails {

	use Environment;
	use Singleton;

	/**
	 * Setup module
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize module
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ! apply_filters( 'orbit_enable_branded_emails', true ) ) {
			return;
		}

		add_filter( 'wp_mail', [ $this, 'apply_branded_email_template' ] );
		add_filter( 'gform_html_message_template_pre_send_email', [ $this, 'apply_branded_email_template_to_gf_notifications' ] );
		add_filter( 'gform_email_background_color_label', [ $this, 'apply_branded_email_colours_to_gf_table_labels' ], 10, 3 );
		add_filter( 'gform_email_background_color_data', [ $this, 'apply_branded_email_colours_to_gf_table_data' ], 10, 3 );
	}

	/**
	 * Wraps the original email message in a branded HTML email template.
	 *
	 * @param array $args Array of arguments passed to wp_mail().
	 * @return array Modified $args array with the email message wrapped in branded HTML.
	 */
	public function apply_branded_email_template( $args ): array {
		$headers = [];

		// Convert headers to an array if they aren't already
		if ( empty( $args['headers'] ) ) {
			$headers = [];
		} elseif ( is_string( $args['headers'] ) ) {
			$headers = explode( "\n", str_replace( "\r\n", "\n", $args['headers'] ) );
		} elseif ( is_array( $args['headers'] ) ) {
			$headers = $args['headers'];
		}

		// Check if Content-Type is already set to HTML
		$content_type_is_html = false;
		foreach ( $headers as $header ) {
			if ( stripos( $header, 'Content-Type:' ) !== false && stripos( $header, 'text/html' ) !== false ) {
				$content_type_is_html = true;
				break;
			}
		}

		// If email is already HTML return original $args
		if ( $content_type_is_html ) {
			return $args;
		}

		// Remove existing Content-Type headers (at this point we've already established it's not HTML)
		$headers = array_filter(
			$headers,
			function ( $header ) {
				return stripos( $header, 'Content-Type:' ) === false;
			}
		);

		// Add HTML Content-Type
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		ob_start();

		Templates::include_template(
			'branded-emails/email-template.php',
			[
				'email_content' => $args['message'],
				'email_subject' => isset( $args['subject'] ) ? $args['subject'] : '',
			]
		);

		$raw_email_content = ob_get_clean();

		$email_content = $this->style_inline( $raw_email_content );

		// Set updated headers and message
		$args['headers'] = $headers;
		$args['message'] = $email_content;

		return $args;
	}

	/**
	 * Provides a branded HTML template for Gravity Forms email notifications.
	 *
	 * `{message}` and `{subject}` placeholders will be replaced by
	 * Gravity Forms with the actual notification content.
	 *
	 * @param string $template The original email template string (usually empty).
	 * @return string The modified email template with placeholders for message and subject.
	 */
	public function apply_branded_email_template_to_gf_notifications( $template ): string {
		ob_start();

		Templates::include_template(
			'branded-emails/email-template.php',
			[
				'email_content' => '{message}',
				'email_subject' => '{subject}',
			]
		);

		$raw_email_content = ob_get_clean();

		$email_content = $this->style_inline( $raw_email_content );

		return $email_content;
	}

	/**
	 * Apply inline styles to email content.
	 *
	 * @param string|null $email_content Content that will receive inline styles.
	 * @return string The email content with inline styles applied.
	 */
	public function style_inline( $email_content ): string {
		try {
			$inlined_content = CssInliner::fromHtml( $email_content )->inlineCss()->render();
		} catch ( Exception $e ) {
			$inlined_content = $email_content;
		}

		return $inlined_content;
	}

	/**
	 * Retrieves a set of resolved and filtered CSS variables used for branded email templates.
	 *
	 * @return array Array of CSS variables for use in the email template.
	 */
	public static function get_css_variables(): array {
		return [
			'background_color' => apply_filters(
				'orbit_branded_emails_background_color',
				self::get_woocommerce_setting( 'woocommerce_email_background_color' ) ?:
				'#ffffff'
			),

			'body_background_color' => apply_filters(
				'orbit_branded_emails_body_background_color',
				self::get_woocommerce_setting( 'woocommerce_email_body_background_color' ) ?:
				'#ffffff'
			),

			'body_border_color' => apply_filters(
				'orbit_branded_emails_body_border_color',
				self::orbit_branded_emails_resolve_color(
					'var(--wp--custom--color--border)'
				) ?:
				'#edeff1'
			),

			'body_text_color' => apply_filters(
				'orbit_branded_emails_body_text_color',
				self::get_woocommerce_setting( 'woocommerce_email_text_color' ) ?:
				self::orbit_branded_emails_resolve_color(
					'var(--wp--preset--color--contrast)'
				) ?:
				'#3f474d'
			),

			'link_color' => apply_filters(
				'orbit_branded_emails_link_color',
				self::get_woocommerce_setting( 'woocommerce_email_base_color' ) ?:
				self::orbit_branded_emails_resolve_color(
					'var(--wp--custom--color--link)'
				) ?:
				'#8549ff'
			),

			'footer_text_color' => apply_filters(
				'orbit_branded_emails_footer_text_color',
				self::get_woocommerce_setting( 'woocommerce_email_footer_text_color' ) ?:
				self::orbit_branded_emails_resolve_color(
					'var(--wp--preset--color--contrast)'
				) ?:
				'#3F474d'
			),

			'font_family' => apply_filters(
				'orbit_branded_emails_font_family',
				self::get_woocommerce_setting( 'woocommerce_email_font_family' ) ?:
				'"Helvetica Neue", Helvetica, Roboto, Arial, sans-serif'
			),

			'logo_image_width' => apply_filters(
				'orbit_branded_emails_logo_image_width',
				self::get_woocommerce_setting( 'woocommerce_email_header_image_width' ) ?:
				120
			),
		];
	}

	/**
	 * Resolves a color value from a theme.json setting to a usable HEX color string.
	 *
	 * This function accepts a color value which may be a direct HEX code or a CSS variable
	 * (e.g. `var(--wp--preset--color--primary)` or `var(--wp--custom--color--custom-blue)`),
	 * and attempts to resolve it to a valid HEX color by looking up the appropriate values
	 * in the provided theme.json structure.
	 *
	 * It supports recursive resolution in case the referenced color is itself another variable.
	 *
	 * @param string $color_value The raw color value to resolve. Can be a HEX code or a CSS variable.
	 * @param string $fallback Optional fallback value to return if resolution fails. Defaults to null.
	 *
	 * @return string|null The resolved HEX color code (e.g. "#ffffff"), or null if resolution fails.
	 */
	public static function orbit_branded_emails_resolve_color( $color_value, $fallback = null ) {
		$theme_json = wp_get_global_settings();

		// If already a hex code, just return it.
		if ( preg_match( '/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color_value ) ) {
			return $color_value;
		}

		// Match CSS variable format
		if ( preg_match( '/var\(--wp--(preset|custom)--color--([a-zA-Z0-9_-]+(?:--[a-zA-Z0-9_-]+)*)\)/', $color_value, $matches ) ) {
			$type       = $matches[1]; // 'preset' or 'custom'
			$color_slug = $matches[2];

			if ( $type === 'preset' ) {
				$presets = $theme_json['color']['palette']['theme'] ?? [];

				foreach ( $presets as $preset ) {
					if ( $preset['slug'] === $color_slug ) {
						return self::orbit_branded_emails_resolve_color( $preset['color'], $fallback );
					}
				}
			}

			if ( $type === 'custom' ) {
				$custom_colors = $theme_json['custom']['color'] ?? [];

				$keys = explode( '--', $color_slug );

				$value = $custom_colors;
				foreach ( $keys as $key ) {
					if ( isset( $value[ $key ] ) ) {
						$value = $value[ $key ];
					} else {
						$value = null;
						break;
					}
				}

				if ( is_string( $value ) && $value !== '' ) {
					return self::orbit_branded_emails_resolve_color( $value, $fallback );
				}
			}
		}

		return $fallback;
	}

	/**
	 * Applies branded email background colors to Gravity Forms table label cells.
	 *
	 * @param string        $color The current background color.
	 * @param GF_Field_Name $field The current field.
	 * @param array         $lead The Gravity Forms entry data for the current form submission.
	 *
	 * @return string The resolved background color after applying the filter.
	 */
	public static function apply_branded_email_colours_to_gf_table_labels( $color, $field, $lead ): string {
		$label_background_color = apply_filters(
			'orbit_branded_emails_gf_label_bg_color',
			self::orbit_branded_emails_resolve_color(
				'var(--wp--preset--color--tint)'
			) ?:
			$color
		);

		return $label_background_color ?: $color;
	}

	/**
	 * Applies branded email colours to Gravity Forms table data.
	 *
	 * @see https://docs.gravityforms.com/gform_email_background_color_data/
	 *
	 * @param string        $color The current background color.
	 * @param GF_Field_Name $field The current field.
	 * @param array         $entry The current entry.
	 * @return string The modified color value.
	 */
	public static function apply_branded_email_colours_to_gf_table_data( $color, $field, $entry ): string {
		$data_background_color = apply_filters(
			'orbit_branded_emails_gf_data_bg_color',
			$color
		);

		return $data_background_color;
	}

	/**
	 * Checks if WooCommerce is active and retrieves a specific setting.
	 *
	 * Should only be used for settings that are expected to be set by WooCommerce.
	 *
	 * @param string $setting_name The name of the WooCommerce setting to retrieve.
	 * @return mixed|null The value of the setting, or null if WooCommerce is not active or the setting does not exist.
	 */
	public static function get_woocommerce_setting( $setting_name ) {
		// check if woocommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		return get_option( $setting_name ) ?: null;
	}
}
