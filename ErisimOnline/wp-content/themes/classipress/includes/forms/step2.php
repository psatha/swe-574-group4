<?php
/**
 * This is step 2 of 3 for the ad submission form
 * 
 * @package ClassiPress
 * @subpackage New Ad
 * @author AppThemes
 *
 * here we are processing the images and gathering all the post values.
 * using sessions would be the optimal way but WP doesn't play nice so instead
 * we take all the form post values and put them into an associative array
 * and then store it in the wp_options table as a serialized array. essentially
 * we are using the wp_options table as our session holder and can access
 * the keys and values later and process the ad in step 3
 *
 */

global $current_user, $wpdb;

// check to see if there are images included
// then valid the image extensions
if ( !empty($_FILES['image']) ) 
    $error_msg = cp_validate_image();

// check to see is ad pack specified for fixed price option
if ( get_option('cp_price_scheme') == 'single' && get_option('cp_charge_ads') == 'yes' && !isset($_POST['ad_pack_id']) ) 
    $error_msg[] = __('Error: no ad pack has been defined. Please contact the site administrator.', 'appthemes');

// images are valid
if ( !$error_msg ) {

    // create the array that will hold all the post values
    $postvals = array();

    // upload the images and put into the new ad array
    if ( !empty($_FILES['image']) ) 
        $postvals = cp_process_new_image();

    // put all the posted form values into an array
    foreach ( $_POST as $key => $value ) 
        if(!is_array($_POST[$key]))
            $postvals[$key] = appthemes_clean($value);
        else
            $postvals[$key] = $value;
	

	//**************************EKLEME*****************************
	// Girilen lokasyon varsa alýr ve set edilmek üzere Session a yazar
	if(!empty($_POST['location']) && $_POST['location']!=''){
		$_SESSION['kordinat'] = $_POST['location'];
	}
	//*******************************************************
            
    // keep only numeric, commas or decimal values
    if ( !empty($_POST['cp_price']) ) {
        $postvals['cp_price'] = appthemes_clean_price($_POST['cp_price']);
		$_POST['cp_price'] = $postvals['cp_price'];
	}

		if ( isset($postvals['cp_currency']) && !empty($postvals['cp_currency']) )
				$price_curr = $postvals['cp_currency'];
		else
				$price_curr = get_option('cp_curr_symbol');

    // keep only values and insert/strip commas if needed
    if ( !empty($_POST['tags_input']) ) {
        $postvals['tags_input'] = appthemes_clean_tags($_POST['tags_input']);
		$_POST['tags_input'] = $postvals['tags_input'];
	}

    // store the user IP address, ID for later
    $postvals['cp_sys_userIP'] = appthemes_get_ip();
    $postvals['user_id'] = $current_user->ID;

    // see if the featured ad checkbox has been checked
    if ( isset($_POST['featured_ad']) ) {
        $postvals['featured_ad'] = $_POST['featured_ad'];
        // get the featured ad price into the array
        $postvals['cp_sys_feat_price'] = get_option('cp_sys_feat_price');
    }

    // calculate the ad listing fee and put into a variable
	if(isset($_POST['ad_pack_id'])) $post_ad_pack_id = $_POST['ad_pack_id']; else $post_ad_pack_id = '';
    if ( get_option('cp_charge_ads') == 'yes' )
        $postvals['cp_sys_ad_listing_fee'] = cp_ad_listing_fee($_POST['cat'], $post_ad_pack_id, $_POST['cp_price'], $price_curr);

    // check to prevent "Notice: Undefined index:" on php strict error checking. get ad pack id and lookup length
    $adpackid = '';
    if ( isset($_POST['ad_pack_id']) ) {
        $adpackid = $_POST['ad_pack_id'];
        $postvals['pack_duration'] = cp_get_ad_pack_length($adpackid);
    }

	//check if coupon code was entered, then check if coupon exists and is active
	if ( isset($_POST['cp_coupon_code']) ) {
		$coupon = cp_check_coupon_discount($_POST['cp_coupon_code']);
		
		//if $coupon has any results
		if ( $coupon ) {
			$postvals['cp_coupon_type'] = $coupon->coupon_discount_type;
			$postvals['cp_coupon'] = $coupon->coupon_discount;
		}
		//if coupon is entered but not valid, display proper error.
		elseif ( $_POST['cp_coupon_code'] != '' ) {
			$postvals['cp_coupon_type'] = '';
			$coupon_code_name = $_POST['cp_coupon_code'];
			$postvals['cp_coupon'] = '<span class="error-coupon">'. __("Coupon code '$coupon_code_name' is not active or does not exist.", 'appthemes') . '</span>';
		}

	}
	//if coupon was not entered, leave array unset
	else {
		$coupon = array();
	}
	
    // calculate the total cost of the ad
	if ( isset($postvals['cp_sys_feat_price']) )
    	$postvals['cp_sys_total_ad_cost'] = cp_calc_ad_cost($_POST['cat'], $adpackid, $postvals['cp_sys_feat_price'], $_POST['cp_price'], $coupon, $price_curr);
	else $postvals['cp_sys_total_ad_cost'] = cp_calc_ad_cost($_POST['cat'], $adpackid, 0, $_POST['cp_price'], $coupon, $price_curr);
	
	//UPDATE TOTAL BASED ON MEMBERSHIP
	//check for current users active membership pack and that its not expired
	if ( !empty($current_user->active_membership_pack) && appthemes_days_between_dates($current_user->membership_expires) > 0 ) {
		$postvals['cp_membership_pack'] = get_pack($current_user->active_membership_pack);
		//update the total cost based on the membership pack ID and current total cost
		$postvals['cp_sys_total_ad_cost'] = get_pack_benefit($postvals['cp_membership_pack'], $postvals['cp_sys_total_ad_cost']);
    //add featured cost to static pack type
    if ( isset($postvals['cp_sys_feat_price']) && in_array($postvals['cp_membership_pack']->pack_type, array('required_static', 'static')) )
      $postvals['cp_sys_total_ad_cost'] += $postvals['cp_sys_feat_price'];
    //subtract coupon discount from membership static
    if ( is_object($coupon) && in_array($postvals['cp_membership_pack']->pack_type, array('required_static', 'static')) ) {
      if($coupon->coupon_discount_type  != '%')
        $postvals['cp_sys_total_ad_cost'] = $postvals['cp_sys_total_ad_cost'] - (float)$coupon->coupon_discount;
      else
        $postvals['cp_sys_total_ad_cost'] = $postvals['cp_sys_total_ad_cost'] - ($postvals['cp_sys_total_ad_cost'] * (((float)($coupon->coupon_discount))/100));
    }
	}
	
  // prevent from minus prices if bigger discount applied	
	if ( $postvals['cp_sys_total_ad_cost'] < 0 )
    $postvals['cp_sys_total_ad_cost'] = '0.00';
	
	
    
    // Debugging section
    //echo '$_POST ATTACHMENT<br/>';
    //print_r($postvals['attachment']);

    //echo '$_POST PRINT<br/>';
    //print_r($_POST);

    //echo '<br/><br/>$postvals PRINT<br/>';
    //print_r($postvals);

    // now put the array containing all the post values into the database
    // instead of passing hidden values which are easy to hack and so we
    // can also retrieve it on the next step
    $option_name = 'cp_'.$postvals['oid'];
    update_option( $option_name, $postvals );

    ?>

    <div id="step2"></div>

      <h2 class="dotted"><?php _e('Review Your Listing','appthemes');?></h2>

            <img src="<?php bloginfo('template_url'); ?>/images/step2.gif" alt="" class="stepimg" />

            <form name="mainform" id="mainform" class="form_step" action="" method="post" enctype="multipart/form-data">

                <ol>

                    <?php
                    // pass in the form post array and show the ad summary based on the formid
					//print_r($postvals);
                    echo cp_show_review( $postvals );

                    // debugging info
                    //echo get_option('cp_price_scheme') .'<-- pricing scheme<br/>';
                    //echo $postvals['cat'] .'<-- catid<br/>';
                    //echo get_option('cp_cat_price_'.$postvals['cat']) .'<-- cat price<br/>';
                    //echo $postvals['user_id'] .'<-- userid<br/>';
                    //echo get_option('cp_price_per_ad') .'<-- listing cost<br/>';
                    //echo get_option('cp_curr_symbol_pos') .'<-- currency position<br/>'; 
                    ?>
                    
                    <li>
                        <?php if ( $postvals['cp_sys_total_ad_cost'] > 0 ) : ?>
                        <div class="labelwrapper">
                            <label><?php _e('Payment Method','appthemes'); ?>:</label>
                        </div>
                        
                        <select name="cp_payment_method" class="dropdownlist required">
                            <?php if ( get_option('cp_enable_paypal') == 'yes' ) { ?><option value="paypal"><?php echo _e('PayPal', 'appthemes') ?></option><?php } ?>
                            <?php if ( get_option('cp_enable_bank') == 'yes' ) { ?><option value="banktransfer"><?php echo _e('Bank Transfer', 'appthemes') ?></option><?php } ?>
                            
                            <?php cp_action_payment_method(); ?>
                            
                        </select>
                        
                        <?php endif; ?>
                        
                        <div class="clr"></div>
                        
                    </li>
                    
                </ol>
                
                <div class="pad10"></div>

		        <div class="license"><?php echo get_option('cp_ads_tou_msg'); ?></div>

                <div class="clr"></div>


                <p class="terms"><?php _e('By clicking the proceed button below, you agree to our terms and conditions.','appthemes'); ?>
                <br/>
                <?php _e('Your IP address has been logged for security purposes:','appthemes'); ?> <?php echo $postvals['cp_sys_userIP']; ?>
				</p>


                <p class="btn2">
                    <input type="button" name="goback" class="btn_orange" value="<?php _e('Go back','appthemes') ?>" onclick="history.back()" />
                    <input type="submit" name="step2" id="step2" class="btn_orange" value="<?php _e('Proceed ','appthemes'); ?> &rsaquo;&rsaquo;" />
                </p>

                <input type="hidden" id="oid" name="oid" value="<?php echo $postvals['oid']; ?>" />

	    </form>


		<div class="clear"></div>

<?php

} else {

?>

    <h2 class="dotted"><?php _e('An Error Has Occurred', 'appthemes') ?></h2>
    
    <div class="thankyou">
        <p><?php echo appthemes_error_msg( $error_msg ); ?></p>
        <input type="button" name="goback" class="btn_orange" value="&lsaquo;&lsaquo; <?php _e('Go Back', 'appthemes') ?>" onclick="history.back()" />
    </div>
  

<?php
}
?>



