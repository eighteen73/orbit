<?php

namespace Eighteen73\Orbit\Forms;

/**
 * This does take away some flexibility in the interest of making settings pages
 * really straightforward to set up. Things like making the page title the same as the
 * menu label.
 */
class SimpleSettings {

	/**
	 * The text domain for translations
	 *
	 * @var string
	 */
	private string $text_domain = 'default';

	/**
	 * The capability required
	 *
	 * @var string
	 */
	private string $capability = 'manage_options';

	/**
	 * The title used for the page and the menu
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * The settings group name
	 *
	 * @var string
	 */
	private string $option_group;

	/**
	 * The settings id for the group
	 *
	 * @var string
	 */
	private string $option_id;

	/**
	 * All the form sections and fields
	 *
	 * @var array
	 */
	private array $form_layout = [];

	/**
	 * Constructor
	 *
	 * @param string $option_group Settings group name
	 * @param string $title Settings page title
	 */
	public function __construct( string $option_group, string $title ) {
		$this->title = $title;
		$this->option_group =$this->sanitize( $option_group );
		$this->option_id = "{$this->option_group}_options";
		add_action( 'admin_menu', [ $this, 'add_page_callback' ] );
	}

	/**
	 * Add a new section to the form
	 *
	 * @param string $title Section title
	 * @return string
	 */
	public function add_section( string $title ): string {
		$section_id = $this->sanitize( $title );
		$this->form_layout[ $section_id ] = [
			'id' => $section_id,
			'title' => $title,
			'fields' => [],
		];
		return $section_id;
	}

	public function add_checkbox_group( string $section_id, string $field_id, string $title, array $values ) {
		$field_id = $this->sanitize( $field_id );
		$this->form_layout[ $section_id ]['fields'][] = [
			'type' => 'checkbox_group',
			'id' => $field_id,
			'title' => $title,
			'values' => $values,
		];
	}

	/**
	 * Build the settings page. The last thing to be called after all config has been added.
	 *
	 * @return void
	 */
	public function build() {
		add_action( 'admin_init', [ $this, 'build_callback' ] );
	}

	/**
	 * Convert a string into something usable as an ID
	 *
	 * @param string $string Original string
	 * @return array|string|string[]|null
	 */
	private function sanitize( string $string ) {
		$string = strtolower( sanitize_title( $string ) );
		return preg_replace( '/[^a-z0-9]+/', '_', $string );
	}

	/**
	 * Add the settings page
	 *
	 * @return void
	 */
	public function add_page_callback() {
		add_submenu_page(
			'options-general.php',
			$this->title,
			$this->title,
			$this->capability,
			$this->option_group,
			[ $this, 'page_html_callback' ]
		);
	}

	/**
	 * The callback function for the page's HTML
	 *
	 * @return void
	 */
	public function page_html_callback() {

		// check user capabilities
		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		$messages = "{$this->option_group}_message";

		// Add settings saved message with the class of "updated" if WordPress added the "settings-updated" $_GET
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				$messages,
				$messages,
				__( 'Settings Saved', $this->text_domain ),
				'updated'
			);
		}

		// Show error/update messages
		settings_errors( $messages );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// Security fields for the registered setting
				settings_fields( $this->option_group );
				// Setting sections and their fields
				do_settings_sections( $this->option_group );
				// Save settings button
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Adds a new form section to the page
	 *
	 * @return void
	 */
	public function build_callback() {
		ray()->clearAll();

		register_setting( $this->option_group, $this->option_id );

		foreach ( $this->form_layout as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				[ $this, 'render_section_callback' ],
				$this->option_group
			);

			foreach ( $section['fields'] as $field ) {
				add_settings_field(
					$field['id'],
					$field['title'],
					[ $this, 'render_checkbox_group_callback' ],
					$this->option_group,
					$section['id'],
					$field
				);
			}
		}
	}

	public function render_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', $this->text_domain ); ?></p>
		<?php
	}

	public function render_checkbox_group_callback( $args ) {
		$options = get_option( $this->option_id );
		ray( $args, $options );
		?>
		<div>
			<?php foreach ( $args['values'] as $value => $label ) : ?>
			<fieldset>
				<label for="<?php echo __($value, $this->text_domain); ?>">
					<input name="<?php echo $value; ?>" type="checkbox" id="<?php echo $value; ?>" value="<?php echo $value; ?>">
					<?php echo $label; ?>
				</label>
			</fieldset>
			<?php endforeach; ?>
		</div>
		<?php
		/*
		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'wporg_options' );
		?>
		<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			data-custom="<?php echo esc_attr( $args['wporg_custom_data'] ); ?>"
			name="wporg_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
			<option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
				<?php esc_html_e( 'red pill', $this->text_domain ) ); ?>
			</option>
			<option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
				<?php esc_html_e( 'blue pill', $this->text_domain ) ); ?>
			</option>
		</select>
		<?php
		*/
	}
}
