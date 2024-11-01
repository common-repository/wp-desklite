<?php
/**
 * Ticket.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$customer = $the_ticket->get_customer();
if ( ! $customer ) :
	wpdl_get_template( 'ticket-empty.php' ); return;
endif;
?>

<?php if ( ! is_admin() ) : ?>
<div class="wpdl-area-top">
	<h3 class="wpdl-heading">
		<?php _e( 'My Tickets', 'wp-desklite' ); ?>
		<a href="<?php echo wpdl_my_tickets_url(); ?>" class="wpdl-btn-link wpdl-btn-my-tickets"><span class="wpdl-icon la-copy"></span><?php _e( 'My Tickets', 'wp-desklite' ); ?>
		<a href="<?php echo wpdl_add_ticket_url(); ?>" class="wpdl-btn-link wpdl-btn-add-ticket"><span class="wpdl-icon la-plus"></span><?php _e( 'Add a new ticket', 'wp-desklite' ); ?></a>
	</h3>
</div>

<div class="wpdl-info">

	<div class="wpdl-info-row">
		<div class="wpdl-info-col">
			<span class="wpdl-info-col-title"><?php _e( 'Subject', 'wp-desklite' ); ?></span>
			<span class="wpdl-info-col-data"><?php echo $the_ticket->get_subject(); ?></span>
		</div>
		<div class="wpdl-info-col">
			<span class="wpdl-info-col-title"><?php _e( 'Department', 'wp-desklite' ); ?></span>
			<span class="wpdl-info-col-data"><?php echo $the_ticket->get_department(); ?></span>
		</div>
	</div>

	<div class="wpdl-info-row">
		<div class="wpdl-info-col">
			<span class="wpdl-info-col-title"><?php _e( 'Status', 'wp-desklite' ); ?></span>
			<span class="wpdl-info-col-data"><?php echo $the_ticket->get_status(); ?></span>
		</div>
		<div class="wpdl-info-col">
			<span class="wpdl-info-col-title"><?php _e( 'Priority', 'wp-desklite' ); ?></span>
			<span class="wpdl-info-col-data"><?php echo $the_ticket->get_priority(); ?></span>
		</div>
	</div>

	<div class="wpdl-info-row">
		<div class="wpdl-info-col">
			<span class="wpdl-info-col-title"><?php _e( 'Created on', 'wp-desklite' ); ?></span>
			<span class="wpdl-info-col-data"><?php echo $the_ticket->created_on(); ?></span>
		</div>
		<div class="wpdl-info-col">
			<span class="wpdl-info-col-title"><?php _e( 'Last modified', 'wp-desklite' ); ?></span>
			<span class="wpdl-info-col-data"><?php echo $the_ticket->last_modified(); ?></span>
		</div>
	</div>

</div>
<?php endif; ?>

<div class="wpdl-area">

	<div class="wpdl-main">

		<?php wpdl_get_template( 'ticket-message.php', array( 'customer' => $customer ) ); ?>

		<div class="wpdl-thread">

			<?php
				if ( $the_ticket->get_replies() ) :
					foreach( $the_ticket->get_replies() as $reply ) :
						wpdl_get_template( 'ticket-reply.php', array( 'reply' => $reply ) );
					endforeach;
				endif;
			?>

		</div>

		<?php
			if ( wpdl_user_can_reply() ) :
				wpdl_get_template( 'ticket-add-reply.php' );
			endif;
		?>

	</div>

	<?php if ( is_admin() ) : wpdl_get_template( 'ticket-meta.php', array( 'customer' => $customer ) ); endif; ?>

</div>