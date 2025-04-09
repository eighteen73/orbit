<?php
/**
 * Simplify the CMS UI by removing unwanted elements
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Admin;

use Eighteen73\Orbit\Forms\Options;
use Eighteen73\Orbit\Singleton;

/**
 * Simplify the CMS UI by removing unwanted elements
 *
 * Inspired by https://github.com/wordplate/clean-ui
 */
class CleanUI {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'admin_menu', [ $this, 'clean_ui_menu_items' ] );
		add_action( 'admin_bar_menu', [ $this, 'clean_ui_toolbar_items' ], 999 );
		add_action( 'wp_dashboard_setup', [ $this, 'clean_ui_dashboard_widgets' ] );
		add_action( 'login_head', [ $this, 'clean_ui_logo' ] );
		add_filter( 'admin_footer_text', [ $this, 'remove_cms_footer_text' ] );
	}

	/**
	 * Remove menu items
	 */
	public function clean_ui_menu_items() {
		if ( apply_filters( 'orbit_enable_disable_menu_item_dashboard', false ) ) {
			remove_menu_page( 'index.php' );
		}
		if ( apply_filters( 'orbit_enable_disable_menu_item_posts', false ) ) {
			remove_menu_page( 'edit.php' );
		}
		if ( apply_filters( 'orbit_enable_disable_menu_item_comments', false ) ) {
			remove_menu_page( 'edit-comments.php' );
		}

		// Example for future use:
		// if ( apply_filters( 'orbit_enable_menu_item_media', false ) ) {
		//     remove_menu_page( 'upload.php' );
		// }
	}

	/**
	 * Remove toolbar items
	 *
	 * @param WP_Admin_Bar $menu The menu bar instance
	 *
	 * @return void
	 */
	public function clean_ui_toolbar_items( $menu ) {
		$menu->remove_node( 'comments' );
		$menu->remove_node( 'customize' );
		$menu->remove_node( 'dashboard' );
		$menu->remove_node( 'menus' );

		if ( apply_filters( 'orbit_enable_disable_toolbar_item_new_content', false ) ) {
			$menu->remove_node( 'new-content' );
		}

		$menu->remove_node( 'wpseo-menu' );
		$menu->remove_node( 'search' );
		$menu->remove_node( 'themes' );
		$menu->remove_node( 'view-site' );
		$menu->remove_node( 'widgets' );
		$menu->remove_node( 'wp-logo' );

		// phpcs:disable Squiz.PHP.CommentedOutCode.Found -- we want to keep these for later reference in case they are enabled
		// $menu->remove_node( 'edit' );
		// $menu->remove_node( 'site-name' );
		// $menu->remove_node( 'updates' );  // this is controlled by HideUpdates
		// $menu->remove_node( 'view' );
		// phpcs:enable
	}

	/**
	 * Remove dahboard widgets
	 */
	public function clean_ui_dashboard_widgets() {
		global $wp_meta_boxes;

		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health'] );
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press'] );

		// phpcs:disable Squiz.PHP.CommentedOutCode.Found -- we want to keep these for later reference in case they are enabled
		// unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity'] );
		// unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
		// phpcs:enable
	}

	/**
	 * Hide the CMS name from the footer
	 *
	 * @return string
	 */
	public function remove_cms_footer_text(): string {
		return '';
	}

	/**
	 * Nice logo for the login page
	 */
	public function clean_ui_logo() {
		$image = (string) apply_filters( 'orbit_login_logo_url', '' );
		$width = 250;

		if ( ! $image || $image === '' ) {
			echo '<style> .login h1 { display: none; } </style>';
			return;
		}

		$styles = [
			"background-image: url('{$image}')",
			"width: {$width}px",
			'background-position: center',
			'background-size: contain',
		];

		$css = '<style> .login h1 a { ' . implode( '; ', $styles ) . ' } </style>';

		echo wp_kses( $css, [ 'style' => [] ] );
	}
}
