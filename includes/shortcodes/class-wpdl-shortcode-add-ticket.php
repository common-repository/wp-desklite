<?php
/**
 * Shortcode: Add a Ticket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Shortcode_Add_Ticket class.
 */
class WPDL_Shortcode_Add_Ticket {

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

		wpdl_get_template( 'add-ticket.php' );
	}

}