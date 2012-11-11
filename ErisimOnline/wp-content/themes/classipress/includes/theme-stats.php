<?php
/**
 *
 * Keeps track of ad views for daily and total
 * @author AppThemes
 *
 *
 */

// sidebar widget showing overall popular ads
function cp_todays_overall_count_widget($post_type, $limit) {
    global $wpdb, $nowisnow;

	// get all the post view info to display
	$sql = $wpdb->prepare( "SELECT t.postcount, p.ID, p.post_title
			FROM $wpdb->app_pop_total AS t
			INNER JOIN $wpdb->posts AS p ON p.ID = t.postnum
			WHERE t.postcount > 0
			AND p.post_status = 'publish' AND p.post_type = %s
			ORDER BY t.postcount DESC LIMIT %d", $post_type, $limit );

	$results = $wpdb->get_results($sql);

	//echo $sql;

    echo '<ul class="pop">';

	// must be overall views
	if ($results) {

        foreach ($results as $result)
			echo '<li><a href="'.get_permalink($result->ID).'">'.$result->post_title.'</a> ('.number_format($result->postcount).'&nbsp;'.__('views', 'appthemes') .')</li>';

    } else {

		echo '<li>' . __('No ads viewed yet.', 'appthemes') . '</li>';

	}

	echo '</ul>';
}

// sidebar widget showing today's popular ads
function cp_todays_count_widget($post_type, $limit) {
    global $wpdb, $nowisnow;

	// get all the post view info to display
	$sql = $wpdb->prepare( "SELECT t.postcount, p.ID, p.post_title
			FROM $wpdb->app_pop_daily AS t
			INNER JOIN $wpdb->posts AS p ON p.ID = t.postnum
			WHERE time = %s
			AND t.postcount > 0 AND p.post_status = 'publish' AND p.post_type = %s
			ORDER BY t.postcount DESC LIMIT %d", $nowisnow, $post_type, $limit );

	$results = $wpdb->get_results($sql);

	echo '<ul class="pop">';

	// must be views today
    if ($results) {

        foreach ($results as $result)
			echo '<li><a href="'.get_permalink($result->ID).'">'.$result->post_title.'</a> ('.number_format($result->postcount).'&nbsp;'.__('views', 'appthemes') .')</li>';

    } else {

			echo '<li>' . __('No ads viewed yet.', 'appthemes') . '</li>';
	}

	echo '</ul>';

}

?>
