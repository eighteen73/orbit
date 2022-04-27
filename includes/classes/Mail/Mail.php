<?php

namespace Eighteen73\Orbit\Mail;

use Eighteen73\Orbit\Singleton;
use Eighteen73\Orbit\Traits\EnvReader;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Inspired by https://github.com/wordplate/mail
 */
class Mail extends Singleton {

	use EnvReader;

	private bool $enabled = false;

	public function __construct() {
		$this->enabled = $this->is_enabled();
	}

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'phpmailer_init', [ $this, 'mail_credentials' ] );
		add_filter( 'wp_mail_from', [ $this, 'mail_from_address' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'mail_content_type' ] );
		if ( $this->env( 'ORBIT_MAIL_FROM_NAME' ) ) {
			add_filter( 'wp_mail_from_name', [ $this, 'mail_from_name' ] );
		}
	}

	public function is_enabled(): bool {
		$settings = get_option( 'orbit_settings' );
		if ( ! is_array( $settings ) || ! isset( $settings['mail'] ) ) {
			return false;
		}

		return $settings['mail'] === true;
	}

	/**
	 * Set SMTP credentials
	 */
	public function mail_credentials( PHPMailer $mail ): PHPMailer {
		$mail->IsSMTP();
		$mail->SMTPAutoTLS = false;

		$mail->SMTPAuth   = true;
		$mail->SMTPSecure = $this->env( 'ORBIT_SMTP_ENCRYPTION', 'tls' );

		$mail->Host     = $this->env( 'ORBIT_SMTP_HOST' );
		$mail->Port     = $this->env( 'ORBIT_SMTP_PORT', 587 );
		$mail->Username = $this->env( 'ORBIT_SMTP_USERNAME' );
		$mail->Password = $this->env( 'ORBIT_SMTP_PASSWORD' );

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
		return $this->env( 'ORBIT_MAIL_FROM_ADDRESS' );
	}

	/**
	 * Set from name
	 */
	public function mail_from_name() {
		return $this->env( 'ORBIT_MAIL_FROM_NAME' );
	}

}
