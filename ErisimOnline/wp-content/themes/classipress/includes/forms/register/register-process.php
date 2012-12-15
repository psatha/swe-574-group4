<?php
/**
 * WordPress Register Process
 * Processes the registration forms and returns errors/redirects to a page
 *
 *
 * @version 1.0
 * @author AppThemes
 * @package ClassiPress
 *
 */

function app_process_register_form( $success_redirect = '' ) {

	if (!$success_redirect) $success_redirect = CP_DASHBOARD_URL;

	if ( get_option('users_can_register') ) :

		global $posted, $app_abbr;

		$posted = array();
		$errors = new WP_Error();
		$user_pass = wp_generate_password();

		if ( isset($_POST['register']) && $_POST['register'] ) {

			//Deprecated since WP 3.1. This file no longer needs to be included.
			//require_once( ABSPATH . WPINC . '/registration.php');

			// process the reCaptcha request if it's been enabled
			if ( get_option($app_abbr.'_captcha_enable') == 'yes' ) {
				require_once ( TEMPLATEPATH . '/includes/lib/recaptchalib.php' );
				$resp = null;
				$error = null;

				// check and make sure the reCaptcha values match
				$resp = recaptcha_check_answer(
					get_option($app_abbr.'_captcha_private_key'), 
					$_SERVER["REMOTE_ADDR"], 
					$_POST["recaptcha_challenge_field"], 
					$_POST["recaptcha_response_field"]
				);
			}

			// Get (and clean) data
			$fields = array(
				'user_login',
				'user_email',
				'your_password',
				'your_password_2'
			);

			foreach ( $fields as $field ) {
				if( isset($_POST[$field]) )
				    $posted[$field] = stripslashes( trim($_POST[$field]) );
			}

			$user_login = sanitize_user( $posted['user_login'] );
			$user_email = apply_filters( 'user_registration_email', $posted['user_email'] );


			// Check the username
			if ( $posted['user_login'] == '' )
				$errors->add('empty_username', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Please enter a username.', 'appthemes'));
			elseif ( !validate_username( $posted['user_login'] ) ) {
				$errors->add('invalid_username', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Username is invalid. Please enter a valid username.', 'appthemes'));
				$posted['user_login'] = '';
			} elseif ( username_exists( $posted['user_login'] ) )
				$errors->add('username_exists', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Username is already in use. Please choose another one.', 'appthemes'));

			// Check the e-mail address
			if ($posted['user_email'] == '') {
				$errors->add('empty_email', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Please enter an email address.', 'appthemes'));
			} elseif ( !is_email( $posted['user_email'] ) ) {
				$errors->add('invalid_email', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Email address format is invalid.', 'appthemes'));
				$posted['user_email'] = '';
			} elseif ( email_exists( $posted['user_email'] ) )
				$errors->add('email_exists', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Email address is already in use. Please choose another one.', 'appthemes'));

			// Check Passwords match only if the option is enabled
			if ( get_option($app_abbr.'_allow_registration_password') == 'yes' ) :

				if ($posted['your_password'] == '')	
					$errors->add('empty_password', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Please enter a password.', 'appthemes'));
				elseif ($posted['your_password_2'] == '')
					$errors->add('empty_password', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Please enter the password twice.', 'appthemes'));
				elseif ($posted['your_password'] !== $posted['your_password_2'])
					$errors->add('wrong_password', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('Passwords do not match.', 'appthemes'));	

				$user_pass = $posted['your_password'];

			endif;

			// display the reCaptcha error msg if it's been enabled	
			if (get_option('cp_captcha_enable') == 'yes') {
				// Check reCaptcha  match
				if (!$resp->is_valid)
					$errors->add('invalid_captcha', '<strong>' . __('ERROR', 'appthemes') . '</strong>: ' . __('The reCaptcha anti-spam response was incorrect.', 'appthemes'));
					//$error = $resp->error;	
			}


			do_action('register_post', $posted['user_login'], $posted['user_email'], $errors);
			$errors = apply_filters( 'registration_errors', $errors, $posted['user_login'], $posted['user_email'] );

			if ( !$errors->get_error_code() ) {

				// create the new user. If user set password pass it in, otherwise a WP generated password is created from wp_generate_password() above
				$user_id = wp_create_user(  $posted['user_login'], $user_pass, $posted['user_email'] );

				if ( !$user_id ) {
					$errors->add('registerfail', sprintf(__('Registration failed. Please contact the <a href="mailto:%s">webmaster</a> !', 'appthemes'), get_option('admin_email')));
					return array( 'errors' => $errors, 'posted' => $posted);
				}

				// Change role
				// wp_update_user( array ('ID' => $user_id, 'role' => 'contributor') ) ;
				
				// EKLEME (USER ENGELI EKLE)
				//require_once('./dbconnect.php');
				$con = mysql_connect("localhost","root","");
				mysql_select_db("erisimdb", $con);
				$disSql = "INSERT INTO er_disability_user (user_id,disability_id) VALUES(".$user_id.",".$_POST['engelDurumu'].")";
				mysql_query($disSql);
				mysql_close($con);

				// set the first login date/time
				appthemes_first_login( $user_id );

				// send the user a confirmation and their login details
				app_new_user_notification( $user_id, $user_pass );

				// check to see if user set password option is enabled
				if ( get_option($app_abbr.'_allow_registration_password') == 'yes' ) :

					// set the WP login cookie
					$secure_cookie = is_ssl() ? true : false;					
					wp_set_auth_cookie($user_id, true, $secure_cookie);
					wp_redirect($success_redirect);
					exit;

				else :

					//create own password option is turned off so show a message that it's been emailed instead
					$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : '?checkemail=newpass';
					wp_safe_redirect( $redirect_to );
					exit();

				endif;


			} else {
				return array( 'errors' => $errors, 'posted' => $posted );
			}
		}

	endif;

}