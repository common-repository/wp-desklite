<?php
/**
 * New ticket reply (operator) email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo esc_html( $email_heading ) . "\n";

echo "==========================================\n\n";

echo __( 'Customer&apos;s Response:', 'wp-desklite' ) . "\n";
echo stripslashes( wp_strip_all_tags( $ticket->get_reply() ) ) . "\n";

echo "==========================================\n\n";

echo __( 'View or reply to this ticket:', 'wp-desklite' ) . "\n";
echo $ticket->get_core_ticket_url() . "\n\n"; 

echo "==========================================\n\n";

echo esc_html( apply_filters( 'wpdl_email_footer_text', get_option( 'wpdl_email_footer_text_plain' ) ) );