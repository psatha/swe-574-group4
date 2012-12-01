<?php
/**
 *
 * This controls how the login, logout,
 * registration, and forgot your password pages look.
 * It overrides the default WP pages by intercepting the request.
 *
 * @package ClassiPress
 * @author AppThemes
 *
 */

global $pagenow;

// check to prevent php "notice: undefined index" msg
if ( isset($_GET['action']) ) 
    $theaction = $_GET['action']; 
else 
    $theaction = '';

// if the user is on the login page, then let the games begin
if ( $pagenow == 'login.php' && $theaction != 'logout' && !isset($_GET['key']) ) :
	add_action('init', 'app_login_init', 98);
	add_filter('wp_title', 'app_title');
	//run original login actions on custom login page
	add_action('wp_head', 'cp_do_login_head');
	add_action('wp_footer', 'cp_do_login_footer');
endif;

// if the user is on the reset password page, then style it
if ($pagenow == 'login.php' && ($theaction == 'rp' || $theaction == 'resetpass') && isset($_GET['key'])) :
	add_action( 'login_head', 'cp_login_styles' );
	add_action( 'login_head', 'cp_rp_page_style' );
	add_filter( 'login_headerurl', 'cp_rp_page_headerurl' );
	add_filter( 'login_headertitle', 'cp_rp_page_headertitle' );
	remove_action( 'wp_print_scripts', 'cp_load_scripts' );
endif;

// main function that routes the request
function app_login_init() {

	nocache_headers();
	
    if ( isset($_REQUEST['action']) ) :
        $action = $_REQUEST['action'];
    else :
        $action = 'login';
    endif;

		//Set a cookie now to see if they are supported by the browser.
		setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
		if ( SITECOOKIEPATH != COOKIEPATH )
				setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);

		// allow plugins to override the default actions, and to add extra actions if they want
		do_action( 'login_init' );
		do_action( 'login_form_' . $action );

    switch( $action ) :
        case 'lostpassword' :
        case 'retrievepassword' :
            app_show_password();
        break;
        case 'register':
            app_show_registration();
            break;
        case 'login':
        default:
            app_show_login();
        break;
    endswitch;
    exit;
}

// display the meta page title based on the current page
function app_title($title) {
		global $pagenow;
		$sitename = get_bloginfo( 'name' );
    $theaction = isset($_GET['action']) ? $_GET['action'] : ''; 

		if ( $pagenow == 'login.php' ) :

				switch( $theaction ) :
						case 'lostpassword':
								$title = sprintf( __('Retrieve your lost password for %s', 'appthemes'), $sitename );
						break;
						case 'login':
								$title = sprintf( __('Login at %s', 'appthemes'), $sitename );
						break;
						case 'register':
								$title = sprintf( __('Register at %s', 'appthemes'), $sitename );
						break;
						default:
								$title = sprintf( __('Login/Register at %s', 'appthemes'), $sitename );
						break;
				endswitch;

		elseif ( $pagenow == 'profile.php' ) :
				$title = sprintf( __('Your Profile at %s', 'appthemes'), $sitename );
		endif;

		return $title;
}

// Show registation form
function app_show_registration() {
	global $posted;
	
	if ( isset($_POST['register']) && $_POST['register'] ) {
		
            // redirect to ad creation page once they are registered
            $result = app_process_register_form(CP_ADD_NEW_URL);

            $errors = $result['errors'];
            $posted = $result['posted'];
		
	}

	// Clear errors if loggedout is set.
	if ( !empty($_GET['loggedout']) ) $errors = new WP_Error();

	// If cookies are disabled we can't log in even with a valid user+pass
        // causing problems so no cookie detecting setup. since 3.0.5
	//if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
        //    $errors->add('test_cookie', __('Cookies are blocked or not supported by your browser. You must enable cookies to continue.','appthemes'));
	
	if ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )
            $message = __('You are now logged out.','appthemes');

	elseif	( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )	
            $errors->add('registerdisabled', __('User registration is currently not allowed.','appthemes'));

	elseif	( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )	
            $message = __('Check your email for the confirmation link.','appthemes');

	elseif	( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )	
            $message = __('Check your email for your new password.','appthemes');

	elseif	( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )
            $message = __('Registration complete. Please check your e-mail.','appthemes');

	if ( file_exists(STYLESHEETPATH . '/header.php') )
            include_once(STYLESHEETPATH . '/header.php');
	else
            include_once(TEMPLATEPATH . '/header.php');
	?>
	<!-- CONTENT -->
        <div class="content">

            <div class="content_botbg">

                <div class="content_res">

                    <!-- full block -->
                    <div class="shadowblock_out">

                        <div class="shadowblock">

                        <h2 class="dotted"><span class="colour"><?php _e('Register', 'appthemes'); ?></span></h2>
			
						<?php 
							if ( isset($message) && !empty($message) ) {
								echo '<p class="success">'.$message.'</p>';
							}
						?>
						<?php 
						if ( isset($errors) && sizeof($errors)>0 && $errors->get_error_code() ) :
							echo '<ul class="errors">';
							foreach ($errors->errors as $error) {
								echo '<li>'.$error[0].'</li>';
							}
							echo '</ul>';
						endif; 
						?>

						<p><?php _e('Complete the fields below to create your free account. Your login details will be emailed to you for confirmation so make sure to use a valid email address. Once registration is complete, you will be able to submit your ads.', 'appthemes') ?></p>
					
						<div class="left-box">						

							<?php app_register_form(); ?>

						</div>	
						
						<div class="right-box">
	
						
	
						</div><!-- /right-box -->

						<div class="clr"></div>						
		    
						</div><!-- /shadowblock -->

					</div><!-- /shadowblock_out -->


			  </div><!-- /content_res -->

			</div><!-- /content_botbg -->

		  </div><!-- /content -->
			
<?php 
	
	if ( file_exists(STYLESHEETPATH . '/footer.php') )
		include_once(STYLESHEETPATH . '/footer.php');
	else
		include_once(TEMPLATEPATH . '/footer.php');

}



// Show registation form
function app_show_login() {

	global $posted;
	
	if ( isset($_POST['login']) && $_POST['login'] ) {
		
		$errors = app_process_login_form();
		
	}

	// Clear errors if loggedout is set.
	if ( !empty($_GET['loggedout']) ) $errors = new WP_Error();

	// If cookies are disabled we can't log in even with a valid user+pass
	//if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
	//		$errors->add('test_cookie', __('Cookies are blocked or not supported by your browser. You must enable cookies to continue.','appthemes'));
	
	if ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )
			$message = __('You are now logged out.','appthemes');

	elseif	( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )	
			$errors->add('registerdisabled', __('User registration is currently not allowed.','appthemes'));

	elseif	( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )	
			$message = __('Check your email for the confirmation link.','appthemes');

	elseif	( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )	
			$message = __('Check your email for your new password.','appthemes');

	elseif	( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )
			$message = __('Registration complete. Please check your e-mail.','appthemes');

	if ( file_exists(STYLESHEETPATH . '/header.php') )
		include_once(STYLESHEETPATH . '/header.php');
	else
		include_once(TEMPLATEPATH . '/header.php');	
	?>
	<!-- CONTENT -->
        <div class="content">

            <div class="content_botbg">

                <div class="content_res">

                    <!-- full block -->
                    <div class="shadowblock_out">

                        <div class="shadowblock">

                        <h2 class="dotted"><span class="colour"><?php _e('Login', 'appthemes'); ?></span></h2>
			
						<?php 
							if ( isset($message) && !empty($message) ) {
								echo '<p class="success">'.$message.'</p>';
							}
						?>
						<?php 
						if ( isset($errors) && sizeof($errors)>0 && $errors->get_error_code() ) :
							echo '<ul class="errors">';
							foreach ( $errors->errors as $error ) {
								echo '<li>'.$error[0].'</li>';
							}
							echo '</ul>';
						endif; 
						?>

						<p><?php _e('Please complete the fields below to login to your account.', 'appthemes') ?></p>
					
						<div class="left-box">						

							<?php app_login_form(); ?>

						</div>	
						
						<div class="right-box">
	
							<?php if(function_exists('app_login_rightbox')) app_login_rightbox(); ?>
	
						</div><!-- /right-box -->

						<div class="clr"></div>						
		    
						</div><!-- /shadowblock -->

					</div><!-- /shadowblock_out -->


			  </div><!-- /content_res -->

			</div><!-- /content_botbg -->

		  </div><!-- /content -->
			
<?php 
	
	if ( file_exists(STYLESHEETPATH . '/footer.php') )
		include_once(STYLESHEETPATH . '/footer.php');
	else
		include_once(TEMPLATEPATH . '/footer.php');

}



// show the forgot your password page
function app_show_password() {
    $errors = new WP_Error();

    if ( isset($_POST['user_login']) && $_POST['user_login'] ) {
        $errors = retrieve_password();

        if ( !is_wp_error($errors) ) {
            wp_redirect('login.php?checkemail=confirm');
            exit();
        }

    }

    if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] ) $errors->add('invalidkey', __('Sorry, that key does not appear to be valid.','appthemes'));

    do_action('lost_password');
    do_action('lostpassword_post');

    if ( file_exists(STYLESHEETPATH . '/header.php') )
		include_once(STYLESHEETPATH . '/header.php');
	else
		include_once(TEMPLATEPATH . '/header.php');
	?>
	<!-- CONTENT -->
        <div class="content">

            <div class="content_botbg">

                <div class="content_res">

                    <!-- full block -->
                    <div class="shadowblock_out">

                        <div class="shadowblock">

                        <h2 class="dotted"><span class="colour"><?php _e('Password Recovery', 'appthemes'); ?></span></h2>
			
	   		
						<?php 
							if (isset($message) && !empty($message)) {
								echo '<p class="success">'.$message.'</p>';
							}
						?>
						<?php 
						if ($errors && sizeof($errors)>0 && $errors->get_error_code()) :
							echo '<ul class="errors">';
							foreach ($errors->errors as $error) {
								echo '<li>'.$error[0].'</li>';
							}
							echo '</ul>';
						endif; 
						?>
						
						<p><?php _e('Please enter your username or email address. A new password will be emailed to you.', 'appthemes') ?></p>
						
						<div class="left-box">						

							<?php app_forgot_password_form(); ?>

						</div>	
						
						<div class="right-box">
	
	
						</div><!-- /right-box -->				

						<div class="clr"></div>						

					</div><!-- /shadowblock -->

					</div><!-- /shadowblock_out -->


			  </div><!-- /content_res -->

			</div><!-- /content_botbg -->

		  </div><!-- /content -->

            
<?php	
	if ( file_exists(STYLESHEETPATH . '/footer.php') )
		include_once(STYLESHEETPATH . '/footer.php');
	else
		include_once(TEMPLATEPATH . '/footer.php');
}


// replace header title on reset password page
function cp_rp_page_headertitle($title) {
	return get_bloginfo('name');
}


// replace header url on reset password page
function cp_rp_page_headerurl($url) {
	return get_bloginfo('wpurl');
}


// replace header logo, adds styles
function cp_rp_page_style() {
		if(get_option('cp_logo'))
			$logo_url = get_option('cp_logo');
		else
			$logo_url = get_bloginfo('template_directory').'/images/cp_logo_black.png';

		echo '<style type="text/css">
				.login h1 a { background-image: url('.$logo_url.') !important; }
				</style>';
}


?>