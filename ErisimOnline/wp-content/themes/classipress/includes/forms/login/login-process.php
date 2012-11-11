<?php
/**
 * WordPress Login Process
 * Processes the login forms and returns errors/redirects to a page
 *
 *
 * @version 3.1
 * @author AppThemes
 * @package ClassiPress
 *
 */

if ( !function_exists('user_can') ) :
	function user_can( $user, $capability ) {
		if ( ! is_object( $user ) )
			$user = new WP_User( (int) $user );
		
		if ( ! $user || ! $user->ID )
			return false;
	
		$args = array_slice( func_get_args(), 2 );
		$args = array_merge( array( $capability ), $args );
	
		return call_user_func_array( array( &$user, 'has_cap' ), $args );
	}
endif;

function app_process_login_form() {

	global $posted;
	
	if ( isset( $_REQUEST['redirect_to'] ) )
		$redirect_to = $_REQUEST['redirect_to'];
	else
		$redirect_to = admin_url();
	
	if ( is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
		$secure_cookie = false;
	else
		$secure_cookie = '';

	$user = wp_signon( '', $secure_cookie );

	$redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

	if ( !is_wp_error($user) ) {
	
		// automatically redirect admins to the WP back-end
		if ( user_can($user, 'manage_options') )
			$redirect_to = admin_url('admin.php?page=admin-options.php');

		// otherwise redirect them to the hidden post url	
		wp_safe_redirect($redirect_to);
		exit;
	}

	$errors = $user;

	return $errors;

}