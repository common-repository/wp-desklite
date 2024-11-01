<?php
/**
 * List tables: Support Tickets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPDL_Admin_List_Table', false ) ) {
	include_once WPDL_ABSPATH . 'includes/admin/list-tables/abstract-class-wpdl-admin-list-table.php';
}

/**
 * WPDL_Admin_List_Table_Ticket class.
 */
class WPDL_Admin_List_Table_Ticket extends WPDL_Admin_List_Table {

	/**
	 * Post type.
	 */
	protected $list_table_type = 'wpdl_ticket';

	/**
	 * Total number of payments
	 */
	public $total_count = 0;

	/**
	 * Total number of new tickets.
	 */
	public $new_count = 0;

	/**
	 * Total number of pending tickets.
	 */
	public $pending_count = 0;

	/**
	 * Total number of resolved tickets.
	 */
	public $resolved_count = 0;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->get_ticket_counts();
		add_filter( 'disable_months_dropdown', '__return_true' );
		add_filter( 'get_search_query', array( $this, 'search_label' ) );
	}

	/**
	 * Retrieve the transaction counts
	 */
	public function get_ticket_counts() {
		global $wp_query;

		$ticket_count 			= wp_count_posts( 'wpdl_ticket' );

		$this->new_count   		= $ticket_count->new;
		$this->pending_count    = $ticket_count->pending;
		$this->resolved_count	= $ticket_count->resolved;
		$this->total_count		= $this->new_count + $this->pending_count + $this->resolved_count + $ticket_count->publish;
	}

	/**
	 * Define primary column.
	 */
	protected function get_primary_column() {
		return 'ticket';
	}

	/**
	 * Default hidden columns.
	 */
	public function default_hidden_columns( $hidden, $screen ) {
		if ( isset( $screen->id ) && 'edit-wpdl_ticket' === $screen->id ) {
			$hidden[] = 'id';
		}
		return $hidden;
	}

	/**
	 * Retrieve the view types
	 */
	public function get_views( $views ) {
		$current          	= isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count      	= '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$new_count   		= '&nbsp;<span class="count">(' . $this->new_count . ')</span>';
		$pending_count    	= '&nbsp;<span class="count">(' . $this->pending_count  . ')</span>';
		$resolved_count 	= '&nbsp;<span class="count">(' . $this->resolved_count  . ')</span>';

		$views = array(
			'all'        	=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array( 'status', 'paged' ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All','wp-desklite' ) . $total_count ),
			'new'    	 	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'new', 'paged' => FALSE ) ), $current === 'new' ? ' class="current"' : '', __('New','wp-desklite' ) . $new_count ),
			'pending'    	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'pending', 'paged' => FALSE ) ), $current === 'pending' ? ' class="current"' : '', __('Pending','wp-desklite' ) . $pending_count ),
			'resolved'  	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'resolved', 'paged' => FALSE ) ), $current === 'resolved' ? ' class="current"' : '', __('Resolved','wp-desklite' ) . $resolved_count ),
		);

		return apply_filters( 'wpdl_ticket_table_views', $views );
	}

	/**
	 * Show department/ticket type filters.
	 */
	public function restrict_manage_posts( $post_type, $which = 'top' ) {
		if ( 'wpdl_ticket' != $post_type ) {
			return;
		}
		$taxonomies = get_object_taxonomies ( $post_type, 'object' );
		foreach ( $taxonomies as $tax_obj ) {
			if ( $tax_obj->_builtin ) {
				continue;
			}

			$args = array (
				'show_option_all' 	=> $tax_obj->labels->all_items,
				'taxonomy'    		=> $tax_obj->name,
				'name'        		=> $tax_obj->name,
				'value_field' 		=> 'slug',
				'orderby'     		=> 'name',
				'selected'    		=> isset ($_REQUEST[$tax_obj->name]) ? $_REQUEST[$tax_obj->name] : '0',
				'hierarchical'    	=> $tax_obj->hierarchical,
				'show_count'      	=> false,
				'hide_empty'      	=> false,
				'fields'			=> 'all',
			);

			wp_dropdown_categories ($args) ;
        }
	}

	/**
	 * Get row actions to show in the list table.
	 */
	protected function get_row_actions( $actions, $post ) {

		$row_actions  = array();

		$row_actions['id']   = sprintf( esc_html__( 'ID: %s', 'wp-desklite' ), $post->ID );
		$row_actions['edit'] = '<a href="' . get_edit_post_link( $post ) . '">' . __( 'View', 'wp-desklite' ) . '</a>';

		if ( strtolower( $post->post_status ) == 'resolved' ) {
			$row_actions['deactivate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'wpdl-action' => 'unresolve_ticket', 'ticket' => $post->ID ) ), 'wpdl_ticket_nonce' ) ) . '">' . __( 'Unresolve', 'wp-desklite' ) . '</a>';
		} elseif ( strtolower( $post->post_status ) == 'new' || strtolower( $post->post_status ) == 'pending' ) {
			$row_actions['activate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'wpdl-action' => 'resolve_ticket', 'ticket' => $post->ID ) ), 'wpdl_ticket_nonce' ) ) . '">' . __( 'Resolve', 'wp-desklite' ) . '</a>';
		}

		// If user can delete a ticket.
		if ( current_user_can( 'delete_wpdl_ticket', $post->ID ) ) {
			$row_actions['delete'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'wpdl-action' => 'delete_ticket', 'ticket' => $post->ID ) ), 'wpdl_ticket_nonce' ) ) . '">' . __( 'Delete', 'wp-desklite' ) . '</a>';
		}

		$row_actions = apply_filters( 'wpdl_ticket_row_actions', $row_actions, $post );

		return $row_actions;
	}

	/**
	 * Define which columns are sortable.
	 */
	public function define_sortable_columns( $columns ) {
		$custom = array(
			'id'			=> 'id',
			'last_modified'	=> 'last_modified',
			'priority'		=> 'priority',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Define which columns to show on this screen.
	 */
	public function define_columns( $columns ) {
		$show_columns 					= array();
		$show_columns['cb']				= $columns['cb'];
		$show_columns['id'] 			= __( 'ID', 'wp-desklite' );
		$show_columns['ticket']			= __( 'Ticket', 'wp-desklite' );
		$show_columns['customer']		= __( 'Customer', 'wp-desklite' );
		$show_columns['assigned_to']	= __( 'Assigned to', 'wp-desklite' );
		$show_columns['status']			= __( 'Status', 'wp-desklite' );
		$show_columns['type']			= __( 'Type', 'wp-desklite' );
		$show_columns['priority']		= __( 'Priority', 'wp-desklite' );
		$show_columns['department']		= __( 'Department', 'wp-desklite' );
		$show_columns['last_modified']	= __( 'Last modified', 'wp-desklite' );

		return $show_columns;
	}

	/**
	 * Define bulk actions.
	 */
	public function define_bulk_actions( $actions ) {
		$actions = array(
			'resolve'		=> __( 'Mark as resolved', 'wp-desklite' ),
			'set_new'		=> __( 'Set as new', 'wp-desklite' ),
			'set_pending'	=> __( 'Set as pending', 'wp-desklite' ),
			'delete'     	=> __( 'Delete', 'wp-desklite' ),
		);

		return $actions;
	}

	/**
	 * Pre-fetch any data for the row each column has access to it. global is there for bw compat.
	 */
	protected function prepare_row_data( $post_id ) {
		global $the_ticket;

		if ( empty( $this->object ) || $this->object->id !== $post_id ) {
			$this->object = new WPDL_Ticket( $post_id );
			$the_ticket = $this->object;
		}
	}

	/**
	 * Render column: id.
	 */
	protected function render_id_column() {

		echo '<a href="' . get_edit_post_link( $this->object->id ) . '">' . absint( $this->object->id ) . '</a>';
	}

	/**
	 * Render column: ticket.
	 */
	protected function render_ticket_column() {
		global $post;

		$edit_link = get_edit_post_link( $this->object->id );
		$title     = _draft_or_post_title();

		if ( $this->object->is_resolved ) {
			echo '<span class="wpdl-resolved wpdl-tip" title="' . esc_attr__( 'Resolved', 'wp-desklite' ) . '"></span>';
		}
		
		if ( get_post_meta( $this->object->id, '_is_waiting', true ) == 1 ) {
			echo '<a href="' . esc_url( $edit_link ) . '"><strong>' . esc_html( $title ) . '</strong></a>';
		} else {
			echo '<a href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a>';
		}
	}

	/**
	 * Render column: customer.
	 */
	protected function render_customer_column() {
		$user_id = $this->object->customer;
		if ( empty( $user_id ) ) {
			echo __( 'No customer', 'wp-desklite' );
		} else {
			$user = get_userdata( $user_id );
			echo $user ? $user->user_email : __( 'No customer', 'wp-desklite' );
		}
	}

	/**
	 * Render column: assigned_to.
	 */
	protected function render_assigned_to_column() {
		$user_id = $this->object->assigned_to;
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			echo '<a href="' . add_query_arg( array( 'assigned_to' => $user_id, 'paged' => false ) ) . '" title="' . esc_html( wpdl_get_name( $user ) ) . '" class="wpdl-tip">' . get_avatar( $user->user_email, 32 ) . '</a>';
		} else {
			echo '&mdash;';
		}
	}

	/**
	 * Render column: status.
	 */
	protected function render_status_column() {
		echo wpdl_get_status( $this->object->post_status );
	}

	/**
	 * Render column: type.
	 */
	protected function render_type_column() {
		$terms = $this->object->get_types();
		if ( $terms ) {
			foreach( $terms as $term ) {
				$color = get_term_meta( $term->term_id, '_wpdl_color', true );
				if ( $color && $color != 'eeeeee' ) {
				?>
					<a href="<?php echo add_query_arg( array( 'wpdl_ticket_type' => $term->slug, 'paged' => false ) ); ?>" class="wpdl-tag" style="background-color: #<?php echo esc_attr( $color ); ?>; color: #fff;"><?php echo esc_html( $term->name ); ?></a>
				<?php } else { ?>
					<a href="<?php echo add_query_arg( array( 'wpdl_ticket_type' => $term->slug, 'paged' => false ) ); ?>" class="wpdl-tag"><?php echo esc_html( $term->name ); ?></a>
				<?php
				}
			}
		} else {
			echo __( 'No types', 'wp-desklite' );
		}
	}

	/**
	 * Render column: priority.
	 */
	protected function render_priority_column() {

		$priority = $this->object->priority ? $this->object->priority : 3;

		echo '<a href="' . add_query_arg( array( 'priority' => $priority, 'paged' => false ) ) . '" class="wpdl-line priority-' . esc_attr( str_replace( '_', '-', $priority ) ) . '">' . wpdl_get_priority_label( $priority ) . '</a>';
	}

	/**
	 * Render column: department.
	 */
	protected function render_department_column() {
		$terms = $this->object->get_departments();
		if ( $terms ) {
			foreach( $terms as $term ) {
				$color = get_term_meta( $term->term_id, '_wpdl_color', true );
				if ( $color && $color != 'eeeeee' ) {
				?>
					<a href="<?php echo add_query_arg( array( 'wpdl_ticket_dep' => $term->slug, 'paged' => false ) ); ?>" class="wpdl-tag" style="background-color: #<?php echo esc_attr( $color ); ?>; color: #fff;"><?php echo esc_html( $term->name ); ?></a>
				<?php } else { ?>
					<a href="<?php echo add_query_arg( array( 'wpdl_ticket_dep' => $term->slug, 'paged' => false ) ); ?>" class="wpdl-tag"><?php echo esc_html( $term->name ); ?></a>
				<?php
				}
			}
		} else {
			echo __( 'No departments', 'wp-desklite' );
		}
	}

	/**
	 * Render column: last_modified.
	 */
	protected function render_last_modified_column() {
		$date = sprintf( __( '%1$s %2$s', 'wp-desklite' ), get_option( 'date_format' ), get_option( 'time_format' ) );
		echo date_i18n( $date, get_the_modified_time( 'U' ) );
	}

	/**
	 * Change the label when searching
	 */
	public function search_label( $query ) {
		global $pagenow, $typenow;

		if ( 'edit.php' !== $pagenow || 'wpdl_ticket' !== $typenow || ! get_query_var( 'ticket_search' ) || ! isset( $_GET['s'] ) ) {
			return $query;
		}

		return wpdl_clean( wp_unslash( $_GET['s'] ) );
	}

	/**
	 * Handle any query filters.
	 */
	protected function query_filters( $query_vars ) {

		// Status.
		if ( isset( $_GET['status'] ) ) {
			$query_vars[ 'post_status' ] = wpdl_clean( $_GET['status'] );
		}

		// Priority.
		if ( isset( $_GET['priority'] ) ) {
			$query_vars[ 'meta_key' ] 	= 'priority';
			$query_vars[ 'meta_value' ] = absint( $_GET['priority'] );
		}

		// Orderby.
		if ( isset( $query_vars['orderby'] ) ) {
			$orderby = wpdl_clean( $query_vars['orderby'] );
			if ( empty( $orderby ) ) {
				$query_vars[ 'orderby' ]  = 'modified';
				$query_vars[ 'order' ]    = 'desc';
			} else {
				// Order by last modified.
				if ( $orderby == 'last_modified' ) {
					$query_vars[ 'orderby' ] = 'modified';
				}
				// Order by priority.
				if ( $orderby == 'priority' ) {
					$query_vars[ 'meta_key' ] = 'priority';
					$query_vars[ 'orderby' ]  = 'meta_value';
				}
			}
		}

		// Search.
		if ( ! empty( $query_vars['s'] ) ) {

		}

		return $query_vars;
	}

	/**
	 * Handle any bulk actions.
	 */
	public function handle_bulk_actions( $redirect, $action, $ids ) {
		if ( $action == 'resolve' ) {
			foreach( $ids as $id ) {
				wpdl_ticket_set_as_resolved( $id );
			}
		}

		if ( $action == 'set_new' ) {
			foreach( $ids as $id ) {
				wpdl_ticket_set_as_new( $id );
			}
		}

		if ( $action == 'set_pending' ) {
			foreach( $ids as $id ) {
				wpdl_ticket_set_as_pending( $id );
			}
		}

		if ( in_array( $action, array( 'resolve', 'set_new', 'set_pending' ) ) ) {
			$redirect = add_query_arg( 'updated', count( $ids ), $redirect );
			return $redirect;
		}
	}

}