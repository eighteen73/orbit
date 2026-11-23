<?php
/**
 * Disable Emails.
 *
 * Turns off noisy WordPress emails that site admins don't need, such as being
 * told every time someone registers or resets their password. Which emails are
 * disabled is controlled by the `orbit_disabled_emails` filter.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Notifications;

use Eighteen73\Orbit\Singleton;

/**
 * Disable Emails.
 */
class DisableEmails {

	use Singleton;

	/**
	 * Emails this module knows how to disable, keyed by ID, mapped to the
	 * method that disables them.
	 */
	private const EMAILS = [
		'new_user_admin'        => 'disable_new_user_admin',
		'password_change_admin' => 'disable_password_change_admin',
	];

	/**
	 * Emails disabled unless a site filters them back on.
	 */
	private const DEFAULT_DISABLED = [
		'new_user_admin',
		'password_change_admin',
	];

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Disable each filtered email.
	 *
	 * Runs on init so themes and plugins have had a chance to add the filter.
	 *
	 * @return void
	 */
	public function init(): void {
		foreach ( $this->get_disabled_emails() as $email ) {
			if ( isset( self::EMAILS[ $email ] ) ) {
				$this->{self::EMAILS[ $email ]}();
			}
		}
	}

	/**
	 * IDs of the emails to disable.
	 *
	 * @return string[]
	 */
	public function get_disabled_emails(): array {
		/**
		 * Filters which WordPress emails are disabled.
		 *
		 * Supported IDs:
		 * - `new_user_admin`: the admin's "New User Registration" email.
		 * - `password_change_admin`: the admin's "Password Changed" email when a user resets their password.
		 *
		 * @param string[] $emails Email IDs to disable.
		 */
		$emails = apply_filters( 'orbit_disabled_emails', self::DEFAULT_DISABLED );

		return is_array( $emails ) ? array_unique( $emails ) : [];
	}

	/**
	 * Stop the admin being emailed when a new user is created.
	 *
	 * The new user still receives their own welcome email.
	 *
	 * @return void
	 */
	private function disable_new_user_admin(): void {
		add_filter( 'wp_send_new_user_notification_to_admin', '__return_false' );
	}

	/**
	 * Stop the admin being emailed when a user resets their password.
	 *
	 * Covers core's lost password flow and WooCommerce's My Account reset.
	 *
	 * @return void
	 */
	private function disable_password_change_admin(): void {
		remove_action( 'after_password_reset', 'wp_password_change_notification' );
		add_filter( 'woocommerce_disable_password_change_notification', '__return_true' );
	}
}
