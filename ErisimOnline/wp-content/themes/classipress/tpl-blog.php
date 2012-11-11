<?php
/*
Template Name: Blog Template
*/
?>

<?php get_header(); ?>

<div class="content">

    <div class="content_botbg">

        <div class="content_res">

            <div id="breadcrumb">
                <?php if ( function_exists('cp_breadcrumb') ) cp_breadcrumb(); ?>
            </div>

            <div class="content_left">

                <?php $args = array( 'paged'=> $paged, 'post_type' => 'post' ); query_posts( $args ); ?>

                <?php get_template_part( 'loop' ); ?>

                <div class="clr"></div>

            </div><!-- /content_left -->

            <?php get_sidebar( 'blog' ); ?>

            <div class="clr"></div>

        </div><!-- /content_res -->

    </div><!-- /content_botbg -->

</div><!-- /content -->

<?php get_footer(); ?>
