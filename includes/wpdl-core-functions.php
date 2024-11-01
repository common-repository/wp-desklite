<?php
/**
 * Core Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include core functions (available in both admin and frontend)
require WPDL_ABSPATH . 'includes/wpdl-formatting-functions.php';
require WPDL_ABSPATH . 'includes/wpdl-role-functions.php';
require WPDL_ABSPATH . 'includes/wpdl-ticket-functions.php';
require WPDL_ABSPATH . 'includes/wpdl-user-functions.php';
require WPDL_ABSPATH . 'includes/wpdl-operator-functions.php';
require WPDL_ABSPATH . 'includes/wpdl-wc-functions.php';
require WPDL_ABSPATH . 'includes/wpdl-upload-functions.php';
require WPDL_ABSPATH . 'includes/wpdl-taxonomy-functions.php';

/**
 * Define a constant if it is not already defined.
 */
function wpdl_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Display admin bar to support reps.
 */
function wpdl_show_admin_bar() {
	if ( current_user_can( 'view_admin_dashboard' ) ) {
		return true;
	}
}
add_filter( 'show_admin_bar' , 'wpdl_show_admin_bar', 1000 );

/**
 * Get color options.
 */
function wpdl_get_color_options() {

	$colors = array(
		'#eeeeee',
		'#61bd4f',
		'#b04632',
		'#89609e',
		'#055a8c',
		'#ff9504',
		'#4d5ec3',
		'#565656',
	);

	return apply_filters( 'wpdl_get_color_options', $colors );
}

/**
 * Function for recounting terms
 */
function _wpdl_term_recount( $terms, $taxonomy ) {
    global $wpdb;
 
    $object_types = (array) $taxonomy->object_type;
 
    foreach ( $object_types as &$object_type ) {
        list( $object_type ) = explode( ':', $object_type );
    }
 
    $object_types = array_unique( $object_types );
 
    if ( $object_types ) {
        $object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );
    }
 
    foreach ( (array) $terms as $term ) {
        $count = 0;
 
        if ( $object_types ) {
            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration
            $count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_type IN ('" . implode( "', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );
        }
 
        /** This action is documented in wp-includes/taxonomy.php */
        do_action( 'edit_term_taxonomy', $term, $taxonomy->name );
        $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
 
        /** This action is documented in wp-includes/taxonomy.php */
        do_action( 'edited_term_taxonomy', $term, $taxonomy->name );
    }
}


/**
 * Get template part.
 */
function wpdl_get_template_part( $slug, $name = '' ) {
	global $the_ticket;

	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/wp-desklite/slug-name.php.
	if ( $name && ! WPDL_TEMPLATE_DEBUG_MODE ) {
		$template = locate_template( array( "{$slug}-{$name}.php", wpdl()->template_path() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php.
	if ( ! $template && $name && file_exists( wpdl()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = wpdl()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/wp-desklite/slug.php.
	if ( ! $template && ! WPDL_TEMPLATE_DEBUG_MODE ) {
		$template = locate_template( array( "{$slug}.php", wpdl()->template_path() . "{$slug}.php" ) );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'wpdl_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 */
function wpdl_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	global $the_ticket;

	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = wpdl_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'wpdl_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'wpdl_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

	do_action( 'wpdl_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Like wpdl_get_template, but returns the HTML instead of outputting.
 */
function wpdl_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	global $the_ticket;

	ob_start();
	wpdl_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 */
function wpdl_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	global $the_ticket;

	if ( ! $template_path ) {
		$template_path = wpdl()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = wpdl()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/.
	if ( ! $template || WPDL_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'wpdl_locate_template', $template, $template_name, $template_path );
}

/**
 * WSIWYG style.
 */
function wpdl_get_editor_style() {

	$style = wpdl()->plugin_url() . '/templates/wpdl-editor.css?ver=' . time();

	return apply_filters( 'wpdl_get_editor_style', $style );
}

/**
 * Insert an instance of the editor.
 */
function wpdl_wp_editor( $content = '', $id = '' ) {
	$settings = array(
		'media_buttons' => false,
		'teeny'			=> false,
		'quicktags'		=> false,
		'tinymce'       => array(
			'toolbar1'		=> 'bold,italic,underline,bullist,numlist,blockquote,link,unlink,undo,redo',
			'content_css' 	=> wpdl_get_editor_style(),
		),
		'editor_height' => 120,
	);

	wp_editor( $content, $id, $settings );
}

/**
 * Display a help tip.
 */
function wpdl_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = wpdl_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="wpdl-help-tip" data-tip="' . $tip . '"><i class="dashicons dashicons-editor-help"></i></span>';
}

/**
 * Add new ticket URL.
 */
function wpdl_add_ticket_url() {
	$url = remove_query_arg( 'ticket_id' );
	$url = remove_query_arg( 'submitted', $url );
	$url = add_query_arg( 'new-ticket', '1', $url );

	return apply_filters( 'wpdl_add_ticket_url', $url );
}

/**
 * My tickets URL.
 */
function wpdl_my_tickets_url() {
	$url = remove_query_arg( 'ticket_id' );
	$url = remove_query_arg( 'submitted', $url );
	$url = remove_query_arg( 'new-ticket', $url );

	return apply_filters( 'wpdl_my_tickets_url', $url );
}

/**
 * Get tickets page ID.
 */
function wpdl_get_page_id() {
	return absint( get_option( 'wpdl_tickets_page_id' ) );
}

/**
 * Display a SVG icon from the sprite.
 */
function wpdl_svg_icon( $icon = '' ) {
	$html = '<svg class="feather"><use xlink:href="'. wpdl()->plugin_url() . '/assets/images/feather-sprite.svg#' . esc_html( $icon ) . '" /></svg>';

	// can be used for custom icon output.
	return apply_filters( 'wpdl_svg_icon_html', $html, $icon );
}

/**
 * Outputs a "back" link so admin screens can easily jump back a page.
 */
function wpdl_back_link( $label, $url ) {
	echo '<small class="wpdl-admin-breadcrumb"><a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '">' . __( '&#8592; Back', 'wp-desklite' ) . '</a></small>';
}

/**
 * Queue some JavaScript code to be output in the footer.
 */
function wpdl_enqueue_js( $code ) {
	global $wpdl_queued_js;

	if ( empty( $wpdl_queued_js ) ) {
		$wpdl_queued_js = '';
	}

	$wpdl_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function wpdl_print_js() {
	global $wpdl_queued_js;

	if ( ! empty( $wpdl_queued_js ) ) {
		// Sanitize.
		$wpdl_queued_js = wp_check_invalid_utf8( $wpdl_queued_js );
		$wpdl_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $wpdl_queued_js );
		$wpdl_queued_js = str_replace( "\r", '', $wpdl_queued_js );

		$js = "<!-- WP DeskLite JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $wpdl_queued_js });\n</script>\n";

		echo apply_filters( 'wpdl_queued_js', $js ); // WPCS: XSS ok.

		unset( $wpdl_queued_js );
	}
}

/**
 * Switch to site language.
 */
function wpdl_switch_to_site_locale() {
	if ( function_exists( 'switch_to_locale' ) ) {
		switch_to_locale( get_locale() );

		// Filter on plugin_locale so load_plugin_textdomain loads the correct locale.
		add_filter( 'plugin_locale', 'get_locale' );

		// Init locale.
		wpdl()->load_plugin_textdomain();
	}
}

/**
 * Switch language to original.
 */
function wpdl_restore_locale() {
	if ( function_exists( 'restore_previous_locale' ) ) {
		restore_previous_locale();

		// Remove filter.
		remove_filter( 'plugin_locale', 'get_locale' );

		// Init locale.
		wpdl()->load_plugin_textdomain();
	}
}