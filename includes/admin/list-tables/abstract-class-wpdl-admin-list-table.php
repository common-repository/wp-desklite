<?php
/**
 * List tables.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Admin_List_Table class.
 */
abstract class WPDL_Admin_List_Table {

	/**
	 * Post type.
	 */
	protected $list_table_type = '';

	/**
	 * Object being shown on the row.
	 */
	protected $object = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( $this->list_table_type ) {
			add_action( 'manage_posts_extra_tablenav', array( $this, 'maybe_render_blank_state' ) );
			add_filter( 'view_mode_post_types', array( $this, 'disable_view_mode' ) );
			add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ), 10, 2 );
			add_filter( 'request', array( $this, 'request_query' ) );
			add_filter( 'post_row_actions', array( $this, 'row_actions' ), 100, 2 );
			add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );
			add_filter( 'list_table_primary_column', array( $this, 'list_table_primary_column' ), 10, 2 );
			add_filter( 'manage_edit-' . $this->list_table_type . '_sortable_columns', array( $this, 'define_sortable_columns' ) );
			add_filter( 'manage_' . $this->list_table_type . '_posts_columns', array( $this, 'define_columns' ) );
			add_filter( 'bulk_actions-edit-' . $this->list_table_type, array( $this, 'define_bulk_actions' ) );
			add_action( 'manage_' . $this->list_table_type . '_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
			add_filter( 'handle_bulk_actions-edit-' . $this->list_table_type, array( $this, 'handle_bulk_actions' ), 10, 3 );
			add_filter( 'views_edit-' . $this->list_table_type, array( $this, 'get_views' ), 10 );
		}
	}

	/**
	 * Show blank slate.
	 */
	public function maybe_render_blank_state( $which ) {}

	/**
	 * Render blank state. Extend to add content.
	 */
	protected function render_blank_state() {}

	/**
	 * Removes this type from list of post types that support "View Mode" switching.
	 * View mode is seen on posts where you can switch between list or excerpt. Our post types don't support
	 * it, so we want to hide the useless UI from the screen options tab.
	 */
	public function disable_view_mode( $post_types ) {
		unset( $post_types[ $this->list_table_type ] );
		return $post_types;
	}

	/**
	 * See if we should render search filters or not.
	 */
	public function restrict_manage_posts( $post_type, $which ) {
		global $typenow;

		if ( $this->list_table_type === $typenow ) {
			$this->render_filters();
		}
	}

	/**
	 * Handle any filters.
	 */
	public function request_query( $query_vars ) {
		global $typenow;

		if ( $this->list_table_type === $typenow ) {
			return $this->query_filters( $query_vars );
		}

		return $query_vars;
	}

	/**
	 * Render any custom filters and search inputs for the list table.
	 */
	protected function render_filters() {}

	/**
	 * Handle any custom filters.
	 */
	protected function query_filters( $query_vars ) {
		return $query_vars;
	}

	/**
	 * Set row actions.
	 */
	public function row_actions( $actions, $post ) {
		if ( $this->list_table_type === $post->post_type ) {
			return $this->get_row_actions( $actions, $post );
		}
		return $actions;
	}

	/**
	 * Get row actions to show in the list table.
	 */
	protected function get_row_actions( $actions, $post ) {
		return $actions;
	}

	/**
	 * Adjust which columns are displayed by default.
	 */
	public function default_hidden_columns( $hidden, $screen ) {
		if ( isset( $screen->id ) && 'edit-' . $this->list_table_type === $screen->id ) {
			$hidden = array_merge( $hidden, $this->define_hidden_columns() );
		}
		return $hidden;
	}

	/**
	 * Set list table primary column.
	 */
	public function list_table_primary_column( $default, $screen_id ) {
		if ( 'edit-' . $this->list_table_type === $screen_id && $this->get_primary_column() ) {
			return $this->get_primary_column();
		}
		return $default;
	}

	/**
	 * Define primary column.
	 */
	protected function get_primary_column() {
		return '';
	}

	/**
	 * Get views.
	 */
	public function get_views( $views ) {
		return $views;
	}

	/**
	 * Define hidden columns.
	 */
	protected function define_hidden_columns() {
		return array();
	}

	/**
	 * Define which columns are sortable.
	 */
	public function define_sortable_columns( $columns ) {
		return $columns;
	}

	/**
	 * Define which columns to show on this screen.
	 */
	public function define_columns( $columns ) {
		return $columns;
	}

	/**
	 * Define bulk actions.
	 */
	public function define_bulk_actions( $actions ) {
		return $actions;
	}

	/**
	 * Pre-fetch any data for the row each column has access to it.
	 */
	protected function prepare_row_data( $post_id ) {}

	/**
	 * Render individual columns.
	 */
	public function render_columns( $column, $post_id ) {
		$this->prepare_row_data( $post_id );

		if ( ! $this->object ) {
			return;
		}

		if ( is_callable( array( $this, 'render_' . $column . '_column' ) ) ) {
			$this->{"render_{$column}_column"}();
		}
	}

	/**
	 * Handle bulk actions.
	 */
	public function handle_bulk_actions( $redirect_to, $action, $ids ) {
		return esc_url_raw( $redirect_to );
	}

}