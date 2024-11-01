<?php
/**
 * Ticket Reply Attachments.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php if ( $attachments = wpdl_get_reply_attachments( $reply_id ) ) : ?>

	<div class="wpdl-files">

		<div class="wpdl-files-head"><span class="wpdl-icon la-paperclip"></span><?php echo sprintf( __( '%s Attachment(s)', 'wp-desklite' ), count( $attachments ) ); ?></div>

		<?php foreach( $attachments as $attachment_id ) : ?>
		<?php
			$thumbnail = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
			$original  = wp_get_attachment_image_src( $attachment_id, 'full' );
		?>

		<div class="wpdl-file">
			<a href="<?php echo $original[0]; ?>" target="_blank"><img src="<?php echo $thumbnail[0]; ?>" alt="" /></a>
		</div>

		<?php endforeach; ?>

	</div>

<?php endif; ?>