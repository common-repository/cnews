<?php
/**
 * CNews model
 *
 * @link       https://casperschultz.dk
 * @since      1.0.0
 *
 * @package    CNews
 * @subpackage CNews/model
 */

if ( ! class_exists( 'CNewsModel' ) ) {

	/**
	 * Handles all communication between the plugin and the WordPress Database.
	 *
	 * @since      1.0.0
	 * @package    CNews
	 * @subpackage CNews/model
	 * @author     Casper Schultz <casper@casperschultz.dk>
	 */
	class CNewsModel {


		/**
		 * The settings name for WP options.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $settings_name The cnews settings name.
		 */
		protected $settings_name;


		/**
		 * CNewsModel constructor.
		 */
		public function __construct() {
			$this->settings_name = 'cnews_settings';
		}


		/**
		 * Deletes all the settings that has been set by the plugin.
		 *
		 * @since    1.0.0
		 */
		public function delete_settings() {

			// Make sure the user has the access rights to delete these.
			if ( ! current_user_can( 'delete_plugins' ) ) {
				wp_die( __( 'You do not have access to perform this action.', 'cnews' ), __( 'Access denied', 'cnews' ) );
			}

			// Delete all the stored emails.
			delete_post_meta_by_key( 'cnews_emails' );

			// Delete all tmp stored data.
			delete_post_meta_by_key( 'cnews_tmp_email' );

			// Delete email helpers option.
			delete_option( 'cs_admin_notices' );

			// Delete all settings.
			delete_option( $this->settings_name );
		}


		/**
		 * Sets the default values for the plugin settings.
		 *
		 * @since    1.0.0
		 */
		public function set_default_settings() {

			// Provide default email address.
			$domain_name   = preg_replace( '/^www\./', '', $_SERVER['SERVER_NAME'] );
			$default_email = 'no-reply@' . $domain_name;
			$default_name  = get_bloginfo( 'name' );

			// @TODO Either move these array keys into ctrl or move them from ctrl to model.
			$default = array(
				'from_email' => $default_email,
				'from_name'  => $default_name,
			);

			add_option( $this->settings_name, $default );
		}


		/**
		 * @param string $field field name to get the options for.
		 *
		 * @return mixed|string option value.
		 *
		 * @since    1.0.0
		 */
		public function get_option_value( $field ) {
			$options = get_option( $this->settings_name );

			return isset( $options[ $field ] ) ? $options[ $field ] : '';
		}


		/**
		 * Settings name.
		 *
		 * Some WordPress functions require the settings name, like the settings API, so we
		 * have to provide a way to get this name.
		 *
		 * @return string plugin settings name.
		 *
		 * @since    1.0.0
		 */
		public function get_settings_name() {
			return $this->settings_name;
		}


		/**
		 * Retrieve the users notification status.
		 *
		 * @param int $user_id user id.
		 *
		 * @return string notification status.
		 *
		 * @since    1.0.0
		 */
		public function get_receive_notifications_status( $user_id ) {
			$status = get_the_author_meta( 'cnews_notification_status', $user_id ) !== null ? get_the_author_meta( 'cnews_notification_status', $user_id ) : '';

			return $status;
		}


		/**
		 * Sets the users notification status.
		 *
		 * @param $user_id
		 * @param $status
		 *
		 * @since    1.0.0
		 */
		public function set_receive_notification_status( $user_id, $status ) {
			update_user_meta( $user_id, 'cnews_notification_status', $status );
		}


		/**
		 * Temporarily stores the email that is being sent.
		 *
		 * @param int $post_id post id.
		 * @param string $body the email body.
		 * @param array $user_groups user groups to receive the email.
		 *
		 * @since    1.0.0
		 */
		public function tmp_store_email_data( $post_id, $body, $subject, $user_groups = array() ) {

			$data = array(
				'body'        => $body,
				'user_groups' => $user_groups,
				'subject'     => $subject,
			);

			update_post_meta( $post_id, 'cnews_tmp_email', $data );
		}


		/**
		 * Retrieves the temporarily stored email data.
		 *
		 * @param int $post_id post id.
		 *
		 * @return array of email data.
		 *
		 * @since    1.0.0
		 */
		public function get_tmp_storage( $post_id ) {

			$meta = get_post_meta( $post_id, 'cnews_tmp_email', true );

			if ( empty( $meta ) ) {
				$meta = array(
					'body'        => '',
					'user_groups' => array(),
					'subject'     => '',
				);
			}

			return $meta;
		}


		/**
		 * Deletes the tmp storage.
		 *
		 * @param int $post_id post id.
		 *
		 * @since    1.0.0
		 */
		public function delete_tmp_storage( $post_id ) {
			delete_post_meta( $post_id, 'cnews_tmp_email' );
		}


		/**
		 * Stores a sent email.
		 *
		 * @param int $post_id the post id.
		 * @param string $body the email body.
		 * @param string $subject the email subject.
		 * @param array $user_goups the user groups sent to.
		 *
		 * @since    1.0.0
		 */
		public function save_sent_email_information( $post_id, $body, $subject, $user_goups, $time ) {

			$meta = get_post_meta( $post_id, 'cnews_emails', true );

			if ( empty( $meta ) ) {

				// We need to base encode the html because encode/decode json adds \r and \n
				// I haven't been able to remove these, so for now base64 will have to do
				// I do not believe this will have any impact on the performance, because
				// I expect users to send less then 10 emails.
				$emails[] = array(
					'body'        => base64_encode( $body ),
					'subject'     => $subject,
					'user_groups' => $user_goups,
					'sent'        => $time,
				);

			} else {

				$emails = json_decode( $meta );

				// See comment above for reason behind base64.
				$email = array(
					'body'        => base64_encode( $body ),
					'subject'     => $subject,
					'user_groups' => $user_goups,
					'sent'        => $time,
				);

				$emails[] = $email;
			}

			$json = json_encode( $emails );

			update_post_meta( $post_id, 'cnews_emails', $json );

			// We don't need the tmp storage anymore, so we delete it.
			$this->delete_tmp_storage( $post_id );
		}


		/**
		 * Retrieves the emails sent out from the post.
		 *
		 * @param int $post_id post id to get meta from.
		 *
		 * @return array of emails sent - empty if none has been sent.
		 *
		 * @since    1.0.0
		 */
		public function get_email_information( $post_id ) {

			$meta   = get_post_meta( $post_id, 'cnews_emails', true );
			$emails = array();

			if ( ! empty( $meta ) ) {
				$emails = json_decode( $meta );

				foreach ( $emails as $email ) {
					$email->body = base64_decode( $email->body );
				}
			}

			return $emails;
		}


		/**
		 * Fetches all the users that are to receive email notifications.
		 *
		 * @param array $user_groups the groups that are to receive notifications.
		 *
		 * @return array of user emails.
		 *
		 * @since    1.0.0
		 */
		public function get_receivers( $user_groups ) {

			$users = array();

			$args = array(
				'role__in'     => $user_groups,
				'meta_key'     => 'cnews_notification_status',
				'meta_value'   => '1',
				'meta_compare' => '=',

			);

			$query = new WP_User_Query( $args );

			if ( ! empty( $query->get_results() ) ) {
				foreach ( $query->get_results() as $user ) {
					array_push( $users, $user->data->user_email );
				}
			}

			return $users;
		}


		/**
		 * Returns all user roles in WordPress.
		 *
		 * @return array of user roles.
		 *
		 * @since    1.0.0
		 */
		public function get_user_roles() {
			global $wp_roles;

			return $wp_roles->get_names();
		}
	}
}
