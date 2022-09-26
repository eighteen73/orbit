<?php
/**
 * The options form for this plugin
 *
 * @package Orbit
 */

namespace Eighteen73\Orbit\Forms;

use Eighteen73\Orbit\Singleton;

/**
 * The options form for this plugin
 */
class Options {
	use Singleton;

	/**
	 * Setup module
	 */
	public function setup() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Registers Orbits main menu.
	 *
	 * @return void
	 */
	public function register_menu() {

		add_submenu_page(
			'options-general.php',
			'Orbit Settings',
			'Orbit',
			'manage_options',
			'orbit',
			[ $this, 'view' ],
			100
		);
	}

	/**
	 * Render the settings page view.
	 *
	 * @return void
	 */
	public function view() {
		if ( isset( $_POST['orbit_settings_nonce'] ) && wp_verify_nonce( $_POST['orbit_settings_nonce'], plugin_basename( __FILE__ ) ) ) {
			$this->save_fields( $_POST );
		}
		?>
			<div class="wrap">
				<h1><?php esc_attr_e( 'Orbit Settings', 'orbit' ); ?></h1>

				<form class="form" action="" method="post">
					<input
						type="hidden"
						name="orbit_settings_nonce"
						id="orbit_settings_nonce"
						value="<?php echo esc_attr( wp_create_nonce( plugin_basename( __FILE__ ) ) ); ?>"
					>

					<?php $this->section_ui(); ?>
					<?php $this->section_security(); ?>

					<?php submit_button(); ?>
				</form>

			</div>
		<?php
	}

	/**
	 * Render the UI section of the settings page.
	 *
	 * @return void
	 */
	public function section_ui() {
		?>
			<h2 class="title"><?php esc_attr_e( 'UI Cleanup', 'orbit' ); ?></h2>
			<p>Orbit automatically removes a lot of UI elements that are rarely used and can confuse some CMS users. The items below are a few that can be toggled on/off as needed.<br>
			Note this doesn't disable functionality so do not rely on it as a security feature. It only removes menu links.</p>

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php esc_attr_e( 'Disable menu items', 'orbit' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_attr_e( 'Disable menu items', 'orbit' ); ?></legend>

								<label>
									<input
										name="orbit_ui_menu_dashboard"
										type="checkbox"
										id="orbit_ui_menu_dashboard"
										value="1"
										<?php checked( 1, get_option( 'orbit_ui_menu_dashboard' ), true ); ?>
									>

									<?php esc_attr_e( 'Dashboard', 'orbit' ); ?>
								</label>

								<br>

								<label>
									<input
										name="orbit_ui_menu_posts"
										type="checkbox"
										id="orbit_ui_menu_posts"
										value="1"
										<?php checked( 1, get_option( 'orbit_ui_menu_posts' ), true ); ?>
									>

									<?php esc_attr_e( 'Posts', 'orbit' ); ?>
								</label>

								<br>

								<label>
									<input
										name="orbit_ui_menu_comments"
										type="checkbox"
										id="orbit_ui_menu_comments"
										value="1"
										<?php checked( 1, get_option( 'orbit_ui_menu_comments' ), true ); ?>
									>

									<?php esc_attr_e( 'Comments', 'orbit' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_attr_e( 'Disable toolbar items', 'orbit' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<?php esc_attr_e( 'Disable toolbar items', 'orbit' ); ?>
								</legend>

								<label>
									<input
										name="orbit_ui_toolbar_new_content"
										type="checkbox"
										id="orbit_ui_toolbar_new_content"
										value="1"
										<?php checked( 1, get_option( 'orbit_ui_toolbar_new_content' ), true ); ?>
									>

									<?php esc_attr_e( 'New content', 'orbit' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_attr_e( 'WordPress updates', 'orbit' ); ?>
						</th>
						<td>
							<label>
								<input
									name="orbit_ui_wordpress_updates"
									type="checkbox"
									id="orbit_ui_wordpress_updates"
									value="1"
									<?php checked( 1, get_option( 'orbit_ui_wordpress_updates' ), true ); ?>
								>

								<?php esc_attr_e( 'Disable WordPress update nag', 'orbit' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="orbit_ui_login_logo">
								<?php esc_attr_e( 'Login logo', 'orbit' ); ?>
							</label>
						</th>
						<td>
							<input
								id="orbit_ui_login_logo_button"
								type="button"
								class="button"
								value="<?php esc_html_e( 'Select image', 'orbit' ); ?>"
							>

							<input
								type="hidden"
								name="orbit_ui_login_logo"
								id="orbit_ui_login_logo"
								value="<?php echo esc_attr( get_option( 'orbit_ui_login_logo' ) ); ?>"
							>

							<div class="login-logo-preview-wrapper" style="margin-top: 10px;">
								<img
									id="login-logo-preview"
									src="<?php echo esc_url( wp_get_attachment_url( esc_attr( get_option( 'orbit_ui_login_logo' ) ) ) ); ?>"
									width="100"
								>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}

	/**
	 * Render the security section of the settings page.
	 *
	 * @return void
	 */
	public function section_security() {
		?>
			<h2 class="title"><?php esc_attr_e( 'Security', 'orbit' ); ?></h2>
			<p>We highy encourage all of these options to be left at the default value (checked) unless this website has very specific reason to re-enable a feature.</p>

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php esc_attr_e( 'General', 'orbit' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input
										name="orbit_security_wordpress_version"
										type="checkbox"
										id="orbit_security_wordpress_version"
										value="1"
										<?php checked( 1, get_option( 'orbit_security_wordpress_version' ), true ); ?>
									>

									<?php esc_attr_e( 'Hide WordPress version', 'orbit' ); ?>
								</label>
								<p class="description">This could act as an hint for hackers to target the website with known vulnerabilities.</p>

								<br>

								<label>
									<input
										name="orbit_security_xmlrpc"
										type="checkbox"
										id="orbit_security_xmlrpc"
										value="1"
										<?php checked( 1, get_option( 'orbit_security_xmlrpc' ), true ); ?>
									>

									<?php esc_attr_e( 'Disable XML RPC', 'orbit' ); ?>
								</label>

								<p class="description">This outdated way of communicating with WordPress leaves websites open to brute force and DDoS attacks.<br>If you must enable this, please try to limit it to necessary functioanlity and put request rate limiting in place.</p>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_attr_e( 'REST API', 'orbit' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<?php esc_attr_e( 'REST API', 'orbit' ); ?>
								</legend>

								<label for="orbit_security_rest_api_users">
									<input
										name="orbit_security_rest_api_users"
										type="checkbox"
										id="orbit_security_rest_api_users"
										value="1"
										<?php checked( 1, get_option( 'orbit_security_rest_api_users' ), true ); ?>
									>

									<?php esc_attr_e( 'Disable user endpoints in REST API', 'orbit' ); ?>
								</label>

								<p class="description">You should disable the user endpoints if not needed.<br>This helps user privacy, hides usernames from hackers, and adds a layer of protection in case some other code opens up a vulnerability in user management.</p>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}

	/**
	 * Save plugin settings.
	 *
	 * @param array $data the $_POST data.
	 *
	 * @return void
	 */
	public function save_fields( $data ) {
		update_option( 'orbit_ui_menu_dashboard', $data['orbit_ui_menu_dashboard'] ?? 0 );
		update_option( 'orbit_ui_menu_posts', $data['orbit_ui_menu_posts'] ?? 0 );
		update_option( 'orbit_ui_menu_comments', $data['orbit_ui_menu_comments'] ?? 0 );
		update_option( 'orbit_ui_toolbar_new_content', $data['orbit_ui_toolbar_new_content'] ?? 0 );
		update_option( 'orbit_security_wordpress_version', $data['orbit_security_wordpress_version'] ?? 0 );
		update_option( 'orbit_security_xmlrpc', $data['orbit_security_xmlrpc'] ?? 0 );
		update_option( 'orbit_security_rest_api_users', $data['orbit_security_rest_api_users'] ?? 0 );
		update_option( 'orbit_ui_login_logo', $data['orbit_ui_login_logo'] ?? '' );
		update_option( 'orbit_ui_wordpress_updates', $data['orbit_ui_wordpress_updates'] ?? 0 );
		?>
			<div class="updated">
				<p>Settings saved.</p>
			</div>
		<?php
	}

	/**
	 * Enqueue required scripts for settings.
	 *
	 * @param string $page The current page.
	 *
	 * @return void
	 */
	public function enqueue( $page ) {

		if ( ! $page === 'options-general.php' ) {
			return;
		}

		// Needed for media select/upload.
		wp_enqueue_media();

		// Enqueue settings JS.
		wp_enqueue_script(
			'orbit-settings',
			ORBIT_URL . 'js/settings.js',
			[ 'jquery' ],
			'0.1',
			true,
		);
	}
}
