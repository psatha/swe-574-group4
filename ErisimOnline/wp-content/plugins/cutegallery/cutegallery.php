<?php
/*
Plugin Name: cutegallery
Plugin URI: http://classipro.com/
Description: A nice gallery to show your ads pictures on your ClassiPress based website.
Version: 1.0.1
Author: alucas & rubencio
Author URI: http://classipro.com/
License: GPL2
*/
if (!defined('TT'))
	define('TT', plugins_url('/cutegallery/timthumb.php'));
	
add_action('wp_head', 'cutegallery_header');

function cutegallery(){
	global $wpdb, $post;
?>

<?php
	if (is_single())
		query_posts( array('post__in' => get_option('sticky_posts'),  'post_type' => APP_POST_TYPE, 'post_status' => 'publish', 'orderby' => 'rand', 'posts_per_page'=>'-1') );
	else if (is_tax()){
		$term  = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
		query_posts( array('post__in' => get_option('sticky_posts'),  'post_type' => APP_POST_TYPE, 'post_status' => 'publish', 'orderby' => 'rand', 'posts_per_page'=>'-1', 'ad_cat'=>$term->slug) );
	}
	if (have_posts()) : 
?>
                   
<ul id="rb_imageGallery" class="gallery">       
<?php
//'orderby' => 'menu_order'
$attachment = get_posts(array('post_type' => 'attachment', 'numberposts' => null, 'post_parent' => $post->ID, 'posts_per_page'=>'-1','post_mime_type' => 'image','order' => 'ASC'));

$cont =0; 
if ($attachment){
	$scriptNavigationImages = " 
jQuery('ul.dpUniSlider_nav li').each(function(i) {";
	foreach( $attachment as $atach)
	{
		$url_raw = wp_get_attachment_url($attachment[$cont]->ID, false);
		$url = TT.'?src='.wp_get_attachment_url($attachment[$cont]->ID, false).'&h=300&w=556';
		$url_thumb = TT.'?src='.wp_get_attachment_url($attachment[$cont]->ID, false).'&h=50&w=50';
		$url_thumb_bw = TT.'?src='.wp_get_attachment_url($attachment[$cont]->ID, false).'&h=50&w=50&f=2|3,80';
		echo "
		    <li>
            <a class='img-main cboxElement' title='". the_title_attribute('echo=0') .' - '.__('Image ', 'appthemes'). ($cont+1) ."' rel='colorbox' id='thumb1' href='$url_raw'>
            <img src='$url' alt='".__('Image ', 'appthemes'). ($cont+1) ."' />
            </a>
            </li> <!-- end .slide -->
		   ";
		 $scriptNavigationImages .= "
		 if(i==$cont) jQuery(this).html(\" <img src='$url_thumb' alt='".__('Image ', 'appthemes'). ($cont+1) ."' /><img src='$url_thumb_bw' alt='".__('Image ', 'appthemes'). ($cont+1) ."' /> \")" ;   
		$cont++;
	}
	if($cont ==1) echo "<div class=\"dpUniSlider_autoLoader\" style=\"display: block; opacity: 1;\"></div>";
	$scriptNavigationImages .= " 
});

jQuery('.dpUniSlider_nav').css({
	marginLeft: -(jQuery('.dpUniSlider_nav').outerWidth() / 2)+'px'
}); ";

$css_style="<style type='text/css'>
.dpUniSlider_wrapper ul.dpUniSlider_nav li {width:50px;height:50px; overflow:hidden; }
.dpUniSlider_wrapper ul.dpUniSlider_nav li img:first-child{ display:none;}
.dpUniSlider_wrapper ul.dpUniSlider_nav li.active img:first-child{ display:block;}
.dpUniSlider_wrapper ul.dpUniSlider_nav {height:50px; }
.dpUniSlider_container{overflow:hidden; width:556px; height:300px;}  ";
$css_style.= "</style>";

} //end_atachment
else
{
	$url = TT.'?src='. plugins_url('/cutegallery/images/').'no_pic.jpg&h=300&w=556';
	echo "
		<li>
		<img src='$url' alt='".__('Image ', 'appthemes'). ($cont+1) ."' />
		</li> <!-- end .slide -->
	";
}
?>
</ul>
<?php
if ($attachment){	
	if ($cont >1) $var_autoSlide = "true";
	else {$var_autoSlide = "false"; }
?>
 <script type="text/javascript"> 
	jQuery(document).ready(function(){
		jQuery('#rb_imageGallery').dpUniSlider({
			autoSlide: <? echo $var_autoSlide; ?>, /* Set the autoslide feature */
			autoSlideSpeed: 6000, /* Set the speed of the autoslide in miliseconds */
			pauseOnHover: true, /* Pause the autoslide on mouse hover */
			showAutoSlideIcon: false, /* Show/Hide autoslide loading icon */
			loop: true, /* Set the loop feature */
			showArrows: false, /* Show left/right arrows */
			showNavigation: true, /* Show Navigation bar */
			navPosition: 'bottom-center', /* Set the position of the navigation bar (possible values are: 'top-right', 'top-center', 'top-left', 'bottom-right', 'bottom-center', 'bottom-left') */
			draggable: false, /* Set Drag feature (Mobile compatible) */
			dragOffset: 100, /* Distance in pixels to drag the next slide */
			fixedHeight: 300, /* Set a fixed height for the slider */
			preselectSlideNum: 1, /* Preselect any slide number */
			slideTransitionSpeed: 200, /* Set the slider transition speed */
			elementsDelayTransition: true, /* Set the elements transition feature */
			elementsDelayTransitionSpeed: 500, /* Set the elements transition speed */
			startOffset: 110, /* Start animation offset */
			endOffset: 80, /* End animation offset */
			slideOpacity: 800 /* Fade effect on slide transition */
		});
		
	<?php echo $scriptNavigationImages ; ?>
	});
	
</script> 
<?php  echo $css_style; 
}?>
<div class="clr"></div>

<?php
wp_reset_query();
else :
$term  = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
query_posts( array('post__in' => get_option('sticky_posts'),  'post_type' => APP_POST_TYPE, 'post_status' => 'publish', 'orderby' => 'rand', 'posts_per_page'=>'-1', 'ad_cat'=>$term->slug) );
endif;
}
function cutegallery_header(){	
	$dir = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
	echo '<link type="text/css" rel="stylesheet" href="' . $dir .'css/dpUniSlider.css" />' . "\n";
	wp_deregister_script('jquery');
	wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js', false, '1.7.1');
	wp_enqueue_script('jquery');
	echo '<script type="text/javascript" src="' . $dir .'js/jquery.dpUniSlider.min.js"></script>' . "\n";
}
?>