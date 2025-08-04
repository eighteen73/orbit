<?php
/**
 * Branded Email Template
 * This file can be overridden in your theme.
 *
 * Available variables:
 * @var string $email_content
 */

namespace Eighteen73\Orbit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <?php Templates::include_template_part( 'branded-emails/email-styles.php' ); ?>
</head>
<body>
  <div class="email-wrapper">
    <?php echo wpautop( $email_content ); ?>

    <div class="email-footer">
      Sent from <?php echo esc_html( get_bloginfo('name') ); ?>
    </div>
  </div>
</body>
</html>
