<?php
/**
 * Admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Admin class.
 */
class WPDL_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'admin_init', array( $this, 'preview_emails' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_action( 'admin_footer', 'wpdl_print_js', 25 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once dirname( __FILE__ ) . '/wpdl-admin-functions.php';
		include_once dirname( __FILE__ ) . '/wpdl-meta-box-functions.php';
		include_once dirname( __FILE__ ) . '/class-wpdl-admin-post-types.php';
		include_once dirname( __FILE__ ) . '/class-wpdl-admin-taxonomies.php';
		include_once dirname( __FILE__ ) . '/class-wpdl-admin-assets.php';
		include_once dirname( __FILE__ ) . '/class-wpdl-admin-menus.php';
		include_once dirname( __FILE__ ) . '/class-wpdl-admin-notices.php';
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * Preview email template.
	 */
	public function preview_emails() {

		if ( isset( $_GET['preview_wpdl_mail'] ) ) {
			if ( ! ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'preview-mail' ) ) ) {
				die( __( 'Security check.', 'wp-desklite' ) );
			}

			// load the mailer class.
			$mailer = wpdl()->mailer();

			// get the preview email subject.
			$email_heading = __( 'HTML email preview', 'wp-desklite' );

			// get the preview email content.
			ob_start();
			include 'views/html-email-template-preview.php';
			$message = ob_get_clean();

			// create a new email.
			$email = new WPDL_Email();

			// wrap the content with the email template and then add styles.
			$message = apply_filters( 'wpdl_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );

			echo $message;
			exit;
		}
	}

	/**
	 * Handle redirects
	 */
	public function admin_redirects() {
		global $pagenow;
		// Prevents support operators from creating new support tickets.
		if ( $pagenow === 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'wpdl_ticket' && ! current_user_can( 'add_wpdl_tickets' ) ) {
			exit( wp_redirect( admin_url( 'edit.php?post_type=wpdl_ticket' ) ) );
		}
	}

}

return new WPDL_Admin();