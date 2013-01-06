<?php
global $current_user, $app_abbr, $gmap_active;

// make sure google maps has a valid address field before showing tab
$custom_fields = get_post_custom(); 
if ( !empty($custom_fields[$app_abbr.'_zipcode']) || !empty($custom_fields[$app_abbr.'_country']) || 
	!empty($custom_fields[$app_abbr.'_state']) || !empty($custom_fields[$app_abbr.'_city']) || 
	!empty($custom_fields[$app_abbr.'_street']) ) {
	$gmap_active = true; 
}

?>

<!-- right sidebar -->
<div class="content_right">

    <div class="tabprice">

        <ul class="tabnavig">
          <?php if ( $gmap_active ) { ?>
              <li><a href="#priceblock1"><span class="big"><?php _e('Map', 'appthemes') ?></span></a></li>
          <?php } ?>
        </ul>

    <?php if ( $gmap_active ) { ?>

        <!-- tab 1 -->
        <div id="priceblock1">

            <div class="clr"></div>

                <div class="singletab">

                    <?php include_once ( TEMPLATEPATH . '/includes/sidebar-gmap.php' ); ?>

                </div><!-- /singletab -->

        </div>
        
    <?php } ?>


      </div><!-- /tabprice -->   


<?php appthemes_before_sidebar_widgets(); ?>

<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_listing') ) : else : ?>

<!-- no dynamic sidebar so don't do anything -->

<?php endif; ?>

<?php appthemes_after_sidebar_widgets(); ?>


</div><!-- /content_right -->
