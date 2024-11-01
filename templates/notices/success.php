<?php
/**
 * Show messages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $messages ) {
	return;
}

?>

<?php foreach ( $messages as $message ) : ?>
	<div class="wpdl-success" role="alert">
		<?php
			echo wpdl_kses_notice( $message );
		?>
	</div>
<?php endforeach; ?>