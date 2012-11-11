<?php
/**
 * Theme functions file
 *
 * DO NOT MODIFY THIS FILE. Make a child theme instead: http://codex.wordpress.org/Child_Themes
 *
 * @package ClassiPress
 * @author AppThemes
 */

// Framework
//require( dirname(__FILE__) . '/framework/load.php' );

// Theme-specific files
//require( dirname(__FILE__) . '/includes/theme-functions.php' );




// rubencio@me.com get the image associated to the ad used on the facebook thumbnail for sharing
if (!function_exists('rb_get_image_url_feat')) {
    function rb_get_image_url_feat($post_id = '', $size = 'medium', $class = '', $num = 1) {
        $images = get_posts(array('post_type' => 'attachment', 'numberposts' => $num, 'post_status' => null, 'post_parent' => $post_id, 'order' => 'ASC', 'orderby' => 'ID'));
        if ($images) {
            foreach ($images as $image) {
				$alt = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
                $iarray = wp_get_attachment_image_src($image->ID, $size, $icon = false);
                $img_check = ''.$iarray[0].'';
            }
        } else {
            if(get_option('cp_ad_images') == 'yes') { $img_check = ''. get_stylesheet_directory_uri() .'/images/no_pic_150x150.png'; }
            //cp_single_image_legacy($post_id, get_option('thumbnail_size_w'), get_option('thumbnail_size_h'), 'captify', true);
        }
        echo $img_check;
    }
}

add_filter('appthemes_extended_profile_fields', 'sm_add_profile_fields');
function sm_add_profile_fields($fields){
$fields['home_phone_number'] = array(
'title'=> __('Ev Telefonu:','appthemes'),
'type' => 'text',
'description' => __('Ev telefonunuzu girerek diğer kullanıcıların size rahatça ulaşmasını sağlayın.','appthemes')
);
$fields['work_phone_number'] = array(
'title'=> __('İş Telefonu:','appthemes'),
'type' => 'text',
'description' => __('İş telefonunuzu girerek diğer kullanıcıların size rahatça ulaşmasını sağlayın.','appthemes')
);
$fields['cell_phone_number'] = array(
'title'=> __('Cep Telefonu:','appthemes'),
'type' => 'text',
'description' => __('Cep telefonunuzu girerek diğer kullanıcıların size rahatça ulaşmasını sağlayın.','appthemes')
);
return $fields;
}