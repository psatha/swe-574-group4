<?php

/**
 * Module for gathering post view statistics.
 * TODO: cleanup
 */

// get the local time based off WordPress setting
$nowisnow = date( 'Y-m-d', current_time( 'timestamp' ) );

// get the total page views and daily page views for a post
function appthemes_stats_counter( $post_id ) {
	$today = appthemes_get_stats_by( $post_id, 'today' );
	$total = appthemes_get_stats_by( $post_id, 'total' );

	if ( $total > 0 )
		echo $total . '&nbsp;' .__( 'total views', 'appthemes' ) . ', ' . $today . '&nbsp;' .__( 'today', 'appthemes' );
	else
		echo __( 'No views yet', 'appthemes' );
}

// record the page view
function appthemes_stats_update( $post_id ) {
	global $wpdb, $app_abbr, $nowisnow;

	$thepost = get_post( $post_id );

	if ( $thepost->post_author == get_current_user_id() ) return;

	// first try and update the existing total post counter
	$results = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->app_pop_total SET postcount = postcount+1 WHERE postnum = %s LIMIT 1", $post_id ) );

	// if it doesn't exist, then insert two new records
	// one in the total views, another in today's views
	if ( $results == 0 ) {
		$wpdb->insert( $wpdb->app_pop_total, array(
			"postnum" => $post_id,
			"postcount" => 1
		) );
		$wpdb->insert( $wpdb->app_pop_daily, array(
			"time" => $nowisnow,
			"postnum" => $post_id,
			"postcount" => 1
		) );
		// post exists so let's just update the counter
	} else {
		$results2 = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->app_pop_daily SET postcount = postcount+1 WHERE time = %s AND postnum = %s LIMIT 1", $nowisnow, $post_id ) );

		// insert a new record since one hasn't been created for current day
		if ( $results2 == 0 ) {
			$wpdb->insert( $wpdb->app_pop_daily, array(
				"time" => $nowisnow,
				"postnum" => $post_id,
				"postcount" => 1
			) );
		}
	}

	// get all the post view info so we can update meta fields
	$sql = $wpdb->prepare( "
		SELECT t.postcount AS total, d.postcount AS today
		FROM $wpdb->app_pop_total AS t
		INNER JOIN $wpdb->app_pop_daily AS d ON t.postnum = d.postnum
		WHERE t.postnum = %s AND d.time = %s
		", $post_id, $nowisnow );

	$row = $wpdb->get_row( $sql );

	// add the counters to temp values on the post so it's easy to call from the loop
	update_post_meta( $post_id, $app_abbr.'_daily_count', $row->today );
	update_post_meta( $post_id, $app_abbr.'_total_count', $row->total );
}

// collect statistical data for displayed posts
function appthemes_collect_stats() {
	global $wpdb, $app_stats, $posts, $pageposts, $wp_query, $nowisnow, $stats_data;

	if ( !isset( $app_stats ) )
		$app_stats = 'both';

	if ( isset( $posts ) && is_array( $posts ) )
		foreach ( $posts as $post )
			$post_ids[] = $post->ID;

	if ( isset( $pageposts ) && is_array( $pageposts ) )
		foreach ( $pageposts as $post )
			$post_ids[] = $post->ID;

	if ( isset( $wp_query->posts ) && is_array( $wp_query->posts ) )
		foreach ( $wp_query->posts as $post )
			$post_ids[] = $post->ID;

	if ( isset( $post_ids ) && is_array( $post_ids ) ) {
		$post_ids = array_unique( $post_ids );
		$post_list = implode( ",", $post_ids );
		if ( $app_stats == 'today' || $app_stats == 'both' )
			$todays = $wpdb->get_results( "SELECT postcount, postnum FROM $wpdb->app_pop_daily WHERE postnum IN ($post_list) AND time = '$nowisnow'" );
		if ( $app_stats == 'total' || $app_stats == 'both' )
			$totals = $wpdb->get_results( "SELECT postcount, postnum FROM $wpdb->app_pop_total WHERE postnum IN ($post_list)" );
	}

	if ( isset( $todays ) && is_array( $todays ) )
		foreach ( $todays as $today )
			$stats_data[$today->postnum]['today'] = $today->postcount;

	if ( isset( $totals ) && is_array( $totals ) )
		foreach ( $totals as $total )
			$stats_data[$total->postnum]['total'] = $total->postcount;

	if ( isset( $post_ids ) && is_array( $post_ids ) ) {
		foreach ( $post_ids as $post_id ) {
			if ( $app_stats == 'today' || $app_stats == 'both' )
				if ( !isset( $stats_data[$post_id]['today'] ) )
					$stats_data[$post_id]['today'] = 0;
			if ( $app_stats == 'total' || $app_stats == 'both' )
				if ( !isset( $stats_data[$post_id]['total'] ) )
					$stats_data[$post_id]['total'] = 0;
		}
	}
}

// get the total page views and daily page views for a post
function appthemes_get_stats_by( $post_id, $type = 'total' ) {
	global $wpdb, $app_abbr, $nowisnow, $stats_data;

	if ( $type == 'today' ) {

		if ( isset( $stats_data[$post_id]['today'] ) )
			$counter = $stats_data[$post_id]['today'];
		else
			$counter = $wpdb->get_var( $wpdb->prepare( "SELECT postcount FROM $wpdb->app_pop_daily WHERE postnum = %d AND time = %s", $post_id, $nowisnow ) );

	} else {

		if ( isset( $stats_data[$post_id]['total'] ) )
			$counter = $stats_data[$post_id]['total'];
		elseif ( get_post_meta( $post_id, $app_abbr.'_total_count', true ) != '' )
			$counter = get_post_meta( $post_id, $app_abbr.'_total_count', true );
		else
			$counter = $wpdb->get_var( $wpdb->prepare( "SELECT postcount FROM $wpdb->app_pop_total WHERE postnum = %d", $post_id ) );
	}

	if ( !isset( $counter ) || !is_numeric( $counter ) )
		$counter = 0;

	return $counter;
}
