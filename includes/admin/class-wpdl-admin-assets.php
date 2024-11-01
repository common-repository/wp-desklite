<?php
/**
 * Load assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Admin_Assets class.
 */
class WPDL_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {
		global $wp_scripts;

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Register admin styles.
		wp_register_style( 'wpdl_menu_styles', wpdl()->plugin_url() . '/assets/css/menu.css', array(), WPDL_VERSION );
		wp_register_style( 'wpdl_admin_styles', wpdl()->plugin_url() . '/assets/css/admin.css', array(), WPDL_VERSION );
		wp_register_style( 'line-awesome', wpdl()->plugin_url() . '/assets/css/line-awesome.min.css', array(), WPDL_VERSION );
		wp_register_style( 'wpdl_styles', wpdl()->plugin_url() . '/assets/css/wp-desklite.css', array( 'line-awesome' ), WPDL_VERSION );

		// Add RTL support for admin styles.
		wp_style_add_data( 'wpdl_menu_styles', 'rtl', 'replace' );
		wp_style_add_data( 'wpdl_admin_styles', 'rtl', 'replace' );
		wp_style_add_data( 'wpdl_styles', 'rtl', 'replace' );

		// Global.
		wp_enqueue_style( 'wpdl_menu_styles' );

		// Admin styles for plugin pages only.
		if ( in_array( $screen_id, wpdl_get_screen_ids() ) ) {
			wp_enqueue_style( 'wpdl_admin_styles' );
		}
		
		if ( $screen_id === 'wpdl_ticket' ) {
			wp_enqueue_style( 'wpdl_styles' );
		}
	}	

	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		global $wp_query, $post;

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';

		// Register scripts.
		wp_register_script( 'jquery-tiptip', wpdl()->plugin_url() . '/assets/js/jquery-tiptip/jquery-tiptip.js', array( 'jquery' ), WPDL_VERSION, true );
		wp_register_script( 'jquery-blockui', wpdl()->plugin_url() . '/assets/js/jquery-blockui/jquery-blockui.js', array( 'jquery' ), WPDL_VERSION, true );
		wp_register_script( 'jquery-selectize', wpdl()->plugin_url() . '/assets/js/jquery-selectize/jquery-selectize.js', array( 'jquery' ), WPDL_VERSION, true );
		wp_register_script( 'wpdl_admin', wpdl()->plugin_url() . '/assets/js/admin/admin.js', array( 'jquery', 'jquery-tiptip', 'jquery-blockui', 'jquery-selectize', 'jquery-ui-sortable' ), WPDL_VERSION, true );
		wp_register_script( 'wpdl_frontend', wpdl()->plugin_url() . '/assets/js/frontend/frontend.js', array( 'jquery', 'jquery-tiptip', 'jquery-blockui' ), WPDL_VERSION, true );

		// Admin pages.
		if ( in_array( $screen_id, wpdl_get_screen_ids() ) ) {

			wp_enqueue_script( 'iris' );
			wp_enqueue_script( 'wpdl_admin' );

			$params = array(
				'ajax_url'		=> admin_url( 'admin-ajax.php' ),
				'ajax_nonce'	=> wp_create_nonce( 'wpdl-ajax-nonce' ),
			);

			wp_localize_script( 'wpdl_admin', 'wpdl_admin', $params );
		}

		// Ticket screen.
		if ( $screen_id === 'wpdl_ticket' ) {
			wp_enqueue_script( 'wpdl_frontend' );
			$params = array(
				'ajax_url'		=> admin_url( 'admin-ajax.php' ),
				'ajax_nonce'	=> wp_create_nonce( 'wpdl-ajax-nonce' ),
			);

			wp_localize_script( 'wpdl_frontend', 'wpdl_frontend', $params );
		}
	}

}

return new WPDL_Admin_Assets();