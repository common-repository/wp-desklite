<?php
/**
 * Ticket settings meta box.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wpdl-actions">
	<div>
		<label for="_wpdl_assigned_to"><?php _e( 'Assign to', 'wp-desklite' ); ?></label>
	</div>
	<div>
		<select name="_wpdl_assigned_to" id="_wpdl_assigned_to">
			<?php foreach( wpdl_get_operators() as $user_id => $operator ) : ?>
			<option value="<?php echo absint( $user_id ); ?>" <?php selected( $user_id, $the_ticket->assigned_to ); ?>>
				<?php if ( get_current_user_id() === $user_id ) : ?>
				<?php echo __( 'Me', 'wp-desklite' ); ?>
				<?php else : ?>
				<?php echo esc_html( $operator['name'] ); ?>
				<?php endif; ?>
			</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<div class="wpdl-actions">
	<div>
		<label for="_wpdl_priority"><?php _e( 'Priority', 'wp-desklite' ); ?></label>
	</div>
	<div>
		<select name="_wpdl_priority" id="_wpdl_priority">
			<?php foreach( wpdl_get_priorities() as $key => $value ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $the_ticket->priority ); ?>><?php echo esc_html( $value ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<div class="wpdl-actions">
	<div>
		<label for="_wpdl_status"><?php _e( 'Status', 'wp-desklite' ); ?></label>
	</div>
	<div>
		<select name="_wpdl_status" id="_wpdl_status">
			<?php foreach( wpdl_ticket_statuses() as $key => $value ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $the_ticket->post_status ); ?>><?php echo esc_html( $value ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<div class="wpdl-actions">
	<div>
		<input type="button" name="wpdl-update" id="wpdl-update" class="button button-primary button-large" value="<?php echo esc_attr_e( 'Update', 'wp-desklite' ); ?>">
	</div>
</div>