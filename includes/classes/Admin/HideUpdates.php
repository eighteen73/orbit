<?php

namespace Orbit\Admin;

use Orbit\Singleton;

/**
 * Inspired by https://wordpress.org/plugins/hide-updates/
 */
class HideUpdates extends Singleton {

	private bool $enabled = false;

	/**
	 * Setup module
	 */
	public function setup() {
		$this->enabled = $this->is_enabled();
		add_action( 'admin_menu', [ $this, 'hide_updates_submenu_page' ] );
		add_action( 'admin_bar_menu', [ $this, 'hide_updates_toolbar_item' ], 999 );
		add_action( 'admin_init', [ $this, 'block_admin_pages' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_plugin_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_plugin_styles' ] );
	}

	public function is_enabled(): bool {

		// Plugin setting
		$settings = get_option( 'orbit_settings' );
		if ( ! is_array( $settings ) || ! isset( $settings['hide_updates'] ) ) {
			return false;
		}
		if ( $settings['hide_updates'] !== true ) {
			return false;
		}

		// It's enabled, so check the user's role
		$current_user       = wp_get_current_user();
		$current_user_roles = $current_user->roles;
		if ( in_array( 'administrator', $current_user_roles ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Remove submenu pages for users not allowed to see WordPress updates.
	 */
	public function hide_updates_submenu_page() {
		if ( $this->enabled ) {
			remove_submenu_page( 'index.php', 'update-core.php' );
		}
	}

	/**
	 * Remove submenu pages for users not allowed to see WordPress updates.
	 */
	public function hide_updates_toolbar_item( $menu ) {
		if ( $this->enabled ) {
			$menu->remove_node( 'updates' );
		}
	}

	/**
	 * Block access to certain admin pages for users not allowed to see WordPress updates.
	 */
	public function block_admin_pages() {

		if ( $this->enabled ) {
			global $pagenow;

			$blocked_admin_pages = [
				'update-core.php',
				'plugins.php?plugin_status=upgrade',
			];

			$block_current_page = false;

			foreach ( $blocked_admin_pages as $block_admin_page ) {
				$block_admin_page = explode( '?', $block_admin_page );

				if ( $pagenow == $block_admin_page[0] ) {
					$block_current_page = true;
				}

				if ( isset( $block_admin_page[1] ) ) {
					parse_str( $block_admin_page[1], $query_params );

					foreach ( $query_params as $key => $value ) {
						if ( isset( $_GET[ $key ] ) && $_GET[ $key ] == $value ) {
							$block_current_page = true;
							break;
						} else {
							$block_current_page = false;
						}
					}
				}

				if ( $block_current_page ) {
					wp_safe_redirect( admin_url( '/' ) );
					exit;
				}
			}
		}
	}

	/**
	 * Enqueue plugin stylesheet to hide elements for users not allowed to see WordPress updates.
	 */
	public function enqueue_plugin_styles() {
		if ( $this->enabled ) {
			wp_enqueue_style( 'hide_updates_css', plugins_url( 'hide-updates.css', __FILE__ ) );
		}
	}

}
