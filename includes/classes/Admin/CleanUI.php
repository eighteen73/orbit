<?php
/**
 * Simplify the CMS UI by removing unwanted elements
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Admin;

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
		if ( ! apply_filters( 'orbit_enable_menu_item_dashboard', true ) ) {
			remove_menu_page( 'index.php' );
		}
		if ( ! apply_filters( 'orbit_enable_menu_item_posts', true ) ) {
			remove_menu_page( 'edit.php' );
		}
		if ( ! apply_filters( 'orbit_enable_menu_item_comments', false ) ) {
			remove_menu_page( 'edit-comments.php' );
		}
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

		if ( ! apply_filters( 'orbit_enable_toolbar_item_new_content', true ) ) {
			$menu->remove_node( 'new-content' );
		}

		$menu->remove_node( 'wpseo-menu' );
		$menu->remove_node( 'search' );
		$menu->remove_node( 'themes' );
		$menu->remove_node( 'view-site' );
		$menu->remove_node( 'widgets' );
		$menu->remove_node( 'wp-logo' );
	}

	/**
	 * Remove dahboard widgets
	 */
	public function clean_ui_dashboard_widgets() {
		global $wp_meta_boxes;

		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health'] );
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press'] );
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
		if ( ! apply_filters( 'orbit_enable_login_logo', true ) ) {
			return;
		}

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
