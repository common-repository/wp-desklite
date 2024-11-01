<?php
/**
 * Comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Comments class.
 */
class WPDL_Comments {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		// Secure ticket replies.
		add_filter( 'comments_clauses', array( __CLASS__, 'exclude_ticket_replies' ), 10, 1 );
		add_filter( 'comment_feed_where', array( __CLASS__, 'exclude_ticket_replies_from_feed_where' ) );

		// Secure ticket notes.
		add_filter( 'comments_clauses', array( __CLASS__, 'exclude_ticket_notes' ), 10, 1 );
		add_filter( 'comment_feed_where', array( __CLASS__, 'exclude_ticket_notes_from_feed_where' ) );

		// Count comments.
		add_filter( 'wp_count_comments', array( __CLASS__, 'wp_count_comments' ), 10, 2 );

		// Delete comments count cache whenever there is a new comment or a comment status changes.
		add_action( 'wp_insert_comment', array( __CLASS__, 'delete_comments_count_cache' ) );
		add_action( 'wp_set_comment_status', array( __CLASS__, 'delete_comments_count_cache' ) );

	}

	/**
	 * Exclude ticket replies from queries and RSS.
	 */
	public static function exclude_ticket_replies( $clauses ) {
		$clauses['where'] .= ( $clauses['where'] ? ' AND ' : '' ) . " comment_type != 'ticket_reply' ";
		return $clauses;
	}

	/**
	 * Exclude ticket replies from queries and RSS.
	 */
	public static function exclude_ticket_replies_from_feed_where( $where ) {
		return $where . ( $where ? ' AND ' : '' ) . " comment_type != 'ticket_reply' ";
	}

	/**
	 * Exclude ticket notes from queries and RSS.
	 */
	public static function exclude_ticket_notes( $clauses ) {
		$clauses['where'] .= ( $clauses['where'] ? ' AND ' : '' ) . " comment_type != 'ticket_note' ";
		return $clauses;
	}

	/**
	 * Exclude ticket notes from queries and RSS.
	 */
	public static function exclude_ticket_notes_from_feed_where( $where ) {
		return $where . ( $where ? ' AND ' : '' ) . " comment_type != 'ticket_note' ";
	}

	/**
	 * Delete comments count cache whenever there is
	 */
	public static function delete_comments_count_cache() {
		delete_transient( 'wpdl_count_comments' );
	}

	/**
	 * Remove ticket notes from wp_count_comments().
	 */
	public static function wp_count_comments( $stats, $post_id ) {
		global $wpdb;

		if ( 0 === $post_id ) {
			$stats = get_transient( 'wpdl_count_comments' );

			if ( ! $stats ) {
				$stats = array(
					'total_comments' => 0,
					'all'            => 0,
				);

				$count = $wpdb->get_results(
					"
					SELECT comment_approved, COUNT(*) AS num_comments
					FROM {$wpdb->comments}
					WHERE comment_type NOT IN ('ticket_reply', 'ticket_note')
					GROUP BY comment_approved
					",
					ARRAY_A
				);

				$approved = array(
					'0'            => 'moderated',
					'1'            => 'approved',
					'spam'         => 'spam',
					'trash'        => 'trash',
					'post-trashed' => 'post-trashed',
				);

				foreach ( (array) $count as $row ) {
					// Don't count post-trashed toward totals.
					if ( ! in_array( $row['comment_approved'], array( 'post-trashed', 'trash', 'spam' ), true ) ) {
						$stats['all']            += $row['num_comments'];
						$stats['total_comments'] += $row['num_comments'];
					} elseif ( ! in_array( $row['comment_approved'], array( 'post-trashed', 'trash' ), true ) ) {
						$stats['total_comments'] += $row['num_comments'];
					}
					if ( isset( $approved[ $row['comment_approved'] ] ) ) {
						$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
					}
				}

				foreach ( $approved as $key ) {
					if ( empty( $stats[ $key ] ) ) {
						$stats[ $key ] = 0;
					}
				}

				$stats = (object) $stats;
				set_transient( 'wpdl_count_comments', $stats );
			}
		}

		return $stats;
	}

}

WPDL_Comments::init();