<?php get_header(); ?>

<?php // appthemes_highlight_search_term(esc_attr(get_search_query())); ?>

<!-- CONTENT -->
  <div class="content">

    <div class="content_botbg">

      <div class="content_res">

      <div id="breadcrumb">

          <?php if ( function_exists('cp_breadcrumb') ) cp_breadcrumb(); ?>

        </div><!-- /breadcrumb -->


        <!-- left block -->
        <div class="content_left">            


			<?php if ( have_posts() ) : ?>

				<div class="shadowblock_out">

					<div class="shadowblock">

						<h1 class="single dotted">
						<?php 
							$searchTxt = trim( strip_tags( esc_attr( get_search_query() ) ) );
							if ( $searchTxt ==  __('What are you looking for?','appthemes') ) $searchTxt = '*';
							printf( __("Search for '%s' returned %s results",'appthemes'), $searchTxt, $wp_query->found_posts ); 
						?>
                        </h1>
						<p><?php if (function_exists('twg_tfsp_sort')) twg_tfsp_sort();?></p>
					</div><!-- /shadowblock -->

				</div><!-- /shadowblock_out -->


                <?php get_template_part( 'loop', 'ad_listing' ); ?>
				

			<?php else: ?>


				<div class="shadowblock_out">

					<div class="shadowblock">

						<h1 class="single dotted"><?php printf( __("Search for '%s' returned %s results",'appthemes'), trim( strip_tags( esc_attr( get_search_query() ) ) ), $wp_query->found_posts ); ?></h1>

						<p><?php _e('Sorry, no listings were found.','appthemes')?></p>
						<p class="suggest"><?php // appthemes_search_suggest(); // deprecated by Yahoo ?></p>

					</div><!-- /shadowblock -->

				</div><!-- /shadowblock_out -->


			<?php endif; ?>


            <div class="pad5"></div>

            <div class="clr"></div>


            <?php
            // show the ad block if it's been activated
            if ( get_option('cp_adcode_336x280_enable') == 'yes' ) :

                if ( function_exists('appthemes_single_ad_336x280') ) { ?>

                <div class="shadowblock_out">

                    <div class="shadowblock">

                      <h2 class="dotted"><?php _e('Sponsored Links','appthemes') ?></h2>

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
