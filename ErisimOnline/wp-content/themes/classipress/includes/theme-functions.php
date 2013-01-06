<?php
/**
 * ClassiPress core theme functions
 * This file is the backbone and includes all the core functions
 * Modifying this will void your warranty and could cause
 * problems with your instance of CP. Proceed at your own risk!
 *
 * @package ClassiPress
 * @author AppThemes
 *
 */

/**
 * The theme custom post type constant
 *
 * Override these values by defining
 * your own in functions.php
 *
 */
if (!defined('APP_POST_TYPE'))
    define('APP_POST_TYPE', 'ad_listing');

if (!defined('APP_TAX_CAT'))
    define('APP_TAX_CAT', 'ad_cat');

if (!defined('APP_TAX_TAG'))
    define('APP_TAX_TAG', 'ad_tag');


// legacy classipress path variables
$upload_dir = wp_upload_dir();
define('UPLOADS_FOLDER', trailingslashit('classipress'));
define('CP_UPLOAD_DIR', trailingslashit($upload_dir['basedir']) . UPLOADS_FOLDER);

// activate support for .mo localization files
appthemes_load_textdomain('appthemes');

// set global path variables
define('CP_DASHBOARD_URL', get_bloginfo('url').'/'.get_option($app_abbr.'_dashboard_url').'/');
define('CP_PROFILE_URL', get_bloginfo('url').'/'.get_option($app_abbr.'_profile_url').'/');
define('CP_EDIT_URL', get_bloginfo('url').'/'.get_option($app_abbr.'_edit_item_url').'/');
define('CP_ADD_NEW_URL', get_bloginfo('url').'/'.get_option($app_abbr.'_add_new_url').'/');
define('CP_ADD_NEW_CONFIRM_URL', get_bloginfo('url').'/'.get_option($app_abbr.'_add_new_confirm_url').'/');
define('CP_MEMBERSHIP_PURCHASE_URL', get_bloginfo('url').'/'.get_option($app_abbr.'_membership_purchase_url').'/');
define('CP_MEMBERSHIP_PURCHASE_CONFIRM_URL', get_bloginfo('url').'/'.get_option($app_abbr.'_membership_purchase_confirm_url').'/');
// define('CP_AUTHOR_PATH', get_bloginfo('url').'/'.get_option($app_abbr.'_author_url').'/'); // deprecated since 3.0.5
// define('CP_BLOG_URL', cp_detect_blog_path()); // deprecated since 3.0.5
define('FAVICON', get_bloginfo('template_directory').'/images/favicon.ico');
define('THE_POSITION', 3);

// define the db tables we use
$app_db_tables = array($app_abbr.'_ad_fields', $app_abbr.'_ad_forms', $app_abbr.'_ad_geocodes', $app_abbr.'_ad_meta', $app_abbr.'_ad_packs', $app_abbr.'_ad_pop_daily', $app_abbr.'_ad_pop_total', $app_abbr.'_coupons', $app_abbr.'_order_info');

// register the db tables
foreach ( $app_db_tables as $app_db_table )
		scb_register_table($app_db_table);



// include all the core files
include_once(TEMPLATEPATH.'/includes/theme-hooks.php');
include_once(TEMPLATEPATH.'/includes/theme-cron.php');
include_once(TEMPLATEPATH.'/includes/theme-enqueue.php');
include_once(TEMPLATEPATH.'/includes/appthemes-functions.php');
include_once(TEMPLATEPATH.'/includes/theme-actions.php');
include_once(TEMPLATEPATH.'/includes/theme-widgets.php');
include_once(TEMPLATEPATH.'/includes/theme-sidebars.php');
include_once(TEMPLATEPATH.'/includes/theme-comments.php');
include_once(TEMPLATEPATH.'/includes/theme-profile.php');
include_once(TEMPLATEPATH.'/includes/theme-security.php');
include_once(TEMPLATEPATH.'/includes/theme-footer.php');
include_once(TEMPLATEPATH.'/includes/theme-header.php');
include_once(TEMPLATEPATH.'/includes/theme-emails.php');
include_once(TEMPLATEPATH.'/includes/theme-stats.php');
include_once(TEMPLATEPATH.'/includes/theme-refine.php');
include_once(TEMPLATEPATH.'/includes/theme-deprecated.php');

// include the new custom post type and taxonomy declarations.
// must be included on all pages to work with site functions
include_once(TEMPLATEPATH.'/includes/admin/admin-post-types.php');


// front-end includes
if ( !is_admin() ) :
    include_once(TEMPLATEPATH.'/includes/theme-login.php');
    include_once(TEMPLATEPATH.'/includes/forms/login/login-form.php');
    include_once(TEMPLATEPATH.'/includes/forms/login/login-process.php');
    include_once(TEMPLATEPATH.'/includes/forms/register/register-form.php');
    include_once(TEMPLATEPATH.'/includes/forms/register/register-process.php');
    include_once(TEMPLATEPATH.'/includes/forms/forgot-password/forgot-password-form.php');
endif;

// admin-only functions
if ( is_admin() ) :
	include_once(TEMPLATEPATH.'/includes/admin/install-script.php'); // needs to be above admin-options otherwise install/upgrade script doesn't work correctly
    include_once(TEMPLATEPATH.'/includes/admin/admin-enqueue.php');
    include_once(TEMPLATEPATH.'/includes/admin/admin-options.php');
    include_once(TEMPLATEPATH.'/includes/admin/write-panel.php');
endif;


if (file_exists(TEMPLATEPATH.'/includes/gateways/paypal/ipn.php'))
    require_once (TEMPLATEPATH.'/includes/gateways/paypal/ipn.php');

// add query var for search functions
function cp_add_query_vars() {
	global $wp;
	$wp->add_query_var( 'scat' );
}
add_filter('init', 'cp_add_query_vars');

// localized text for the theme-scripts.js file
function cp_theme_scripts_localization() {

	wp_localize_script( 'theme-scripts', 'theme_scripts_loc', array(
		'appTaxTag' => APP_TAX_TAG,
	) );

}
add_filter( 'wp_print_scripts', 'cp_theme_scripts_localization' );



// admin var for the admin-scripts.js file
// to set the active tab after options saved
function cp_theme_scripts_admin() {

    if ( !IS_ADMIN() ) return;

    if ( !isset($_POST['setTabIndex']) )
        $setTabIndex = 0;
    else
        $setTabIndex = $_POST['setTabIndex'];

	wp_localize_script( 'admin-scripts', 'theme_scripts_admin', array(
		'setTabIndex' => $setTabIndex,
	) );

}
add_filter( 'wp_print_scripts', 'cp_theme_scripts_admin' );


if ( !function_exists('cp_getChildrenCategories') ) :
function cp_getChildrenCategories() {
	$parentCat = $_POST['catID'];
	$result = '';
	if($parentCat < 1) die($result);
	//$result .= '<!-- Looking for child categories for category ID: '.$parentCat.' -->'.PHP_EOL;

	if(get_categories('taxonomy='.APP_TAX_CAT.'&child_of='.$parentCat.'&hide_empty=0')) {
		if (get_option('cp_price_scheme') == 'category' && get_option('cp_enable_paypal') == 'yes'
	 	&& get_option('cp_ad_parent_posting') != 'no') {
			$result .= cp_dropdown_categories_prices('show_option_none='.__('Select one','appthemes').'&class=dropdownlist&orderby=name&order=ASC&hide_empty=0&hierarchical=1&taxonomy='.APP_TAX_CAT.'&depth=1&echo=0&child_of='.$parentCat);
		}
		else {
			$result .= wp_dropdown_categories('show_option_none='.__('Select one','appthemes').'&class=dropdownlist&orderby=name&order=ASC&hide_empty=0&hierarchical=1&taxonomy='.APP_TAX_CAT.'&depth=1&echo=0&child_of='.$parentCat)."\n".'<div style="clear:both;">';
		}
	}//end if child categories are found

	//returning empty html response tells our javascript that it failed to find child categories
	else {
		die('');
	}

	//return the result to the ajax post
	die($result);
}
endif;

// add AJAX functions
add_action( 'wp_ajax_nopriv_ajax-tag-search-front', 'cp_suggest' );
add_action( 'wp_ajax_ajax-tag-search-front', 'cp_suggest' );
add_action( 'wp_ajax_nopriv_cp_getChildrenCategories', 'cp_getChildrenCategories'); //keep for people who allow post before registration
add_action( 'wp_ajax_cp_getChildrenCategories', 'cp_getChildrenCategories');

// display the login message in the header
if (!function_exists('cp_login_head')) {
	function cp_login_head() {

		if (is_user_logged_in()) :
			global $current_user;
			$current_user = wp_get_current_user();
			$display_user_name = cp_get_user_name();
			$logout_url = cp_logout_url();
			?>
			<?php _e('Welcome,','appthemes'); ?> <strong><?php echo $display_user_name; ?></strong> [ <a href="<?php echo CP_DASHBOARD_URL ?>"><?php _e('My Dashboard','appthemes'); ?></a> | <a href="<?php echo $logout_url; ?>"><?php _e('Log out','appthemes'); ?></a> ]&nbsp;
		<?php else : ?>
			<?php _e('Welcome,','appthemes'); ?> <strong><?php _e('visitor!','appthemes'); ?></strong> [ <a href="<?php echo get_option('siteurl'); ?>/login.php?action=register"><?php _e('Register','appthemes'); ?></a> | <a href="<?php echo get_option('siteurl'); ?>/login.php"><?php _e('Login','appthemes'); ?></a> ]&nbsp;
		<?php endif;

	}
}

// return user name depend of account type
function cp_get_user_name($user = false) {
	global $current_user;

	if (!$user && is_object($current_user))
		$user = $current_user;
	else if (is_numeric($user))
		$user = get_userdata($user);

	if (is_object($user)) {

		if ( 'fb-' == substr( $user->user_login, 0, 3 ) )
			$display_user_name = $user->display_name;
		else
			$display_user_name = $user->user_login;

		return $display_user_name;

	} else {
		return false;
	}
}

// return logout url depend of login type
function cp_logout_url( $url = '' ) {
  global $app_abbr;

  if(!$url)
    $url = home_url();

  if( is_user_logged_in() ) :

    return wp_logout_url($url);

  else :
    return false;
  endif;

}

// correct logout url in admin bar
function cp_admin_bar_render() {
  global $wp_admin_bar;

  if( is_user_logged_in() ) :
    $wp_admin_bar->remove_menu('logout');
  	$wp_admin_bar->add_menu( array(
  		'parent' => 'user-actions',
  		'id'     => 'logout',
  		'title'  => __('Log out','appthemes'),
  		'href'   => cp_logout_url(),
  	) );
  endif;

}
add_action( 'wp_before_admin_bar_render', 'cp_admin_bar_render' );

// dont show any posts that are sub cats of the blog cat
function cp_post_in_desc_cat($cats, $_post = null) {
    foreach ( (array) $cats as $cat ) {
        // get_term_children() accepts integer ID only
        $descendants = get_term_children((int) $cat, 'category');
        if ($descendants && in_category($descendants, $_post))
            return true;
    }
    return false;
}

// get the blog category id
function cp_get_blog_catid() {
    $blogcatid = get_option('cp_blog_cat');

    // set to default cat id if option is blank
    if (empty($blogcatid))
        $blogcatid = 1;

    return $blogcatid;
}

// get the blog id and all blog sub cat ids so we can filter them out of ads
function cp_get_blog_cat_ids() {
    $catid = get_option('cp_blog_cat');
    $output = array();

    // make sure the blog cat id is set to something
    if(!($catid))
        $catid = 1;

    // put the catid into an array
    $output[] = $catid;

    // get all the sub cats of catid and also put them into the array
    $descendants = get_term_children((int) $catid, 'category');

    foreach($descendants as $key => $value) {
        $output[] = $value;
    }

    // spit out the array and separate each value with a comma
    $allcats = trim(join(',', $output));

    return $allcats;
}

// same function as above but give us the ids in an array. needed on home page for filtering out blog posts
function cp_get_blog_cat_ids_array() {
    $catid = get_option('cp_blog_cat');
    $output = array();

    // make sure the blog cat id is set to something
    if(!($catid))
        $catid = 1;

    // put the catid into an array
    $output[] = $catid;

    // get all the sub cats of catid and also put them into the array
    $descendants = get_term_children((int) $catid, 'category');

    foreach($descendants as $key => $value) {
        $output[] = $value;
    }

    return $output;
}


// assemble the blog path
// deprecated since 3.0.5
function cp_detect_blog_path() {
    $blogcatid = get_option('cp_blog_cat');

    if (!empty($blogcatid))
        $blogpath = get_category_link(get_option('cp_blog_cat'));
    else // since the cat id field is blank, we need to guess the path
        $blogpath = cp_cat_base().'/blog/';

    return $blogpath;
}


// find out if the category base has been set. If not, use the default of "category"
// deprecated since 3.0.5
function cp_cat_base() {
    if ((appthemes_clean(get_option('category_base')) == ''))
        $cat_base = trailingslashit(get_bloginfo('url')) . 'category';
    else
        $cat_base = trailingslashit(get_bloginfo('url')) . get_option('category_base');

   return $cat_base;
}


// get category id for search form
function cp_get_search_catid() {
    global $post;

    if(is_tax(APP_TAX_CAT)){ 
      $ad_cat_array = get_term_by( 'slug', get_query_var(APP_TAX_CAT), APP_TAX_CAT, ARRAY_A );
      $catid = $ad_cat_array['term_id'];
    } else if (is_singular(APP_POST_TYPE)) {
      $term = wp_get_object_terms($post->ID, APP_TAX_CAT);
      if($term)
        $catid = $term[0]->term_id;
    } else if (is_search()) {
      $catid = get_query_var('scat');
    }

    if(!isset($catid) || !is_numeric($catid))
      $catid = 0;

    return $catid;
}
  

// get search term for refine results form
function cp_get_search_term() {

    $search_term = get_query_var('s');

    if(empty($search_term))
      $search_term = __('What are you looking for?', 'appthemes');

    return $search_term;
}
  

// processes the entire ad thumbnail logic within the loop
if ( !function_exists('cp_ad_loop_thumbnail') ) :
	function cp_ad_loop_thumbnail() {
		global $post;

		// go see if any images are associated with the ad
    $image_id = cp_get_featured_image_id($post->ID);

		// set the class based on if the hover preview option is set to "yes"
		if ( get_option('cp_ad_image_preview') == 'yes' )
		    $prevclass = 'preview';
		else
		    $prevclass = 'nopreview';

		if ( $image_id > 0 ) {

			// get 75x75 v3.0.5+ image size
			$adthumbarray = wp_get_attachment_image( $image_id, 'ad-thumb' );

			// grab the large image for onhover preview
			$adlargearray = wp_get_attachment_image_src( $image_id, 'large' );
			$img_large_url_raw = $adlargearray[0];

			// must be a v3.0.5+ created ad
			if ( $adthumbarray ) {
				echo '<a href="'. get_permalink() .'" title="'. the_title_attribute('echo=0') .'" class="'.$prevclass.'" rel="'.$img_large_url_raw.'">'.$adthumbarray.'</a>';

			// maybe a v3.0 legacy ad
			} else {
				$adthumblegarray = wp_get_attachment_image_src($image_id, 'thumbnail');
				$img_thumbleg_url_raw = $adthumblegarray[0];
				echo '<a href="'. get_permalink() .'" title="'. the_title_attribute('echo=0') .'" class="'.$prevclass.'" rel="'.$img_large_url_raw.'">'.$adthumblegarray.'</a>';
			}

		// no image so return the placeholder thumbnail
		} else {
			echo '<a href="'. get_permalink() .'" title="'. the_title_attribute('echo=0') .'"><img class="attachment-medium" alt="" title="" src="'. get_bloginfo('template_url') .'/images/no-thumb-75.jpg" /></a>';
		}

	}
endif;


// processes the entire ad thumbnail logic for featured ads
if ( !function_exists('cp_ad_featured_thumbnail') ) :
	function cp_ad_featured_thumbnail() {
		global $post;

		// go see if any images are associated with the ad
    $image_id = cp_get_featured_image_id($post->ID);

		// set the class based on if the hover preview option is set to "yes"
		if (get_option('cp_ad_image_preview') == 'yes')	$prevclass = 'preview'; else $prevclass = 'nopreview';

		if ( $image_id > 0 ) {

			// get 50x50 v3.0.5+ image size
			$adthumbarray = wp_get_attachment_image($image_id, 'sidebar-thumbnail');

			// grab the large image for onhover preview
			$adlargearray = wp_get_attachment_image_src($image_id, 'large');
			$img_large_url_raw = $adlargearray[0];

			// must be a v3.0.5+ created ad
			if($adthumbarray) {
				echo '<a href="'. get_permalink() .'" title="'. the_title_attribute('echo=0') .'" class="'.$prevclass.'" rel="'.$img_large_url_raw.'">'.$adthumbarray.'</a>';

			// maybe a v3.0 legacy ad
			} else {
				$adthumblegarray = wp_get_attachment_image_src($image_id, 'thumbnail');
				$img_thumbleg_url_raw = $adthumblegarray[0];
				echo '<a href="'. get_permalink() .'" title="'. the_title_attribute('echo=0') .'" class="'.$prevclass.'" rel="'.$img_large_url_raw.'">'.$adthumblegarray.'</a>';
			}

		// no image so return the placeholder thumbnail
		} else {
			echo '<a href="'. get_permalink() .'" title="'. the_title_attribute('echo=0') .'"><img class="attachment-sidebar-thumbnail" alt="" title="" src="'. get_bloginfo('template_url') .'/images/no-thumb-sm.jpg" /></a>';
		}

	}
endif;


// display all the custom fields on the single ad page, by default they are placed in the list area
if (!function_exists('cp_get_ad_details')) {
    function cp_get_ad_details($postid, $catid, $locationOption = 'list') {
        global $wpdb;
        $totalRate = 0;
        $totalRateCount = 0;
        //$all_custom_fields = get_post_custom($post->ID);
        // see if there's a custom form first based on catid.
        $fid = cp_get_form_id($catid);

        // if there's no form id it must mean the default form is being used
        if(!($fid)) {

			// get all the custom field labels so we can match the field_name up against the post_meta keys
			$sql = $wpdb->prepare("SELECT field_label, field_name, field_type FROM $wpdb->cp_ad_fields");

        } else {

            // now we should have the formid so show the form layout based on the category selected
            $sql = $wpdb->prepare("SELECT f.field_label, f.field_name, f.field_type, f.field_max_value, f.field_min_value, m.field_pos "
                     . "FROM $wpdb->cp_ad_fields f "
                     . "INNER JOIN $wpdb->cp_ad_meta m "
                     . "ON f.field_id = m.field_id "
                     . "WHERE m.form_id = %s "
                     . "ORDER BY m.field_pos asc",
                     $fid);

        }

        $results = $wpdb->get_results($sql);

        if($results) {
            if($locationOption == 'list') {
                    foreach ($results as $result) :
                    	// EKLEME RATING
                    	if(($result->field_max_value != null && $result->field_max_value != '') && ($result->field_min_value != null && $result->field_min_value != '')){
                    		$maxValue = $result->field_max_value;
                    		$minValue = $result->field_min_value;
                    		$post_meta_val = get_post_meta($postid, $result->field_name, false);
                    		$erisimVal = appthemes_make_clickable(implode(", ", $post_meta_val));
                    		
                    		if($maxValue < $erisimVal){
                    			$totalRate += (($erisimVal / (($maxValue+$minValue)/2))-1)*10;
                    			$totalRateCount++;
                    		}else if ($minValue > $erisimVal){
                    			$totalRate += (((($maxValue+$minValue)/2) / $erisimVal)-1)*10;
                    			$totalRateCount++;
                    		}
                    		//echo round($totalRate,0).'-'.$totalRateCount;
                    	}
                    	//
                        // now grab all ad fields and print out the field label and value
                        $post_meta_val = get_post_meta($postid, $result->field_name, true);
                        if (!empty($post_meta_val))
                            if($result->field_type == "checkbox"){
                                $post_meta_val = get_post_meta($postid, $result->field_name, false);
                                echo '<li id="'. $result->field_name .'"><span>' . esc_html( translate( $result->field_label, 'appthemes' ) ) . ':</span> ' . appthemes_make_clickable(implode(", ", $post_meta_val)) .'</li>'; // make_clickable is a WP function that auto hyperlinks urls}
                            }elseif($result->field_name != 'cp_price' && $result->field_name != 'cp_currency' && $result->field_type != "text area"){
                                echo '<li id="'. $result->field_name .'"><span>' . esc_html( translate( $result->field_label, 'appthemes' ) ) . ':</span> ' . appthemes_make_clickable($post_meta_val) .'</li>'; // make_clickable is a WP function that auto hyperlinks urls
                            }
                    endforeach;
                }
                elseif($locationOption == 'content')
                {
                    foreach ($results as $result) :
                        // now grab all ad fields and print out the field label and value
                        $post_meta_val = get_post_meta($postid, $result->field_name, true);
                        if (!empty($post_meta_val))
                            if($result->field_name != 'cp_price' && $result->field_name != 'cp_currency' && $result->field_type == 'text area')
                                echo '<div id="'. $result->field_name .'" class="custom-text-area dotted"><h3>' . esc_html( translate( $result->field_label, 'appthemes' ) ) . '</h3>' . appthemes_make_clickable($post_meta_val) .'</div>'; // make_clickable is a WP function that auto hyperlinks urls

                    endforeach;
                }
                else
                {
                        // uncomment for debugging
                        // echo 'Location Option Set: ' . $locationOption;
                }

        } else {

          echo __('No ad details found.', 'appthemes');

        }
        if($totalRate>0){
        	return round(($totalRate/$totalRateCount),0);	
        }else {
        	return 0;
        }
    }
}


// give us the custom form id based on category id passed in
// this is used on the single-default.php page to display the ad fields
function cp_get_form_id($catid) {
    global $wpdb;
    $fid = ''; // set to nothing to make WP notice happy

    // we first need to see if this ad is using a custom form
    // so lets search for a catid match and return the id if found
    $sql = "SELECT ID, form_cats FROM $wpdb->cp_ad_forms WHERE form_status = 'active'";

    $results = $wpdb->get_results($sql);

    if($results) {

        foreach ($results as $result) :

            // put the form_cats into an array
            $catarray = unserialize($result->form_cats);

            // now search the array for the ad catid
            if (in_array($catid, $catarray))
                $fid = $result->ID; // when there's a catid match, grab the form id

        endforeach;

        // kick back the form id
        return $fid;

    }

}


// get the first medium image associated to the ad
// used on the home page, search, category, etc
// deprecated since 3.0.5.2
if (!function_exists('cp_get_image')) {
    function cp_get_image($post_id = '', $size = 'medium', $num = 1) {
        $images = get_posts(array('post_type' => 'attachment', 'numberposts' => $num, 'post_status' => null, 'post_parent' => $post_id, 'order' => 'ASC', 'orderby' => 'ID'));
        if ($images) {
            foreach ($images as $image) {
                $img_check = wp_get_attachment_image($image->ID, $size, $icon = false);
				// legacy since 3.0.5 which now includes image alt text editing
                //$post_title = get_the_title($post_id); // grab the post title so we can include in alt and title for SEO
                //$img_check = preg_replace('/title=\"(.*?)\"/','title="'.$post_title.'"', $img_check);
                //$img_check = preg_replace('/alt=\"(.*?)\"/','alt="'.$post_title.'"', $img_check);
            }
        } else {
           // show the placeholder image
           if(get_option('cp_ad_images') == 'yes') { $img_check = '<img class="attachment-medium" alt="" title="" src="'. get_bloginfo('template_url') .'/images/no-thumb-75.jpg" />'; }
        }
        echo $img_check;
    }
}


// get the main image associated to the ad used on the single page
if (!function_exists('cp_get_image_url')) {
	function cp_get_image_url() {
		global $post, $wpdb;

		// go see if any images are associated with the ad
		$images = get_children( array('post_parent' => $post->ID, 'post_status' => 'inherit', 'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'ID') );

		if ($images) {

			// move over bacon
			$image = array_shift($images);

			// see if this v3.0.5+ image size exists
			//$adthumbarray = wp_get_attachment_image($image->ID, 'medium');
			$adthumbarray = wp_get_attachment_image_src($image->ID, 'medium');
			$img_medium_url_raw = $adthumbarray[0];

			// grab the large image for onhover preview
			$adlargearray = wp_get_attachment_image_src($image->ID, 'large');
			$img_large_url_raw = $adlargearray[0];

			// must be a v3.0.5+ created ad
			if($adthumbarray)
				echo '<a href="'. $img_large_url_raw .'" class="img-main" rel="colorbox"><img src="'. $img_medium_url_raw .'" title="'. the_title_attribute('echo=0') .'" alt="'. the_title_attribute('echo=0') .'"  /></a>';

		// no image so return the placeholder thumbnail
		} else {
			echo '<img class="attachment-medium" alt="" title="" src="'. get_bloginfo('template_url') .'/images/no-thumb.jpg" />';
		}

	}
}


// get the image associated to the ad used in the loop-ad for hover previewing
if (!function_exists('cp_get_image_url_raw')) {
    function cp_get_image_url_raw($post_id = '', $size = 'medium', $class = '', $num = 1) {
        $images = get_posts(array('post_type' => 'attachment', 'numberposts' => $num, 'post_status' => null, 'post_parent' => $post_id, 'order' => 'ASC', 'orderby' => 'ID'));
        if ($images) {
            foreach ($images as $image) {
              $iarray = wp_get_attachment_image_src($image->ID, $size, $icon = false);
              $img_url_raw = $iarray[0];
            }
        } else {
            //if(get_option('cp_ad_images') == 'yes') {$img_url_raw = get_bloginfo('template_url') .'/images/no-thumb.jpg"'; }
        }
        return $img_url_raw;
    }
}


// get the image associated to the ad used on the home page
if (!function_exists('cp_get_image_url_feat')) {
    function cp_get_image_url_feat($post_id = '', $size = 'medium', $class = '', $num = 1) {
        $images = get_posts(array('post_type' => 'attachment', 'numberposts' => $num, 'post_status' => null, 'post_parent' => $post_id, 'order' => 'ASC', 'orderby' => 'ID'));
        if ($images) {
            foreach ($images as $image) {
				$alt = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
                $iarray = wp_get_attachment_image_src($image->ID, $size, $icon = false);
                $img_check = '<img class="'.$class.'" src="'.$iarray[0].'" width="'.$iarray[1].'" height="'.$iarray[2].'" alt="'.$alt.'" title="'.$alt.'" />';
            }
        } else {
            if(get_option('cp_ad_images') == 'yes') { $img_check = '<img class="preview" alt="" title="" src="'. get_bloginfo('template_url') .'/images/no-thumb-sm.jpg" />'; }
        }
        echo $img_check;
    }
}


// get all the small images for the ad and colorbox href
// important and used on the single page
if (!function_exists('cp_get_image_url_single')) {
    function cp_get_image_url_single($post_id = '', $size = 'medium', $title = '', $num = 1) {
        $images = get_posts(array('post_type' => 'attachment', 'numberposts' => $num, 'post_status' => null, 'post_parent' => $post_id, 'order' => 'ASC', 'orderby' => 'ID'));

		// remove the first image since it's already being shown as the main one
		$images = array_slice($images,1,count($images)-1);

		if ($images) {
            $i = 1;
            foreach ($images as $image) {
                $alt = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
                if(empty($alt))
                  $alt = $title . ' - ' . __('Image ', 'appthemes') . $i;
                $iarray = wp_get_attachment_image_src($image->ID, $size, $icon = false);
                $iarraylg = wp_get_attachment_image_src($image->ID, 'large', $icon = false);
                if ($i == 1) $mainpicID = 'id="mainthumb"'; else $mainpicID = '';
                echo '<a href="'.$iarraylg[0].'" id="thumb'.$i.'" class="post-gallery" rel="colorbox" title="'.$title.' - '.__('Image ', 'appthemes').$i.'"><img src="'.$iarray[0].'" alt="'.$alt.'" title="'.$alt.'" width="'.$iarray[1].'" height="'.$iarray[2].'" /></a>';
                $i++;
            }
        }
    }
}


// sets the thumbnail pic on the WP admin post
function cp_set_ad_thumbnail($post_id, $thumbnail_id) {
    $thumbnail_html = wp_get_attachment_image($thumbnail_id, 'thumbnail');
    if (!empty($thumbnail_html)) {
        update_post_meta($post_id, '_thumbnail_id', $thumbnail_id);
        die( _wp_post_thumbnail_html($thumbnail_id));
    }
}


// deletes the thumbnail pic on the WP admin post
function cp_delete_ad_thumbnail($post_id) {
    delete_post_meta($post_id, '_thumbnail_id');
    die(_wp_post_thumbnail_html());
}


// gets just the first raw image url
function cp_get_image_url_OLD($postID, $num=1, $order='ASC', $orderby='menu_order', $mime='image') {
    $images = get_posts(array('post_type' => 'attachment','numberposts' => $num,'post_status' => null,'order' => $order,'orderby' => $orderby,'post_mime_type' => $mime,'post_parent' => $postID));
    if ($images) {
        foreach ($images as $image) {
            $single_url = wp_get_attachment_url($image->ID, false);
        }
    }
    echo $single_url;
}


// get the uploaded file extension and make sure it's an image
function cp_file_is_image($path) {
    $info = @getimagesize($path);
    if (empty($info))
        $result = false;
    elseif (!in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
        $result = false;
    else
        $result = true;

    return apply_filters('cp_file_is_image', $result, $path);
}


// legacy function used on CP 2.9.3 and earlier
// get the ad price and position the currency symbol
function cp_get_price_legacy($postid) {

    if(get_post_meta($postid, 'price', true)) {
        $price_out = get_post_meta($postid, 'price', true);

        // uncomment the line below to change price format
        //$price_out = number_format($price_out, 2, ',', '.');

        if(get_option('cp_curr_symbol_pos') == 'right')
            $price_out = $price_out . get_option($app_abbr.'_curr_pay_type_symbol');
        else
            $price_out = get_option($app_abbr.'_curr_pay_type_symbol') . $price_out;
    } else {
        $price_out = '&nbsp;';
    }

    echo $price_out;

}


// get the ad price and position the currency symbol
if (!function_exists('cp_get_price')) {
    function cp_get_price($postid, $meta_field) {

        if(get_post_meta($postid, $meta_field, true)) {
            $price_out = get_post_meta($postid, $meta_field, true);

            // uncomment the line below to change price format
            //$price_out = number_format($price_out, 2, '.', ',');

            $price_out = cp_pos_currency($price_out, 'ad');

        } else {
            if( get_option('cp_force_zeroprice') == 'yes' )
                $price_out = cp_pos_currency(0, 'ad');
            else
                $price_out = '&nbsp;';
        }

        echo $price_out;
    }
}


// pass in the price and get the position of the currency symbol
function cp_pos_price($numout, $price_type = '') {
    $numout = cp_pos_currency($numout, $price_type);
    echo $numout;
}

// figure out the position of the currency symbol and return it with the price
function cp_pos_currency($price_out, $price_type = '') {
	global $post, $app_abbr;

	$price = $price_out;

	//if its set to the ad type, display the currency symbol option related to ad currency
	if($price_type == 'ad') $curr_symbol = get_option('cp_curr_symbol');
	//if price_type not set use the currency type of the payment gateway currency type
	else $curr_symbol = get_option($app_abbr.'_curr_pay_type_symbol');
	//if ad have custom currency, display it instead of default one
	if($price_type == 'ad' && isset($post) && is_object($post)){
		$custom_curr = get_post_meta($post->ID, $app_abbr.'_currency', true);
		if(!empty($custom_curr))
			$curr_symbol = $custom_curr;
	}

	//possition the currency symbol
	if (get_option('cp_curr_symbol_pos') == 'left')
		$price_out = $curr_symbol . $price_out;
	elseif (get_option('cp_curr_symbol_pos') == 'left_space')
		$price_out = $curr_symbol . '&nbsp;' . $price_out;
	elseif (get_option('cp_curr_symbol_pos') == 'right')
		$price_out = $price_out . $curr_symbol;
	else
		$price_out = $price_out . '&nbsp;' . $curr_symbol;

	return apply_filters('cp_currency_position', $price_out, $price, $curr_symbol, $price_type);
}


// on ad submission form, check images for valid file size and type
function cp_validate_image() {
    $error_msg  = array();
    $max_size = (get_option('cp_max_image_size') * 1024); // 1024 K = 1 MB. convert into bytes so we can compare file size to max size. 1048576 bytes = 1MB.

    while(list($key,$value) = each($_FILES['image']['name'])) {
        $value = strtolower($value); // added for 3.0.1 to force image names to lowercase. some systems throw an error otherwise
        if(!empty($value)) {
            if ($max_size < $_FILES['image']['size'][$key]) {
                $size_diff = number_format(($_FILES['image']['size'][$key] - $max_size)/1024);
                $max_size_fmt = number_format(get_option('cp_max_image_size'));
                $error_msg[] = '<strong>'.$_FILES['image']['name'][$key].'</strong> '. sprintf( __('exceeds the %s KB limit by %s KB. Please go back and upload a smaller image.', 'appthemes'), $max_size_fmt, $size_diff );
            }
            elseif (!cp_file_is_image($_FILES['image']['tmp_name'][$key])) {
                $error_msg[] = '<strong>'.$_FILES['image']['name'][$key].'</strong> '. __('is not a valid image type (.gif, .jpg, .png). Please go back and upload a different image.', 'appthemes');
            }
        }
    }
    return $error_msg;
}


// process each image that's being uploaded
function cp_process_new_image() {
    global $wpdb;
    $postvals = '';

    for ( $i=0; $i < count( $_FILES['image']['tmp_name'] ); $i++ ) {
        if ( !empty($_FILES['image']['tmp_name'][$i]) ) {
            // rename the image to a random number to prevent junk image names from coming in
            $renamed = mt_rand( 1000,1000000 ).".".appthemes_find_ext( $_FILES['image']['name'][$i] );

            //Hack since WP can't handle multiple uploads as of 2.8.5
            $upload = array( 'name' => $renamed,'type' => $_FILES['image']['type'][$i],'tmp_name' => $_FILES['image']['tmp_name'][$i],'error' => $_FILES['image']['error'][$i],'size' => $_FILES['image']['size'][$i] );

            // need to set this in order to send to WP media
            $overrides = array( 'test_form' => false );

            // check and make sure the image has a valid extension and then upload it
            $file = cp_image_upload( $upload );

            if ( $file ) // put all these keys into an array and session so we can associate the image to the post after generating the post id
                $postvals['attachment'][$i] = array( 'post_title' => $renamed,'post_content' => '','post_excerpt' => '','post_mime_type' => $file['type'],'guid' => $file['url'], 'file' => $file['file'] );
        }
    }
    return $postvals;
}


// this ties the uploaded files to the correct ad post and creates the multiple image sizes.
function cp_associate_images($post_id,$file,$print = false) {
	$image_count = count($file);
	if($image_count > 0 && $print) echo __('Your ad images are now being processed...','appthemes').'<br />';
    for ($i=0; $i < count($file);$i++ ) {
        $post_title = esc_attr( get_the_title( $post_id ) );
        $attachment = array( 'post_title' => $post_title, 'post_content' => $file[$i]['post_content'], 'post_excerpt' => $file[$i]['post_excerpt'], 'post_mime_type' => $file[$i]['post_mime_type'], 'guid' => $file[$i]['guid'] );
        $attach_id = wp_insert_attachment( $attachment, $file[$i]['file'], $post_id );

        // create multiple sizes of the uploaded image via WP controls
        wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata($attach_id, $file[$i]['file']) );

		if($print) echo sprintf(__('Image number %1$d of %2$s has been processed.','appthemes'), $i+1, $image_count).'<br />';

        // this only does a specific resize.
        // image_make_intermediate_size($file, $width, $height, $crop=false)
        // $crop Optional, default is false. Whether to crop image to specified height and width or resize.
        //wp_update_attachment_metadata($attach_id, image_make_intermediate_size($file[$i]['file'], 50, 50, true));
        //wp_update_attachment_metadata($attach_id, image_make_intermediate_size($file[$i]['file'], 25, 25, true));
    }
}


// get all the images associated to the ad and display the
// thumbnail with checkboxes for deleting them
// used on the ad edit page
if (!function_exists('cp_get_ad_images')) {
    function cp_get_ad_images($ad_id) {
        $args = array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $ad_id, 'order' => 'ASC', 'orderby' => 'ID');

        // get all the images associated to this ad
        $images = get_posts($args);

        // print_r($images); // for debugging

        // get the total number of images already on this ad
        // we need it to figure out how many upload fields to show
        $imagecount = count($images);

        // make sure we have images associated to the ad
        if ($images) :

            $i = 1;
            $media_dims = '';
            foreach ($images as $image) :

				// go get the width and height fields since they are stored in meta data
				$meta = wp_get_attachment_metadata( $image->ID );
				if (is_array($meta) && array_key_exists('width', $meta) && array_key_exists('height', $meta))
					$media_dims = "<span id='media-dims-".$image->ID."'>{$meta['width']}&nbsp;&times;&nbsp;{$meta['height']}</span> ";
            ?>
				<li class="images">
					<div class="labelwrapper">
                    	<label><?php _e('Image', 'appthemes'); ?> <?php echo $i ?>:</label>
					</div>

					<div class="thumb-wrap-edit">
						<?php echo cp_get_attachment_link($image->ID); ?>
					</div>

					<div class="image-meta">
						<p class="image-delete"><input class="checkbox" type="checkbox" name="image[]" value="<?php echo $image->ID; ?>">&nbsp;<?php _e('Delete Image', 'appthemes') ?></p>
						<p class="image-meta"><strong><?php _e('Upload Date:', 'appthemes') ?></strong> <?php echo mysql2date( get_option('date_format'), $image->post_date); ?></p>
						<p class="image-meta"><strong><?php _e('File Info:', 'appthemes') ?></strong> <?php echo $media_dims ?> <?php echo $image->post_mime_type; ?></p>
					</div>

					<div class="clr"></div>

					<?php // get the alt text and print out the field
						 $alt = get_post_meta($image->ID, '_wp_attachment_image_alt', true); ?>
					<p class="alt-text">
						<div class="labelwrapper">
                        	<label><?php _e('Alt Text:','appthemes') ?></label>
						</div>
						<input type="text" class="text" name="attachments[<?php echo $image->ID; ?>][image_alt]" id="image_alt" value="<?php if(count($alt)) echo esc_attr(stripslashes($alt)); ?>" />
					</p>

					<div class="clr"></div>
				</li>
            <?php
            $i++;
			endforeach;

        endif;

        // returns a count of array keys so we know how many images currently
        // are being used with this ad. this value is needed for cp_ad_edit_image_input_fields()
        return $imagecount;
    }
}


// gets the image link for each ad. used in the edit-ads page template
function cp_get_attachment_link($id = 0, $size = 'thumbnail', $permalink = false, $icon = false, $text = false) {
	$id = intval($id);
	$_post = & get_post( $id );

	// print_r($_post);

	if ( ('attachment' != $_post->post_type) || !$url = wp_get_attachment_url($_post->ID) )
		return __('Missing Attachment', 'appthemes');

	if ( $permalink )
		$url = get_attachment_link($_post->ID);

	$post_title = esc_attr($_post->post_title);

	if ( $text ) {
		$link_text = esc_attr($text);
	} elseif ( ( is_int($size) && $size != 0 ) or ( is_string($size) && $size != 'none' ) or $size != false ) {
		$link_text = wp_get_attachment_image($id, $size, $icon);
	} else {
		$link_text = '';
	}

	if( trim($link_text) == '' )
		$link_text = $_post->post_title;

	return apply_filters( 'cp_get_attachment_link', "<a target='_blank' href='$url' alt='' class='post-gallery' rel='colorbox' title='$post_title'>$link_text</a>", $id, $size, $permalink, $icon, $text );
}


// gives us a count of how many images are associated to an ad
function cp_count_ad_images($ad_id) {
    $args = array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $ad_id, 'order' => 'ASC', 'orderby' => 'ID');

    // get all the images associated to this ad
    $images = get_posts($args);

    // get the total number of images already on this ad
    // we need it to figure out how many upload fields to show
    $imagecount = count($images);

    // returns a count of array keys so we know how many images currently
    // are being used with this ad.
    return $imagecount;
}


// calculates total number of image input upload boxes
// minus the number of existing images
function cp_ad_edit_image_input_fields($imagecount) {
    $disabled = '';

    // get the max number of images allowed option
    $maximages = get_option('cp_num_images');

    // figure out how many image upload fields we need
    $imageboxes = ($maximages - $imagecount);

    // now loop through and print out the upload fields
    for ( $i = 0; $i < $imageboxes; $i++ ) :
		$next = $i + 1;
		if ( $i > 0 ) $disabled = 'disabled="disabled"';
    ?>
        <li>
            <div class="labelwrapper">
				<label><?php _e('Add Image','appthemes') ?>:</label>
			</div>
				<?php echo "<input type=\"file\" name=\"image[]\" id=\"upload$i\" class=\"fileupload\" onchange=\"enableNextImage(this,$next)\" $disabled" . ' />'; ?>
            <div class="clr"></div>
        </li>
    <?php
    endfor;
    ?>

    <p class="small"><?php printf(__('You are allowed %s image(s) per ad.','appthemes'), $maximages) ?> <?php echo get_option('cp_max_image_size') ?><?php _e('KB max file size per image.','appthemes') ?> <?php _e('Check the box next to each image you wish to delete.','appthemes') ?></p>
    <div class="clr"></div>

<?php
}


// make sure it's an image file and then upload it
function cp_image_upload($upload) {
    if ( cp_file_is_image( $upload['tmp_name'] ) ) {
        $overrides = array( 'test_form' => false );
        // move image to the WP defined upload directory and set correct permissions
        $file = wp_handle_upload( $upload, $overrides );
    }
    return $file;
}


// delete the image from WordPress
function cp_delete_image() {
    foreach ( (array) $_POST['image'] as $img_id_del ) {
        $img_del = & get_post($img_id_del);

        if ( $img_del )
            if ( $img_del->post_type == 'attachment' )
                if ( !wp_delete_attachment($img_id_del, true) )
                    wp_die( __('Error in deleting the image.', 'appthemes') );
    }
}

// update the image alt and title text on edit ad page. since v3.0.5
function cp_update_alt_text() {
	foreach ($_POST['attachments'] as $attachment_id => $attachment) :
		if (isset($attachment['image_alt'])) {
			$image_alt = esc_html(get_post_meta($attachment_id, '_wp_attachment_image_alt', true));

			if ($image_alt != esc_html($attachment['image_alt'])) {
				$image_alt = wp_strip_all_tags(esc_html($attachment['image_alt']), true);

        $image_data = & get_post($attachment_id);
          if($image_data):
  				// update the image alt text for based on the id
  				update_post_meta($attachment_id, '_wp_attachment_image_alt', addslashes($image_alt));

  				// update the image title text. it's stored as a post title so it's different to update
  				$post = array();
  				$post['ID'] = $attachment_id;
  				$post['post_title'] = $image_alt;
  				wp_update_post($post);
        endif;
			}
		}
	endforeach;
}


// checks if a user is logged in, if not redirect them to the login page
function auth_redirect_login() {
    $user = wp_get_current_user();
    if ( $user->ID == 0 ) {
        nocache_headers();
        wp_redirect(get_option('siteurl') . '/login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}


// gets the ad tags
function cp_get_the_term_list( $id = 0, $taxonomy, $before = '', $sep = '', $after = '' ) {
    $terms = get_the_terms( $id, $taxonomy );

    if (is_wp_error($terms))
        return $terms;

    if (empty($terms))
        return false;

    foreach ($terms as $term) {
        $link = get_term_link($term, $taxonomy);
        if (is_wp_error($link))
            return $link;
        $term_links[] = $term->name . ', ';
    }

    $term_links = apply_filters( "term_links-$taxonomy", $term_links );

    return $before . join( $sep, $term_links ) . $after;
}


// change ad to draft if it's expired
function cp_has_ad_expired($post_id) {
    global $wpdb;

    // check to see if ad is legacy or not
    if(get_post_meta($post_id, 'expires', true))
        $expire_date = get_post_meta($post_id, 'expires', true);
    else
        $expire_date = get_post_meta($post_id, 'cp_sys_expire_date', true);

    // debugging variables
    // echo date_i18n('m/d/Y H:i:s') . ' <-- current date/time GMT<br/>';
    // echo $expire_date . ' <-- expires date/time<br/>';

    // if current date is past the expires date, change post status to draft
    if (strtotime(date('Y-m-d H:i:s')) > (strtotime($expire_date))) :
        $my_post = array();
        $my_post['ID'] = $post_id;
        $my_post['post_status'] = 'draft';
        wp_update_post($my_post);

        return true;
    endif;
}


// saves the ad on the tpl-edit-item.php page template
function cp_update_listing() {
	// --------EKLEME----------- Cozuldu-Cozulmedi Update-Insert
	if ( !empty($_POST['cp_av_solved']) ){
		$solved = get_post_meta(trim( $_POST['ad_id'] ), 'cp_av_solved', true);
		if($solved != null){
			update_post_meta( trim( $_POST['ad_id'] ), 'cp_av_solved', $_POST['cp_av_solved'] );
		}else{
			add_post_meta( trim( $_POST['ad_id'] ), 'cp_av_solved', $_POST['cp_av_solved'] );
		}
	}
	// ------------------------- 
	
    global $wpdb;
    
    // check to see if html is allowed
    if ( get_option('cp_allow_html') != 'yes' )
        $post_content = appthemes_filter($_POST['post_content']);
    else
        $post_content = $_POST['post_content'];

    // keep only numeric, commas or decimal values
    if ( !empty($_POST['cp_price']) )
        $_POST['cp_price'] = appthemes_clean_price( $_POST['cp_price'] );

    // keep only values and insert/strip commas if needed and put into an array
    if ( !empty($_POST['tags_input']) ) {
        $_POST['tags_input'] = appthemes_clean_tags( $_POST['tags_input'] );
        $new_tags = explode( ',', $_POST['tags_input'] );
	}

    // put all the ad elements into an array
    // these are the minimum required fields for WP (except tags)
    $update_ad                      = array();
    $update_ad['ID']                = trim( $_POST['ad_id'] );
    $update_ad['post_title']        = appthemes_filter( $_POST['post_title'] );
    $update_ad['post_content']      = trim( $post_content );
    //$update_ad['post_category']   = array((int)appthemes_filter($_POST['cat'])); // maybe use later if we decide to let users change categories

    // make sure the WP sanitize_post function doesn't strip out embed & other html
    if ( get_option('cp_allow_html') == 'yes' )
        $update_ad['filter'] = true;

    //print_r($update_ad).' <- new ad array<br>'; // for debugging

    // update the ad and return the ad id
    $post_id = wp_update_post( $update_ad );


    if ( $post_id ) {

		//update post custom taxonomy "ad_tags"
		// keep only values and insert/strip commas if needed and put into an array
		if ( !empty($_POST['tags_input']) ) {
            $_POST['tags_input'] = appthemes_clean_tags( $_POST['tags_input'] );
            $new_tags = explode( ',', $_POST['tags_input'] );
            $settags = wp_set_object_terms( $post_id, $new_tags, APP_TAX_TAG );
            //echo 'Update Tags or Erro:'.print_r($settags, true);
		}

        // assemble the comma separated hidden fields back into an array so we can save them.
        $metafields = explode( ',', $_POST['custom_fields_vals'] );

      	// loop through all custom meta fields and update values
      	foreach ( $metafields as $name ) {
		
      		if ( !isset($_POST[$name]) ) {
            delete_post_meta($post_id, $name);
          } else if ( is_array($_POST[$name]) ) {
        		delete_post_meta($post_id, $name);
            foreach ( $_POST[$name] as $checkbox_value )
              add_post_meta( $post_id, $name, $checkbox_value );
          } else {
        		update_post_meta( $post_id, $name, $_POST[$name] );
          }
          
      	}	


        $errmsg = '<div class="box-yellow"><b>' . __('Your ad has been successfully updated.','appthemes') . '</b> <a href="' . CP_DASHBOARD_URL . '">' . __('Return to my dashboard','appthemes') . '</a></div>';

    } else {
        // the ad wasn't updated so throw an error
        $errmsg = '<div class="box-red"><b>' . __('There was an error trying to update your ad.','appthemes') . '</b></div>';

    }

    return $errmsg;

}


// builds the edit ad form on the tpl-edit-item.php page template
function cp_edit_ad_formbuilder($results, $getad) {
	
	//------------EKLEME-------------- (AV Cozulup cozulmemesine gore update etme)
		$solved = get_post_meta( $getad->ID , 'cp_av_solved', true);
		?>
			<li id="list_cp_av_solved">
					<div class="labelwrapper">
						<label><a href="#" tip="Lutfen Erisim Engelinin cozulup cozulmedigini seciniz!" tabindex="999"><div class="helpico"></div></a>Erisim Engeli Durumu : <span class="colour">*</span></label><br />
                        <label class="invalid" for="cp_av_solved">Bu alanin secilmesi zorunlu!</label>
					</div>
                    <select name="cp_av_solved" id="cp_av_solved" class="dropdownlist required">
                    <?php
					if ($solved != null && $solved == 'yes') { // Eger kayit varsa cozulmus demektir
						?>
						<option style="min-width:177px" selected="yes" value="yes">Cozuldu</option>
						<option style="min-width:177px" value="no">Cozulmedi</option>
						<?php
					}else{ // Eger kayit yoksa cozulmemis demektir
						?>
						<option style="min-width:177px" value="yes">Cozuldu</option>
						<option style="min-width:177px" selected="yes" value="no">Cozulmedi</option>
						<?php
					}
					?>
                    </select>
                    <div class="clr"></div>
                </li>
		<?php
		
        //----------------------------------
        
    global $wpdb;

    // create array before adding custom fields
    $custom_fields_array = array();

    foreach ($results as $result) :

        // get all the custom fields on the post and put into an array
        $custom_field_keys = get_post_custom_keys($getad->ID);

        if(!$custom_field_keys) continue;
            // wp_die('Error: There are no custom fields');

        // we only want key values that match the field_name in the custom field table or core WP fields.
        if (in_array($result->field_name, $custom_field_keys) || ($result->field_name == 'post_content') || ($result->field_name == 'post_title') || ($result->field_name == 'tags_input') || $result->field_type == 'checkbox' ) :

            // add each custom field name to an array so we can save them correctly later
            if ( appthemes_str_starts_with($result->field_name, 'cp_'))
              $custom_fields_array[] = $result->field_name;

            // we found a match so go fetch the custom field value
            $post_meta_val = get_post_meta($getad->ID, $result->field_name, true);

            // now loop through the form builder and make the proper field and display the value
            //************************************** EKLEME **************************************************************
            
            switch($result->field_type) {

            case 'text box':
            ?>
                <li id="list_<?php echo $result->field_name; ?>">
                    <div class="labelwrapper">
                    	<label><?php if ($result->field_tooltip) : ?><a href="#" tip="<?php echo esc_attr( translate( $result->field_tooltip, 'appthemes' ) ); ?>" tabindex="999"><div class="helpico"></div></a><?php endif; ?><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>: <?php if ($result->field_req) echo '<span class="colour">*</span>'; ?></label><br />
                        <label class="invalid" for="<?php echo $result->field_name; if(stristr($result->field_name, 'checkbox')) echo '_list' ?>"><?php _e('This field is required.','appthemes');?></label>
					</div>
                    <input name="<?php echo esc_attr($result->field_name); ?>" id="<?php echo esc_attr($result->field_name); ?>" type="text" class="text<?php if ($result->field_req) echo ' required'; ?>" style="min-width:200px;" value="<?php if ($result->field_name == 'post_title') { echo esc_attr($getad->post_title); } elseif ($result->field_name == 'tags_input') { echo rtrim(trim(cp_get_the_term_list($getad->ID, APP_TAX_TAG)), ','); } else { echo esc_attr($post_meta_val); } ?>" />
                    <div class="clr"></div>
                </li>
            <?php
            break;

            case 'drop-down':
            ?>
				<li id="list_<?php echo esc_attr($result->field_name); ?>">
					<div class="labelwrapper">
						<label><?php if ($result->field_tooltip) : ?><a href="#" tip="<?php echo esc_attr( translate( $result->field_tooltip, 'appthemes' ) ); ?>" tabindex="999"><div class="helpico"></div></a><?php endif; ?><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>: <?php if ($result->field_req) echo '<span class="colour">*</span>'; ?></label><br />
                        <label class="invalid" for="<?php echo esc_attr($result->field_name); if(stristr($result->field_name, 'checkbox')) echo '_list'; ?>"><?php _e('This field is required.', 'appthemes'); ?></label>
					</div>
                    <select name="<?php echo esc_attr($result->field_name); ?>" id="<?php echo esc_attr($result->field_name); ?>" class="dropdownlist<?php if ($result->field_req) echo ' required'; ?>">
					<?php if (!$result->field_req) : ?><option value="">-- <?php _e('Select', 'appthemes') ?> --</option><?php endif; ?>
                    <?php
                    $options = explode(',', $result->field_values);

                    foreach ($options as $option) :
                    ?>

                        <option style="min-width:177px" <?php if ($post_meta_val == trim($option)) echo 'selected="yes"'; ?> value="<?php echo trim(esc_attr($option)); ?>"><?php echo trim(esc_attr($option));?></option>

                    <?php endforeach; ?>

                    </select>
                    <div class="clr"></div>
                </li>

            <?php
            break;

            case 'text area':

            ?>
                <li id="list_<?php echo $result->field_name; ?>">
					<div class="labelwrapper">
                    	<label><?php if ($result->field_tooltip) : ?><a href="#" tip="<?php echo esc_attr( translate( $result->field_tooltip, 'appthemes' ) ); ?>" tabindex="999"><div class="helpico"></div></a><?php endif; ?><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>: <?php if ($result->field_req) echo '<span class="colour">*</span>'; ?></label><br />
                        <label class="invalid" for="<?php echo esc_attr($result->field_name); if (stristr($result->field_name, 'checkbox')) echo '_list' ?>"><?php _e('This field is required.', 'appthemes');?></label>
					</div>
                    <div class="clr"></div>
                    <textarea rows="4" cols="23" class="<?php if ($result->field_req) echo ' required'; ?>" name="<?php echo esc_attr($result->field_name); ?>" id="<?php echo esc_attr($result->field_name); ?>"><?php if ($result->field_name == 'post_content') echo esc_textarea($getad->post_content); else echo esc_textarea($post_meta_val); ?></textarea>
					<div class="clr"></div>

					<?php if (get_option('cp_allow_html') == 'yes') : ?>
						<script type="text/javascript"> <!--
						tinyMCE.execCommand('mceAddControl', false, '<?php echo esc_attr($result->field_name); ?>');
						--></script>
					<?php endif; ?>

                </li>
            <?php
            break;

			case 'radio':
					$options = explode(',', $result->field_values);
					?>
				<li id="list_<?php echo esc_attr($result->field_name); ?>">
					<div class="labelwrapper">
                    	<label><?php if ($result->field_tooltip) : ?><a href="#" tip="<?php echo esc_attr( translate( $result->field_tooltip, 'appthemes' ) ); ?>" tabindex="999"><div class="helpico"></div></a><?php endif; ?><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>: <?php if ($result->field_req) echo '<span class="colour">*</span>'; ?></label>
					</div>

					<ol class="radios">

						<?php if(!$result->field_req): ?>
							<li>
								<input type="radio" name="<?php echo esc_attr($result->field_name); ?>" id="<?php echo esc_attr($result->field_name); ?>" class="radiolist" <?php if( (trim($post_meta_val) == trim($option)) || !$post_meta_val ) { echo 'checked="checked"'; } ?> value="">
								<?php _e('None', 'appthemes'); ?>
							</li>
						<?php
						endif;

						foreach ($options as $option) {
						?>
							<li>
								<input type="radio" name="<?php echo esc_attr($result->field_name); ?>" id="<?php echo esc_attr($result->field_name); ?>" value="<?php echo esc_html($option); ?>" class="radiolist <?php if ($result->field_req) echo 'required'; ?>" <?php if ( trim($post_meta_val) == trim($option) ) echo 'checked="checked"'; ?>>&nbsp;&nbsp;<?php echo esc_html(trim($option)); ?>
							</li> <!-- #radio-button -->
						<?php
						}
						?>

					</ol>

					<div class="clr"></div>
				</li>



			<?php
			break;

			case 'checkbox':
				$options = explode(',', $result->field_values); 
        // fetch the custom field values as array
        $post_meta_val = get_post_meta($getad->ID, $result->field_name, false);
      ?>

				<li id="list_<?php echo esc_attr($result->field_name); ?>">
					<div class="labelwrapper">
						<label><?php if ($result->field_tooltip) : ?><a href="#" tip="<?php echo esc_attr( translate( $result->field_tooltip, 'appthemes' ) ); ?>" tabindex="999"><div class="helpico"></div></a><?php endif; ?><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>: <?php if ($result->field_req) echo '<span class="colour">*</span>' ?></label>
					</div>

					<ol class="checkboxes">

						<?php
						$optionCursor = 1;
						foreach ($options as $option) {
						?>
							<li>
								<input type="checkbox" name="<?php echo esc_attr($result->field_name); ?>[]" id="<?php echo esc_attr($result->field_name); echo '_'.$optionCursor++; ?>" value="<?php echo esc_attr($option); ?>" class="checkboxlist <?php if ($result->field_req) echo 'required'; ?>" <?php if (is_array($post_meta_val) && in_array(trim($option), $post_meta_val)) echo 'checked="checked"'; ?> />&nbsp;&nbsp;&nbsp;<?php echo trim(esc_html($option)); ?>
							</li> <!-- #checkbox -->

						<?php
						}
						?>

					</ol>

					<div class="clr"></div>
				</li>

			<?php
			break;

            }// END SWITCH	
            //}// END ELSE

        endif;

    endforeach;

	// put all the custom field names into an hidden field so we can process them on save
	$custom_fields_vals = implode( ',', $custom_fields_array );
	?>
	
	<input type="hidden" name="custom_fields_vals" value="<?php echo $custom_fields_vals; ?>" />
	<script language="javascript">
						
						cp_country = document.getElementById('cp_country');
						
						if(cp_country){
						
							print_country("cp_country");
							set_country("cp_country",'cp_state',''); // Set Default Country here
							cp_country.setAttribute("onChange", "print_state('cp_state',this.selectedIndex);" );
							
						}
						
		</script>
	
<?php	
}

// shows how much time is left before the ad expires
function cp_timeleft($theTime) {
	$now = strtotime("now");
	$timeLeft = $theTime - $now;

    $days_label = __('days','appthemes');
    $day_label = __('day','appthemes');
    $hours_label = __('hours','appthemes');
    $hour_label = __('hour','appthemes');
    $mins_label = __('mins','appthemes');
    $min_label = __('min','appthemes');
    $secs_label = __('secs','appthemes');
    $r_label = __('remaining','appthemes');
    $expired_label = __('This ad has expired','appthemes');

    if($timeLeft > 0)
    {
    $days = floor($timeLeft/60/60/24);
    $hours = $timeLeft/60/60%24;
    $mins = $timeLeft/60%60;
    $secs = $timeLeft%60;

    if($days == 01) {$d_label=$day_label;} else {$d_label=$days_label;}
    if($hours == 01) {$h_label=$hour_label;} else {$h_label=$hours_label;}
    if($mins == 01) {$m_label=$min_label;} else {$m_label=$mins_label;}

    if($days){$theText = $days . " " . $d_label;
    if($hours){$theText .= ", " .$hours . " " . $h_label;}}
    elseif($hours){$theText = $hours . " " . $h_label;
    if($mins){$theText .= ", " .$mins . " " . $m_label;}}
    elseif($mins){$theText = $mins . " " . $m_label;
    if($secs){$theText .= ", " .$secs . " " . $secs_label;}}
    elseif($secs){$theText = $secs . " " . $secs_label;}}
    else{$theText = $expired_label;}
    return $theText;
}


// Breadcrumb for the top of pages
function cp_breadcrumb() {
	global $app_abbr, $post;

	$delimiter = '&raquo;';
	$currentBefore = '<span class="current">';
	$currentAfter = '</span>';

	if ( !is_home() || !is_front_page() || is_paged() ) :
		$flag = 1;
		echo '<div id="crumbs">';
		echo '<a href="' . get_bloginfo('url') . '">' . __('Home', 'appthemes') . '</a> ' . $delimiter . ' ';

		// figure out what to display
		switch ($flag) :

			case is_tax(APP_TAX_TAG):
				echo $currentBefore . __('Ads tagged with', 'appthemes') .' &#39;' . single_tag_title('', false) . '&#39;' . $currentAfter;
			break;

			case is_tax():
				// get the current ad category
				$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
				// get the current ad category parent id
				$parent = $term->parent;
				// WP doesn't have a function to grab the top-level term id so we need to
				// climb up the tree and create a list of all the ad cat parents
				while ($parent):
					$parents[] = $parent;
					$new_parent = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
					$parent = $new_parent->parent;
				endwhile;

				// if parents are found display them
				if(!empty($parents)):
					// flip the array over so we can print out descending
					$parents = array_reverse($parents);
					// for each parent, create a breadcrumb item
					foreach ($parents as $parent):
						$item = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
						$url = get_term_link( $item->slug, APP_TAX_CAT );
						echo '<a href="'.$url.'">'.$item->name.'</a> ' . $delimiter . ' ';
					endforeach;
				endif;
				echo $currentBefore . $term->name . $currentAfter;
			break;

			case is_singular(APP_POST_TYPE):
				// get the ad category array
				$term = wp_get_object_terms($post->ID, APP_TAX_CAT);
        if(!empty($term)):
  				// get the first ad category parent id
  				$parent = $term[0]->parent;
  				// get the first ad category id and put into array
  				$parents[] = $term[0]->term_id;
  				// WP doesn't have a function to grab the top-level term id so we need to
  				// climb up the tree and create a list of all the ad cat parents
  				while ($parent):
  					$parents[] = $parent;
  					$new_parent = get_term_by( 'id', $parent, APP_TAX_CAT );
  					$parent = $new_parent->parent;
  				endwhile;
  				// if parents are found display them
  				if(!empty($parents)):
  					// flip the array over so we can print out descending
  					$parents = array_reverse($parents);
  					// for each parent, create a breadcrumb item
  					foreach ($parents as $parent):
  						$item = get_term_by( 'id', $parent, APP_TAX_CAT );
  						$url = get_term_link( $item->slug, APP_TAX_CAT );

  						echo '<a href="'.$url.'">'.$item->name.'</a> ' . $delimiter . ' ';
  					endforeach;
  				endif;
        endif;
				echo $currentBefore . the_title() . $currentAfter;
			break;

			case is_single():
				$cat = get_the_category();
				$cat = $cat[0];
				echo get_category_parents($cat, TRUE, " $delimiter ");
				echo $currentBefore . the_title() . $currentAfter;
			break;

			case is_category():
				global $wp_query;
				$cat_obj = $wp_query->get_queried_object();
				$thisCat = $cat_obj->term_id;
				$thisCat = get_category($thisCat);
				$parentCat = get_category($thisCat->parent);
				if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
				echo $currentBefore . single_cat_title() . $currentAfter;
			break;

			case is_page():
				// get the parent page id
				$parent_id  = $post->post_parent;
				$breadcrumbs = array();
				if ($parent_id > 0 ) :
					// now loop through and put all parent pages found above current one in array
					while ($parent_id) {
						$page = get_page($parent_id);
						$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
						$parent_id  = $page->post_parent;
					}
					$breadcrumbs = array_reverse($breadcrumbs);
					foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
				endif;
				echo $currentBefore . the_title() . $currentAfter;
			break;

			case is_search():
				echo $currentBefore . __('Search results for', 'appthemes') .' &#39;' . get_search_query() . '&#39;' . $currentAfter;
			break;

			case is_tag():
				echo $currentBefore . __('Posts tagged with', 'appthemes') .' &#39;' . single_tag_title('', false) . '&#39;' . $currentAfter;
			break;

			case is_author():
				global $author;
				$userdata = get_userdata($author);
				echo $currentBefore . __('About', 'appthemes') .'&nbsp;' . $userdata->display_name . $currentAfter;
			break;

			case is_day():
				echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
				echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
				echo $currentBefore . get_the_time('d') . $currentAfter;
			break;

			case is_month():
				echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
				echo $currentBefore . get_the_time('F') . $currentAfter;
			break;

			case is_year():
				echo $currentBefore . get_the_time('Y') . $currentAfter;
			break;

			case is_archive():
        if( !empty($_GET['sort']) && $_GET['sort'] == 'random' )
  				  echo $currentBefore . __('Random Ads', 'appthemes') . $currentAfter;
        elseif( !empty($_GET['sort']) && $_GET['sort'] == 'popular' )
  				  echo $currentBefore . __('Popular Ads', 'appthemes') . $currentAfter;
				else
  				  echo $currentBefore . __('Latest Ads', 'appthemes') . $currentAfter;
			break;

			case is_404():
				echo $currentBefore . __('Page not found', 'appthemes') . $currentAfter;
			break;

		endswitch;

		if ( get_query_var('paged') ) {
		  if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() || is_archive() || is_tax() ) echo ' (';
			echo __('Page', 'appthemes') . ' ' . get_query_var('paged');
		  if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() || is_archive() || is_tax() ) echo ')';
		}

		echo '</div>';

	endif;

}


// return most popular ads for use in loop
function cp_get_popular_ads(){
    global $wpdb, $wp_query, $cp_has_next_page;

    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
    $posts_per_page = ( get_query_var('posts_per_page') ) ? get_query_var('posts_per_page') : 10;

    $start = ($paged * $posts_per_page) - $posts_per_page;
    $limit = $posts_per_page + 1; // Add an additional entry to test for a next page

    // give us the most popular ads based on page views
    $sql = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS p.* FROM $wpdb->cp_ad_pop_total a "
        . "INNER JOIN $wpdb->posts p ON p.ID = a.postnum "
        . "WHERE postcount > 0 AND post_status = 'publish' AND post_type = %s "
        . "ORDER BY postcount DESC LIMIT %d, %d", APP_POST_TYPE, $start, $limit );

    $count_sql = $wpdb->prepare( "SELECT FOUND_ROWS()" );

    $pageposts = $wpdb->get_results( $sql );
    $pageposts_count = $wpdb->get_var( $count_sql );

    // set found posts and number of pages for correct pagination
    $wp_query->found_posts = $pageposts_count;
    $wp_query->max_num_pages = ceil($pageposts_count/$posts_per_page);

    if(count($pageposts) == ($posts_per_page + 1)){
      	$cp_has_next_page = true;
    }else{
        $cp_has_next_page = false;
    }

    $pageposts = array_slice( $pageposts, 0, $posts_per_page);

    // create cache for post meta
    if( $pageposts ){
		update_meta_cache('post', wp_list_pluck($pageposts, 'ID'));
    }

    return $pageposts;
}


// custom related posts function based on tags
// not being used in 3.0 yet
function cp_related_posts($postID, $width, $height) {
    global $wpdb, $post;
    $output = '';

    if (!get_option('cp_similar_items')) {

// if (!$post_id) { $post_id = $post->ID; }

        $q = "SELECT DISTINCT object_id, post_title, post_content ".
                "FROM $wpdb->term_relationships r, $wpdb->term_taxonomy t, $wpdb->posts p ".
                "WHERE t.term_id IN (".
                "SELECT t.term_id FROM $wpdb->term_relationships r, $wpdb->term_taxonomy t ".
                "WHERE r.term_taxonomy_id = t.term_taxonomy_id ".
                "AND t.taxonomy = 'category' ".
                "AND r.object_id = $postID".
                ") ".
                "AND r.term_taxonomy_id = t.term_taxonomy_id ".
                "AND p.post_status = 'publish' ".
                "AND p.ID = r.object_id ".
                "AND object_id <> $postID ".
                "AND p.post_type = '".APP_POST_TYPE."' ".
                "ORDER BY RAND() LIMIT 5";

        $entries = $wpdb->get_results($q);

//$output .= '<h3>'. __('Similar Items','appthemes') . '</h3>';
        $output .= '<div id="similar-items">';

        if ($entries) {

            $output .= '<ul>';

            foreach ($entries as $post) {
                $output .= '<li class="clearfix">';
                $output .= '<div class="list_ad_img"><img src="'.cp_single_image_raw($post->object_id, $width, $height).'" /></div>';
                $output .= '<span class="list_ad_wrap_wide"><a class="list_ad_link_wide" href="'.get_permalink($post->object_id).'">'. $post->post_title. '</a><br/>';
                $output .= substr(strip_tags($post->post_content), 0, 165).'...</span>';
                $output .= '</li>';
            }

        } else {
            $output .= '<p>' . __('No matches found', 'appthemes') . '</p>';
        }
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}


// show category with price dropdown
if (!function_exists('cp_dropdown_categories_prices')) {
	function cp_dropdown_categories_prices( $args = '' ) {
		$defaults = array( 'show_option_all' => '', 'show_option_none' => '','orderby' => 'ID', 'order' => 'ASC','show_last_update' => 0, 'show_count' => 0,'hide_empty' => 1, 'child_of' => 0,'exclude' => '', 'echo' => 1,'selected' => 0, 'hierarchical' => 0,'name' => 'cat', 'class' => 'postform','depth' => 0, 'tab_index' => 0 );

		$defaults['selected'] = ( is_category() ) ? get_query_var( 'cat' ) : 0;
		$r = wp_parse_args( $args, $defaults );
		$r['include_last_update_time'] = $r['show_last_update'];
		extract( $r );

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		$categories = get_categories( $r );
		$output = '';
		if ( ! empty( $categories ) ) {
			$output = "<select name='$name' id='$name' class='$class' $tab_index_attribute>\n";

			if ( $show_option_all ) {
				$show_option_all = apply_filters( 'list_cats', $show_option_all );
				$selected = ( '0' === strval($r['selected']) ) ? " selected='selected'" : '';
				$output .= "\t<option value='0'$selected>$show_option_all</option>\n";
			}

			if ( $show_option_none ) {
				$show_option_none = apply_filters( 'list_cats', $show_option_none );
				$selected = ( '-1' === strval($r['selected']) ) ? " selected='selected'" : '';
				$output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
			}

			if ( $hierarchical )
				$depth = $r['depth'];  // Walk the full depth.
			else
				$depth = -1; // Flat.

			$output .= cp_category_dropdown_tree( $categories, $depth, $r );
			$output .= "</select>\n";
		}

		$output = apply_filters( 'wp_dropdown_cats', $output );

		if ( $echo )
			echo $output;

		return $output;
	}
}

// needed for the cp_dropdown_categories_prices function
function cp_category_dropdown_tree() {
    $args = func_get_args();
    if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
        $walker = new cp_CategoryDropdown;
    else
        $walker = $args[2]['walker'];
    return call_user_func_array(array( &$walker, 'walk' ), $args );
}

// needed for the cp_category_dropdown_tree function
class cp_CategoryDropdown extends Walker {
    var $tree_type = 'category';
    var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');
    function start_el(&$output, $category, $depth, $args) {
		global $app_abbr;
        $pad = str_repeat('&nbsp;', $depth * 3);
        $cat_name = apply_filters('list_cats', $category->name, $category);
        $output .= "\t<option class=\"level-$depth\" value=\"".$category->term_id."\">";
        $output .= $pad.$cat_name;
        $output .= ' - ' . cp_pos_currency(get_option('cp_cat_price_'.$category->cat_ID)) . '</option>'."\n";
    }
}


// categories list display
function cp_create_categories_list( $location = 'menu' ) {
	global $app_abbr;

	$prefix = $app_abbr . '_cat_' . $location . '_';

	$args['menu_cols'] = ( $location == 'menu' ? 3 : 2 );
	$args['menu_depth'] = get_option($prefix . 'depth');
	$args['menu_sub_num'] = get_option($prefix . 'sub_num');
	$args['cat_parent_count'] = get_option($prefix . 'count');
	$args['cat_child_count'] = get_option($prefix . 'count');
	$args['cat_hide_empty'] = get_option($prefix . 'hide_empty');
	$args['cat_nocatstext'] = true;
	$args['cat_order'] = 'ASC';
	$args['taxonomy'] = APP_TAX_CAT;

	return appthemes_categories_list( $args );
}


// delete transient to refresh cat menu
function cp_edit_term_delete_transient() {
     delete_transient('cp_cat_menu');
}

// runs when categories/tags are edited
add_action('edit_term', 'cp_edit_term_delete_transient');




// If you want to automatically resize youtube videos uncomment the filter
function cp_resize_youtube($content) {
    return str_replace('width="640" height="385"></embed>', 'width="480" height="295"></embed>', $content);
}
//add_filter('the_content', 'cp_resize_youtube', 999);



//get a list of coupons, or details about a single coupon if an Coupon Code is passed
function cp_get_coupons($couponCode = '') {
    global $wpdb;
    $sql = "SELECT * "
    . "FROM $wpdb->cp_coupons ";
    if($couponCode != '')
    $sql .= "WHERE coupon_code='$couponCode' ";
    $sql .= "ORDER BY coupon_id desc";

    $results = $wpdb->get_results($sql);
    return $results;
}

//check coupon code against coupons in the database and return the discount
function cp_check_coupon_discount($couponCode) {
	//stop if no coupon code is passed or passed empty
	if($couponCode == '') return false;

	//get the coupon
	$results = cp_get_coupons($couponCode);

	//stop if result is empty or inactive
	if(!$results) return false;
	if($results[0]->coupon_status != 'active') return false;
	if(($results[0]->coupon_use_count >= $results[0]->coupon_max_use_count) && ($results[0]->coupon_max_use_count != 0)) return false;
	if(strtotime($results[0]->coupon_expire_date) < strtotime(date("Y-m-d"))) return false;
	if(strtotime($results[0]->coupon_start_date) > strtotime(date("Y-m-d"))) return false;

	//if coupon exists and is not inactive then return the discount
	return $results[0];
}

//function uses a coupon code by incrimenting its value in the database
function cp_use_coupon($couponCode) {
	global $wpdb;
        $update =   "UPDATE $wpdb->cp_coupons " .
                    "SET coupon_use_count = coupon_use_count + 1 " .
                    "WHERE coupon_code = '$couponCode'";
	$results = $wpdb->query($update);
}


// ajax auto-complete search
function cp_suggest() {
    global $wpdb;

	$s = $_GET['term']; // is this slashed already?

    if ( isset($_GET['tax']) )
            $taxonomy = sanitize_title($_GET['tax']);
    else
            die('no taxonomy');

    if ( false !== strpos( $s, ',' ) ) {
        $s = explode( ',', $s );
        $s = $s[count( $s ) - 1];
    }
    $s = trim( $s );
    if ( strlen( $s ) < 2 ) {
        die(__('need at least two characters', 'appthemes')); // require 2 chars for matching
	}

	$terms = $wpdb->get_col( "
		SELECT t.slug FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id ".
		"WHERE tt.taxonomy = '$taxonomy' ".
		"AND t.name LIKE (
			'%$s%'
		)" .
		"LIMIT 50"
		);
	if(empty($terms)){
		//$results[0] = {"name":"no results"};
		echo json_encode($terms);
		die;
	}else{
		$i = 0;
		foreach ($terms as $term) {
			$results[$i] = get_term_by( 'slug', $term, $taxonomy );
			$i++;
		}
		echo json_encode($results);
		die;
	}
}


/**
 * Custom ClassiPress search engine to search
 * and include custom fields
 * @global <type> $wpdb
 * @param <type> $join
 * @return <type>
 *
 *
 */



// exclude pages and blog entries from search results if option is set
// not using yet since still using custom where statement below
// since 3.0.5
function appthemes_exclude_search_types($query) {
    if ( $query->is_search ) {

    if ( get_option('cp_search_ex_blog') == 'yes' )
        $query->set('post_type', APP_POST_TYPE);
    else
        $query->set( 'post_type', array( 'post', APP_POST_TYPE ) );

    }
return $query;
}

//if (get_option('cp_search_ex_pages') == 'yes')
//add_filter('pre_get_posts', 'appthemes_exclude_search_types');



// search only ads and not pages
function cp_is_type_page() {
    global $post;
    if ( $post->post_type == 'page' )
        return true;
    else
        return false;
}

// get all custom field names so we can use them for search
function cp_custom_search_fields() {
    global $wpdb;

    $sql = "SELECT field_name FROM $wpdb->cp_ad_fields p WHERE p.field_name LIKE 'cp_%' ";

    $results = $wpdb->get_results( $sql );

    if ( $results ) :
        foreach ($results as $result) {
            // put the fields into an array
            $custom_fields[] = $result->field_name;
        }
    endif;

    return $custom_fields;
}


// search on custom fields
function custom_search_groupby($groupby) {
	global $wpdb, $wp_query;

	$groupby = "$wpdb->posts.ID";

    return $groupby;
}


// search on custom fields
function custom_search_join($join) {
	global $wpdb, $wp_query;

	if ( is_search() && isset($_GET['s']) ) {

		$join  = " INNER JOIN $wpdb->term_relationships AS r ON ($wpdb->posts.ID = r.object_id) ";
		$join .= " INNER JOIN $wpdb->term_taxonomy AS x ON (r.term_taxonomy_id = x.term_taxonomy_id) ";
		$join .= " AND (x.taxonomy = '".APP_TAX_TAG."' OR x.taxonomy = '".APP_TAX_CAT."' OR 1=1) ";


		// if an ad category is selected, limit results to that cat only
		$catid = get_query_var('scat');

		if ( !empty($catid) ) :

			// put the catid into an array
			(array) $include_cats[] = $catid;

			// get all sub cats of catid and put them into the array
			$descendants = get_term_children( (int) $catid, APP_TAX_CAT );

			foreach ( $descendants as $key => $value )
				$include_cats[] = $value;

			// take catids out of the array and separate with commas
			$include_cats = "'" . implode( "', '", $include_cats ) . "'";

			// add the category filter to show anything within this cat or it's children
			$join .= " AND x.term_id IN ($include_cats) ";

		endif; // end category filter


		$join .= " INNER JOIN $wpdb->postmeta AS m ON ($wpdb->posts.ID = m.post_id) ";
		$join .= " INNER JOIN $wpdb->terms AS t ON x.term_id = t.term_id ";

    }

    return $join;
}


// search on custom fields
function custom_search_where($where) {
    global $wpdb, $wp_query;
    $old_where = $where; // intercept the old where statement
    if ( is_search() && isset($_GET['s']) ) {

			if(get_option('cp_search_custom_fields') == 'yes'){
				// get the custom fields to add to search
				$custom_fields = cp_custom_search_fields();
				// enter additional legacy custom fields and ad id field
				$custom_fields_more = array('name', 'price', 'phone', 'location', 'cp_sys_ad_conf_id');
				// now merge the two arrays together
				$customs = array_merge( $custom_fields, $custom_fields_more );
			}

			$query = '';

		$var_q = stripslashes($_GET['s']);
		//empty the s parameter if set to default search text
		if ( __('What are you looking for?','appthemes') == $var_q ) {
			$var_q = '';
		}

        if ( isset($_GET['sentence']) || $var_q == '' ) {
            $search_terms = array($var_q);
        }
        else {
            preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $var_q, $matches);
            $search_terms = array_map(create_function('$a', 'return trim($a, "\\"\'\\n\\r ");'), $matches[0]);
        }

        if (!isset($_GET['exact']) ) $_GET['exact'] = '';

        $n = ( $_GET['exact'] ) ? '' : '%';

        $searchand = '';

        foreach ( (array)$search_terms as $term ) {
            $term = addslashes_gpc($term);

            $query .= "{$searchand}(";
            $query .= "($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
            $query .= " OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
            $query .= " OR ((t.name LIKE '{$n}{$term}{$n}')) OR ((t.slug LIKE '{$n}{$term}{$n}'))";

            if(isset($customs)){
              foreach ( $customs as $custom ) {
                $query .= " OR (";
                $query .= "(m.meta_key = '$custom')";
                $query .= " AND (m.meta_value  LIKE '{$n}{$term}{$n}')";
                $query .= ")";
              }
            }

            $query .= ")";
            $searchand = ' AND ';
        }

        $term = $wpdb->escape($var_q);

        if ( !isset($_GET['sentence']) && count($search_terms) > 1 && $search_terms[0] != $var_q ) {
            $query .= " OR ($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
            $query .= " OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
        }

        if ( !empty($query) ) {

            $where = " AND ({$query}) AND ($wpdb->posts.post_status = 'publish') ";

            // setup the array for post types
            $post_type_array = array();

            // always include the ads post type
            $post_type_array[] = APP_POST_TYPE;

            // check to see if we include blog posts
            if (get_option('cp_search_ex_blog') == 'no')
                $post_type_array[] = 'post';

            // check to see if we include pages
            if (get_option('cp_search_ex_pages') == 'no')
                $post_type_array[] = 'page';

            // build the post type filter sql from the array values
            $post_type_filter = "'" . implode("','",$post_type_array). "'";

            // return the post type sql to complete the where clause
            $where .= " AND ($wpdb->posts.post_type IN ($post_type_filter)) ";

        }
    }

    return( $where );
}

// load filters only on frontend
if(!is_admin()) {
  add_filter('posts_join', 'custom_search_join');
  add_filter('posts_where', 'custom_search_where');
  add_filter('posts_where', 'custom_search_refine_where');
  add_filter('posts_join', 'custom_search_refine_join');
  add_filter('posts_groupby', 'custom_search_groupby');
}



// if an ad is created and doesn't have an expiration date,
// make sure to insert one based on the Ad Listing Period option.
// all ads need an expiration date otherwise they will automatically
// expire. this is common when customers manually create an ad through
// the WP admin new post or when using an automated scrapper script
function cp_check_expire_date($post_id) {
	global $wpdb;

	// we don't want to add the expires date to blog posts
	if ( get_post_type() != APP_POST_TYPE )  {

		// do nothing

	} else {

		// add default expiration date if the expired custom field is blank or empty
		$ad_expire_date = get_post_meta($post_id, 'cp_sys_expire_date', true);
		if ( empty($ad_expire_date) ) :
			$ad_length = get_option('cp_prun_period');
			if ( !$ad_length || !is_numeric($ad_length) ) $ad_length = '365'; // if the prune days is empty, set it to one year
			$ad_expire_date = date( 'm/d/Y H:i:s', strtotime('+' . $ad_length . ' days') ); // don't localize the word 'days'
			add_post_meta( $post_id, 'cp_sys_expire_date', $ad_expire_date, true );
		endif;

	}

}

/**
 * RENEW AD LISTINGS : @SC - Allowing free ads to be relisted, call this
 * function and send the ads post id. We will check to make sure its free
 * and relist the ad for the same duration it
 */
if ( !function_exists('cp_renew_ad_listing') ) :
function cp_renew_ad_listing ( $ad_id ) {
	$listfee = (float)get_post_meta($ad_id, 'cp_sys_total_ad_cost', true);

	// protect against false URL attempts to hack ads into free renewal
	if ( $listfee == 0 )	{
		$ad_length = get_post_meta($ad_id, 'cp_sys_ad_duration', true);
		if ( empty($ad_length) )
			$ad_length = get_option('cp_prun_period');

		if ( !$ad_length || !is_numeric($ad_length) ) $ad_length = '365'; // if the prune days is empty, set it to one year

		// set the ad listing expiration date
		$ad_expire_date = date('m/d/Y H:i:s', strtotime('+' . $ad_length . ' days')); // don't localize the word 'days'

		//now update the expiration date on the ad
		update_post_meta($ad_id, 'cp_sys_expire_date', $ad_expire_date);
		wp_update_post( array('ID' => $ad_id, 'post_date' => date('Y-m-d H:i:s'), 'edit_date' => true) );
		return true;
	}

	//attempt to relist a paid ad
	else {	return false;	}
}
endif;


// delete ad listings together with associated attachments
function cp_delete_ad_listing ($postid) {
  global $wpdb;

	$attachments_query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type='attachment'", $postid);
	$attachments = $wpdb->get_results($attachments_query);

  // delete all associated attachments
  if($attachments)
    foreach($attachments as $attachment)
      wp_delete_attachment( $attachment->ID, true );
  
  // delete post and it's revisions, comments, meta
  if( wp_delete_post( $postid, true ) )
    return true;
  else
    return false;
}


// runs when a post is published, or is edited and status is "published"
add_filter('publish_post', 'cp_check_expire_date', 9, 3);



// creates the charts on the dashboard
function cp_dashboard_charts() {
	global $wpdb, $app_abbr;

	$sql = "SELECT COUNT(post_title) as total, post_date FROM ". $wpdb->posts ." WHERE post_type = '".APP_POST_TYPE."' AND post_date > '" . date('Y-m-d', strtotime('-30 days')) . "' GROUP BY DATE(post_date) DESC";
	$results = $wpdb->get_results($sql);

	$listings = array();

	// put the days and total posts into an array
	foreach ( $results as $result ) {
		$the_day = date( 'Y-m-d', strtotime($result->post_date) );
		$listings[$the_day] = $result->total;
	}

	// setup the last 30 days
	for ( $i = 0; $i < 30; $i++ ) {
		$each_day = date( 'Y-m-d', strtotime('-'. $i .' days') );

		// if there's no day with posts, insert a goose egg
		if ( !in_array( $each_day, array_keys($listings) ) ) $listings[$each_day] = 0;
	}

	// sort the values by date
	ksort( $listings );

	// print_r($listings);

	// Get sales - completed orders with a cost
	$sql = "SELECT SUM(mc_gross) as total, payment_date FROM $wpdb->cp_order_info WHERE payment_status <> 'pending' AND payment_date > '" . date('Y-m-d', strtotime('-30 days')) . "' GROUP BY DATE(payment_date) DESC";
	$results = $wpdb->get_results($sql);

	$sales = array();

	// put the days and total posts into an array
	foreach ( $results as $result ) {
		$the_day = date('Y-m-d', strtotime($result->payment_date));
		$sales[$the_day] = $result->total;
	}

	// setup the last 30 days
	for ( $i = 0; $i < 30; $i++ ) {
		$each_day = date('Y-m-d', strtotime('-'. $i .' days'));

		// if there's no day with posts, insert a goose egg
		if (!in_array($each_day, array_keys($sales))) $sales[$each_day] = 0;
	}

	// sort the values by date
	ksort($sales);
?>

<div id="placeholder"></div>

<script language="javascript" type="text/javascript">
// <![CDATA[
jQuery(function () {

    var posts = [
		<?php
		foreach ($listings as $day => $value) {
			$sdate = strtotime($day);
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			//$theoutput[] = $newoutput;
			echo $newoutput;
		}
		?>
	];

	var sales = [
		<?php
		foreach ($sales as $day => $value) {
			$sdate = strtotime($day);
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			//$theoutput[] = $newoutput;
			echo $newoutput;
		}
		?>
	];


	var placeholder = jQuery("#placeholder");

	var output = [
		{
			data: posts,
			label: "<?php _e('New Ad Listings', 'appthemes'); ?>",
			symbol: ''
		},
		{
			data: sales,
			label: "<?php _e('Total Sales', 'appthemes'); ?>",
			symbol: '<?php echo get_option($app_abbr.'_curr_pay_type_symbol'); ?>',
			yaxis: 2
		}
	];

	var options = {
       series: {
		   lines: { show: true },
		   points: { show: true }
	   },
	   grid: {
		   tickColor:'#f4f4f4',
		   hoverable: true,
		   clickable: true,
		   borderColor: '#f4f4f4',
		   backgroundColor:'#FFFFFF'
	   },
       xaxis: { mode: 'time',
				timeformat: "%m/%d"
	   },
	   yaxis: { min: 0 },
	   y2axis: { min: 0, tickFormatter: function (v, axis) { return "<?php echo get_option($app_abbr.'_curr_pay_type_symbol'); ?>" + v.toFixed(axis.tickDecimals) }},
	   legend: { position: 'nw' }
    };

	jQuery.plot(placeholder, output, options);

	// reload the plot when browser window gets resized
	jQuery(window).resize(function() {
		jQuery.plot(placeholder, output, options);
	});

	function showChartTooltip(x, y, contents) {
		jQuery('<div id="charttooltip">' + contents + '</div>').css( {
		position: 'absolute',
		display: 'none',
		top: y + 5,
		left: x + 5,
		opacity: 1
		}).appendTo("body").fadeIn(200);
	}

	var previousPoint = null;
	jQuery("#placeholder").bind("plothover", function (event, pos, item) {
		jQuery("#x").text(pos.x.toFixed(2));
		jQuery("#y").text(pos.y.toFixed(2));
		if (item) {
			if (previousPoint != item.datapoint) {
                previousPoint = item.datapoint;

				jQuery("#charttooltip").remove();
				var x = new Date(item.datapoint[0]), y = item.datapoint[1];
				var xday = x.getDate(), xmonth = x.getMonth()+1; // jan = 0 so we need to offset month
				showChartTooltip(item.pageX, item.pageY, xmonth + "/" + xday + " - <b>" + item.series.symbol + y + "</b> " + item.series.label);
			}
		} else {
			jQuery("#charttooltip").remove();
			previousPoint = null;
		}
	});
});
// ]]>
</script>

<?php // print_r($theoutput); ?>

<?php
}


// activate theme support items
if (function_exists('add_theme_support')) { // added in 2.9

	// this theme uses post thumbnails
	add_theme_support('post-thumbnails', array('post', 'page'));
	set_post_thumbnail_size(100, 100); // normal post thumbnails

	// add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );
}

// setup different image sizes
if ( function_exists( 'add_image_size' ) ) {
	add_image_size('blog-thumbnail', 150, 150); // blog post thumbnail size, box crop mode
	add_image_size('sidebar-thumbnail', 50, 50, true); // sidebar blog thumbnail size, box crop mode

	// create special sizes for the ads
	add_image_size('ad-thumb', 75, 75, true);
	add_image_size('ad-small', 100, 100, true);
	add_image_size('ad-medium', 250, 250, true);
	//add_image_size('ad-large', 500, 500);
}

// Set the content width based on the theme's design and stylesheet.
// Used to set the width of images and content. Should be equal to the width the theme
// is designed for, generally via the style.css stylesheet.
if (!isset($content_width))
	$content_width = 500;


// This theme supports native menu options, and uses wp_nav_menu() in one location for top navigation.
function appthemes_register_menus() {
	register_nav_menus(array(
		'primary' => __( 'Primary Navigation', 'appthemes'),
		'theme_dashboard' => __( 'Theme Dashboard', 'appthemes')
	));
}
add_action( 'init', 'appthemes_register_menus' );

//default navigation menu to display if custom menu is not defined
function appthemes_default_menu ($args = array()) {
	global $app_abbr;
	$excludePages = get_option('cp_excluded_pages');
	if(get_option($app_abbr.'_enable_blog') == 'no')
		$excludePages .= ','.get_option($app_abbr.'_blog_page_id');

	$wrapperOpen = '<ul class="menu">';
	$wrapperClose = '</ul>';
	apply_filters('appthemes_default_menu_wrapperOpen', $wrapperOpen);
	apply_filters('appthemes_default_menu_wrapperClose', $wrapperClose);

	$menu = $wrapperOpen . wp_list_pages('echo=0&sort_column=menu_order&depth=0&title_li=0&exclude='.$excludePages) . $wrapperClose;
	echo apply_filters('appthemes_default_menu', $menu);
}


//ajax header javascript builder for child categories AJAX dropdown builder
function cp_ajax_addnew_js_header() {
	global $app_abbr;
	$parentPosting = get_option($app_abbr.'_ad_parent_posting');
	// Define custom JavaScript function
?>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function() {
	//if on page load the parent category is already selected, load up the child categories
	jQuery('#catlvl0').attr('level', 0);
	if (jQuery('#catlvl0 #cat').val() > 0) {
		js_cp_getChildrenCategories(jQuery(this),'catlvl-', 1, '<?php echo $parentPosting; ?>');
	}
	//bind the ajax lookup event to #cat object
	jQuery('#cat').live('change', function(){
		currentLevel = parseInt(jQuery(this).parent().attr('level'));
		js_cp_getChildrenCategories(jQuery(this), 'catlvl', currentLevel+1, '<?php echo $parentPosting; ?>');

		//rebuild the entire set of dropdowns based on which dropdown was changed
		jQuery.each(jQuery(this).parent().parent().children(), function(childLevel, childElement) {
			if(currentLevel+1 < childLevel) jQuery(childElement).remove();
			if(currentLevel+1 == childLevel) jQuery(childElement).removeClass('hasChild');
			//console.log(childElement);
		});

		//find the deepest selected category and assign the value to the "chosenCateory" field
		if(jQuery(this).val() > 0) jQuery('#chosenCategory input:first').val(jQuery(this).val());
		else if(jQuery('#catlvl'+(currentLevel-1)+' select').val() > 0) jQuery('#chosenCategory input:first').val(jQuery('#catlvl'+(currentLevel-1)+' select').val());
		else jQuery('#chosenCategory input:first').val('-1');
	});
});

function js_cp_getChildrenCategories(dropdown, results_div_id, level, allow_parent_posting) {
	parent_dropdown = jQuery(dropdown).parent();
	category_ID = jQuery(dropdown).val();
	results_div = results_div_id+level;
	if(!jQuery(parent_dropdown).hasClass('hasChild'))
		jQuery(parent_dropdown).addClass('hasChild').parent().append('<div id="'+results_div+'" level="'+level+'" class="childCategory"></div>')

  	jQuery.ajax({
		type: "post",
		url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
		data: {
			action: 'cp_getChildrenCategories',
			//_ajax_nonce: '<?php //echo $nonce; ?>',
			catID : category_ID
		},
		beforeSend: function() { jQuery('#getcat').hide();jQuery('#ad-categories-footer').addClass('ui-autocomplete-loading').slideDown("fast");}, //show loading just when dropdown changed
		complete: function() {jQuery('#ad-categories-footer').removeClass('ui-autocomplete-loading'); }, //stop showing loading when the process is complete
		success: function(html){ //so, if data is retrieved, store it in html
			//if no categories are found
			if(html == "") { 
				jQuery('#'+results_div).slideUp("fast");
				if(jQuery(dropdown).val() == -1 && level == 2){
					whenEmpty = false; 
				} else {
					whenEmpty = true;
				}
			//child categories found so build and display them
			} else {
				jQuery('#'+results_div).html(html).slideDown("fast"); //build html from ajax post
				/* FANCY SELECT BOX ACTIVATOR - UNCOMMENT ONCE ITS READY
				jQuery('#'+results_div+" #cat").selectBox({ menuTransition: 'fade', menuSpeed: 'fast' });
				*/
				jQuery('#'+results_div+" a").fadeIn(); //fade in the new dropdown (selectBox converts to <a>
				if(level == 1){
					whenEmpty = false; 
				} else {
					whenEmpty = true;
				}
			}

			//always check if go button should be on or off, jQuery parent is used for traveling backup the category heirarchy
			if( (allow_parent_posting == 'yes' &&  jQuery('#chosenCategory input:first').val() > 0) ){ jQuery('#getcat').fadeIn(); }
			//check for empty category option
			else if(whenEmpty && allow_parent_posting == 'whenEmpty' && jQuery('#chosenCategory input:first').val() > 0) { jQuery('#getcat').fadeIn(); }
			//if child category exists, is set, and allow_parent_posting not set to "when empty"
			else if(jQuery('#'+results_div_id+(level-1)).hasClass('childCategory') && jQuery(dropdown).val() > -1 && allow_parent_posting == 'no') { jQuery('#getcat').fadeIn(); }
			else {jQuery('#getcat').fadeOut(); }

		}
	}); //close jQuery.ajax(
} // end of JavaScript function js_cp_getChildrenCategories
//]]>
</script>
<?php
} // end of PHP function cp_ajax_addnew_js_header


/**
* return all the order values we plan on using as hidden payment fields
*
* @since 3.1
*
*/
function cp_get_order_vals( $order_vals ) {
    // figure out the number of days this ad was listed for
    if ( get_post_meta( $order_vals['post_id'], 'cp_sys_ad_duration', true ) )
        $order_vals['prune_period'] = get_post_meta( $order_vals['post_id'], 'cp_sys_ad_duration', true );
	else
	    $order_vals['prune_period'] = get_option( 'cp_prun_period' );

	$order_vals['item_name'] = sprintf( __( 'Classified ad listing on %s for %s days', 'appthemes' ), get_bloginfo('name'), $order_vals['prune_period'] );
    $order_vals['item_number'] = get_post_meta( $order_vals['post_id'], 'cp_sys_ad_conf_id', true );
    $order_vals['item_amount'] =  get_post_meta( $order_vals['post_id'], 'cp_sys_total_ad_cost', true );
    $order_vals['notify_url'] = get_bloginfo( 'url' ) . '/index.php?invoice=' . get_post_meta( $order_vals['post_id'], 'cp_sys_ad_conf_id', true ) . '&amp;aid=' . $order_vals['post_id'];
    $order_vals['return_url'] = CP_ADD_NEW_CONFIRM_URL . '?pid=' . get_post_meta( $order_vals['post_id'], 'cp_sys_ad_conf_id', true ) . '&amp;aid=' . $order_vals['post_id'];
    $order_vals['return_text'] = __( 'Click here to publish your ad on', 'appthemes' ) . ' ' . get_bloginfo( 'name' );

    return $order_vals;
}


/**
* return all the order pack values we plan on using as hidden payment fields
*
* @since 3.1
*
*/
function cp_get_order_pack_vals( $order_vals ) {
    // lookup the pack info
    $pack = get_pack( $order_vals['pack'] );

    // figure out the number of days this ad was listed for
    // not needed? keeping for safety
    $order_vals['prune_period'] = get_option( 'cp_prun_period' );

	//setup variables depending on the purchase type
	if ( isset( $pack->pack_name ) && stristr( $pack->pack_status, 'membership' ) ) {

	    $order_vals['item_name'] = sprintf( __( 'Membership on %s for %s days', 'appthemes' ), get_bloginfo( 'name' ), $pack->pack_duration );
		$order_vals['item_number'] = stripslashes($pack->pack_name);
		$order_vals['item_amount'] = $pack->pack_membership_price;
		$order_vals['notify_url'] = get_bloginfo( 'url' ) . '/index.php?invoice=' . $order_vals['oid'];
		$order_vals['return_url'] = CP_MEMBERSHIP_PURCHASE_CONFIRM_URL . '?oid=' . $order_vals['oid'];
		$order_vals['return_text'] = __( 'Click here to complete your purchase on', 'appthemes' ) . ' ' . get_bloginfo( 'name' );

    } else {

        _e( "Sorry, but there's been an error.", 'appthemes' );
        die;

    }

    return $order_vals;
}



//function retreives the membership pack name given a membership pack ID
function get_pack($theID, $type = '', $return = '') {
	global $wpdb, $the_pack;

	if ( stristr($theID, 'pend') )
	    $theID = get_pack_id($theID);

	//if the type is dashboard or ad, then get the assume the ID sent is the postID and packID needs to be obtained
	if ( $type == 'ad' || $type == 'dashboard' )
		$theID = get_pack_id( $theID, $type );

	//make sure the value is a proper MySQL int value
	$theID = intval($theID);

	if ( $theID > 0 )
		$the_pack = $wpdb->get_row( "SELECT * FROM $wpdb->cp_ad_packs WHERE pack_id = '$theID'" );

	if ( !empty($return) && !empty($the_pack)) {
		$the_pack = (array)$the_pack;

		if ( $return == 'array' )
		    return $the_pack;
		else
		    return $the_pack[$return];
	}

	return $the_pack;
}

//function send a string and attempt to filter out and return only the actual packID
function get_pack_id($active_pack, $type = '') {
	if ( !empty($type) ) { /*TODO LOOKUP PACK ID FROM POST - Will be possible once pack is stored with posts*/	}
	preg_match('/^pend(?P<pack_id>\w+)-(?P<order_id>\w+)/', $active_pack, $matches);

	if ($matches)
	    return $matches['pack_id'];
	else
	    return $active_pack;
}

//function send a string and attempt to filter out and return only the private order ID
function get_order_id($active_pack) {
	//attempt to match based on "pend" prefix
	preg_match('/^pend(?P<membership_pack_id>\w+)-(?P<private_order_id>\w+)/', $active_pack, $matches);

	//if order id is not foundyet, attempt to match based on option_name prefix
	if ( !isset($matches['private_order_id']) )
		preg_match('/^cp_order_(?P<user_id>\w+)_(?P<private_order_id>\w+)/', $active_pack, $matches);

	return $matches['private_order_id'];
}

//function send a string and attempt to filter out and return only the user ID from the order
function get_order_userid($active_pack) {
	//attempt to match based on "pend" prefix
	preg_match('/^pend(?P<membership_pack_id>\w+)-(?P<private_order_id>\w+)/', $active_pack, $matches);

	//if order id is not foundyet, attempt to match based on option_name prefix
	if ( !isset($matches['private_order_id']) )
		preg_match('/^cp_order_(?P<user_id>\w+)_(?P<private_order_id>\w+)/', $active_pack, $matches);

	return $matches['user_id'];
}

//function that retreives a users pending orders
function get_user_orders($user_id = '', $oid = '') {
	global $wpdb;
	$lookup = 'cp_order';

	if (!empty($user_id))
	    $lookup = 'cp_order_'.$user_id;

	if (!empty($oid))
	    $lookup = $oid;

	$orders = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%".$lookup."%'");

	//currently only expecting 1 order to be available, but programmed to enable easy expansion
	if (isset($orders[0])) {
		//if the order ID is passed, we always return the option string related tothe order
		if (!empty($oid)) return $orders[0]->option_name;
		//if the order ID is not passed, send back an array of all the "orders" for the user
		else return array($orders[0]->option_name);
	}

	//if not returning yet, this value is most likely just "false"
	return $orders;
}

//function that takes a membership pack and returns the proper benefit explanation
function get_pack_benefit($membership, $returnTotal = false) {
	$benefitHTML = '';

	switch ( $membership->pack_type ) {
		case 'percentage':
			if($returnTotal) return number_format(($returnTotal * ($membership->pack_price / 100)),2);
			$benefitHTML .= preg_replace('/.00$/', '', $membership->pack_price).'% '.__('of price','appthemes'); //remove decimal when decimal is .00
			break;
		case 'discount':
			if($returnTotal) return number_format(($returnTotal - ($membership->pack_price*1)),2);
			$benefitHTML .= cp_pos_currency($membership->pack_price).__('\'s less per ad','appthemes');
			break;
		case 'required_static':
			if($returnTotal) return number_format(($membership->pack_price*1),2);
			if((float)$membership->pack_price == 0) $benefitHTML .= __('Free Posting','appthemes');
			else $benefitHTML .= cp_pos_currency($membership->pack_price).__(' per ad','appthemes');
			$benefitHTML .= ' ('.__('required to post','appthemes').')';
			break;
		case 'required_discount':
			if($returnTotal) return number_format(($returnTotal - ($membership->pack_price*1)),2);
			if($membership->pack_price > 0) $benefitHTML .= cp_pos_currency($membership->pack_price).__('\'s less per ad','appthemes');
			$benefitHTML .= ' ('.__('required to post','appthemes').')';
			break;
		case 'required_percentage':
			if($returnTotal) return number_format(($returnTotal * ($membership->pack_price / 100)),2);
			if($membership->pack_price < 100) $benefitHTML .= preg_replace('/.00$/', '', $membership->pack_price).'% '.__('of price','appthemes'); //remove decimal when decimal is .00
			$benefitHTML .= ' ('.__('required to post','appthemes').')';
			break;
		default: //likely 'static'
			if($returnTotal) return number_format(($membership->pack_price*1), 2);
			if((float)$membership->pack_price == 0) $benefitHTML .= __('Free Posting','appthemes');
			else $benefitHTML .= cp_pos_currency($membership->pack_price).__(' per ad','appthemes');
	}

	return $benefitHTML;
}

function get_membership_requirement($catID) {
	//if all posts require "required" memberships
	if ( get_option('cp_required_membership_type') == 'all' ) { return 'all'; }
	//if post requirements are based on category specific requirements
	elseif ( get_option('cp_required_membership_type') == 'category' ) {
		//check if catID option exists to determine if its a required to post category
		$required_categories = get_option('cp_required_categories');
		if ( isset($required_categories[$catID])) return $catID;
	}
	//no requirements active
	else return false;
}

//pass the function the MySQL standardized date and retreive a date relative to wordpress GMT and wordpress date and time display options
function appthemes_display_date($mysqldate) {
	$display_date = date_i18n( get_option('date_format').' '.get_option('time_format'),strtotime($mysqldate), get_option('gmt_offset') );
	return $display_date;
}

//pass the function a UNIX TIMESTAMP or "Properly Formated Date/Time" and retreive a date formated for MySQL database date field type
//optionally pass a number of days to return a time XX days before or after the date/time sent
function appthemes_mysql_date($time, $days = 0) {
	$seconds = 60*60*24*$days;
	$unix_time = strtotime($time)+$seconds;
	$mysqldate = date( 'Y-m-d H:i:s', $unix_time);
	return $mysqldate;
}

function appthemes_seconds_to_days($seconds) {
	return ($seconds / 24 / 60 / 60);
}

function appthemes_days_between_dates($date1, $date2 = '', $precision = '1') {
	if (empty($date2))
	    $date2 = current_time('mysql');

	//setup the times based on string dates, if dates are not strings return false.
	if ( is_string($date1) )
	    $date1 = strtotime($date1);
	else
	    return false;

	if ( is_string($date2) )
	    $date2 = strtotime($date2);
	else
	    return false;

	$days = round( appthemes_seconds_to_days($date1 - $date2), $precision );
	return $days;
}

//setup function to stop function from failing if sm debug bar is not installed
//this allows for optional use of sm debug bar plugin
if (!function_exists('dbug')) { function dbug($args) {} }


function cp_update_geocode( $post_id, $cat, $lat, $lng ) {
	global $wpdb;

	if ( !$lat || !$lng || !$cat || !$post_id )
		return false;

	$post_id = absint( $post_id );

	$table = $wpdb->cp_ad_geocodes;

	if ( ! $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM $table WHERE post_id = %d AND category = %s",
		$post_id, $cat ) ) )
		return cp_add_geocode( $post_id, $cat, $lat, $lng );

	$lat = floatval( $lat );
	$lng = floatval( $lng );

	$wpdb->update(
		$table,
		array (
			'lat' => $lat,
			'lng' => $lng
		),
		array(
			'post_id' => $post_id,
			'category'     => $cat
		)
	);
	return true;
}

// add the pinned Geo Location codes to the Geocode table in the DB
function cp_add_geocode( $post_id, $cat, $lat, $lng ) {
	global $wpdb;
	$table = $wpdb->cp_ad_geocodes;
	$post_id = intval( $post_id );
	$lat = floatval( $lat );
	$lng = floatval( $lng );

	if ( $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM $table WHERE post_id = %d AND category = %s",
		$post_id, $cat ) ) )
		return false;

	$wpdb->insert( $table, array(
		'post_id' => $post_id,
		'category' => $cat,
		'lat' => $lat,
		'lng' => $lng
	) );
	return true;
}

// gets the geo location of the AV from the Geocode table in DB
function cp_get_geocode( $post_id, $cat = '' ) {
	global $wpdb;
  $table = $wpdb->cp_ad_geocodes;
	if ( $cat )
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT lat, lng FROM $table WHERE post_id = %d AND category = %s LIMIT 1", $post_id, $cat ) );
	else
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT lat, lng FROM $table WHERE post_id = %d LIMIT 1", $post_id ) );

	if ( is_object( $row ) )
		return array( 'lat' => $row->lat, 'lng' => $row->lng );
	else
		return false;
}

function cp_do_update_geocode( $meta_id, $post_id, $meta_key, $meta_value ) {
	//*****************************EKLEME*****************************************
}
//add_action( 'added_post_meta', 'cp_do_update_geocode', 10, 4 );
//add_action( 'updated_post_meta', 'cp_do_update_geocode', 10, 4 );
//********************************************************************************


// collect and cache featured images for displayed posts
function cp_collect_featured_images() {
	global $wpdb, $posts, $pageposts, $wp_query, $images_data;

  if(isset($posts) && is_array($posts))
    foreach($posts as $post)
      $post_ids[] = $post->ID;
    
  if(isset($pageposts) && is_array($pageposts))
    foreach($pageposts as $post)
      $post_ids[] = $post->ID;
    
  if(isset($wp_query->posts) && is_array($wp_query->posts))
    foreach($wp_query->posts as $post)
      $post_ids[] = $post->ID;
    
  if(isset($post_ids) && is_array($post_ids)){
    $post_ids = array_unique($post_ids);
    $post_list = implode(",",$post_ids);
    $images = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_parent IN ($post_list) AND (post_mime_type LIKE 'image/%') AND post_type = 'attachment' AND (post_status = 'inherit') ORDER BY ID ASC" );
  }

  if(isset($images) && is_array($images)){
    foreach($images as $image)
      if(!isset($images_data[$image->post_parent]))
        $images_data[$image->post_parent] = $image->ID;
    // create cache for images
    update_post_caches($images, 'post', false, true);
  }

  if(isset($post_ids) && is_array($post_ids)){
    foreach($post_ids as $post_id){
      if(!isset($images_data[$post_id]))
        $images_data[$post_id] = 0;
    }
  }

}


// get the featured image id for a post
function cp_get_featured_image_id($post_id) {
	global $wpdb, $images_data;

  if(isset($images_data[$post_id])) {
    $image_id = $images_data[$post_id];
  } else {
		$images = get_children( array('post_parent' => $post_id, 'post_status' => 'inherit', 'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'ID') );
    if($images){
      $image = array_shift( $images );
      $image_id = $image->ID;
    }
  }

  if(!isset($image_id) || !is_numeric($image_id))
    $image_id = 0;

  return $image_id;
}


// creates edit ad link, use only in loop
function cp_edit_ad_link() {
  global $post, $current_user;
  //if( is_user_logged_in() ) :
    //if( current_user_can('manage_options') ) {
      //edit_post_link( __( 'Edit Post', 'appthemes' ), '<p class="edit">', '</p>', $post->ID );
    //} elseif( get_option('cp_ad_edit') == 'yes' && $post->post_author == $current_user->ID ) {
      $edit_link = add_query_arg('aid', $post->ID, CP_EDIT_URL);
      echo '<p class="edit"><a class="post-edit-link" href="'.$edit_link.'" title="'.__( 'Edit Ad', 'appthemes' ).'">'.__( 'Edit Ad', 'appthemes' ).'</a></p>';
    //}
  //endif;
}


// run the appthemes_init() action hook
appthemes_init();

?>
