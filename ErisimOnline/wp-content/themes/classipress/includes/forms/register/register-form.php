<?php
/**
 * WordPress Registration Form
 * Function outputs the registration form
 *
 *
 * @author AppThemes
 * @package ClassiPress
 *
 */

function app_register_form( $action = '' ) {
    global $posted, $app_abbr;

    if ( get_option('users_can_register') ) :

        if (!$action) $action = site_url('login.php?action=register');
?>

            <form action="<?php echo $action; ?>" method="post" class="loginform" name="registerform" id="registerform">

                <p>
                    <label for="user_login"><?php _e('Username:','appthemes') ?></label>
                    <input tabindex="1" type="text" class="text" name="user_login" id="user_login" value="<?php if (isset($posted['user_login'])) echo esc_attr($posted['user_login']); ?>" />
                </p>

                <p>
                    <label for="user_email"><?php _e('Email:','appthemes') ?></label>
                    <input tabindex="2" type="text" class="text" name="user_email" id="user_email" value="<?php if (isset($posted['user_email'])) echo esc_attr($posted['user_email']); ?>" />
                </p>

      				<?php if (get_option($app_abbr.'_allow_registration_password') == 'yes') : ?>
      					<p>
      						<label for="your_password"><?php _e('Password:','appthemes') ?></label>
      						<input tabindex="3" type="password" class="text" name="your_password" id="your_password" value="" />
      					</p>

      					<p>
      						<label for="your_password_2"><?php _e('Password Again:','appthemes') ?></label>
      						<input tabindex="4" type="password" class="text" name="your_password_2" id="your_password_2" value="" />
      					</p>
      				<?php endif; ?>

                <?php 
      					// include the spam checker if enabled
      					appthemes_recaptcha();
        				?>
				<p>
                    <label for="disability">Engel Durumunuz:</label>
                    <select>
                    	<?php
							require_once('./dbconnect.php'); 
							$sql = "SELECT * FROM er_disability";
							$result = dbconnection($sql);
							while($row = mysql_fetch_array($result))
							{
								echo "<option value=".$row['ID']."/>".$row['name']."</option>";
  							}
						?>
                    </select>
                </p>
                <div id="checksave">

                    <p class="submit">
                        <input tabindex="6" class="btn_orange" type="submit" name="register" id="wp-submit" value="<?php _e('Create Account','appthemes'); ?>" />
                    </p>

										<?php do_action('register_form'); ?>

                </div>

            </form>

        <script type="text/javascript">document.getElementById('user_login').focus();</script> 	

<?php else : ?>

    <p><?php _e('** User registration is currently disabled. Please contact the site administrator. **', 'appthemes') ?></p>

<?php endif; ?>

<?php } ?>