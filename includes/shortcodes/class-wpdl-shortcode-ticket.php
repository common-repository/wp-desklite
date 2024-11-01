<?php
/**
 * Shortcode: Ticket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Shortcode_Ticket class.
 */
class WPDL_Shortcode_Ticket {

	/**
	 * Get the shortcode content.
	 */
	public static function get( $atts ) {
		return WPDL_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 */
	public static function output( $atts ) {
		global $the_ticket;

		$atts = array_merge( array(
			'id'	=> 0,
		), (array) $atts );

		$ticket_id 	= absint( $atts['id'] );
		$the_ticket = new WPDL_Ticket( $ticket_id );

		wpdl_get_template( 'ticket.php' );
	}

}