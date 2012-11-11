<?php

/**
 * These are the admin alert messages displayed
 * on the WordPress admin pages
 * 
 * 
 */
global $app_version;


function cp_admin_info_box() {

    // reserved for future use    

}			


// display nag if paypal sandbox mode is turned on
function cp_paypal_nag() {

	if (get_option('cp_paypal_sandbox') != 'true') return;
	
    if (!current_user_can('manage_options')) return;

    echo "<div class='error fade'><p>". sprintf( __('ClassiPress is currently running in PayPal Sandbox mode. Remember to <a href="%1$s">uncheck the box</a> when you are ready to start selling ads again.', 'appthemes'), 'admin.php?page=gateways') ." </p></div>";
    
}
add_action('admin_notices', 'cp_paypal_nag', 3);



// help text from slide down menu for WP < 3.3
function cp_contextual_help_settings() {
   
   add_contextual_help('toplevel_page_admin-options',
   '<h3>' . __('ClassiPress Help', 'appthemes') . '</h3>' .
	'<p>' . __('Detailed help information coming soon.', 'appthemes') . '</p>' .
	'<p>' . __('<a href="http://www.appthemes.com/support/docs/" target="_blank">Documentation</a>', 'appthemes') .
	'<br />' . __('<a href="http://forums.appthemes.com/" target="_blank">Support Forums</a>', 'appthemes') . '</p>'
	);

}

// help text from slide down menu for WP >= 3.3
function cp_add_help_tab_dashboard () {
  $screen = get_current_screen();

  if ( $screen->id != 'toplevel_page_admin-options' )
      return;

  $screen->add_help_tab( array(
      'id'	=> 'cp_dashboard_help_tab',
      'title'	=> __('ClassiPress Help', 'appthemes'),
      'content'	=> '<p>' . __('Detailed help information coming soon.', 'appthemes') . '</p>',
  ) );

  $screen->set_help_sidebar(
    	'<p>' . __('<a href="http://www.appthemes.com/support/docs/" target="_blank">Documentation</a>', 'appthemes') .
    	'<br />' . __('<a href="http://forums.appthemes.com/" target="_blank">Support Forums</a>', 'appthemes') . '</p>'
  );
}
if ( version_compare($wp_version, '3.3', '>=') )
  add_action( 'load-toplevel_page_admin-options', 'cp_add_help_tab_dashboard' );
else
  add_action( 'admin_init', 'cp_contextual_help_settings' );



// enable to see the current screen name
function dev_check_current_screen() {
	if( !is_admin() ) return;
 
	global $current_screen;
 
	print_r($current_screen);
}
// add_action( 'admin_notices', 'dev_check_current_screen' );


?>