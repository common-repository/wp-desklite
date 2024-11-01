<?php
/**
 * AJAX Events.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_AJAX class.
 */
class WPDL_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// wpdl_EVENT => nopriv.
		$ajax_events = array(
			'add_ticket_reply'		=> false,
			'remove_reply'			=> false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_wpdl_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_wpdl_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}


	/**
	 * Add ticket reply/note.
	 */
	public static function add_ticket_reply() {

		check_ajax_referer( 'wpdl-ajax-nonce', 'security' );

		$id   		= isset( $_POST[ 'ticket' ] ) ? wpdl_clean( $_POST[ 'ticket' ] ) : '';
		$type 		= isset( $_POST[ 'type' ] ) ? wpdl_clean( $_POST[ 'type' ] ) : '';
		$content 	= isset( $_POST[ 'content' ] ) ? wp_kses_post( $_POST[ 'content' ] ) : '';
		$priority   = isset( $_POST[ 'priority' ] ) ? absint( $_POST[ 'priority' ] ) : 3;
		$resolved   = isset( $_POST[ 'resolved' ] ) ? wpdl_clean( $_POST[ 'resolved' ] ) : 'no';
		$user_id    = get_current_user_id();
		$reply_id = 0;

		// Secure.
		if ( ! is_user_logged_in() || ! $id || ! $type || ! $content ) {
			wp_die( -1 );
		}

		// Insert ticket update.
		$ticket = new WPDL_Ticket( $id );
		if ( 'ticket_note' === $type ) {
			$reply_id = $ticket->add_ticket_reply( $content, true, $resolved );
		} else {
			$reply_id = $ticket->add_ticket_reply( $content, false, $resolved );
		}

		// Save ticket properties.
		$props = array(
			'priority'		=> $priority,
			'post_status'	=> $resolved === 'yes' ? 'resolved' : 'pending',
			'assigned_to'	=> $ticket->assigned_to == 0 ? get_current_user_id() : $ticket->assigned_to,
		);
		$ticket->save( apply_filters( 'wpdl_ticket_reply_props', $props, $id ) );

		// Store attachments.
		if ( ! empty( $_FILES ) ) {
			$ticket->upload_attachments_for_reply( $_FILES, $reply_id );
		}

		// Get ticket reply as html.
		echo wpdl_get_template_html( 'ticket-reply.php', array( 'reply' => get_comment( $reply_id ) ) );

		die();
	}

	/**
	 * Remove a reply.
	 */
	public static function remove_reply() {

		check_ajax_referer( 'wpdl-ajax-nonce', 'security' );

		$id = isset( $_POST[ 'id' ] ) ? wpdl_clean( $_POST[ 'id' ] ) : '';

		$reply = get_comment( $id );

		if ( get_current_user_id() == $reply->user_id || ( current_user_can( 'add_wpdl_tickets' ) ) ) {

			// Fired when a comment/reply is about to get deleted.
			do_action( 'wpdl_pre_delete_comment', $id );

			wp_delete_comment( $id, true );
		}

		die();
	}

}

WPDL_AJAX::init();