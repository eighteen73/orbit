<?php
/**
 * Branded Email Styles
 *
 * This template can be overridden by copying it to yourtheme/orbit/branded-emails/email-styles.php.
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$css_variables = BrandedEmails::get_css_variables();
?>

<style>
	body {
		background: <?php echo esc_attr( $css_variables['background_color'] ); ?>;
		font-family: <?php echo $css_variables['font_family']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
		padding: 0;
		text-align: center;
	}

	#outer_wrapper {
		background: <?php echo esc_attr( $css_variables['background_color'] ); ?>;
	}

	#inner_wrapper {
		background-color: <?php echo esc_attr( $css_variables['body_background_color'] ); ?>;
		border-radius: 6px;
	}

	#email_wrapper {
		margin: 0 auto;
		padding: 24px 0;
		-webkit-text-size-adjust: none !important;
		width: 100%;
		max-width: 600px;
	}

	#template_container {
		background-color: <?php echo esc_attr( $css_variables['body_background_color'] ); ?>;
		border-radius: 3px !important;
	}

	#template_header {
		background-color: <?php echo esc_attr( $css_variables['body_background_color'] ); ?>;
		border-radius: 3px 3px 0 0 !important;
		color: <?php echo esc_attr( $css_variables['body_text_color'] ); ?>;
		border-bottom: 0;
		font-weight: bold;
		line-height: 100%;
		vertical-align: middle;
		font-family: <?php echo $css_variables['font_family']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	}

	#template_header h1 {
		color: <?php echo esc_attr( $css_variables['body_text_color'] ); ?>;
		background-color: inherit;
	}

	#template_header_image {
		padding: 32px 32px 0;
	}

	#template_header_image p {
		margin-bottom: 0;
		text-align: left;
	}

	#template_header_image img {
		width: <?php echo esc_attr( $css_variables['logo_image_width'] ); ?>px;
	}

	.email-logo-text {
		color: <?php echo esc_attr( $css_variables['link_color'] ); ?>;
		font-family: <?php echo $css_variables['font_family']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
		font-size: 18px;
	}

	.email-introduction {
		padding-bottom: 24px;
	}

	#template_footer td {
		padding: 0;
	}

	#template_footer #credit {
		border: 0;
		border-top: 1px solid <?php echo esc_attr( $css_variables['body_border_color'] ); ?>;
		color: <?php echo esc_attr( $css_variables['footer_text_color'] ); ?>;
		font-family: <?php echo $css_variables['font_family']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
		font-size: 12px;
		line-height: 140%;
		text-align: center;
		padding: 32px;
	}

	#template_footer #credit p {
		margin: 0;
	}

	#body_content {
		background-color: <?php echo esc_attr( $css_variables['body_background_color'] ); ?>;
	}

	#body_content table td th {
		padding: 12px;
	}
	#body_content p {
		margin: 0 0 16px;
	}

	#body_content_inner {
		color: <?php echo esc_attr( $css_variables['body_text_color'] ); ?>;
		font-family: <?php echo $css_variables['font_family']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
		font-size: 16px;
		line-height: 150%;
		text-align: left;
	}

	#body_content_inner_cell {
		padding: 20px 32px 32px;
	}

	.td {
		color: <?php echo esc_attr( $css_variables['body_text_color'] ); ?>;
		vertical-align: middle;
	}

	#header_wrapper {
		padding: 20px 32px 0;
		display: block;
	}

	#template_footer #credit,
	#template_footer #credit a {
		color: <?php echo esc_attr( $css_variables['footer_text_color'] ); ?>;
	}

	h1 {
		color: <?php echo esc_attr( $css_variables['body_text_color'] ); ?>;
		font-family: <?php echo $css_variables['font_family']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
		font-size: 32px;
		font-weight: 700;
		letter-spacing: -1px;
		line-height: 120%;
		margin: 0;
		text-align: left;
	}

	h2 {
		color: <?php echo esc_attr( $css_variables['body_text_color'] ); ?>;
		display: block;
		font-family: <?php echo $css_variables['font_family']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
		font-size: 20px;
		font-weight: bold;
		line-height: 160%;
		margin: 0 0 18px;
		text-align: left;
	}

	h3 {
		color: <?php echo esc_attr( $css_variables['body_text_color'] ); ?>;
		display: block;
		font-family: <?php echo $css_variables['font_family']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
		font-size: 16px;
		font-weight: bold;
		line-height: 160%;
		margin: 16px 0 8px;
		text-align: left;
	}

	a {
		color: <?php echo esc_attr( $css_variables['link_color'] ); ?>;
		font-weight: normal;
		text-decoration: underline;
	}

	img {
		border: none;
		display: inline-block;
		font-size: 14px;
		font-weight: bold;
		height: auto;
		outline: none;
		text-decoration: none;
		text-transform: capitalize;
		vertical-align: middle;
		max-width: 100%;
	}

	/**
	* Media queries are not supported by all email clients, however they do work on modern mobile
	* Gmail clients and can help us achieve better consistency there.
	*/
	@media screen and (max-width: 600px) {
		#template_header_image {
			padding: 16px 10px 0 !important;
		}

		#header_wrapper {
			padding: 16px 10px 0 !important;
		}

		#header_wrapper h1 {
			font-size: 24px !important;
		}

		#body_content_inner_cell {
			padding: 10px !important;
		}

		#body_content_inner {
			font-size: 12px !important;
		}
	}
</style>
