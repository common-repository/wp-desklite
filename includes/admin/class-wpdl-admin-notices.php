<?php
/**
 * Display notices in admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPDL_Admin_Notices class.
 */
class WPDL_Admin_Notices {


	/**
	 * Constructor.
	 */
	public static function init() {
		add_action( 'load-index.php', array( __CLASS__, 'run_in_dashboard' ) );
	}

	/**
	 * Run in dashboard only.
	 */
	public static function run_in_dashboard() {
		add_action( 'admin_notices', array( __CLASS__, 'add_tickets_notice' ) );
	}

	/**
	 * Notice to display tickets count in admin.
	 */
	public static function add_tickets_notice() {
		$count = wpdl_get_pending_count();
		if ( $count < 1 ) {
			return;
		}
		?>
		<div class="wpdl-notice updated">
			<p><strong>
				<?php if ( $count == 1 ) : ?>
				<?php echo sprintf( __( '<a href="%s">%s support ticket</a> is awaiting a reply.', 'wp-desklite' ), admin_url( 'edit.php?post_type=wpdl_ticket' ), absint( $count ) ); ?>
				<?php else : ?>
				<?php echo sprintf( __( '<a href="%s">%s support tickets</a> are awaiting a reply.', 'wp-desklite' ), admin_url( 'edit.php?post_type=wpdl_ticket' ), absint( $count ) ); ?>
				<?php endif; ?>
			</strong></p>
		</div>
		<?php
	}

}

WPDL_Admin_Notices::init();