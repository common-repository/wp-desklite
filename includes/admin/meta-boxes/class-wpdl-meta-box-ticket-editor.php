<?php
/**
 * Ticket Editor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Meta_Box_Ticket_Editor class.
 */
class WPDL_Meta_Box_Ticket_Editor {

	/**
	 * Output the metabox.
	 */
	public static function output( $post ) {
		global $thepostid, $the_ticket;

		$thepostid      = $post->ID;
		$the_ticket 	= $thepostid ? new WPDL_Ticket( $thepostid ) : new WPDL_Ticket();

		wp_nonce_field( 'wpdl_save_data', 'wpdl_meta_nonce' );

		include 'views/html-ticket-editor.php';
	}

}