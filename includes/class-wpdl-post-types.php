<?php
/**
 * Registers post types and taxonomies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Post_Types class.
 */
class WPDL_Post_Types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 0 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 1 );
		add_action( 'init', array( __CLASS__, 'register_post_status' ), 2 );
		add_action( 'wpdl_after_register_post_type', array( __CLASS__, 'maybe_flush_rewrite_rules' ) );
		add_action( 'wpdl_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
	}


	/**
	 * Register core taxonomies.
	 */
	public static function register_taxonomies() {

		if ( ! is_blog_installed() ) {
			return;
		}

		if ( taxonomy_exists( 'wpdl_ticket_dep' ) ) {
			return;
		}

		register_taxonomy(
			'wpdl_ticket_dep',
			apply_filters( 'wpdl_taxonomy_objects_wpdl_ticket_dep', array( 'wpdl_ticket' ) ),
			apply_filters(
				'wpdl_taxonomy_args_wpdl_ticket_dep',
				array(
					'hierarchical'          => true,
					'update_count_callback' => '_wpdl_term_recount',
					'label'                 => __( 'Departments', 'wp-desklite' ),
					'labels'                => array(
						'name'                       => __( 'Departments', 'wp-desklite' ),
						'singular_name'              => __( 'Department', 'wp-desklite' ),
						'menu_name'                  => _x( 'Departments', 'Admin menu name', 'wp-desklite' ),
						'search_items'               => __( 'Search departments', 'wp-desklite' ),
						'all_items'                  => __( 'All departments', 'wp-desklite' ),
						'edit_item'                  => __( 'Edit department', 'wp-desklite' ),
						'update_item'                => __( 'Update department', 'wp-desklite' ),
						'add_new_item'               => __( 'Add new department', 'wp-desklite' ),
						'new_item_name'              => __( 'New department name', 'wp-desklite' ),
						'popular_items'              => __( 'Popular departments', 'wp-desklite' ),
						'separate_items_with_commas' => __( 'Separate departments with commas', 'wp-desklite' ),
						'add_or_remove_items'        => __( 'Add or remove departments', 'wp-desklite' ),
						'choose_from_most_used'      => __( 'Choose from the most used departments', 'wp-desklite' ),
						'not_found'                  => __( 'No departments found', 'wp-desklite' ),
					),
					'show_ui'               => true,
					'query_var'             => true,
					'capabilities'          => array(
						'manage_terms' => 'manage_wpdl_ticket_dep_terms',
						'edit_terms'   => 'edit_wpdl_ticket_dep_terms',
						'delete_terms' => 'delete_wpdl_ticket_dep_terms',
						'assign_terms' => 'assign_wpdl_ticket_dep_terms',
					),
					'rewrite'               => false,
					'show_admin_column' 	=> false,
				)
			)
		);
		register_taxonomy_for_object_type( 'wpdl_ticket_dep', 'wpdl_ticket' );

		register_taxonomy(
			'wpdl_ticket_type',
			apply_filters( 'wpdl_taxonomy_objects_wpdl_ticket_type', array( 'wpdl_ticket' ) ),
			apply_filters(
				'wpdl_taxonomy_args_wpdl_ticket_type',
				array(
					'hierarchical'          => true,
					'update_count_callback' => '_wpdl_term_recount',
					'label'                 => __( 'Ticket Type', 'wp-desklite' ),
					'labels'                => array(
						'name'                       => __( 'Ticket Types', 'wp-desklite' ),
						'singular_name'              => __( 'Ticket type', 'wp-desklite' ),
						'menu_name'                  => _x( 'Ticket Types', 'Admin menu name', 'wp-desklite' ),
						'search_items'               => __( 'Search ticket types', 'wp-desklite' ),
						'all_items'                  => __( 'All ticket types', 'wp-desklite' ),
						'edit_item'                  => __( 'Edit ticket type', 'wp-desklite' ),
						'update_item'                => __( 'Update ticket type', 'wp-desklite' ),
						'add_new_item'               => __( 'Add new ticket type', 'wp-desklite' ),
						'new_item_name'              => __( 'New ticket type name', 'wp-desklite' ),
						'popular_items'              => __( 'Popular ticket types', 'wp-desklite' ),
						'separate_items_with_commas' => __( 'Separate ticket types with commas', 'wp-desklite' ),
						'add_or_remove_items'        => __( 'Add or remove ticket types', 'wp-desklite' ),
						'choose_from_most_used'      => __( 'Choose from the most used ticket types', 'wp-desklite' ),
						'not_found'                  => __( 'No ticket types found', 'wp-desklite' ),
					),
					'show_ui'               => true,
					'query_var'             => true,
					'capabilities'          => array(
						'manage_terms' => 'manage_wpdl_ticket_type_terms',
						'edit_terms'   => 'edit_wpdl_ticket_type_terms',
						'delete_terms' => 'delete_wpdl_ticket_type_terms',
						'assign_terms' => 'assign_wpdl_ticket_type_terms',
					),
					'rewrite'               => false,
					'show_admin_column' 	=> false,
				)
			)
		);
		register_taxonomy_for_object_type( 'wpdl_ticket_type', 'wpdl_ticket' );

	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'wpdl_ticket' ) ) {
			return;
		}

		do_action( 'wpdl_register_post_types' );

		register_post_type(
			'wpdl_ticket',
			apply_filters(
				'wpdl_register_post_type_list',
				array(
					'labels'             => array(
						'name'                  => __( 'Support Tickets', 'wp-desklite' ),
						'singular_name'         => __( 'Support Ticket', 'wp-desklite' ),
						'menu_name'             => esc_html_x( 'Tickets', 'Admin menu name', 'wp-desklite' ),
						'add_new'               => __( 'Add ticket', 'wp-desklite' ),
						'add_new_item'          => __( 'Add new', 'wp-desklite' ),
						'edit'                  => __( 'Edit', 'wp-desklite' ),
						'edit_item'             => __( 'Edit ticket', 'wp-desklite' ),
						'new_item'              => __( 'New ticket', 'wp-desklite' ),
						'view_item'             => __( 'View ticket', 'wp-desklite' ),
						'search_items'          => __( 'Search tickets', 'wp-desklite' ),
						'not_found'             => __( 'No support tickets found', 'wp-desklite' ),
						'not_found_in_trash'    => __( 'No support tickets found in trash', 'wp-desklite' ),
						'parent'                => __( 'Parent ticket', 'wp-desklite' ),
						'filter_items_list'     => __( 'Filter support tickets', 'wp-desklite' ),
						'items_list_navigation' => __( 'Support tickets navigation', 'wp-desklite' ),
						'items_list'            => __( 'Support tickets list', 'wp-desklite' ),
					),
					'description'         => __( 'This is where you can manage support tickets.', 'wp-desklite' ),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'wpdl_ticket',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_in_menu'        => true,
					'menu_icon'			  => 'dashicons-format-chat',
					'menu_position'	      => 58,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title' ),
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => true,
				)
			)
		);

		do_action( 'wpdl_after_register_post_type' );
	}

	/**
	 * Register post status for tickets.
	 */
	public static function register_post_status() {
		register_post_status( 'new', array(
			'label'                     => _x( 'New', 'New ticket status', 'wp-desklite' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'New <span class="count">(%s)</span>', 'New <span class="count">(%s)</span>', 'wp-desklite' )
		)  );
		register_post_status( 'pending', array(
			'label'                     => _x( 'Pending', 'Pending ticket status', 'wp-desklite' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'wp-desklite' )
		)  );
		register_post_status( 'resolved', array(
			'label'                     => _x( 'Resolved', 'Resolved ticket status', 'wp-desklite' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Resolved <span class="count">(%s)</span>', 'Resolved <span class="count">(%s)</span>', 'wp-desklite' )
		)  );
	}

	/**
	 * Flush rules if the event is queued.
	 */
	public static function maybe_flush_rewrite_rules() {
		if ( 'yes' === get_option( 'wpdl_queue_flush_rewrite_rules' ) ) {
			update_option( 'wpdl_queue_flush_rewrite_rules', 'no' );
			self::flush_rewrite_rules();
		}
	}

	/**
	 * Flush rewrite rules.
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

}

WPDL_Post_Types::init();