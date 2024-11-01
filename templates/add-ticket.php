<?php
/**
 * Add a Ticket.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php do_action( 'wpdl_add_ticket_before_form' ); ?>

<div class="wpdl-form">

	<?php do_action( 'wpdl_add_ticket_before_heading' ); ?>

	<h3 class="wpdl-heading">
		<?php _e( 'Submit a support ticket', 'wp-desklite' ); ?>

		<?php if ( ! empty( $back_button ) ) : ?>
		<a href="<?php echo wpdl_my_tickets_url(); ?>" class="wpdl-btn-link"><span class="wpdl-icon la-arrow-left"></span><?php _e( 'My tickets', 'wp-desklite' ); ?></a> 
		<?php endif; ?>
	</h3>

	<?php do_action( 'wpdl_add_ticket_after_heading' ); ?>

	<form action="" method="post" enctype="multipart/form-data">

		<?php wpdl_print_notices(); ?>

		<?php do_action( 'wpdl_add_ticket_form_start' ); ?>

		<div class="wpdl-field wpdl-field-title wpdl-required <?php wpdl_error_class( '_wpdl_title' ); ?>">
			<div class="wpdl-field-label"><label for="_wpdl_title"><span class="wpdl-icon la-envelope-open"></span><?php _e( 'Title', 'wp-desklite' ); ?></label></div>
			<div class="wpdl-field-input">
				<input type="text" name="_wpdl_title" id="_wpdl_title" value="" required />
			</div>
		</div>

		<?php do_action( 'wpdl_add_ticket_after_title' ); ?>

		<?php if ( $departments = wpdl_get_departments() ) : ?>
		<div class="wpdl-field wpdl-field-department wpdl-required <?php wpdl_error_class( '_wpdl_department' ); ?>">
			<div class="wpdl-field-label"><label for="_wpdl_department"><span class="wpdl-icon la-user"></span><?php _e( 'Department', 'wp-desklite' ); ?></label></div>
			<div class="wpdl-field-input">
				<select name="_wpdl_department" id="_wpdl_department" required>
					<option value=""><?php _e( '&ndash; Select a department &ndash;', 'wp-desklite' ); ?></option>
					<?php foreach( $departments as $term ) : ?>
					<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php endif; ?>

		<?php do_action( 'wpdl_add_ticket_after_department' ); ?>

		<?php if ( $types = wpdl_get_ticket_types() ) : ?>
		<div class="wpdl-field wpdl-field-type wpdl-required <?php wpdl_error_class( '_wpdl_type' ); ?>">
			<div class="wpdl-field-label"><label for="_wpdl_type"><span class="wpdl-icon la-question-circle"></span><?php _e( 'Ticket Type', 'wp-desklite' ); ?></label></div>
			<div class="wpdl-field-input">
				<select name="_wpdl_type" id="_wpdl_type" required>
					<option value=""><?php _e( '&ndash; Select a ticket type &ndash;', 'wp-desklite' ); ?></option>
					<?php foreach( $types as $term ) : ?>
					<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php endif; ?>

		<?php do_action( 'wpdl_add_ticket_after_type' ); ?>

		<div class="wpdl-field wpdl-field-priority wpdl-required <?php wpdl_error_class( '_wpdl_priority' ); ?>">
			<div class="wpdl-field-label"><label for="_wpdl_priority"><span class="wpdl-icon la-clock"></span><?php _e( 'Priority', 'wp-desklite' ); ?></label></div>
			<div class="wpdl-field-input">
				<select name="_wpdl_priority" id="_wpdl_priority" required>
					<?php foreach( wpdl_get_priorities() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<?php do_action( 'wpdl_add_ticket_after_priority' ); ?>

		<div class="wpdl-field wpdl-field-description wpdl-required <?php wpdl_error_class( '_wpdl_ticket_message' ); ?>">
			<div class="wpdl-field-label"><label for="_wpdl_ticket_message"><span class="wpdl-icon la-pen"></span><?php _e( 'Description', 'wp-desklite' ); ?></label></div>
			<div class="wpdl-field-input">
				<?php wpdl_wp_editor( '', '_wpdl_ticket_message' ); ?>
			</div>
		</div>

		<?php do_action( 'wpdl_add_ticket_after_description' ); ?>

		<?php if ( get_option( 'wpdl_attachments' ) === 'yes' ) : ?>
		<div class="wpdl-field wpdl-field-attachments">
			<div class="wpdl-field-label"><label for="_wpdl_ticket_files"><span class="wpdl-icon la-paperclip"></span><?php _e( 'Attachments', 'wp-desklite' ); ?></label></div>
			<div class="wpdl-field-input">
				<input type="file" class="wpdl-attachments" name="_wpdl_ticket_files[]" id="_wpdl_ticket_files" multiple="multiple" />
			</div>
		</div>
		<?php endif; ?>

		<?php do_action( 'wpdl_add_ticket_after_attachments' ); ?>

		<div class="wpdl-field wpdl-field-submit">
			<div class="wpdl-field-label"></div>
			<div class="wpdl-field-input">
				<button type="submit" class="button button-primary"><?php _e( 'Submit', 'wp-desklite' ); ?></button>
			</div>
		</div>

		<?php do_action( 'wpdl_add_ticket_form_end' ); ?>

		<?php wp_nonce_field( 'wpdl_add_ticket', 'wpdl_add_ticket_nonce' ); ?>

	</form>

</div>

<?php do_action( 'wpdl_add_ticket_after_form' ); ?>