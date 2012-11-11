<?php

/**
 * Bank transfer payment gateway script
 *
 * @package ClassiPress
 * @author AppThemes
 * @version 3.0.4
 *
 * @param int $post_id
 * @param text $type 
 *
 */

// payment processing script that is used on the new ad confirmation page
// and also the ad dashboard so ad owners can pay for unpaid ads
function banktransfer_gateway_process( $order_vals ) {
    global $gateway_name, $app_abbr, $ref_val;
    
    // if gateway wasn't selected then exit
    if ( $order_vals['cp_payment_method'] != 'banktransfer' ) 
        return;
        
    // ad listing or membership    
    if ( !empty( $order_vals['post_id'] ) ) {
        $ref_val = $order_vals['post_id'];
        $info_message = __('Please include the following details when sending the bank transfer. Once your transfer has been verified, we will then approve your ad listing.', 'appthemes');
        cp_bank_owner_new_ad_email($ref_val);
    } else {
        $ref_val = $order_vals['oid'];
        $info_message = __('Please include the following details when sending the bank transfer. Once your transfer has been verified, we will then activate your membership.', 'appthemes');
        cp_new_membership_email($ref_val);
        cp_bank_owner_new_membership_email($ref_val);
    }

    // regardless of what happens, log the transaction
    if (file_exists(TEMPLATEPATH . '/includes/gateways/process.php')){
      include_once (TEMPLATEPATH . '/includes/gateways/process.php');

      $trdata = cp_prepare_transaction_entry($order_vals);
      if($trdata)
        $tr_id = cp_add_transaction_entry($trdata);
    }
?>

<h2><?php _e('Your Unique Ad Details', 'appthemes') ?></h2>

<p><?php echo $info_message; ?></p>

<p>
    <strong><?php _e('Transaction ID:', 'appthemes') ?></strong> <?php echo esc_html( $order_vals['item_number'] ); ?><br />
    <strong><?php _e('Reference #:', 'appthemes') ?></strong> <?php echo esc_attr( $ref_val ); ?><br />
    <strong><?php _e('Total Amount:', 'appthemes') ?></strong> <?php echo esc_html( $order_vals['item_amount'] ); ?> (<?php echo get_option('cp_curr_pay_type'); ?>)<br />

</p>

<br /><br />

<h2><?php _e('Bank Transfer Instructions', 'appthemes') ?></h2>

<p><?php echo stripslashes( appthemes_nl2br( get_option('cp_bank_instructions') ) ); ?></p>

<p><?php _e('For questions or problems, please contact us directly at', 'appthemes') ?> <?php echo get_option('admin_email'); ?></p>


<?php
}
add_action( 'cp_action_gateway', 'banktransfer_gateway_process', 10, 1 );
?>


