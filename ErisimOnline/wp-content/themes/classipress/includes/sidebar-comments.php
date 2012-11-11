<?php

/**
 * recent comments for tabbed sidebar
 * based off original woothemes theme
 */

global $wpdb;

// TODO: Use get_comments()
$sql = "SELECT DISTINCT ID, post_title, post_password, comment_ID,
        comment_post_ID, comment_author, comment_author_email, comment_date_gmt, comment_approved,
        comment_type,comment_author_url,
        SUBSTRING(comment_content,1,115) as excerpt
        FROM $wpdb->comments
        LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
        WHERE comment_approved = '1' AND comment_type = '' AND
        post_password = ''
        ORDER BY comment_date_gmt DESC LIMIT 5";

$comments = $wpdb->get_results($sql);
 ?>


<ul class="side-comments">

	<?php
	foreach ($comments as $comment) {

	?>

		<li>

			<?php echo get_avatar($comment, '50'); ?>

			<div class="comment">

				<p><?php echo strip_tags($comment->comment_author); ?> - <a href="<?php echo get_permalink($comment->ID); ?>#comment-<?php echo $comment->comment_ID; ?>" title="<?php _e('Comment on article ', 'appthemes'); ?>'<?php echo $comment->post_title; ?>'">"<?php echo strip_tags($comment->excerpt); ?>"...</a></p>

			</div>

			<div class="clr"></div>

		</li>

	<?php
	}
	?>

</ul>
