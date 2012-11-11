<?php
/**
 * This is step 3 of 3 for the ad submission form
 * 
 * @package ClassiPress
 * @subpackage Membership
 * @author AppThemes
 *
 *
 */

global $wpdb, $order, $current_user, $cp_user_orders, $cp_user_recent_order;

//check to make sure the user has an order already setup, othrewise the page was refreshed or page hack was attempted
if(count($cp_user_orders) > 0) {
?>


   <div id="step3"></div>

   <h2 class="dotted">
   <?php if ($_POST['total_cost'] > 0) { echo __('Final Step','appthemes'); } else { echo __('Membership Updated','appthemes'); } ?>
   </h2>

   <img src="<?php bloginfo('template_url'); ?>/images/step3.gif" alt="" class="stepimg" />

    <div class="thankyou">

    <?php
    // call in the selected payment gateway as long as the price isn't zero
    if ($order['total_cost'] > 0) :
        include_once (TEMPLATEPATH . '/includes/gateways/gateway.php');
	
	//process the "free" orders on this page, the payment gateway orders will be processed on tpl-membership-purchase.php
	else : 
		$order_processed = appthemes_process_membership_order($current_user, $order);
    //send email to user
    if($order_processed)
      cp_owner_activated_membership_email($current_user, $order_processed);
	?>

		<h3><?php _e('Your order has been completed and your membership status should now be active.','appthemes') ?></h3>

		<p><?php _e('Visit your dashboard to review your membership status details.','appthemes') ?></p>
		
		<ul class="membership-pack">
			<li><strong><?php _e('Membership Pack','appthemes')?>:</strong> <?php echo stripslashes($order_processed['pack_name']); ?></li>
			<li><strong><?php _e('Membership Expires','appthemes')?>:</strong> <?php echo appthemes_display_date($order_processed['updated_expires_date']); ?></li>
		</ul>
        
		<div class="pad50"></div>

    </div> <!-- /thankyou -->

	<?php do_action('appthemes_after_membership_confirmation'); ?>

    <?php
		// remove the order option from the database because the free order was processed
		delete_option($cp_user_recent_order);
	
    endif;

		// send new membership notification email to admin
		//if (get_option('cp_new_membership_email') == 'yes' || $_POST['cp_payment_method'] == 'banktransfer')
		//    cp_new_membership_email($order['order_id']);
		
?>

<?php

} else {

?>

    <h2 class="dotted"><?php _e('An Error Has Occurred','appthemes') ?></h2>

    <div class="thankyou">
        <p><?php _e('Your session or order has expired or we cannot cannot find your order in our systems. Please start over to create a valid membership order.','appthemes') ?></p>
    </div>

<?php

}

?>

    <div class="pad100"></div>