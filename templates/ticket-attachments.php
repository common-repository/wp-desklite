<?php
/**
 * Ticket Attachments.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php if ( $attachments = $the_ticket->get_attachments() ) : ?>

	<div class="wpdl-files">

		<div class="wpdl-files-head"><span class="wpdl-icon la-paperclip"></span><?php echo sprintf( __( '%s Attachment(s)', 'wp-desklite' ), count( $attachments ) ); ?></div>

		<?php foreach( $attachments as $attachment_id ) : ?>
		<?php
			$original = wp_get_attachment_image_src( $attachment_id, 'full' );
		?>

		<div class="wpdl-file">
			<a href="<?php echo $original[0]; ?>" target="_blank"><?php echo basename( $original[0] ); ?></a>
		</div>

		<?php endforeach; ?>

	</div>

<?php endif; ?>