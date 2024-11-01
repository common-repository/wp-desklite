<?php
/**
 * Create menus in WP admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Admin_Menus class.
 */
class WPDL_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_head', array( $this, 'menu_order_fix' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 30 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );
	}

	/**
	 * Removes the parent menu item.
	 */
	public function menu_order_fix() {
		global $submenu;

		if ( ! current_user_can( 'add_wpdl_tickets' ) ) {

			echo '<style type="text/css">body.post-type-wpdl_ticket .page-title-action {display:none!important}</style>';

			// Remove add ticket from menu.
			if ( isset( $submenu['edit.php?post_type=wpdl_ticket'] ) ) {
				unset( $submenu['edit.php?post_type=wpdl_ticket'][10] );
			}
		}
	}

	/**
	 * Add count.
	 */
	public function admin_menu() {
		global $menu, $submenu;

		if ( wpdl_get_pending_count() == 0 ) {
			return;
		}

		$count = wpdl_get_pending_count();
		foreach( $menu as $key => $data ) {
			if ( $data[2] === 'edit.php?post_type=wpdl_ticket' ) {
				$menu[ $key ][ 0 ] .= '&nbsp;' . sprintf( '<span class="awaiting-mod">%d</span>', $count );
				if ( isset( $submenu['edit.php?post_type=wpdl_ticket'] ) ) {
					$submenu['edit.php?post_type=wpdl_ticket'][5][0] .= '&nbsp;' . sprintf( '<span class="awaiting-mod">%d</span>', $count );
				}
			}
		}

	}

	/**
	 * Add menu item.
	 */
	public function settings_menu() {

		$settings_page = add_submenu_page( 'edit.php?post_type=wpdl_ticket', __( 'Settings', 'wp-desklite' ), __( 'Settings', 'wp-desklite' ), 'manage_wpdl', 'wpdl-settings', array( $this, 'settings_page' ) );

		add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
	}

	/**
	 * Loads gateways and shipping methods into memory for use within settings.
	 */
	public function settings_page_init() {
		global $current_tab, $current_section;

		// Include settings pages.
		WPDL_Admin_Settings::get_settings_pages();

		// Get current tab/section.
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( wp_unslash( $_REQUEST['section'] ) );

		if ( $current_tab == 'addons' && empty( $current_section ) ) {
			$sections = apply_filters( 'wpdl_get_sections_addons', array() );
			asort( $sections );
			if ( $sections ) {
				$keys = array_keys( $sections );
				$first = $keys[0];
				exit( wp_safe_redirect( add_query_arg( 'section', $first ) ) );
			}
		}

		// Save settings if data has been posted.
		if ( '' !== $current_section && apply_filters( "wpdl_save_settings_{$current_tab}_{$current_section}", ! empty( $_POST['save'] ) ) ) {
			WPDL_Admin_Settings::save();
		} elseif ( '' === $current_section && apply_filters( "wpdl_save_settings_{$current_tab}", ! empty( $_POST['save'] ) ) ) {
			WPDL_Admin_Settings::save();
		}

		// Add any posted messages.
		if ( ! empty( $_GET['wpdl_error'] ) ) {
			WPDL_Admin_Settings::add_error( wp_kses_post( wp_unslash( $_GET['wpdl_error'] ) ) );
		}

		// Custom message.
		if ( ! empty( $_GET['wpdl_message'] ) ) {
			WPDL_Admin_Settings::add_message( wp_kses_post( wp_unslash( $_GET['wpdl_message'] ) ) );
		}

		do_action( 'wpdl_settings_page_init' );
	}

	/**
	 * Init the settings page.
	 */
	public function settings_page() {
		WPDL_Admin_Settings::output();
	}

}

return new WPDL_Admin_Menus();