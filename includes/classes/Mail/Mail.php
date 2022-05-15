<?php

namespace Eighteen73\Orbit\Mail;

use Eighteen73\Orbit\Singleton;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Inspired by https://github.com/wordplate/mail
 */
class Mail extends Singleton {

	/**
	 * Setup module
	 */
	public function setup() {

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
	 */
	public function mail_credentials( PHPMailer $mail ): PHPMailer {
		$mail->IsSMTP();
		$mail->SMTPAutoTLS = false;

		$mail->Host     = carbon_get_theme_option( 'separator_mail_host' );
		$mail->Port     = carbon_get_theme_option( 'separator_mail_port' );

		// If either 'ssl' or 'tls'
		if ( carbon_get_theme_option( 'orbit_mail_encryption' ) !== 'none' ) {
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = carbon_get_theme_option( 'separator_mail_encryption' );
		}

		if ( carbon_get_theme_option( 'orbit_mail_auth' ) === true ) {
			$mail->Username = carbon_get_theme_option( 'separator_mail_username' );
			$mail->Password = carbon_get_theme_option( 'separator_mail_password' );
		}

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
