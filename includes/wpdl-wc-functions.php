<?php
/**
 * WooCommerce Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add query var support.
 */
function wpdl_add_query_vars( $vars ) {
	$vars[] = 'my-tickets';

	return $vars;
}
add_filter( 'query_vars', 'wpdl_add_query_vars', 0 );

/**
 * Append the WooCommerce account menu.
 */
function wpdl_woocommerce_account_menu_items( $items ) {

	if ( 'yes' === get_option( 'wpdl_woocommerce' ) ) {
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );
		$items[ 'my-tickets' ] = __( 'My Tickets', 'wp-desklite' );
		$items[ 'customer-logout'] = $logout;
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'wpdl_woocommerce_account_menu_items' );

/**
 * Display the custom endpoint. My Tickets.
 */
function woocommerce_my_tickets_endpoint() {
	echo do_shortcode( '[wpdl_my_tickets]' );
}
add_action( 'woocommerce_account_my-tickets_endpoint', 'woocommerce_my_tickets_endpoint' );

/**
 * Add a rewrite endpoint for WooCommerce page.
 */
function wpdl_woocommerce_add_endpoint() {
	add_rewrite_endpoint( 'my-tickets', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'wpdl_woocommerce_add_endpoint' );

/**
 * WooCommerce customer address for customer.
 */
function wpdl_show_wc_address( $user_id = 0 ) {
	if ( ! class_exists( 'WC_Customer' ) || ( 'no' === get_option( 'wpdl_woocommerce' ) ) ) {
		return;
	}

	$addr = '';
	$html = '';

	$data = new WC_Customer( $user_id );

	// Get address for customer.
	foreach( $data->get_billing() as $i => $key ) {
		if ( in_array( $i, array( 'first_name', 'last_name', 'email' ) ) ) continue;
		if ( $key ) {
			$addr .= $key;
			if ( $i == 'address_1' ) {
				$addr .= '<br />';
			} else {
				$addr .= ' ';
			}
		}
	}

	// Show address tag if it exists.
	if ( $addr ) {
		$html = '<div class="wpdl-meta"><span class="wpdl-icon la-map-marker-alt"></span>';
		$html .= '<address>';
		$html .= $addr;
		$html .= '</address>';
		$html .= '</div>';
	}

	return apply_filters( 'wpdl_show_wc_address', $html, $user_id );
}

/**
 * Get customer spending overview.
 */
function wpdl_wc_get_customer_total_spent( $user_id = 0 ) {
	if ( ! class_exists( 'WC_Customer' ) || ( 'no' === get_option( 'wpdl_woocommerce' ) ) ) {
		return;
	}

	$html = '<div class="wpdl-meta"><span class="wpdl-icon la-credit-card"></span>';
	$html .= sprintf( __( 'Spent %s', 'wp-desklite' ), '&nbsp;' . wc_price( wc_get_customer_total_spent( $user_id ) ) ) . '&nbsp;&mdash;&nbsp;' . sprintf( __( '%s orders', 'wp-desklite' ), wc_get_customer_order_count( $user_id ) );
	$html .= '</div>';

	return apply_filters( 'wpdl_wc_get_customer_total_spent', $html, $user_id );
}