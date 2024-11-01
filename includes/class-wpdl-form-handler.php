<?php
/**
 * Form Handler.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Form_Handler class.
 */
class WPDL_Form_Handler {

	public static $error_fields = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {

		// New Ticket.
		add_action( 'template_redirect', array( __CLASS__, 'add_ticket' ) );
	}

	/**
	 * Add a ticket.
	 */
	public static function add_ticket() {
		if ( ! isset( $_REQUEST[ 'wpdl_add_ticket_nonce' ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST[ 'wpdl_add_ticket_nonce' ], 'wpdl_add_ticket' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		// Validate user entry.
		$subject 		= isset( $_POST['_wpdl_title'] ) ? wpdl_clean( $_POST['_wpdl_title'] ) : '';
		$department		= isset( $_POST['_wpdl_department'] ) ? absint( $_POST['_wpdl_department'] ) : '';
		$type			= isset( $_POST['_wpdl_type'] ) ? absint( $_POST['_wpdl_type'] ) : '';
		$priority		= isset( $_POST['_wpdl_priority'] ) ? absint( $_POST['_wpdl_priority'] ) : '';
		$message 		= isset( $_POST['_wpdl_ticket_message'] ) ? wp_kses_post( $_POST['_wpdl_ticket_message'] ) : '';
		$attachments    = isset( $_FILES ) && ! empty( $_FILES['_wpdl_ticket_files'] ) ? $_FILES['_wpdl_ticket_files'] : '';

		if ( ! $subject ) {
			wpdl_add_notice( __( 'Please enter a subject', 'wp-desklite' ), 'error', '_wpdl_title' );
		}
		if ( ! $department && wpdl_get_departments() ) {
			wpdl_add_notice( __( 'Please select a department', 'wp-desklite' ), 'error', '_wpdl_department' );
		}
		if ( ! $type && wpdl_get_ticket_types() ) {
			wpdl_add_notice( __( 'Please select a ticket type', 'wp-desklite' ), 'error', '_wpdl_type' );
		}
		if ( ! $priority ) {
			wpdl_add_notice( __( 'Please select a priority', 'wp-desklite' ), 'error', '_wpdl_priority' );
		}
		if ( ! $message ) {
			wpdl_add_notice( __( 'Please enter your issue description', 'wp-desklite' ), 'error', '_wpdl_ticket_message' );
		}

		if ( wpdl_notice_count( 'error' ) > 0 ) {
			return;
		}

		// If we are this far. Create a ticket.
		$args = compact( 'subject', 'department', 'type', 'priority', 'message', 'attachments' );

		$ticket_id = wpdl_create_ticket( $args );
		if ( is_numeric( $ticket_id ) ) {
			wpdl_add_notice( sprintf( __( 'Your support ticket was submitted successfully. Your ticket ID is %s', 'wp-desklite' ), '<span class="wpdl-ticket-id">#' . $ticket_id . '</span>' ), 'success' );
			exit( wp_redirect( add_query_arg( 'submitted', $ticket_id ) ) );
		} else {
			wpdl_add_notice( __( 'An error has occured. We cannot submit your request at this time.', 'wp-desklite' ), 'error' );
		}
	}

}

WPDL_Form_Handler::init();