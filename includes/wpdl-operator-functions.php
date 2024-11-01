<?php
/**
 * Operator Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get users with operator capabilities.
 */
function wpdl_get_operators() {
	global $wpdb;

	$operators = array();

	$results = $wpdb->get_results( "
		SELECT {$wpdb->users}.ID
		FROM {$wpdb->users} INNER JOIN {$wpdb->usermeta} 
		ON {$wpdb->users}.ID = {$wpdb->usermeta}.user_id WHERE 
		( {$wpdb->usermeta}.meta_key = 'wp_capabilities' AND {$wpdb->usermeta}.meta_value LIKE '%operator%' ) OR 
		( {$wpdb->usermeta}.meta_key = 'wp_capabilities' AND {$wpdb->usermeta}.meta_value LIKE '%administrator%' )
		ORDER BY {$wpdb->users}.ID ASC", ARRAY_A );

	if ( is_array( $results ) ) {
		foreach( $results as $result ) {
			$user = get_userdata( $result['ID'] );
			$operators[ $user->ID ] = array(
				'name'		=> wpdl_get_name( $user ),
				'email'		=> $user->user_email,
			);
		}
	}

	return $operators;
}