<?php
/**
 * Global Email Template
 * This file can be overridden in your theme.
 *
 * Available variables:
 * @var string $email_content
 */

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      padding: 20px;
    }
    .email-wrapper {
      background: #ffffff;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 6px;
      max-width: 600px;
      margin: 0 auto;
    }
    .email-footer {
      margin-top: 20px;
      font-size: 12px;
      color: #999;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="email-wrapper">
    <?php echo wpautop($email_content); ?>
    <div class="email-footer">
      Sent from <?php echo esc_html(get_bloginfo('name')); ?>
    </div>
  </div>
</body>
</html>
