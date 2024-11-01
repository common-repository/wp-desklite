<?php
/**
 * Ticket Reply.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$author = get_userdata( $reply->user_id );

?>

<div class="wpdl-item wpdl-reply <?php echo esc_attr( $reply->comment_type ); ?>">

	<div class="wpdl-item-1"><div class="wpdl-avatar-u"><?php echo get_avatar( $reply->comment_author_email, 54 ); ?></div></div>

	<div class="wpdl-item-2">

		<div class="wpdl-head">
			<?php if ( $reply->comment_type == 'ticket_note' ) : ?>
			<div class="wpdl-header"><?php echo sprintf( __( '%s <span>left a note</span>', 'wp-desklite' ), wpdl_get_name( $author, true ) ); ?></div>
			<?php else : ?>
			<div class="wpdl-header"><?php echo sprintf( __( '%s <span>added a reply</span>', 'wp-desklite' ), wpdl_get_name( $author, true ) ); ?></div>
			<?php endif; ?>
		</div>

		<?php echo wpautop( $reply->comment_content ); ?>

		<?php wpdl_get_template( 'ticket-reply-attachments.php', array( 'reply_id' => $reply->comment_ID ) ); ?>

		<div class="wpdl-foot">
			<?php if ( get_current_user_id() == $reply->user_id || ( current_user_can( 'add_wpdl_tickets' ) ) ) : ?>
			<div class="wpdl-do"><a href="#" class="wpdl-ajax-link" data-action="wpdl_remove_reply" data-id="<?php echo absint( $reply->comment_ID ); ?>"><span class="wpdl-icon la-trash-alt"></span><?php _e( 'Delete', 'wp-desklite' ); ?></a></div>
			<?php endif; ?>
		</div>

	</div>

</div>