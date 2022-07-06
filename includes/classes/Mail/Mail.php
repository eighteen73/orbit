<?php
/**
 * Override mail sending to use an SMTP server
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Mail;

use Eighteen73\Orbit\Singleton;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Inspired by https://github.com/wordplate/mail
 */
class Mail {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup(): void {

		if ( get_option( '_orbit_mail_enabled' ) !== true ) {
			return;
		}

		add_action( 'phpmailer_init', [ $this, 'mail_credentials' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'mail_content_type' ] );

		if ( get_option( '_orbit_mail_from_address' ) ) {
			add_filter( 'wp_mail_from', [ $this, 'mail_from_address' ] );
		}

		if ( get_option( '_orbit_mail_from_name' ) ) {
			add_filter( 'wp_mail_from_name', [ $this, 'mail_from_name' ] );
		}
	}

	/**
	 * Set SMTP credentials
	 *
	 * @param PHPMailer $mail The PHPMailer instance
	 *
	 * @return PHPMailer
	 */
	public function mail_credentials( PHPMailer $mail ): PHPMailer {
		// phpcs:disable WordPress.NamingConventions.ValidVariableName -- these class properties are not our code
		$mail->IsSMTP();
		$mail->SMTPAutoTLS = false;

		$mail->Host = get_option( '_orbit_mail_host' );
		$mail->Port = get_option( '_orbit_mail_port' );

		// Enable encruption if either 'ssl' or 'tls' are set
		if ( get_option( '_orbit_mail_encryption' ) !== 'none' ) {
			$mail->SMTPAuth   = true;
			$mail->SMTPSecure = get_option( '_orbit_mail_encryption' );
		}

		if ( get_option( '_orbit_mail_auth' ) === true ) {
			$mail->Username = get_option( '_orbit_mail_username' );
			$mail->Password = get_option( '_orbit_mail_password' );
		}
		// phpcs:enable
		return $mail;
	}

	/**
	 * Force HTML emails
	 */
	public function mail_content_type(): string {
		return 'text/html';
	}

	/**
	 * Set from address
	 */
	public function mail_from_address() {
		return get_option( '_orbit_mail_from_address' );
	}

	/**
	 * Set from name
	 */
	public function mail_from_name() {
		return get_option( '_orbit_mail_from_name' );
	}

}
