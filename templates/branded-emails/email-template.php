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
$site_name = get_bloginfo( 'name', 'display' );
$email_content = $args['email_content'] ?? '';
$email_subject = $args['email_subject'] ?? get_bloginfo( 'name', 'display' );
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title><?php echo esc_attr( $email_subject ); ?></title>
		<?php Templates::include_template( 'branded-emails/email-styles.php' ); ?>
	</head>
	<body marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<table width="100%" id="outer_wrapper">
			<tr>
				<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
				<td width="600">
					<div id="email_wrapper">
						<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="inner_wrapper">
							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td id="template_header_image">
												<?php
												if ( $header_logo ) {
													echo '<p style="margin-top:0;"><img src="' . esc_url( $header_logo ) . '" alt="' . esc_attr( $site_name ) . '" /></p>';
												} else {
													echo '<p class="email-logo-text">' . esc_html( $site_name ) . '</p>';
												}
												?>
											</td>
										</tr>
									</table>
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_container">
										<tr>
											<td align="center" valign="top">
												<!-- Header -->
												<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header">
													<tr>
														<td id="header_wrapper">
															<h1><?php echo esc_html( $email_subject ); ?></h1>
														</td>
													</tr>
												</table>
												<!-- End Header -->
											</td>
										</tr>
										<tr>
											<td align="center" valign="top">
												<!-- Body -->
												<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body">
													<tr>
														<td valign="top" id="body_content">
															<!-- Content -->
															<table border="0" cellpadding="20" cellspacing="0" width="100%">
																<tr>
																	<td valign="top" id="body_content_inner_cell">
																		<div id="body_content_inner">
																			<?php echo wp_kses_post( wpautop( $email_content ) ); ?>
																		</div>
																	</td>
																</tr>
															</table>
															<!-- End Content -->
														</td>
													</tr>
												</table>
												<!-- End Body -->
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Footer -->
									<table border="0" cellpadding="10" cellspacing="0" width="100%" id="template_footer">
										<tr>
											<td valign="top">
												<table border="0" cellpadding="10" cellspacing="0" width="100%">
													<tr>
														<td colspan="2" valign="middle" id="credit">
															<p>Sent from <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></p>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<!-- End Footer -->
								</td>
							</tr>
						</table>
					</div>
				</td>
				<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
			</tr>
		</table>
	</body>
</html>
