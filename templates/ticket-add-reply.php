<?php
/**
 * Ticket Reply Form.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wpdl-element">

	<!-- reply content -->
	<div class="wpdl-elementsub">
		<?php wpdl_wp_editor( '', '_wpdl_reply' ); ?>
	</div>

	<!-- attachments -->
	<?php if ( get_option( 'wpdl_attachments' ) === 'yes' ) : ?>
	<div class="wpdl-elementsub">
		<div class="wpdl-attachment-wrap">
			<label class="wpdl-attachment-label" for="_wpdl_files"><span class="wpdl-icon la-paperclip"></span><?php _e( 'Upload attachments', 'wp-desklite' ); ?></label>
			<input type="file" class="wpdl-attachments" name="_wpdl_files" id="_wpdl_files" multiple="multiple" />
		</div>
	</div>
	<?php endif; ?>

	<!-- status, priority, ... -->
	<div class="wpdl-elementsub">
		<div class="wpdl-subfield">
			<label for="_wpdl_set_resolved"><input type="checkbox" name="_wpdl_set_resolved" id="_wpdl_set_resolved" value="1" <?php if ( $the_ticket->is_resolved ) echo 'checked'; ?> /> <?php _e( 'Mark this ticket as resolved', 'wp-desklite' ); ?></label>
		</div>
		<div class="wpdl-subfield">
			<label for="_wpdl_set_priority"><?php _e( 'Priority:', 'wp-desklite' ); ?></label>
			<select name="_wpdl_set_priority" id="_wpdl_set_priority">
				<?php foreach( wpdl_get_priorities() as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $the_ticket->priority ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<!-- submit as reply/note -->
	<div class="wpdl-elementsub">
		<a href="#" class="wpdl-ajax wpdl-add-reply button button-primary" data-ticket="<?php echo absint( $the_ticket->id ); ?>"><?php _e( 'Add reply', 'wp-desklite' ); ?></a>
		<?php if ( current_user_can( 'edit_wpdl_ticket', $the_ticket->id ) ) : ?>
		<a href="#" class="wpdl-ajax wpdl-add-note button button-secondary" data-ticket="<?php echo absint( $the_ticket->id ); ?>"><?php _e( 'Add as a note', 'wp-desklite' ); ?></a>
		<?php endif; ?>
	</div>

</div>