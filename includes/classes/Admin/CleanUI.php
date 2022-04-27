<?php

namespace Eighteen73\Orbit\Admin;

use Eighteen73\Orbit\Singleton;

/**
 * Inspired by https://github.com/wordplate/clean-ui
 */
class CleanUI extends Singleton {

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'admin_init', [ $this, 'clean_ui_menu_items' ] );
		add_action( 'admin_bar_menu', [ $this, 'clean_ui_toolbar_items' ], 999 );
		add_action( 'wp_dashboard_setup', [ $this, 'clean_ui_dashboard_widgets' ] );
		add_action( 'login_head', [ $this, 'clean_ui_logo' ] );
	}

	/**
	 * Remove menu items
	 */
	public function clean_ui_menu_items(): void {
		remove_menu_page( 'edit-comments.php' ); // Comments
		// remove_menu_page('edit.php?post_type=page'); // Pages
		remove_menu_page( 'edit.php' );          // Posts
		// remove_menu_page( 'index.php' );         // Dashboard
		// remove_menu_page('upload.php'); // Media
	}

	/**
	 * Remove toolbar items
	 */
	public function clean_ui_toolbar_items( $menu ): void {
		$menu->remove_node( 'comments' );    // Comments
		$menu->remove_node( 'customize' );   // Customize
		$menu->remove_node( 'dashboard' );   // Dashboard
		$menu->remove_node( 'edit' );        // Edit
		$menu->remove_node( 'menus' );       // Menus
		$menu->remove_node( 'new-content' ); // New Content
		$menu->remove_node( 'search' );      // Search
		// $menu->remove_node('site-name'); // Site Name
		$menu->remove_node( 'themes' );      // Themes
		// $menu->remove_node( 'updates' );     // Updates
		$menu->remove_node( 'view-site' );   // Visit Site
		$menu->remove_node( 'view' );        // View
		$menu->remove_node( 'widgets' );     // Widgets
		$menu->remove_node( 'wp-logo' );     // WordPress Logo
	}

	/**
	 * Remove dahboard widgets
	 */
	public function clean_ui_dashboard_widgets(): void {
		global $wp_meta_boxes;

		// unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity'] );    // Activity
		// unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']); // At a Glance
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health'] ); // Site Health Status
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );       // WordPress Events and News
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press'] );   // Quick Draft
	}

	/**
	 * Nice logo for the login page
	 */
	public function clean_ui_logo(): void {
		if ( ! file_exists( get_theme_file_path( 'favicon.svg' ) ) ) {
			echo '';
		}
		$url = get_theme_file_uri( 'favicon.svg' );

		$width = 200;

		$styles = [
			sprintf( 'background-image: url(%s);', $url ),
			sprintf( 'width: %dpx;', $width ),
			'background-position: center;',
			'background-size: contain;',
		];

		echo sprintf(
			'<style> .login h1 a { %s } </style>',
			implode( '', $styles )
		);
	}
}
