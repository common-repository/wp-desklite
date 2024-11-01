<?php
/**
 * Post Types Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Admin_Post_Types class.
 */
class WPDL_Admin_Post_Types {

	/**
	 * Constructor.
	 */
	public function __construct() {
		include_once dirname( __FILE__ ) . '/class-wpdl-admin-meta-boxes.php';

		// Load correct list table classes for current screen.
		add_action( 'current_screen', array( $this, 'setup_screen' ) );
		add_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );

		// Admin notices.
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_post_updated_messages' ), 10, 2 );

		// Extra post data and screen elements.
		add_action( 'edit_form_top', array( $this, 'edit_form_top' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );

		// Add a post display state for special WC pages.
		add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );
	}

	/**
	 * Looks at the current screen and loads the correct list table handler.
	 */
	public function setup_screen() {
		global $wpdl_list_table;

		$screen_id = false;

		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		if ( ! empty( $_REQUEST['screen'] ) ) {
			$screen_id = wpdl_clean( wp_unslash( $_REQUEST['screen'] ) );
		}

		switch ( $screen_id ) {
			case 'edit-wpdl_ticket':
				include_once 'list-tables/class-wpdl-admin-list-table-ticket.php';
				$wpdl_list_table = new WPDL_Admin_List_Table_Ticket();
				break;
		}

		// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
		remove_action( 'current_screen', array( $this, 'setup_screen' ) );
		remove_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
	}

	/**
	 * Change messages when a post type is updated.
	 */
	public function post_updated_messages( $messages ) {
		global $post;

		$messages['wpdl_ticket'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Support ticket updated.', 'wp-desklite' ),
			2  => __( 'Custom field updated.', 'wp-desklite' ),
			3  => __( 'Custom field deleted.', 'wp-desklite' ),
			4  => __( 'Support ticket updated.', 'wp-desklite' ),
			5  => __( 'Revision restored.', 'wp-desklite' ),
			6  => __( 'Support ticket updated.', 'wp-desklite' ),
			7  => __( 'Support ticket saved.', 'wp-desklite' ),
			8  => __( 'Support ticket submitted.', 'wp-desklite' ),
			9  => sprintf(
				__( 'Support ticket scheduled for: %s.', 'wp-desklite' ),
				'<strong>' . date_i18n( __( 'M j, Y @ G:i', 'wp-desklite' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => __( 'Support ticket draft updated.', 'wp-desklite' ),
			11 => __( 'Support ticket updated and sent.', 'wp-desklite' ),
		);

		return $messages;
	}

	/**
	 * Specify custom bulk actions messages for different post types.
	 */
	public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {


		$bulk_messages['wpdl_ticket'] = array(
			'updated'   => _n( '%s support ticket updated.', '%s support tickets updated.', $bulk_counts['updated'], 'wp-desklite' ),
			'locked'    => _n( '%s support ticket not updated, somebody is editing it.', '%s support tickets not updated, somebody is editing them.', $bulk_counts['locked'], 'wp-desklite' ),
			'deleted'   => _n( '%s support ticket permanently deleted.', '%s support tickets permanently deleted.', $bulk_counts['deleted'], 'wp-desklite' ),
			'trashed'   => _n( '%s support ticket moved to the Trash.', '%s support tickets moved to the Trash.', $bulk_counts['trashed'], 'wp-desklite' ),
			'untrashed' => _n( '%s support ticket restored from the Trash.', '%s support tickets restored from the Trash.', $bulk_counts['untrashed'], 'wp-desklite' ),
		);

		return $bulk_messages;
	}

	/**
	 * Output extra data on post forms.
	 */
	public function edit_form_top( $post ) {
		echo '<input type="hidden" id="original_post_title" name="original_post_title" value="' . esc_attr( $post->post_title ) . '" />';
	}

	/**
	 * Change title boxes in admin.
	 */
	public function enter_title_here( $text, $post ) {
		switch ( $post->post_type ) {
			case 'wpdl_ticket':
				$text = __( 'Enter support ticket subject', 'wp-desklite' );
				break;
		}
		return $text;
	}

	/**
	 * Add a post display state.
	 */
	public function add_display_post_states( $post_states, $post ) {

		if ( wpdl_get_page_id() === $post->ID ) {
			$post_states['wpdl_page_for_tickets'] = __( 'My Tickets Page', 'wp-desklite' );
		}

		return $post_states;
	}

}

new WPDL_Admin_Post_Types();