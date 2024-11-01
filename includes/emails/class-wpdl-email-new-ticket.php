<?php
/**
 * New Account Email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Email_New_Ticket class.
 */
class WPDL_Email_New_Ticket extends WPDL_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'new_ticket';
		$this->title          = __( 'New ticket notification', 'wp-desklite' );
		$this->description    = __( 'New ticket notification emails are sent to chosen recipient(s) when a user creates a support ticket.', 'wp-desklite' );
		$this->template_html  = 'emails/admin-new-ticket.php';
		$this->template_plain = 'emails/plain/admin-new-ticket.php';
		$this->placeholders   = array(
			'{ticket_id}'   => '',
			'{subject}'		=> '',
		);

		// Trigger.
		add_action( 'wpdl_new_ticket_notification', array( $this, 'trigger' ), 10 );

		// Call parent constructor.
		parent::__construct();

		// Other settings.
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
	}

	/**
	 * Get email subject.
	 */
	public function get_default_subject() {
		return __( '[New Ticket #{ticket_id}] {subject}', 'wp-desklite' );
	}

	/**
	 * Get email heading.
	 */
	public function get_default_heading() {
		return __( 'New support ticket', 'wp-desklite' );
	}

	/**
	 * Trigger.
	 */
	public function trigger( $ticket = '' ) {
		$this->setup_locale();

		if ( is_object( $ticket ) ) {
			$this->object     = $ticket;
			$this->placeholders['{ticket_id}']   	= $ticket->get_id();
			$this->placeholders['{subject}']		= $ticket->get_subject();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 */
	public function get_content_html() {
		return wpdl_get_template_html(
			$this->template_html,
			array(
				'ticket'			 => $this->object,
				'email_heading'      => $this->get_heading(),
				'blogname'      	 => $this->get_blogname(),
				'sent_to_admin'      => true,
				'plain_text'         => false,
				'email'              => $this,
			)
		);
	}

	/**
	 * Get content plain.
	 */
	public function get_content_plain() {
		return wpdl_get_template_html(
			$this->template_plain,
			array(
				'ticket'			 => $this->object,
				'email_heading'      => $this->get_heading(),
				'blogname'      	 => $this->get_blogname(),
				'sent_to_admin'      => true,
				'plain_text'         => true,
				'email'              => $this,
			)
		);
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		/* translators: %s: list of placeholders */
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'wp-desklite' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'wp-desklite' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wp-desklite' ),
				'default' => 'yes',
			),
			'recipient'  => array(
				'title'       => __( 'Recipient(s)', 'wp-desklite' ),
				'type'        => 'text',
				/* translators: %s: WP admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'wp-desklite' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'wp-desklite' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email heading', 'wp-desklite' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'wp-desklite' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wp-desklite' ),
				'default'     => 'html',
				'class'       => 'email_type wpdl-select small',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

}

return new WPDL_Email_New_Ticket();