<?php

/* Process Ad Payment - PayPal IPN
 *
 * @author AppThemes
 * @version 3.0.4
 * 
 *
 */


// this is the paypal ipn listener which waits for the request
function cp_ipn_listener() {

    // validate the paypal request by sending it back to paypal
    function cp_ipn_request_check() {
		
		define('SSL_P_URL', 'https://www.paypal.com/cgi-bin/webscr');
		define('SSL_SAND_URL','https://www.sandbox.paypal.com/cgi-bin/webscr');
			
		$hostname = gethostbyaddr ( $_SERVER ['REMOTE_ADDR'] );
		if (! preg_match ( '/paypal\.com$/', $hostname )) {
			$ipn_status = 'Validation post isn\'t from PayPal';
			if (get_option('cp_paypal_ipn_debug') == 'true')
				wp_mail(get_option('admin_email'), $ipn_status, 'fail');
			return false;
		}
		
		// parse the paypal URL
		$paypal_url = ($_POST['test_ipn'] == 1) ? SSL_SAND_URL : SSL_P_URL;
		$url_parsed = parse_url($paypal_url);


        $post_string = '';    
		foreach ($_POST as $field=>$value) { 
			$post_string .= $field.'='.urlencode(stripslashes($value)).'&'; 
		}
		$post_string.="cmd=_notify-validate"; // append ipn command

        // get the correct paypal url to post request to
        if (get_option('cp_paypal_sandbox') == 'true')
            $fp = fsockopen ( 'ssl://www.sandbox.paypal.com', "443", $err_num, $err_str, 60 );
        else
            $fp = fsockopen ( 'ssl://www.paypal.com', "443", $err_num, $err_str, 60 );
        
        
        $ipn_response = '';
        
        
        if(!$fp) {
			// could not open the connection.  If loggin is on, the error message
			// will be in the log.
			$ipn_status = "fsockopen error no. $err_num: $err_str";
			if (get_option('cp_paypal_ipn_debug') == 'true')
				wp_mail(get_option('admin_email'), $ipn_status, 'fail');
			return false;
		} else { 
			// Post the data back to paypal
			fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n"); 
			fputs($fp, "Host: $url_parsed[host]\r\n"); 
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
			fputs($fp, "Content-length: ".strlen($post_string)."\r\n"); 
			fputs($fp, "Connection: close\r\n\r\n"); 
			fputs($fp, $post_string . "\r\n\r\n"); 
		
			// loop through the response from the server and append to variable
			while(!feof($fp)) { 
		   	$ipn_response .= fgets($fp, 1024); 
		   } 
		  fclose($fp); // close connection
		}    
        
        // Invalid IPN transaction.  Check the $ipn_status and log for details.
		if (!preg_match("/VERIFIED/s", $ipn_response)) {
			$ipn_status = 'IPN Validation Failed';
			if (get_option('cp_paypal_ipn_debug') == 'true')
				wp_mail(get_option('admin_email'), $ipn_status, 'fail');
			return false;
		} else {
			$ipn_status = "IPN VERIFIED";
			if (get_option('cp_paypal_ipn_debug') == 'true')
				wp_mail(get_option('admin_email'), $ipn_status, 'SUCCESS');
			return true;
		}
    }
	
	
		
    // if the test variable is set (sandbox mode), send a debug email with all values
    if (isset($_POST['test_ipn'])) {
        $_REQUEST = stripslashes_deep($_REQUEST);
		if (get_option('cp_paypal_ipn_debug') == 'true')
	        wp_mail(get_option('admin_email'), 'PayPal IPN Debug Email Test IPN', "".print_r($_REQUEST, true));
    }

    // make sure the request came from classipress (pid) or paypal (txn_id refund, update)
    if (isset($_POST['txn_id']) || isset($_REQUEST['invoice'])) {
        $_REQUEST = stripslashes_deep($_REQUEST);

        // if paypal sends a response code back let's handle it
        if (cp_ipn_request_check()) {

            // send debug email to see paypal ipn post vars
            if (get_option('cp_paypal_ipn_debug') == 'true')
                wp_mail(get_option('admin_email'), 'PayPal IPN Debug Email Main', "".print_r($_REQUEST, true));

            // process the ad since paypal gave us a valid response
            do_action('cp_init_ipn_response', $_REQUEST);

        }
		//this exit caused WSOD, unsure of its original purpose but it can be addressed with checkout
		//goes through detailed security testing and security fixes.
        //exit;
    }

}

// initialize the listener if IPN option is turned on
if (get_option('cp_enable_paypal_ipn') == 'yes')
    add_action('init', 'cp_ipn_listener');



function cp_handle_ipn_response($_POST) {
    global $wpdb;

	//step functions required to process orders
	include_once("wp-load.php");
	include_once (TEMPLATEPATH . '/includes/forms/step-functions.php');

	// make sure the ad unique trans id (stored in invoice var) is included
	if ( !empty($_POST['txn_id']) && !empty($_POST['invoice']) ) {


            // process the ad based on the paypal response
            switch (strtolower($_POST['payment_status'])) :

                // payment was made so we can approve the ad
                case 'completed' :

                    $pid = trim($_POST['invoice']);
					
					//attempt to process membership order first
					$orders = get_user_orders('',$pid);
          if(!empty($orders)){
    					$order_id = get_order_id($orders);
    					$storedOrder = get_option($orders);
					
    					$user_id = get_order_userid($orders); 
    					$the_user = get_userdata($user_id);
    					if (get_option('cp_paypal_ipn_debug') == 'true' && !empty($orders))
		                wp_mail(get_option('admin_email'), 'PayPal IPN Attempting to Activate Memebership', print_r($orders, true).PHP_EOL.print_r($order, true).PHP_EOL.print_r($_REQUEST, true));
						
    					$order_processed = appthemes_process_membership_order($the_user, $storedOrder);
          }
					if($order_processed) {
            //send email to user
            cp_owner_activated_membership_email($the_user, $order_processed);
            
						//admin email confirmation
						//TODO - move into wordpress options panel and allow customization
						wp_mail(get_option('admin_email'), 'PayPal IPN Activated Memebership', 
							__('A membership order has been completed. Check to make sure this is a valid order by comparing this messages Paypal Transaction ID to the respective ID in the Paypal payment receipt email.','appthemes').PHP_EOL
							//.print_r($order_processed, true).PHP_EOL
							//.PHP_EOL.print_r($_POST, true).PHP_EOL
							.__('Order ID: ','appthemes').print_r($orders, true).PHP_EOL
							.__('User ID: ','appthemes').print_r($user_id, true).PHP_EOL
							.__('User Login: ','appthemes').print_r($the_user->user_login, true).PHP_EOL
							.__('Pack Name: ','appthemes').print_r(stripslashes($storedOrder['pack_name']), true).PHP_EOL
							.__('Total Cost: ','appthemes').print_r($storedOrder['total_cost'], true).PHP_EOL
							.__('Paypal Transaction ID: ','appthemes').print_r($_POST['txn_id'], true).PHP_EOL
						);
						break;
					}

                    $sql = $wpdb->prepare("SELECT p.ID, p.post_status
                            FROM $wpdb->posts p, $wpdb->postmeta m
                            WHERE p.ID = m.post_id
                            AND p.post_status <> 'publish'
                            AND m.meta_key = 'cp_sys_ad_conf_id'
                            AND m.meta_value = %s
                            ",
                            $pid);

                    $newadid = $wpdb->get_row($sql);


                    // if the ad is found, then publish it
                    if($newadid) {
                        $the_ad = array();
                        $the_ad['ID'] = $newadid->ID;
                        $the_ad['post_status'] = 'publish';
                        $ad_id = wp_update_post($the_ad);

                        // now we need to update the ad expiration date so they get the full length of time
                        // sometimes they didn't pay for the ad right away or they are renewing

                        // first get the ad duration and first see if ad packs are being used
                        // if so, get the length of time in days otherwise use the default
                        // prune period defined on the CP settings page

                        $ad_length = get_post_meta($ad_id, 'cp_sys_ad_duration', true);

                        if(isset($ad_length))
                            $ad_length = $ad_length;
                        else
                            $ad_length = get_option('cp_prun_period');

                        // set the ad listing expiration date
                        $ad_expire_date = date_i18n('m/d/Y H:i:s', strtotime('+' . $ad_length . ' days')); // don't localize the word 'days'

                        //now update the expiration date on the ad
                        update_post_meta($ad_id, 'cp_sys_expire_date', $ad_expire_date);
                    }

                break;

                case 'pending' :

                    // send an email if payment is pending
                    $mailto = get_option('admin_email');
                    $subject = __('PayPal IPN - payment pending', 'appthemes');
                    $headers = 'From: '. __('ClassiPress Admin', 'appthemes') .' <'. get_option('admin_email') .'>' . "\r\n";
                    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

                    $message  = __('Dear Admin,', 'appthemes') . "\r\n\r\n";
                    $message .= sprintf(__('The following payment is pending on your %s website.', 'appthemes'), $blogname) . "\r\n\r\n";
                    $message .= __('Payment Details', 'appthemes') . "\r\n";
                    $message .= __('-----------------') . "\r\n";
                    $message .= __('Payer PayPal address: ', 'appthemes') . $_POST['payer_email'] . "\r\n";
                    $message .= __('Transaction ID: ', 'appthemes') . $_POST['txn_id'] . "\r\n";
                    $message .= __('Payer first name: ', 'appthemes') . $_POST['first_name'] . "\r\n";
                    $message .= __('Payer last name: ', 'appthemes') . $_POST['last_name'] . "\r\n";
                    $message .= __('Payment type: ', 'appthemes') . $_POST['payment_type'] . "\r\n";
                    $message .= __('Amount: ', 'appthemes') . $_POST['mc_gross'] . " (" . $_POST['mc_currency'] . ")\r\n\r\n";
                    $message .= __('Full Details', 'appthemes') . "\r\n";
                    $message .= __('-----------------') . "\r\n";
                    $message .= print_r($_POST, true) . "\r\n";
                    
                    wp_mail($mailto, $subject, $message, $headers);

                break;

                // payment failed so don't approve the ad
                case 'denied' :
                case 'expired' :
                case 'failed' :
                case 'voided' :

                    // send an email if payment didn't work
                    $mailto = get_option('admin_email');
                    $subject = __('PayPal IPN - payment failed', 'appthemes');
                    $headers = 'From: '. __('ClassiPress Admin', 'appthemes') .' <'. get_option('admin_email') .'>' . "\r\n";
                    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

                    $message  = __('Dear Admin,', 'appthemes') . "\r\n\r\n";
                    $message .= sprintf(__('The following payment has failed on your %s website.', 'appthemes'), $blogname) . "\r\n\r\n";
                    $message .= __('Payment Details', 'appthemes') . "\r\n";
                    $message .= __('-----------------') . "\r\n";
                    $message .= __('Payer PayPal address: ', 'appthemes') . $_POST['payer_email'] . "\r\n";
                    $message .= __('Transaction ID: ', 'appthemes') . $_POST['txn_id'] . "\r\n";
                    $message .= __('Payer first name: ', 'appthemes') . $_POST['first_name'] . "\r\n";
                    $message .= __('Payer last name: ', 'appthemes') . $_POST['last_name'] . "\r\n";
                    $message .= __('Payment type: ', 'appthemes') . $_POST['payment_type'] . "\r\n";
                    $message .= __('Amount: ', 'appthemes') . $_POST['mc_gross'] . " (" . $_POST['mc_currency'] . ")\r\n\r\n";
                    $message .= __('Full Details', 'appthemes') . "\r\n";
                    $message .= __('-----------------') . "\r\n";
                    $message .= print_r($_POST, true) . "\r\n";
                    
                    wp_mail($mailto, $subject, $message, $headers);

                break;

                case 'refunded' :
                case 'reversed' :
                case 'chargeback' :

                    // send an email if payment was refunded
                    $mailto = get_option('admin_email');
                    $subject = __('PayPal IPN - payment refunded/reversed', 'appthemes');
                    $headers = 'From: '. __('ClassiPress Admin', 'appthemes') .' <'. get_option('admin_email') .'>' . "\r\n";
                    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

                    $message  = __('Dear Admin,', 'appthemes') . "\r\n\r\n";
                    $message .= sprintf(__('The following payment has been marked as refunded on your %s website.', 'appthemes'), $blogname) . "\r\n\r\n";
                    $message .= __('Payment Details', 'appthemes') . "\r\n";
                    $message .= __('-----------------') . "\r\n";
                    $message .= __('Payer PayPal address: ', 'appthemes') . $_POST['payer_email'] . "\r\n";
                    $message .= __('Transaction ID: ', 'appthemes') . $_POST['txn_id'] . "\r\n";
                    $message .= __('Payer first name: ', 'appthemes') . $_POST['first_name'] . "\r\n";
                    $message .= __('Payer last name: ', 'appthemes') . $_POST['last_name'] . "\r\n";
                    $message .= __('Payment type: ', 'appthemes') . $_POST['payment_type'] . "\r\n";
                    $message .= __('Reason code: ', 'appthemes') . $_POST['reason_code'] . "\r\n";
                    $message .= __('Amount: ', 'appthemes') . $_POST['mc_gross'] . " (" . $_POST['mc_currency'] . ")\r\n\r\n";
                    $message .= __('Full Details', 'appthemes') . "\r\n";
                    $message .= __('-----------------') . "\r\n";
                    $message .= print_r($_POST, true) . "\r\n";
                    
                    wp_mail($mailto, $subject, $message, $headers);

                break;
            endswitch;

            // regardless of what happens, log the transaction
            if (file_exists(TEMPLATEPATH . '/includes/gateways/process.php'))
                include_once (TEMPLATEPATH . '/includes/gateways/process.php');
	}
}


add_action('cp_init_ipn_response', 'cp_handle_ipn_response');

?>