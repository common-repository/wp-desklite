<?php
/**
 * General Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WPDL_Settings_General', false ) ) {
	return new WPDL_Settings_General();
}

/**
 * WPDL_Settings_General class.
 */
class WPDL_Settings_General extends WPDL_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'wp-desklite' );

		parent::__construct();
		$this->notices();
	}

	/**
	 * Get sections.
	 */
	public function get_sections() {
		$sections = array(
			''             => __( 'General options', 'wp-desklite' ),
		);

		return apply_filters( 'wpdl_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output a color picker input box.
	 */
	public function color_picker( $name, $id, $value, $desc = '' ) {
		echo '<div class="color_box">' . wpdl_help_tip( $desc ) . '
			<input name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
	}

	/**
	 * Notices.
	 */
	private function notices() {

	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WPDL_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WPDL_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {

			do_action( 'wpdl_update_options_' . $this->id . '_' . $current_section );

		}
	}

	/**
	 * Get settings array.
	 */
	public function get_settings( $current_section = '' ) {
		if ( 'x' === $current_section ) {

		} else {
			$settings = apply_filters(
				'wpdl_general_settings', array(

					array(
						'title' => __( 'General Settings', 'wp-desklite' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_options',
					),

					array(
						'title'    => __( 'My Tickets page', 'wp-desklite' ),
						'desc'     => sprintf( __( 'Page contents: [%s]', 'wp-desklite' ), 'wpdl_my_tickets' ),
						'id'       => 'wpdl_tickets_page_id',
						'type'     => 'single_select_page',
						'default'  => '',
						'class'    => 'wpdl-select',
						'desc_tip' => true,
					),

					array(
						'title'    		=> __( 'Support Roles', 'wp-desklite' ),
						'desc'     		=> __( 'Users with specified roles will have access to support tickets as operators.', 'wp-desklite' ),
						'placeholder'	=> __( 'Select user roles...', 'wp-desklite' ),
						'id'       		=> 'wpdl_support_roles',
						'default' 		=> array( 'operator' ),
						'type'     		=> 'multiselect',
						'class'    		=> 'wpdl-select-multi',
						'options'  		=> wpdl_get_roles(),
						'desc_tip' 		=> true,
					),

					array(
						'title'    => __( 'WooCommerce Integration', 'wp-desklite' ),
						'desc'     => __( 'Check this box to enable automatic integration with WooCommerce.', 'wp-desklite' ),
						'id'       => 'wpdl_woocommerce',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'desc_tip' => __( 'This will add an extra tab in front-end Woocommerce account page.', 'wp-desklite' ),
					),

					array(
						'title'    => __( 'Allow Attachments', 'wp-desklite' ),
						'desc'     => __( 'Check this box to allow users to attach files to support tickets.', 'wp-desklite' ),
						'id'       => 'wpdl_attachments',
						'default'  => 'yes',
						'type'     => 'checkbox',
					),

					array(
						'title'    => __( 'Remove all data on uninstall?', 'wp-desklite' ),
						'desc'     => __( 'Check this box to remove all plugin data when it is uninstalled.', 'wp-desklite' ),
						'id'       => 'wpdl_remove_all_data',
						'default'  => 'no',
						'type'     => 'checkbox',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					)

				)
			);
		}

		return apply_filters( 'wpdl_get_settings_' . $this->id, $settings, $current_section );
	}

}

return new WPDL_Settings_General();