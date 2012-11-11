<?php

scb_register_table( 'app_geodata' );

add_action( 'appthemes_first_run', array( 'APP_Geo_Query', 'install' ), 9 );

add_action( 'init', array( 'APP_Geo_Query', 'add_query_vars' ) );
add_action( 'parse_query', array( 'APP_Geo_Query', 'parse_query' ) );
add_filter( 'posts_clauses', array( 'APP_Geo_Query', 'posts_clauses' ), 10, 2 );


function appthemes_get_coordinates( $post_id, $fallback_to_zero = true ) {
	global $wpdb;

	$coord = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->app_geodata WHERE post_id = %d", $post_id ) );

	if ( !$coord && $fallback_to_zero )
		return (object) array( 'lat' => 0, 'lng' => 0 );

	return $coord;
}

function appthemes_set_coordinates( $post_id, $lat, $lng ) {
	global $wpdb;

	$coord = appthemes_get_coordinates( $post_id, false );

	if ( !$coord ) {
		return $wpdb->insert( $wpdb->app_geodata, compact( 'lat', 'lng', 'post_id' ) );
	} else {
		return $wpdb->update( $wpdb->app_geodata, compact( 'lat', 'lng' ), compact( 'post_id' ) );
	}
}

function appthemes_delete_coordinates( $post_id ) {
	global $wpdb;

	return $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->app_geodata WHERE post_id = %d", $post_id ) );
}

function appthemes_enqueue_geo_scripts( $callback ) {
	list( $unit, $region ) = get_theme_support( 'app-geo' );

	$google_maps_url = is_ssl() ? 'https://maps-api-ssl.google.com/maps/api/js' : 'http://maps.google.com/maps/api/js';

	$params = array(
		'v' => 3,
		'sensor' => 'false',
		'region' => $region,
		'callback' => $callback
	);

	$google_maps_url = add_query_arg( $params, $google_maps_url );

	wp_enqueue_script( 'google-maps-api', $google_maps_url, array(), '3', true );
}


/**
 * Provides 'location' => 'San Francisco' and 'orderby' => 'distance' public query vars.
 */
class APP_Geo_Query {

	function install() {
		scb_install_table( 'app_geodata', "
			post_id bigint(20) unsigned NOT NULL,
			lat decimal(10,6) NOT NULL,
			lng decimal(10,6) NOT NULL,
			PRIMARY KEY  (post_id)
		" );
	}

	function uninstall() {
		scb_uninstall_table( 'app_geodata' );
	}

	function distance($lat_1, $lng_1, $lat_2, $lng_2, $unit = 'mi') {
		list( $unit, $region ) = get_theme_support( 'app-geo' );

		$earth_radius = 'mi' == $unit ? 3959 : 6371;

		$delta_lat = $lat_2 - $lat_1 ;
		$delta_lon = $lng_2 - $lng_1 ;
		$alpha    = $delta_lat/2;
		$beta     = $delta_lon/2;
		$a        = sin(deg2rad($alpha)) * sin(deg2rad($alpha)) + cos(deg2rad($lat_1)) * cos(deg2rad($lat_2)) * sin(deg2rad($beta)) * sin(deg2rad($beta)) ;
		$c        = asin(min(1, sqrt($a)));
		$distance = 2 * $earth_radius * $c;
		$distance = round( $distance, 4 );

		return $distance;
	}

	function add_query_vars() {
		global $wp;

		$wp->add_query_var( 'location' );
		$wp->add_query_var( 'radius' );
	}

	function parse_query( $wp_query ) {
		list( $unit, $region ) = get_theme_support( 'app-geo' );

		$location = trim( $wp_query->get( 'location' ) );
		if ( !$location )
			return;

		$wp_query->is_search = true;

		$radius = (int) $wp_query->get( 'radius' );
		if ( !$radius )
			$radius = 50;

		$args = array(
			'address' => urlencode($location),
			'sensor' => 'false',
			'region' => $region
		);

		$url = add_query_arg( $args, 'http://maps.googleapis.com/maps/api/geocode/json' );

		$transient_key = 'app_geo_' . md5( $url );

		if ( defined( 'WP_DEBUG' ) )
			$geo_coord = false;
		else
			$geo_coord = get_transient( $transient_key );

		if ( !$geo_coord ) {
			$response = wp_remote_get( $url );

			$geocode = json_decode( wp_remote_retrieve_body( $response ) );

		    $radius = (int) $wp_query->get( 'radius' );

			if ( !$radius ) {
				$bounds_distance = self::distance(
					$geocode->results[0]->geometry->bounds->northeast->lat,
					$geocode->results[0]->geometry->bounds->northeast->lng,
					$geocode->results[0]->geometry->bounds->southwest->lat,
					$geocode->results[0]->geometry->bounds->southwest->lng
				);

				// since distance is more of a diameter, and since the bounds are a square, lets use something more than half the diameter to make a radius (circle) thats clsoe to the the area of the square bounds
				$radius = $bounds_distance * .65;
			}

			if ( $geocode && 'OK' == $geocode->status ) {
				$geo_coord = array(
					'lat' => $geocode->results[0]->geometry->location->lat,
					'lng' => $geocode->results[0]->geometry->location->lng,
				);
				set_transient( $transient_key, $geo_coord, 60*60*24*7 ); // Cache for a week
			}
		}

		if ( $geo_coord ) {
			$wp_query->set( 'app_geo_query', array(
				'lat' => $geo_coord['lat'],
				'lng' => $geo_coord['lng'],
				'rad' => $radius
			) );
		} else {
			// Fall back to basic string matching
			$wp_query->set( 'meta_query', array(
				array(
					'key' => 'address',
					'value' => $location,
					'compare' => 'LIKE'
				)
			) );
		}
	}

	function posts_clauses( $clauses, $wp_query ) {
		list( $unit, $region ) = get_theme_support( 'app-geo' );

		global $wpdb;

		$geo_query = $wp_query->get( 'app_geo_query' );

		if ( !$geo_query )
			return $clauses;

		extract( $geo_query, EXTR_SKIP );

		$R = 'mi' == $unit ? 3959 : 6371;

		$clauses['join'] .= $wpdb->prepare( " INNER JOIN (
			SELECT post_id, ( %d * acos( cos( radians(%f) ) * cos( radians(lat) ) * cos( radians(lng) - radians(%f) ) + sin( radians(%f) ) * sin( radians(lat) ) ) ) AS distance FROM $wpdb->app_geodata
		) as distances ON ($wpdb->posts.ID = distances.post_id)
		", $R, $lat, $lng, $lat );

		$clauses['where'] .= $wpdb->prepare( " AND distance < %d", $rad );

		if ( 'distance' == $wp_query->get( 'orderby' ) ) {
			$clauses['orderby'] = 'distance ' . ( 'DESC' == strtoupper( $wp_query->get( 'order' ) ) ? 'DESC' : 'ASC' );
		}

		return $clauses;
	}
}

