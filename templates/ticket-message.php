<?php
/**
 * Ticket Message.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wpdl-item">

	<div class="wpdl-item-1"><div class="wpdl-avatar-u"><?php echo get_avatar( $customer->user_email, 54 ); ?></div></div>
	<div class="wpdl-item-2">

		<div class="wpdl-head">
			<div class="wpdl-header"><?php echo sprintf( __( '%s <span>started the conversation</span>', 'wp-desklite' ), wpdl_get_name( $customer, true ) ); ?></div>
		</div>

		<?php echo wpautop( $the_ticket->get_message() ); ?>

		<?php wpdl_get_template( 'ticket-attachments.php' ); ?>

	</div>

</div>