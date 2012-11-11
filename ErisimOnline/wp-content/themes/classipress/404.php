<?php get_header(); ?>

<div class="content">

    <div class="content_botbg">

        <div class="content_res">

            <div id="breadcrumb">

                <?php if ( function_exists('cp_breadcrumb') ) cp_breadcrumb(); ?>

            </div><!-- /breadcrumb -->
 

            <!-- left block -->
            <div class="content_left">

                <div class="shadowblock_out">
    
                    <div class="shadowblock">
                        
                        <h1 class="single dotted"><?php _e('Whoops! Page Not Found.', 'appthemes')?></h1>
    
                            <p><?php _e('The page or ad listing you are trying to reach no longer exists or has expired.', 'appthemes') ?></p>
    
                        <div class="pad25"></div>
    
                    </div><!-- /shadowblock -->
    
                </div><!-- /shadowblock_out -->
        
                <div class="clr"></div>

                <?php
                // show the ad block if it's been activated
                if ( get_option('cp_adcode_336x280_enable') == 'yes' ) :
    
                    if ( function_exists('appthemes_single_ad_336x280') ) { ?>
    
                    <div class="shadowblock_out">
    
                        <div class="shadowblock">
    
                          <h2 class="dotted"><?php _e('Sponsored Links', 'appthemes') ?></h2>
    
                          <?php appthemes_single_ad_336x280(); ?>
    
                        </div><!-- /shadowblock -->
    
                    </div><!-- /shadowblock_out -->
    
                <?php
                    }
                    
                endif;
                ?>
    
                <div class="clr"></div>
                        
            </div><!-- /content_left -->
            

            <?php get_sidebar(); ?>
            

            <div class="clr"></div>

        </div><!-- /content_res -->

    </div><!-- /content_botbg -->

</div><!-- /content -->


<?php get_footer(); ?>
