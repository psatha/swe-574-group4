<?php

/**
 * These are Administration Panel additions
 * within the WordPress admin pages
 * http://codex.wordpress.org/Administration_Panels
 * 
 * 
 */

// @ since v3.1 
// column sorting ajax 
add_action('admin_head', 'cp_ajax_sortable_js');

function cp_ajax_sortable_js() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {

	// Return a helper with preserved width of cells
	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			jQuery(this).width(jQuery(this).width());
			//ui.placeholder.html('<!--[if IE]><td>&nbsp;</td><![endif]-->');
		});
		return ui;
	};

	jQuery("tbody.sortable").sortable({
		helper: fixHelper,
		opacity: 0.7,
		cursor: 'move',
		// connectWith: 'table.widefat tbody',
		placeholder: 'ui-placeholder',
		forcePlaceholderSize: true, 
		items: 'tr',
		update: function() {
			var results = jQuery("tbody.sortable").sortable("toArray"); // pass in the array of row ids based off each tr css id
			
			var data = { // pass in the action
			action: 'cp_ajax_update',
			rowarray: results
			};

			jQuery("span#loading").html('<img src="<?php echo bloginfo('template_directory') ?>/images/ajax-loading.gif" />');
			jQuery.post(ajaxurl, data, function(theResponse){
				jQuery("span#loading").html(theResponse);
			}); 															 
		}	
	}).disableSelection();

 
});

</script>
<?php
}

// db update function for the column sort ajax feature
add_action('wp_ajax_cp_ajax_update', 'cp_ajax_sort_callback');

function cp_ajax_sort_callback() {
	global $wpdb;
	
	$counter = 1;
	foreach ($_POST['rowarray'] as $value) {		
		$wpdb->update($wpdb->cp_ad_meta,
			array(
				"field_pos" => $counter
			),
			array(
				"meta_id" => $value
			)
		);
		$counter = $counter + 1;	
	}	
	die();
}





// adds the thumbnail column to the WP Posts Edit SubPanel
if (!function_exists('cp_thumbnail_column') && function_exists('add_theme_support')) {

function cp_thumbnail_column($cols) {
    $cols['thumbnail'] = __('Image', 'appthemes');
    return $cols;
}

function cp_thumbnail_value($column_name, $post_id) {

    $width = (int) 50;
    $height = (int) 50;

    if ('thumbnail' == $column_name) {
        // thumbnail of WP 2.9
        $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
        // image from gallery
        $attachments = get_children(array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image'));
        if ($thumbnail_id)
            $thumb = wp_get_attachment_image($thumbnail_id, array($width, $height), true);
        elseif ($attachments) {
            foreach ($attachments as $attachment_id => $attachment) {
                $thumb = wp_get_attachment_image($attachment_id, array($width, $height), true);
            }
        }
        if (isset($thumb) && $thumb) {
            echo $thumb;
        } else {
            // echo __('None', 'appthemes');
            // @todo Fail gracefully
        }
    }
}

    // for posts
    add_filter('manage_posts_columns', 'cp_thumbnail_column');
    add_action('manage_posts_custom_column', 'cp_thumbnail_value', 10, 2);

    // for ads
    add_filter('manage_edit-'.APP_POST_TYPE.'_columns', 'cp_thumbnail_column');
    add_action('manage_posts_custom_column', 'cp_thumbnail_value', 10, 2);


    // for pages
    // add_filter('manage_pages_columns', 'cp_thumbnail_column');
    // add_action('manage_pages_custom_column', 'cp_thumbnail_value', 10, 2);
}




?>