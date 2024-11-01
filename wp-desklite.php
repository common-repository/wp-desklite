<?php
/**
 * Plugin Name: WP DeskLite
 * Plugin URI: https://codeflock.net
 * Description: A Simple Helpdesk and Support Plugin for WordPress.
 * Author: CodeFlock
 * Author URI: https://codeflock.net/
 * Version: 1.0.0
 * Text Domain: wp-desklite
 * Domain Path: /i18n/languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define WPDL_PLUGIN_FILE.
if ( ! defined( 'WPDL_PLUGIN_FILE' ) ) {
	define( 'WPDL_PLUGIN_FILE', __FILE__ );
}

// Define WPDL_PLUGIN_BASENAME.
if ( ! defined( 'WPDL_PLUGIN_BASENAME' ) ) {
	define( 'WPDL_PLUGIN_BASENAME', plugin_basename( WPDL_PLUGIN_FILE ) );
}

// Include the main class.
if ( ! class_exists( 'WPDL' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wp-desklite.php';
}

/**
 * Main instance.
 */
function wpdl() {
	return WPDL::instance();
}

// Global for backwards compatibility.
$GLOBALS[ 'wpdl' ] = wpdl();