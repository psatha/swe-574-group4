<?php
/*
  Image Sizes Manager
  (c) 2011. Web factory Ltd
  www.webfactoryltd.com
*/

if (!function_exists('add_action')) {
  die('Please don\'t open this file directly!');
}


class eis_ajax extends wf_eis {
  // create/edit image size
  function save_image_size() {
    if (!$_POST || !isset($_POST['size_label']) || !isset($_POST['do'])) {
      die('<li>Bad request. Please reload the page.</li>');
    }

    $err = '';
    $do = $_POST['do'];
    $options = wf_eis::fetch_option();
    $all_sizes = wf_eis::get_image_sizes();
    $new_option = '';

    $new_size['size_label'] = strtolower(trim($_POST['size_label']));
    $new_size['width'] = (int) $_POST['width'];
    $new_size['height'] = (int) $_POST['height'];
    $new_size['crop'] = (int) $_POST['crop'];

    if ($do == 'add' && isset($all_sizes[$new_size['size_label']])) {
      $err .= '<li>That image size label already exists. Please use a different one.</li>';
    }
    if (!$new_size['size_label'] || strlen($new_size['size_label']) > 20 || strlen($new_size['size_label']) < 2) {
      $err .= '<li>Image size label has to be a string with length between 2 and 20 characters.</li>';
    }
    if ($new_size['width'] < 1 || $new_size['width'] > 10000 ) {
      $err .= '<li>Image width must be an integer between 1 and 10000 pixels.</li>';
    }
    if ($new_size['height'] < 1 || $new_size['height'] > 10000 ) {
      $err .= '<li>Image height must be an integer between 1 and 10000 pixels.</li>';
    }

    if ($err) {
      die($err);
    }

    if ($do == 'add') {
      // add new image size
      update_option('wf_ism', wf_eis::sanitize_settings($new_size));
      die('1');
    } else { // edit image size
      unset($options[$new_size['size_label']]);
      update_option('wf_ism', wf_eis::sanitize_settings($new_size));
      die('1');
    }
  } // save_image_size

  // Delete image size
  function delete() {
    if (!$_POST || !isset($_POST['label'])) {
      die('0');
    }

    $options = self::fetch_option();
    $label = $_POST['label'];

    unset($options[$label]);
    update_option('wf_ism', $options);

    die('1');
  }

  // Edit image size
  function edit() {
    if (!$_POST || !isset($_POST['label'])) {
      die('Bad request.');
    }

    $options = self::fetch_option();
    $label = $_POST['label'];

    $output = $options[$label];
    echo json_encode($output);
    die();
  }

  // Ajax for regenerating thumbnails
  // Process all or just single image ID (this is an AJAX handler)
  function ajax_regenerate_image() {
    ob_start();

    // Get the thumbnails
    if (isset($_POST['thumbnails'])) {
      $thumbnails = $_POST['thumbnails'];
    } else {
      $thumbnails = '';
    }

    $image_id = $_POST['id'];
    $attachment = get_post($image_id);
    if (!$attachment) {
      die('0');
    }

    // Regenerate all image sizes
    if (!$thumbnails) {
      // regenerate all image sizes
      $sizes = self::get_image_sizes();
      foreach($sizes as $label => $data) {
        $thumbnails[] = $label;
      }
    }

    // Get the path
    $fullsizepath = get_attached_file($attachment->ID);

    // Regen the attachment
    if (FALSE !== $fullsizepath && file_exists($fullsizepath)) {
      set_time_limit(WF_TIME_LIMIT_PER_IMAGE);
      $tmp = wp_update_attachment_metadata($attachment->ID, self::wp_generate_attachment_metadata_custom($attachment->ID, $fullsizepath, $thumbnails));
      die('1');
    } else {
      die('0');
    }

    die('1');
  } // ajax_regenerate_image


  // Custom function for generating attachment meta data and updating it!
  function wp_generate_attachment_metadata_custom($attachment_id, $file, $thumbnails = array()) {
    // Fetch attachment
    $attachment = get_post($attachment_id);
    $metadata = array();

    if (preg_match('!^image/!', get_post_mime_type($attachment)) && file_is_displayable_image($file)) {
      // Fetch image sizes
      $imagesize = getimagesize($file);
      // Fetch image width and height
      $metadata['width'] = $imagesize[0];
      $metadata['height'] = $imagesize[1];
      list($uwidth, $uheight) = wp_constrain_dimensions($metadata['width'], $metadata['height'], 128, 128);
      // Setup image metadata
      $metadata['hwstring_small'] = "height='$uheight' width='$uwidth'";

      // Make the file path relative to the upload dir
      $metadata['file'] = _wp_relative_upload_path($file);

      $sizes = self::get_image_sizes();
      $sizes = apply_filters('intermediate_image_sizes_advanced', $sizes);

      foreach ($sizes as $size => $size_data) {
        if(isset($thumbnails) && is_array($thumbnails) && !in_array($size, $thumbnails)) {
          continue;
        }
        $resized = image_make_intermediate_size($file, $size_data['width'], $size_data['height'], $size_data['crop']);
        if ($resized) {
          $metadata['sizes'][$size] = $resized;
        }
      } // foreach

      // fetch additional metadata
      $image_meta = wp_read_image_metadata( $file );
      if ($image_meta) {
        $metadata['image_meta'] = $image_meta;
      }
    }

    return apply_filters('wp_generate_attachment_metadata', $metadata, $attachment_id);
  } // wp_generate_attachment_metadata_custom


  // Function for retrieving list of all images
  function get_list() {
    $out = array();
    $args['post_type'] = 'attachment';
    $args['post_mime_type'] = 'image/*';
    $args['numberposts'] = -1;
    $attachments = get_posts($args);

    foreach ($attachments as $tmp) {
      $out[] = (object) array('ID' => $tmp->ID);
    }

    echo json_encode($out);
    die();
  } // getList
} // eis_ajax
?>