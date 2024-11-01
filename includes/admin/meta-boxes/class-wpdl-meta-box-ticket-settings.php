<?php
/**
 * Ticket Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Meta_Box_Ticket_Settings class.
 */
class WPDL_Meta_Box_Ticket_Settings {

	/**
	 * Output the metabox.
	 */
	public static function output( $post ) {
		global $thepostid, $the_ticket;

		$thepostid      = $post->ID;
		$the_ticket 	= $thepostid ? new WPDL_Ticket( $thepostid ) : new WPDL_Ticket();

		wp_nonce_field( 'wpdl_save_data', 'wpdl_meta_nonce' );

		include 'views/html-ticket-settings.php';
	}

	/**
	 * Save meta box data.
	 */
	public static function save( $post_id, $post ) {
		global $the_ticket;

		$props = array();
		$the_ticket = new WPDL_Ticket( $post_id );
		$the_ticket->save( apply_filters( 'wpdl_ticket_save_options', $props, $post_id ) );
	}

}