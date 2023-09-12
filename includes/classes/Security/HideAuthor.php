<?php
/**
 * Remove ?author=x page to avoid promoting CMS usernames
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Security;

use Eighteen73\Orbit\Singleton;

/**
 * Remove ?author=x page to avoid promoting CMS usernames
 */
class HideAuthor {
	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'template_redirect', [ $this, 'author_page_404' ] );
	}

	/**
	 * Remove ?author=x page to avoid promoting CMS usernames
	 *
	 * @return string
	 */
	public function author_page_404() {
		global $wp_query;

		if ( is_author() ) {
			$wp_query->set_404();
			status_header( 404 );
		}
	}
}
