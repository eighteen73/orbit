<?php

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;

/**
 * Remove head links that we never use and potentially expose data.
 * We have not given an option to disable this because there have never been
 * a case when these are needed.
 */
class RemoveHeadLinks extends Singleton {

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// We don't want to advertise the JSON API, even if its enabled on the site
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );

		// Disable oEmbed discovery links and auto discovery
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result' );
		add_filter( 'embed_oembed_discover', '__return_false' );
		add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );
	}
}
