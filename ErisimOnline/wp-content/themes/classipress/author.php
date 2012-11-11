<?php

/**
 * Author profile page
 *
 */

//This sets the $curauth variable
if ( isset($_GET['author_name']) ) :
    $curauth = get_user_by('login', $author_name);
else :
    $curauth = get_userdata( intval($author) );
endif;


?>

<?php get_header(); ?>

<div class="content">

    <div class="content_botbg">

        <div class="content_res">

			<div id="breadcrumb">

                  <?php if ( function_exists('cp_breadcrumb') ) cp_breadcrumb(); ?>

              </div>


            <!-- left block -->
            <div class="content_left">

                <div class="shadowblock_out">

                    <div class="shadowblock">

                        <h1 class="single dotted"><?php _e('About','appthemes')?> <?php echo($curauth->display_name); ?></h1>

                        <div class="post">

							<div id="user-photo"><?php appthemes_get_profile_pic($curauth->ID, $curauth->user_email, 96) ?></div>

                            <div class="author-main">

								<ul class="author-info">
									<li><strong><?php _e('Member Since:','appthemes');?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($curauth->user_registered)); ?></li>
									<li><strong><?php _e('Website:','appthemes'); ?></strong> <a href="<?php echo esc_url($curauth->user_url); ?>"><?php echo strip_tags( $curauth->user_url ); ?></a></li>
									<li><div class="twitterico"></div><a href="http://twitter.com/<?php echo urlencode($curauth->twitter_id); ?>" target="_blank"><?php _e('Twitter','appthemes')?></a></li>
									<li><div class="facebookico"></div><a href="http://facebook.com/<?php echo urlencode($curauth->facebook_id); ?>" target="_blank"><?php _e('Facebook','appthemes')?></a></li>
								</ul>

                            </div>


							<h3 class="dotted"><?php _e('Description','appthemes'); ?></h3>
							<p><?php echo $curauth->user_description; ?></p>

							<div class="pad20"></div>

							<h3 class="dotted"><?php _e('Latest items listed','appthemes'); ?></h3>

							<div class="pad5"></div>

							<ul class="latest">

								<?php query_posts( array('posts_per_page' => 10, 'post_type' => APP_POST_TYPE, 'post_status' => 'publish', 'author' => $curauth->ID) ); ?>

								<?php if ( have_posts() ) : ?>

									<?php while ( have_posts() ) : the_post() ?>

										<li>
											<a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
										</li>

									<?php endwhile; ?>

								<?php else: ?>

									<li><?php _e('No ads by this poster yet.','appthemes'); ?></li>

								<?php endif; ?>

							</ul>



							<div class="pad20"></div>

							<h3 class="dotted"><?php _e('Recent blog posts','appthemes'); ?></h3>

							<div class="pad5"></div>

							<ul class="recent">

								<?php query_posts( array( 'posts_per_page' => 10, 'post_type' => 'post', 'post_status' => 'publish', 'author' => $curauth->ID ) ); ?>

								<?php if ( have_posts() ) : ?>

									<?php while ( have_posts() ) : the_post() ?>

										<li>
											<a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
										</li>

									<?php endwhile; ?>

								<?php else: ?>

									<li><?php _e('No blog posts written yet.','appthemes'); ?></li>

								<?php endif; ?>

							</ul>



                        </div><!--/directory-->

                    </div><!-- /shadowblock -->

                </div><!-- /shadowblock_out -->


            </div><!-- /content_left -->


            <?php get_sidebar(); ?>

            <div class="clr"></div>


        </div><!-- /content_res -->

    </div><!-- /content_botbg -->

</div><!-- /content -->


<?php get_footer(); ?>
