<?php global $current_user; ?>

<!-- right sidebar -->
<div class="content_right">

	<!-- start tabs -->
	<div class="tabprice">

		<ul class="tabnavig">
		  <li><a href="#priceblock1"><?php _e('Popular', 'appthemes') ?></a></li>
		  <li><a href="#priceblock2"><?php _e('Comments', 'appthemes') ?></a></li>
		  <li><a href="#priceblock3"><?php _e('Tags', 'appthemes') ?></a></li>
		</ul>


		<!-- popular tab 1 -->
		<div id="priceblock1">

			<div class="clr"></div>

			<?php include_once(TEMPLATEPATH . '/includes/sidebar-popular.php'); ?>

		</div>


		<!-- comments tab 2 -->
		<div id="priceblock2">

			<div class="clr"></div>

			<?php include_once(TEMPLATEPATH . '/includes/sidebar-comments.php'); ?>

		</div><!-- /priceblock2 -->


		<!-- tag cloud tab 3 -->
		<div id="priceblock3">

		  <div class="clr"></div>

		  <div class="pricetab">

			  <?php if ( function_exists('wp_tag_cloud') ) : ?>

			  <div id="tagcloud">

				<?php wp_tag_cloud('smallest=9&largest=16'); ?>

			  </div>

				<?php endif; ?>

			  <div class="clr"></div>

		  </div>

		</div>

	</div><!-- end tabs -->
	

    <?php appthemes_before_sidebar_widgets(); ?>

	<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_blog') ) : else : ?>

	<!-- no dynamic sidebar so don't do anything -->

	<?php endif; ?>
	
	<?php appthemes_after_sidebar_widgets(); ?>


</div><!-- /content_right -->
