<?php
/**
 * Taxonomies Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Admin_Taxonomies class.
 */
class WPDL_Admin_Taxonomies {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'wpdl_ticket_dep_add_form_fields', 		array( $this, 'add_form_fields' ), 10, 2 );
		add_action( 'wpdl_ticket_dep_edit_form_fields', 		array( $this, 'edit_form_fields' ), 10, 2 );
		add_action( 'wpdl_ticket_type_add_form_fields', 		array( $this, 'add_form_fields' ), 10, 2 );
		add_action( 'wpdl_ticket_type_edit_form_fields', 	array( $this, 'edit_form_fields' ), 10, 2 );

		add_action( 'created_wpdl_ticket_dep', 	array( $this, 'save_term_meta' ) );
		add_action( 'edited_wpdl_ticket_dep', 	array( $this, 'save_term_meta' ) );
		add_action( 'created_wpdl_ticket_type', 	array( $this, 'save_term_meta' ) );
		add_action( 'edited_wpdl_ticket_type', 	array( $this, 'save_term_meta' ) );
	}

	/**
	 * Add fields.
	 */
	public function add_form_fields( $taxonomy ) {
		?>
		<div class="form-field term-woo-color">
			<label><?php _e( 'Color (optional)', 'wp-desklite' ); ?></label>
			<div class="wpdl-labels">
				<?php $i = 0; foreach( wpdl_get_color_options() as $color ) : $i++; if ( $i == 1 ) { $class = 'default'; } else { $class = ''; } ?>
				<label>
					<input type="radio" name="_wpdl_color" value="<?php echo esc_attr( $color ); ?>" <?php if ( $i == 1 ) echo 'checked'; ?> />
					<span class="wpdl-label <?php echo esc_attr( $class ); ?>" style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
				</label>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Edit fields.
	 */
	public function edit_form_fields( $term ) {

		$wpdl_color = get_term_meta( $term->term_id, '_wpdl_color', true );
		$wpdl_color = ( ! empty( $wpdl_color ) ) ? "#{$wpdl_color}" : '#eeeeee';
		?>
		<tr class="form-field term-colorpicker-wrap">
			<th scope="row"><label><?php _e( 'Color (optional)', 'wp-desklite' ); ?></label></th>
			<td>
				<div class="wpdl-labels">
					<?php $i = 0; foreach( wpdl_get_color_options() as $color ) : $i++; if ( $i == 1 ) { $class = 'default'; } else { $class = ''; } ?>
					<?php
						if ( $wpdl_color == $color ) {
							$checked = 'checked';
						} else {
							$checked = '';
						}
					?>
					<label>
						<input type="radio" name="_wpdl_color" value="<?php echo esc_attr( $color ); ?>" <?php echo $checked; ?> />
						<span class="wpdl-label <?php echo esc_attr( $class ); ?>" style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
					</label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save term meta.
	 */
	public function save_term_meta( $term_id ) {

		// Save term color if possible
		if( isset( $_POST['_wpdl_color'] ) && ! empty( $_POST['_wpdl_color'] ) ) {
			update_term_meta( $term_id, '_wpdl_color', sanitize_hex_color_no_hash( $_POST['_wpdl_color'] ) );
		} else {
			delete_term_meta( $term_id, '_wpdl_color' );
		}
	}

}

new WPDL_Admin_Taxonomies();