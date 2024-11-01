<?php
/**
 * My tickets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php do_action( 'wpdl_before_my_tickets_heading' ); ?>

<h3 class="wpdl-heading">
	<?php _e( 'My Tickets', 'wp-desklite' ); ?>
	<a href="<?php echo wpdl_add_ticket_url(); ?>" class="wpdl-btn-link wpdl-btn-add-ticket"><span class="wpdl-icon la-plus"></span><?php _e( 'Add a new ticket', 'wp-desklite' ); ?></a>
</h3>

<?php do_action( 'wpdl_before_my_tickets_table' ); ?>

<table class="wpdl-table">
	<thead>
		<tr>
			<th class="wpdl-column-id"><?php _e( 'ID', 'wp-desklite' ); ?></th>
			<th class="wpdl-column-title"><?php _e( 'Title', 'wp-desklite' ); ?></th>
			<th class="wpdl-column-status"><?php _e( 'Status', 'wp-desklite' ); ?></th>
			<th class="wpdl-column-department"><?php _e( 'Department', 'wp-desklite' ); ?></th>
			<th class="wpdl-column-priority"><?php _e( 'Priority', 'wp-desklite' ); ?></th>
			<th class="wpdl-column-created"><?php _e( 'Created on', 'wp-desklite' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $my_tickets as $my_ticket ) : $the_ticket = new WPDL_Ticket( $my_ticket->ID ); ?>
		<tr>
			<td><a href="<?php echo $the_ticket->get_ticket_url(); ?>"><?php echo absint( $the_ticket->id ); ?></a></td>
			<td><a href="<?php echo $the_ticket->get_ticket_url(); ?>"><?php echo $the_ticket->get_subject(); ?></a></td>
			<td><?php echo $the_ticket->get_status(); ?></td>
			<td><?php echo $the_ticket->get_department(); ?></td>
			<td><?php echo $the_ticket->get_priority(); ?></td>
			<td><?php echo $the_ticket->created_on(); ?></td>
		</tr>
		<?php endforeach; ?>

		<?php if ( count( $my_tickets ) == 0 ) : ?>
		<tr>
			<td colspan="6" class="wpdl-column-nodata"><?php _e( 'No support tickets do display', 'wp-desklite' ); ?></td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

<?php do_action( 'wpdl_after_my_tickets_table' ); ?>