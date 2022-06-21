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
		$this->title        = $title;
		$this->option_group = $this->sanitize( $option_group );
		$this->option_id    = "{$this->option_group}_options";
		add_action( 'admin_menu', [ $this, 'add_page_callback' ] );
	}

	/**
	 * Add a new section to the form
	 *
	 * @param string $title Section title
	 *
	 * @return string
	 */
	public function add_section( string $title, $intro = null ): string {
		$section_id                       = $this->sanitize( $title );
		$this->form_layout[ $section_id ] = [
			'id'     => $section_id,
			'title'  => $title,
			'intro'  => $intro ?? null,
			'fields' => [],
		];

		return $section_id;
	}

	public function add_text_field( string $section_id, string $field_id, string $title ) {
		$field_id                                     = $this->sanitize( $field_id );
		$this->form_layout[ $section_id ]['fields'][] = [
			'type'  => 'text',
			'id'    => $field_id,
			'title' => $title,
		];
	}

	public function add_checkbox_group( string $section_id, string $field_id, string $title, array $values ) {
		$field_id                                     = $this->sanitize( $field_id );
		$this->form_layout[ $section_id ]['fields'][] = [
			'type'   => 'checkbox_group',
			'id'     => $field_id,
			'title'  => $title,
			'values' => $values,
		];
	}

	public function add_radio_group( string $section_id, string $field_id, string $title, array $values ) {
		$field_id                                     = $this->sanitize( $field_id );
		$this->form_layout[ $section_id ]['fields'][] = [
			'type'   => 'radio_group',
			'id'     => $field_id,
			'title'  => $title,
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
	 *
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
		register_setting( $this->option_group, $this->option_id );

		foreach ( $this->form_layout as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				fn() => $this->render_section_callback( $section['intro'] ),
				$this->option_group
			);

			foreach ( $section['fields'] as $field ) {
				switch ( $field['type'] ) {
					case 'text':
						add_settings_field(
							$field['id'],
							$field['title'],
							[ $this, 'render_text_callback' ],
							$this->option_group,
							$section['id'],
							$field
						);
						break;
					case 'checkbox_group':
						add_settings_field(
							$field['id'],
							$field['title'],
							[ $this, 'render_checkbox_group_callback' ],
							$this->option_group,
							$section['id'],
							$field
						);
						break;
					case 'radio_group':
						add_settings_field(
							$field['id'],
							$field['title'],
							[ $this, 'render_radio_group_callback' ],
							$this->option_group,
							$section['id'],
							$field
						);
						break;
				}
			}
		}
	}

	/**
	 * Form section
	 *
	 * @param array|string $intro intro text
	 * @return void
	 */
	public function render_section_callback( $intro ) {
		if ( ! $intro ) {
			return;
		}
		$intro = is_array( $intro ) ? $intro : [ $intro ];
		foreach ( $intro as $paragraph ) {
			?>
			<p><?php esc_html_e( $paragraph, $this->text_domain ); ?></p>
			<?php
		}
	}

	/**
	 * Text field
	 *
	 * @param array $args Field arguments
	 * @return void
	 */
	public function render_text_callback( array $args ) {
		$setting = get_option( 'orbit_options' );
		?>
		<div>
			<fieldset>
				<input type="text"
					   name="orbit_options[<?php echo $args['id']; ?>]"
					   value="<?php echo isset( $setting[ $args['id'] ] ) ? esc_attr( $setting[ $args['id'] ] ) : ''; ?>"
				>
				<?php if ( isset( $args['hint'] ) && $args['hint'] ) : ?>
					<p class="description">
						<?php echo esc_html( $args['hint'] ); ?>
					</p>
				<?php endif; ?>
			</fieldset>
		</div>
		<?php
	}

	/**
	 * Checkbox group
	 *
	 * @param array $args Field arguments
	 * @return void
	 */
	public function render_checkbox_group_callback( array $args ) {
		$setting = get_option( 'orbit_options' );
		?>
		<div>
			<?php foreach ( $args['values'] as $value => $info ) : ?>
				<fieldset>
					<label>
						<input type="checkbox"
							   name="orbit_options[<?php echo $args['id']; ?>][]"
							   value="<?php echo $value; ?>"
							   <?php if ( isset( $setting[ $args['id'] ] ) && in_array( $value, $setting[ $args['id'] ] ) ) { echo 'checked'; } ?>
						>
						<?php echo esc_html( $info['label'] ); ?>
					</label>
					<?php if ( isset( $info['hint'] ) && $info['hint'] ) : ?>
						<p class="description">
							<?php echo esc_html( $info['hint'] ); ?>
						</p>
					<?php endif; ?>
				</fieldset>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Radio group
	 *
	 * @param array $args Field arguments
	 * @return void
	 */
	public function render_radio_group_callback( array $args ) {
		$setting = get_option( 'orbit_options' );
		?>
		<div>
			<?php foreach ( $args['values'] as $value => $info ) : ?>
				<fieldset>
					<label>
						<input type="radio"
							   name="orbit_options[<?php echo $args['id']; ?>]"
							   value="<?php echo $value; ?>"
							<?php if ( isset( $setting[ $args['id'] ] ) && $setting[ $args['id'] ] === $value ) { echo 'checked'; } ?>
						>
						<?php echo esc_html( $info['label'] ); ?>
					</label>
					<?php if ( isset( $info['hint'] ) && $info['hint'] ) : ?>
						<p class="description">
							<?php echo esc_html( $info['hint'] ); ?>
						</p>
					<?php endif; ?>
				</fieldset>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
