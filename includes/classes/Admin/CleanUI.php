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
class CleanUI extends Singleton {


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
		$options = get_option( 'orbit_options' );

		if ( ! isset( $options['menu'] ) || ! in_array( 'dashboard', $options['menu'], true ) ) {
			remove_menu_page( 'index.php' );
		}
		if ( ! isset( $options['menu'] ) || ! in_array( 'posts', $options['menu'], true ) ) {
			remove_menu_page( 'edit.php' );
		}
		if ( ! isset( $options['menu'] ) || ! in_array( 'pages', $options['menu'], true ) ) {
			remove_menu_page( 'edit.php?post_type=page' );
		}
		if ( ! isset( $options['menu'] ) || ! in_array( 'comments', $options['menu'], true ) ) {
			remove_menu_page( 'edit-comments.php' );
		}

		// phpcs:disable Squiz.PHP.CommentedOutCode.Found -- we want to keep these for later reference in case they are enabled
		// remove_menu_page('upload.php');  // Media management
		// phpcs:enable
	}

	/**
	 * Remove toolbar items
	 *
	 * @param WP_Admin_Bar $menu The menu bar instance
	 *
	 * @return void
	 */
	public function clean_ui_toolbar_items( $menu ) {
		$options = get_option( 'orbit_options' );

		$menu->remove_node( 'comments' );
		$menu->remove_node( 'customize' );
		$menu->remove_node( 'dashboard' );
		$menu->remove_node( 'menus' );
		if ( ! isset( $options['toolbar'] ) || ! in_array( 'new_content', $options['toolbar'], true ) ) {
			$menu->remove_node( 'new-content' );
		}
		$menu->remove_node( 'search' );
		$menu->remove_node( 'themes' );
		$menu->remove_node( 'view-site' );
		$menu->remove_node( 'widgets' );
		$menu->remove_node( 'wp-logo' );

		// phpcs:disable Squiz.PHP.CommentedOutCode.Found -- we want to keep these for later reference in case they are enabled
		// $menu->remove_node( 'edit' );
		// $menu->remove_node('site-name');
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
		$options = get_option( 'orbit_options' );
		ray( $options['login_image'] );

		if ( empty( $options['login_image'] ) ) {
			echo '';
			return;
		}

		/*
		 * Restore the following if going back to an attachment
		 *
		$attachment_id = (int) $options['login_image'];

		if ( ! $attachment_id ) {
			echo '<style> .login h1 { display: none; } </style>';

			return;
		}

		$image_src = wp_get_attachment_image_src( $attachment_id, 'medium' );
		*/

		$image_src = $options['login_image'];
		$width     = 250;

		$styles = [
			"background-image: url('{$image_src[0]}')",
			"width: {$width}px",
			'background-position: center',
			'background-size: contain',
		];

		$css = '<style> .login h1 a { ' . implode( '; ', $styles ) . ' } </style>';

		echo wp_kses( $css, [ 'style' => [] ] );
	}
}
