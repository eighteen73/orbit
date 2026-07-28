# Orbit

Orbit moulds some of WordPress' default behaviour to simplify the CMS experience, protect users from changing settings that should be reserved for website developers, and reinforce some areas of the CMS that tend to be a little weak.

The plugin is unapologetically opinionated to fit the needs and preferences of our web agency. We feel the choices we've made (including which ones are even configurable in the CMS) are sensible and pragmatic for the kinds of websites we work on and the control we tend to hold back from CMS users. We understand not everyone will agree with the choices we have made.

## Installation

This plugin has no prerequisites. Assuming you're using a modern Composer workflow for WordPress development (such as our [Nebula](https://github.com/eighteen73/nebula) WordPress stack) just run the following command and Orbit will be installed as a must-use plugin:

```shell
composer require eighteen73/orbit
```

If necessary, you may install it manually by downloading a Zip archive from [GitHub](https://github.com/eighteen73/orbit) and extracting it to your plugins directory.

## Vendored Dependencies

Orbit ships a small number of vendored third-party libraries (currently `pelago/emogrifier`, `enshrined/svg-sanitize`, and their transitive dependencies) under `includes/lib/`, namespaced via [Mozart](https://github.com/coenjacobs/mozart) into `Eighteen73\Orbit\Dependencies\…` to avoid collisions with other plugins.

To refresh these after pulling a new Composer release of a vendored package:

```shell
composer install
composer update <vendor>/<package>
vendor/bin/mozart compose
composer dump-autoload
```

Security advisories that affect any of the vendored packages **must** be applied here as well as in any Composer-installed copies; the `includes/lib/` tree is a snapshot and will not be patched automatically by `composer update`.

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
- Adds an environment icon to indicate the configured environment type

### Security

- Disable user endpoints in the REST API (configurable)
- Disable XML-RPC (configurable)
- Hide the WordPress version in page markup (configurable)
- Disable/hide unwanted website markup
    - Short links
    - REST API links
    - Oembed links
    - Windows Live Writer manifest links
- Set sensible security headers
- Disable author pages for posts

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
- Allow SVG uploads with sanitization and media library previews (inspired by [Safe SVG](https://github.com/10up/safe-svg); configurable via `orbit_enable_svg_uploads`)
  - Anyone with the `upload_files` capability can upload SVGs when the feature is enabled
  - Uploads are sanitized with `enshrined/svg-sanitize` plus Orbit’s stricter defaults (no `<style>`/`<a>`, fragment-only `href`s, remote references removed)
  - Gzipped `.svgz` uploads are **disabled by default** (`orbit_enable_svgz_uploads`); when enabled, decompression is size- and ratio-limited
  - Sanitized SVGs are intended for use as `<img src="…">` (or CSS backgrounds). **Do not inline** upload SVGs into HTML without a separate inline-SVG policy — standalone or inlined SVGs can still be active documents
  - For stronger isolation, serve `wp-content/uploads` from a cookieless domain and/or apply restrictive CSP/`Content-Disposition` rules for `*.svg` at the web server
- Reduce PHP error-log noise while preserving serious errors (configurable via `ORBIT_ERROR_REPORTING`)
- Syncs theme patterns marked `Synced: true` into WordPress synced patterns (`wp_block`) so design updates from the theme roll out to every existing instance

### Synced theme patterns

Orbit can turn theme pattern files into real synced patterns (with pattern overrides), so the theme remains the source of truth in version control.

**Authoring (theme file)**

1. Add a pattern under `patterns/` as usual.
2. Opt in with a header value of `true`, `yes`, or `1`:

```php
<?php
/**
 * Title: Feature cards
 * Slug: mytheme/feature-cards
 * Categories: featured
 * Synced: true
 */
?>
<!-- wp:group ... -->
```

3. Mark overridable fields with `core/pattern-overrides` bindings and stable `metadata.name` values (those names are a public API — renaming them orphans existing instance content).

**Runtime (Orbit)**

- On `init`, Orbit compares a cheap **file fingerprint** (theme + pattern file mtimes/sizes) to a cached option — same idea as core’s theme pattern cache.
- When the fingerprint is unchanged, Orbit only re-registers in-memory `core/block` refs from cache (no file includes, no DB upserts).
- When files change (e.g. after deploy), it finds Synced theme patterns (child wins over parent for the same slug), creates/updates matching `wp_block` posts, then stores the new cache.
- The theme pattern is re-registered so `<!-- wp:pattern {"slug":"mytheme/feature-cards"} /-->` resolves to a `core/block` ref.
- The synced pattern appears in the editor Patterns UI like other synced patterns; inserting it stores a ref so layout updates propagate to all instances.
- REST updates to Orbit-managed synced patterns are blocked — change the theme file instead.
- Cache clears automatically on theme switch; you can also delete the `orbit_synced_theme_patterns_cache` option to force a full sync.

**Orbit vs Block Theme Developer**

| Tool | Responsibility |
|------|----------------|
| **Orbit** (always installed) | Runtime sync: theme `Synced` patterns → `wp_block` on every environment |
| **Block Theme Developer** (local only) | Authoring/export of patterns and template parts to theme files |

Do not rely on Block Theme Developer for production sync.

**Override naming contract**

Treat each overridable block’s `metadata.name` (e.g. `Card Title 1`) as stable. Layout/style changes in the theme file will update every instance; override values survive only when those names still exist on the updated pattern.

**Not every pattern should be Synced**

Use Synced for library patterns clients should keep on-brand site-wide. Leave one-off page scaffolds unsynced (WP 7.0 `contentOnly` still locks casual structural edits on those).

## Available Filters

### Branded Emails

The following filters can be used to override default behaviour/values of branded email features.

-   `orbit_enable_branded_emails`: Enable Orbit's branded emails feature. Default `true` (enabled).
-   `orbit_branded_emails_header_logo`: Set the logo image in branded emails. Default `''` (empty).
-   `orbit_branded_emails_background_color`: Set the background color of branded emails. Default `woocommerce_email_background_color || '#ffffff'`.
-   `orbit_branded_emails_body_background_color`: Set the body background color of branded emails. Default `woocommerce_email_body_background_color || '#ffffff'`.
-   `orbit_branded_emails_body_border_color`: Set the border color of branded emails. Default `var(--wp--custom--color--border) || '#edeff1'`.
-   `orbit_branded_emails_body_text_color`: Set the body text color of branded emails. Default `woocommerce_email_text_color || var(--wp--preset--color--contrast) || '#3f474d'`.
-   `orbit_branded_emails_link_color`: Set the link color of branded emails. Default `woocommerce_email_base_color || var(--wp--custom--color--link) || '#8549ff'`.
-   `orbit_branded_emails_footer_text_color`: Set the footer text color of branded emails. Default `woocommerce_email_footer_text_color || var(--wp--preset--color--contrast) || '#3F474d'`.
-   `orbit_branded_emails_font_family`: Set the font family of branded emails. Default `woocommerce_email_font_family || '"Helvetica Neue", Helvetica, Roboto, Arial, sans-serif'`.
-   `orbit_branded_emails_logo_image_width`: Set the logo image width in branded emails. Default `woocommerce_email_header_image_width || 120`.

### UI Cleanup

The following filters can be used to override the default behavior of certain features. Set the filter to `true` to enable the feature, or `false` to disable it.

-   `orbit_enable_menu_item_dashboard`: Control the visibility of the Dashboard menu item. Default `true` (visible).
-   `orbit_enable_menu_item_posts`: Control the visibility of the Posts menu item. Default `true` (visible).
-   `orbit_enable_menu_item_comments`: Control the visibility of the Comments menu item and toolbar item. Default `false` (hidden).
-   `orbit_enable_toolbar_item_new_content`: Control the visibility of the "New Content" item in the toolbar. Default `true` (visible).
-   `orbit_enable_login_logo`: Enable replacement of the login logo. Default `true` (enabled).
-   `orbit_login_logo_url`: Provide a URL to replace the default WordPress login logo. No default.
-   `orbit_login_logo_width`: Width (in pixels) for the replacement login logo. Default `250`.
-   `orbit_enable_admin_environment_name`: Control the display of the environment name in the admin area. Default `true` (enabled).

### Security

The following filters can be used to override the default behavior of certain features. Set the filter to `true` to enable the feature, or `false` to disable it.

-   `orbit_enable_rest_api_user_endpoints`: Enable or disable REST API user endpoints. Default `false` (disabled).
-   `orbit_enable_xmlrpc`: Enable or disable XML-RPC functionality. Default `false` (disabled).
-   `orbit_enable_expose_wordpress_version`: Show or hide the WordPress version in the site's frontend markup. Default `false` (hidden).
-   `orbit_default_security_headers`: Set an array of security headers.
-   `orbit_enable_author_pages`: Enable or disable author pages for posts. Default `false` (disabled).

### Capabilities

The following filters can be used to override the default behavior of certain features. Set the filter to `true` to enable the feature, or `false` to disable it.

-   `orbit_enable_user_caps_access`: Enable Orbit's user capability restrictions. Default `true` (enabled).
-   `orbit_enable_editor_caps_access`: Enable Orbit's editor capability restrictions. Default `true` (enabled).
-   `orbit_enable_gravity_forms_access`: Enable Orbit's Gravity Forms capability restrictions. Default `true` (enabled).

### Other Safety Measures

The following filters can be used to override the default behavior of certain features. Set the filter to `true` to enable the feature, or `false` to disable it.

-   `orbit_enable_wordpress_updates`: Control the visibility of the WordPress updates item in the toolbar. Default `false` (hidden).

### Other Features

-   `orbit_remote_files_url`: Override the production URL used for loading remote media files. Default value comes from `ORBIT_REMOTE_FILES_URL`.
-   `orbit_enable_svg_uploads`: Enable SVG upload sanitization and media library support. Default `true` (enabled).
-   `orbit_enable_svgz_uploads`: Allow gzipped `.svgz` uploads (still sanitized, with decompression limits). Default `false` (disabled).
-   `orbit_svg_max_decompressed_bytes`: Maximum uncompressed size when decoding gzipped SVGs. Default `10485760` (10 MiB).
-   `orbit_svg_max_compression_ratio`: Maximum uncompressed/compressed ratio when decoding gzipped SVGs. Default `100`.
-   `orbit_svg_allowed_tags`: Filter the allowlist of SVG tags used during sanitization (after Orbit’s default removals).
-   `orbit_svg_allowed_attributes`: Filter the allowlist of SVG attributes used during sanitization (after Orbit’s default removals).
-   `orbit_svg_disallowed_tags`: Tags removed from the library defaults before `orbit_svg_allowed_tags`. Default `['a', 'style']`.
-   `orbit_svg_disallowed_attributes`: Attributes removed from the library defaults before `orbit_svg_allowed_attributes`. Default `['style']`.
-   `orbit_svg_allow_style`: Keep `<style>` elements and `style` attributes after sanitization. Default `false`.
-   `orbit_svg_allow_external_hrefs`: Allow `http://` / `https://` in `href` / `xlink:href`. Default `false`.
-   `orbit_svg_allow_root_relative_hrefs`: Allow root-relative paths such as `/path` in `href` / `xlink:href`. Default `false`.
-   `orbit_enable_synced_theme_patterns`: Enable Orbit’s theme → synced pattern sync. Default `true` (enabled).
-   `orbit_synced_theme_patterns`: Filter the list of synced pattern data arrays discovered from theme files (full sync only).
-   `orbit_synced_theme_patterns_fingerprint_parts`: Filter fingerprint segments used for the sync cache.
-   `orbit_allow_theme_synced_pattern_updates`: Allow REST/Site Editor updates to Orbit-managed synced patterns. Default `false`. Block Theme Developer enables this in file mode.
-   `orbit_enable_disable_external_patterns`: Enable removal of external (e.g. WooCommerce) patterns. Default `true` (enabled).

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

## Error Reporting

Orbit overrides WordPress' PHP error reporting defaults to keep error logs useful. In development environments it suppresses deprecated and user-deprecated messages. In all other environments it also suppresses warnings, user warnings, notices, and user notices. Other errors continue through any previously registered error handler, or fall back to PHP's standard error handling.

Orbit re-registers its handler at several points during the WordPress lifecycle so these defaults remain effective if another plugin changes the active error handler or calls `error_reporting()`.

Define `ORBIT_ERROR_REPORTING` in `wp-config.php` to customise this behaviour:

```php
// Use a custom PHP error-reporting bitmask.
define( 'ORBIT_ERROR_REPORTING', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );

// Or disable Orbit's error-reporting override entirely.
define( 'ORBIT_ERROR_REPORTING', false );
```

When a custom bitmask is provided, Orbit passes it to `error_reporting()`. Its handler still suppresses the environment-specific noisy error levels described above; use `false` if another component should have full control of both the reporting level and error handler.
