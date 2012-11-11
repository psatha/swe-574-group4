<?php
/**
 * This is step 3 of 3 for the ad submission form
 * 
 * @package ClassiPress
 * @subpackage New Ad
 * @author AppThemes
 *
 *
 */


global $current_user, $wpdb;

// now get all the ad values which we stored in an associative array in the db
// first we do a check to make sure this db session still exists and then we'll
// use this option array to create the new ad below
$advals = get_option( 'cp_'.$_POST['oid'] );

if ( isset( $_POST['cp_payment_method'] ) ) 
    $advals['cp_payment_method'] = $_POST['cp_payment_method'];
else 
    $advals['cp_payment_method'] = '';

//$advals['kordinat'] = $_POST['kordinat'];
//$_POST['kordinat'] = $advals['kordinat'];
// check and make sure the form was submitted from step 2 and the hidden oid matches the oid in the db
// we don't want to create duplicate ad submissions if someone reloads their browser
if ( isset( $_POST['step2'] ) && isset( $advals['oid'] ) && ( strcasecmp( $_POST['oid'], $advals['oid'] ) == 0 ) ) {
?>


   <div id="step3"></div>

   <h2 class="dotted">
       <?php 
        if ( get_option('cp_charge_ads') == 'yes' ) 
            _e('Final Step', 'appthemes'); 
        else 
            _e('Ad Listing Received', 'appthemes'); 
        ?>
   </h2>

   <img src="<?php bloginfo('template_url'); ?>/images/step3.gif" alt="" class="stepimg" />

	<div class="processlog">
	<?php 
	    // insert the ad and get back the post id
   		$post_id = cp_add_new_listing( $advals );
	?>
	</div>
    <div class="thankyou">


    <?php
	
	//incriment coupon code count only if total ad price was not zero
	if (isset($advals['cp_coupon_code']) && cp_check_coupon_discount($advals['cp_coupon_code']) )
		cp_use_coupon($advals['cp_coupon_code']);
		
    // call in the selected payment gateway as long as the price isn't zero
    if ( (get_option('cp_charge_ads') == 'yes') && ($advals['cp_sys_total_ad_cost'] != 0) ) {
		
		//load payment gateway page to process checkout
        include_once ( TEMPLATEPATH . '/includes/gateways/gateway.php' );

    } else {

    // otherwise the ad was free and show the thank you page.
        // get the post status
        $the_post = get_post( $post_id ); 

        // check to see what the ad status is set to
        if ( $the_post->post_status == 'pending' ) {

            // send ad owner an email
            cp_owner_new_ad_email( $post_id );

        ?>

            <h3><?php _e('Thank you! Your ad listing has been submitted for review.','appthemes') ?></h3>
            <p><?php _e('You can check the status by viewing your dashboard.','appthemes') ?></p>

        <?php } else { ?>

            <h3><?php _e('Thank you! Your ad listing has been submitted and is now live.','appthemes') ?></h3>
            <p><?php _e('Visit your dashboard to make any changes to your ad listing or profile.','appthemes') ?></p>
            <a href="<?php echo get_permalink($post_id); ?>"><?php _e('View your new ad listing.','appthemes') ?></a>

        <?php } ?>


    </div> <!-- /thankyou -->

    <?php
    }


    // send new ad notification email to admin
    if ( get_option('cp_new_ad_email') == 'yes' || $advals['cp_payment_method'] == 'banktransfer' )
        cp_new_ad_email( $post_id );


    // remove the temp session option from the database
    delete_option( 'cp_'.$_POST['oid'] );


} else {

?>

    <h2 class="dotted"><?php _e('An Error Has Occurred','appthemes') ?></h2>

    <div class="thankyou">
        <p><?php _e('Your session has expired or you are trying to submit a duplicate ad. Please start over.','appthemes') ?></p>
    </div>

<?php

}

?>

    <div class="pad100"></div>

