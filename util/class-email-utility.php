<?php
/**
 * CNews Email Helper.
 *
 * @link       https://casperschultz.dk
 * @since      1.0.0
 *
 * @package    CNews
 * @subpackage CNews/includes
 */

if ( ! class_exists( 'EmailUtil' ) ) {

	/**
	 * Handles sending out emails and email settings..
	 *
	 * @since      1.0.0
	 * @package    CNews
	 * @subpackage CNews/includes
	 * @author     Casper Schultz <casper@casperschultz.dk>
	 */
	class EmailUtil {

		/**
		 * The receivers of the emails.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      array $receivers the receivers of the email.
		 */
		protected $receivers;


		/**
		 * The msg to be sent out.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $msg the email message.
		 */
		protected $msg;


		/**
		 * The subject of the email.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $subject Subject of the email.
		 */
		protected $subject;


		/**
		 * The from email.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $from the from email address.
		 */
		protected $from;


		/**
		 * The name provided in the email header.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $from_name a name like Casper Schultz.
		 */
		protected $from_name;


		/**
		 * CNewsCtrl constructor.
		 *
		 * Sets the default values.
		 *
		 * @since    1.0.0
		 */
		public function __construct( $to, $msg, $subject, $from = '', $from_name = '' ) {

			$this->receivers = $to;
			$this->msg       = $msg;
			$this->subject   = $subject;
			$this->from      = $from;
			$this->from_name = $from_name;

			if ( empty( $from ) || ! is_email( $from ) ) {
				$domain_name = preg_replace( '/^www\./', '', $_SERVER['SERVER_NAME'] );
				$this->from  = 'no-reply@' . $domain_name;
			}

			if ( empty( $from_name ) ) {
				$this->from_name = get_bloginfo( 'name' );
			}
		}


		/**
		 * Sets the from email address.
		 *
		 * @param string $original_email the original email address provided by WordPress.
		 *
		 * @return string the from email address.
		 *
		 * @since    1.0.0
		 */
		function set_wp_mail_from( $original_email ) {
			return $this->from;
		}


		/**
		 * Sets a new name for wp_mail.
		 *
		 * @param string $original_from the original from name.
		 *
		 * @return string the blog name.
		 *
		 * @since    1.0.0
		 */
		function custom_wp_mail_from_name( $original_from ) {
			return $this->from_name;
		}


		/**
		 * Sets the Wordpress mail function to send html emails.
		 *
		 * @param string $content_type current content type.
		 *
		 * @return string new content type.
		 *
		 * @since    1.0.0
		 */
		public function set_html_mail_content_type( $content_type ) {
			return 'text/html';
		}


		/**
		 * Sends out the emails.
		 *
		 * We are using WP_Mail, there are limits for what mail() can handle, but by
		 * using wp_mail the user has the option to plugin with a 3 party plugin that
		 * can send the emails.
		 *
		 * @since    1.0.0
		 */
		public function send_emails() {

			// Allow for html emails.
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_mail_content_type' ) );

			// Set from email address.
			add_filter( 'wp_mail_from', array( $this, 'set_wp_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( $this, 'custom_wp_mail_from_name' ) );

			// Send the emails here.
			$status = wp_mail( $this->receivers, $this->subject, $this->msg );

			// Remove the filters to avoid conflicts.
			remove_filter( 'wp_mail_content_type', array( $this, 'set_html_mail_content_type' ) );
			remove_filter( 'wp_mail_from', array( $this, 'set_wp_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( $this, 'custom_wp_mail_from_name' ) );

			return $status;
		}

	}

}
