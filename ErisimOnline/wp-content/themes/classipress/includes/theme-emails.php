<?php
/**
 *
 * Emails that get called and sent out for ClassiPress
 * @package ClassiPress
 * @author AppThemes
 * For wp_mail to work, you need the following:
 * settings SMTP and smtp_port need to be set in your php.ini
 * also, either set the sendmail_from setting in php.ini, or pass it as an additional header.
 *
 */


// send new ad notification email to admin
function cp_new_ad_email($post_id) {

		// get the post values
		$the_ad = get_post($post_id);
		$category = appthemes_get_custom_taxonomy($post_id, APP_TAX_CAT, 'name');

    $ad_title = stripslashes($the_ad->post_title);
    $ad_cat = stripslashes($category);
    $ad_author = stripslashes(cp_get_user_name($the_ad->post_author));
    $ad_slug = get_option('siteurl').'/?post_type='.APP_POST_TYPE.'&p='.$post_id;
    //$ad_content = appthemes_filter(stripslashes($the_ad->post_content));
    $adminurl = get_option('siteurl').'/wp-admin/post.php?action=edit&post='.$post_id;

    $mailto = get_option('admin_email');
    // $mailto = 'tester@127.0.0.1'; // USED FOR TESTING
    $subject = __('New Ad Submission','appthemes');
    $headers = 'From: '. __('ClassiPress Admin', 'appthemes') .' <'. get_option('admin_email') .'>' . "\r\n";

    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $message  = __('Dear Admin,', 'appthemes') . "\r\n\r\n";
    $message .= sprintf(__('The following ad listing has just been submitted on your %s website.', 'appthemes'), $blogname) . "\r\n\r\n";
    $message .= __('Ad Details', 'appthemes') . "\r\n";
    $message .= __('-----------------') . "\r\n";
    $message .= __('Title: ', 'appthemes') . $ad_title . "\r\n";
    $message .= __('Category: ', 'appthemes') . $ad_cat . "\r\n";
    $message .= __('Author: ', 'appthemes') . $ad_author . "\r\n\r\n";
    //$message .= __('Description: ', 'appthemes') . $ad_content . "\r\n";
    if(get_post_meta($post_id, 'cp_payment_method', true) == 'banktransfer'){
      $message .= __('Payment Type: ', 'appthemes') . __('Bank Transfer', 'appthemes') . "\r\n";
      $message .= __('Transaction ID: ', 'appthemes') . get_post_meta($post_id, 'cp_sys_ad_conf_id', true) . "\r\n";
      $message .= __('Reference #: ', 'appthemes') . $post_id . "\r\n";
      $message .= __('Total Amount: ', 'appthemes') . get_post_meta($post_id, 'cp_sys_total_ad_cost', true) . " (" . get_option('cp_curr_pay_type') . ")\r\n";
    }
    $message .= __('-----------------') . "\r\n\r\n";
    $message .= __('Preview Ad: ', 'appthemes') . $ad_slug . "\r\n";
    $message .= sprintf(__('Edit Ad: %s', 'appthemes'), $adminurl) . "\r\n\r\n\r\n";
    $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
    $message .= __('ClassiPress', 'appthemes') . "\r\n\r\n";

    // ok let's send the email
    wp_mail($mailto, $subject, $message, $headers);

}


// send new ad notification email to ad owner
function cp_owner_new_ad_email($post_id) {

    // get the post values
    $the_ad = get_post($post_id);
    $category = appthemes_get_custom_taxonomy($post_id, APP_TAX_CAT, 'name');

    $ad_title = stripslashes($the_ad->post_title);
    $ad_cat = stripslashes($category);
    $ad_author = stripslashes(cp_get_user_name($the_ad->post_author));
    $ad_author_email = stripslashes(get_the_author_meta('user_email', $the_ad->post_author));
    $ad_status = stripslashes($the_ad->post_status);
    //$ad_content = appthemes_filter(stripslashes($the_ad->post_content));
    $siteurl = trailingslashit(get_option('home'));

    $dashurl = trailingslashit(CP_DASHBOARD_URL);

    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $mailto = $ad_author_email;
    //$mailto = 'tester@127.0.0.1'; // USED FOR TESTING

    $subject = sprintf(__('Your Ad Submission on %s','appthemes'), $blogname);
    $headers = 'From: '. sprintf(__('%s Admin', 'appthemes'), $blogname) .' <'. get_option('admin_email') .'>' . "\r\n";

    $message  = sprintf(__('Hi %s,', 'appthemes'), $ad_author) . "\r\n\r\n";
    $message .= sprintf(__('Thank you for your recent submission! Your ad listing has been submitted for review and will not appear live on our site until it has been approved. Below you will find a summary of your ad listing on the %s website.', 'appthemes'), $blogname) . "\r\n\r\n";
    $message .= __('Ad Details', 'appthemes') . "\r\n";
    $message .= __('-----------------') . "\r\n";
    $message .= __('Title: ', 'appthemes') . $ad_title . "\r\n";
    $message .= __('Category: ', 'appthemes') . $ad_cat . "\r\n";
    $message .= __('Status: ', 'appthemes') . $ad_status . "\r\n";
    //$message .= __('Description: ', 'appthemes') . $ad_content . "\r\n";
    $message .= __('-----------------') . "\r\n\r\n";
    $message .= __('You may check the status of your ad(s) at anytime by logging into your dashboard.', 'appthemes') . "\r\n";
    $message .= $dashurl . "\r\n\r\n\r\n\r\n";
    $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
    $message .= sprintf(__('Your %s Team', 'appthemes'), $blogname) . "\r\n";
    $message .= $siteurl . "\r\n\r\n\r\n\r\n";

    // ok let's send the email
    wp_mail($mailto, $subject, $message, $headers);

}


// send new ad notification email to ad owner when purchased by bank transfer
function cp_bank_owner_new_ad_email($post_id) {

    // get the post values
    $the_ad = get_post($post_id);
    $category = appthemes_get_custom_taxonomy($post_id, APP_TAX_CAT, 'name');

    $ad_title = stripslashes($the_ad->post_title);
    $ad_cat = stripslashes($category);
    $ad_author = stripslashes(cp_get_user_name($the_ad->post_author));
    $ad_author_email = stripslashes(get_the_author_meta('user_email', $the_ad->post_author));
    $ad_status = stripslashes($the_ad->post_status);
    //$ad_content = appthemes_filter(stripslashes($the_ad->post_content));
    $siteurl = trailingslashit(get_option('home'));

    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $mailto = $ad_author_email;

    $subject = sprintf(__('Your Ad Submission on %s','appthemes'), $blogname);
    $headers = 'From: '. sprintf(__('%s Admin', 'appthemes'), $blogname) .' <'. get_option('admin_email') .'>' . "\r\n";

    $message  = sprintf(__('Hi %s,', 'appthemes'), $ad_author) . "\r\n\r\n";
    $message .= sprintf(__('Thank you for your recent submission! Your ad listing has been submitted and will not appear live on our site until you pay for it. Below you will find a summary of your ad listing on the %s website.', 'appthemes'), $blogname) . "\r\n\r\n";
    $message .= __('Ad Details', 'appthemes') . "\r\n";
    $message .= __('-----------------') . "\r\n";
    $message .= __('Title: ', 'appthemes') . $ad_title . "\r\n";
    $message .= __('Category: ', 'appthemes') . $ad_cat . "\r\n";
    $message .= __('Status: ', 'appthemes') . $ad_status . "\r\n";
    //$message .= __('Description: ', 'appthemes') . $ad_content . "\r\n";
    $message .= __('-----------------') . "\r\n\r\n";

    $message .= __('Please include the following details when sending the bank transfer. Once your transfer has been verified, we will then approve your ad listing.', 'appthemes') . "\r\n\r\n";
    $message .= __('Details for Payment', 'appthemes') . "\r\n";
    $message .= __('-----------------') . "\r\n";
    $message .= __('Transaction ID: ', 'appthemes') . get_post_meta($post_id, 'cp_sys_ad_conf_id', true) . "\r\n";
    $message .= __('Reference #: ', 'appthemes') . $post_id . "\r\n";
    $message .= __('Total Amount: ', 'appthemes') . get_post_meta($post_id, 'cp_sys_total_ad_cost', true) . " (" . get_option('cp_curr_pay_type') . ")\r\n";
    $message .= __('-----------------') . "\r\n\r\n";

    $message .= __('Bank Transfer Instructions', 'appthemes') . "\r\n";
    $message .= __('-----------------') . "\r\n";
    $message .= strip_tags( appthemes_br2nl( stripslashes( get_option('cp_bank_instructions') ) ) ) . "\r\n";
    $message .= __('-----------------') . "\r\n\r\n";

    $message .= __('For questions or problems, please contact us directly at', 'appthemes') . " " . get_option('admin_email') . "\r\n\r\n\r\n\r\n";
    $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
    $message .= sprintf(__('Your %s Team', 'appthemes'), $blogname) . "\r\n";
    $message .= $siteurl . "\r\n\r\n\r\n\r\n";

    // ok let's send the email
    wp_mail($mailto, $subject, $message, $headers);

}


// when an ad is approved or expires, send the ad owner an email
function cp_notify_ad_owner_email($new_status, $old_status, $post) {
    global $current_user, $wpdb;

    //$contributor = get_userdata($post->post_author);

    $the_ad = get_post($post->ID);
    $category = appthemes_get_custom_taxonomy($post->ID, APP_TAX_CAT, 'name');

    $ad_title = stripslashes($the_ad->post_title);
    $ad_cat = stripslashes($category);
    $ad_author_id = stripslashes(get_the_author_meta('ID', $the_ad->post_author));
    $ad_author = stripslashes(cp_get_user_name($the_ad->post_author));
    $ad_author_email = stripslashes(get_the_author_meta('user_email', $the_ad->post_author));
    $ad_status = stripslashes($the_ad->post_status);
    $ad_content = appthemes_filter(stripslashes($the_ad->post_content));
    $siteurl = trailingslashit(get_option('home'));
    $dashurl = trailingslashit(CP_DASHBOARD_URL);


    // check to see if ad is legacy or not
    if(get_post_meta($post->ID, 'email', true))
        $mailto = get_post_meta($post->ID, 'email', true);
    else
        $mailto = $ad_author_email;

    //$mailto = 'tester@127.0.0.1'; // USED FOR TESTING

    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    // make sure the admin wants to send emails
    $send_approved_email = get_option('cp_new_ad_email_owner');
    $send_expired_email = get_option('cp_expired_ad_email_owner');

    // if the ad has been approved send email to ad owner only if owner is not equal to approver
    // admin approving own ads or ad owner pausing and reactivating ad on his dashboard don't need to send email
    if ($old_status == 'pending' && $new_status == 'publish' && $current_user->ID != $ad_author_id && $send_approved_email == 'yes') {

        $subject = __('Your Ad Has Been Approved','appthemes');
        $headers = 'From: '. sprintf(__('%s Admin', 'appthemes'), $blogname) .' <'. get_option('admin_email') .'>' . "\r\n";

        $message  = sprintf(__('Hi %s,', 'appthemes'), $ad_author) . "\r\n\r\n";
        $message .= sprintf(__('Your ad listing, "%s" has been approved and is now live on our site.', 'appthemes'), $ad_title) . "\r\n\r\n";

        $message .= __('You can view your ad by clicking on the following link:', 'appthemes') . "\r\n";
        $message .= get_permalink($post->ID) . "\r\n\r\n\r\n\r\n";
        $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
        $message .= sprintf(__('Your %s Team', 'appthemes'), $blogname) . "\r\n";
        $message .= $siteurl . "\r\n\r\n\r\n\r\n";

        // ok let's send the email
        wp_mail($mailto, $subject, $message, $headers);


    // if the ad has expired, send an email to the ad owner only if owner is not equal to approver
    } elseif ($old_status == 'publish' && $new_status == 'draft' && $current_user->ID != $ad_author_id && $send_expired_email == 'yes') {

        $subject = __('Your Ad Has Expired','appthemes');
        $headers = 'From: '. sprintf(__('%s Admin', 'appthemes'), $blogname) .' <'. get_option('admin_email') .'>' . "\r\n";

        $message  = sprintf(__('Hi %s,', 'appthemes'), $ad_author) . "\r\n\r\n";
        $message .= sprintf(__('Your ad listing, "%s" has expired.', 'appthemes'), $ad_title) . "\r\n\r\n";

        if (get_option('cp_allow_relist') == 'yes') {
            $message .= __('If you would like to relist your ad, please visit your dashboard and click the "relist" link.', 'appthemes') . "\r\n";
            $message .= $dashurl . "\r\n\r\n\r\n\r\n";
        }

        $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
        $message .= sprintf(__('Your %s Team', 'appthemes'), $blogname) . "\r\n";
        $message .= $siteurl . "\r\n\r\n\r\n\r\n";

        // ok let's send the email
        wp_mail($mailto, $subject, $message, $headers);

    }
}

add_filter('transition_post_status', 'cp_notify_ad_owner_email', 10, 3);


// ad poster sidebar contact form email
function cp_contact_ad_owner_email($postID) {

    // wp_mail doesn't seem to work with cc or bcc in headers (as of 2.9.2)
    // this is here for adding it later
    // $Cc = 'youremailaddress@domain.com';
    // $Bcc = get_option('admin_email');

    // check to see if ad is legacy or not
    if(get_post_meta($postID, 'email', true))
        $mailto = get_post_meta($postID, 'email', true);
    else
        $mailto = get_the_author_meta('user_email');

    $from_name = strip_tags($_POST['from_name']);
    $from_email = strip_tags($_POST['from_email']);
    //$mailto = 'testing@appthemes.com'; // USED FOR TESTING
    $subject = strip_tags($_POST['subject']);
    $headers = "From: $from_name <$from_email> \r\n";
    $headers .= "Reply-To: $from_name <$from_email> \r\n";
    // $headers .= "Cc: $Cc \r\n";
    // $headers .= "BCC: $Bcc \r\n";

    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails
    $sitename = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $siteurl = trailingslashit(get_option('home'));
    $permalink = get_permalink();

    $message  = sprintf(__('Someone is interested in your ad listing: %s', 'appthemes'), $permalink) . "\r\n\r\n";
    // $message  = sprintf(__('From: %s - %s', 'appthemes'), $from_name, $from_email) . "\r\n\r\n";
		$fixPostMessage = stripslashes($_POST['message']);
    $message .= '"' . wordwrap(strip_tags($fixPostMessage), 70) . '"' . "\r\n\r\n\r\n";
    $message .= sprintf(__('Name: %s', 'appthemes'), $from_name) . "\r\n";
    $message .= sprintf(__('E-mail: %s', 'appthemes'), $from_email) . "\r\n\r\n\r\n\r\n";
    $message .= '-----------------------------------------' . "\r\n";
    $message .= sprintf(__('This message was sent from %s', 'appthemes'), $sitename) . "\r\n";
    $message .=  $siteurl . "\r\n\r\n";
		$message .= __('Sent from IP Address: ', 'appthemes') . appthemes_get_ip() . "\r\n\r\n"; 

    // ok let's send the email
    wp_mail($mailto, $subject, $message, $headers);

}



// overwrite the default generic WordPress from name and email address
if(get_option('cp_custom_email_header') == 'yes') {

    if (!class_exists('wp_mail_from')) :
        class wp_mail_from {

            function wp_mail_from() {
                add_filter('wp_mail_from', array(&$this, 'cp_mail_from'));
                add_filter('wp_mail_from_name', array(&$this, 'cp_mail_from_name'));
            }

            // new from name
            function cp_mail_from_name() {
                $name = get_option('blogname');
                $name = esc_attr($name);
                return $name;
            }

            // new email address
            function cp_mail_from() {
                $email = get_option('admin_email');
                $email = is_email($email);
                return $email;
            }

        }

        $wp_mail_from = new wp_mail_from();

    endif;

}


// email that gets sent out to new users once they register
function app_new_user_notification($user_id, $plaintext_pass = '') {
	global $app_abbr, $wp_mail_from;
	if (!defined('PHP_EOL')) define ('PHP_EOL', strtoupper(substr(PHP_OS,0,3) == 'WIN') ? "\r\n" : "\n");

	$user = new WP_User($user_id);

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);
	//$user_email = 'tester@127.0.0.1'; // USED FOR TESTING

	// variables that can be used by admin to dynamically fill in email content
	$find = array('/%username%/i', '/%password%/i', '/%blogname%/i', '/%siteurl%/i', '/%loginurl%/i', '/%useremail%/i');
	$replace = array($user_login, $plaintext_pass, get_option('blogname'), get_option('siteurl'), get_option('siteurl').'/login.php', $user_email);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	// send the site admin an email everytime a new user registers
	if (get_option($app_abbr.'_nu_admin_email') == 'yes') {	
		$message  = sprintf(__('New user registration on your site %s:', 'appthemes'), $blogname) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s', 'appthemes'), $user_login) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s', 'appthemes'), $user_email) . "\r\n";

		@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration', 'appthemes'), $blogname), $message);
	}

	if ( empty($plaintext_pass) )
		return;

	// check and see if the custom email option has been enabled
	// if so, send out the custom email instead of the default WP one
	if (get_option($app_abbr.'_nu_custom_email') == 'yes') {	

		// email sent to new user starts here				
		$from_name = strip_tags(get_option($app_abbr.'_nu_from_name'));
		$from_email = strip_tags(get_option($app_abbr.'_nu_from_email'));

		// search and replace any user added variable fields in the subject line
		$subject = stripslashes(get_option($app_abbr.'_nu_email_subject'));
		$subject = preg_replace($find, $replace, $subject);
		$subject = preg_replace("/%.*%/", "", $subject);	

		// search and replace any user added variable fields in the body
		$message = stripslashes(get_option($app_abbr.'_nu_email_body'));
		$message = preg_replace($find, $replace, $message);
		$message = preg_replace("/%.*%/", "", $message);

		// assemble the header
		$headers = "From: $from_name <$from_email> \r\n";
		$headers .= "Reply-To: $from_name <$from_email> \r\n";	
		$headers .= "Content-Type: ". get_option($app_abbr.'_nu_email_type') . PHP_EOL;

    // if custom from name and email address enabled in CP, remove it filters
    if(get_option('cp_custom_email_header') == 'yes') {
      remove_filter('wp_mail_from', array($wp_mail_from, 'cp_mail_from'));
      remove_filter('wp_mail_from_name', array($wp_mail_from, 'cp_mail_from_name'));
    }
		// ok let's send the new user an email
		wp_mail($user_email, $subject, $message, $headers);

	// send the default email to debug
	} else {

		$message  = sprintf(__('Username: %s', 'appthemes'), $user_login) . "\r\n";
		$message .= sprintf(__('Password: %s', 'appthemes'), $plaintext_pass) . "\r\n";
		$message .= wp_login_url() . "\r\n";

		wp_mail($user_email, sprintf(__('[%s] Your username and password', 'appthemes'), $blogname), $message);

	}

}

// send new ad notification email to admin
function app_report_post($post_id) {

    // get the post values
    $the_ad = get_post($post_id);
    $category = appthemes_get_custom_taxonomy($post_id, APP_TAX_CAT, 'name');

    $ad_title = stripslashes($the_ad->post_title);
    $ad_cat = stripslashes($category);
    $ad_author = stripslashes(cp_get_user_name($the_ad->post_author));
  	$ad_slug = get_option('siteurl').'/?post_type='.APP_POST_TYPE.'&p='.$post_id;
    //$ad_content = appthemes_filter(stripslashes($the_ad->post_content));
    $adminurl = get_option('siteurl').'/wp-admin/post.php?action=edit&post='.$post_id;

    $mailto = get_option('admin_email');
    //$mailto = 'tester@127.0.0.1'; // USED FOR TESTING
    $subject = __('Post Reported','appthemes');
    $headers = 'From: '. __('ClassiPress Admin', 'appthemes') .' <'. get_option('admin_email') .'>' . "\r\n";

    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $message  = __('Dear Admin,', 'appthemes') . "\r\n\r\n";
    $message .= sprintf(__('The following ad listing has just been reported on your %s website.', 'appthemes'), $blogname) . "\r\n\r\n";
    $message .= __('Ad Details', 'appthemes') . "\r\n";
    $message .= __('-----------------') . "\r\n";
    $message .= __('Title: ', 'appthemes') . $ad_title . "\r\n";
    $message .= __('Category: ', 'appthemes') . $ad_cat . "\r\n";
    $message .= __('Author: ', 'appthemes') . $ad_author . "\r\n";
    //$message .= __('Description: ', 'appthemes') . $ad_content . "\r\n";
    $message .= __('-----------------') . "\r\n\r\n";
    $message .= __('Preview Ad: ', 'appthemes') . $ad_slug . "\r\n";
    $message .= sprintf(__('Edit Ad: %s', 'appthemes'), $adminurl) . "\r\n\r\n\r\n";

    $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
    $message .= __('ClassiPress', 'appthemes') . "\r\n\r\n";

    // ok let's send the email
    wp_mail($mailto, $subject, $message, $headers);

}


// send new membership notification email to admin
function cp_new_membership_email($oid) {

		$orders = get_user_orders('',$oid);
    if(!empty($orders)){
    	$order_id = get_order_id($orders);
    	$storedOrder = get_option($orders);

    	$user_id = get_order_userid($orders); 
    	$the_user = get_userdata($user_id);


      $membership_order_id = stripslashes($order_id);
      $membership_pack_id = stripslashes($storedOrder['pack_id']);
      $membership_pack_name = stripslashes($storedOrder['pack_name']);
      $membership_user_id = stripslashes($user_id);
      $membership_user_login = stripslashes($the_user->user_login);
      $membership_total_cost = stripslashes($storedOrder['total_cost']);

      $transactionsurl = get_option('siteurl').'/wp-admin/admin.php?page=transactions';
      $activateurl = get_option('siteurl').'/wp-admin/admin.php?page=transactions&action=activateMembership&oid='.$oid;

      $mailto = get_option('admin_email');

      $subject = __('Membership Activation', 'appthemes');
      $headers = 'From: '. __('ClassiPress Admin', 'appthemes') .' <'. get_option('admin_email') .'>' . "\r\n";

      // The blogname option is escaped with esc_html on the way into the database in sanitize_option
      // we want to reverse this for the plain text arena of emails.
      $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

      $message  = __('Dear Admin,', 'appthemes') . "\r\n\r\n";
      $message .= sprintf(__('The following membership has just been purchased on your %s website.', 'appthemes'), $blogname) . "\r\n\r\n";
      $message .= __('Membership Details', 'appthemes') . "\r\n";
      $message .= __('-----------------') . "\r\n";
      $message .= __('Order ID: ', 'appthemes') . $membership_order_id . "\r\n";
      $message .= __('Pack ID: ', 'appthemes') . $membership_pack_id . "\r\n";
      $message .= __('Pack Name: ', 'appthemes') . $membership_pack_name . "\r\n";
      $message .= __('User ID: ', 'appthemes') . $membership_user_id . "\r\n";
      $message .= __('User Login: ', 'appthemes') . $membership_user_login . "\r\n";
      $message .= __('Total Cost: ', 'appthemes') . $membership_total_cost . " (" . get_option('cp_curr_pay_type') . ")\r\n";
      $message .= __('-----------------') . "\r\n\r\n";
      $message .= __('Show transactions: ', 'appthemes') . $transactionsurl . "\r\n";
      $message .= __('Activate membership: ', 'appthemes') . $activateurl . "\r\n\r\n\r\n";
      $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
      $message .= __('ClassiPress', 'appthemes') . "\r\n\r\n";

      // ok let's send the email
      wp_mail($mailto, $subject, $message, $headers);

    }

}


// send new membership notification email to buyer when purchased by bank transfer
function cp_bank_owner_new_membership_email($oid) {

		$orders = get_user_orders('',$oid);
    if(!empty($orders)){
    	$order_id = get_order_id($orders);
    	$storedOrder = get_option($orders);

    	$user_id = get_order_userid($orders); 
    	$the_user = get_userdata($user_id);


      $membership_order_id = stripslashes($order_id);
      $membership_pack_id = stripslashes($storedOrder['pack_id']);
      $membership_pack_name = stripslashes($storedOrder['pack_name']);
      $membership_user_email = stripslashes($the_user->user_email);
      $membership_user_login = stripslashes(cp_get_user_name($user_id));
      $membership_total_cost = stripslashes($storedOrder['total_cost']);

      $siteurl = trailingslashit(get_option('home'));

      // The blogname option is escaped with esc_html on the way into the database in sanitize_option
      // we want to reverse this for the plain text arena of emails.
      $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

      $mailto = $membership_user_email;

      $subject = sprintf(__('Your Membership Purchase on %s','appthemes'), $blogname);
      $headers = 'From: '. sprintf(__('%s Admin', 'appthemes'), $blogname) .' <'. get_option('admin_email') .'>' . "\r\n";

      $message  = sprintf(__('Hi %s,', 'appthemes'), $membership_user_login) . "\r\n\r\n";
      $message .= __('Thank you for your membership order! Your membership has been submitted and will not be valid on our site until you pay for it.', 'appthemes') . "\r\n\r\n";
      $message .= __('Please include the following details when sending the bank transfer. Once your transfer has been verified, we will then activate your membership.') . "\r\n\r\n";
      $message .= __('Details for Payment', 'appthemes') . "\r\n";
      $message .= __('-----------------') . "\r\n";
      $message .= __('Transaction ID: ', 'appthemes') . $membership_pack_name . "\r\n";
      $message .= __('Reference #: ', 'appthemes') . $oid . "\r\n";
      $message .= __('Total Amount: ', 'appthemes') . $membership_total_cost . " (" . get_option('cp_curr_pay_type') . ")\r\n";
      $message .= __('-----------------') . "\r\n\r\n";
    
      $message .= __('Bank Transfer Instructions', 'appthemes') . "\r\n";
      $message .= __('-----------------') . "\r\n";
      $message .= strip_tags( appthemes_br2nl( stripslashes( get_option('cp_bank_instructions') ) ) ) . "\r\n";
      $message .= __('-----------------') . "\r\n\r\n";
    
      $message .= __('For questions or problems, please contact us directly at', 'appthemes') . " " . get_option('admin_email') . "\r\n\r\n\r\n\r\n";
      $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
      $message .= sprintf(__('Your %s Team', 'appthemes'), $blogname) . "\r\n";
      $message .= $siteurl . "\r\n\r\n\r\n\r\n";

      // ok let's send the email
      wp_mail($mailto, $subject, $message, $headers);
      
    }

}


// send notification email to buyer when membership was activated
function cp_owner_activated_membership_email($user, $order) {
  global $app_abbr;

	if (get_option($app_abbr.'_membership_activated_email_owner') == 'yes') {	
    $membership_user_email = stripslashes($user->user_email);
    $membership_user_login = stripslashes(cp_get_user_name($user->ID));
    $membership_pack_name = stripslashes($order['pack_name']);

    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $siteurl = trailingslashit(get_option('home'));

    $mailto = $membership_user_email;

    $subject = __('Your membership has been activated', 'appthemes');
    $headers = 'From: '. sprintf(__('%s Admin', 'appthemes'), $blogname) .' <'. get_option('admin_email') .'>' . "\r\n";

    $message  = sprintf(__('Hi %s,', 'appthemes'), $membership_user_login) . "\r\n\r\n";
    $message .= sprintf(__('Your membership, "%s" has been activated on our site, and You are ready to post ad listings.', 'appthemes'), $membership_pack_name) . "\r\n\r\n";
    $message .= __('You can post your ad by clicking on the following link:', 'appthemes') . "\r\n";
    $message .= CP_ADD_NEW_URL . "\r\n\r\n\r\n\r\n";
    $message .= __('Regards,', 'appthemes') . "\r\n\r\n";
    $message .= sprintf(__('Your %s Team', 'appthemes'), $blogname) . "\r\n";
    $message .= $siteurl . "\r\n\r\n\r\n\r\n";

    // ok let's send the email
    wp_mail($mailto, $subject, $message, $headers);
  }

}



?>