<?php

/**
 * popular posts on blog for tabbed sidebar
 * shows most popular posts within the last 3 months
 * or overall if any found based on page views
 */

	global $wpdb;

	$now = gmdate('Y-m-d H:i:s', time());
	$lastmonth = gmdate('Y-m-d H:i:s', gmmktime(date_i18n('H'), date_i18n('i'), date_i18n('s'), date_i18n('m')-3, date_i18n('d'), date_i18n('Y')));


	// give us the most popular blog posts based on page views, last 3 months
	$sql = $wpdb->prepare("SELECT * FROM " . $wpdb->app_pop_total . " a
		INNER JOIN " . $wpdb->posts . " p ON p.ID = a.postnum
		WHERE postcount > 0 AND post_status = 'publish'
		AND post_type = 'post'
		AND post_date < %s
		AND post_date > %s
		ORDER BY postcount DESC LIMIT 5",
		$now,
		$lastmonth);

	// give us the most popular blog posts based on page views, overall
	$sql_overall = $wpdb->prepare("SELECT * FROM " . $wpdb->app_pop_total . " a
		INNER JOIN " . $wpdb->posts . " p ON p.ID = a.postnum
		WHERE postcount > 0 AND post_status = 'publish'
		AND post_type = 'post'
		ORDER BY postcount DESC LIMIT 5");

	$posts = $wpdb->get_results($sql);
  
  if(!$posts)
    $posts = $wpdb->get_results($sql_overall);

?>


<ul class="pop-blog">

	<?php
	if($posts) {

		foreach($posts as $post) :
			setup_postdata($post);
	?>

		<li>

			<div class="post-thumb">
				<?php if (has_post_thumbnail()) { echo get_the_post_thumbnail($post->ID,'sidebar-thumbnail'); } ?>
			</div>

			<h3><a href="<?php echo get_permalink($post->ID); ?>"><span class="colour"><?php echo stripslashes($post->post_title); ?></span></a></h3>
			<p class="side-meta"><?php _e('by','appthemes') ?> <?php the_author_posts_link(); ?> <?php _e('on','appthemes') ?> <?php echo appthemes_date_posted($post->post_date); ?> - <a href="<?php echo get_permalink($post->ID); ?>#comment"><?php echo ($post->comment_count); ?> <?php _e('Comments','appthemes') ?></a></p>
			<p><?php echo substr(strip_tags($post->post_content), 0, 160)."...";?></p>

		</li>

	<?php
		endforeach;

	} else { ?>

		<li><?php _e('There are no popular posts yet.', 'appthemes') ?></li>

	<?php
	}
	?>

</ul>