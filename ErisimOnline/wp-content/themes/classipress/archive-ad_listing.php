<?php get_header(); ?>

<!-- CONTENT -->
  <div class="content">

    <div class="content_botbg">

      <div class="content_res">

        <div id="breadcrumb">

          <?php if ( function_exists('cp_breadcrumb') ) cp_breadcrumb(); ?>

        </div>

	<?php
	    // Figure out what we are displaying
	    $sort = "latest";
	    if( !empty($_GET['sort']) && in_array($_GET['sort'], array("popular", "random"))){
		$sort = $_GET['sort'];
	    }

	    $loop_type = "ad_listing";

	    if( $sort == "latest" ){
		// show latest posts
		$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		query_posts( array('post_type' => APP_POST_TYPE, 'ignore_sticky_posts' => 1, 'paged' => $paged) );
	    }
	    else if($sort == "random"){
		// show all random ads but make sure the sticky featured ads don't show up first
		$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		query_posts( array('post_type' => APP_POST_TYPE, 'ignore_sticky_posts' => 1, 'paged' => $paged, 'orderby' => 'rand') );
	    }
	    else if($sort == "popular"){

		$loop_type = "featured";

	    }
	?>
        <!-- left block -->
        <div class="content_left">            				

                <?php get_template_part( 'loop', $loop_type ); ?>

	</div><!-- /content_left -->


        <?php get_sidebar(); ?>


        <div class="clr"></div>

      </div><!-- /content_res -->

    </div><!-- /content_botbg -->

  </div><!-- /content -->


<?php get_footer(); ?>
