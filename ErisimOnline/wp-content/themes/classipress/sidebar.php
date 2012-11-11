<div class="content_right">

    <?php if ( is_home() ) {

				$current_user = wp_get_current_user();
				$display_user_name = cp_get_user_name();
        ?>

      <div class="shadowblock_out">

        <div class="shadowblock">

            <?php if ( !is_user_logged_in() ) : ?>

                <?php echo get_option('cp_ads_welcome_msg'); ?>          
                <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=register" class="mbtn btn_orange"><?php _e('Join Now!', 'appthemes') ?></a>

            <?php else: ?>

                <div class="avatar"><?php appthemes_get_profile_pic( $current_user->ID, $current_user->user_email, 60 ) ?></div>

                <div class="user">

                    <p class="welcome-back"><?php _e('Welcome back,','appthemes'); ?> <strong><?php echo $display_user_name; ?></strong>.</p>
                    <p class="last-login"><?php _e('You last logged in at:','appthemes'); ?> <?php echo appthemes_get_last_login($current_user->ID); ?></p>
                    <p><?php _e('Manage your ads or edit your profile from your personalized dashboard.','appthemes'); ?></p>

                    <div class="pad5"></div>

                    <a href="<?php echo CP_DASHBOARD_URL ?>" class="mbtn btn_orange"><?php _e('Manage Ads', 'appthemes') ?></a>&nbsp;&nbsp;&nbsp;<a href="<?php echo CP_PROFILE_URL ?>" class="mbtn btn_orange"><?php _e('Edit Profile', 'appthemes') ?></a>

                    <div class="pad5"></div>
                    
                    <div class="clr"></div>

		        </div><!-- /user -->

		    <?php endif; ?>

        </div><!-- /shadowblock -->

      </div><!-- /shadowblock_out -->

    <?php } // is_home ?>


    <?php if ( is_tax( APP_TAX_CAT ) ) {

		// go get the taxonomy category id so we can filter with it
		// have to use slug instead of name otherwise it'll break with multi-word cats
		if ( !isset( $filter ) )
			$filter = '';
			
		$ad_cat_array = get_term_by( 'slug', get_query_var(APP_TAX_CAT), APP_TAX_CAT, ARRAY_A, $filter );

        // build the advanced sidebar search
        cp_show_refine_search( $ad_cat_array['term_id'] ); 
         
        // show all subcategories if any 
		$subcats = wp_list_categories( 'hide_empty=0&orderby=name&show_count=1&title_li=&use_desc_for_title=1&echo=0&show_option_none=0&taxonomy='.APP_TAX_CAT.'&depth=1&child_of=' . $ad_cat_array['term_id'] );
		
		if ( !empty( $subcats ) ) : 
		?>
			<div class="shadowblock_out">
			
				<div class="shadowblock">
				
					<h2 class="dotted"><?php _e('Sub Categories', 'appthemes') ?></h2>

					<ul>
						<?php print_r( $subcats ); ?>
					</ul>

					<div class="clr"></div>
					
				</div><!-- /shadowblock -->
				
			</div><!-- /shadowblock_out -->
			
		<?php endif; ?>

    <?php } // is_tax ?>
      

    <?php if ( is_search() ) {

        // build the advanced sidebar search
        cp_show_refine_search( get_query_var('scat') );
        
		} // is_search ?>
      

    <?php appthemes_before_sidebar_widgets(); ?>

    <?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_main') ) : else : ?>

        <!-- no dynamic sidebar setup -->
    
        <div class="shadowblock_out">
        
            <div class="shadowblock">
            
              <h2 class="dotted"><?php _e('BlogRoll', 'appthemes') ?></h2>
    
              <ul>
                  <?php wp_list_bookmarks('title_li=&categorize=0'); ?>
              </ul>
    
                <div class="clr"></div>
                
            </div><!-- /shadowblock -->
            
          </div><!-- /shadowblock_out -->
    
    
         <div class="shadowblock_out">
         
            <div class="shadowblock">
            
              <h2 class="dotted"><?php _e('Meta', 'appthemes') ?></h2>
    
              <ul>
                  <?php wp_register(); ?>
                  <li><?php wp_loginout(); ?></li>
                  <li><a target="_blank" href="http://www.appthemes.com/" title="Premium WordPress Themes">AppThemes</a></li>
                  <li><a target="_blank" href="http://wordpress.org/" title="Powered by WordPress">WordPress</a></li>
                  <?php wp_meta(); ?>
              </ul>
    
                <div class="clr"></div>
                
            </div><!-- /shadowblock -->
            
          </div><!-- /shadowblock_out -->
    
    <?php endif; ?>

    <?php appthemes_after_sidebar_widgets(); ?>

</div><!-- /content_right -->