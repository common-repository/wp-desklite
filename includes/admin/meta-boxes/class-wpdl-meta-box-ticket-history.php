<?php
/**
 * Ticket History.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Meta_Box_Ticket_History class.
 */
class WPDL_Meta_Box_Ticket_History {

	/**
	 * Output the metabox.
	 */
	public static function output( $post ) {
		global $thepostid, $the_ticket;

		$thepostid      = $post->ID;

		echo do_shortcode( '[wpdl_ticket id=' . $thepostid . ']' );
	}

}