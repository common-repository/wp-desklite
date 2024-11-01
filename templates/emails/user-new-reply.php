<?php
/**
 * New ticket reply email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php do_action( 'wpdl_email_header', $email_heading, $email ); ?>

<p>
	<strong><?php _e( 'Support Response:', 'wp-desklite' ); ?></strong><br />
	<?php echo stripslashes( wp_strip_all_tags( $ticket->get_reply() ) ); ?>
</p>

<p class="button"><a href="<?php echo $ticket->get_customer_ticket_url(); ?>" class="link"><?php esc_html_e( 'View support ticket', 'wp-desklite' ); ?></a></p>

<?php do_action( 'wpdl_email_footer', $email ); ?>