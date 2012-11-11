<?php
/**
 * These are scripts used within the theme
 * To increase speed and performance, we only want to
 * load them when needed
 *
 * @package ClassiPress
 *
 */


// correctly load all the jquery scripts so they don't conflict with plugins
if ( !function_exists('cp_load_scripts') ) :
function cp_load_scripts() {
    global $app_abbr;

	// load google cdn hosted scripts if enabled
    if ( get_option($app_abbr.'_google_jquery') == 'yes' ) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', ('http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js'), false, '1.6.1');
	}

	wp_enqueue_script('jquery'); // load no matter what
	
	// needed for single ad sidebar email & comments on pages, edit ad & profile pages, ads, blog posts
	if ( is_singular() )
        wp_enqueue_script('validate', get_bloginfo('template_directory').'/includes/js/validate/jquery.validate.min.js', array('jquery'), '1.8.1');
    
    // search autocomplete and slider on certain pages
	wp_enqueue_script('autocomplete', get_bloginfo('template_directory').'/includes/js/ui.autocomplete.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), '1.8.5');
	    
	// advanced search sidebar and home page carousel
	wp_enqueue_script('jquery-ui-slider', get_bloginfo('template_directory').'/includes/js/ui.slider.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-mouse', 'jquery-ui-widget'), '1.8.13');   
	    
	wp_enqueue_script('selectbox', get_bloginfo('template_directory').'/includes/js/selectbox.min.js', array('jquery'), '1.0.7');
	
    
    if ( get_option($app_abbr.'_enable_featured') == 'yes' && is_home() ) {
        wp_enqueue_script('jqueryeasing', get_bloginfo('template_directory').'/includes/js/easing.js', array('jquery'), '1.3');
        wp_enqueue_script('jcarousellite', get_bloginfo('template_directory').'/includes/js/jcarousellite_1.0.1.js', array('jquery', 'jquery-ui-slider'), '1.0.1');
    }
    
    wp_enqueue_script('theme-scripts', get_bloginfo('template_directory').'/includes/js/theme-scripts.js', array('jquery'), '3.1');
    wp_enqueue_script('superfish', get_bloginfo('template_directory').'/includes/js/superfish.js', array('jquery'), '1.4.8');

	// only load the general.js if it's been enabled
    if ( get_option($app_abbr.'_general_js') == 'yes' )
		wp_enqueue_script('general', get_bloginfo('template_directory').'/includes/js/general.js', array('jquery'), '1.0');

    // only load cufon if it's been enabled
    if ( get_option($app_abbr.'_cufon_enable') == 'yes' )
        wp_enqueue_script('cufon-yui', get_bloginfo('template_directory').'/includes/js/cufon-yui.js', array('jquery'), '1.0.9');

    // only load colorbox & gmaps when we need it
    if ( is_singular(APP_POST_TYPE) ) {
        $cp_gmaps_lang = esc_attr( get_option('cp_gmaps_lang') );
	    $cp_gmaps_region = esc_attr( get_option('cp_gmaps_region') );
        wp_enqueue_script('colorbox', get_bloginfo('template_directory').'/includes/js/colorbox/jquery.colorbox-min.js', array('jquery'), '1.3.15');
        wp_enqueue_script('google-maps', "http://maps.google.com/maps/api/js?sensor=false&amp;language=$cp_gmaps_lang&amp;region=$cp_gmaps_region", array('jquery'), '3.0');
    }    
}
endif;


// this function is called when submitting a new ad listing in tpl-add-new.php
if ( !function_exists('cp_load_form_scripts') ) :
function cp_load_form_scripts() {
    global $app_abbr;

    // only load the tinymce editor when html is allowed
    if ( get_option($app_abbr.'_allow_html') == 'yes' ) {
        wp_enqueue_script('tiny_mce', get_bloginfo('url').'/wp-includes/js/tinymce/tiny_mce.js', array('jquery'), '3.0');
        wp_enqueue_script('wp-langs-en', get_bloginfo('url').'/wp-includes/js/tinymce/langs/wp-langs-en.js', array('jquery'), '3241-1141');
    }
    wp_enqueue_script('validate', get_bloginfo('template_directory').'/includes/js/validate/jquery.validate.min.js', array('jquery'), '1.8.1');
	wp_enqueue_script('easytooltip', get_bloginfo('template_directory').'/includes/js/easyTooltip.js', array('jquery'), '1.0');

    // add the language validation file if not english
    if ( get_option($app_abbr.'_form_val_lang') ) {
        $lang_code = strtolower( get_option($app_abbr.'_form_val_lang') );
        wp_enqueue_script('validate-lang', get_bloginfo('template_directory')."/includes/js/validate/localization/messages_$lang_code.js", array('jquery'), '1.6');
    }
}
endif;



// changes the css file based on what is selected on the options page
if ( !function_exists('cp_style_changer') ) : 
function cp_style_changer() {

	wp_register_style('at-main', get_bloginfo('stylesheet_url'), false);
    wp_enqueue_style('at-main');
	
    // turn off stylesheets if customers want to use child themes
    if ( get_option('cp_disable_stylesheet') <> 'yes' ) {
    
        if ( get_option('cp_stylesheet') ) {  
            wp_register_style('at-color', get_bloginfo('template_directory')."/styles/" . get_option('cp_stylesheet'), false);
            wp_enqueue_style('at-color');
        } else {
            wp_register_style('at-color', get_bloginfo('template_directory')."/styles/red.css", false);
            wp_enqueue_style('at-color');
        }
    }

	if ( file_exists(TEMPLATEPATH . '/styles/custom.css') )
	    wp_register_style('at-custom', get_bloginfo('template_directory')."/styles/custom.css", false);
        wp_enqueue_style('at-custom');

}
endif;



// load the css files correctly
if ( !function_exists('cp_load_styles') ) :
function cp_load_styles() {

    if ( is_singular(APP_POST_TYPE) ) { // only load colorbox when we need it
        wp_register_style('colorbox', get_bloginfo('template_directory').'/includes/js/colorbox/colorbox.css', false, '1.3.15');
        wp_enqueue_style('colorbox');
    }
		wp_register_style('autocomplete', get_bloginfo('template_directory').'/includes/js/jquery-ui/jquery-ui-1.8.5.autocomplete.css', false, '1.8.5');
		wp_enqueue_style('autocomplete');

}
endif;


// enqueue login page styles
function cp_login_styles() {

	if ( file_exists(STYLESHEETPATH . '/styles/login-style.css') )
		wp_enqueue_style( 'login-style', get_bloginfo( 'stylesheet_directory' ) . '/styles/login-style.css', false );
	else
		wp_enqueue_style( 'login-style', get_bloginfo( 'template_directory' ) . '/styles/login-style.css', false );

}


// to speed things up, don't load these scripts in the WP back-end (which is the default)
if ( !is_admin() ) {
    add_action('wp_print_styles', 'cp_style_changer');
    add_action('wp_print_styles', 'cp_load_styles');
    add_action('wp_print_scripts', 'cp_load_scripts');
}


?>
