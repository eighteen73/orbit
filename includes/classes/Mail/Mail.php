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
class Mail extends Singleton {

	/**
	 * Setup module
	 */
	public function setup(): void {
		$options = get_option( 'orbit_options' );

		if ( ! isset( $options['mail'] ) || $options['mail'][0] !== 'enabled' ) {
			return;
		}

		add_action( 'phpmailer_init', [ $this, 'mail_credentials' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'mail_content_type' ] );

		if ( isset( $options['from_email'] ) && ! empty( $options['from_email'] ) ) {
			add_filter( 'wp_mail_from', [ $this, 'mail_from_address' ] );
		}

		if ( isset( $options['from_name'] ) && ! empty( $options['from_name'] ) ) {
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
		$options = get_option( 'orbit_options' );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName -- these class properties are not our code
		$mail->IsSMTP();
		$mail->SMTPAutoTLS = false;

		$mail->Host = $options['host_address'];
		$mail->Port = $options['host_port'];

		// Enable encruption if either 'ssl' or 'tls' are set
		if ( $options['encryption'] !== 'none' ) {
			$mail->SMTPAuth   = true;
			$mail->SMTPSecure = $options['host_address'];
		}

		if ( ! isset( $options['authentication'] ) || $options['authentication'][0] !== 'enabled' ) {
			$mail->Username = $options['username'];
			$mail->Password = $options['password'];
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
		$options = get_option( 'orbit_options' );
		return $options['from_email'];
	}

	/**
	 * Set from name
	 */
	public function mail_from_name() {
		$options = get_option( 'orbit_options' );
		return $options['from_name'];
	}

}
