<?php
/**
 * Admin Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all screen ids.
 */
function wpdl_get_screen_ids() {
	$screen_ids = array();
	$post_types = array( 'wpdl_ticket', 'wpdl_ticket_dep', 'wpdl_ticket_type' );

	foreach( $post_types as $post_type ) {
		$screen_ids[] = "edit-{$post_type}";
		$screen_ids[] = $post_type;
	}

	$screen_ids[] = 'wpdl_ticket_page_wpdl-settings';

	return apply_filters( 'wpdl_get_screen_ids', $screen_ids );
}


/**
 * Get pending count.
 */
function wpdl_get_pending_count() {
	global $wpdb;

	$count = get_transient( '_wpdl_pending_count' );
	if ( false === $count ) {
	$count = $wpdb->get_var( "
		SELECT COUNT(p.ID) FROM {$wpdb->posts} as p 
		INNER JOIN {$wpdb->postmeta} AS pm1 ON pm1.post_id = p.ID 
		WHERE 
		p.post_type = 'wpdl_ticket' AND 
		p.post_status != 'auto-draft' AND
		pm1.meta_key = '_is_waiting' AND
		pm1.meta_value = 1" );
		set_transient( '_wpdl_pending_count', $count, DAY_IN_SECONDS );
	}

	return $count;
}

/**
 * Processes all actions sent via POST and GET by looking for the 'wpdl-action'
 */
function wpdl_process_actions() {
	if ( isset( $_POST['wpdl-action'] ) ) {
		do_action( 'wpdl_' . $_POST['wpdl-action'], $_POST );
	}

	if ( isset( $_GET['wpdl-action'] ) ) {
		do_action( 'wpdl_' . $_GET['wpdl-action'], $_GET );
	}
}
add_action( 'admin_init', 'wpdl_process_actions' );

/**
 * Resolve a ticket
 */
function wpdl_resolve_ticket( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'wpdl_ticket_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'wp-desklite' ), __( 'Error', 'wp-desklite' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'edit_wpdl_ticket', $data['ticket'] ) ) {
		wp_die( __( 'You do not have permission to manage support tickets', 'wp-desklite' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['ticket'] );
	wpdl_ticket_set_as_resolved( $id );
}
add_action( 'wpdl_resolve_ticket', 'wpdl_resolve_ticket' );

/**
 * Unresolve a ticket
 */
function wpdl_unresolve_ticket( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'wpdl_ticket_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'wp-desklite' ), __( 'Error', 'wp-desklite' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'edit_wpdl_ticket', $data['ticket'] ) ) {
		wp_die( __( 'You do not have permission to manage support tickets', 'wp-desklite' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['ticket'] );
	wpdl_ticket_set_as_pending( $id );
}
add_action( 'wpdl_unresolve_ticket', 'wpdl_unresolve_ticket' );

/**
 * Delete a ticket
 */
function wpdl_delete_ticket( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'wpdl_ticket_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'wp-desklite' ), __( 'Error', 'wp-desklite' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'add_wpdl_tickets' ) ) {
		wp_die( __( 'You do not have permission to manage support tickets', 'wp-desklite' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['ticket'] );
	wpdl_ticket_delete( $id );
}
add_action( 'wpdl_delete_ticket', 'wpdl_delete_ticket' );

/**
 * Setup support capabilities based on chosen options.
 */
function wpdl_add_remove_support_roles( $options, $tab, $section ) {
	global $wp_roles;

	if ( $tab != 'general' ) {
		return;
	}

	if ( ! class_exists( 'WP_Roles' ) ) {
		return;
	}

	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	$roles = $options['wpdl_support_roles'];

	// Remove support roles altogether.
	if ( empty( $roles ) ) {
		// Add default capabilities to all roles.
		foreach( $wp_roles->roles as $role => $data ) {
			if ( ! in_array( $role, array( 'administrator' ) ) ) {
				foreach( wpdl_get_support_caps() as $capability => $bool ) {
					$wp_roles->remove_cap( $role, $capability );
				}
			}
		}
	} else {

		// Add the support caps to every role selected.
		foreach( $roles as $role ) {
			foreach( wpdl_get_support_caps() as $capability => $bool ) {
				$wp_roles->add_cap( $role, $capability );
			}
		}
	}

}
add_action( 'wpdl_after_settings_update', 'wpdl_add_remove_support_roles', 20, 3 );

/**
 * Enable the form to capture files.
 */
function wpdl_post_edit_form_tag() {
	global $post_type;
	if ( 'wpdl_ticket' === $post_type ) {
		echo ' enctype="multipart/form-data"';
	}
}
add_action( 'post_edit_form_tag', 'wpdl_post_edit_form_tag' );

/**
 * Create a page and store the ID in an option.
 */
function wpdl_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
	global $wpdb;

	$option_value = get_option( $option );

	if ( $option_value > 0 && ( $page_object = get_post( $option_value ) ) ) {
		if ( 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ) ) ) {
			// Valid page is already in place
			return $page_object->ID;
		}
	}

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
	}

	$valid_page_found = apply_filters( 'wpdl_create_page_id', $valid_page_found, $slug, $page_content );

	if ( $valid_page_found ) {
		if ( $option ) {
			update_option( $option, $valid_page_found );
		}
		return $valid_page_found;
	}

	// Search for a matching valid trashed page
	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
	}

	if ( $trashed_page_found ) {
		$page_id   = $trashed_page_found;
		$page_data = array(
			'ID'          => $page_id,
			'post_status' => 'publish',
		);
		wp_update_post( $page_data );
	} else {
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => $slug,
			'post_title'     => $page_title,
			'post_content'   => $page_content,
			'post_parent'    => $post_parent,
			'comment_status' => 'closed',
		);
		$page_id   = wp_insert_post( $page_data );
	}

	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}