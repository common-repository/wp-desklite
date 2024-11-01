<?php
/**
 * Ticket Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook so we can save ticket settings.
 */
function wpdl_ticket_save_options( $props, $post_id ) {

	$props['post_status'] 	= isset( $_POST['_wpdl_status'] ) ? wpdl_clean( $_POST['_wpdl_status'] ) : 'new';
	$props['post_content'] 	= isset( $_POST['_wpdl_ticket_message'] ) ? wp_kses_post( $_POST['_wpdl_ticket_message'] ) : '';
	$props['priority'] 		= isset( $_POST['_wpdl_priority'] ) ? wpdl_clean( $_POST['_wpdl_priority'] ) : 3;
	$props['assigned_to']   = isset( $_POST['_wpdl_assigned_to'] ) ? absint( $_POST['_wpdl_assigned_to'] ) : '';

	// Support operator doing this.
	if ( current_user_can( 'edit_wpdl_ticket', $post_id ) && ! $props['assigned_to'] ) {
		$props['assigned_to'] = get_current_user_id();
	}

	// If no comments, force status new.
	$comments = get_comments_number( $post_id );
	if ( $comments < 1 ) {
		$props['post_status'] = 'new';
	}

	// Add ticket attachments.
	if ( ! empty( $_FILES ) && ! empty( $_FILES['_wpdl_ticket_files'] ) ) {
		wpdl_add_attachments( $_FILES['_wpdl_ticket_files'], $post_id );
	}

	return $props;
}
add_filter( 'wpdl_ticket_save_options', 'wpdl_ticket_save_options', 99, 2 );

/**
 * Create a new ticket from the front-end.
 */
function wpdl_create_ticket( $args = array() ) {

	$data = array(
		'post_title'	=> $args['subject'],
		'post_content'	=> $args['message'],
		'post_type'		=> 'wpdl_ticket',
		'post_author'	=> get_current_user_id(),
		'post_status'	=> 'new',
	);

	$ticket_id = wp_insert_post( $data );

	// Set post meta.
	update_post_meta( $ticket_id, 'post_status', 'new' );
	update_post_meta( $ticket_id, 'priority', $args['priority'] );
	update_post_meta( $ticket_id, 'customer', get_current_user_id() );

	// Set post terms.
	if ( $args['department'] ) {
		wp_set_object_terms( $ticket_id, $args['department'], 'wpdl_ticket_dep' );
	}
	if ( $args['type'] ) {
		wp_set_object_terms( $ticket_id, $args['type'], 'wpdl_ticket_type' );
	}

	// Add ticket attachments.
	if ( ! empty( $args['attachments'] ) ) {
		wpdl_add_attachments( $args['attachments'], $ticket_id );
	}

	// Allow extensions and hooks to do something after ticket is created.
	do_action( 'wpdl_new_ticket_created', $ticket_id );

	return $ticket_id;
}

/**
 * Mark a ticket as a new one.
 */
function wpdl_mark_ticket_as_new( $ticket_id ) {

	delete_transient( '_wpdl_pending_count' );

	update_post_meta( $ticket_id, '_is_waiting', 1 );

}
add_action( 'wpdl_new_ticket_created', 'wpdl_mark_ticket_as_new', 10 );

/**
 * Send a notification to admin when a ticket is created.
 */
function wpdl_send_admin_email( $ticket_id ) {

	$ticket = new WPDL_Ticket( $ticket_id );

	wpdl()->mailer();

	do_action( 'wpdl_new_ticket_notification', $ticket );
}
add_action( 'wpdl_new_ticket_created', 'wpdl_send_admin_email', 20 );

/**
 * Get available ticket statuses.
 */
function wpdl_ticket_statuses() {

	$statuses = array(
		'new'		=> __( 'New', 'wp-desklite' ),
		'pending'	=> __( 'Pending', 'wp-desklite' ),
		'resolved'  => __( 'Resolved', 'wp-desklite' ),
	);

	return apply_filters( 'wpdl_ticket_statuses', $statuses );
}

/**
 * Get status as a label.
 */
function wpdl_get_status( $status ) {
	$labels = wpdl_ticket_statuses();

	return apply_filters( 'wpdl_get_status', isset( $labels[ $status ] ) ? $labels[ $status ] : '', $status );
}

/**
 * Get priorities list.
 */
function wpdl_get_priorities() {

	$priorities = array(
		1		=> __( 'Very Low', 'wp-desklite' ),
		2		=> __( 'Low', 'wp-desklite' ),
		3  		=> __( 'Normal', 'wp-desklite' ),
		4  		=> __( 'High', 'wp-desklite' ),
		5  		=> __( 'Very High', 'wp-desklite' ),
	);

	return apply_filters( 'wpdl_get_priorities', $priorities );
}

/**
 * Get priorities list.
 */
function wpdl_get_priority_label( $priority ) {

	$labels = wpdl_get_priorities();

	return apply_filters( 'wpdl_get_priority_label', isset( $labels[ $priority ] ) ? $labels[ $priority ] : '', $priority );
}

/**
 * Set a ticket as new.
 */
function wpdl_ticket_set_as_new( $ticket_id = 0 ) {

	$ticket = new WPDL_Ticket( $ticket_id );
	$ticket->set_new();
}

/**
 * Set a ticket as pending.
 */
function wpdl_ticket_set_as_pending( $ticket_id = 0 ) {

	$ticket = new WPDL_Ticket( $ticket_id );
	$ticket->set_pending();
}

/**
 * Mark a ticket as resolved.
 */
function wpdl_ticket_set_as_resolved( $ticket_id = 0 ) {

	$ticket = new WPDL_Ticket( $ticket_id );
	$ticket->set_resolved();
}

/**
 * Delete a ticket.
 */
function wpdl_ticket_delete( $ticket_id = 0 ) {

	// Find and remove all attached files.
	$attachments = get_attached_media( '', $ticket_id );
	foreach( $attachments as $attachment) {
		wp_delete_attachment( $attachment->ID, true );
	}

	delete_transient( '_wpdl_pending_count' );

	wp_delete_post( $ticket_id, true );
}