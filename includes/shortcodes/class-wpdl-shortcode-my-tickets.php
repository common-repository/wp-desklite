<?php
/**
 * Shortcode: My Tickets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Shortcode_My_Tickets class.
 */
class WPDL_Shortcode_My_Tickets {

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

		), (array) $atts );

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( isset( $_GET['new-ticket'] ) && $_GET['new-ticket'] == 1 ) {

			wpdl_get_template( 'add-ticket.php', array( 'back_button' => 1 ) );

		} else if ( isset( $_GET['ticket_id'] ) ) {

			$ticket_id 	= absint( $_GET['ticket_id'] );
			$the_ticket = new WPDL_Ticket( $ticket_id );
			wpdl_get_template( 'ticket.php', array( 'source' => 'my-tickets' ) );

		} else {

			$my_tickets = wpdl_get_customer_tickets();

			wpdl_get_template( 'my-tickets.php', array( 'my_tickets' => $my_tickets ) );

		}

	}

}