<?php
/**
 * Upload Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This is run when a comment is deleted.
 */
function wpdl_delete_comment_attachments( $comment_id ) {

	// Find attachments in that comment and delete them.
	$attachments = get_comment_meta( $comment_id, '_attachments', true );
	if ( $attachments ) {
		foreach( $attachments as $attachment_id ) {
			wp_delete_attachment( $attachment_id, true );
		}
	}
}
add_action( 'delete_comment', 'wpdl_delete_comment_attachments' );
add_action( 'wpdl_pre_delete_comment', 'wpdl_delete_comment_attachments' );

/**
 * Delete attachments when tickets are deleted.
 */
function wpdl_delete_attachments( $post_id ) {
	global $post_type;

	if ( $post_type != 'wpdl_ticket' ) {
		return;
	}

	// Find and remove all attached files.
	$attachments = get_attached_media( '', $post_id );
	foreach( $attachments as $attachment) {
		wp_delete_attachment( $attachment->ID, true );
	}

	delete_transient( '_wpdl_pending_count' );
}
add_action( 'before_delete_post', 'wpdl_delete_attachments' );

/**
 * Get attachment IDs for a reply/comment.
 */
function wpdl_get_reply_attachments( $comment_id = 0 ) {
	$attachments = get_comment_meta( $comment_id, '_attachments', true );

	return $attachments;
}

/**
 * Add attachments to a ticket.
 */
function wpdl_add_attachments( $files, $ticket_id = 0 ) {
	if ( empty( $files ) || $ticket_id == 0 ) {
		return;
	}

	// These maybe required from the front-end.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

	// Upload each file in the loop.
    foreach( $files['name'] as $key => $value ) {
		if ( $files['name'][$key] ) {
			$file = array(
				'name' 		=> $files['name'][$key],
				'type' 		=> $files['type'][$key],
				'tmp_name' 	=> $files['tmp_name'][$key],
				'error' 	=> $files['error'][$key],
				'size'		=> $files['size'][$key]
			);
			$_FILES = array( "_wpdl_ticket_files" => $file );
			foreach ( $_FILES as $file => $array ) {
				$newupload = media_handle_upload( $file, $ticket_id );
			}
		}
	}

}