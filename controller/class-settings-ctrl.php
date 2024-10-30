<?php
/**
 * CNews controller
 *
 * @link       https://casperschultz.dk
 * @since      1.0.0
 *
 * @package    CNews
 * @subpackage CNews/controller
 */

if ( ! class_exists( 'SettingsCtrl' ) ) {

	/**
	 * Handles business logic for settings of the plugin.
	 *
	 * @since      1.0.0
	 * @package    CNews
	 * @subpackage CNews/controller
	 * @author     Casper Schultz <casper@casperschultz.dk>
	 */
	class SettingsCtrl {

		/**
		 * The model used to communicate with data.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $plugin_name The cnews model.
		 */
		protected $model;


		/**
		 * SettingsCtrl constructor.
		 *
		 * @since    1.0.0
		 */
		function __construct() {
			$this->model = new CNewsModel();
		}


		/**
		 * Adds actions to WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		}


		/**
		 * Adds settings page to admin menu.
		 *
		 * @since    1.0.0
		 */
		public function add_settings_page() {

			// Only users that can manage options should be able to access the settings page.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$page_title = 'CNews settings';
			$menu_title = 'CNews';
			$capability = 'manage_options';
			$menu_slug  = 'cnews-settings';
			$callback   = array( $this, 'render_options_page' );

			add_options_page(
				$page_title,
				$menu_title,
				$capability,
				$menu_slug,
				$callback
			);
		}


		/**
		 * Renders option page.
		 *
		 * @since    1.0.0
		 */
		public function render_options_page() {
			?>
			<div class='wrap'>
				<h2>Settings</h2>
				<form method='post' action='options.php'>
					<?php

					// @TODO can this be done smarter?
					settings_fields( $this->model->get_settings_name() );
					do_settings_sections( 'cnews-settings-page' );
					?>
					<p class='submit'>
						<input name='submit' type='submit' id='submit' class='button-primary' value='Save'/>
					</p>
				</form>
			</div>
			<?php
		}


		/**
		 * Registers the settings used for the content boxes.
		 *
		 * @since    1.0.0
		 */
		public function register_settings() {

			// Only users that can manage options should be able to handle setings.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$settings_name = $this->model->get_settings_name();

			$settings_section = $settings_name;
			$option_name      = $settings_name;
			register_setting( $settings_section, $option_name );

			$this->register_email_settings( $settings_name );
			$this->register_posttype_settings( $settings_name );
		}


		/**
		 * Registers the settings section for the posttypes.
		 *
		 * @param string $settings_name the plugins option name.
		 */
		private function register_posttype_settings( $settings_name ) {

			$section          = 'cnews_posttype_settings';
			$section_title    = __( 'CNews posttype settings', 'cnews' );
			$section_callback = array( $this, 'render_posttype_settings_section' );
			$page             = 'cnews-settings-page';

			add_settings_section( $section, $section_title, $section_callback, $page );

			$post_types = get_post_types();
			$exlude     = array(
				'attachment',
				'revision',
				'nav_menu_item',
			);

			foreach ( $post_types as $type ) {

				if ( ! in_array( $type, $exlude ) ) {
					add_settings_field( $type, ucfirst( $type ), array(
						$this,
						'render_checkbox_field',
					), $page, $section, array( 'posttype' => $type, 'settings_name' => $settings_name ) );
				}
			}
		}


		/**
		 * Registers email settings.
		 *
		 * @since    1.0.0
		 */
		private function register_email_settings( $settings_name ) {

			$section          = 'cnews_email_settings';
			$section_title    = __( 'CNews email settings', 'cnews' );
			$section_callback = array( $this, 'render_email_settings_section' );
			$page             = 'cnews-settings-page';
			$field_callback   = array( $this, 'render_input_field' );

			add_settings_section( $section, $section_title, $section_callback, $page );

			// Lets display the default settings for the user.
			$domain_name  = preg_replace( '/^www\./', '', $_SERVER['SERVER_NAME'] );
			$default_from = 'no-reply@' . $domain_name;
			$default_name = get_bloginfo( 'name' );


			$setting_fields = array(

				array(
					'id'    => 'from_email',
					'title' => 'From email',
					'args'  => array(
						'settings_name' => $settings_name,
						'field_id'      => 'from_email',
						'desc'          => __( 'Leave empty to use the default email address', 'cnews' ) . ': ' . $default_from,
					),
				),
				array(
					'id'    => 'from_name',
					'title' => 'From name',
					'args'  => array(
						'settings_name' => $settings_name,
						'field_id'      => 'from_name',
						'desc'          => __( 'Leave empty to use the default name', 'cnews' ) . ': ' . $default_name,
					),
				),
			);


			foreach ( $setting_fields as $field ) {
				add_settings_field( $field['id'], $field['title'], $field_callback, $page, $section, $field['args'] );
			}
		}


		/**
		 * Displays the section description.
		 *
		 * @since 1.0.0
		 */
		public function render_posttype_settings_section() {
			echo __( 'Choose the post types that you would like to be able to send notifications from.', 'cnews' );
		}


		/**
		 * Displays the section description.
		 *
		 * @since    1.0.0
		 */
		public function render_email_settings_section() {
			echo __( 'Set name and email address that will be shown as From: when sending out emails.', 'cnews' );
		}


		/**
		 * Displays checkboxes.
		 *
		 * @param array $args arguments.
		 */
		public function render_checkbox_field( $args ) {

			$checked = $this->model->get_option_value( $args['posttype'] ) != '' ? 'checked' : '';

			?>
			<label><input type="checkbox" name="<?php echo $args['settings_name'] ?>[<?php echo $args['posttype'] ?>]"
			       value="1" <?php echo $checked ?>> <?php echo ucfirst( $args['posttype'] ) ?></label>
			<?
		}


		/**
		 * Displays input fields.
		 *
		 * @param array $args arguments.
		 *
		 * @since    1.0.0
		 */
		public function render_input_field( $args ) {

			$value = $this->model->get_option_value( $args['settings_name'] );

			?>
			<input type="text" name="<?php echo $args['settings_name'] ?>[<?php echo $args['field_id'] ?>]"
			       class="regular-text"
			       value="<?php echo isset( $_POST[ $args['settings_name'] ] ) ? $_POST[ $args['settings_name'] ] : $value ?>">

			<?php

			if ( isset( $args['desc'] ) && ! empty( $args['desc'] ) ) {
				echo '<p><small>' . $args['desc'] . '</small></p>';
			}

		}
	}

}
