<?php
/**
 * Admin Settings Page/Tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Settings_Page class.
 */
abstract class WPDL_Settings_Page {

	/**
	 * Setting page id.
	 */
	protected $id = '';

	/**
	 * Setting page label.
	 */
	protected $label = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'wpdl_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'wpdl_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'wpdl_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'wpdl_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings page ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings page label.
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Add this page to settings.
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get settings array.
	 */
	public function get_settings() {
		return apply_filters( 'wpdl_get_settings_' . $this->id, array() );
	}

	/**
	 * Get sections.
	 */
	public function get_sections() {
		return apply_filters( 'wpdl_get_sections_' . $this->id, array() );
	}

	/**
	 * Output sections.
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'edit.php?post_type=wpdl_ticket&page=wpdl-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();

		WPDL_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings();
		WPDL_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( 'wpdl_update_options_' . $this->id . '_' . $current_section );
		}
	}

}