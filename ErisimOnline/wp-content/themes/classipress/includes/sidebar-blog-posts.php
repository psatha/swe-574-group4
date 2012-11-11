<?php

/**
 * show the most recent blog posts
 * in this sidebar widget.
 */

?>

<?php query_posts(array('posts_per_page' => $count, 'post_type' => 'post')); ?>

<ul class="from-blog">

	<?php if(have_posts()) : ?>

		<?php while(have_posts()) : the_post() ?>

			<li>

				<div class="post-thumb">
					<?php if (has_post_thumbnail()) { echo get_the_post_thumbnail($post->ID,'sidebar-thumbnail'); } ?>
				</div>

				<h3><a href="<?php the_permalink(); ?>"><?php if (mb_strlen(get_the_title()) >= 40) echo mb_substr(get_the_title(), 0, 40).'...'; else the_title(); ?></a></h3>

				<p class="side-meta"><?php _e('by','appthemes') ?> <?php the_author_posts_link(); ?> <?php _e('on','appthemes') ?> <?php echo appthemes_date_posted(get_the_date("Y-m-d H:i:s")); ?> - <?php comments_popup_link(__('0 Comments', 'appthemes'), __('1 Comment', 'appthemes'), __('% Comments', 'appthemes')) ?></p>

				<p><?php echo mb_substr(strip_tags(get_the_content()), 0, 160).'...';?></p>

			</li>

		<?php endwhile; ?>

	<?php else: ?>

		<li><?php _e('There are no blog articles yet.', 'appthemes') ?></li>

	<?php endif; ?>

</ul>