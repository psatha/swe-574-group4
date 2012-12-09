<?php
/**
 * @package Classipress Ajax Post Sort
 * @version 4.3
 * @author Julio Gallegos <thesaneroner@gmail.com>
 */
 
/*
	Plugin Name: Classipress Ajax Post Sort
	Description: WordPress plugin for implementing ajax post sorting on Classipress. 
	Author: Julio Gallegos
	Author URI: http://myclassipro.com/
	Plugin URI: http://myclassipro.com/
	Version: 4.3
	License: GPL
*/
session_start();

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'twg_waps_install_plugin'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'twg_waps_uninstall_plugin' );

add_action( 'wp_print_scripts', 'twg_tfsp_print_scripts' );

function twg_waps_install_plugin(){
	update_option('enable_price', 'true');  
    update_option('enable_date', 'true');   
    update_option('enable_title', 'true');
    update_option('enable_mod', 'true'); 
	
    update_option('enable_ex_sold', 'true');
    update_option('enable_ex_noimage', 'true');  
}

function twg_waps_uninstall_plugin() {  
    delete_option('sort_price_asc'); 
    delete_option('sort_price_desc');
    delete_option('sort_by_newest');
    delete_option('sort_by_oldest');
    delete_option('sort_by_az');
    delete_option('sort_by_za');
    delete_option('sort_by_mod_new');
    delete_option('sort_by_mod_old');	
	delete_option('enable_price');
    delete_option('enable_date');
    delete_option('enable_title');
    delete_option('enable_mod'); 
    delete_option('default_sort');	
	delete_option('exclude_sold');
	delete_option('exclude_noimage');	
	delete_option('enable_ex_sold');
	delete_option('enable_ex_noimage');
    delete_option('sort_by_ignore');  
	
	unregister_setting( 'cps-settings-group', 'sort_price_asc' );
	unregister_setting( 'cps-settings-group', 'sort_price_desc' );
	unregister_setting( 'cps-settings-group', 'sort_by_newest' );
	unregister_setting( 'cps-settings-group', 'sort_by_oldest' );
	unregister_setting( 'cps-settings-group', 'sort_by_az' );
	unregister_setting( 'cps-settings-group', 'sort_by_za' );
	unregister_setting( 'cps-settings-group', 'sort_by_mod_new' );
	unregister_setting( 'cps-settings-group', 'sort_by_mod_old' );	
	unregister_setting( 'cps-settings-group', 'enable_price' );
	unregister_setting( 'cps-settings-group', 'enable_date' );
	unregister_setting( 'cps-settings-group', 'enable_title' );
	unregister_setting( 'cps-settings-group', 'enable_mod' );
	unregister_setting( 'cps-settings-group', 'default_sort' );
	unregister_setting( 'cps-settings-group', 'exclude_sold' );
	unregister_setting( 'cps-settings-group', 'exclude_noimage' );
	unregister_setting( 'cps-settings-group', 'enable_ex_sold' );
	unregister_setting( 'cps-settings-group', 'enable_ex_noimage' );
	unregister_setting( 'cps-settings-group', 'sort_by_ignore' );
}

function twg_tfsp_print_scripts() {
	global $wp_query;
    wp_register_script( 'wp-ajax-post-sort',  plugins_url('classipress-ajax-post-sort/wp-ajax-post-sort.js'));
	wp_enqueue_script('wp-ajax-post-sort', plugins_url('classipress-ajax-post-sort/wp-ajax-post-sort.js'), array('jquery'), '2.50', true);
	$data = array( 'term' =>  get_query_var('term'),'taxonomy' => get_query_var('taxonomy'),'paged' => get_query_var('paged'),'url'=>admin_url( 'admin-ajax.php' ));
	wp_localize_script( 'wp-ajax-post-sort', 'wp_ajax_post_sort', $data );
	
}

// create custom plugin settings menu
add_action('admin_menu', 'cps_create_menu');

function cps_create_menu() {

	add_submenu_page( 'options-general.php', 'Classipress Sort Plugin Settings', 'Classipress Sort Plugin', 'manage_options', 'ClassipressSortPlugin', 'cps_settings_page');

	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
	cps_setup_options();
}

function cps_setup_options() {
    if (get_option('sort_price_asc') == '') update_option('sort_price_asc', 'Price: Lowest');
    if (get_option('sort_price_desc') == '') update_option('sort_price_desc', 'Price: Highest');
    if (get_option('sort_by_newest') == '') update_option('sort_by_newest', 'Newest');
    if (get_option('sort_by_oldest') == '') update_option('sort_by_oldest', 'Oldest');
    if (get_option('sort_by_az') == '') update_option('sort_by_az', 'Alpha: A-Z');
    if (get_option('sort_by_za') == '') update_option('sort_by_za', 'Alpha: Z-A');
    if (get_option('sort_by_mod_new') == '') update_option('sort_by_mod_new', 'Modified Date: Newest');
    if (get_option('sort_by_mod_old') == '') update_option('sort_by_mod_old', 'Modified Date: Oldest');
    if (get_option('default_sort') == '') update_option('default_sort', '');	
    if (get_option('exclude_sold') == '') update_option('exclude_sold', 'Exclude Sold');
    if (get_option('exclude_noimage') == '') update_option('exclude_noimage', 'Exclude No Image Ads');		
    if (get_option('sort_by_ignore') == '') update_option('sort_by_ignore', '$');
}

function register_mysettings() {
	//register our settings
	register_setting( 'cps-settings-group', 'sort_price_asc' );
	register_setting( 'cps-settings-group', 'sort_price_desc' );
	register_setting( 'cps-settings-group', 'sort_by_newest' );
	register_setting( 'cps-settings-group', 'sort_by_oldest' );
	register_setting( 'cps-settings-group', 'sort_by_az' );
	register_setting( 'cps-settings-group', 'sort_by_za' );
	register_setting( 'cps-settings-group', 'sort_by_mod_new' );
	register_setting( 'cps-settings-group', 'sort_by_mod_old' );
	register_setting( 'cps-settings-group', 'enable_price' );
	register_setting( 'cps-settings-group', 'enable_date' );
	register_setting( 'cps-settings-group', 'enable_title' );
	register_setting( 'cps-settings-group', 'enable_mod' );
	register_setting( 'cps-settings-group', 'default_sort' );	
	register_setting( 'cps-settings-group', 'exclude_sold' );
	register_setting( 'cps-settings-group', 'exclude_noimage' );
	register_setting( 'cps-settings-group', 'enable_ex_sold' );
	register_setting( 'cps-settings-group', 'enable_ex_noimage' );
	register_setting( 'cps-settings-group', 'sort_by_ignore');
}

	global $my_order;
	$my_order;


function cps_settings_page() {
	global $my_order;
?>
<div class="wrap" style="float:left">
<h2>Classipress Sort Plugin Settings</h2>
<form method="post" action="options.php">
    <?php settings_fields( 'cps-settings-group' ); ?>
    <?php do_settings_sections( 'cps-settings-group' ); 
		$options = get_option( 'default_sort' );
		$checked = ' checked="checked" ';	
	?>
    	<h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000; position:relative">Drop-Down Titles<span style="position:absolute;right:15px">Default</span></h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Sort by Price Lowest:</th>
                <td><input type="text" name="sort_price_asc" value="<?php echo get_option('sort_price_asc'); ?>" /></td>
                <td><input type="checkbox" name="default_sort[price-lowest]" class="default" <?php if(isset( $options['price-lowest'] )) echo $checked  ?>  /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Sort by Price Highest:</th>
                <td><input type="text" name="sort_price_desc" value="<?php echo get_option('sort_price_desc'); ?>" /></td>
                <td><input type="checkbox" name="default_sort[price-highest]" class="default" <?php if(isset( $options['price-highest'] )) echo $checked  ?>  /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Sort by Newest:</th>
                <td><input type="text" name="sort_by_newest" value="<?php echo get_option('sort_by_newest'); ?>" /></td>
                <td><input type="checkbox" name="default_sort[date-newest]" class="default" <?php if(isset( $options['date-newest'] ))  echo $checked  ?>  /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Sort by Oldest:</th>
                <td><input type="text" name="sort_by_oldest" value="<?php echo get_option('sort_by_oldest'); ?>" /></td>
                <td><input type="checkbox" name="default_sort[date-oldest]" class="default" <?php if(isset( $options['date-oldest'] )) echo $checked  ?>  /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Sort by A-Z:</th>
                <td><input type="text" name="sort_by_az" value="<?php echo get_option('sort_by_az'); ?>" /></td>
                <td><input type="checkbox" name="default_sort[title-az]" class="default" <?php if(isset( $options['title-az'] )) echo $checked  ?>  /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Sort by Z-A:</th>
                <td><input type="text" name="sort_by_za" value="<?php echo get_option('sort_by_za'); ?>" /></td>
                <td><input type="checkbox" name="default_sort[title-za]" class="default" <?php if(isset( $options['title-za'] )) echo $checked  ?>  /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Sort by Modified Newest:</th>
                <td><input type="text" name="sort_by_mod_new" value="<?php echo get_option('sort_by_mod_new'); ?>" /></td>
                <td><input type="checkbox" name="default_sort[mod-newest]" class="default" <?php if(isset( $options['mod-newest'] )) echo $checked  ?>  /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Sort by Modified Oldest:</th>
                <td><input type="text" name="sort_by_mod_old" value="<?php echo get_option('sort_by_mod_old'); ?>" /></td>
                <td><input type="checkbox" name="default_sort[mod-oldest]" class="default" <?php if(isset( $options['mod-oldest'] )) echo $checked  ?>  /></td>
            </tr>
        </table>
        <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000">Sort Options</h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Enable Sort by Price:</th>			
                <td><input type="checkbox" name="enable_price" <?php if(get_option('enable_price')) echo $checked  ?>  /></td>
            </tr>
			<tr valign="top">
                <th scope="row">Enable Sort by Date:</th>			
                <td><input type="checkbox" name="enable_date" <?php if(get_option('enable_date')) echo $checked  ?>  /></td>
            </tr>
			<tr valign="top">
                <th scope="row">Enable Sort by Title:</th>			
                <td><input type="checkbox" name="enable_title" <?php if(get_option('enable_title')) echo $checked  ?>  /></td>
            </tr>
			<tr valign="top">
                <th scope="row">Enable Sort by Modified:</th>			
                <td><input type="checkbox" name="enable_mod" <?php if(get_option('enable_mod')) echo $checked  ?>  /></td>
            </tr>
        </table>
        <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000">"Exclude Ads" Text and Options</h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Enable Exclude Sold Ads:</th>
                <td><input type="text" name="exclude_sold" value="<?php echo get_option('exclude_sold'); ?>" /></td>
                <td><input type="checkbox" name="enable_ex_sold"  <?php if(get_option('enable_ex_sold')) echo $checked  ?>  /></td>
            </tr>
			<tr valign="top">
                <th scope="row">Enable Exclude No Image Ads:</th>
                <td><input type="text" name="exclude_noimage" value="<?php echo get_option('exclude_noimage'); ?>" /></td>
                <td><input type="checkbox" name="enable_ex_noimage"  <?php if(get_option('enable_ex_noimage')) echo $checked  ?>  /></td>
            </tr>
        </table>
        <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000">Special Symbol to Ignore During Sort</h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Usually A currency Symbol:</th>
                <td><input type="text" name="sort_by_ignore" maxlength="1" style="width:40px" value="<?php echo get_option('sort_by_ignore'); ?>" /> <span style="color:red">(Warning: Only Enter 1 Character)</span> </td>
            </tr>
        </table>
    	<p class="submit">
    	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    	</p>
</form>
<script type="text/javascript">
(function($) {	
var $unique = $('input.default');
$unique.click(function() {
    $unique.filter(':checked').not(this).removeAttr('checked');
});
})(jQuery);
</script>
</div>
<?php } 

global $thisorder,$no_img,$no_sold;
$thisorder;
$no_img; 
$no_sold;

function twg_tfsp_sort() {
	global $thisorder,$no_sold,$no_img;
	
	$options2 = get_option( 'exclude' );
	
	if($_POST['reset']) {
		$thisorder  = ''; unset($_SESSION['sort_order']);
		$no_img  = false; unset($_SESSION['ex_noimg']); unset($_REQUEST['ex_noimg']);
		$no_sold  = false; unset($_SESSION['ex_sold']); unset($_REQUEST['ex_sold']);
		$show_me = 'hidden';
	}

		$show_price = get_option('enable_price');
		$price_string_asc = get_option('sort_price_asc');
		$price_string_desc = get_option('sort_price_desc');
		$show_date = get_option('enable_date');
		$new_string_asc = get_option('sort_by_newest');
		$new_string_desc = get_option('sort_by_oldest');
		$show_title = get_option('enable_title');
		$tlt_string_az = get_option('sort_by_az');
		$tlt_string_za = get_option('sort_by_za');
		$show_mod = get_option('enable_mod');
		$mod_string_az = get_option('sort_by_mod_new');
		$mod_string_za = get_option('sort_by_mod_old');
		$selected = 'selected="selected"';
		$checked = 	'checked="checked"';
		if($_REQUEST['sort_order']) {
	 	 	$thisorder  = $_REQUEST['sort_order'];
		 	$_SESSION['sort_order'] = $thisorder;
			$show_me = 'visible';
		}
		else if($_SESSION['sort_order']){
		 	$thisorder  = $_SESSION['sort_order'];
			$show_me = 'visible';
		}
		if($_REQUEST['ex_sold']) {
	 	 	$no_sold  = true;
		 	$_SESSION['ex_sold'] = $no_sold;
		}
		else if($_SESSION['ex_sold']){
		 	$no_sold  = $_SESSION['ex_sold'];
		}
		if($_REQUEST['ex_noimg']) {
	 	 	$no_img  = true;
		 	$_SESSION['ex_noimg'] = $no_img;
		}
		else if($_SESSION['ex_noimg']){
		 	$no_img  = $_SESSION['ex_noimg'];
		}
?> 
	
    <form  action="" method="post" id="sort_form">Sirala: 
       <select name="sort_order" id="sort_order">
        <option value="">Siralama</option>
         	<?php if($show_price) {?>
			<option value="price-lowest" <?php if($thisorder == "price-lowest") echo $selected; ?>><?php echo  $price_string_asc ?></option>
			<option value="price-highest" <?php if($thisorder == "price-highest") echo $selected; ?>><?php echo $price_string_desc ?></option>
		<?php } if($show_date) {?>
        	<option value="date-newest" <?php if($thisorder == "date-newest") echo $selected; ?>><?php echo $new_string_asc ?></option>
        	<option value="date-oldest" <?php if($thisorder == "date-oldest") echo $selected; ?>><?php echo $new_string_desc ?></option>
      	<?php } if($show_title) {?>
        	<option value="title-az" <?php if($thisorder == "title-az") echo $selected; ?>><?php echo $tlt_string_az ?></option>
        	<option value="title-za" <?php if($thisorder == "title-za") echo $selected; ?>><?php echo $tlt_string_za ?></option>
      	<?php } if($show_mod) {?>
        	<option value="mod-newest" <?php if($thisorder == "mod-newest") echo $selected; ?>><?php echo $mod_string_az ?></option>
        	<option value="mod-oldest" <?php if($thisorder == "mod-oldest") echo $selected; ?>><?php echo $mod_string_za ?></option>
		<?php } ?>
       </select>
       <input id="reset_me" type="submit" name="reset" value="Sifirla" style="visibility:<?php echo $show_me ?>">
        <?php if(get_option('enable_ex_sold')) {?>
       	<input name="ex_sold" style="margin-left:15px" type="checkbox" id="exclude-sold" <?php if($no_sold) echo $checked; ?> ><span><?php echo get_option('exclude_sold'); ?></span>
      	<?php } if(get_option('enable_ex_noimage')) {?>
       	<input name="ex_noimg" style="margin-left:15px" type="checkbox" id="exclude-noimg" <?php if($no_img) echo $checked; ?> ><span><?php echo get_option('exclude_noimage'); ?></span>
       	<?php } ?>
        <img id='reload_me' title="Tekrardan Yukle" style="padding-left:20px; cursor:pointer" src="<?php bloginfo('url'); ?>/wp-content/plugins/classipress-ajax-post-sort/images/refresh_icon.png" alt="" />
    </form>
<?php
	do_action('wp_ajax_post_sort');
}
			
function sort_ads_by() {
	global $thisorder ;
	
		if($thisorder == 'date-newest') { $this_type = 'date'; $this_way = 'DESC'; }
		else if($thisorder == 'date-oldest') { $this_type = 'date'; $this_way =  'ASC'; }
		else if($thisorder == 'title-az') { $this_type = 'title'; $this_way =  'ASC'; }
		else if($thisorder == 'title-za')  { $this_type = 'title'; $this_way =  'DESC'; }
		else if($thisorder == 'mod-newest') {  $this_type = 'modified'; $this_way =  'DESC'; }
		else if($thisorder == 'mod-oldest') { $this_type = 'modified'; $this_way =  'ASC'; } 		

	return array ($this_type, $this_way);
}

function price_sort_it() {
    global $wpdb, $thisorder;
	
		if($thisorder == 'price-lowest') {
			$this_order = 'ASC';
		}
		else if($thisorder == 'price-highest') {
			$this_order = 'DESC';
		}
		
    return "(SELECT CAST(REPLACE(REPLACE(REPLACE($wpdb->prefix" . "postmeta.meta_value, ',', ''), '" . $sym . "',''),' ','') AS SIGNED)
               FROM $wpdb->prefix" . "postmeta
              WHERE $wpdb->prefix" . "posts.ID = $wpdb->prefix" . "postmeta.post_id
                AND $wpdb->prefix" . "postmeta.meta_key = 'cp_price')" . $this_order;
}

function exclude_sold( $where ) {
	global $wpdb,$no_sold;

    if ($no_sold) {
        return $where . " AND $wpdb->prefix" . "posts.ID NOT IN ( SELECT DISTINCT post_id FROM $wpdb->prefix" . "postmeta WHERE meta_key = 'cp_av_solved' AND meta_value = 'yes' ) ";
    }
    else {
        return $where;
    }

}

function exclude_noimg( $where ) {
	global $wpdb,$no_img;

    if ($no_img) {
        return $where . " AND $wpdb->prefix" . "posts.ID IN ( SELECT DISTINCT post_parent FROM $wpdb->prefix" . "posts WHERE post_parent > 0 AND post_type = 'attachment') ";
    }
    else {
        return $where;
    }
}

function twg_tfsp_cat_sort_price() {
	global $wpdb,$wp_query,$thisorder;

		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$my_sort = get_option( 'default_sort' );
		
 			if($my_sort && $thisorder =="") { 
				foreach(array_keys($my_sort) as $paramName) {
				 	$thisorder = $paramName;
				}
			}
						
		list ($sort_type, $sort_order) = sort_ads_by();

		if($thisorder == 'price-lowest' || $thisorder == 'price-highest') { 
			add_filter('posts_orderby', 'price_sort_it');
			$args = array_merge( $wp_query->query, array('caller_get_posts' => 1,'paged' => $paged ));
		}
		else {		
			$args = array_merge( $wp_query->query, array('caller_get_posts' => 1,'paged' => $paged, 'orderby' => $sort_type, 'order' => $sort_order ));
		}
		add_filter( 'posts_where', 'exclude_sold'); 
		add_filter( 'posts_where', 'exclude_noimg'); 
							
		query_posts($args);				
		
}

add_action('wp_ajax_post_sort', 'twg_tfsp_cat_sort_price');

?>