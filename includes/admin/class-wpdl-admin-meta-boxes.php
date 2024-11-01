<?php
/**
 * Meta Boxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Admin_Meta_Boxes class.
 */
class WPDL_Admin_Meta_Boxes {

	/**
	 * Is meta boxes saved once?
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Meta box error messages.
	 */
	public static $meta_box_errors = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );

		// Save Meta Boxes.
		add_action( 'wpdl_ticket_process_metadata', 'WPDL_Meta_Box_Ticket_Settings::save', 10, 2 );

		// Error handling (for showing errors from meta boxes on next page load).
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );
	}

	/**
	 * Remove bloat.
	 */
	public function remove_meta_boxes() {
		foreach( array( 'wpdl_ticket' ) as $post_type ) {
			remove_meta_box( 'slugdiv', $post_type, 'normal' );
			remove_meta_box( 'commentsdiv', $post_type, 'normal' );
			remove_meta_box( 'commentstatusdiv', $post_type, 'normal' );
		}
	}

	/**
	 * Add Meta boxes.
	 */
	public function add_meta_boxes() {
		global $wp_meta_boxes;

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Support ticket metaboxes.
		add_meta_box( 'wpdl-ticket-editor', __( 'User-submitted Ticket Message', 'wp-desklite' ), 'WPDL_Meta_Box_Ticket_Editor::output', 'wpdl_ticket', 'normal', 'high' );
		add_meta_box( 'wpdl-ticket-history', __( 'Support Ticket', 'wp-desklite' ), 'WPDL_Meta_Box_Ticket_History::output', 'wpdl_ticket', 'normal', 'default' );
		add_meta_box( 'wpdl-ticket-settings', __( 'Settings', 'wp-desklite' ), 'WPDL_Meta_Box_Ticket_Settings::output', 'wpdl_ticket', 'side', 'high' );

		do_action( 'wpdl_add_metaboxes' );
	}

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['wpdl_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wpdl_meta_nonce'], 'wpdl_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// We need this save event to run once to avoid potential endless loops. This would have been perfect:
		self::$saved_meta_boxes = true;

		// Check the post type
		if ( in_array( $post->post_type, array( 'wpdl_ticket' ) ) ) {
			do_action( $post->post_type . '_process_metadata', $post_id, $post );
		} else {
			do_action( $post->post_type . '_process_metadata', $post_id, $post );
		}
	}

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = array_filter( (array) get_option( 'wpdl_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {

			echo '<div id="wpdl_errors" class="error notice is-dismissible">';

			foreach ( $errors as $error ) {
				echo '<p>' . wp_kses_post( $error ) . '</p>';
			}

			echo '</div>';

			// Clear
			delete_option( 'wpdl_meta_box_errors' );
		}
	}

	/**
	 * Add an error message.
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option.
	 */
	public function save_errors() {
		update_option( 'wpdl_meta_box_errors', self::$meta_box_errors );
	}

}

new WPDL_Admin_Meta_Boxes();