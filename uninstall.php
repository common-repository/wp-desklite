<?php
/**
 * Plugin Uninstall
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

define( 'WPDL_ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WPDL_PLUGIN_FILE', __FILE__ );
define( 'WPDL_PLUGIN_BASENAME', plugin_basename( WPDL_PLUGIN_FILE ) );

// Load the install class into memory to uninstall.
include_once dirname( __FILE__ ) . '/includes/class-wpdl-install.php';
WPDL_Install::uninstall();