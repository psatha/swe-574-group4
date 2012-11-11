<?php
/**
 * PayPal payment gateway
 *
 * @package ClassiPress
 * @author AppThemes
 *
 * @param array $order_vals contains all the order values
 *
 */




// payment processing used on the new ad confirmation page
function paypal_gateway_process( $order_vals ) {
    global $gateway_name, $app_abbr, $post_url;

    // if this gateway wasn't selected then exit
    if ( $order_vals['cp_payment_method'] != 'paypal' )
        return;

    // is this a test transaction?
    if ( get_option( $app_abbr.'_paypal_sandbox' ) == true )
        $post_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    else
        $post_url = 'https://www.paypal.com/cgi-bin/webscr';

?>

    <form name="paymentform" method="post" action="<?php echo esc_url( $post_url ) ?>">

				<input type="hidden" name="cmd" value="_xclick" />
				<input type="hidden" name="charset" value="utf-8" />
				<input type="hidden" name="business" value="<?php echo esc_attr( get_option( 'cp_paypal_email' ) ); ?>" />
				<input type="hidden" name="item_name" value="<?php echo esc_attr( $order_vals['item_name'] ); ?>" />
				<input type="hidden" name="item_number" value="<?php echo esc_attr( $order_vals['item_number'] ); ?>" />
				<input type="hidden" name="amount" value="<?php echo esc_attr( $order_vals['item_amount'] ); ?>" />
				<input type="hidden" name="custom" value="<?php echo esc_attr( $order_vals['oid'] ); ?>" />
				<input type="hidden" name="cancel_return" value="<?php echo esc_url( get_option('home') ); ?>" />
				<input type="hidden" name="return" value="<?php echo esc_attr( $order_vals['return_url'] ); ?>" />
				<input type="hidden" name="cbt" value="<?php echo esc_attr( $order_vals['return_text'] ); ?>" />
				<input type="hidden" name="currency_code" value="<?php echo esc_attr( get_option('cp_curr_pay_type') ); ?>" />
				<input type="hidden" name="no_shipping" value="1" />
				<input type="hidden" name="no_note" value="1" />
				<input type="hidden" name="rm" value="2" />

        <?php if ( get_option('cp_enable_paypal_ipn') == 'yes' ) { ?>
            <input type="hidden" name="notify_url" value="<?php echo esc_attr( $order_vals['notify_url'] ); ?>" />
            <?php if ( get_option('cp_paypal_sandbox') == true ) : ?>
                <input type="hidden" name="test_ipn" value="1" />
            <?php endif; ?>
        <?php } ?>

        <?php if ( get_option('cp_paypal_logo_url') ) { ?>
            <input type="hidden" name="cpp_header_image" value="<?php echo get_option('cp_paypal_logo_url'); ?>" />
        <?php } ?>

        <center><input type="submit" class="btn_orange" value="<?php _e('Continue', 'appthemes');?> &rsaquo;&rsaquo;" /></center>

        <script type="text/javascript"> setTimeout("document.paymentform.submit();", 500); </script>

    </form>

<?php
}
add_action( 'cp_action_gateway', 'paypal_gateway_process', 10, 1 );




// payment processing for ad dashboard so ad owners can pay for unpaid ads
function cp_dashboard_paypal_button($the_id) {
    global $wpdb, $current_user;
    $current_user = wp_get_current_user();

  	$pack = get_pack( $the_id );

    // figure out the number of days this ad was listed for
    if ( get_post_meta($the_id, 'cp_sys_ad_duration', true) )
        $prun_period = get_post_meta($the_id, 'cp_sys_ad_duration', true);
  	else
  	    $prun_period = get_option('cp_prun_period');

	//setup variables depending on the purchase type
	if ( isset( $pack->pack_name ) && stristr( $pack->pack_status, 'membership' ) ) {
    //get any existing orders
    $cp_user_orders = get_user_orders($current_user->ID);
    if(isset($cp_user_orders) && $cp_user_orders) {
      $cp_user_recent_order = $cp_user_orders[0];
    } else {
      $oid = uniqid(rand(10,1000), false);
      $order = array();
      $order['user_id'] = $current_user->ID;
      $order['order_id'] = $oid;
      $order['option_order_id'] = 'cp_order_'.$current_user->ID.'_'.$oid;
      $order['pack_type'] = 'membership';
      $order['total_cost'] = $pack->pack_membership_price;
      $order = array_merge($order, (array)$pack);

      if(add_option($order['option_order_id'], $order)) {
				$cp_user_orders = get_user_orders($current_user->ID);
				if(isset($cp_user_orders) && $cp_user_orders) 
          $cp_user_recent_order = $cp_user_orders[0];
			}
    }

		$item_name = sprintf( __('Membership on %s for %s days', 'appthemes'), get_bloginfo('name'),$pack->pack_duration);
		$item_number = stripslashes($pack->pack_name);
		$custom = get_order_id($cp_user_recent_order);
		$amount = $pack->pack_membership_price;
		$notify_url = get_bloginfo('url').'/index.php?invoice='.$custom;
		$return = CP_MEMBERSHIP_PURCHASE_CONFIRM_URL.'?oid='.$custom;
		$cbt = __('Click here to complete your purchase on','appthemes').' '.get_bloginfo('name');
	} else { //by default we assume its an ad posting
		$item_name = sprintf( __('Classified ad listing on %s for %s days', 'appthemes'), get_bloginfo('name'), $prun_period);
		$item_number = get_post_meta($the_id, 'cp_sys_ad_conf_id', true);
		$custom = get_post_meta( $the_id, 'cp_sys_ad_conf_id', true );
		$amount =  get_post_meta($the_id, 'cp_sys_total_ad_cost', true);
		$notify_url = get_bloginfo('url').'/index.php?invoice='.get_post_meta( $the_id, 'cp_sys_ad_conf_id', true ).'&amp;aid='.$the_id;
		$return = CP_ADD_NEW_CONFIRM_URL.'?pid='.get_post_meta( $the_id, 'cp_sys_ad_conf_id', true ).'&amp;aid='.$the_id;
		$cbt = __('Click here to publish your ad on','appthemes').' '.get_bloginfo('name');
	}

?>

   <form name="paymentform" action="<?php if (get_option('cp_paypal_sandbox') == 'true') echo 'https://www.sandbox.paypal.com/cgi-bin/webscr'; else echo 'https://www.paypal.com/cgi-bin/webscr'; ?>" method="post">

				<input type="hidden" name="cmd" value="_xclick" />
				<input type="hidden" name="charset" value="utf-8" />
				<input type="hidden" name="business" value="<?php echo get_option('cp_paypal_email'); ?>" />
				<input type="hidden" name="item_name" value="<?php echo esc_attr( $item_name ); ?>" />
				<input type="hidden" name="item_number" value="<?php echo esc_attr( $item_number ); ?>" />
				<input type="hidden" name="amount" value="<?php echo esc_attr( $amount ); ?>" />
				<input type="hidden" name="no_shipping" value="1" />
				<input type="hidden" name="no_note" value="1" />
				<input type="hidden" name="custom" value="<?php echo esc_attr( $custom ); ?>" />
				<input type="hidden" name="cancel_return" value="<?php echo home_url(); ?>" />
				<input type="hidden" name="return" value="<?php echo esc_attr( $return ); ?>" />
				<input type="hidden" name="rm" value="2" />
				<input type="hidden" name="cbt" value="<?php echo esc_attr( $cbt ); ?>" />
				<input type="hidden" name="currency_code" value="<?php echo esc_attr( get_option('cp_curr_pay_type') ); ?>" />

       <?php if ( get_option('cp_enable_paypal_ipn') == 'yes' ) { ?>
           <input type="hidden" name="notify_url" value="<?php echo esc_attr( $notify_url ); ?>" />
           <?php if ( get_option('cp_paypal_sandbox') == 'true' ) : ?>
               <input type="hidden" name="test_ipn" value="1" />
           <?php endif; ?>
  	   <?php } ?>

       <?php if ( get_option('cp_paypal_logo_url') ) { ?>
           <input type="hidden" name="cpp_header_image" value="<?php echo esc_attr( get_option('cp_paypal_logo_url') ); ?>" />
       <?php } ?>

           <input type="image" src="<?php bloginfo('template_directory'); ?>/images/paypal.png" name="submit" />

   </form>

<?php
}
?>
