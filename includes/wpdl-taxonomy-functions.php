<?php
/**
 * Taxonomy Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all available departments.
 */
function wpdl_get_departments() {

	$terms = get_terms( array(
		'taxonomy' => 'wpdl_ticket_dep',
		'hide_empty' => false,
	) );

	return $terms;
}

/**
 * Get all available ticket types.
 */
function wpdl_get_ticket_types() {

	$terms = get_terms( array(
		'taxonomy' => 'wpdl_ticket_type',
		'hide_empty' => false,
	) );

	return $terms;
}