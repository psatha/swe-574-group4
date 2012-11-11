<?php get_header(); ?>

<div class="content">

    <div class="content_botbg">

        <div class="content_res">

            <div id="breadcrumb">

                <?php if ( function_exists('cp_breadcrumb') ) cp_breadcrumb(); ?>

            </div>

            <div class="content_left">
                
                <?php appthemes_before_page_loop(); ?>

                <?php if ( have_posts() ) : ?>

                    <?php while ( have_posts() ) : the_post() ?>
                        
                        <?php appthemes_before_page(); ?>

                        <div class="shadowblock_out">

                            <div class="shadowblock">

                                <div class="post">
                                    
                                    <?php appthemes_before_page_title(); ?>

                                    <h1 class="single dotted"><?php the_title();?></h1>
                                        
                                    <?php appthemes_after_page_title(); ?>
                                        
                                    <?php appthemes_before_page_content(); ?>

                                    <?php the_content(); ?>
                                        
                                    <?php appthemes_after_page_content(); ?>

                                    <div class="prdetails">
                                    
                                        <?php edit_post_link( '<p class="edit">'.__('Edit Page','appthemes'), '', '' ).'</p>'; ?>
                                        
									</div>

                                    <?php if ( function_exists('selfserv_sexy') ) selfserv_sexy(); ?>

                                </div><!--/post-->

                            </div><!-- /shadowblock -->

                        </div><!-- /shadowblock_out -->
                            
                        <?php appthemes_after_page(); ?>

					<?php endwhile; ?>
					
					    <?php appthemes_after_page_endwhile(); ?>
					
					<?php else: ?>
					    
					    <?php appthemes_page_loop_else(); ?>

						<p><?php _e('Sorry, no pages matched your criteria.', 'appthemes'); ?></p>

					<?php endif; ?>

					<div class="clr"></div>
					
					<?php appthemes_after_page_loop(); ?>

					<?php if ( comments_open() ) comments_template( '/comments-page.php' ); ?>

                </div><!-- /content_left -->

                <?php get_sidebar( 'page' ); ?>

            <div class="clr"></div>

        </div><!-- /content_res -->

    </div><!-- /content_botbg -->

</div><!-- /content -->
	
<?php get_footer(); ?>	