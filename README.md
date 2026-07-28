# Orbit

Orbit moulds WordPress default behaviour to simplify the CMS experience, protect users from changing settings that should be reserved for website developers, and reinforce areas of the CMS that tend to be fragile or noisy.

The plugin is unapologetically opinionated to fit the needs and preferences of our web agency.

For complete documentation, visit [docs.eighteen73.co.uk/wordpress/plugins/orbit](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/).

## Installation

Assuming you are using a modern Composer workflow for WordPress development (such as our [Nebula](https://github.com/eighteen73/nebula) WordPress stack), install Orbit as a Must-Use plugin using:

```bash
composer require eighteen73/orbit
```

For manual installation or non-Composer environments, see the [Installation Guide](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/installation).

## Features Overview

- **[Synced Theme Patterns](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/synced-patterns)**: Keeps block theme pattern files in version control while syncing them to real database patterns with block overrides.
- **[Branded Emails](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/branded-emails)**: Wraps system emails and Gravity Forms notifications in responsive HTML with WooCommerce styling parity.
- **[UI Cleanup](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/ui-cleanup)**: Simplifies admin menus, toolbar nodes, and dashboard widgets, with custom login logo and environment indicator badge support.
- **[Security Hardening](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/security)**: Blocks REST API user enumeration, disables XML-RPC, strips head tags, and enforces baseline HTTP security headers.
- **[Capabilities & Access](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/capabilities)**: Granular permission management for Editors and Shop Managers across Site Editor, User management, Gravity Forms, and Privacy options.
- **[Performance & Utilities](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/performance-utilities)**: Fast 404 responses for missing static assets, remote media hotlinking from production, health check endpoint (`/wp-json/orbit/up`), and search engine index suppression on non-production.
- **[Integrations](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/integrations)**: Performance and safety tweaks for WooCommerce, Action Scheduler, and Altcha CAPTCHA.

For a complete reference of filter hooks and constants, see the [Filters & Hooks Reference](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/filters-reference).

## Vendored Dependencies

Orbit ships a small number of vendored third-party libraries under `includes/lib/`, namespaced via [Mozart](https://github.com/coenjacobs/mozart) into `Eighteen73\Orbit\Dependencies\…` to avoid collisions.

To refresh these dependencies after pulling updates:

```bash
composer install
composer update <vendor>/<package>
vendor/bin/mozart compose
composer dump-autoload
```

For security advisory notes, refer to the [Installation Documentation](https://docs.eighteen73.co.uk/wordpress/plugins/orbit/installation).
