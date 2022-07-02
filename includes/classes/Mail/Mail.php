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

		if ( carbon_get_theme_option( 'orbit_mail_enabled' ) !== true ) {
			return;
		}

		add_action( 'phpmailer_init', [ $this, 'mail_credentials' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'mail_content_type' ] );

		if ( carbon_get_theme_option( 'orbit_mail_from_address' ) ) {
			add_filter( 'wp_mail_from', [ $this, 'mail_from_address' ] );
		}

		if ( carbon_get_theme_option( 'orbit_mail_from_name' ) ) {
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

		$mail->Host = carbon_get_theme_option( 'orbit_mail_host' );
		$mail->Port = carbon_get_theme_option( 'orbit_mail_port' );

		// Enable encruption if either 'ssl' or 'tls' are set
		if ( carbon_get_theme_option( 'orbit_mail_encryption' ) !== 'none' ) {
			$mail->SMTPAuth   = true;
			$mail->SMTPSecure = carbon_get_theme_option( 'orbit_mail_encryption' );
		}

		if ( carbon_get_theme_option( 'orbit_mail_auth' ) === true ) {
			$mail->Username = carbon_get_theme_option( 'orbit_mail_username' );
			$mail->Password = carbon_get_theme_option( 'orbit_mail_password' );
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
		return carbon_get_theme_option( 'orbit_mail_from_address' );
	}

	/**
	 * Set from name
	 */
	public function mail_from_name() {
		return carbon_get_theme_option( 'orbit_mail_from_name' );
	}

}
