<?php
/**
 * @package ClassiPress Author Post Sort
 * @version 1.1
 * @author Classipro
 */
 
/*
	Plugin Name: ClassiPress Author Post Sort
	Description: Classipress plugin for implementing  post sorting on Author's Dashboard. 
	Author: J.G
	Author URI: http://wprabbits.com/
	Version: 1.1
	License: GPL
*/
session_start();

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'mcp_aps_install_plugin'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'mcp_aps_uninstall_plugin' );

function mcp_aps_install_plugin(){
	//Set Defaults Options
    update_option('enable_mcp_title', 'true');
    update_option('enable_mcp_views', 'true');
    update_option('enable_mcp_status', 'true');
    update_option('enable_mcp_mod', 'true');
    update_option('enable_mcp_date', 'true');
    update_option('exclude_mcp_sold_ads', 'true');
    update_option('exclude_mcp_ex_ads', 'true');
}

function mcp_aps_uninstall_plugin() { 
	//Delete Strings 
    delete_option('mcp_title_asc'); 
    delete_option('mcp_title_desc');
    delete_option('mcp_views_asc');
    delete_option('mcp_views_desc');
    delete_option('mcp_status_asc');
    delete_option('mcp_status_desc');
    delete_option('mcp_mod_new');
    delete_option('mcp_mod_old');
    delete_option('mcp_date_new');
    delete_option('mcp_date_old');
    delete_option('mcp_ex_sold_ads');
    delete_option('mcp_ex_exp_ads');
	//Delete Options 
	delete_option('enable_mcp_title');
    delete_option('enable_mcp_views');
    delete_option('enable_mcp_status');
    delete_option('enable_mcp_mod'); 
    delete_option('enable_mcp_date');	
	delete_option('exclude_mcp_sold_ads');
    delete_option('exclude_mcp_ex_ads');  
	//Unhook Strings  
	unregister_setting( 'mcp-aps-settings-group', 'mcp_title_asc' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_title_desc' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_views_asc' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_views_desc' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_status_asc' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_status_desc' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_mod_new' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_mod_old' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_date_new' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_date_old' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_ex_sold_ads' );
	unregister_setting( 'mcp-aps-settings-group', 'mcp_ex_exp_ads' );
	//Unhook Options 
	unregister_setting( 'mcp-aps-settings-group', 'enable_mcp_title' );
	unregister_setting( 'mcp-aps-settings-group', 'enable_mcp_views' );
	unregister_setting( 'mcp-aps-settings-group', 'enable_mcp_status' );
	unregister_setting( 'mcp-aps-settings-group', 'enable_mcp_mod' );
	unregister_setting( 'mcp-aps-settings-group', 'enable_mcp_date' );
	unregister_setting( 'mcp-aps-settings-group', 'exclude_mcp_sold_ads' );
	unregister_setting( 'mcp-aps-settings-group', 'exclude_mcp_ex_ads' );
	
	
}

function mcp_aps_print_scripts() {
	if ( is_page_template('tpl-dashboard.php') ) {
    	wp_register_script( 'cp-author-post-sort',  plugins_url('classipress-author-post-sort/classipress-author-post-sort.js'));
		wp_enqueue_script('cp-author-post-sort', plugins_url('classipress-author-post-sort/classipress-author-post-sort.js'), array('jquery'), '2.50', true);
	} else {
		mcp_aps_master_reset();
	}
}
// Print our script s in wp_head

add_action( 'wp_print_scripts', 'mcp_aps_print_scripts' );


// create custom plugin settings menu
add_action('admin_menu', 'mcp_aps_create_menu');

function mcp_aps_create_menu() {

	add_submenu_page( 'options-general.php', 'ClassiPress Author Sort Plugin Settings', 'ClassiPress Author Sort Plugin', 'manage_options', 'ClassiPressAuthorSortPlugin', 'mcp_aps_settings_page');
	//call register settings function
	add_action( 'admin_init', 'mcp_aps_settings' );
	mcp_aps_options();

}

function mcp_aps_options() {
	//Set Defaults strings
    if (get_option('mcp_title_asc') == '') update_option('mcp_title_asc', ' Title A-Z');
    if (get_option('mcp_title_desc') == '') update_option('mcp_title_desc', 'Title Z-A');
    if (get_option('mcp_views_asc') == '') update_option('mcp_views_asc', 'Lowest Views');
    if (get_option('mcp_views_desc') == '') update_option('mcp_views_desc', 'Highest Views');
    if (get_option('mcp_status_asc') == '') update_option('mcp_status_asc', 'Status ASC');
    if (get_option('mcp_status_desc') == '') update_option('mcp_status_desc', 'Status DESC');
    if (get_option('mcp_mod_new') == '') update_option('mcp_mod_new', 'Modified: Newest');
    if (get_option('mcp_mod_old') == '') update_option('mcp_mod_old', 'Modified: Oldest');
    if (get_option('mcp_date_new') == '') update_option('mcp_date_new', 'Created: Newest');
    if (get_option('mcp_date_old') == '') update_option('mcp_date_old', 'Created: Oldest');
    if (get_option('mcp_ex_sold_ads') == '') update_option('mcp_ex_sold_ads', 'Exclude Sold');
    if (get_option('mcp_ex_exp_ads') == '') update_option('mcp_ex_exp_ads', 'Exlcude Expired');
}

function mcp_aps_settings() {
	//Hook Strings 
	register_setting( 'mcp-aps-settings-group', 'mcp_title_asc' );
	register_setting( 'mcp-aps-settings-group', 'mcp_title_desc' );
	register_setting( 'mcp-aps-settings-group', 'mcp_views_asc' );
	register_setting( 'mcp-aps-settings-group', 'mcp_views_desc' );
	register_setting( 'mcp-aps-settings-group', 'mcp_status_asc' );
	register_setting( 'mcp-aps-settings-group', 'mcp_status_desc' );
	register_setting( 'mcp-aps-settings-group', 'mcp_mod_new' );
	register_setting( 'mcp-aps-settings-group', 'mcp_mod_old' );
	register_setting( 'mcp-aps-settings-group', 'mcp_date_new' );
	register_setting( 'mcp-aps-settings-group', 'mcp_date_old' );
	register_setting( 'mcp-aps-settings-group', 'mcp_ex_sold_ads' );
	register_setting( 'mcp-aps-settings-group', 'mcp_ex_exp_ads' );
	//Hook Options 
	register_setting( 'mcp-aps-settings-group', 'enable_mcp_title' );
	register_setting( 'mcp-aps-settings-group', 'enable_mcp_views' );
	register_setting( 'mcp-aps-settings-group', 'enable_mcp_status' );
	register_setting( 'mcp-aps-settings-group', 'enable_mcp_mod' );
	register_setting( 'mcp-aps-settings-group', 'enable_mcp_date' );
	register_setting( 'mcp-aps-settings-group', 'exclude_mcp_sold_ads' );
	register_setting( 'mcp-aps-settings-group', 'exclude_mcp_ex_ads' );
}

global $my_order;
$my_order;


function mcp_aps_settings_page() {
	global $my_order;
?>
	<div class="wrap" style="float:left">
		<h2>ClassiPress Author Post Sort Plugin Settings</h2>
			<form method="post" action="options.php">
				<?php 
                    settings_fields( 'mcp-aps-settings-group');
                    do_settings_sections( 'mcp-aps-settings-group' ); 
                    $checked = ' checked="checked" ';	
                ?>
                <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000; position:relative">Drop-Down Titles</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Sort by Title A-Z:</th>
                        <td><input type="text" name="mcp_title_asc" value="<?php echo get_option('mcp_title_asc'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Title Z-A:</th>
                        <td><input type="text" name="mcp_title_desc" value="<?php echo get_option('mcp_title_desc'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Highest Views:</th>
                        <td><input type="text" name="mcp_views_asc" value="<?php echo get_option('mcp_views_asc'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Lowest Views:</th>
                        <td><input type="text" name="mcp_views_desc" value="<?php echo get_option('mcp_views_desc'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Status ASC:</th>
                        <td><input type="text" name="mcp_status_asc" value="<?php echo get_option('mcp_status_asc'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Status DESC:</th>
                        <td><input type="text" name="mcp_status_desc" value="<?php echo get_option('mcp_status_desc'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Modified Newest:</th>
                        <td><input type="text" name="mcp_mod_new" value="<?php echo get_option('mcp_mod_new'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Modified Oldest:</th>
                        <td><input type="text" name="mcp_mod_old" value="<?php echo get_option('mcp_mod_old'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Created Newest:</th>
                        <td><input type="text" name="mcp_date_new" value="<?php echo get_option('mcp_date_new'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sort by Created Oldest:</th>
                        <td><input type="text" name="mcp_date_old" value="<?php echo get_option('mcp_date_old'); ?>" /></td>
                    </tr>
                </table>
                <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000">Sort Options</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Enable Sort by Title:</th>			
                        <td><input type="checkbox" name="enable_mcp_title" <?php if(get_option('enable_mcp_title')) echo $checked  ?>  /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable Sort by Views:</th>			
                        <td><input type="checkbox" name="enable_mcp_views" <?php if(get_option('enable_mcp_views')) echo $checked  ?>  /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable Sort by Status:</th>			
                        <td><input type="checkbox" name="enable_mcp_status" <?php if(get_option('enable_mcp_status')) echo $checked  ?>  /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable Sort by Modified:</th>			
                        <td><input type="checkbox" name="enable_mcp_mod" <?php if(get_option('enable_mcp_mod')) echo $checked  ?>  /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable Sort by Created:</th>			
                        <td><input type="checkbox" name="enable_mcp_date" <?php if(get_option('enable_mcp_date')) echo $checked  ?>  /></td>
                    </tr>
                </table>
                <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000">"Exclude Ads" Text and Options</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Enable Exclude Sold Ads:</th>
                        <td><input type="text" name="mcp_ex_sold_ads" value="<?php echo get_option('mcp_ex_sold_ads'); ?>" /></td>
                        <td><input type="checkbox" name="exclude_mcp_sold_ads"  <?php if(get_option('exclude_mcp_sold_ads')) echo $checked  ?>  /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable Exclude Expired Ads:</th>
                        <td><input type="text" name="mcp_ex_exp_ads" value="<?php echo get_option('mcp_ex_exp_ads'); ?>" /></td>
                        <td><input type="checkbox" name="exclude_mcp_ex_ads"  <?php if(get_option('exclude_mcp_ex_ads')) echo $checked  ?>  /></td>
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
    <div style="float:right; padding:100px 50px; text-align:center">
        <h3>Enjoy using this plugin?</h3>
        <h3>Would you like to see more enhacements?</h3>
        <h3>You can help by buying me a cup of coffee</h3>
        <img src="<?php echo plugins_url( '/images/starbucks-coffee-cup.jpg' , __FILE__ ) ?>" alt="" />
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="PCMR792R2YRV2">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
    </div>
<?php } 

global $mcp_aps_order,$mcp_aps_ex_sold,$mcp_aps_ex_exp;

$mcp_aps_order;
$mcp_aps_ex_sold;
$mcp_aps_ex_exp;

function mcp_author_post_sort() {
	global $mcp_aps_order,$mcp_aps_ex_sold,$mcp_aps_ex_exp;
	
	//Rest Sorter
	if($_POST['reset']) {
		$mcp_aps_order  = ''; unset($_SESSION['mcp_aps_order']);
		$mcp_aps_ex_sold  = false; unset($_SESSION['mcp_aps_ex_sold']);
		$mcp_aps_ex_exp  = false; unset($_SESSION['mcp_aps_ex_exp']); 
		$show_me = 'hidden';
	}
	
	$mcp_ex_sold_ads = get_option('mcp_ex_sold_ads');
	
	//Input Settings
	$selected = 'selected="selected"';
	$checked = 	'checked="checked"';
	
	
	if($_REQUEST['mcp_aps_order']) {
		$mcp_aps_order  = $_REQUEST['mcp_aps_order'];
		$_SESSION['mcp_aps_order'] = $mcp_aps_order;
		$show_me = 'visible';
	}
	else if($_SESSION['mcp_aps_order']){
		$mcp_aps_order  = $_SESSION['mcp_aps_order'];
		$show_me = 'visible';
	}
	if($_POST['mcp_box1'] == "0") {
		$mcp_aps_ex_sold  = false;
		$_SESSION['mcp_aps_ex_sold'] = $mcp_aps_ex_sold;
	}
	else if($_POST['mcp_box1'] == "1"){
		$mcp_aps_ex_sold  = true;
		$_SESSION['mcp_aps_ex_sold'] = $mcp_aps_ex_sold;
	}
	if($_POST['mcp_box2'] == "0") {
		$mcp_aps_ex_exp  = false;
		$_SESSION['mcp_aps_ex_exp'] = $mcp_aps_ex_exp;
	}
	else if($_POST['mcp_box2'] == "1"){
		$mcp_aps_ex_exp  = true;
		$_SESSION['mcp_aps_ex_exp'] = $mcp_aps_ex_exp;
	}
	?> 
	
    <form  action="" method="post" id="mcp_aps_order_form">Sirala: 
       <select name="mcp_aps_order" id="mcp_aps_order">
        <option value="">Siralama</option>
         	<?php if(get_option('enable_mcp_title')) {?>
				<option value="title-az" <?php if($mcp_aps_order == "title-az") echo $selected; ?>><?php echo get_option('mcp_title_asc'); ?></option>
				<option value="title-za" <?php if($mcp_aps_order == "title-za") echo $selected; ?>><?php echo get_option('mcp_title_desc'); ?></option>
			<?php } if(get_option('enable_mcp_views')) {?>
				<option value="views-high" <?php if($mcp_aps_order == "views-high") echo $selected; ?>><?php echo get_option('mcp_views_asc'); ?></option>
				<option value="views-low" <?php if($mcp_aps_order == "views-low") echo $selected; ?>><?php echo get_option('mcp_views_desc'); ?></option>
      		<?php } if(get_option('enable_mcp_status')) {?>
				<option value="status-up" <?php if($mcp_aps_order == "status-up") echo $selected; ?>><?php echo get_option('mcp_status_asc'); ?></option>
				<option value="status-dw" <?php if($mcp_aps_order == "status-dw") echo $selected; ?>><?php echo get_option('mcp_status_desc'); ?></option>
      		<?php } if(get_option('enable_mcp_mod')) {?>
				<option value="mod-newest" <?php if($mcp_aps_order == "mod-newest") echo $selected; ?>><?php echo get_option('mcp_mod_new'); ?></option>
				<option value="mod-oldest" <?php if($mcp_aps_order == "mod-oldest") echo $selected; ?>><?php echo get_option('mcp_mod_old'); ?></option>
			<?php } if(get_option('enable_mcp_date'))  {?>
				<option value="date-newest" <?php if($mcp_aps_order == "date-newest") echo $selected; ?>><?php echo get_option('mcp_date_new'); ?></option>
				<option value="date-oldest" <?php if($mcp_aps_order == "date-oldest") echo $selected; ?>><?php echo get_option('mcp_date_old'); ?></option>
			<?php } ?>
       </select>
       <input id="mcp_aps_reset_me" type="submit" name="reset" value="Sifirla" style="visibility:<?php echo $show_me ?>">
	<?php if(get_option('exclude_mcp_sold_ads')) {?>
       	<input name="mcp_aps_ex_sold" style="margin-left:15px" type="checkbox" id="mcp_aps_ex_sold" <?php if($_SESSION['mcp_aps_ex_sold']) echo $checked; ?> ><span><?php echo get_option('mcp_ex_sold_ads'); ?></span>
	<?php } if(get_option('exclude_mcp_ex_ads')) {?>
       	<input name="mcp_aps_ex_exp" style="margin-left:15px" type="checkbox" id="mcp_aps_ex_exp" <?php if($_SESSION['mcp_aps_ex_exp']) echo $checked; ?> ><span><?php echo get_option('mcp_ex_exp_ads'); ?></span>
	<?php } ?>
    	<input type="hidden" name="mcp_box1" id="mcp_box1" value="0" />
    	<input type="hidden" name="mcp_box2" id="mcp_box2" value="0" />
        <img id='mcp_aps_reload_me' title="Tekrar Yukle" style="padding-left:20px; cursor:pointer" src="<?php echo plugins_url( '/images/refresh_icon.png' , __FILE__ ) ?>" alt="" />
    </form>
<?php do_action('mcp_author_post_sort_do'); 

}
			
function mcp_aps_sort_author_ads() {
	global $wpdb,$mcp_aps_order;
	
			$metaorder = "(SELECT $wpdb->prefix" . "postmeta.meta_value
				FROM $wpdb->prefix" . "postmeta
              	WHERE $wpdb->prefix" . "posts.id = $wpdb->prefix" . "postmeta.post_id
                AND $wpdb->prefix" . "postmeta.meta_key = 'cp_total_count')"; 

		if($mcp_aps_order == 'title-az') { $this_type = 'post_title'; $this_way =  'ASC'; }
		else if($mcp_aps_order == 'title-za')  { $this_type = 'post_title'; $this_way =  'DESC'; }
		else if($mcp_aps_order == 'views-low') { $this_type = $metaorder; $this_way =  'DESC'; }
		else if($mcp_aps_order == 'views-high') { $this_type = $metaorder; $this_way =  'ASC'; } 	
		else if($mcp_aps_order == 'status-up') { $this_type = 'post_status'; $this_way =  'DESC'; }
		else if($mcp_aps_order == 'status-dw') { $this_type = 'post_status'; $this_way =  'ASC'; } 	
		else if($mcp_aps_order == 'mod-newest') {  $this_type = 'post_modified'; $this_way =  'DESC'; }
		else if($mcp_aps_order == 'mod-oldest') { $this_type = 'post_modified'; $this_way =  'ASC'; } 		
		else if($mcp_aps_order == 'date-newest') { $this_type = 'post_date'; $this_way = 'DESC'; }
		else if($mcp_aps_order == 'date-oldest') { $this_type = 'post_date'; $this_way =  'ASC'; }
		
		if($mcp_aps_order != "") {
			$orderby = $this_type . " " . $this_way;
		} else {
			$orderby = 'post_date DESC';
		}
		
	return $orderby;
}

function mcp_aps_exclude_sold_ads( $where ) {
	global $wpdb,$mcp_aps_ex_sold;
	
		if ($mcp_aps_ex_sold) {
			return $where . " AND $wpdb->prefix" . "posts.ID NOT IN ( SELECT DISTINCT post_id FROM $wpdb->prefix" . "postmeta WHERE meta_key = 'cp_av_solved' AND meta_value = 'yes' ) ";
		} else {
			return $where;
		}
}

function mcp_aps_exclude_exp_ads( $where ) {
	global $wpdb,$mcp_aps_ex_exp;
	
		if ($mcp_aps_ex_exp) {
			return $where . " AND $wpdb->prefix" . "posts.ID NOT IN ( SELECT DISTINCT post_id FROM $wpdb->prefix" . "postmeta WHERE post_status = 'draft' ) ";
		} else {
			return $where;
		}
}

function mcp_aps_master_reset() {
	
	remove_filter( 'posts_where', 'mcp_aps_exclude_sold_ads'); 
	remove_filter( 'posts_where', 'mcp_aps_exclude_exp_ads'); 
	remove_filter( 'posts_orderby', 'mcp_aps_sort_author_ads'); 
	
	
	
}

function mcp_author_post_sort_action() {
							
		add_filter( 'posts_where', 'mcp_aps_exclude_sold_ads'); 
		add_filter( 'posts_where', 'mcp_aps_exclude_exp_ads'); 
		add_filter( 'posts_orderby', 'mcp_aps_sort_author_ads'); 
		
		if($_POST['reset']) {
			remove_filter( 'posts_where', 'mcp_aps_exclude_sold_ads'); 
			remove_filter( 'posts_where', 'mcp_aps_exclude_exp_ads'); 
			remove_filter( 'posts_orderby', 'mcp_aps_sort_author_ads'); 
		}
}

add_action('mcp_author_post_sort_do', 'mcp_author_post_sort_action');

?>