<?php
/**
 * Discount Core.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Ticket class.
 */
class WPDL_Ticket {

	/**
	 * ID.
	 */
	public $id = 0;

	/**
	 * Not resolved.
	 */
	public $is_resolved = 0;

	/**
	 * Post type.
	 */
	public $post_type = 'wpdl_ticket';

	public $ticket_reply = null;

	/**
	 * Meta keys.
	 */
	public $internal_meta_keys = array(
		'assigned_to',
		'priority',
		'post_status',
		'customer',
		'is_resolved',
		'is_waiting',
	);

	/**
	 * Constructor.
	 */
	public function __construct( $ticket_id = '' ) {
		$this->id = absint( $ticket_id );

		foreach( apply_filters( $this->post_type . '_meta_keys', $this->internal_meta_keys ) as $key ) {
			$this->{$key} = '';
		}

		if ( $ticket_id > 0 ) {
			$this->get_meta();
		}
	}

	/**
	 * Get meta.
	 */
	public function get_meta() {

		// Get post meta.
		$meta = get_post_custom( $this->id );

		if ( ! is_array( $meta ) ) {
			return;
		}

		foreach( $meta as $key => $value ) {
			if ( in_array( $key, apply_filters( $this->post_type . '_meta_keys', $this->internal_meta_keys ) ) ) {
				$this->{$key} = is_serialized( $value[0] ) ? unserialize( $value[0] ) : $value[0];
			}
		}

		// Get post data.
		$data = get_post( $this->id );
		if ( ! isset( $data->post_name ) ) {
			return;
		}

		// Set basic attributes.
		$this->post_title 	= $data->post_title;
		$this->post_name 	= $data->post_name;
		$this->post_content = $data->post_content;

		// Set defaults if needed.
		if ( empty( $this->priority ) ) {
			$this->priority = 3;
		}

		// Default post status.
		if ( empty( $this->post_status ) ) {
			$this->post_status = 'new';
		}

		// If ticket is resolved.
		if ( $this->post_status === 'resolved' ) {
			$this->is_resolved = 1;
		}
	}

	/**
	 * Save meta.
	 */
	public function save( $props ) {
		if ( $this->id <= 0 ) {
			return;
		}

		if ( ! $this->customer ) {
			$props['customer'] = get_current_user_id();
		}

		// Save meta data.
		if ( $props && is_array( $props ) ) {
			foreach( $props as $key => $value ) {
				if ( in_array( $key, apply_filters( $this->post_type . '_meta_keys', $this->internal_meta_keys ) ) ) {
					update_post_meta( $this->id, $key, $value );
				}
			}
		}

		// Update ticket.
		$ticket = apply_filters( 'wpdl_ticket_update_array', array(
			'ID'    			=> $this->id,
			'post_status' 		=> ! empty( $props['post_status'] ) ? $props['post_status'] : $this->post_status,
			'post_content'		=> ! empty( $props['post_content'] ) ? $props['post_content'] : $this->post_content,
			'comment_status'	=> 'open',
			'ping_status'		=> 'closed',
        ), $this );

		// Runs just before a ticket is updated in database.
		do_action( "{$this->post_type}_pre_save", $this, $this->id, $props );

		// Fired when ticket status is transitioned. e.g. new to pending.
		$old_status = $this->post_status;
		$new_status = $props['post_status'];
		do_action( "{$this->post_type}_transition_{$old_status}_{$new_status}", $this, $this->id );

		// A ticket is resolved.
		if ( $new_status == 'resolved' ) {
			delete_transient( '_wpdl_pending_count' );
			update_post_meta( $this->id, '_is_waiting', 0 );
		}

		// Finally. Let's update the ticket.
		wp_update_post( $ticket );

		// Runs after a ticket is updated.
		do_action( "{$this->post_type}_saved", $this, $this->id, $props );

		// Uncomment to debug?
		//die( print_r( $props ) );
	}

	/**
	 * Get ticket departments.
	 */
	public function get_departments() {
		$terms = wp_get_post_terms( $this->id, 'wpdl_ticket_dep' );
		if ( $terms ) {
			return $terms;
		}
		return null;
	}

	/**
	 * Get ticket types.
	 */
	public function get_types() {
		$terms = wp_get_post_terms( $this->id, 'wpdl_ticket_type' );
		if ( $terms ) {
			return $terms;
		}
		return null;
	}

	/**
	 * Set ticket as new.
	 */
	public function set_new() {
		$ticket = array(
			'ID'			=> $this->id,
			'post_status'	=> 'new',
		);
		update_post_meta( $this->id, 'post_status', 'new' );
		wp_update_post( $ticket );

		do_action( 'wpdl_ticket_is_new', $this->id );
	}

	/**
	 * Set ticket as pending.
	 */
	public function set_pending() {
		$ticket = array(
			'ID'			=> $this->id,
			'post_status'	=> 'pending',
		);
		update_post_meta( $this->id, 'post_status', 'pending' );
		wp_update_post( $ticket );

		do_action( 'wpdl_ticket_is_pending', $this->id );
	}

	/**
	 * Set ticket as resolved.
	 */
	public function set_resolved() {
		$ticket = array(
			'ID'			=> $this->id,
			'post_status'	=> 'resolved',
		);
		update_post_meta( $this->id, 'post_status', 'resolved' );
		wp_update_post( $ticket );

		do_action( 'wpdl_ticket_is_resolved', $this->id );
	}

	/**
	 * Set ticket customer.
	 */
	public function set_customer( $user_id = 0 ) {
		update_post_meta( $this->id, 'customer', $user_id );
	}

	/**
	 * Get customer.
	 */
	public function get_customer() {
		$user_id = absint( $this->customer );

		$user = get_userdata( $user_id );

		return $user;
	}

	/**
	 * Get a ticket message.
	 */
	public function get_message() {
		return $this->post_content;
	}

	/**
	 * Get replies for this ticket.
	 */
	public function get_replies() {
		global $wpdb;

		$replies = $wpdb->get_results(
			"SELECT * FROM {$wpdb->comments}
			WHERE comment_type IN ('ticket_reply', 'ticket_note')
			AND comment_post_ID = '{$this->id}' 
			ORDER BY comment_date_gmt ASC
			"
		);

		return $replies;
	}

	/**
	 * Adds a ticket reply or note.
	 */
	public function add_ticket_reply( $reply, $is_note = 0, $resolved = 'no' ) {
		if ( ! $this->id ) {
			return 0;
		}

		if ( is_user_logged_in() ) {
			$user                 = get_user_by( 'id', get_current_user_id() );
			$comment_author       = wpdl_get_name( $user );
			$comment_author_email = $user->user_email;
		} else {
			return;
		}

		$this->ticket_reply = $reply;

		$commentdata = apply_filters(
			'wpdl_new_ticket_note_data',
			array(
				'comment_post_ID'      => $this->id,
				'comment_author'       => $comment_author,
				'comment_author_email' => $comment_author_email,
				'comment_author_url'   => '',
				'comment_content'      => $reply,
				'comment_agent'        => 'WP DeskLite',
				'comment_type'         => $is_note ? 'ticket_note' : 'ticket_reply',
				'comment_parent'       => 0,
				'comment_approved'     => 1,
				'user_id'			   => get_current_user_id(),
			),
			array(
				'ticket_id'         => $this->id,
				'is_note' 			=> $is_note,
			)
		);

		$comment_id = wp_insert_comment( $commentdata );

		if ( $is_note ) {
			add_comment_meta( $comment_id, 'is_note', 1 );
		}

		// When an operator responds to the ticket.
		if ( get_current_user_id() != $this->customer ) {
			delete_transient( '_wpdl_pending_count' );
			update_post_meta( $this->id, '_is_waiting', 0 );
		} else {
			if ( $resolved == 'no' ) {
				delete_transient( '_wpdl_pending_count' );
				update_post_meta( $this->id, '_is_waiting', 1 );
			}
		}

		// If the reply marks a ticket as resolved.
		if ( $resolved == 'yes' ) {
			delete_transient( '_wpdl_pending_count' );
			update_post_meta( $this->id, '_is_waiting', 0 );
		}

		// Send customer's an update.
		wpdl()->mailer();

		// If current user is ticket's owner - notify the operator.
		if ( ! $is_note ) {
			if ( get_current_user_id() == $this->customer ) {
				do_action( 'wpdl_operator_new_reply_notification', $this );
			} else {
				do_action( 'wpdl_new_reply_notification', $this );
			}
		}

		return $comment_id;
	}

	/**
	 * Get the ticket reply.
	 */
	public function get_reply() {
		return $this->ticket_reply;
	}

	/**
	 * Get customer's email.
	 */
	public function get_customer_email() {
		$user = get_userdata( $this->customer );
		return $user->user_email;
	}

	/**
	 * Get operator's email.
	 */
	public function get_operator_email() {
		$user = get_userdata( $this->assigned_to );
		return $user->user_email;
	}

	/**
	 * Upload files with WP and attach to a specific reply.
	 */
	public function upload_attachments_for_reply( $files, $reply_id ) {
		if ( empty( $files ) || $reply_id == 0 ) {
			return;
		}

		$upload_ids = array();

		// These maybe required from the front-end.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		// Upload each file in the loop.
		foreach( $files as $key => $file ) {
			$upload_id = media_handle_upload( $key, 0 );
			if ( is_numeric( $upload_id ) ) {
				$upload_ids[] = $upload_id;
			}
		}

		// Add attachment ID comment meta.
		update_comment_meta( $reply_id, '_attachments', $upload_ids );
	}

	/**
	 * Get attachments for this ticket.
	 */
	public function get_attachments() {
		$images = get_attached_media( 'image', $this->id );
		if ( $images ) {
			return array_keys( $images );
		}
		return null;
	}

	/**
	 * Get ticket title.
	 */
	public function get_subject() {
		return $this->post_title;
	}

	/**
	 * Get ticket status.
	 */
	public function get_status() {
		return wpdl_get_status( $this->post_status );
	}

	/**
	 * Get ticket priority.
	 */
	public function get_priority() {
		return wpdl_get_priority_label( $this->priority );
	}

	/**
	 * Get creation date.
	 */
	public function created_on() {
		$date = sprintf( __( '%1$s %2$s', 'wp-desklite' ), get_option( 'date_format' ), get_option( 'time_format' ) );
		return date_i18n( $date, get_the_time( 'U', $this->id ) );
	}

	/**
	 * Get last modified date.
	 */
	public function last_modified() {
		$date = sprintf( __( '%1$s %2$s', 'wp-desklite' ), get_option( 'date_format' ), get_option( 'time_format' ) );
		return date_i18n( $date, get_the_modified_time( 'U', $this->id ) );
	}

	/**
	 * Get department.
	 */
	public function get_department() {
		$departments = $this->get_departments();
		if ( $departments ) {
			foreach( $departments as $department ) {
				$output = $department->name;
			}
		} else {
			$output = __( 'No department', 'wp-desklite' );
		}

		return $output;
	}

	/**
	 * Get ticket URL.
	 */
	public function get_ticket_url() {
		$url = remove_query_arg( 'new-ticket' );
		$url = remove_query_arg( 'submitted' );
		$url = add_query_arg( 'ticket_id', $this->id, $url );

		return $url;
	}

	/**
	 * Get ticket ID.
	 */
	public function get_id() {
		return absint( $this->id );
	}

	/**
	 * Get the core ticket URL.
	 */
	public function get_core_ticket_url() {
		$url = admin_url( 'post.php?post=' . $this->id . '&action=edit' );

		return $url;
	}

	/**
	 * Get the customer ticket URL.
	 */
	public function get_customer_ticket_url() {

		if ( class_exists( 'WC_Customer' ) && 'yes' === get_option( 'wpdl_woocommerce' ) ) {
			$url = wc_get_account_endpoint_url( 'my-tickets' );
			$url = add_query_arg( 'ticket_id', $this->id, $url );
		} else {
			$url = get_permalink( wpdl_get_page_id() );
			$url = add_query_arg( 'ticket_id', $this->id, $url );
		}

		return $url;
	}

}