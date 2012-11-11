<?php
/**
 * Function to prevent visitors without admin permissions
 * to access the wordpress backend. If you wish to permit
 * others besides admins acces, change the user_level
 * to a different number.
 *
 * http://codex.wordpress.org/Roles_and_Capabilities#level_8
 *
 * @global <type> $user_level
 *
 * in order to use this for wpmu, you need to follow the comment
 * instructions below in all locations and make the changes
 */

function cp_security_check() {

    $cp_access_level = get_option('cp_admin_security');
    if (!isset($cp_access_level) || $cp_access_level == '') $cp_access_level = 'read'; // if there's no value then give everyone access

    if (!current_user_can($cp_access_level)) {

    // comment out the above two lines and uncomment this line if you are using
    // wpmu and want to block back office access to everyone except admins
    // if (!is_site_admin()) {

?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html>

            <head>
                <title><?php _e('Access Denied.', 'appthemes') ?></title>
                <link rel="stylesheet" href="<?php bloginfo('url'); ?>/wp-admin/css/install.css" type="text/css" />
            </head>

            <body id="error-page">

                <p><?php _e('Access Denied. Your site administrator has blocked your access to the WordPress back-office.', 'appthemes') ?></p>

            </body>

        </html>

<?php
	echo strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
        exit();

    }

}

// if people are having trouble with this option, they can disable it
if (get_option('cp_admin_security') != 'disable') {
    
	// check and make sure security option is enabled and the request is not ajax which is used for search auto-complete
    if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
		//do not lock down xmlhttprequest calls
	}
	else {
		// comment out the below line to work with wpmu
		if (!appthemes_is_wpmu())
			add_action('admin_init', 'cp_security_check', 1);
	}	
}
?>