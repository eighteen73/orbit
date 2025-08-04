<?php
/**
 * Branded Email Template
 * This file can be overridden in your theme.
 *
 * @package         Orbit
 *
 * @var string $email_content
 */

namespace Eighteen73\Orbit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$header_logo = apply_filters( 'orbit_branded_emails_header_logo', '' );
$email_content = $args['email_content'] ?? '';
$email_subject = $args['email_subject'] ?? get_bloginfo( 'name', 'display' );
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo esc_attr( $email_subject ); ?></title>
		<?php Templates::include_template( 'branded-emails/email-styles.php' ); ?>
	</head>

	<body>
		<div class="email-wrapper">
			<div class="email-header">
				<?php
				if ( ! empty( $header_logo ) ) {
					echo '<p style="margin-top:0;"><img src="' . esc_url( $header_logo ) . '" alt="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" /></p>';
				} else {
					echo '<h1>' . esc_html( get_bloginfo( 'name' ) ) . '</h1>';
				}
				?>
			</div>

			<div class="email-content">
				<?php echo wp_kses_post( wpautop( $email_content ) ); ?>
			</div>

			<div class="email-footer">
				Sent from <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?>
			</div>
		</div>
	</body>
</html>
