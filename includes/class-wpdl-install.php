<?php
/**
 * Installation functions and actions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Install class.
 */
class WPDL_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
	}

	/**
	 * Check version and run the updater is required.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'wpdl_version' ), wpdl()->version, '<' ) ) {
			self::install();
			do_action( 'wpdl_updated' );
		}
	}

	/**
	 * Install.
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		self::create_options();
		self::create_roles();
		self::create_pages();
		self::update_version();

		do_action( 'wpdl_flush_rewrite_rules' );
		do_action( 'wpdl_installed' );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	public static function create_options() {
		// Include settings so that we can run through defaults.
		include_once dirname( __FILE__ ) . '/admin/class-wpdl-admin-settings.php';

		$settings = WPDL_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}

		// Define other defaults if not in setting screens.
	}

	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Create plugin-specific roles
		_x( 'Operator', 'User role', 'wp-desklite' );

		wpdl_add_role(
			'operator',
			'Operator',
			$wp_roles->roles['subscriber']['capabilities']
		);

		// Add default capabilities to all roles.
		foreach( $wp_roles->roles as $role => $data ) {
			if ( in_array( $role, array( 'administrator' ) ) ) {
				foreach( wpdl_get_support_admin_caps() as $capability => $bool ) {
					$wp_roles->add_cap( $role, $capability );
				}
			}
			if ( in_array( $role, array( 'administrator', 'operator' ) ) ) {
				foreach( wpdl_get_support_caps() as $capability => $bool ) {
					$wp_roles->add_cap( $role, $capability );
				}
			}
		}

	}

	/**
	 * Create pages on install.
	 */
	public static function create_pages() {

		include_once dirname( __FILE__ ) . '/admin/wpdl-admin-functions.php';

		$pages[ 'tickets' ] = array(
			'name'    => _x( 'tickets', 'Page slug', 'wp-desklite' ),
			'title'   => _x( 'My Tickets', 'Page title', 'wp-desklite' ),
			'content' => '[wpdl_my_tickets]',
		);

		foreach ( (array) $pages as $key => $page ) {
			wpdl_create_page( esc_sql( $page['name'] ), 'wpdl_' . $key . '_page_id', $page['title'], $page['content'], '' );
		}
	}

	/**
	 * Update version to current.
	 */
	private static function update_version() {
		delete_option( 'wpdl_version' );
		add_option( 'wpdl_version', wpdl()->version );
	}

	/**
	 * Uninstall.
	 */
	public static function uninstall() {
		global $wpdb, $wp_version;

		// Check for needed files.
		if ( ! function_exists( 'wpdl_maybe_define_constant' ) ) {
			include_once dirname( __FILE__ ) . '/wpdl-core-functions.php';
		}

		// Only proceed if admin want to remove all data.
		if ( get_option( 'wpdl_remove_all_data' ) !== 'yes' ) {
			return;
		}

		// Delete options.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpdl\_%';" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'widget\_wpdl\_%';" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_wpdl\_%';" );

		// Delete comments.
		$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_type IN ( 'ticket_reply', 'ticket_note' );" );
		$wpdb->query( "DELETE meta FROM {$wpdb->commentmeta} meta LEFT JOIN {$wpdb->comments} comments ON comments.comment_ID = meta.comment_id WHERE comments.comment_ID IS NULL;" );

		// Delete posts.
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'wpdl_ticket';" );

		// Delete pages.
		wp_trash_post( get_option( 'wpdl_tickets_page_id' ) );

		// Delete terms if > WP 4.2 (term splitting was added in 4.2).
		if ( version_compare( $wp_version, '4.2', '>=' ) ) {
			// Delete term taxonomies.
			foreach ( array( 'wpdl_ticket_dep', 'wpdl_ticket_type' ) as $_taxonomy ) {
				$wpdb->delete(
					$wpdb->term_taxonomy,
					array(
						'taxonomy' => $_taxonomy,
					)
				);
			}

			// Delete orphan relationships.
			$wpdb->query( "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} posts ON posts.ID = tr.object_id WHERE posts.ID IS NULL;" );

			// Delete orphan terms.
			$wpdb->query( "DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.term_id IS NULL;" );

			// Delete orphan term meta.
			if ( ! empty( $wpdb->termmeta ) ) {
				$wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id WHERE tt.term_id IS NULL;" );
			}
		}

		// Roles + capabilities.
		self::remove_roles();

		// Clear any cached data that has been removed.
		wp_cache_flush();
	}

	/**
	 * Remove roles and capabilities.
	 */
	public static function remove_roles() {
		global $wpdb, $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Add default capabilities to all roles.
		foreach( $wp_roles->roles as $role => $data ) {
			if ( in_array( $role, array( 'administrator' ) ) ) {
				foreach( wpdl_get_support_admin_caps() as $capability => $bool ) {
					$wp_roles->remove_cap( $role, $capability );
				}
			}
			if ( in_array( $role, array( 'administrator', 'operator' ) ) ) {
				foreach( wpdl_get_support_caps() as $capability => $bool ) {
					$wp_roles->remove_cap( $role, $capability );
				}
			}
		}

		// Remove support operator.
		remove_role( 'operator' );
	}

}

WPDL_Install::init();