<?php


if ( !function_exists('cp_upgrade_all') ) :
/**
 * Functions to be called in install and upgrade scripts.
 *
 *
 * @since 3.1.0
 */
function cp_upgrade_all() {
	global $app_abbr, $app_version, $app_current_db_version, $app_db_version, $wp_rewrite;
	
	$app_current_db_version = get_option($app_abbr.'_db_version');

	// We're up-to-date. Nothing to do.
	if ( $app_db_version == $app_current_db_version )
		return;

	// If the version is not set in the db, try to guess the version.
	if ( empty($app_current_db_version) )
		$app_current_db_version = 0;

	if ( $app_current_db_version < 1200 )
	    cp_upgrade_310();

	if ( $app_current_db_version < 1280 )
	    cp_update_advanced_search_db();

	if ( $app_current_db_version < 1290 )
	    cp_upgrade_317();

	if ( $app_current_db_version < 1300 )
			cp_upgrade_319();

	// Now that we're all done, set the version number
	update_option($app_abbr.'_version', $app_version);

}
endif;




/**
 * Execute changes made in ClassiPress 3.1.0.
 *
 * @since 3.1.0
 */
function cp_upgrade_310() {
	global $wpdb, $app_abbr, $app_version;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		maybe_add_column($wpdb->cp_ad_meta, 'field_search', "ALTER TABLE $wpdb->cp_ad_meta ADD `field_search` int(10) NOT NULL");
		maybe_add_column($wpdb->cp_ad_fields, 'field_min_length', "ALTER TABLE $wpdb->cp_ad_fields ADD `field_min_length` int(11) NOT NULL");
		maybe_add_column($wpdb->cp_ad_fields, 'field_validation', "ALTER TABLE $wpdb->cp_ad_fields ADD `field_validation` longtext DEFAULT NULL");
		maybe_add_column($wpdb->cp_ad_packs, 'pack_type', "ALTER TABLE $wpdb->cp_ad_packs ADD `pack_type` varchar(255) NOT NULL");
		maybe_add_column($wpdb->cp_ad_packs, 'pack_membership_price', "ALTER TABLE $wpdb->cp_ad_packs ADD `pack_membership_price` decimal(10,2) unsigned NOT NULL DEFAULT '0'");
    
    if ( get_option($app_abbr.'_distance_unit') == false ) update_option($app_abbr.'_distance_unit', 'mi');
    if ( get_option('embed_size_w') == false ) update_option('embed_size_w', 500); // set the WP maximum embed size width
    if ( get_option($app_abbr.'_membership_purchase_url') == false ) update_option($app_abbr.'_membership_purchase_url', 'membership');
    if ( get_option($app_abbr.'_membership_purchase_confirm_url') == false ) update_option($app_abbr.'_membership_purchase_confirm_url', 'membership-confirm');

    
    /**
    * create and set new membership page templates
    */
    
    $cur_ex_pages = array();
    
    $wpdb->get_results( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'membership' LIMIT 1" );
    if ( $wpdb->num_rows == 0 ) {
        $my_page = array( 'post_status' => 'publish', 'post_type' => 'page', 'post_author' => 1, 'post_name' => 'membership', 'post_title' => 'Memberships' );
        $page_id = wp_insert_post( $my_page );
        update_post_meta( $page_id, '_wp_page_template', 'tpl-membership-purchase.php' );
        $cur_ex_pages[] = $page_id;
    }

    $wpdb->get_results( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = 'membership-confirm' LIMIT 1" );
    if ( $wpdb->num_rows == 0 ) {
        $my_page = array( 'post_status' => 'publish', 'post_type' => 'page', 'post_author' => 1, 'post_name' => 'membership-confirm', 'post_title' => 'Membership Confirmation' );
        $page_id = wp_insert_post( $my_page );
        update_post_meta( $page_id, '_wp_page_template', 'tpl-membership-confirm.php' );
        $cur_ex_pages[] = $page_id;
    }
    
    // check to see if array of page ids is empty
    // if not, add them to the pages to be excluded from the nav meta option.
    if ( !empty( $cur_ex_pages ) ) {
        $all_ex_pages = array();
        
        // get all excluded pages
        $ex_pages = get_option( $app_abbr.'_excluded_pages' );

        if ( $ex_pages == true ) {
            // put page ids into an array
            $ex_pages = explode( ',', $ex_pages );
            
            // merge them with the new page ids
            $all_ex_pages = array_merge( $ex_pages, $cur_ex_pages );
            
            // convert back to a comma separated string for saving
            $all_ex_pages = implode( ',', $all_ex_pages );
        } else {
            // option doesn't exist so no existing page ids
            $all_ex_pages = implode( ',', $cur_ex_pages );
        }
        
        // update with the new list of excluded page ids
        update_option( $app_abbr.'_excluded_pages', appthemes_clean( $all_ex_pages ) );
    }
    
    update_option( 'cp_db_version', 1200 );
    update_option( $app_abbr.'_version', $app_version );

}




/**
 * These are functions to run for ClassiPress 3.0.5.
 *
 * @since 3.0.5
 */
 

// display nag if instance needs to be upgraded
function cp_upgrade_305_nag() {
	global $wpdb, $the_cats;
	
	if ( !current_user_can('manage_options') ) 
        return;

        // quick test to see if it's a new install (hack job)
        $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' LIMIT 10" );
        if ( $wpdb->num_rows < 10 )
            return;
    
        // see if ads have already been migrated over to new custom post type
        $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = '".APP_POST_TYPE."' LIMIT 1" );

        // upgrade already done
        if ( $wpdb->num_rows != 0) 
            return;
            

		// get all the blog categories in a comma delimited string
		$incats = cp_get_blog_cat_ids();
		
		echo $incats;

		$sql = $wpdb->prepare("SELECT count(ID)
		FROM $wpdb->posts wposts
		LEFT JOIN $wpdb->term_relationships ON (wposts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		WHERE $wpdb->term_taxonomy.taxonomy = 'category'
		AND $wpdb->term_taxonomy.term_id NOT IN($incats)
		AND post_type = 'post'");

		$ads_to_migrate = $wpdb->get_var( $sql );

		// get the blog cats and nice names
		$blog_cats = get_categories("hide_empty=0&include=$incats");
		foreach ( $blog_cats as $blog_cat ) {
			$the_cats .= $blog_cat->name . ', ';
        }

		$the_cats = trim( $the_cats, ', ' );

		// get the total count of ad categories
		$ad_cats = get_categories("hide_empty=0&exclude=$incats");
		$cats_to_migrate = count( $ad_cats );

		// get the total count of tags
		$all_tags = get_tags();
		$tags_to_migrate = count( $all_tags );
        ?>

		<div id="message2" class="updated">

			<h3>ClassiPress upgrade required</h3>
			<p>Your database needs to be updated before using this version of ClassiPress. It's important to first back-up your database <strong>BEFORE</strong> running this upgrade tool. We are not responsible for any lost or corrupt data. We recommend using the <a href='http://wordpress.org/extend/plugins/wp-db-backup/' target='_blank'>WP-DB plugin</a> to easily back-up your database. To install it directly from within WordPress, just go to your '<a href='plugin-install.php'>Add New</a>' plugins page and search for 'WP-DB-Backup'. For more instructions, see <a href='http://codex.wordpress.org/Backing_Up_Your_Database#Installation' target='_blank'>this page</a>.</p>

			<h3>What will this upgrade do?</h3>
			<p>As of version 3.0.5, ClassiPress takes advantage of the new custom post types and taxonomies available in WordPress so we need to migrate your ads, ad categories, and copy your tags from 'posts' to 'ads'. See the new 'Ads' menu group in your left-hand sidebar? Yep, that's where we're going to move them to.</p>
			<p>This script will take any ads NOT assigned to your blog categories (and blog sub-categories) which in your case are: <strong style="color:#009900;"><?php echo $the_cats ?></strong> and move them over. If this does not look correct or you wish to move ads out of these categories, please do so before running this script. These blog categories are determined by your "Blog Category ID" option on your settings page.</p>

			<p>This script will attempt to move your <strong style="color:#009900;"><?php echo number_format($ads_to_migrate); ?> ads, <?php echo number_format($cats_to_migrate); ?> ad categories, and <?php echo number_format($tags_to_migrate); ?> tags</strong> under the new 'Ads' menu group. <strong>NOTE</strong>: Only tags assigned to an ad will be moved over so less than the total tags found (<strong><?php echo number_format($tags_to_migrate); ?> tags</strong>) will likely be moved.</p>
			<p><strong>IMPORTANT:</strong> Once you click the update button below, there's no going back. Chances of anything going wrong are slim, and since you've already backed up your database, there's nothing to be worried about. :-) This may take a while depending on how many ads you have. Please only click the button once.</p>

			<form action="admin.php?page=settings" id="msgForm" method="post">
				<p class="submit btop">
					<input type="submit" value="Migrate My Ads" name="convert" onclick="return confirmUpdate();" />
				</p>
				<input type="hidden" value="convertToCustomPostType" name="submitted" />
			</form>

			<p><small><?php _e('Note: This message will not disappear until you have upgraded your database.', 'appthemes'); ?></small></p>

		</div>

	<script type="text/javascript">
        /* <![CDATA[ */
            function confirmUpdate() { return confirm("Are you sure you wish to run the ClassiPress upgrade script? Promise you already backed up your database? It's better to be safe than sorry! :-)"); }
        /* ]]> */
    </script>
<?php

}
add_action('admin_notices', 'cp_upgrade_305_nag', 3);


// convert post types to ad_listing
function cp_convert_posts2Ads() {
	global $wpdb, $app_version;

	echo '<div id="message2" class="updated" style="padding:10px 20px;">';

	// setup post conversion and stop if there are no valid ad listings in the posts table to convert
	$blogCatIDs = array();
	$blogCatIDs = cp_get_blog_cat_ids_array();

	// get all posts not in blog cats for quick check
	$args = array('category__not_in' => $blogCatIDs, 'post_status' => 'any', 'numberposts' => 10);
	$theposts = get_posts($args);

	if ( count($theposts) < 1 )
		wp_die('<h3>Migration script error</h3><p>Process did not run. No ad listings were found. You only have blog posts or your blog parent category ID is incorrect.</p>');

	// convert all the NON-BLOG categories to be part of the new ad_cat taxonomy
	echo '<p>Converting ad categories.........</p>';


	// get all category ids
	$cat_ids = get_all_category_ids();

	$cat_count_total = count($cat_ids);

	echo '<ul>';

	$cat_count = 0;

	foreach ( $cat_ids as $cat_id ) {

		// only move categories not belonging to the blog cats or blog sub cats
		if ( !in_array($cat_id, $blogCatIDs) ){
			$wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => APP_TAX_CAT ), array( 'term_id' => $cat_id ) );
			$thisCat = get_category($cat_id);
			echo '<li style="color:#009900"><strong>' . $thisCat->name . '</strong> (ID:' . $cat_id . ')' . ' category has been moved</li>';
			$cat_count ++;
		} else {
			$thisCat = get_category($cat_id);
			echo '<li><strong>' . $thisCat->name . '</strong> (ID:' . $cat_id . ')' . ' category has been skipped</li>';
		}

	}

	echo '</ul>';


	//convert all the NON-BLOG posts to be part of the new "ad_listing" taxonomy
	echo '<br /><p><strong>Converting posts........</strong></p>';

	$newTagsSummary = array();
	$post_count = 0;
	$ad_count = 0;
	$tag_count = 0;

	echo '<ul>';


	// get all the posts
	$args = array('post_status' => 'any', 'numberposts' => -1);
	$theposts = get_posts($args);

	foreach ( $theposts as $post ) {
		
		setup_postdata($post);    	

		// get the post terms
		$oldTags = wp_get_post_terms($post->ID);
		$newTags = array();			
		
		// get the cat object array for the post
		$post_cats = get_the_category($post->ID);

		// grab the first cat id found
		$cat_id = $post_cats[0]->cat_ID;

		//check if the post is in a blog category
		if (!in_array($cat_id, $blogCatIDs)){

			// if yes, then first see if it has any tags
			if (!empty($oldTags)) {
				foreach($oldTags as $thetag) :
					$newTags[] = $thetag->name;
					$newTagsSummary[] = '<li style="color:#009900"><strong>"' . $thetag->name . '"</strong> tag has been copied</li>';
					$tag_count++;
				endforeach;
			}

			// copy the tag array over if it's not empty
			if (!empty($newTags))
				wp_set_post_terms($post->ID, $newTags, APP_TAX_TAG);

			//now change the post to an ad
			set_post_type($post->ID, APP_POST_TYPE);
			echo '<li style="color:#009900"><strong>"' . $post->post_title . '"</strong> (ID:' . $post->ID . ') post was converted</li>';
			$ad_count++;

		// not an ad so must be a blog post
		} else {

			// see if it has tags since we still want to echo them not moved
			if (!empty($oldTags)) {
			foreach($oldTags as $thetag) {
				$newTags[] = $thetag->name;
				$newTagsSummary[] = '<li><strong>"' . $thetag->name . '"</strong> tag has been skipped</li>';
				//$tag_count++;
				}
			}

			echo '<li><strong>"<a href="post.php?post='.$post->ID.'&action=edit" target="_blank">' . $post->post_title . '</a>"</strong> (ID:' . $post->ID . ') post has been skipped (in blog or blog-sub category)</li>';
		}

		$post_count++;
		
	}

	
	echo '<br/><p><strong>Copying tags...........</strong></p>';

	// get the total count of tags
	$all_tags = get_tags();
	$tags_count_total = count($all_tags);


	// calculate the results
	$blog_cats_total = $cat_count_total - $cat_count;
	$blog_posts_total = $post_count - $ad_count;
	$blog_tags_total = $tags_count_total - $tag_count;

	// print out all the tags
	foreach($newTagsSummary as $key => $value)
		echo $value;

	echo '</ul><br/>';

	echo '<h3>Migration Summary</h3>';
	echo '<p>Total categories converted: <strong>' . $cat_count . '/'.$cat_count_total.'</strong>  <small>(excluded '.$blog_cats_total.' blog categories)</small><br/>';
	echo 'Total posts converted: <strong>' . $ad_count . '/'.$post_count.'</strong>  <small>(excluded '.$blog_posts_total.' blog posts)</small><br/>';
	echo 'Total tags copied: <strong>' . $tag_count . '/'.$tags_count_total.'</strong>  <small>(excluded '.$blog_tags_total.' tags not assigned to ads)</small><br/>';

	echo '<br/><p><strong>The ads conversion utility has completed!</strong><br/><br/>Note: If for some reason an ad did not get converted, you can manually do it via the "Post Type" option on the edit post page.</p>';


	//reset the old version to current so this script doesn't appear again
	// update_option('cp_version_old', $app_version);
?>

	<form action="admin.php?page=settings" id="msgForm" method="post">
		<p class="submit btop">
			<input type="submit" value="Run Migration Script Again?" name="convert" />
		</p>
		<input type="hidden" value="convertToCustomPostType" name="submitted" />
	</form>

	<p><strong>IMPORTANT: </strong>If you navigate away from this page, you will no longer be able to access this script. If you wish to run it again, open another browser tab and make your changes there first. Then come back and push the above button and the script will re-run.</p>

	<?php

	echo '</div>';
}


/**
* ClassiPress 3.1.0 geocoding migration script
*
* @since 3.1.0
*/
function cp_update_advanced_search_db() {
	global $wpdb, $app_current_db_version;

	// get the ClassiPress db version number
	$app_current_db_version = get_option( 'cp_db_version' );

	cp_create_geocode_table();

	$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = '" . APP_POST_TYPE . "' ORDER BY ID ASC" ) );
	if ( $post_ids ) {
	?>
        <div class="wrap">
        <?php screen_icon( 'themes' ); ?>
        <h2><?php _e( 'ClassiPress Update', 'appthemes' ); ?></h2>
        <p class="info"><?php _e( 'Geocoding ad listing addresses to make the advanced search radius feature work. This process queries Google Maps to get longitude and latitude coordinates based on each ad listings address. Please be patient as this may take a few minutes to complete.', 'appthemes' ); ?></p>
        <?php

        foreach ( $post_ids as $post_id ) {
            if ( ! cp_get_geocode( $post_id ) ) {
                $result = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key IN ('cp_street','cp_city','cp_state','cp_zipcode','cp_country')", $post_id ), OBJECT_K );
                $address = '';
                foreach( $result as $cur ) {
                    if ( ! empty( $cur->meta_key ) )
                        $address .= "{$cur->meta_value}, ";
                }
                $address = rtrim( $address, ', ' );
                if ( $address ) {
                    printf( '<p>' . __( "Ad #%d - %s ", 'appthemes' ), $post_id, $address );
                    $geocode = json_decode( wp_remote_retrieve_body( wp_remote_get( 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode( $address ) . '&sensor=false' ) ) );
                    if ( 'OK' == $geocode->status ) {
                        echo esc_html( "({$geocode->results[0]->geometry->location->lat}, {$geocode->results[0]->geometry->location->lng})" );
                        $category = get_the_terms( $post_id, APP_TAX_CAT );
                        cp_update_geocode( $post_id, $category[0]->name, $geocode->results[0]->geometry->location->lat, $geocode->results[0]->geometry->location->lng );
                        echo ' &raquo; <font color="green">' . __( 'Geocoding complete.', 'appthemes' ) . '</font>';
                    } else {
                        echo ' &raquo; <font color="red">' . __( 'Geocoding failed - address not found.', 'appthemes' ) . '</font>';
                    }
                    echo '</p>';
                }
            }
        }
    
        echo '<br /><strong>' . __( 'Database update complete.', 'appthemes' ) . '</strong><br />';
        echo '<small>' . __( 'Please note: Ads that failed during this process will not show up during a radius search since the address was invalid. Ignore this message for new installs.', 'appthemes' ) . '</small>';
        echo '<br /><br /><a class="button" href="admin.php?page=admin-options.php">' . __( 'Continue to Your Dashboard', 'appthemes' ) . '</a>';
        ?>
        </div>
	<?php
	    update_option( 'cp_db_version', 1280 );
	    include( ABSPATH . '/wp-admin/admin-footer.php');
	    exit;
	} // end if $post_ids
	
	update_option( 'cp_db_version', 1280 );
		
}

function cp_create_geocode_table() {
	global $wpdb;

	// create the geocodes table - store geo location data

		$sql = "
					id int(20) NOT NULL auto_increment,
					post_id int(20) NOT NULL DEFAULT '0',
					category varchar(200) NOT NULL,
					lat float( 10, 6 ) NOT NULL,
					lng float( 10, 6 ) NOT NULL,
					PRIMARY KEY (id)";

	scb_install_table( 'cp_ad_geocodes', $sql, '' );

}

/**
 * Execute changes made in ClassiPress 3.1.7.
 *
 * @since 3.1.7
 */
function cp_upgrade_317() {
	global $wpdb, $app_abbr, $app_version;

	$sql = $wpdb->prepare( "SELECT field_name FROM $wpdb->cp_ad_fields WHERE field_type = 'checkbox' " );
  $results = $wpdb->get_results( $sql );
  
  if ($results) :
    foreach ( $results as $result ) :
      $sql_meta = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s GROUP BY post_id", $result->field_name );
      $results_meta = $wpdb->get_results( $sql_meta );
      
      if ($results_meta) {
        foreach ( $results_meta as $meta ){
          $post_meta = get_post_meta($meta->post_id, $result->field_name, true);
          if(!empty($post_meta)){
            delete_post_meta($meta->post_id, $result->field_name);
            delete_post_meta($meta->post_id, $result->field_name.'_list');
            $post_meta_vals = explode(",", $post_meta);
            if(is_array($post_meta_vals))
              foreach($post_meta_vals as $checkbox_value)
                add_post_meta($meta->post_id, $result->field_name, $checkbox_value );
          }
        }
      }
    endforeach;
  endif;

	update_option( 'cp_db_version', 1290 );
		
}

/**
 * Execute changes made in ClassiPress 3.1.9.
 *
 * @since 3.1.9
 */
function cp_upgrade_319() {
	global $wpdb, $app_abbr, $app_version;

	// set defaults for new options
	if (get_option($app_abbr.'_cat_menu_depth') == false) update_option($app_abbr.'_cat_menu_depth', 3);
	if (get_option($app_abbr.'_cat_menu_sub_num') == false) update_option($app_abbr.'_cat_menu_sub_num', 3);
	if (get_option($app_abbr.'_cat_dir_depth') == false) update_option($app_abbr.'_cat_dir_depth', 3);
	if (get_option($app_abbr.'_cat_dir_sub_num') == false) update_option($app_abbr.'_cat_dir_sub_num', 3);
	if (get_option($app_abbr.'_cat_count') == false) update_option($app_abbr.'_cat_count', 0);

	if (get_option($app_abbr.'_search_custom_fields') == false) update_option($app_abbr.'_search_custom_fields', 'no');

	// Add Currency field
	$sql = $wpdb->prepare( "SELECT field_id FROM $wpdb->cp_ad_fields WHERE field_name = %s ", $app_abbr .'_currency' );
	$results = $wpdb->get_results( $sql );
	if( !$results ) {

				// Currency field
				$wpdb->insert( $wpdb->cp_ad_fields, array(
						'field_name' => $app_abbr.'_currency',
						'field_label' => 'Currency',
						'field_desc' => 'This is the currency drop-down select box for the ad. Add it to the form below the price to allow users to choose the currency for the ad price.',
						'field_type' => 'drop-down',
						'field_values' => '$,€,£,¥',
						'field_search' => '',
						'field_perm' => '0',
						'field_core' => '0',
						'field_req' => '0',
						'field_owner' => 'ClassiPress',
						'field_created' => date_i18n("Y-m-d H:i:s"),
						'field_modified' => date_i18n("Y-m-d H:i:s"),
						'field_min_length' => '0'
				) );

	}

	update_option( 'cp_db_version', 1300 );

}


?>