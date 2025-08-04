<?php
/**
 * Add branding email templates to site emails.
 *
 * @package         Orbit
 */

namespace Eighteen73\Orbit;

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
		if ( apply_filters( 'orbit_disable_branded_emails', false ) ) {
			return;
		}

		add_filter( 'wp_mail', [ $this, 'apply_branded_email_template' ] );
	}

	/**
	 * Wraps the original email message in a branded HTML email template.
	 *
	 * @param array $args Array of arguments passed to wp_mail().
	 * @return array Modified $args array with the email message wrapped in branded HTML.
	 */
	public function apply_branded_email_template( $args ) {
		ob_start();

		Templates::include_template_part(
			'branded-emails/email-template.php',
			[
				'email_content' => $args['message'],
			]
		);

		$styled_message = ob_get_clean();

		$args['message'] = $styled_message;

		return $args;
	}
}
