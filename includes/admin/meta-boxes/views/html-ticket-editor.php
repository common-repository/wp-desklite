<?php
/**
 * Ticket meta box.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wpdl_wp_editor( wpautop( $post->post_content ), '_wpdl_ticket_message' );

?>

<?php if ( get_option( 'wpdl_attachments' ) === 'yes' ) : ?>

<div class="wpdl-attachment-wrap">

	<label class="wpdl-attachment-label" for="_wpdl_ticket_files"><span class="wpdl-icon la-paperclip"></span><?php _e( 'Upload attachments', 'wp-desklite' ); ?></label>

	<input type="file" class="wpdl-attachments" name="_wpdl_ticket_files[]" id="_wpdl_ticket_files" multiple="multiple" />

</div>

<?php endif; ?>