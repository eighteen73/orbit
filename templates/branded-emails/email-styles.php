<?php
/**
 * Branded Email Styles
 * This file can be overridden in your theme.
 *
 * @package         Orbit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$background_color = apply_filters( 'orbit_branded_emails_background_color', '#f4f4f4' );
$body_background_color = apply_filters( 'orbit_branded_emails_body_background_color', '#ffffff' );
$body_border_color = apply_filters( 'orbit_branded_emails_body_border_color', '#ddd' );
$body_max_width = apply_filters( 'orbit_branded_emails_body_max_width', '800px' );
$body_text_color = apply_filters( 'orbit_branded_emails_body_text_color', '#000' );
$footer_text_color = apply_filters( 'orbit_branded_emails_footer_text_color', '#999' );
?>

<style>
	body {
		background: <?php echo esc_attr( $background_color ); ?>;
		font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
		padding: 20px;
	}

	.email-wrapper {
		background: <?php echo esc_attr( $body_background_color ); ?>;
		border: 1px solid <?php echo esc_attr( $body_border_color ); ?>;
		border-radius: 6px;
		color: <?php echo esc_attr( $body_text_color ); ?>;
		max-width: <?php echo esc_attr( $body_max_width ); ?>;
		margin: 0 auto;
		padding: 20px;
	}

	.email-header img {
		max-height: 100px;
		width: auto;
	}

	.email-content {
		margin-bottom: 40px;
		margin-top: 40px;
	}

	.email-footer {
		color: <?php echo esc_attr( $footer_text_color ); ?>;
		font-size: 12px;
		margin-top: 20px;
		text-align: center;
	}
</style>
