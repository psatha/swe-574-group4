<?php
/**
 * @package ClassiPress Infinite Scroll
 * @version 1.4
 * @author Julio Gallegos <thesaneroner@gmail.com>
 */
 
/*
	Plugin Name: ClassiPress Infinite Scroll
	Description: Replace the old and ugly Classipress pagination with a fancy Ajax one! Less pages refreshed and more enjoyment!. 
	Author: Julio Gallegos
	Author URI: http://myclassipro.com/
	Plugin URI: http://myclassipro.com/
	Version: 1.2
	License: GPL
*/


/* Runs when plugin is activated */
register_activation_hook(__FILE__,'mcp_mis_install_plugin'); 


/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'mcp_mis_uninstall_plugin' );

global $myThemeName,$myThemeVer;
	//$myThemeName = wp_get_theme();
	$myThemeVer = $myThemeName['Version'];
 
function mcp_mis_install_plugin(){
	global $myThemeName,$myThemeVer;
	
	if($myThemeVer <= '3.1.2') {
    	update_option('mcp_mis_ad_block', '#listing_id');
    	update_option('mcp_mis_ads', '.post-block');
    	update_option('mcp_mis_pager', '.paging');
		update_option('mcp_mis_next', '.current');
	}
	else {
    	update_option('mcp_mis_ad_block', '.content_left');
    	update_option('mcp_mis_ads', '.post-block-out');
    	update_option('mcp_mis_pager', '.paging');
		update_option('mcp_mis_next', '.current');
	}
	update_option('mcp_mis_pagenum', true);
	
}

function mcp_mis_uninstall_plugin() {
	///remove options from WP database
	delete_option("mcp_mis_ad_block");
	delete_option('mcp_mis_ads');
	delete_option("mcp_mis_pager");
	delete_option('mcp_mis_next');
	delete_option('mcp_mis_pagenum');
	delete_option('mcp_mis_loader');
	unregister_setting( 'mcp-mis-settings-group', 'mcp_mis_ad_block' );
	unregister_setting( 'mcp-mis-settings-group', 'mcp_mis_ads' );
	unregister_setting( 'mcp-mis-settings-group', 'mcp_mis_pager' );
	unregister_setting( 'mcp-mis-settings-group', 'mcp_mis_next' );
	unregister_setting( 'mcp-mis-settings-group', 'mcp_mis_pagenum' );
	unregister_setting( 'mcp-mis-settings-group', 'mcp_mis_loader' );
}


// Print our script s in wp_head
function mcp_mis_enqueue_scripts(){
	if (!is_admin()) {	
	
		wp_register_style( 'mcp_mis_admin_css', plugins_url('classipress-infinite-scroll/css/classipress-infinite-scroll.css'),false, 1.0, false);
		wp_register_script( 'mcp_mis_admin_js', plugins_url('classipress-infinite-scroll/js/classipress-infinite-scroll.js'), array('jquery'), '1.0', false);
		wp_enqueue_style('mcp_mis_admin_css');
		wp_enqueue_script('mcp_mis_admin_js');       
        echo '<style type="text/css">' . get_option("mcp_mis_pager") . '{ display:none}</style>';
	
	}
}
add_action('wp_print_scripts', 'mcp_mis_enqueue_scripts' );

function mcp_make_it_rain() {
	if (!is_admin()) {	
	echo 'test';
		if(!is_page('dashboard')) {
			$container = get_option("mcp_mis_ad_block");
			$ads = get_option('mcp_mis_ads');
			$pagination = get_option('mcp_mis_pager');
			$next = get_option('mcp_mis_next');
			$loader = get_option('mcp_mis_loader');
			if(!get_option('mcp_mis_pagenum')) {
				$pagenum =  'showpages  : false';
			} 
			if(empty($loader)) {
				$loader = plugins_url( 'images/loading-7.gif' , __FILE__ ); 
			} else {
				foreach(array_keys($loader) as $img)
  				$loader = plugins_url( 'images/' . $img . '.gif' , __FILE__ ); 	
			}

			echo '<script type="text/javascript">
					jQuery.myClassiPro({
						container 	: "' . $container  .'",
						ads			: "' . $ads . '",
						pagination	: "' . $pagination . '",
						next		: "' . $next . '",
						loader		: "' . $loader . '",'
						. $pagenum .
					'});
				</script>';
		}
		else {
			echo '<script type="text/javascript">
					jQuery.myClassiPro({
						container 	: ".tblwide",
						ads			: ".even",
						page 		: "dash",'
						. $pagenum . 
					'});
				</script>';	
		}
	}
}
add_action('wp_print_footer_scripts', 'mcp_make_it_rain');

// create custom plugin settings menu
function mcp_mis_admin_menu() {
	add_options_page('ClassiPress Infinite Scroll', 'ClassiPress Infinite Scroll', 'administrator','classipress_infinite_scroll_admin', 'mcp_mis_settings_page');
	add_action( 'admin_init', 'mcp_mis_settings' );
	add_action( 'admin_init', 'mcp_mis_options' );
	
}
add_action('admin_menu', 'mcp_mis_admin_menu');


function mcp_mis_options() {
	global $myThemeName,$myThemeVer;

	//Set Defaults strings	
	if($myThemeVer <= '3.1.2') {
		if (get_option('mcp_mis_ad_block') == '') update_option('mcp_mis_ad_block', '.block1');
	} else {
		if (get_option('mcp_mis_ad_block') == '') update_option('mcp_mis_ad_block', '.content_left');
	}
    if (get_option('mcp_mis_ads') == '') update_option('mcp_mis_ads', '.post-block-out');
    if (get_option('mcp_mis_pager') == '') update_option('mcp_mis_pager', '.paging');
    if (get_option('mcp_mis_next') == '') update_option('mcp_mis_next', '.current');
}

function mcp_mis_settings() {
	//Hook Strings 
	register_setting( 'mcp-mis-settings-group', 'mcp_mis_ad_block' );
	register_setting( 'mcp-mis-settings-group', 'mcp_mis_ads' );
	register_setting( 'mcp-mis-settings-group', 'mcp_mis_pager' );
	register_setting( 'mcp-mis-settings-group', 'mcp_mis_next' );
	register_setting( 'mcp-mis-settings-group', 'mcp_mis_pagenum' );
	register_setting( 'mcp-mis-settings-group', 'mcp_mis_loader' );

}

function mcp_mis_settings_page() {
	//global $my_order;
?>
	<div class="wrap" style="float:left">
		<h2>ClassiPress Infinite Scroll Plugin Settings</h2>
			<form method="post" action="options.php">
				<?php 
                    settings_fields( 'mcp-mis-settings-group');
                    do_settings_sections( 'mcp-mis-settings-group' ); 
					$pagenum = get_option('mcp_mis_pagenum');
					$loader = get_option('mcp_mis_loader');
                    $checked = ' checked="checked" ';
					
					if(empty($loader)) {
						$empty = true;
					}
                ?>
                <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000">The guts of the operation</h3><span style="color:red">*These settings should not be changed unless your site is highley customized!</span>
                <table class="form-table" style="position:relative">
                    <tr valign="top">
                        <th scope="row">Ads Container:</th>
                        <td><input type="text" name="mcp_mis_ad_block" value="<?php  echo get_option("mcp_mis_ad_block"); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Individual Ad Container:</th>
                        <td><input type="text" name="mcp_mis_ads" value="<?php  echo get_option('mcp_mis_ads'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Pagination Container:</th>
                        <td><input type="text" name="mcp_mis_pager" value="<?php  echo get_option('mcp_mis_pager'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Pagination Next:</th>
                        <td><input type="text" name="mcp_mis_next" value="<?php  echo get_option('mcp_mis_next'); ?>" /></td>
                    </tr>
                </table>
                <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000">Visual Options</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Show Page Numbers:</th>			
                        <td><input type="checkbox" name="mcp_mis_pagenum" <?php  if($pagenum) echo $checked  ?>  /></td>
                    </tr>
                </table>
                <h3 style="color: #0066FF; width:100%; border-bottom:1px solid #000">Select an Animation GIF</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Plain Jane:</th>
                        <td>
                        	<input type="checkbox" class="default" name="mcp_mis_loader[loading-1]"  <?php if($loader['loading-1']) echo $checked  ?>   />
                        	<img style="padding:0 0 0 50px; position:absolute" src="<?php echo plugins_url( '/images/loading-1.gif' , __FILE__ ) ?>" alt="" />
                        </td>
                    </tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top">
                        <th scope="row">The Man:</th>
                        <td>
                        	<input type="checkbox" class="default" name="mcp_mis_loader[loading-2]"  <?php if($loader['loading-2']) echo $checked  ?>  />
                        	<img style="padding:0 0 0 50px; position:absolute" src="<?php echo plugins_url( '/images/loading-2.gif' , __FILE__ ) ?>" alt="" />
                        </td>
                    </tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top">
                        <th scope="row">Red Rain:</th>
                        <td>
                        	<input type="checkbox" class="default" name="mcp_mis_loader[loading-3]"  <?php if($loader['loading-3'] ) echo $checked  ?>  />
                        	<img style="padding:0 0 0 50px; position:absolute" src="<?php echo plugins_url( '/images/loading-3.gif' , __FILE__ ) ?>" alt="" />
                        </td>
                    </tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top">
                        <th scope="row">Tic Toc:</th>
                        <td>
                        	<input type="checkbox" class="default" name="mcp_mis_loader[loading-4]"  <?php if($loader['loading-4'] ) echo $checked  ?>  />
                        	<img style="padding:0 0 0 50px; position:absolute" src="<?php echo plugins_url( '/images/loading-4.gif' , __FILE__ ) ?>" alt="" />
                        </td>
                    </tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top">
                        <th scope="row">Big Nerd:</th>
                        <td>
                        	<input type="checkbox" class="default" name="mcp_mis_loader[loading-5]"  <?php if( $loader['loading-5']) echo $checked  ?>  />
                        	<img style="padding:0 0 0 50px; position:absolute" src="<?php echo plugins_url( '/images/loading-5.gif' , __FILE__ ) ?>" alt="" />
                        </td>
                    </tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top">
                        <th scope="row">Any Day Now:</th>
                        <td>
                        	<input type="checkbox" class="default" name="mcp_mis_loader[loading-6]"  <?php if($loader['loading-6']) echo $checked  ?>  />
                        	<img style="padding:0 0 0 50px; position:absolute" src="<?php echo plugins_url( '/images/loading-6.gif' , __FILE__ ) ?>" alt="" />
                        </td>
                    </tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top"><td></td></tr>
                    <tr valign="top">
                        <th scope="row">Default:</th>
                        <td>
                        	<input type="checkbox" class="default" name="mcp_mis_loader[loading-7]"  <?php if($loader['loading-7'] || $empty == true) echo $checked  ?>  />
                        	<img style="padding:0 0 0 50px; position:absolute" src="<?php echo plugins_url( '/images/loading-7.gif' , __FILE__ ) ?>" alt="" />
                        </td>
                    </tr>
                </table>
                <br />
                <br />
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
    
<?php } ?>
