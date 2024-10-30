<?php
/**
 * UserCtrl controller
 *
 * @link       https://casperschultz.dk
 * @since      1.0.0
 *
 * @package    CNews
 * @subpackage CNews/controller
 */


if ( ! class_exists( 'UserCtrl' ) ) {

	/**
	 * Handles business logic for user information.
	 *
	 * @since      1.0.0
	 * @package    CNews
	 * @subpackage CNews/controller
	 * @author     Casper Schultz <casper@casperschultz.dk>
	 */
	class UserCtrl {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $plugin_name The cnews model.
		 */
		protected $model;


		/**
		 * CNewsCtrl constructor.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			$this->model = new CNewsModel();
		}


		/**
		 * Runs the plugin.
		 *
		 * @since    1.0.0
		 */
		public function run() {

			// Add the user field.
			add_action( 'show_user_profile', array( $this, 'render_user_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'render_user_fields' ) );
			add_action( 'user_new_form', array( $this, 'render_user_fields' ) );

			// Save user fields.
			add_action( 'personal_options_update', array( $this, 'save_user_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_user_fields' ) );
			add_action( 'user_register', array( $this, 'save_user_fields' ), 10, 1 );
		}


		/**
		 * Saves user fields.
		 *
		 * @param int $user_id updates the users status for receiving notifications.
		 *
		 * @TODO Should we check for permissions here?
		 *
		 * @since    1.0.0
		 */
		function save_user_fields( $user_id ) {
			$status = isset( $_POST['cnews_receive_notifications'] ) ? 1 : 0;
			$this->model->set_receive_notification_status( $user_id, $status );
		}


		/**
		 * Renders the field for receive notifications status.
		 *
		 * @param WP_User $user WordPress current user object.
		 *
		 * @since    1.0.0
		 */
		public function render_user_fields( $user ) {

			$status = 'add-new-user' === $user ? '' : $this->model->get_receive_notifications_status( $user->ID );

			$data = array(
				'status' => $status,
			);

			$this->render_html_template( 'view-user-profile-field', $data );
		}


		/**
		 * Renders html template.
		 *
		 * @TODO Validate that the template is valid - this is not secure.
		 * @TODO This is duplicated in CNewsCtrl.
		 *
		 * @param $template
		 * @param array $data
		 *
		 * @since    1.0.0
		 */
		public function render_html_template( $template, $data = array() ) {
			ob_start();
			require_once WP_PLUGIN_DIR . '/cnews/view/' . $template . '.php';
			$html = ob_get_contents();
			ob_clean();
			echo $html;
		}
	}

}
