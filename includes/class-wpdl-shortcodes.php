<?php
/**
 * Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Shortcodes class.
 */
class WPDL_Shortcodes {

	/**
	 * Init shortcodes.
	 */
	public static function init() {
		add_shortcode( 'wpdl_ticket', 		__CLASS__ . '::wpdl_ticket' );
		add_shortcode( 'wpdl_add_ticket', 	__CLASS__ . '::wpdl_add_ticket' );
		add_shortcode( 'wpdl_my_tickets',	__CLASS__ . '::wpdl_my_tickets' );
	}

	/**
	 * Shortcode Wrapper.
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'wpdl',
			'before' => null,
			'after'  => null,
		)
	) {
		ob_start();

		$ui_class = is_admin() ? 'wpdl-admin' : 'wpdl-front';

		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . ' ' . $ui_class . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		return ob_get_clean();
	}

	/**
	 * Output ticket.
	 */
	public static function wpdl_ticket( $atts ) {
		return self::shortcode_wrapper( array( 'WPDL_Shortcode_Ticket', 'output' ), $atts );
	}

	/**
	 * Output new ticket form.
	 */
	public static function wpdl_add_ticket( $atts ) {
		return self::shortcode_wrapper( array( 'WPDL_Shortcode_Add_Ticket', 'output' ), $atts );
	}

	/**
	 * Output my tickets list.
	 */
	public static function wpdl_my_tickets( $atts ) {
		return self::shortcode_wrapper( array( 'WPDL_Shortcode_My_Tickets', 'output' ), $atts );
	}

}