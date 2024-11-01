<?php
/**
 * Role Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a WordPress role.
 */
function wpdl_add_role( $role = '', $label = '', $capabilities = '' ) {

	remove_role( $role );

	if ( ! $label ) {
		$label = ucfirst( $role );
	}

	$capabilities = array_merge( $capabilities, array( 'read' => true, 'level_0' => true ) );

	add_role( $role, $label, $capabilities );
}

/**
 * Get support capabilities.
 */
function wpdl_get_support_caps() {
	$capabilities = array(
		'view_admin_dashboard'  		=> true,
		'view_support_tickets'			=> true,
		'read_wpdl_ticket'				=> true,
		'edit_wpdl_ticket'				=> true,
		'edit_wpdl_tickets'				=> true,
		'edit_others_wpdl_tickets'		=> true,
		'assign_wpdl_ticket_dep_terms'	=> true,
		'assign_wpdl_ticket_type_terms'	=> true,
	);

	return apply_filters( 'wpdl_get_support_caps', $capabilities );
}

/**
 * Get support admin capabilities.
 */
function wpdl_get_support_admin_caps() {
	$capabilities = array(
		'view_admin_dashboard'  		=> true,
		'view_support_tickets'			=> true,
		'manage_wpdl'					=> true,
		'add_wpdl_tickets'				=> true,
	);

	$capability_types = array( 'wpdl_ticket', 'wpdl_ticket_dep', 'wpdl_ticket_type' );

	foreach ( $capability_types as $capability_type ) {
		$capabilities = $capabilities + array(
			// Custom post types.
			"edit_{$capability_type}" 						=> true,
			"read_{$capability_type}" 						=> true,
			"delete_{$capability_type}" 					=> true,
			"edit_{$capability_type}s" 						=> true,
			"edit_others_{$capability_type}s" 				=> true,
			"publish_{$capability_type}s" 					=> true,
			"read_private_{$capability_type}s" 				=> true,
			"delete_{$capability_type}s" 					=> true,
			"delete_private_{$capability_type}s" 			=> true,
			"delete_published_{$capability_type}s" 			=> true,
			"delete_others_{$capability_type}s" 			=> true,
			"edit_private_{$capability_type}s" 				=> true,
			"edit_published_{$capability_type}s" 			=> true,
			// Terms.
			"manage_{$capability_type}_terms"				=> true,
			"edit_{$capability_type}_terms"					=> true,
			"delete_{$capability_type}_terms"				=> true,
			"assign_{$capability_type}_terms"				=> true,
		);
	}

	return apply_filters( 'wpdl_get_support_admin_caps', $capabilities );
}

/**
 * Get list of WP roles.
 */
function wpdl_get_roles( $exclude_admin = true ) {
    global $wp_roles;

	$roles = array();

	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	$editable_roles = array_reverse( get_editable_roles() );

	foreach ( $editable_roles as $role => $details ) {
		if ( $exclude_admin && in_array( $role, array( 'administrator' ) ) ) {
			// Do not add admin roles.
		} else {
			$roles[ $role ] = translate_user_role( $details['name'] );
		}
	}

	return apply_filters( 'wpdl_get_roles', $roles );
}