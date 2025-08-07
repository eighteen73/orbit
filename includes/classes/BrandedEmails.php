<?php
/**
 * Add branding email templates to site emails.
 *
 * @package         Orbit
 */

namespace Eighteen73\Orbit;

use Exception;
use Eighteen73\Orbit\Dependencies\Pelago\Emogrifier\CssInliner;

/**
 * This class is built upon BE Media from Production so all due credit to those authors.
 * http://www.github.com/billerickson/be-media-from-production
 */
class BrandedEmails {

	use Environment;
	use Singleton;

	/**
	 * Primary constructor
	 *
	 * @return void
	 */
	public function setup() {
		if ( apply_filters( 'orbit_branded_emails_disable', false ) ) {
			return;
		}

		add_filter( 'wp_mail', [ $this, 'apply_branded_email_template' ] );
		add_filter( 'gform_html_message_template_pre_send_email', [ $this, 'apply_branded_email_template_to_gf_notifications' ] );
	}

	/**
	 * Wraps the original email message in a branded HTML email template.
	 *
	 * @param array $args Array of arguments passed to wp_mail().
	 * @return array Modified $args array with the email message wrapped in branded HTML.
	 */
	public function apply_branded_email_template( $args ) {
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
	public function apply_branded_email_template_to_gf_notifications( $template ) {
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
	public function style_inline( $email_content ) {
		try {
			$inlined_content = CssInliner::fromHtml( $email_content )->inlineCss()->render();
		} catch ( Exception $e ) {
			$inlined_content = $email_content;
		}

		return $inlined_content;
	}
}
