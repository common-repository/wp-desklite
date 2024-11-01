<?php
/**
 * Operator New Ticket Reply.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Email_Operator_New_Reply class.
 */
class WPDL_Email_Operator_New_Reply extends WPDL_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id             = 'operator_new_reply';
		$this->is_user_email  = true;

		$this->title       = esc_html__( 'New ticket reply (operator)', 'wp-desklite' );
		$this->description = esc_html__( 'Operator "new ticket reply" emails are sent when a customer replies to a support ticket.', 'wp-desklite' );

		$this->template_html  = 'emails/operator-new-reply.php';
		$this->template_plain = 'emails/plain/operator-new-reply.php';
		$this->placeholders   = array(
			'{ticket_id}'   => '',
			'{subject}'		=> '',
		);

		// Trigger.
		add_action( 'wpdl_operator_new_reply_notification', array( $this, 'trigger' ), 10 );

		// Call parent constructor.
		parent::__construct();
	}

	/**
	 * Get email subject.
	 */
	public function get_default_subject() {
		return __( '[RE: Ticket #{ticket_id}] {subject}', 'wp-desklite' );
	}

	/**
	 * Get email heading.
	 */
	public function get_default_heading() {
		return '';
	}

	/**
	 * Trigger.
	 */
	public function trigger( $ticket = '' ) {
		$this->setup_locale();

		if ( is_object( $ticket ) ) {
			$this->object     = $ticket;
			$this->recipient  = $this->object->get_operator_email();
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
			$this->template_html, array(
				'ticket'		=> $this->object,
				'email_heading' => $this->get_heading(),
				'blogname'      => $this->get_blogname(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
			)
		);
	}

	/**
	 * Get content plain.
	 */
	public function get_content_plain() {
		return wpdl_get_template_html(
			$this->template_plain, array(
				'ticket'		=> $this->object,
				'email_heading' => $this->get_heading(),
				'blogname'      => $this->get_blogname(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this,
			)
		);
	}

}

return new WPDL_Email_Operator_New_Reply();