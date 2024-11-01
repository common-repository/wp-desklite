<?php
/**
 * Show error messages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $messages ) {
	return;
}

?>

<?php if ( count( $messages ) > 1 ) : ?>

	<ul class="wpdl-errors" role="alert">
		<li class="wpdl-error-header"><?php esc_html_e( 'Please correct the following errors:', 'wp-desklite' ); ?></li>
		<?php foreach ( $messages as $message ) : ?>
			<li>
				<?php
					echo wpdl_kses_notice( $message );
				?>
			</li>
		<?php endforeach; ?>
	</ul>

<?php else : ?>

	<?php foreach ( $messages as $message ) : ?>
		<div class="wpdl-error" role="alert">
			<?php
				echo wpdl_kses_notice( $message );
			?>
		</div>
	<?php endforeach; ?>

<?php endif; ?>