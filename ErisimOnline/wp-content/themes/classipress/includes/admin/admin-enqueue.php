<?php
/**
 * These are scripts used within the AppThemes admin pages
 *
 * @package AppThemes
 *
 */


// correctly load all the scripts so they don't conflict with plugins
function appthemes_load_admin_scripts() {
    global $pagenow;
    
    wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('media-upload'); // needed for image upload
	wp_enqueue_script('thickbox'); // needed for image upload
	wp_enqueue_style('thickbox'); // needed for image upload
	

    //wp_deregister_script('jquery-ui-core'); wp_deregister_script( 'jquery-ui-sortable' ); wp_deregister_script( 'jquery-ui-draggable' );  // this version included with WP doesn't work with datepicker
    //wp_register_script('jquery-ui-core', get_bloginfo('template_directory').'/includes/js/jquery-ui/jquery-ui-1.8.5.nosort-nodragdrop.min.js');
    wp_register_script('jquery-ui-sortable', get_bloginfo('template_directory').'/includes/js/jquery-ui/jquery-ui-1.8.5.sortonly.min.js', array('jquery-ui-core'));
    wp_register_script('jquery-ui-draggable', get_bloginfo('template_directory').'/includes/js/jquery-ui/jquery-ui-1.8.5.draggable.min.js', array('jquery-ui-core'));
    wp_register_script('jquery-ui-droppable', get_bloginfo('template_directory').'/includes/js/jquery-ui/jquery-ui-1.8.5.droppable.min.js', array('jquery-ui-draggable'));

    //TODO: For now we call these on all admin pages because of some javascript errors, however it should be registered per admin page (like wordpress does it)
    wp_enqueue_script('jquery-ui-sortable'); //this script has issues on the page edit.php?post_type=ad_listing
    
    
    //timepicker requires datepicker in order to work
    wp_enqueue_script('datepicker', get_bloginfo('template_directory').'/includes/js/ui.datepicker.js', array('jquery'), '1.2');
	wp_enqueue_script('jqueryslider', get_bloginfo('template_directory').'/includes/js/ui.slider.min.js', array('jquery-ui-core'), '1.8.12', true); // load in the footer after the widget and mouse js are loaded by WP
    wp_enqueue_script('timepicker', get_bloginfo('template_directory').'/includes/js/timepicker.min.js', array('jquery-ui-core'), '0.7');
	
    wp_register_style('jquery-ui-custom', get_bloginfo('template_directory').'/includes/js/jquery-ui/jquery-ui-1.8.5.custom.css', false, '1.8.5');
    wp_enqueue_style('jquery-ui-custom');

    wp_enqueue_script('easytooltip', get_bloginfo('template_directory').'/includes/js/easyTooltip.js', array('jquery'), '1.0');
	
	if ($pagenow == 'admin.php') // only trigger this on CP edit pages otherwise it causes a conflict with edit ad and edit post meta field buttons
		wp_enqueue_script('validate', get_bloginfo('template_directory').'/includes/js/validate/jquery.validate.min.js', array('jquery-ui-core'), '1.6');
	
    wp_enqueue_script('admin-scripts', get_bloginfo('template_directory').'/includes/admin/admin-scripts.js', array('jquery','media-upload','thickbox'), '1.2');

	wp_enqueue_script('excanvas', get_bloginfo('template_directory').'/includes/js/excanvas.min.js', array('jquery'), '1.0');
	wp_enqueue_script('flot', get_bloginfo('template_directory').'/includes/js/jquery.flot.min.js', array('excanvas'), '0.6');

    wp_register_style('admin-style', get_bloginfo('template_directory').'/includes/admin/admin-style.css', false, '3.0');
    wp_enqueue_style('admin-style');

    //wp_register_style('jqueryUI-style', get_bloginfo('template_directory').'/includes/js/jquery-ui/jquery-ui-1.8.5.smoothness.css', false, '1.8.5');
    //wp_enqueue_style('jqueryUI-style');
    
	
}


add_action('admin_enqueue_scripts', 'appthemes_load_admin_scripts');


?>