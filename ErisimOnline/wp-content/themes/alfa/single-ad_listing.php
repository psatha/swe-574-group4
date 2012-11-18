<?php get_header(); ?>

<script type='text/javascript'>
// <![CDATA[
/* setup the form validation */
jQuery(document).ready(function ($) { 
    $('#mainform').validate({
        errorClass: 'invalid'
    });
});
// ]]>
</script>

<?php // if($_GET['reportpost'] == $post->ID) { app_report_post($post->ID); $reported = true;} ?>

<div class="content">

    <div class="content_botbg">

        <div class="content_res">

            <div id="breadcrumb">

                <?php if ( function_exists('cp_breadcrumb') ) cp_breadcrumb(); ?>
                  
            </div>
              
            <!-- <div style="width: 105px; height:16px; text-align: right; float: left; font-size:11px; margin-top:-10px; padding:0 10px 5px 5px;"> -->
                <?php // if($reported) : ?>
                    <!-- <span id="reportedPost"><?php _e('Post Was Reported', 'appthemes'); ?></span> -->
                <?php // else : ?>
                    <!--	<a id="reportPost" href="?reportpost=<?php echo $post->ID; ?>"><?php _e('Report This Post','appthemes') ?></a> -->
                <?php // endif; ?>
			<!-- </div> -->
              
            <div class="clr"></div>

            <div class="content_left">
	
	            <?php appthemes_before_loop(); ?>

		        <?php if ( have_posts() ) : ?>

			        <?php while ( have_posts() ) : the_post() ?>
			        
			            <?php appthemes_before_post(); ?>

				        <?php appthemes_stats_update( $post->ID ); //records the page hit ?>

				        <div class="shadowblock_out">

					        <div class="shadowblock">
                                
                                <?php appthemes_before_post_title(); ?>

							    <h1 class="single-ad"><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h1>
							    
							    <div class="clr"></div>
							    
							    <?php appthemes_after_post_title(); ?>
                                
                                <div class="rb_share_ad">
                                
                                    <div class="rb_tweet">
                                    <a href="http://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
                                    </div>
                                    
                                    <div class="rb_facebook">
                                    <iframe src="http://www.facebook.com/plugins/like.php?app_id=175266875876185&amp;href=<?php the_permalink(); ?>&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font=lucida+grande&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true"></iframe>
                                    </div>
                                    
                                    <div class="rb_google">
                                    <g:plusone size="medium" href="<?php the_permalink(); ?>"></g:plusone>
                                    </div>
                                    
                                    <div class="rb_favorites">
                                    	<?php if (function_exists('wpfp_link')) { wpfp_link(); } ?>
                                    </div>
                                </div><!-- /rb_share_ad -->

							    <div class="pad5 dotted"></div>

                                <div class="bigright" style="width: 230px;"<?php if(get_option($GLOBALS['app_abbr'].'_ad_images') == 'no') echo 'style="float:none;"'; ?>>
    
                                    <ul>
    
                                        <?php
                                        // grab the category id for the functions below
                                        $cat_id = appthemes_get_custom_taxonomy( $post->ID, APP_TAX_CAT, 'term_id' );
    
                                        // check to see if ad is legacy or not
                                        if ( get_post_meta( $post->ID, 'expires', true ) ) {  ?>
    
                                            <li><span><?php _e('Location:', 'appthemes') ?></span> <?php echo get_post_meta( $post->ID, 'location', true ); ?></li>
                                            <li><span><?php _e('Phone:', 'appthemes') ?></span> <?php echo get_post_meta( $post->ID, 'phone', true ); ?></li>
    
                                            <?php if ( get_post_meta( $post->ID, 'cp_adURL', true ) ) ?>
                                                <li><span><?php _e('URL:','appthemes'); ?></span> <?php echo appthemes_make_clickable( get_post_meta( $post->ID, 'cp_adURL', true ) ); ?></li>
    
                                            <li><span><?php _e('Listed:', 'appthemes') ?></span> <?php the_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ?></li>
                                            <li><span><?php _e('Expires:', 'appthemes') ?></span> <?php echo cp_timeleft( strtotime( get_post_meta( $post->ID, 'expires', true ) ) ); ?></li>
    
                                        <?php
                                        } else {
    
                                            if ( get_post_meta($post->ID, 'cp_ad_sold', true) == 'yes' ) : ?>
                                            <li id="cp_sold"><span><?php _e('This item has been sold', 'appthemes'); ?></span></li>
                                            <?php endif; ?>
                                            <?php
                                            // 3.0+ display the custom fields instead (but not text areas)	
                                            cp_get_ad_details( $post->ID, $cat_id );
                                        ?>
    					   	
                                        <table><tr><td width="114px"><div style="font-weight: 700; margin-right: 5px;">Kayit Tarihi</div></td><td> <?php the_time( get_option( 'date_format' ) ) ?></td>
					</tr></table>
    
                                            <?php if ( get_post_meta($post->ID, 'cp_sys_expire_date', true) ) ?>
                                                <!-- <li id="cp_expires"><span><?php _e( 'Expires:', 'appthemes' ) ?></span> <?php echo cp_timeleft( strtotime( get_post_meta( $post->ID, 'cp_sys_expire_date', true) ) ); ?></li> -->
    
                                        <?php
                                        } // end legacy check
                                        ?>
    
                                    </ul>
    
                                </div><!-- /bigright -->

				
                                <?php if ( get_option( 'cp_ad_images' ) == 'yes' ) : ?>
            
                                    <!-- <div class="bigleft"> -->
				    <div class="bigleft" style="width: 300px;">	
            
                                        <div id="main-pic">
            
                                            <?php cp_get_image_url(); ?>
            
                                            <div class="clr"></div>
                                            
                                        </div>
            
                                        <div id="thumbs-pic">
            
                                            <?php cp_get_image_url_single( $post->ID, 'thumbnail', $post->post_title, -1 ); ?>
            
                                            <div class="clr"></div>
                                            
                                        </div>
            
                                    </div> <!-- /bigleft -->
            
                                <?php endif; ?>

				                <div class="clr"></div>
				                
				                <?php appthemes_before_post_content(); ?>
					
                                <div class="single-main">
					<br/>
					<div class="contFullWidth"> <!-- contFullWidth -->
					    <div class="header">Açýklamalar</div>
						<br/>
    						<?php the_content(); ?>
  					</div>
                                    
					<?php 
						cp_get_ad_details( $post->ID, $cat_id, 'content' ); 
					?> 
					<div class="clr"></div>
                                </div>
                                
                                <?php appthemes_after_post_content(); ?>

                            </div><!-- /shadowblock -->

                        </div><!-- /shadowblock_out -->
                        
                        <?php appthemes_after_post(); ?>

			        <?php endwhile; ?>
			        
			            <?php appthemes_after_endwhile(); ?>
			        
			        <?php else: ?>
			        
			            <?php appthemes_loop_else(); ?>

                    <?php endif; ?>

                    <div class="clr"></div>
                    
                    <?php appthemes_after_loop(); ?>

                    <?php wp_reset_query(); ?>

                    <div class="clr"></div>
      

            </div><!-- /content_left -->

            <?php get_sidebar( 'ad' ); ?>

            <div class="clr"></div>

        </div><!-- /content_res -->

    </div><!-- /content_botbg -->

</div><!-- /content -->

<?php get_footer(); ?>