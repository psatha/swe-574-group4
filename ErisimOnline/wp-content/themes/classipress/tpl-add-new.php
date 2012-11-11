<?php
/*
Template Name: Add New Listing
*/

// if not logged in, redirect to login page
auth_redirect_login();

// grabs the user info and puts into vars
global $current_user, $app_abbr;
$current_user = wp_get_current_user(); 

// don't cache the headers
// nocache_headers();

// this is needed for IE to work with the go back button
header("Cache-control: private");

//if membership required to post, and no membership is active on logged in user, redirect to membership page
if(isset($current_user->active_membership_pack)) $current_membership = get_pack($current_user->active_membership_pack);
else $current_membership = false;

if(isset($_POST['cat'])) $current_requirement = get_membership_requirement($_POST['cat']);
if(get_option($app_abbr.'_required_membership_type') == 'all') $current_requirement = 'all';

//if requirement is found, but required is not in the users pack type, fail and redirect to membership page.
if ( isset($current_requirement) && $current_requirement && get_option($app_abbr.'_enable_membership_packs') == 'yes') {
	//if no membership, or if membership but not a membership that satisfies required memberships, or if membership expired
	if(!isset($current_membership->pack_type) || !isset($current_user->membership_expires) || (isset($current_membership->pack_type) && !stristr($current_membership->pack_type, 'required')) || (appthemes_days_between_dates($current_user->membership_expires) < 0) ) {
		wp_redirect( CP_MEMBERSHIP_PURCHASE_URL.'?membership=required&cat='.$current_requirement );
		exit;
	}
}


// needed for file uploading to work
if (defined('ABSPATH')) {
    require_once (ABSPATH . 'wp-admin/includes/file.php');
    require_once (ABSPATH . 'wp-admin/includes/image.php');
} else {
    require_once ('../wp-admin/includes/file.php');
    require_once ('../wp-admin/includes/image.php');
}

// load up the validate and tinymce scripts
add_action('wp_print_scripts', 'cp_load_form_scripts');

//load ajax child categories scripts
add_action('wp_head', 'cp_ajax_addnew_js_header' );


// include all the functions needed for this form
include_once (TEMPLATEPATH . '/includes/forms/step-functions.php');
?>


<?php get_header(); ?>

<script type='text/javascript'>
// <![CDATA[
jQuery(document).ready(function(){

	/* setup the form validation */
	jQuery('#mainform').validate({
		errorClass: 'invalid',
		errorPlacement: function(error, element) {
			if (element.attr('type') == 'checkbox' || element.attr('type') == 'radio') {
				element.closest('ol').after(error);
			} else {
				offset = element.offset();
				error.insertBefore(element)
				error.addClass('message');  // add a class to the wrapper
				error.css('position', 'absolute');
				error.css('left', offset.left + element.outerWidth());
				error.css('top', offset.top);
			}	
        }

	});

	/* setup the tooltip */
    jQuery("#mainform a").easyTooltip();

});


/* General Trim Function  */
function trim (str) {
    var	str = str.replace(/^\s\s*/, ''),
            ws = /\s/,
            i = str.length;
    while (ws.test(str.charAt(--i)));
    return str.slice(0, i + 1);
}

/* Used for enabling the image for uploads */
function enableNextImage($a, $i) {
    jQuery('#upload'+$i).removeAttr('disabled');
}

// ]]>
</script>

<!-- CONTENT -->
  <div class="content">

    <div class="content_botbg">

      <div class="content_res">

        <!-- full block -->
        <div class="shadowblock_out">

          <div class="shadowblock">


            <?php

            // check and make sure the form was submitted from step1 and the session value exists
            if(isset($_POST['step1'])) {

                include_once(TEMPLATEPATH . '/includes/forms/step2.php');

            } elseif(isset($_POST['step2'])) {

                include_once(TEMPLATEPATH . '/includes/forms/step3.php');

            } else {

                // create a unique ID for this new ad order
                // uniqid requires a param for php 4.3 or earlier. added for 3.0.1
                $order_id  = uniqid(rand(10,1000), false);
                include_once(TEMPLATEPATH . '/includes/forms/step1.php');

            }
            ?>   
  

            </div><!-- /shadowblock -->

        </div><!-- /shadowblock_out -->

        <div class="clr"></div>

      </div><!-- /content_res -->

    </div><!-- /content_botbg -->

  </div><!-- /content -->
	
   
<?php get_footer(); ?>

