# Orbit

Orbit moulds some of WordPress' default behaviour to simplify the CMS experience, protect users from changing settings that should be reserved for website developers, and reinforce some areas of the CMS that tend to be a little weak.

The plugin is unapologetically opinionated to fit the needs and preferences of our web agency. We feel the choices we've made (including which ones are even configurable in the CMS) are sensible and pragmatic for the kinds of websites we work on and the control we tend to hold back from CMS users. We understand not everyone will agree with the choices we have made.

## Installation

This plugin has no prerequisites. Assuming you're using a modern Composer workflow for WordPress development (such as our [Nebula](https://github.com/eighteen73/nebula) WordPress stack) just run the following command and Orbit will be installed as a must-use plugin:

```shell
composer require eighteen73/orbit
```

If necessary, you may install it manually by downloading a Zip archive from [GitHub](https://github.com/eighteen73/orbit) and extracting it to your plugins directory.

## Summary of Features

### Branded Emails
- Adds branding to emails that aren't set to be 'text/html'
- Adds branding to Gravity Forms notification emails
- When possible uses WooCommerce email branding for consistenty
- Main email template can be overriden by copying `templates/branded-emails/email-template.php` into `yourtheme/orbit/branded-emails`
- Email styles template can be overriden by copying `templates/branded-emails/email-styles.php` into `yourtheme/orbit/branded-emails`

### UI Cleanup

- Remove unwanted items from the menu (with limited configuration)
- Remove unwanted items from the toolbar (with limited configuration)
- Remove unwanted dashboard widgets
- Replace the login logo (configurable)
- Remove the footer message in CMS

### Security

- Disable user endpoints in the REST API (configurable)
- Disable XML-RPC (configurable)
- Hide the WordPress version in page markup (configurable)
- Disable/hide unwanted website markup
    - Short links
    - REST API links
    - Oembed links
    - Windows Live Writer manifest links

### Capabilities

- Restrict user access based on capabilities (configurable)
- Restrict editor access based on capabilities (configurable)
- Restrict Gravity Forms access based on capabilities (configurable)

### Other Safety Measures

- Disallow robot indexing in non-production environments
- Disable updates (configurable via `orbit_enable_wordpress_updates`)

### Other Features

- Adds endpoint "/wp-json/orbit/up" for use as quick website availability check
- Load media files from a production URL in non-production environments (requires `ORBIT_REMOTE_FILES_URL` environment variable/constant)

## Available Filters

The following filters can be used to override the default behavior of certain features. Set the filter to `true` to enable the feature, or `false` to disable it.

-   `orbit_enable_wordpress_updates`: Control the visibility of the WordPress updates item in the toolbar. Default `false` (hidden).
-   `orbit_enable_xmlrpc`: Enable or disable XML-RPC functionality. Default `false` (disabled).
-   `orbit_enable_user_caps_access`: Enable Orbit's user capability restrictions. Default `true` (enabled).
-   `orbit_enable_editor_caps_access`: Enable Orbit's editor capability restrictions. Default `true` (enabled).
-   `orbit_enable_gravity_forms_access`: Enable Orbit's Gravity Forms capability restrictions. Default `true` (enabled).
-   `orbit_enable_expose_wordpress_version`: Show or hide the WordPress version in the site's frontend markup. Default `false` (hidden).
-   `orbit_enable_admin_environment_name`: Control the display of the environment name in the admin area. Default `true` (enabled).
-   `orbit_enable_menu_item_dashboard`: Control the visibility of the Dashboard menu item. Default `true` (visible).
-   `orbit_enable_menu_item_posts`: Control the visibility of the Posts menu item. Default `true` (visible).
-   `orbit_enable_menu_item_comments`: Control the visibility of the Comments menu item and toolbar item. Default `false` (hidden).
-   `orbit_enable_toolbar_item_new_content`: Control the visibility of the "New Content" item in the toolbar. Default `true` (visible).
-   `orbit_enable_login_logo`: Enable replacement of the login logo. Default `true` (enabled).
-   `orbit_login_logo_url`: Provide a URL to replace the default WordPress login logo. No default.
-   `orbit_enable_rest_api_user_endpoints`: Enable or disable REST API user endpoints. Default `false` (disabled).
-   `orbit_remote_files_url`: Override the production URL used for loading remote media files. Default value comes from `ORBIT_REMOTE_FILES_URL`.

### Branded Emails

-   `orbit_enable_branded_emails`: Enable Orbit's branded emails feature. Default `true` (enabled).
-   `orbit_branded_emails_header_logo`: Set the logo image in branded emails. Default `''` (empty).
-   `orbit_branded_emails_background_color`: Set the background color of branded emails. Default `woocommerce_email_background_color || '#ffffff'`.
-   `orbit_branded_emails_body_background_color`: Set the body background color of branded emails. Default `woocommerce_email_body_background_color || '#ffffff'`.
-   `orbit_branded_emails_body_border_color`: Set the border color of branded emails. Default `var(--wp--custom--color--border) || '#edeff1'`.
-   `orbit_branded_emails_body_text_color`: Set the body text color of branded emails. Default `woocommerce_email_text_color || var(--wp--preset--color--contrast) || '#3f474d'`.
-   `orbit_branded_emails_link_color`: Set the link color color of branded emails. Default `woocommerce_email_base_color || var(--wp--custom--color--link) || '#8549ff'`.
-   `orbit_branded_emails_footer_text_color`: Set the footer text color of branded emails. Default `woocommerce_email_footer_text_color || var(--wp--preset--color--contrast) || '#3F474d'`.
-   `orbit_branded_emails_font_family`: Set the font family of branded emails. Default `woocommerce_email_font_family || '"Helvetica Neue", Helvetica, Roboto, Arial, sans-serif'`.
-   `orbit_branded_emails_logo_image_width`: Set the logo image width in branded emails. Default `woocommerce_email_header_image_width || 120`.

### Examples

You can use standard WordPress functions like `__return_true` and `__return_false` to easily toggle these features. Add the following lines to your theme or a custom plugin:

```php
// Example: Enable XML-RPC (Orbit disables it by default)
add_filter( 'orbit_enable_xmlrpc', '__return_true' );

// Example: Show the WordPress version in the site markup (Orbit hides it by default)
add_filter( 'orbit_enable_expose_wordpress_version', '__return_true' );

// Example: Disable the Posts menu item (Orbit shows it by default)
add_filter( 'orbit_enable_menu_item_posts', '__return_false' );

// Example: Disable the login logo replacement (Orbit enables it by default)
add_filter( 'orbit_enable_login_logo', '__return_false' );
```
