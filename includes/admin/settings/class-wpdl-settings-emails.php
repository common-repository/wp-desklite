<?php
/**
 * Email Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WPDL_Settings_Emails', false ) ) {
	return new WPDL_Settings_Emails();
}

/**
 * WPDL_Settings_Emails class.
 */
class WPDL_Settings_Emails extends WPDL_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'email';
		$this->label = __( 'Emails', 'wp-desklite' );

		add_action( 'wpdl_admin_field_email_notification', array( $this, 'email_notification_setting' ) );
		parent::__construct();
	}

	/**
	 * Get sections.
	 */
	public function get_sections() {
		$sections = array(
			''             => __( 'Email options', 'wp-desklite' ),
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
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		// Define emails that can be customised here.
		$mailer          = wpdl()->mailer();
		$email_templates = $mailer->get_emails();

		if ( $current_section ) {
			foreach ( $email_templates as $email_key => $email ) {
				if ( strtolower( $email_key ) === $current_section ) {
					$email->admin_options();
					break;
				}
			}
		} else {
			$settings = $this->get_settings();
			WPDL_Admin_Settings::output_fields( $settings );
		}

	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		if ( ! $current_section ) {
			WPDL_Admin_Settings::save_fields( $this->get_settings() );

		} else {
			$wpdl_emails = WPDL_Emails::instance();

			if ( in_array( $current_section, array_map( 'sanitize_title', array_keys( $wpdl_emails->get_emails() ) ), true ) ) {
				foreach ( $wpdl_emails->get_emails() as $email_id => $email ) {
					if ( sanitize_title( $email_id ) === $current_section ) {
						do_action( 'wpdl_update_options_' . $this->id . '_' . $email->id );
					}
				}
			} else {
				do_action( 'wpdl_update_options_' . $this->id . '_' . $current_section );
			}
		}
	}

	/**
	 * Get settings array.
	 */
	public function get_settings( $current_section = '' ) {
		$settings = apply_filters(
			'wpdl_email_settings', array(

				array(
					'title' => __( 'Email notifications', 'wp-desklite' ),
					'desc'  => __( 'Email notifications sent by the plugin are listed below. Click on an email to configure it.', 'wp-desklite' ),
					'type'  => 'title',
					'id'    => 'email_notification_settings',
				),

				array( 'type' => 'email_notification' ),

				array(
					'type' => 'sectionend',
					'id'   => 'email_notification_settings',
				),

				array(
					'type' => 'sectionend',
					'id'   => 'email_recipient_options',
				),

				array(
					'title' => __( 'Email sender options', 'wp-desklite' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'email_options',
				),

				array(
					'title'    => __( '"From" name', 'wp-desklite' ),
					'desc'     => __( 'How the sender name appears in outgoing emails.', 'wp-desklite' ),
					'id'       => 'wpdl_email_from_name',
					'type'     => 'text',
					'default'  => esc_attr( get_bloginfo( 'name', 'display' ) ),
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'title'             => __( '"From" address', 'wp-desklite' ),
					'desc'              => __( 'How the sender email appears in outgoing emails.', 'wp-desklite' ),
					'id'                => 'wpdl_email_from_address',
					'type'              => 'email',
					'custom_attributes' => array(
						'multiple' => 'multiple',
					),
					'default'           => get_option( 'admin_email' ),
					'autoload'          => false,
					'desc_tip'          => true,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'email_options',
				),

				array(
					'title' => __( 'Email template', 'wp-desklite' ),
					'type'  => 'title',
					'desc'  => sprintf( wp_kses_post( __( 'This section lets you customize the Support Ticket emails. <a href="%s" target="_blank">Click here to preview your email template</a>.', 'wp-desklite' ) ), wp_nonce_url( admin_url( '?preview_wpdl_mail=true' ), 'preview-mail' ) ),
					'id'    => 'email_template_options',
				),

				array(
					'title'       => __( 'Header image', 'wp-desklite' ),
					'desc'        => __( 'URL to an image you want to show in the email header. Upload images using the media uploader (Admin > Media).', 'wp-desklite' ),
					'id'          => 'wpdl_email_header_image',
					'type'        => 'text',
					'placeholder' => __( 'N/A', 'wp-desklite' ),
					'default'     => '',
					'autoload'    => false,
					'desc_tip'    => true,
				),

				array(
					'title'       => __( 'Footer text (HTML)', 'wp-desklite' ),
					'desc'        => __( 'The text to appear in the footer of Support Ticket emails.', 'wp-desklite' ) . ' ' . sprintf( __( 'Available placeholders: %s', 'wp-desklite' ), '{site_title}' ),
					'id'          => 'wpdl_email_footer_text',
					'css'         => 'height:75px;',
					'placeholder' => __( 'N/A', 'wp-desklite' ),
					'type'        => 'textarea',
					'default'     => '{site_title}<br />Built by WP DeskLite',
					'autoload'    => false,
					'desc_tip'    => true,
				),

				array(
					'title'       => __( 'Footer text (Plain)', 'wp-desklite' ),
					'desc'        => __( 'The text to appear in the footer of Support Tickets plain text emails.', 'wp-desklite' ) . ' ' . sprintf( __( 'Available placeholders: %s', 'wp-desklite' ), '{site_title}' ),
					'id'          => 'wpdl_email_footer_text_plain',
					'css'         => 'height:75px;',
					'placeholder' => __( 'N/A', 'wp-desklite' ),
					'type'        => 'textarea',
					'default'     => sprintf( wp_kses_post( __( 'Best regards,%s%s', 'wp-desklite' ) ), "\r\n", '{site_title}' ),
					'autoload'    => false,
					'desc_tip'    => true,
				),

				array(
					'title'    => __( 'Base color', 'wp-desklite' ),
					'desc'     => sprintf( wp_kses_post( __( 'The base color for Support Tickets email templates. Default %s.', 'wp-desklite' ) ), '<code>#027FD2</code>' ),
					'id'       => 'wpdl_email_base_color',
					'type'     => 'color',
					'css'      => 'width:6em;',
					'default'  => '#027FD2',
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Background color', 'wp-desklite' ),
					'desc'     => sprintf( wp_kses_post( __( 'The background color for Support Tickets email templates. Default %s.', 'wp-desklite' ) ), '<code>#f7f7f7</code>' ),
					'id'       => 'wpdl_email_background_color',
					'type'     => 'color',
					'css'      => 'width:6em;',
					'default'  => '#f7f7f7',
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Body background color', 'wp-desklite' ),
					'desc'     => sprintf( wp_kses_post( __( 'The main body background color. Default %s.', 'wp-desklite' ) ), '<code>#ffffff</code>' ),
					'id'       => 'wpdl_email_body_background_color',
					'type'     => 'color',
					'css'      => 'width:6em;',
					'default'  => '#ffffff',
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Body text color', 'wp-desklite' ),
					'desc'     => sprintf( wp_kses_post( __( 'The main body text color. Default %s.', 'wp-desklite' ) ), '<code>#66757f</code>' ),
					'id'       => 'wpdl_email_text_color',
					'type'     => 'color',
					'css'      => 'width:6em;',
					'default'  => '#66757f',
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'email_template_options',
				)

			)
		);

		return apply_filters( 'wpdl_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Output email notification settings.
	 */
	public function email_notification_setting() {
		// Define emails that can be customised here.
		$mailer          = wpdl()->mailer();
		$email_templates = $mailer->get_emails();

		?>
		<tr valign="top">
		<td class="wpdl_emails_wrapper" colspan="2">
			<table class="wpdl_emails widefat striped" cellspacing="0">
				<thead>
					<tr>
						<?php
						$columns = apply_filters(
							'wpdl_email_setting_columns', array(
								'status'     => '',
								'name'       => __( 'Email', 'wp-desklite' ),
								'email_type' => __( 'Content type', 'wp-desklite' ),
								'recipient'  => __( 'Recipient(s)', 'wp-desklite' ),
								'actions'    => '',
							)
						);
						foreach ( $columns as $key => $column ) {
							echo '<th class="wpdl-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
						}
						?>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $email_templates as $email_key => $email ) {
							echo '<tr>';

							foreach ( $columns as $key => $column ) {

								switch ( $key ) {
									case 'name':
										echo '<td class="wpdl-email-settings-table-' . esc_attr( $key ) . '">
										<a href="' . esc_url( admin_url( 'admin.php?page=wpdl-settings&tab=email&section=' . strtolower( $email_key ) ) ) . '">' . esc_html( $email->get_title() ) . '</a>
										' . wpdl_help_tip( $email->get_description() ) . '
									</td>';
										break;
									case 'recipient':
										echo '<td class="wpdl-email-settings-table-' . esc_attr( $key ) . '">
										' . esc_html( $email->is_user_email() ? __( 'User', 'wp-desklite' ) : $email->get_recipient() ) . '
									</td>';
										break;
									case 'status':
										echo '<td class="wpdl-email-settings-table-' . esc_attr( $key ) . '">';

										if ( $email->is_manual() ) {
											echo '<span class="status-manual wpdl-help-tip" data-tip="' . esc_attr__( 'Manually sent', 'wp-desklite' ) . '">' . wpdl_svg_icon( 'arrow-right' ) . '</span>';
										} elseif ( $email->is_enabled() ) {
											echo '<span class="status-enabled wpdl-help-tip" data-tip="' . esc_attr__( 'Enabled', 'wp-desklite' ) . '">' . wpdl_svg_icon( 'check' ) . '</span>';
										} else {
											echo '<span class="status-disabled wpdl-help-tip" data-tip="' . esc_attr__( 'Disabled', 'wp-desklite' ) . '">' . wpdl_svg_icon( 'x' ) . '</span>';
										}

										echo '</td>';
										break;
									case 'email_type':
										echo '<td class="wpdl-email-settings-table-' . esc_attr( $key ) . '">
										' . esc_html( $email->get_content_type() ) . '
									</td>';
										break;
									case 'actions':
										echo '<td class="wpdl-email-settings-table-' . esc_attr( $key ) . '">
										<a class="button" href="' . esc_url( admin_url( 'admin.php?page=wpdl-settings&tab=email&section=' . strtolower( $email_key ) ) ) . '">' . wpdl_svg_icon( 'settings' ) . '</a>
									</td>';
										break;
									default:
										do_action( 'wpdl_email_setting_column_' . $key, $email );
										break;
								}
							}

							echo '</tr>';
						}
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}

}

return new WPDL_Settings_Emails();