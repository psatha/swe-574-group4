<?php
/**
 * Custom post types and taxonomies
 *
 *
 * @version 3.0.5
 * @author AppThemes
 * @package ClassiPress
 *
 */



// create the custom post type and category taxonomy for ad listings
function cp_ad_listing_post_type() {
    global $app_abbr;

    // get the slug value for the ad custom post type & taxonomies
    if(get_option($app_abbr.'_post_type_permalink')) $post_type_base_url = get_option($app_abbr.'_post_type_permalink'); else $post_type_base_url = 'ads';
    if(get_option($app_abbr.'_ad_cat_tax_permalink')) $cat_tax_base_url = get_option($app_abbr.'_ad_cat_tax_permalink'); else $cat_tax_base_url = 'ad-category';
    if(get_option($app_abbr.'_ad_tag_tax_permalink')) $tag_tax_base_url = get_option($app_abbr.'_ad_tag_tax_permalink'); else $tag_tax_base_url = 'ad-tag';

    // register the new post type
    register_post_type( APP_POST_TYPE,
        array( 'labels' => array(
            'name' => __( 'Ads', 'appthemes' ),
            'singular_name' => __( 'Ad', 'appthemes' ),
            'add_new' => __( 'Add New', 'appthemes' ),
            'add_new_item' => __( 'Create New Ad', 'appthemes' ),
            'edit' => __( 'Edit', 'appthemes' ),
            'edit_item' => __( 'Edit Ad', 'appthemes' ),
            'new_item' => __( 'New Ad', 'appthemes' ),
            'view' => __( 'View Ads', 'appthemes' ),
            'view_item' => __( 'View Ad', 'appthemes' ),
            'search_items' => __( 'Search Ads', 'appthemes' ),
            'not_found' => __( 'No ads found', 'appthemes' ),
            'not_found_in_trash' => __( 'No ads found in trash', 'appthemes' ),
            'parent' => __( 'Parent Ad', 'appthemes' ),
            ),
            'description' => __( 'This is where you can create new classified ads on your site.', 'appthemes' ),
            'public' => true,
            'show_ui' => true,
	    'has_archive' => true,
            'capability_type' => 'post',
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'menu_position' => 8,
            'menu_icon' => FAVICON,
            'hierarchical' => false,
            'rewrite' => array( 'slug' => $post_type_base_url, 'with_front' => false ),
            'query_var' => true,
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'sticky' ),
            )
);

    // register the new ad category taxonomy
    register_taxonomy( APP_TAX_CAT,
            array(APP_POST_TYPE),
            array('hierarchical' => true,
                  'labels' => array(
                        'name' => __( 'Ad Categories', 'appthemes'),
                        'singular_name' => __( 'Ad Category', 'appthemes'),
                        'search_items' =>  __( 'Search Ad Categories', 'appthemes'),
                        'all_items' => __( 'All Ad Categories', 'appthemes'),
                        'parent_item' => __( 'Parent Ad Category', 'appthemes'),
                        'parent_item_colon' => __( 'Parent Ad Category:', 'appthemes'),
                        'edit_item' => __( 'Edit Ad Category', 'appthemes'),
                        'update_item' => __( 'Update Ad Category', 'appthemes'),
                        'add_new_item' => __( 'Add New Ad Category', 'appthemes'),
                        'new_item_name' => __( 'New Ad Category Name', 'appthemes')
                    ),
                    'show_ui' => true,
                    'query_var' => true,
					'update_count_callback' => '_update_post_term_count',
                    'rewrite' => array( 'slug' => $cat_tax_base_url, 'with_front' => false, 'hierarchical' => true ),
            )
    );

    // register the new ad tag taxonomy
    // EKLEME (CIKARMA ISLEMI)
    //register_taxonomy( APP_TAX_TAG,
            //array(APP_POST_TYPE),
            //array('hierarchical' => false,
                  //'labels' => array(
                        //'name' => __( 'Ad Tags', 'appthemes'),
                        //'singular_name' => __( 'Ad Tag', 'appthemes'),
                        //'search_items' =>  __( 'Search Ad Tags', 'appthemes'),
                        //'all_items' => __( 'All Ad Tags', 'appthemes'),
                        //'parent_item' => __( 'Parent Ad Tag', 'appthemes'),
                        //'parent_item_colon' => __( 'Parent Ad Tag:', 'appthemes'),
                        //'edit_item' => __( 'Edit Ad Tag', 'appthemes'),
                        //'update_item' => __( 'Update Ad Tag', 'appthemes'),
                        //'add_new_item' => __( 'Add New Ad Tag', 'appthemes'),
                        //'new_item_name' => __( 'New Ad Tag Name', 'appthemes')
                    //),
                    //'show_ui' => true,
                    //'query_var' => true,
					//'update_count_callback' => '_update_post_term_count',
                    //'rewrite' => array( 'slug' => $tag_tax_base_url, 'with_front' => false ),
            //)
    //);
    // EKLEME BITISI

    // this needs to happen once after install script first runs
    if (get_option('cp_rewrite_flush_flag') == 'true') {
        flush_rewrite_rules();
        delete_option('cp_rewrite_flush_flag');
    }


}

// activate the custom post type
add_action( 'init', 'cp_ad_listing_post_type', 0 );


// add the custom edit ads page columns
function cp_edit_ad_columns($columns){
    $columns = array(
            'cb' => "<input type=\"checkbox\" />",
            'title' => __('Title', 'appthemes'),
            'author' => __('Author', 'appthemes'),
            APP_TAX_CAT => __('Category', 'appthemes'),
            //APP_TAX_TAG => __('Tags', 'appthemes'),
            //'cp_price' => __('Price', 'appthemes'),
            //'cp_daily_count' => __('Views Today', 'appthemes'),
            'cp_total_count' => __('Views Total', 'appthemes'),
            //'cp_sys_expire_date' => __('Expires', 'appthemes'),
            'comments' => '<div class="vers"><img src="' . esc_url( admin_url( 'images/comment-grey-bubble.png' ) ) . '" /></div>',
            'date' => __('Date', 'appthemes'),
    );
    return $columns;
}
add_filter('manage_edit-'.APP_POST_TYPE.'_columns', 'cp_edit_ad_columns');

// register the columns as sortable
function cp_ad_column_sortable($columns) {
	//$columns['cp_price'] = 'cp_price'; 
	//$columns['cp_daily_count'] = 'cp_daily_count'; 
	$columns['cp_total_count'] = 'cp_total_count'; 
	$columns['cp_sys_expire_date'] = 'cp_sys_expire_date'; 
	return $columns;
}
add_filter('manage_edit-'.APP_POST_TYPE.'_sortable_columns', 'cp_ad_column_sortable');


// how the custom columns should sort
function cp_ad_column_orderby($vars) {
	
    if ( isset( $vars['orderby'] ) && 'cp_price' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'cp_price',
            'orderby' => 'meta_value_num',
        ) );
    }
    
    if ( isset( $vars['orderby'] ) && 'cp_daily_count' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'cp_daily_count',
            'orderby' => 'meta_value_num',
        ) );
    }
    
    if ( isset( $vars['orderby'] ) && 'cp_total_count' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'cp_total_count',
            'orderby' => 'meta_value_num',
        ) );
    }
    

    return $vars;
}
add_filter('request', 'cp_ad_column_orderby');


// add the custom edit ads page column values
function cp_custom_columns($column){
	global $post;
	$custom = get_post_custom();

	switch ($column) :

		case 'cp_sys_expire_date':
			if ( isset($custom['cp_sys_expire_date'][0]) && !empty($custom['cp_sys_expire_date'][0]) )
				echo $custom['cp_sys_expire_date'][0];
		break;

		//case 'cp_price':
			//if ( isset($custom['cp_price'][0]) && !empty($custom['cp_price'][0]) )
				//echo $custom['cp_price'][0];
		//break;
		
		case 'cp_daily_count':
			if ( isset($custom['cp_daily_count'][0]) && !empty($custom['cp_daily_count'][0]) )
				echo $custom['cp_daily_count'][0];
		break;
		
		case 'cp_total_count':
			if ( isset($custom['cp_total_count'][0]) && !empty($custom['cp_total_count'][0]) )
				echo $custom['cp_total_count'][0];
		break;

		case APP_TAX_TAG :
			echo get_the_term_list($post->ID, APP_TAX_TAG, '', ', ','');
		break;

		case APP_TAX_CAT :
			echo get_the_term_list($post->ID, APP_TAX_CAT, '', ', ','');
		break;

	endswitch;
}
add_action('manage_posts_custom_column',  'cp_custom_columns');


// add the custom edit ad categories page columns
function cp_edit_ad_cats_columns($columns){
    $columns = array(
            'cb' => "<input type=\"checkbox\" />",
            'name' => __('Name', 'appthemes'),
            'description' => __('Description', 'appthemes'),
            'slug' => __('Slug', 'appthemes'),
            'num' => __('Ads', 'appthemes'),
    );
    return $columns;
}

// don't enable this yet. see wp-admin function _tag_row for main code
//add_filter('manage_edit-'.APP_TAX_CAT.'_columns', 'cp_edit_ad_cats_columns');


// add a drop-down post type selector to the edit post/ads admin pages
function cp_post_type_changer() {
    global $post;

    // disallow things like attachments, revisions, etc
    $safe_filter = array('public' => true, 'show_ui' => true);

    // allow this to be filtered
    $args = apply_filters('cp_post_type_changer', $safe_filter);

    // get the post types
    $post_types = get_post_types((array)$args);

    // get the post_type values
    $cur_post_type_object = get_post_type_object($post->post_type);

    $cur_post_type = $cur_post_type_object->name;

    // make sure the logged in user has perms
    $can_publish = current_user_can($cur_post_type_object->cap->publish_posts);
	?>
	
	<?php if ( $can_publish ) : ?>
	
	<div class="misc-pub-section misc-pub-section-last post-type-switcher">
	
		<label for="pts_post_type"><?php _e('Post Type:', 'appthemes'); ?></label>
	
		<span id="post-type-display"><?php echo $cur_post_type_object->label; ?></span>
	
		<a href="#pts_post_type" class="edit-post-type hide-if-no-js"><?php _e('Edit', 'appthemes'); ?></a>
		<div id="post-type-select" class="hide-if-js">
	
			<select name="pts_post_type" id="pts_post_type">
	            <?php foreach ( $post_types as $post_type ) {
				$pt = get_post_type_object( $post_type );
	
				if ( current_user_can( $pt->cap->publish_posts ) ) : ?>
	
					<option value="<?php echo $pt->name; ?>"<?php if ( $cur_post_type == $post_type ) : ?>selected="selected"<?php endif; ?>><?php echo $pt->label; ?></option>
	
				<?php
				endif;
			}
	            ?>
			</select>
	
			<input type="hidden" name="hidden_post_type" id="hidden_post_type" value="<?php echo $cur_post_type; ?>" />
	
			<a href="#pts_post_type" class="save-post-type hide-if-no-js button"><?php _e('OK', 'appthemes'); ?></a>
			<a href="#pts_post_type" class="cancel-post-type hide-if-no-js"><?php _e('Cancel', 'appthemes'); ?></a>
		</div>	
		
	</div>
	
	<div class="misc-pub-section misc-pub-section-last post-type-switcher">
		<span id="sticky"><input id="sticky" name="sticky" type="checkbox" value="sticky" <?php checked(is_sticky($post->ID)); ?> tabindex="4" /> <label for="sticky" class="selectit"><?php _e('Featured Ad (sticky)', 'appthemes') ?></label><br /></span>
	</div>

<?php
	endif;
}
// add this option to the edit post submit box
add_action('post_submitbox_misc_actions', 'cp_post_type_changer');


// hack until WP supports custom post type sticky feature
function cp_sticky_option() {
	global $post;
	
	//if post is a custom post type and only during the first execution of the action quick_edit_custom_box
	if ($post->post_type == APP_POST_TYPE && did_action('quick_edit_custom_box') === 1): ?>
	
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-col">
			<label class="alignleft">
				<input type="checkbox" name="sticky" value="sticky" />
				<span class="checkbox-title"><?php _e('Featured Ad (sticky)', 'appthemes'); ?></span>
			</label>
		</div>	
	</fieldset>
<?php
	endif;
}
//add the sticky option to the quick edit area
add_action('quick_edit_custom_box', 'cp_sticky_option');


// jquery and css for the post type changer
function cp_post_type_changer_head() {
?>

<script type='text/javascript'>
    jQuery(document).ready(function(){
        jQuery('#post-type-select').siblings('a.edit-post-type').click(function() {
            if (jQuery('#post-type-select').is(":hidden")) {
                jQuery('#post-type-select').slideDown("normal");
                jQuery(this).hide();
            }
            return false;
        });

        jQuery('.save-post-type', '#post-type-select').click(function() {
            jQuery('#post-type-select').slideUp("normal");
            jQuery('#post-type-select').siblings('a.edit-post-type').show();
            pts_updateText();
            return false;
        });

        jQuery('.cancel-post-type', '#post-type-select').click(function() {
            jQuery('#post-type-select').slideUp("normal");
            jQuery('#pts_post_type').val(jQuery('#hidden_post_type').val());
            jQuery('#post-type-select').siblings('a.edit-post-type').show();
            pts_updateText();
            return false;
        });

        function pts_updateText() {
            jQuery('#post-type-display').html( jQuery('#pts_post_type :selected').text() );
            jQuery('#hidden_post_type').val(jQuery('#pts_post_type').val());
            jQuery('#post_type').val(jQuery('#pts_post_type').val());
            return true;
        }
    });
</script>

<style type="text/css">
    #post-type-select { line-height: 2.5em; margin-top: 3px; }
    #post-type-display { font-weight: bold; }
    div.post-type-switcher { border-top: 1px solid #eee; }
</style>

<?php
}
add_action('admin_head', 'cp_post_type_changer_head');


// custom user page columns
function cp_manage_users_columns( $columns ) {
	$newcol = array_slice( $columns, 0, 1 );
	$newcol = array_merge( $newcol, array( 'id' => __('Id', 'appthemes') ) );
	$columns = array_merge( $newcol, array_slice( $columns, 1 ) );

    $columns['cp_ads_count'] = __('Ads', 'appthemes');
	$columns['last_login'] = __('Son Giris', 'appthemes');
	$columns['registered'] = __('Kayit Tarihi', 'appthemes');
    return $columns;
}
add_action('manage_users_columns', 'cp_manage_users_columns');


// register the columns as sortable
function cp_users_column_sortable( $columns ) {
	$columns['id'] = 'id';
	return $columns;
}
add_filter('manage_users_sortable_columns', 'cp_users_column_sortable');


// display the coumn values for each user
function cp_manage_users_custom_column( $r, $column_name, $user_id ) {
	switch ( $column_name ) {
		case 'cp_ads_count' :
			global $cp_counts;

			if ( !isset( $cp_counts ) )
				$cp_counts = cp_count_ads();

			if ( !array_key_exists( $user_id, $cp_counts ) )
				$cp_counts = cp_count_ads();

			if ( $cp_counts[$user_id] > 0 ) {
				$r .= "<a href='edit.php?post_type=" . APP_POST_TYPE . "&author=$user_id' title='" . esc_attr__( 'View ads by this author', 'appthemes' ) . "' class='edit'>";
				$r .= $cp_counts[$user_id];
				$r .= '</a>';
			} else {
				$r .= 0;
			}
		break;
	
		case 'last_login' :
			$r = get_user_meta($user_id, 'last_login', true);
		break;

		case 'registered' :
			$user_info = get_userdata($user_id);
			$r = $user_info->user_registered;
			//$r = appthemes_get_reg_date($reg_date);
		break;

		case 'id' :
			$r = $user_id;
	}

	return $r;
}
//Display the ad counts for each user
add_action( 'manage_users_custom_column', 'cp_manage_users_custom_column', 10, 3 );


// count the number of ad listings for the user
function cp_count_ads() {
	global $wpdb, $wp_list_table;

	$users = array_keys( $wp_list_table->items );
	$userlist = implode( ',', $users );
	$result = $wpdb->get_results( "SELECT post_author, COUNT(*) FROM $wpdb->posts WHERE post_type = '" . APP_POST_TYPE . "' AND post_author IN ($userlist) GROUP BY post_author", ARRAY_N );
	foreach ( $result as $row ) {
		$count[ $row[0] ] = $row[1];
	}

	foreach ( $users as $id ) {
		if ( ! isset( $count[ $id ] ) )
			$count[ $id ] = 0;
	}

	return $count;
}

?>