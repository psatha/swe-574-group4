<?php

/**
 * This creates all the fields and assembles them
 * on the ad category sidebar based on either custom forms
 * built by the admin or it just defaults to a
 * standard form which has been pre-defined.
 *
 * @global <type> $wpdb
 * @param <type> $results
 *
 *
 */


// queries the db for the custom ad form based on the cat id
if ( !function_exists('cp_show_refine_search') ) :
    function cp_show_refine_search($catid) {
        global $wpdb;
        $fid = '';

        // get the category ids from all the form_cats fields.
        // they are stored in a serialized array which is why
        // we are doing a separate select. If the form is not
        // active, then don't return any cats.

        $results = $wpdb->get_results( $sql = $wpdb->prepare( "SELECT ID, form_cats FROM $wpdb->cp_ad_forms WHERE form_status = 'active'" ) );

        if ( $results ) :

            // now loop through the recordset
            foreach ( $results as $result ) {

                // put the form_cats into an array
                $catarray = unserialize( $result->form_cats );

                // now search the array for the $catid which was passed in via the cat drop-down
                if ( in_array($catid, $catarray) )
                    $fid = $result->ID; // when there's a catid match, grab the form id
            }

            // now we should have the formid so show the form layout based on the category selected
            $sql = $wpdb->prepare( "SELECT f.field_label, f.field_name, f.field_type, f.field_values, f.field_perm, m.field_search, m.meta_id, m.field_pos, m.field_req, m.form_id "
                     . "FROM $wpdb->cp_ad_fields f "
                     . "INNER JOIN $wpdb->cp_ad_meta m "
                     . "ON f.field_id = m.field_id "
                     . "WHERE m.form_id = %s "
          					 . "AND f.field_type <> 'text area' "
          					 . "AND m.field_search = '1' "
                     . "ORDER BY m.field_pos ASC",
                     $fid);

            $results = $wpdb->get_results( $sql );

			// echo $sql;
			//print_r($results);

            if ( $results )
                echo cp_refine_search_builder($results); // loop through the custom form fields and display them

        endif;

    }
endif;


// loops through the custom fields and builds the custom refine search in the sidebar
if ( !function_exists('cp_refine_search_builder') ) :
    function cp_refine_search_builder($results) {
        global $wpdb;
		$cp_min_price = str_replace( ',', '', $wpdb->get_var( "SELECT min( CAST( meta_value AS UNSIGNED ) ) FROM $wpdb->postmeta WHERE meta_key = 'cp_price'" ) );
		$cp_max_price = str_replace( ',', '', $wpdb->get_var( "SELECT max( CAST( meta_value AS UNSIGNED ) ) FROM $wpdb->postmeta WHERE meta_key = 'cp_price'" ) );

		$locarray = array();
?>
	<script type="text/javascript">
	// <![CDATA[
	// toggles the refine search field values
	jQuery(document).ready(function($) {
		$('div.handle').click(function() {
			$(this).next('div.element').animate({
				height: ['toggle', 'swing'],
				opacity: 'toggle' }, 200
			);

			$(this).toggleClass('close', 'open');
			return false;
		});
		<?php foreach ( $_GET as $field => $val ) : ?>
		$('.<?php echo esc_js($field); ?> div.handle').toggleClass('close', 'open');
		$('.<?php echo esc_js($field); ?> div.element').show();
		<?php endforeach; ?>

	});
	// ]]>
	</script>

	<div class="shadowblock_out">

		<div class="shadowblock">

			<h2 class="dotted"><?php _e('Refine Results', 'appthemes') ?></h2>

			<ul class="refine">

				<form action="<?php if ( is_tax( APP_TAX_CAT ) ) echo get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); else bloginfo('wpurl'); ?>" method="get" name="refine-search">
				<?php if ( !is_tax( APP_TAX_CAT ) ) { ?>
					<input type="hidden" name="s" value="<?php echo esc_attr(cp_get_search_term()); ?>" />
					<input type="hidden" name="scat" value="<?php echo esc_attr(cp_get_search_catid()); ?>" />
				<?php } ?>

					<?php
					// grab the price and location fields first and put into a separate array
					// then remove them from the results array so they don't print out again
					foreach ( $results as $key => $value ) {

						switch ( $value->field_name ) :

							case 'cp_city':
								$locarray[0] = $results[$key];
								unset($results[$key]);
							break;

							case 'cp_zipcode':
								$locarray[1] = $results[$key];
								unset($results[$key]);
							break;

							case 'cp_price':
								$locarray[2] = $results[$key];
								unset($results[$key]);
							break;

							case 'cp_country':
								$locarray[3] = $results[$key];
								unset($results[$key]);
							break;

							case 'cp_region':
								$locarray[4] = $results[$key];
								unset($results[$key]);
							break;

						endswitch;
					}

					// sort array by key so we get the city/zip code first
					ksort( $locarray );

					// echo '<pre>';
					// print_r($locarray);
					// echo '</pre><br/><br/>';

					// both zip code and city have been checked
					if ( array_key_exists(0, $locarray) && array_key_exists(1, $locarray) ) {
						$flabel = sprintf(__("%s or %s", 'appthemes'), $locarray[0]->field_label, $locarray[1]->field_label);
						$fname = 'cp_city_zipcode';
					} elseif ( array_key_exists(0, $locarray) ) { // must be the city only
						$flabel = $locarray[0]->field_label;
						$fname = 'cp_city_zipcode';
					} elseif ( array_key_exists(1, $locarray) ) { // must be the zip code only
						$flabel = $locarray[1]->field_label;
						$fname = 'cp_city_zipcode';
					}

					$distance_unit = 'mi' == get_option( 'cp_distance_unit', 'mi' ) ? 'miles' : 'kilometers';
					// show the city/zip code field and radius slider bar
					if ( array_key_exists(0, $locarray) || array_key_exists(1, $locarray) ) :
					?>
						<script type="text/javascript">
						// <![CDATA[
							jQuery(document).ready(function($) {
								$('#dist-slider').slider( {
									range: 'min',
									min: 0,
									max: 100,
									value: <?php echo esc_js( isset( $_GET['distance'] ) ? intval( $_GET['distance'] ) : '50' ); ?>,
									step: 5,
									slide: function(event, ui) {
										$('#distance').val(ui.value + ' <?php _e($distance_unit, 'appthemes'); ?>');
									}
								});
								$('#distance').val($('#dist-slider').slider('value') + '  <?php _e($distance_unit, 'appthemes'); ?>');
							});
						// ]]>
						</script>

						<li class="distance">
							<label class="title"><?php echo $flabel; ?></label>
							<input name="<?php echo esc_attr( $fname ); ?>" id="<?php echo esc_attr( $fname ); ?>" type="text" minlength="2" value="<?php if(isset($_GET[$fname])) echo esc_attr( $_GET[$fname] ); ?>" class="text" />
							<div class="clr"></div>
							<label for="distance" class="title"><?php _e('Radius', 'appthemes'); ?>:</label>
							<input type="text" id="distance" name="distance" />
							<div id="dist-slider"></div>
						</li>

					<?php

					endif;

					// now loop through the other special fields
					foreach ( $locarray as $value ) :

						// show the price field range slider
						if ( $value->field_name == 'cp_price' ) {
							$curr_symbol = get_option( 'cp_curr_symbol', '$' );
							$cp_curr_symbol_pos = get_option( 'cp_curr_symbol_pos', 'left' );
							if ( isset( $_GET['amount'] ) )
								$amount = explode( ' - ', $_GET['amount'] );
							$amount[0] = empty( $amount[0] ) ? $cp_min_price : $amount[0];
							$amount[1] = empty( $amount[1] ) ? $cp_max_price : $amount[1];
							$amount[0] = str_replace( array( ',', $curr_symbol, ' ' ), '', $amount[0] );
							$amount[1] = str_replace( array( ',', $curr_symbol, ' ' ), '', $amount[1] );
							?>

							<script type="text/javascript">
							// <![CDATA[
								jQuery(document).ready(function($) {
									$('#slider-range').slider( {
									  range: true,
									  min: <?php echo esc_js( intval( $cp_min_price ) ); ?>,
									  max: <?php echo esc_js( intval( $cp_max_price ) ); ?>,
									  step: 1,
									  values: [ <?php echo esc_js( "{$amount[0]}, {$amount[1]}" ); ?> ],
									  slide: function(event, ui) {
										<?php switch ( $cp_curr_symbol_pos ) {
											case 'left' :
												?>$('#amount').val('<?php echo $curr_symbol; ?>' + ui.values[0] + ' - <?php echo $curr_symbol; ?>' + ui.values[1]);<?php
												break;
											case 'left_space' :
											?>$('#amount').val('<?php echo $curr_symbol; ?> ' + ui.values[0] + ' - <?php echo $curr_symbol; ?> ' + ui.values[1]);<?php
												break;
											case 'right' :
											?>$('#amount').val(ui.values[0] + '<?php echo $curr_symbol; ?> - ' + ui.values[1] + '<?php echo $curr_symbol; ?>' );<?php
												break;
											case 'right_space' :
											?>$('#amount').val(ui.values[0] + ' <?php echo $curr_symbol; ?> - ' + ui.values[1] + ' <?php echo $curr_symbol; ?>' );<?php
												break;
										} ?>
									  }
									});
									<?php switch ( $cp_curr_symbol_pos ) {
										case 'left' :
											?>$('#amount').val('<?php echo $curr_symbol; ?>' + $('#slider-range').slider('values', 0) + ' - <?php echo $curr_symbol; ?>' + $('#slider-range').slider('values', 1));<?php
											break;
										case 'left_space' :
										?>$('#amount').val('<?php echo $curr_symbol; ?> ' + $('#slider-range').slider('values', 0) + ' - <?php echo $curr_symbol; ?> ' + $('#slider-range').slider('values', 1));<?php
											break;
										case 'right' :
										?>$('#amount').val($('#slider-range').slider('values', 0) + '<?php echo $curr_symbol; ?> - ' + $('#slider-range').slider('values', 1) + '<?php echo $curr_symbol; ?>');<?php
											break;
										case 'right_space' :
										?>$('#amount').val($('#slider-range').slider('values', 0) + ' <?php echo $curr_symbol; ?> - ' + $('#slider-range').slider('values', 1) + ' <?php echo $curr_symbol; ?>');<?php
											break;
										} ?>

								});
							// ]]>
							</script>

							<li class="amount">
								<label class="title"><?php echo esc_html( translate( $value->field_label, 'appthemes' ) ); ?>:</label>
								<input type="text" id="amount" name="amount" />
								<div id="slider-range"></div>
							</li>
							<?php

						}

						if ( 'cp_region' == $value->field_name || 'cp_country' == $value->field_name )
							echo cp_refine_fields($value->field_label, $value->field_name, $value->field_values);


						// show the state values
						// uncomment to include states
						// if ( $value->field_name == 'cp_state' )
							// echo cp_refine_fields( $value->field_label, $value->field_name, $value->field_values );

					endforeach;


					// echo '<pre>';
					// print_r($results);
					// echo'</pre>';


					foreach ( $results as $key => $result ) {

						switch ( $result->field_type ) :

                            // case 'text box':
                            case 'radio':
                            case 'checkbox':
                            case 'drop-down':

                            echo cp_refine_fields( $result->field_label, $result->field_name, $result->field_values );

                            break;

						endswitch;
						?>

					<?php
					}
					?>
					<div class="pad10"></div>
					<button class="obtn btn_orange" type="submit" tabindex="1" id="go" value="Go" name="sa"><?php _e('Refine Results &rsaquo;&rsaquo;', 'appthemes'); ?></button>
          
          <input type="hidden" name="refine_search" value="yes" />
          
				</form>

			</ul>

			<div class="clr"></div>

		</div>

	</div>

	<?php
    }
endif;


// spit out the field names and values
function cp_refine_fields($label, $name, $values) {
?>

	<li class="<?php echo esc_attr( $name ); ?>">
		<label class="title"><?php echo esc_html( translate( $label, 'appthemes' ) ); ?></label>

		<div class="handle close"></div>

		<div class="element">

			<?php
			$options = explode(',', $values);
			$optionCursor = 1;
			$checked = '';
			?>

			<div class="scrollbox">

				<ol class="checkboxes">

					<?php
					$cur = isset( $_GET[$name] ) ? $_GET[$name] : array();
					foreach ( $options as $option ) {
						if ( $cur )
							$checked = in_array( trim( $option ), $cur ) ? " checked='checked'" : ''; ?>
						<li>
							<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( trim( $option ) ); ?>" <?php echo $checked;?>/>&nbsp;<label for="<?php echo esc_attr( $name ); ?>[]"><?php echo esc_html( trim($option) ); ?></label>
						</li> <!-- #checkbox -->
			  <?php } ?>

				</ol> <!-- #checkbox-wrap -->

			</div> <!-- #end scrollbox -->

		</div> <!-- #end element -->

		<div class="clr"></div>

	</li>
<?php
}

function cp_pre_get_posts( $query ) {
	global $wpdb;
	if ( $query->is_archive && isset( $query->query_vars['ad_cat'] ) ) {
		$meta_query = array();
		foreach ( $_GET as $key => $value ) {
			if ( $value )
			switch ( $key ) {
				case 'cp_city_zipcode' :
					$region = get_option( 'cp_gmaps_region', 'us' );
					$value = urlencode( $value );
					$geocode = json_decode( wp_remote_retrieve_body( wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=$value&sensor=false&region=$region" ) ) );
					if ( 'OK' == $geocode->status ) {
						$query->set( 'app_geo_query', array(
							'lat' => $geocode->results[0]->geometry->location->lat,
							'lng' => $geocode->results[0]->geometry->location->lng,
							'rad' => intval( $_GET['distance'] ),
						) );
					} else {
						// Google Maps API error
					}
					break;

				case 'amount' :
					$value = str_replace( array( get_option( 'cp_curr_symbol' ), ' ' ), '', $value );
					$value = str_replace( ' ', '', $value );
					$meta_query[] = array(
								'key' => 'cp_price',
								'value' => explode( '-', $value ),
								'compare' => 'BETWEEN',
								'type' => 'numeric',
					);
					break;

				default :
					if ( 'cp_' == substr( $key, 0, 3 ) ) {
						$meta_query[] = array(
							'key'   => $key,
							'value' => $value,
							'compare' => 'IN'
						);
					}
					break;
			}
		}
		$query->set( 'meta_query', $meta_query );
		//var_dump( $_GET );
	}

	return $query;
}
add_filter( 'pre_get_posts', 'cp_pre_get_posts' );

function cp_posts_clauses( $clauses, $wp_query ) {
	global $wpdb;

	$geo_query = $wp_query->get( 'app_geo_query' );
	if ( !$geo_query )
		return $clauses;

	extract( $geo_query, EXTR_SKIP );

	$R = 'mi' == get_option( 'cp_distance_unit', 'mi' ) ? 3959 : 6371;
	$table = $wpdb->cp_ad_geocodes;

	$clauses['join'] .= " INNER JOIN $table ON ($wpdb->posts.ID = $table.post_id)";

	$clauses['where'] .= $wpdb->prepare( " AND ( %d * acos( cos( radians(%f) ) * cos( radians(lat) ) * cos( radians(lng) - radians(%f) ) + sin( radians(%f) ) * sin( radians(lat) ) ) ) < %d", $R, $lat, $lng, $lat, $rad );

	return $clauses;
}
add_filter( 'posts_clauses', 'cp_posts_clauses', 10, 2 );


// refine search on custom fields
function custom_search_refine_where($where) {
    global $wpdb, $wp_query, $refine_count;

    $refine_count = 0; // count how many post meta we query
    $old_where = $where; // intercept the old where statement

    if ( is_search() && isset($_GET['s']) && isset($_GET['refine_search']) ) {
      $query = '';
    
  		foreach ( $_GET as $key => $value ) {
        if ( $value ) {
    			switch ( $key ) {
    				case 'cp_city_zipcode' :
    					$region = get_option( 'cp_gmaps_region', 'us' );
    					$value = urlencode( $value );
    					$geocode = json_decode( wp_remote_retrieve_body( wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=$value&sensor=false&region=$region" ) ) );
    					if ( 'OK' == $geocode->status ) {
    						$wp_query->set( 'search_geo_query', array(
    							'lat' => $geocode->results[0]->geometry->location->lat,
    							'lng' => $geocode->results[0]->geometry->location->lng,
    							'rad' => intval( $_GET['distance'] ),
    						) );
    					} else {
    						// Google Maps API error
    					}
  					break;

    				case 'amount' :
              $refine_count++;  
    					$value = str_replace( array( get_option( 'cp_curr_symbol' ), ' ' ), '', $value );
    					$value = str_replace( ' ', '', $value );
              $value = explode( '-', $value );
              
              $query .= " AND (";
              $query .= "(mt".$refine_count.".meta_key = 'cp_price')";
              $query .= " AND (CAST(mt".$refine_count.".meta_value AS SIGNED) BETWEEN '$value[0]' AND '$value[1]')";
              $query .= ")";
  					break;

    				default :
    					if ( 'cp_' == substr( $key, 0, 3 ) ) {
                $refine_count++;  
                if(is_array($value))
                  $value = implode("','",$value);
                
                $query .= " AND (";
                $query .= "(mt".$refine_count.".meta_key = '$key')";
                $query .= " AND (CAST(mt".$refine_count.".meta_value AS CHAR) IN ('$value'))";
                $query .= ")";
    					}
  					break;
  			  }
        }
		  }

    	$geo_query = $wp_query->get( 'search_geo_query' );
    	if ( $geo_query ) {
      	extract( $geo_query, EXTR_SKIP );
        $R = 'mi' == get_option( 'cp_distance_unit', 'mi' ) ? 3959 : 6371;
        $query .= $wpdb->prepare( " AND ( %d * acos( cos( radians(%f) ) * cos( radians(lat) ) * cos( radians(lng) - radians(%f) ) + sin( radians(%f) ) * sin( radians(lat) ) ) ) < %d", $R, $lat, $lng, $lat, $rad );
      }

      if ( !empty($query) )
        $where .= $query;

    }

    return( $where );
}


// refine search on custom fields
function custom_search_refine_join($join) {
    global $wpdb, $wp_query, $refine_count;

    if ( is_search() && isset($_GET['s']) && isset($_GET['refine_search']) ) {
    
    	$geo_query = $wp_query->get( 'search_geo_query' );
    	if ( $geo_query ) {
        	$table = $wpdb->cp_ad_geocodes;
        	$join .= " INNER JOIN $table ON ($wpdb->posts.ID = $table.post_id)";
      }

    	if ( isset($refine_count) && is_numeric($refine_count) && $refine_count > 0 ) {
        for ($i = 1; $i <= $refine_count; $i++) {
      		$join .= " INNER JOIN $wpdb->postmeta AS mt".$i." ON ($wpdb->posts.ID = mt".$i.".post_id) ";
        }
      }

    }

    return $join;
}

