<?php
/**
 * Plugin controller.
 *
 * @link       https://casperschultz.dk
 * @since      1.0.0
 *
 * @package    CNews
 * @subpackage CNews/controller
 */

if ( ! class_exists( 'PluginCtrl' ) ) {

	/**
	 * Handles business logic for the plugin.
	 *
	 * - Inits the plugin.
	 * - plugin activation/deactivation logic.
	 * - plugin uninstall logic.
	 * - loads dependencies.
	 * - loads scripts and styles.
	 *
	 * @since      1.0.0
	 * @package    CNews
	 * @subpackage CNews/controller
	 * @author     Casper Schultz <casper@casperschultz.dk>
	 */
	class PluginCtrl {

		/**
		 * The plugin version.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $plugin_version The plugin version.
		 */
		protected $plugin_version;


		/**
		 * CNewsCtrl constructor.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			$this->load_dependencies();
			$this->set_locale();
			$this->plugin_version = '1.0.0';
		}


		/**
		 * Runs the plugin.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$cnews_ctrl = new CNewsCtrl();
			$cnews_ctrl->run();

			$settings_ctrl = new SettingsCtrl();
			$settings_ctrl->run();

			$user_ctrl = new UserCtrl();
			$user_ctrl->run();

			// Enqueue scripts and styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
		}


		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Clogin_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {

			$plugin_i18n = new CNewsI18n();

			add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
		}


		/**
		 * Loads plugin dependencies.
		 *
		 * @since    1.0.0
		 */
		private function load_dependencies() {

			// CNews model.
			require_once WP_PLUGIN_DIR . '/cnews/model/class-cnews-model.php';

			// Settings ctrl.
			require_once WP_PLUGIN_DIR . '/cnews/controller/class-settings-ctrl.php';

			// CNews ctrl.
			require_once WP_PLUGIN_DIR . '/cnews/controller/class-cnews-ctrl.php';

			// User ctrl.
			require_once WP_PLUGIN_DIR . '/cnews/controller/class-user-ctrl.php';

			// Email utility class.
			require_once WP_PLUGIN_DIR . '/cnews/util/class-email-utility.php';

			// Admin notice utility class.
			require_once WP_PLUGIN_DIR . '/cnews/util/class-admin-notice-utility.php';

			// CNews model.
			require_once WP_PLUGIN_DIR . '/cnews/util/class-cnews-i18n.php';
		}


		/**
		 * Register the scripts and stylesheets.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts_and_styles( $hook ) {

			// Pages to load the scripts and stylesheets on.
			$pages = array(
				'post.php',
				'post-new.php',
			);

			if ( ! in_array( $hook, $pages ) ) {
				return;
			}

			$base_path = plugins_url() . '/cnews/assets/';

			wp_enqueue_style( 'cnews-styles', $base_path . 'css/admin-styles.css', array(), $this->plugin_version, 'all' );
			wp_enqueue_script( 'cnews-js', $base_path . 'js/admin-js.js', array( 'jquery' ), $this->plugin_version, false );
		}


		/**
		 * Runs on plugin activation.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {

			// Need the model to set default settings.
			require_once WP_PLUGIN_DIR . '/cnews/model/class-cnews-model.php';

			$model = new CNewsModel();
			$model->set_default_settings();
		}


		/**
		 * Runs on plugin deactivation.
		 *
		 * @since    1.0.0
		 */
		public static function deactivate() {
			// Unused for now.
		}


		/**
		 * Runs when the plugin is deleted.
		 *
		 * @since    1.0.0
		 */
		public static function uninstall() {

			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			// Need the model to delete all settings.
			require_once WP_PLUGIN_DIR . '/cnews/model/class-cnews-model.php';

			$model = new CNewsModel();
			$model->delete_settings();
		}
	}

}
