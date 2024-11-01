<?php
/**
 * User Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get customer's tickets.
 */
function wpdl_get_customer_tickets( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$args = array(
		'post_type'			=> 'wpdl_ticket',
		'post_status'		=> 'any',
		'author'        	=>  $user_id, 
		'orderby' 			=>  'post_date',
		'order'         	=>  'DESC',
		'posts_per_page' 	=> -1
	);

	$tickets = get_posts( $args );

	return $tickets;
}

/**
 * Get a user's name or display name.
 */
function wpdl_get_name( $user = null, $u = false ) {

	if ( empty ( $user ) ) {
		return null;
	}

	$fname = $user->first_name;
	$lname = $user->last_name;

	if ( $fname || $lname ) {
		$name = $fname . ' ' . $lname;
	} else {
		$name = $user->display_name;
	}

	if ( $u ) {
		if ( get_current_user_id() == $user->ID ) {
			$name = __( 'you', 'wp-desklite' );
		}
	}

	return apply_filters( 'wpdl_get_name', $name, $user );
}

/**
 * Checks if a user can reply a ticket.
 */
function wpdl_user_can_reply() {
	global $the_ticket;

	$bool 		= false;
	$user_id 	= get_current_user_id();

	if ( current_user_can( 'view_support_tickets' ) || ( absint( $the_ticket->customer ) === get_current_user_id() ) ) {
		$bool = true;
	}

	return apply_filters( 'wpdl_user_can_reply', $bool, $user_id );
}