<?php
/**
 * Admin new ticket email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo esc_html( $email_heading ) . "\n";

echo "==========================================\n\n";

echo __( 'Subject:', 'wp-desklite' ) . "\n";
echo $ticket->get_subject() . "\n\n";

echo __( 'Message:', 'wp-desklite' ) . "\n";
echo wpautop( $ticket->get_message() ) . "\n";

echo "==========================================\n\n";

echo __( 'View or reply to this ticket:', 'wp-desklite' ) . "\n";
echo $ticket->get_core_ticket_url() . "\n\n"; 

echo "==========================================\n\n";

echo esc_html( apply_filters( 'wpdl_email_footer_text', get_option( 'wpdl_email_footer_text_plain' ) ) );