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

if ( ! class_exists( 'CNewsCtrl' ) ) {

	/**
	 * Handles business logic for rendering forms and sending emails.
	 *
	 * @since      1.0.0
	 * @package    CNews
	 * @subpackage CNews/controller
	 * @author     Casper Schultz <casper@casperschultz.dk>
	 */
	class CNewsCtrl {

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

			// Add meta boxes.
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

			// Process email form.
			add_action( 'save_post', array( $this, 'send_emails' ) );

			// Process email result.
			add_action( 'wp_mail_failed', array( $this, 'send_emails_failed' ) );
		}


		/**
		 * Processes the form for sending emails.
		 *
		 * @TODO validate input
		 *
		 * @param int $post_id post id.
		 *
		 * @since    1.0.0
		 */
		public function send_emails( $post_id ) {

			// We only send out emails, if the user presses our own submit button.
			if ( ! isset( $_POST['cnews_submit'] ) ) {
				return;
			}

			// Check form submission is valid.
			if ( ! isset( $_POST['cnews_notification_nonce'] ) || ! wp_verify_nonce( $_POST['cnews_notification_nonce'], 'cnews_send_notification' ) ) {
				return;
			}

			if ( ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) ) {
				return;
			}

			// Only users that can publish content are allowed to send out mails.
			if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'publish_pages' ) ) {
				return;
			}

			$msg         = isset( $_POST['cnews_email_body'] ) ? $_POST['cnews_email_body'] : '';
			$user_groups = isset ( $_POST['cnews_user_groups'] ) ? $_POST['cnews_user_groups'] : array();
			$subject     = isset( $_POST['cnews_email_subject'] ) ? sanitize_text_field( strip_tags( $_POST['cnews_email_subject'] ) ) : '';

			// We need to store the information in something else then $ global, because
			// WordPress redirects when saving the post.
			$this->model->tmp_store_email_data( $post_id, $msg, $subject, $user_groups );

			// Make sure the user wants to send an email.
			if ( ! isset( $_POST['cnews_notification_confirm'] ) || '1' !== $_POST['cnews_notification_confirm'] ) {

				if ( ! empty( $msg ) && ! empty( $user_groups ) ) {
					add_notice( __( 'You have composed an email, but you have have not confirmed that it is to be sent.', 'cnews' ), 'error' );
				}

				return;
			}

			if ( empty( $subject ) ) {
				add_notice( __( 'You have to add a subject.', 'cnews' ), 'error' );

				return;
			}

			if ( 5 > strlen( $subject ) || 78 < strlen( $subject ) ) {
				add_notice( __( 'The subject must be between 5 and 78 characters long.', 'cnews' ), 'error' );

				return;
			}

			if ( empty( $msg ) ) {
				add_notice( __( 'You have to type a message in order to send emails.', 'cnews' ), 'error' );

				return;
			}

			if ( empty( $user_groups ) ) {
				add_notice( __( 'Your emails has NOT been sent out because you did not select any user groups to send to.', 'cnews' ), 'error' );

				return;
			}

			// Get the email addresses.
			$receivers = $this->model->get_receivers( $user_groups );
			// @TODO move these string values into model.
			$from      = $this->model->get_option_value( 'from_email' );
			$from_name = $this->model->get_option_value( 'from_name' );

			$emailer = new EmailUtil( $receivers, $msg, $subject, $from, $from_name );
			$status = $emailer->send_emails();

			// If successful we show the message. If it isn't the wp_mail_failed action handles the error output.
			if ( $status ) {

				// Save the email that has been sent.
				// @TODO is it possible to sanitize wysiwig input?
				$this->model->save_sent_email_information( $post_id, $_POST['cnews_email_body'], $subject, $user_groups, time() );

				// Display the success msg.
				add_notice( __( 'Your emails are being sent out now.', 'cnews' ), 'success' );
			}
 		}


		/**
		 * Displays any errors.
		 *
		 * @param $wp_error
		 */
 		public function send_emails_failed( $wp_error ) {
		    add_notice( __( 'Your email could not be sent because of the following error', 'cnews' ) . ': ' . $wp_error->get_error_message(), 'error' );
	    }


		/**
		 * Renders emails sent meta box.
		 *
		 * @param WP_Post $post current post being viewed.
		 * @param array $args arguments like template.
		 *
		 * @since    1.0.0
		 */
		public function render_emails_sent_view( $post, $args ) {

			$data = array(
				'emails' => $this->model->get_email_information( $post->ID ),
			);

			$this->render_html_template( $args['args']['template'], $data );
		}


		/**
		 * Renders email form.
		 *
		 * @param WP_Post $post
		 * @param array $args
		 *
		 * @since    1.0.0
		 */
		public function render_email_form( $post, $args ) {

			$data = array(
				'email'      => $this->model->get_tmp_storage( $post->ID ),
				'user_roles' => $this->model->get_user_roles(),
			);

			$this->render_html_template( $args['args']['template'], $data );
		}


		/**
		 * Renders html template.
		 *
		 * @TODO Validate that the template is valid - this is not secure.
		 * @TODO This is duplicated in CNewsCtrl.
		 *
		 * @param string $template template to render.
		 * @param array $data data to pass to the template.
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


		/**
		 * Add meta boxes..
		 *
		 * @since    1.0.0
		 */
		function add_meta_boxes() {

			// Only users that can publish content are allowed to send out mails.
			if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'publish_pages' ) ) {
				return;
			}

			// @TODO Refactor this mess.
			// Quick and very dirty way...
			$active_types = get_post_types();
			$post_types   = array();

			foreach ( $active_types as $type ) {
				$status = $this->model->get_option_value( $type );

				if ( ! empty( $status ) ) {
					array_push( $post_types, $type );
				}
			}

			$user_fields = array(

				array(
					'id'       => 'cnews_notifications_email',
					'title'    => __( 'Send email notification', 'cnews' ),
					'callback' => array( $this, 'render_email_form' ),
					'args'     => array( 'template' => 'view-news-email-form-meta-box' ),
				),

				array(
					'id'       => 'cnews_notifications_emails_sent',
					'title'    => __( 'Emails sent out', 'cnews' ),
					'callback' => array( $this, 'render_emails_sent_view' ),
					'args'     => array( 'template' => 'view-emails-sent-meta-box' ),
				),
			);

			foreach ( $user_fields as $field ) {
				add_meta_box( $field['id'], $field['title'], $field['callback'], $post_types, 'normal', 'high', $field['args'] );
			}
		}
	}

}
