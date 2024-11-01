<?php
/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_exists        = isset( $tabs[ $current_tab ] ) || has_action( 'wpdl_sections_' . $current_tab ) || has_action( 'wpdl_settings_' . $current_tab ) || has_action( 'wpdl_settings_tabs_' . $current_tab );
$current_tab_label = isset( $tabs[ $current_tab ] ) ? $tabs[ $current_tab ] : '';

?>
<div class="wrap wpdl wpdl-<?php echo $current_tab; ?>">
	<form method="<?php echo esc_attr( apply_filters( 'wpdl_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper wpdl-nav-tab-wrapper">
			<?php

			foreach ( $tabs as $slug => $label ) {
				echo '<a href="' . esc_html( admin_url( 'edit.php?post_type=wpdl_ticket&page=wpdl-settings&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
			}

			do_action( 'wpdl_settings_tabs' );

			?>
		</nav>
		<h1 class="screen-reader-text"><?php echo esc_html( $current_tab_label ); ?></h1>
		<?php
			do_action( 'wpdl_sections_' . $current_tab );

			self::show_messages();

			do_action( 'wpdl_settings_' . $current_tab );
		?>
		<p class="submit">
			<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
				<button name="save" class="button-primary wpdl-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'wp-desklite' ); ?>"><?php esc_html_e( 'Save changes', 'wp-desklite' ); ?></button>
			<?php endif; ?>
			<?php wp_nonce_field( 'wpdl-settings' ); ?>
		</p>
	</form>
</div>