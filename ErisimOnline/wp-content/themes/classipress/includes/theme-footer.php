<?php

/**
 * Add footer elements via the wp_footer hook
 *
 * Anything you add to this file will be dynamically
 * inserted in the footer of your theme
 *
 * @since 3.0.0
 * @uses cp_footer_actions
 *
 */
 
 
/**
 * add the footer contents to the bottom of the page 
 * @since 3.1
 */
function cp_do_footer() {
?> 

<div class="footer">
    
    <div class="footer_menu">

        <div class="footer_menu_res">

            <ul>
                <li class="first"><a href="<?php echo get_option('home')?>"><?php _e('Home','appthemes'); ?></a></li>
                <?php wp_list_pages( 'sort_column=menu_order&depth=1&title_li=&exclude='.get_option('cp_excluded_pages') ); ?>              
            </ul>

            <div class="clr"></div>

        </div><!-- /footer_menu_res -->
        
    </div><!-- /footer_menu -->

    <div class="footer_main">

        <div class="footer_main_res">

            <div class="dotted">

                <?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_footer') ) : else : ?> <!-- no dynamic sidebar so don't do anything --> <?php endif; ?>

                <div class="clr"></div>
          
            </div><!-- /dotted -->

            <p>&copy; <?php echo date_i18n('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All Rights Reserved.', 'appthemes'); ?></p>
        
            <?php if ( get_option('cp_twitter_username') ) : ?>
                <a href="http://twitter.com/<?php echo get_option('cp_twitter_username'); ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/twitter_bot.gif" width="42" height="50" alt="Twitter" class="twit" /></a>
            <?php endif; ?>

            <div class="right">
                <p><a target="_blank" href="http://www.appthemes.com/themes/classipress/" title="Classified Ads Software"><?php _e('Classified Ads Software','appthemes'); ?></a> | <?php _e('Powered by','appthemes'); ?> <a target="_blank" href="http://wordpress.org/" title="WordPress">WordPress</a></p>
            </div>

            <div class="clr"></div>
        
        </div><!-- /footer_main_res -->

    </div><!-- /footer_main -->

</div><!-- /footer --> 
 
<?php
}
add_action('appthemes_footer', 'cp_do_footer'); 


// insert the google analytics tracking code in the footer
function cp_google_analytics_code() {

	// echo "\n\n" . '<!-- start wp_footer -->' . "\n\n";

	if ( get_option('cp_google_analytics') <> '' )
		echo stripslashes( get_option('cp_google_analytics') );
	?>
	<script type="text/javascript" >
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );  ?>';
	</script>
	<?php

	// echo "\n\n" . '<!-- end wp_footer -->' . "\n\n";

}
add_action('wp_footer', 'cp_google_analytics_code');


// enable the gravatar hovercards in footer
function cp_gravatar_hovercards() {
	global $app_abbr;

    if ( get_option($app_abbr.'_use_hovercards') == 'yes' )
		wp_enqueue_script( 'gprofiles', 'http://s.gravatar.com/js/gprofiles.js', array( 'jquery' ), '1.0', true );

}

add_action('wp_enqueue_scripts', 'cp_gravatar_hovercards');


?>