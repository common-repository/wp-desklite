<?php
/**
 * Formatting Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 */
function wpdl_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wpdl_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Sanitize a string destined to be a tooltip.
 */
function wpdl_sanitize_tooltip( $var ) {
	return htmlspecialchars(
		wp_kses(
			html_entity_decode( $var ), array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			)
		)
	);
}

/**
 * Detect if we should use a light or dark color on a background color.
 */
function wpdl_light_or_dark( $color, $dark = '#202020', $light = '#FFFFFF' ) {
	return wpdl_hex_is_light( $color ) ? $dark : $light;
}

/**
 * Determine whether a hex color is light.
 */
function wpdl_hex_is_light( $color ) {
	$hex = str_replace( '#', '', $color );

	$c_r = hexdec( substr( $hex, 0, 2 ) );
	$c_g = hexdec( substr( $hex, 2, 2 ) );
	$c_b = hexdec( substr( $hex, 4, 2 ) );

	$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

	return $brightness > 155;
}

/**
 * Make HEX color lighter.
 */
function wpdl_hex_lighter( $color, $factor = 30 ) {
	$base  = wpdl_rgb_from_hex( $color );
	$color = '#';

	foreach ( $base as $k => $v ) {
		$amount      = 255 - $v;
		$amount      = $amount / 100;
		$amount      = round( $amount * $factor );
		$new_decimal = $v + $amount;

		$new_hex_component = dechex( $new_decimal );
		if ( strlen( $new_hex_component ) < 2 ) {
			$new_hex_component = '0' . $new_hex_component;
		}
		$color .= $new_hex_component;
	}

	return $color;
}

/**
 * Make HEX color darker.
 */
function wpdl_hex_darker( $color, $factor = 30 ) {
	$base  = wpdl_rgb_from_hex( $color );
	$color = '#';

	foreach ( $base as $k => $v ) {
		$amount      = $v / 100;
		$amount      = round( $amount * $factor );
		$new_decimal = $v - $amount;

		$new_hex_component = dechex( $new_decimal );
		if ( strlen( $new_hex_component ) < 2 ) {
			$new_hex_component = '0' . $new_hex_component;
		}
		$color .= $new_hex_component;
	}

	return $color;
}

/**
 * Convert RGB to HEX.
 */
function wpdl_rgb_from_hex( $color ) {
	$color = str_replace( '#', '', $color );
	// Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF".
	$color = preg_replace( '~^(.)(.)(.)$~', '$1$1$2$2$3$3', $color );

	$rgb      = array();
	$rgb['R'] = hexdec( $color{0} . $color{1} );
	$rgb['G'] = hexdec( $color{2} . $color{3} );
	$rgb['B'] = hexdec( $color{4} . $color{5} );

	return $rgb;
}