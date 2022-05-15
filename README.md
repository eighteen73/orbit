# Orbit

Orbit moulds some of WordPress' default behaviour to simplify the CMS experience, protect users from changing settings that should be reserved for website developers, and reinforce some areas of the CMS that tend to be a little weak.

The plugin is unapologetically opinionated to fit the needs and preferences of our web agency. We feel the choices we've made (including which ones are even configurable in the CMS) are sensible and pragmatic for the kinds of websites we work on and the control we like to hold back from website owners. We understand not everyone will agree with the choices we have made.

## Installation

These instructions assume you are using [Nebula](https://github.com/eighteen73/nebula). That is a not a requirement but if you're using alternative foundation for WordPress you must ensure [Carbon Fields](https://carbonfields.net/) is installed as per [these instructions](https://docs.carbonfields.net/).

To install the plugin run the following command:

```shell
composer require eighteen73/orbit
```

Orbit will be installed as a must-use plugin so it is automatically enabled.

## Configuration

All available configuration is done via the menu link _Settings > Orbit Options_.

## Summary of Features

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

### Emails

- SMTP settings for email sending (configurable)
- Email sender name and address (configurable)

### Other Safety Measures

- Disallow robot indexing in non-production environments
- Disable updates
