<?php 
global $current_user; 

$display_user_name = cp_get_user_name();
$logout_url = cp_logout_url();
?>

<!-- right sidebar -->
<div class="content_right">

	<div class="shadowblock_out">

		<div class="shadowblock">

			<h2 class="dotted"><?php _e('User Options','appthemes')?></h2>

			<div class="recordfromblog">

				<?php if(has_nav_menu('theme_dashboard')) : wp_nav_menu(array('theme_location' => 'theme_dashboard', 'container' => false)); else : ?>

				<ul>

					<li><a href="<?php echo CP_DASHBOARD_URL ?>"><?php _e('My Dashboard','appthemes')?></a></li>
					<li><a href="<?php echo CP_PROFILE_URL ?>"><?php _e('Edit Profile','appthemes')?></a></li>
					<?php if (current_user_can('edit_others_posts')) { ?><li><a href="<?php echo get_option('siteurl'); ?>/wp-admin/"><?php _e('WordPress Admin','appthemes')?></a></li><?php } ?>
					<li><a href="<?php echo $logout_url; ?>"><?php _e('Log Out','appthemes')?></a></li>
				</ul>

				<?php endif; ?>

			</div><!-- /recordfromblog -->

		</div><!-- /shadowblock -->

	</div><!-- /shadowblock_out -->



	<div class="shadowblock_out">

		<div class="shadowblock">

			<h2 class="dotted"><?php _e('Account Information','appthemes')?></h2>

				<div class="avatar"><?php appthemes_get_profile_pic($current_user->ID, $current_user->user_email, 60) ?></div>

				<ul class="user-info">
					<li><h3 class="single"><a href="<?php echo get_author_posts_url($current_user->ID); ?>"><?php echo $display_user_name; ?></a></h3></li>
					<li><strong><?php _e('Member Since:','appthemes')?></strong> <?php echo appthemes_get_reg_date($current_user->user_registered); ?></li>
					<li><strong><?php _e('Last Login:','appthemes'); ?></strong> <?php echo appthemes_get_last_login($current_user->ID); ?></li>
				</ul>

				<ul class="user-details">
					<li><div class="emailico"></div><a href="mailto:<?php echo $current_user->user_email; ?>"><?php echo $current_user->user_email; ?></a></li>
				</ul>

		</div><!-- /shadowblock -->

	</div><!-- /shadowblock_out -->


	<?php appthemes_before_sidebar_widgets(); ?>

	<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_user') ) : else : ?>

	<!-- no dynamic sidebar so don't do anything -->

	<?php endif; ?>

	<?php appthemes_after_sidebar_widgets(); ?>

</div><!-- /content_right -->
