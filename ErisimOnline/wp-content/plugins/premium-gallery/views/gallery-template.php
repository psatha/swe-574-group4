<div id="sp-gallery-wrapper<?php echo $id;?>" <?php if($mode=='slider'){ ?> class="sp-gallery-slider"<?php } ?>>
	<div id='sp-gallery<?php echo $id;?>' class='sp-gallery'>
		<ol>
			<?php 
			$x = 0;
			foreach ( $images as $image_id => $attachment ) {

				$item_src = wp_get_attachment_image_src($image_id, 'rb_single_ad', false);

				$fullsize = wp_get_attachment_image_src($image_id, 'full', false);
				$thumb_src = wp_get_attachment_image_src($image_id, 'ad-small', false);
				$thumb_src[0] = add_query_arg('w', $thumb_width, $thumb_src[0]);

				// doing some height/width ratio checks
				if ($item_src[1] > $item_src[2]) { // width greater than height
					$img_dimension = 'style="width:'.$width.'px; display:inline;"';
				}
				else {
					$img_dimension = 'style="height:'.$height.'px; display:block;"';
				}

				// setting up JS array
				$js_array = array('title'=>$attachment->post_title, 'caption'=>$attachment->post_excerpt,'description'=>$attachment->post_content, 'thumbnail'=>$thumb_src[0]);
				$js[] = json_encode($js_array);
				
				echo '<li>';

				if($lightbox == 'yes')
					echo '<a href="'.$fullsize[0].'" rel="imagebox">';

				if($x < 10){
					echo '<span><img '.$img_dimension.' src="'.$item_src[0].'" alt="" /></span>'; 
				}else{
					echo '<span><span '.$img_dimension.' title="'.$item_src[0].'" alt="" class="load"></span></span>'; 
				}
				if($lightbox == 'yes')
					echo '</a>';

				echo '</li>';
				$x++;
			}

	?>
		</ol>

		<div class="sp-gallery-controls">
			<div class='sp-gallery-nav-outer'>
				<div class='sp-gallery-nav-inner'>
					<div class='sp-gallery-nav'></div>
				</div>
			</div>
		</div>
	</div>
	<div id="sp-gallery-meta<?php echo $id;?>" class="sp-gallery-meta <?php if($options['hide_meta'] =='yes'){ ?> hide<?php } ?>">
		<h5 class="sp-gallery-title" <?php echo $hide_title;?>><?php echo $gal_title;?></h5>
		<p class="sp-gallery-description <?php if($options['hide_descriptions'] =='yes'){ ?> hide<?php } ?>"><?php echo $gal_description;?></p>
	</div>
</div>

<style type="text/css">
	.sp-gallery,.sp-gallery li{
		width:<?php echo $width;?>px;
		height:<?php echo $height;?>px;
	}
	.sp-gallery-meta{
		width:<?php echo ($width - 30);?>px;
	}

</style>

<?php $js = implode( ', ', $js ); ?>

<script type="text/javascript">
	jQuery(function(){
		var spGalleryData<?php echo $id;?> = [<?php echo $js;?>];
		jQuery("#sp-gallery<?php echo $id;?>").data("images",spGalleryData<?php echo $id;?>);
		jQuery("#sp-gallery<?php echo $id;?>").data("id",<?php echo $id;?>);
		jQuery("#sp-gallery<?php echo $id;?>").data("timeout",<?php echo $timeout;?>);
		jQuery("#sp-gallery<?php echo $id;?>").data("speed",<?php echo $speed;?>);
	});
</script>