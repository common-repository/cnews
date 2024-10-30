<?php
/**
 * CNews Admin Notice Helper.
 *
 * This notice helper is mainly inspired by Ian Dunns Admin Notice Helper but rewritten to fit the plugin needs.
 * - https://github.com/iandunn/admin-notice-helper
 *
 * @link       https://casperschultz.dk
 * @since      1.0.0
 *
 * @package    CNews
 * @subpackage CNews/includes
 */

if ( ! class_exists( 'AdminNoticeUtil' ) ) {

	/**
	 * Handles sending out emails and email settings..
	 *
	 * @since      1.0.0
	 * @package    CNews
	 * @subpackage CNews/includes
	 * @author     Casper Schultz <casper@casperschultz.dk>
	 */
	class AdminNoticeUtil {

		/**
		 * Singleton instance.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      AdminNoticeUtil $receivers singlton instance of this class.
		 */
		protected static $instance;


		/**
		 * Notices stored.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      array $receivers Admin notices.
		 */
		protected $notices;


		/**
		 * Notices status.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      bool $receivers status for having displayed the notices.
		 */
		protected $notices_were_updated;


		/**
		 * AdminNoticeUtil constructor.
		 *
		 * @since    1.0.0
		 */
		protected function __construct() {

			// needs to run before other plugin's init callbacks so that they can enqueue messages in their init callbacks.
			add_action( 'init', array( $this, 'init' ), 9 );
			add_action( 'admin_notices', array( $this, 'print_notices' ) );
			add_action( 'shutdown', array( $this, 'shutdown' ) );
		}


		/**
		 * Singleton instance.
		 *
		 * @return AdminNoticeUtil
		 *
		 * @since    1.0.0
		 */
		public static function get_singleton() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new AdminNoticeUtil();
			}

			return self::$instance;
		}


		/**
		 * Init variables.
		 *
		 * @since    1.0.0
		 */
		public function init() {
			$default_notices            = array( 'success' => array(), 'error' => array() );
			$this->notices              = array_merge( $default_notices, get_option( 'cs_admin_notices', array() ) );
			$this->notices_were_updated = false;
		}


		/**
		 * Queues up a message to be displayed to the user.
		 *
		 * @param string $message The text to show the user
		 * @param string $type 'success' for a success or notification message, or 'error' for an error message
		 *
		 * @since    1.0.0
		 */
		public function enqueue( $message, $type = 'success' ) {

			if ( in_array( $message, array_values( $this->notices[ $type ] ) ) ) {
				return;
			}

			$this->notices[ $type ][]   = (string) apply_filters( 'cs_enqueue_message', $message );
			$this->notices_were_updated = true;
		}


		/**
		 * Displays updates and errors.
		 *
		 * @since    1.0.0
		 */
		public function print_notices() {
			foreach ( array( 'success', 'error' ) as $type ) {
				if ( count( $this->notices[ $type ] ) ) {
					$class = 'success' == $type ? 'success' : 'error';

					// Print out the messages.
					echo '<div class="notice notice-' . $class . ' is-dismissible">';
					foreach ( $this->notices[ $type ] as $notice ) {
						echo '<p>' . wp_kses( $notice, wp_kses_allowed_html( 'post' ) ) . '</p>';
					}
					echo '</div>';

					$this->notices[ $type ]     = array();
					$this->notices_were_updated = true;
				}
			}
		}


		/**
		 * Writes notices to the database.
		 *
		 * @since    1.0.0
		 */
		public function shutdown() {
			if ( $this->notices_were_updated ) {
				update_option( 'cs_admin_notices', $this->notices );
			}
		}
	}

	AdminNoticeUtil::get_singleton();

	if ( ! function_exists( 'add_notice' ) ) {
		function add_notice( $message, $type = 'success' ) {
			AdminNoticeUtil::get_singleton()->enqueue( $message, $type );
		}
	}
}