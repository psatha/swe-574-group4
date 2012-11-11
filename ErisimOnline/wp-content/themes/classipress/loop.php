<?php
/**
 * Main loop for displaying blog posts
 *
 * @package ClassiPress
 * @author AppThemes
 *
 */
?>

<?php appthemes_before_blog_loop(); ?>

<?php if ( have_posts() ) : ?>

    <?php while ( have_posts() ) : the_post() ?>
    
        <?php appthemes_before_blog_post(); ?>

        <div <?php post_class('shadowblock_out'); ?> id="post-<?php the_ID(); ?>">

            <div class="shadowblock">

                <?php appthemes_before_blog_post_title(); ?>

                <h1 class="single blog"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h1>
                
                <?php appthemes_after_blog_post_title(); ?> 

                <?php // hack needed for "<!-- more -->" to work with templates
                    global $more;
                    $more = 0;
                ?>
                
                <?php appthemes_before_blog_post_content(); ?>
                
                <div class="entry-content">

                    <?php if ( has_post_thumbnail() ) the_post_thumbnail('blog-thumbnail'); ?>

                    <?php the_content('<p>'.__('Continue reading &raquo;', 'appthemes').'</p>'); ?>
                    
                </div>    
                
                <?php appthemes_after_blog_post_content(); ?>

            </div><!-- #shadowblock -->

        </div><!-- #shadowblock_out -->
        
        <?php appthemes_after_blog_post(); ?>

    <?php endwhile; ?>
    
        <?php appthemes_after_blog_endwhile(); ?>

<?php else: ?>
    
    <?php appthemes_blog_loop_else(); ?>

<?php endif; ?>

<?php appthemes_after_blog_loop(); ?>
