<?php
/*
Plugin Name: Image Sizes Manager
Plugin URI: http://image-sizes-manager.webfactoryltd.com/
Description: Easily add, edit, remove and regenerate custom image sizes - built-in ones, theme based and custom created. Includes [thumbnail] shortcode.
Author: Web factory Ltd
Version: 1.0
Author URI: http://www.webfactoryltd.com/
*/

if (!function_exists('add_action')) {
  die('Please don\'t open this file directly!');
}

// maximum time in seconds for regenerating each image
define('WF_TIME_LIMIT_PER_IMAGE', 35);
// shortcode name
define('WF_IMG_SHORTCODE', 'thumbnail');
// plugin folder URL
define('WF_EIS', plugin_dir_url(__FILE__));


require_once 'ism-ajax.php';


class wf_eis {
  // hook filters and actions
  function init() {
    if (is_admin()) {
      if (strpos($_SERVER['REQUEST_URI'], 'upload.php') !== false
            && isset($_GET['page'])
            && $_GET['page'] == 'wf-ism') {
        if (version_compare('3.1', get_bloginfo('version')) != 1) {
          // WP 3.1 or newer
          wp_enqueue_script('jquery-ui-dialog');
          wp_enqueue_style('wp-jquery-ui-dialog');
        } else {
          // WP older than 3.1
          wp_enqueue_script('wf_eis_ui', plugin_dir_url(__FILE__) . 'js/jquery-ui-1.8.13.custom.min.js', array(), '1');
          wp_enqueue_style('wf_eis_ui_style', plugin_dir_url(__FILE__) . 'css/ui-lightness/jquery-ui-1.8.13.custom.css', array(), '1');
        }
        wp_enqueue_script('wf_progress', WF_EIS . 'js/jquery.progressbar.min.js', array(), '1.1');
        wp_enqueue_style('wf_eis_common_style', WF_EIS . 'css/wf-ism.css', array(), '1.0');
      } // is our admin page

      if (strpos($_SERVER['REQUEST_URI'], 'upload.php') !== false) {
        wp_enqueue_script('regenerate', plugin_dir_url(__FILE__) . 'js/wf-ism.js');
      }

      // modify media upload lightbox
      add_filter('attachment_fields_to_edit', array('wf_eis', 'additional_image_sizes'), 11, 2);

      // aditional link in plugin description
      add_action('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__), array('wf_eis', 'plugin_action_links'));

      // register menu item
      add_action('admin_menu', array('wf_eis', 'admin_menu'));

      // add regenerate link for single images
      add_filter('media_row_actions', array('wf_eis', 'add_media_row_action' ), 10, 2);

      // AJAX endpoints
      add_action('wp_ajax_wf_thumbnail_regenerate', array('eis_ajax', 'ajax_regenerate_image'));
      add_action('wp_ajax_wf_save_image_size',      array('eis_ajax', 'save_image_size'));
      add_action('wp_ajax_wf_delete',               array('eis_ajax', 'delete'));
      add_action('wp_ajax_wf_edit',                 array('eis_ajax', 'edit'));
      add_action('wp_ajax_wf_get_list',             array('eis_ajax', 'get_list'));
    } // is_admin

    // register our custom sizes so that others see them to
    self::register_new_sizes();

    // add shortcode
    global $shortcode_tags;
    if (isset($shortcode_tags[THUMBNAIL_SHORTCODE])) {
      add_action('admin_footer', array('wf_eis', 'warning'));
    } else {
      add_shortcode(WF_IMG_SHORTCODE, array('wf_eis', 'shortcode'));
    }

    // add shortcode support in sidebar text widget
    if (has_filter('widget_text', 'do_shortcode') === false) {
      add_filter('widget_text', 'do_shortcode');
    }

    // add theme thumbnail support
    if (!current_theme_supports('post-thumbnails')) {
      add_theme_support('post-thumbnails');
    }
  } // function init


  // display warning if shortcode is already in use
  public function warning() {
    echo '<div id="message" class="error"><p>The shortcode [' . WF_IMG_SHORTCODE . '] is already in use by another plugin. Please refer to <a href="http://image-sizes-manager.webfactoryltd.com/">Image Sizes Manager documentation</a> to resolve this issue.</p></div>';

    return;
  } // warning


  // simple shortcode for thumbnail
  public function shortcode($atts, $content = null) {
    global $post;
    $sizes = self::get_image_sizes();
    $out = '';

    // parse attributes and set some default values
    $atts = shortcode_atts(array('size'    => 'humbnail',
                                 'post_id' => $post->ID),
                           $atts);

    if (!isset($sizes[$atts['size']])) {
      $atts['thumbnail'];
    }

    $out = get_the_post_thumbnail($atts['post_id'], $atts['size']);
    return $out;
  } // shortcode


  // add "regenerate images" link to media row actions
  function add_media_row_action($actions, $post) {
    if ('image/' != substr($post->post_mime_type, 0, 6)) {
      return $actions;
    }

    $actions['regenerate_thumbnails'] = '<a href="#" class="regenerate-single" id="' . $post->ID . '" title="Regenerate all image sizes for this image">Regenerate All Image Sizes</a>';

    return $actions;
  } // add_media_row_action


  // register custom image sizes
  function register_new_sizes() {
    $sizes = self::fetch_option();

    if (empty($sizes) || !is_array($sizes)) {
      return false;
    }

    foreach ($sizes as $name => $size ) {
      if (isset($size['crop']) && $size['crop'] == '1') {
        $crop = true;
      } else {
        $crop = false;
      }
      // add custom images sizes
      add_image_size($size['size_label'], $size['width'], $size['height'], $crop);
    }

    return true;
  } // register_new_sizes


  // sanitize settings on save
  function sanitize_settings($values) {
    $options = self::fetch_option();

    if (is_array($values)) {
      foreach ($values as $key => $value) {
        switch ($key) {
          case 'width':
          case 'height':
          case 'crop':
             $new_size[$key] = (int) trim($value);
          break;
          case 'size_label':
            $new_size[$key] = strtolower(sanitize_title($value, 'size-label-' . rand(0, 100)));
          break;
        }
      } // foreach

      $options[$new_size['size_label']] = $new_size;
    }

    return $options;
  } // sanitize_settings


  // add plugin to admin menu
  function admin_menu() {
     add_media_page('Image Sizes Manager', 'Image Sizes Manager', 'manage_options', 'wf-ism', array('wf_eis', 'options_page'));
  } // admin_menu


   // add settings link to plugins page
  function plugin_action_links($links) {
    $settings_link = '<a href="upload.php?page=wf-ism" title="Image Sizes Manager">Settings</a>';
    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


  // retrives built-in image sizes
  function get_built_in_sizes() {
    $size_options = array();
    $output = array();

    $built_in['thumbnail'] = array('size_label' => 'Thumbnail', 'type' => 'built-in');
    $built_in['medium']    = array('size_label' => 'Medium', 'type' => 'built-in');
    $built_in['large']     = array('size_label' => 'Large', 'type' => 'built-in');

    foreach ($built_in as $label => $atts) {
      $size_options['width'] = get_option($label . '_size_w');
      $size_options['height'] = get_option($label . '_size_h');
      $size_options['crop'] = get_option($label . '_crop');
      $output[$label] = array_merge($built_in[$label], $size_options);
      unset($size_options);
    }

    return $output;
  } // get_built_in_sizes


  // retrives array of built-in, other plugin and our image sizes
  // types: eis, foreign, built-in
  function get_image_sizes($fetch_type = '') {
    global $_wp_additional_image_sizes;
    $output = array();
    $sizes = self::fetch_option();
    if(!is_array($_wp_additional_image_sizes)) {
      $_wp_additional_image_sizes = array();
    }

    // see which image sizes are ours
    if ($_wp_additional_image_sizes) {
      foreach ($_wp_additional_image_sizes as $label => $values) {
        if (is_array($sizes) && array_key_exists($label, $sizes)) {
          $type = array('type' => 'eis');
          $_wp_additional_image_sizes[$label] = array_merge($_wp_additional_image_sizes[$label],$type);
        }  else {
          $type = array('type' => 'foreign');
          $_wp_additional_image_sizes[$label] = array_merge($_wp_additional_image_sizes[$label],$type);
        }
      }
    }

    $built_in = self::get_built_in_sizes();
    $_wp_additional_image_sizes = array_merge($_wp_additional_image_sizes, $built_in);

    if ($fetch_type != '') {
      foreach ($_wp_additional_image_sizes as $label => $data) {
        if ($data['type'] == $fetch_type) {
          $output[$label] = $data;
        }
      } // foreach
    } else {
      $output = $_wp_additional_image_sizes;
    }

    return $output;
  } // get_image_sizes


  // create listings table
  function listing_table($input_array, $enabled_actions = array('edit', 'delete'), $table_id = '', $empty = '') {
    asort($input_array);

    if ($input_array) {
      echo '<table class="wp-list-table widefat wf-ism" cellspacing="0" id="' . $table_id . '"><thead>';

      echo '<tr>
            <th>Name</th>
            <th class="width">Width (px)</th>
            <th class="height">Height (px)</th>
            <th class="crop">Cropped</th>
            <th class="actions">Actions</th>
            <th class="regenerate">Regenerate</th>
            </tr>';
      echo '</thead><tbody>';

      foreach ($input_array as $label => $size) {
        if ($class == '') {
          $class = 'alternate';
        } else {
          $class = '';
        }

        if (!isset($size['size_label']) || $size['size_label'] == '') {
          $size['size_label'] = $label;
        }

        if ($table_id == 'eis-table') {
          echo '<tr class="' . $class . '" id="size-' . $label .'">';
        } else {
          echo '<tr class="' . $class . '">';
        }

        echo '<td class="id-column">' . $size['size_label'] . '</td>
        <td class="width-column">' . $size['width'] . '</td>
        <td class="height-column">' . $size['height'] . '</td>
        <td class="crop-column">' . self::is_crop($size['crop']) . '</td>
        <td class="actions">';

         if (!is_array($enabled_actions)) {
           if ($table_id == 'built-in-table') {
             $url = admin_url('options-media.php');
             echo '<a href="' . $url . '" class="edit-media"><img src="' . plugin_dir_url(__FILE__) . 'images/edit.png" title="Edit built-in image size" alt="Edit built-in image size" /></a>';
           } else {
             echo $enabled_actions;
           }
         } else {
           // is edit action enabled?
           if (isset($enabled_actions['edit']) && $enabled_actions['edit'] == 'true') {
             echo '<a href="#" class="edit" id="' . $label . '"><img src="' . plugin_dir_url(__FILE__) . 'images/edit.png" title="Edit image size" alt="Edit image size" /></a>';
           }

           // is delete action enabled?
           if (isset($enabled_actions['delete']) && $enabled_actions['delete'] == 'true') {
             echo '<a href="#" class="delete" label="' . $label . '"><img src="' . plugin_dir_url(__FILE__) . 'images/delete.png" title="Delete image size" alt="Delete image size" /></a>';
           }
         } // !is_array($enabled_actions)

        echo '</td><td align="center" class="regenerate">
         <input type="checkbox" name="thumbnails[]" class="thumbnails" value="' . $label . '" />
        </td>
        </tr>';
      } // foreach $input_array

      echo '</tbody></table><br/>';
    } else {
      echo '<p>' . $empty . '<br /><br /></p>';
    } // if records
  } // function listings table


  // options page
  function options_page() {
    global $_wp_additional_image_sizes;

    // does the user have enough privilages to access this page?
    if (!current_user_can('manage_options'))  {
      wp_die('You do not have sufficient permissions to access this page.');
    }

    $options = self::fetch_option();

    echo '<div class="wrap">
          <div class="icon32" id="icon-upload"><br /></div>
          <h2>Image Sizes Manager <a title="Add new image size" class="button add-new-h2" id="wf_add_new_size" name="wf_add_new_size">Add new image size</a></h2>';

    echo '<div class="ism_submit"><input type="button" value="Regenerate selected image sizes" class="button" id="wf_regenerate" name="regenerate" /><span id="wf_progress"></span></div>';

    // list your custom image sizes
    echo '<h3>Your custom image sizes</h3>';
    echo '<span class="desc">These image sizes were created by Image Sizes Manager. You can add, edit and delete them.</span>';
    self::listing_table(self::fetch_option(), array('edit' => 'true', 'delete' => 'true'), 'eis-table', 'There are currently no custom image sizes defined. Click "Add new image size" to add some.');

    // list 3rd party image sizes
    echo '<h3>Custom image sizes from other plugins and themes</h3>';
    echo '<span class="desc">These image sizes were created by 3rd party plugins and themes.</span>';
    self::listing_table(self::get_image_sizes('foreign'), '', 'foreign-table', 'There are currently no 3rd party custom image sizes defined.');

    // list built in image sizes
    echo '<h3>Built-in WordPress image sizes</h3>';
    echo '<span class="desc">WordPress has three built-in image sizes which can be modified in Settings - Media admin panel.</span>';
    self::listing_table(self::get_image_sizes('built-in'), 'Built in image sizes can\'t be edited or deleted.', 'built-in-table');


    echo '<p>Notes:</p>';
    echo '<ul id="ism-notes">
            <li>After editing or adding a custom image size <b>you have to regenerate images</b> for that size. It will not happen automatically.</li>
            <li>Regenerating images can take a <b>lot of time and server CPU/memory</b>. Please be patient!</li>
            <li>To access a particular image size in your theme use: <code>the_post_thumbnail(\'size_name\')</code> or <code>get_the_post_thumbnail($post->ID, \'size_name\')</code>.</li>
            <li>Built-in image sizes can\'t be deleted.</li>
            <li>3rd party image sizes can only be modified by plugins which created them or directly in PHP code where they were defined.</li>
            <li>If you can\'t find where a 3rd party image size is defined try looking in your theme\'s <code>functions.php</code> file.</li>
            <li>use <code>[thumbnail size="your-image-size" /]</code> shortcode for inserting custom size thumbnails in posts &amp; pages</li>
          </ul>';

    echo '</div>'; // wrap
    echo "<script type=\"text/javascript\"> var wf_ism_url = '" . WF_EIS . "';</script>";
    self::dialog();
  } // options_page


  // adds custom image sizes to "insert image" dialog
  function additional_image_sizes($fields, $post) {
    if (substr($post->post_mime_type, 0, 5) != 'image') {
      return $fields;
    }
    $sizes = self::fetch_option();

    if (!count($sizes)) {
      return $fields;
    }

    $items = array();
    foreach ($sizes as $size) {
      $downsize = image_downsize($post->ID, $size["size_label"]);

      $enabled = true;
      $css_id = "image-size-{$size["size_label"]}-{$post->ID}";
      $label = $size["size_label"];

      $html  = "<div class='image-size-item'>\n";
      $html .= "\t<input type='radio' " . disabled( $enabled, false, false ) . "name='attachments[{$post->ID}][image-size]' id='{$css_id}' value='{$size['size_label']}' />\n";
      $html .= "\t<label for='{$css_id}'>{$label}</label>\n";

      if ($enabled) {
        $html .= "\t<label for='{$css_id}' class='help'>" . sprintf( "(%d&nbsp;&times;&nbsp;%d)", $size["width"], $size["height"] ). "</label>\n";
      }

      $html .= "</div>";
      $items[] = $html;
    }

    $items = join( "\n", $items );
    $fields['image-size']['html'] = "{$fields['image-size']['html']}\n{$items}";

    return $fields;
  } // additional_image_sizes


  // modal dialog
  function dialog() {
    echo '<div class="wf_dialog" id="wf_dialog" style="display: none;">';
    echo '<ul id="ism-err" style="display: none;"></ul>';
    echo '<form action="" method="post">';
    settings_fields('wf_eis');

    echo '<input type="hidden" id="form_action" name="form_action" value="" />';

    echo '<label for="size_label">Image size name:</label>
    <input type="text" id="size_label" name="wf_eis[size_label]" value="" />';
    echo '<span class="ism-note">Unique size name. Please keep it simple, without any special characters.</span>';

    echo '<label for="width">Image width and height:</label>
    <input type="text" id="width" style="width: 70px;" class="small-text" name="wf_eis[width]" value="" />&nbsp;X&nbsp;<input type="text" id="height" style="width: 70px;" class="small-text" name="wf_eis[height]" value="" />';
    echo '<span class="ism-note">Maximum width and height of the image, in pixels.</span>';

    echo '<label for="crop">Crop images:</label>';
    echo '<select name="wf_eis[crop]" id="crop"><option value="0">no&nbsp;</option><option value="1">yes&nbsp;</option></select>';
    echo '<span class="ism-note">Cropped images are forced into the width/height ratio (box) defined by the image size. Use this option for images that are used on places like thumbnails.</span>';

    echo '</form>';
    echo '</div>';
  } // dialog


  // crop img helper
  function is_crop($crop) {
    if (isset($crop) && $crop == '1') {
      return '<img src="' . plugin_dir_url(__FILE__) . 'images/yes.png" title="Yes" alt="Yes" />';
    } else {
      return '<img src="' . plugin_dir_url(__FILE__) . 'images/no.png" title="No" alt="No" />';
    }
  } // is_crop

  // Function for retriveing options
  function fetch_option($option_name = 'wf_ism') {
    $tmp = get_option($option_name);
    if(!$tmp) {
      $tmp = array();
    }

    return $tmp;
  } // function fetch_options
} // wf_eis

/*
add_action( 'admin_action_bulk_nazivakcije'
// Handles the bulk actions POST
function bulk_action_handler() {
check_admin_referer( 'bulk-media' );

if ( empty( $_REQUEST['media'] ) || ! is_array( $_REQUEST['media'] ) )
return;

$ids = implode( ',', array_map( 'intval', $_REQUEST['media'] ) );

// Can't use wp_nonce_url() as it escapes HTML entities
wp_redirect( add_query_arg( '_wpnonce', wp_create_nonce( 'regenerate-thumbnails' ), admin_url( 'tools.php?page=regenerate-thumbnails&goback=1&ids=' . $ids ) ) );
exit();
}
*/

// hook everything up
add_action('init', array('wf_eis', 'init'));
?>