<?php
/*
Template Name: User Profile
*/

auth_redirect_login(); // if not logged in, redirect to login page
nocache_headers();

global $errmsg;
$current_user = wp_get_current_user(); // grabs the user info and puts into vars
$display_user_name = cp_get_user_name();


// check to see if the form has been posted. If so, validate the fields
if ( !empty($_POST['submit']) ) {

    if ( defined('ABSPATH') ) {
        require_once(ABSPATH . 'wp-admin/includes/user.php');
    } else {
        require_once('../wp-admin/includes/user.php');
    }

    //Deprecated since WP 3.1. This file no longer needs to be included.
    //require_once( ABSPATH . WPINC . '/registration.php' );

    check_admin_referer( 'update-profile_' . $user_ID );

    $errors = edit_user( $user_ID );
    

    if ( is_wp_error( $errors ) ) {
        foreach ( $errors->get_error_messages() as $message )
            $errmsg = "$message";
            //exit;
    }


    // if there are no errors, then process the profile updates
    if ( $errmsg == '' ) {
        // update the user fields
        do_action( 'personal_options_update', $user_ID );

        // update the custom user fields
        update_user_meta( $user_ID, 'twitter_id', sanitize_text_field( $_POST['twitter_id'] ) );
        update_user_meta( $user_ID, 'facebook_id', sanitize_text_field( $_POST['facebook_id'] ) );
        update_user_meta( $user_ID, 'paypal_email', sanitize_text_field( $_POST['paypal_email'] ) );
        
        wp_redirect( './?updated=true' );

    } else {

        $errmsg = '<div class="box-red"><strong>**  ' . $errmsg . ' **</strong></div>';
        $errcolor = 'style="background-color:#FFEBE8;border:1px solid #CC0000;"';
    }
}	

?>

<?php // wp_enqueue_script('jquery'); ?>

<?php get_header(); ?>

<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-admin/js/password-strength-meter.js?ver=20081210'></script>

<script type="text/javascript">
// <![CDATA[
(function($){

	function check_pass_strength () {

		var pass = $('#pass1').val();
		var user = $('#user_login').val();

		$('#pass-strength-result').removeClass('short bad good strong');
		if ( ! pass ) {
			$('#pass-strength-result').html( pwsL10n.empty );
			return;
		}

		var strength = passwordStrength(pass, user, this);

		if ( 2 == strength )
			$('#pass-strength-result').addClass('bad').html( pwsL10n.bad );
		else if ( 3 == strength )
			$('#pass-strength-result').addClass('good').html( pwsL10n.good );
		else if ( 4 == strength )
			$('#pass-strength-result').addClass('strong').html( pwsL10n.strong );
		else
		// this catches 'Too short' and the off chance anything else comes along
			$('#pass-strength-result').addClass('short').html( pwsL10n.short );

	}

	$(document).ready( function() {
		$('#pass1').val('').keyup( check_pass_strength );
	});
})(jQuery);


pwsL10n = {
        empty: "<?php _e('Strength indicator','appthemes') ?>",
        short: "<?php _e('Very weak','appthemes') ?>",
        bad: "<?php _e('Weak','appthemes') ?>",
        good: "<?php _e('Medium','appthemes') ?>",
        strong: "<?php _e('Strong','appthemes') ?>"
    }
    try{convertEntities(pwsL10n);}catch(e){};


/* initialize the form validation */
jQuery(document).ready(function($) {	
    $("#your-profile").validate({errorClass: "invalid"});
});	
/* ]]> */
</script>


<!-- CONTENT -->
  <div class="content">

    <div class="content_botbg">

      <div class="content_res">


        <!-- left block -->
        <div class="content_left">

            <div class="shadowblock_out">
            <div class="shadowblock">

				<h1 class="single dotted"><?php printf( __('%s\'s Profile', 'appthemes'), esc_html( $display_user_name ) ); ?></h1>



        <?php if ( isset($_GET['updated']) ) { ?>
                <div class="box-yellow"><strong><?php _e('Your profile has been updated.','appthemes')?></strong><br /></div>
                <br />
        <?php  } ?>


        <?php echo $errmsg; ?>


		<form name="profile" id="your-profile" action="" method="post">
		<?php wp_nonce_field( 'update-profile_' . $user_ID ); ?>
		
		<input type="hidden" name="from" value="profile" />
		<input type="hidden" name="checkuser_id" value="<?php echo $user_ID ?>" />


		<table class="form-table">
			<tr>
				<th><label for="user_login"><?php _e('Username:', 'appthemes'); ?></label></th>
				<td><input type="text" name="user_login" class="regular-text" id="user_login" value="<?php echo esc_attr( $current_user->user_login ); ?>" maxlength="100" disabled /></td>
			</tr>
			<tr>
				<th><label for="first_name"><?php _e('First Name:', 'appthemes'); ?></label></th>
				<td><input type="text" name="first_name" class="regular-text" id="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>" maxlength="100" /></td>
			</tr>
			<tr>
				<th><label for="last_name"><?php _e('Last Name:', 'appthemes'); ?></label></th>
				<td><input type="text" name="last_name" class="regular-text" id="last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>" maxlength="100" /></td>
			</tr>
			<tr>
				<th><label for="nickname"><?php _e('Nickname:', 'appthemes'); ?></label></th>
				<td><input type="text" name="nickname" class="regular-text" id="nickname" value="<?php echo esc_attr( $current_user->nickname ); ?>" maxlength="100" /></td>
			</tr>
			<tr>
				<th><label for="display_name"><?php _e('Display Name:', 'appthemes'); ?></label></th>
				<td>
					<select name="display_name" class="regular-dropdown" id="display_name">
					<?php
						$public_display = array();
						$public_display['display_displayname'] = esc_attr($current_user->display_name);
						$public_display['display_nickname'] = esc_attr($current_user->nickname);
						$public_display['display_username'] = esc_attr($current_user->user_login);
						$public_display['display_firstname'] = esc_attr($current_user->first_name);
						$public_display['display_firstlast'] = esc_attr($current_user->first_name) . '&nbsp;' . esc_attr($current_user->last_name);
						$public_display['display_lastfirst'] = esc_attr($current_user->last_name) . '&nbsp;' . esc_attr($current_user->first_name);
						$public_display = array_unique(array_filter(array_map('trim', $public_display)));
						foreach($public_display as $id => $item) {
					?>
						<option id="<?php echo $id; ?>" value="<?php echo esc_attr($item); ?>"><?php echo esc_attr($item); ?></option>
					<?php
						}
					?>
					</select>
				</td>
			</tr>

		<tr>
			<th><label for="email"><?php _e('Email:', 'appthemes'); ?></label></th>
			<td><input type="text" name="email" class="regular-text" id="email" value="<?php echo esc_attr($current_user->user_email); ?>" maxlength="100" /></td>
		</tr>


		<tr>
			<th><label for="disability">Engel Durumunuz:</label></th>
            <td><select name="engelUserUpdate" >
                <?php
					// EKLEME DISABILITY TYPE GETIRMEK ICIN
					require_once('./dbconnect.php');
					//Get Disability IDs
					$sqlDis = "SELECT disability_id FROM er_disability_user where user_id = ".$current_user->ID;
					$disResult = dbconnection($sqlDis);
					$i=0;
					while ($row = mysql_fetch_array($disResult)) {
						$disability[$i] = $row['disability_id'];
						$i++;
					}
					
					$sql = "SELECT * FROM er_disability";
					$result = dbconnection($sql);
					while($row = mysql_fetch_array($result))
					{
						if ($disability != null && in_array($row['ID'], $disability)) {
    						echo "<option selected='selected' value='".$row['ID']."'/>".$row['name']."</option>";
						}else{
							echo "<option value='".$row['ID']."'/>".$row['name']."</option>";	
						}
  					}
				?>
            </select></td>
		</tr>

		<tr>
			<th><label for="description"><?php _e('About Me:','appthemes'); ?></label></th>
			<td><textarea name="description" class="regular-text" id="description" rows="10" cols="50"><?php echo esc_textarea($current_user->description); ?></textarea></td>
		</tr>

		<?php
		$show_password_fields = apply_filters('show_password_fields', true);
		if ( $show_password_fields ) :
		?>

		<tr>
			<th><label for="pass1"><?php _e('New Password:','appthemes'); ?></label></th>
			<td>
				<input type="password" name="pass1" class="regular-text" id="pass1" maxlength="50" value="" /><br />
				<span class="description"><?php _e('Leave this field blank unless you would like to change your password.','appthemes'); ?></span>
			</td>
		</tr>
		<tr>
		<th><label for="pass1"><?php _e('Password Again:','appthemes'); ?></label></th>
			<td>
				<input type="password" name="pass2" class="regular-text" id="pass2" maxlength="50" value="" /><br />
				<span class="description"><?php _e('Type your new password again.','appthemes'); ?></span>
			</td>
		</tr>
		<tr>
		<th><label for="pass1">&nbsp;</label></th>
			<td>	
				<div id="pass-strength-result"><?php _e('Strength indicator','appthemes'); ?></div><br /><br /><br />
				<span class="description"><?php _e('Your password should be at least seven characters long.','appthemes'); ?></span>
			</td>
		</tr>

		<?php endif; ?>

		</table>

		<br />

		<?php
		//do_action('profile_personal_options', $current_user);
		//do_action('show_user_profile', $current_user);
		?>

		<table class="form-table" id="userphoto">
			<tr>
				<th><label for="user_login">&nbsp;</label></th>
				<td><?php if (function_exists('userphoto_exists')) { ?><p class='image'><?php if ( userphoto_exists($current_user->ID) ) userphoto_thumbnail($current_user->ID); else echo get_avatar($current_user->user_email, 96); ?><br /><?php _e('Thumbnail','appthemes') ?></p><?php } ?></td>
			</tr>
		</table>

		<br />
                
		<?php if ( !empty($current_user->userphoto_image_file) ) : ?>
			<table class="form-table">
				<tr>
					<th>&nbsp;</th>
					<td>
						<p><label><input type="checkbox" name="userphoto_delete" id="userphoto_delete" /> <?php _e('Delete existing photo?','appthemes') ?></label></p>
					</td>
				</tr>
			</table>
		<?php endif; ?>
	
		<p class="submit center">
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="user_id" id="user_id" value="<?php echo $user_ID; ?>" />
			<input type="hidden" name="admin_color" value="<?php echo esc_attr( $current_user->admin_color ); ?>" />
			<input type="hidden" name="rich_editing" value="<?php echo esc_attr( $current_user->rich_editing ); ?>" />
			<input type="hidden" name="comment_shortcuts" value="<?php echo esc_attr( $current_user->comment_shortcuts ); ?>" />
			
			<?php if ( !empty($current_user->admin_bar_front) ) { ?>
			    <input type="hidden" name="admin_bar_front" value="<?php echo esc_attr( $current_user->admin_bar_front ); ?>" />
			<?php } ?>
			
			<?php if ( !empty($current_user->admin_bar_admin) ) { ?>
			    <input type="hidden" name="admin_bar_admin" value="<?php echo esc_attr( $current_user->admin_bar_admin ); ?>" />
			<?php } ?>
			
			<input type="submit" id="cpsubmit" class="btn_orange" value="<?php _e('Update Profile &raquo;', 'appthemes')?>" name="submit" />
		 </p>
		</form>



            </div><!-- /shadowblock -->

            </div><!-- /shadowblock_out -->



        </div><!-- /content_left -->


        <?php get_sidebar( 'user' ); ?>

        <div class="clr"></div>



      </div><!-- /content_res -->

    </div><!-- /content_botbg -->

  </div><!-- /content -->


<?php get_footer(); ?>
