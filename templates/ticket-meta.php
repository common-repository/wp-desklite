<?php
/**
 * Ticket Meta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wpdl-side">

	<div class="wpdl-meta wpdl-center"><div class="wpdl-avatar-cust"><?php echo get_avatar( $customer->user_email, 76 ); ?></div></div>

	<div class="wpdl-meta"><span class="wpdl-name"><?php echo wpdl_get_name( $customer ); ?></span></div>

	<div class="wpdl-meta"><span class="wpdl-icon la-envelope"></span><a href="mailto:<?php echo $customer->user_email; ?>"><?php echo $customer->user_email; ?></a></div>

	<?php echo wpdl_show_wc_address( $customer->ID ); ?>

	<?php echo wpdl_wc_get_customer_total_spent( $customer->ID ); ?>

</div>