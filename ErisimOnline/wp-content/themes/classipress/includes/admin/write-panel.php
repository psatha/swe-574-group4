<?php

$key = 'ad_meta';

// add/remove meta boxes on the coupon edit admin page
function clpr_setup_meta_box() {
	global $key;

	add_meta_box('ad-meta-box', __('Ad Meta Fields', 'appthemes'), 'cp_custom_fields_meta_box', APP_POST_TYPE, 'normal', 'high');
	add_meta_box('images-meta-box', __('Ad Images', 'appthemes'), 'cp_custom_images_meta_box', APP_POST_TYPE, 'normal', 'high');
		
	// remove the stores metabox since we're using a drop-down instead
	//remove_meta_box( 'postcustom' ,APP_POST_TYPE, 'normal' ); 
	remove_meta_box( 'postexcerpt' ,APP_POST_TYPE , 'normal' ); 
	remove_meta_box( 'authordiv' ,APP_POST_TYPE , 'normal' ); 
		
}
add_action( 'admin_menu', 'clpr_setup_meta_box' );


// show the ad meta fields in a custom meta box
function cp_custom_fields_meta_box() {
	global $wpdb, $post, $meta_boxes, $key;
	
	// use nonce for verification
	wp_nonce_field( basename( __FILE__ ), $key . '_wpnonce', false, true );
	
	// get the ad category id
	$ad_cat_id = appthemes_get_custom_taxonomy( $post->ID, APP_TAX_CAT, 'term_id' );

	// get the form id
	$fid = cp_get_form_id($ad_cat_id); 
	
	
	// if there's no form id it must mean the default form is being used so let's go grab those fields
	if ( !($fid) ) {

		// use this if there's no custom form being used and give us the default form
		$sql = $wpdb->prepare( "SELECT field_label, field_name, field_type, field_values, field_tooltip, field_req "
			 . "FROM $wpdb->cp_ad_fields "
			 . "WHERE field_core = '1' "
			 . "ORDER BY field_id asc" );

	} else {

		// now we should have the formid so show the form layout based on the category selected
		$sql = $wpdb->prepare( "SELECT f.field_label, f.field_name, f.field_type, f.field_values, f.field_perm, f.field_tooltip, m.meta_id, m.field_pos, m.field_req, m.form_id "
			 . "FROM $wpdb->cp_ad_fields f "
			 . "INNER JOIN $wpdb->cp_ad_meta m "
			 . "ON f.field_id = m.field_id "
			 . "WHERE m.form_id = %s "
			 . "ORDER BY m.field_pos asc",
        $fid);

	}

	$results = $wpdb->get_results( $sql );

	// display the write panel for the custom fields
	if ($results) : 
	?>	

	<script type="text/javascript">
		//<![CDATA[	
		/* initialize the datepicker feature */
		jQuery(document).ready(function($) {		   
			$('table input#datepicker').datetimepicker({
				showSecond: true,
				timeFormat: 'hh:mm:ss',
				showOn: 'button',
				dateFormat: 'mm/dd/yy',				
				minDate: 0,
				buttonImageOnly: true,
				buttonText: '',
				buttonImage: '../wp-includes/images/blank.gif' // calling the real calendar image in the admin-style.css. need a blank placeholder image b/c of IE.
			});
		});	
		//]]>
	</script>	

		<table class="form-table ad-meta-table">
		
			<tr>
				<th style="width:20%"><label for="cp_sys_ad_conf_id"><?php _e('Ad Info', 'appthemes'); ?>:</label></th>
				<td class="ad-conf-id">
					<div id="ad-id"><div id="keyico"></div><?php _e('Ad ID', 'appthemes'); ?>: <span><?php echo esc_html( get_post_meta($post->ID, 'cp_sys_ad_conf_id', true) ); ?></span></div>
					<div id="ad-stats"><div id="statsico"></div><?php _e('Views Today', 'appthemes'); ?>: <strong><?php echo esc_html( get_post_meta($post->ID, 'cp_daily_count', true) ); ?></strong> | <?php _e('Views Total:', 'appthemes'); ?> <strong><?php echo esc_html( get_post_meta($post->ID, 'cp_total_count', true) ); ?></strong></div>
				</td>
			</tr>

			<tr>
				<th style="width:20%"><label for="cp_sys_ad_conf_id"><?php _e('Submitted By', 'appthemes'); ?>:</label></th>
				<td style="line-height:3.4em;">
					<?php 
						// show the gravatar for the author
						echo get_avatar( $post->post_author, $size = '48', $default = '' ); 
						
						// show the author drop-down box 
						wp_dropdown_users( array(
							'who' => 'authors',
							'name' => 'post_author_override',
							'selected' => empty($post->ID) ? $user_ID : $post->post_author,
							'include_selected' => true
						));

						// display the author display name 
						$author = get_userdata( $post->post_author );
						echo '<br/><a href="user-edit.php?user_id=' . $author->ID . '">' . $author->display_name . '</a>';

					?>
				</td>
			</tr>

			<tr>
				<th style="width:20%"><label for="cp_sys_ad_conf_id"><?php _e('Ad Terms', 'appthemes'); ?>:</label></th>
				<td><?php echo cp_get_price( $post->ID, 'cp_sys_total_ad_cost' ); ?> <?php _e('for', 'appthemes'); ?> <?php echo esc_html( get_post_meta($post->ID, 'cp_sys_ad_duration', true) ); ?> <?php _e('days', 'appthemes'); ?></td>
			</tr>

			<tr>
				<th style="width:20%"><label for="cp_sys_expire_date"><?php _e('Ad Expires', 'appthemes'); ?>:</label></th>
				<td><input readonly type="text" name="cp_sys_expire_date" class="text" id="datepicker" value="<?php echo esc_html(get_post_meta($post->ID, 'cp_sys_expire_date', true)); ?>" /></td>
			</tr>			
				
			<tr>	
				<th colspan="2" style="padding:0px;">&nbsp;</th>
			</tr>		
			
			<?php cp_edit_ad_fields( $results, $post->ID ); // build the edit ad meta box ?>
			
		
			<tr>
				<th style="width:20%"><label for="cp_sys_userIP"><?php _e('Submitted from IP', 'appthemes'); ?>:</label></th>
				<td><?php echo esc_html( get_post_meta($post->ID, 'cp_sys_userIP', true) ); ?></td>
			</tr>	
			
		</table>	
		
	<?php 
	endif; 
	?>

<?php
}


// show the ad meta fields in a custom meta box
function cp_custom_images_meta_box() {
	global $post, $meta_boxes, $key;
	
	echo cp_edit_ad_images($post->ID); // pull in the ad images
	
}	


// builds the ad custom fields write panel
function cp_edit_ad_fields($results, $post_id) {
    global $wpdb;
	
	// add cp_sys fields to the array before adding cp_ custom fields
	$custom_fields_array = array('cp_sys_expire_date');

    foreach ( $results as $result ) :

        // get all the custom fields on the post and put into an array
        $custom_field_keys = get_post_custom_keys($post_id);

        if ( !$custom_field_keys ) continue;
            // wp_die('Error: There are no custom fields');

        // we only want key values that match the field_name in the custom field table .
        if ( in_array($result->field_name, $custom_field_keys) || $result->field_type == 'checkbox' ) :
		
			// add each custom field name to an array so we can save them correctly later
			$custom_fields_array[] = $result->field_name;

            // we found a match so go fetch the custom field value
            $post_meta_val = get_post_meta( $post_id, $result->field_name, true );

            // now loop through the form builder and make the proper field and display the value
            switch ( $result->field_type ) {

            case 'text box':
            ?>
				<tr>
					<th style="width:20%"><label for="<?php echo $result->field_name; ?>"><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>:</label></th>
					<td><input type="text" name="<?php echo $result->field_name; ?>" class="text" value="<?php echo esc_html($post_meta_val); ?>" /></td>
				</tr>

            <?php
            break;

            case 'drop-down':
            ?>
			
				<tr>
					<th style="width:20%"><label for="<?php echo $result->field_name; ?>"><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>:</label></th>
					<td>
					
						<select name="<?php echo $result->field_name; ?>" class="dropdownlist">
						
							<?php if ( !$result->field_req ) : ?><option value="">-- <?php _e('Select', 'appthemes') ?> --</option><?php endif; ?>
							<?php
							$options = explode( ',', $result->field_values );

							foreach ( $options as $option ) {
							?>
								<option style="min-width:177px" <?php if ($post_meta_val == trim($option)) { echo 'selected="yes"';} ?> value="<?php echo trim($option); ?>"><?php echo trim($option);?></option>
							<?php } ?>
						
						</select>
						
					</td>
				</tr>

            <?php
            break;

            case 'text area':

            ?>
				<tr>
					<th style="width:20%"><label for="<?php echo $result->field_name; ?>"><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>:</label></th>
					<td><textarea rows="4" cols="23" class="" name="<?php echo $result->field_name; ?>" id="<?php echo $result->field_name; ?>"><?php echo esc_html($post_meta_val); ?></textarea></td>
				</tr>

            <?php
            break;

			case 'radio':			
				$options = explode( ',', $result->field_values );
			?>
				<tr>
					<th style="width:20%"><label for="<?php echo $result->field_name; ?>"><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>:</label></th>
					<td>
						<div class="scrollbox">
							<ol class="radios">	
							
								<?php foreach ( $options as $option ) { ?>
									<li>	
										<input type="radio" name="<?php echo $result->field_name; ?>" id="<?php echo $result->field_name; ?>" value="<?php echo $option; ?>" class="radiolist" <?php if( trim($post_meta_val) == trim($option) ) { echo 'checked="checked"'; } ?>>&nbsp;&nbsp;<?php echo trim($option); ?>
									</li> <!-- #radio-button -->
								<?php } ?>
							
							</ol>
						</div>	
					</td>
				</tr>
	
			<?php
			break;
			
			case 'checkbox':
				$options = explode( ',', $result->field_values ); 
        // fetch the custom field values as array
        $post_meta_val = get_post_meta( $post_id, $result->field_name, false );
			?>
				<tr>
					<th style="width:20%"><label for="<?php echo $result->field_name; ?>"><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?>:</label></th>
					<td>
						<div class="scrollbox">
							<ol class="checkboxes">	
							
							<?php foreach ( $options as $option ) { ?>
								<li>
									<input type="checkbox" name="<?php echo $result->field_name; ?>[]" value="<?php echo trim($option); ?>" class="checkboxlist" <?php if(is_array($post_meta_val) && in_array(trim($option), $post_meta_val)) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;&nbsp;<?php echo trim($option); ?>
								</li> <!-- #checkbox -->		
							<?php } ?>	
							
							</ol>
						</div>	
					</td>
				</tr>


			<?php
			break;
			
            }

        endif;

    endforeach; 
	
	// put all the custom field names into an hidden field so we can process them on save
	$custom_fields_vals = implode( ',', $custom_fields_array );
	?>
	
	<input type="hidden" name="custom_fields_vals" value="<?php echo $custom_fields_vals; ?>" />
	
<?php	
// print_r($custom_fields_array);

}


// get the images associated with the ad
function cp_edit_ad_images($ad_id) {
  global $post;

	$args = array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $ad_id, 'order' => 'ASC', 'orderby' => 'ID');

	// get all the images associated to this ad
	$images = get_posts( $args );

	// print_r($images); // for debugging
	?>
	
	<script type="text/javascript">
	//<![CDATA[	
	jQuery(document).ready(function() {

		var formfield;

		/* upload an ad image */
		jQuery('input#upload_image_button').click(function() {
			formfield = jQuery(this).attr('rel');
			tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=image&amp;TB_iframe=true');
			return false;
		});

		window.original_send_to_editor = window.send_to_editor;

		/* send the uploaded image url to the field */
		window.send_to_editor = function(html) {
			if(formfield){
				var s = jQuery('img',html).attr('class'); // get the class with the image id
				var imageID = parseInt(/wp-image-(\d+)/.exec(s)[1], 10); // now grab the image id from the wp-image class
				var imgurl = jQuery('img',html).attr('src'); // get the image url
				var imgoutput = '<a href="' + imgurl + '" target="_blank"><img src="' + imgurl + '" /></a>'; //get the html to output for the image preview

				jQuery('#cp_print_url').val(imgurl); // return the image url to the field
				jQuery('input[name=new_ad_image_id]').val(imageID); // return the image url to the field
				jQuery('#cp_print_url').siblings('.upload_image_preview').slideDown().html(imgoutput); // display the uploaded image thumbnail
				tb_remove();
				formfield = null;
			}else{
				window.original_send_to_editor(html);
			}
		}

	});
	//]]>
	</script>

	<table class="form-table ad-meta-table">
	
	<?php
	// make sure we have images associated to the ad
	if ( $images ) : 
		$i = 1;
		
		foreach ( $images as $image ) :

			// go get the width and height fields since they are stored in meta data
			$meta = wp_get_attachment_metadata( $image->ID );
			if ( is_array($meta) && array_key_exists('width', $meta) && array_key_exists('height', $meta) )
				$media_dims = "<span id='media-dims-".$image->ID."'>{$meta['width']}&nbsp;&times;&nbsp;{$meta['height']}</span> ";
		?>		
		
		<tr>
			<th style="width:20%"><label><?php _e('Image', 'appthemes'); ?> <?php echo $i ?>:</label></th>

			<td>
				<div class="thumb-wrap-edit">
					<?php echo cp_get_attachment_link( $image->ID ); ?>
				</div>

				<div class="image-meta">
					<input class="checkbox" type="checkbox" name="image[]" value="<?php echo $image->ID; ?>">&nbsp;<?php _e('Delete Image', 'appthemes') ?><br />
					<strong><?php _e('Upload Date:', 'appthemes') ?></strong> <?php echo mysql2date( get_option('date_format'), $image->post_date); ?><br />
					<strong><?php _e('File Info:', 'appthemes') ?></strong> <?php echo $media_dims ?> (<?php echo $image->post_mime_type; ?>)<br />
				</div>
				
				<div class="clr"></div>

				<?php $alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true ); // get the alt text and print out the field  ?>
				
				<p class="alt-text">
					<div class="labelwrapper">
						<label><?php _e('Alt Text:','appthemes') ?></label>
					</div>
					<input type="text" class="text" name="attachments[<?php echo $image->ID; ?>][image_alt]" id="image_alt" value="<?php if ( count($alt) ) echo esc_attr( stripslashes($alt) ); ?>" />
				</p>
				
			</td>
			
		</tr>	

		<?php
		$i++;
		endforeach;

	endif;
	?>
	
		<tr>
			<th style="width:20%"><label><?php _e('New Image', 'appthemes'); ?>:</label></th>

			<td>
				<input style="display:none;" type="text" readonly name="cp_print_url" id="cp_print_url" class="upload_image_url text" value="" />
				<input id="upload_image_button" class="upload_button button" rel="cp_print_url" type="button" value="<?php _e('Add Image', 'appthemes') ?>" />							
				<br />
				<div class="upload_image_preview"></div>
				<input type="text" class="hide" id="imageid" name="new_ad_image_id" value="" />	
			</td>
			
		</tr>
	
	</table>
	
	<?php

}



// save all meta values on the ad listing
function cp_save_meta_box($post_id) {
	global $wpdb, $post, $key;
	
	// make sure something has been submitted from our nonce
	if ( !isset($_POST[$key . '_wpnonce']) ) 
		return $post_id;
		
	// verify this came from the our screen and with proper authorization, 
	// because save_post can be triggered at other times	
	if ( !wp_verify_nonce($_POST[$key . '_wpnonce'], basename(__FILE__)) ) 
		return $post_id;
	
	// verify if this is an auto save routine. 
	// if it is our form and it has not been submitted, dont want to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;

	// lastly check to make sure this user has permissions to save post fields
	if ( !current_user_can('edit_post', $post_id) ) 
		return $post_id;
		
		
	// delete any images checked
    if ( !empty($_POST['image']) )
        cp_delete_image();	
        
    // update the image alt text
	if ( !empty($_POST['attachments']) )
		cp_update_alt_text();   
		
	// add a new image if one has been detected
	if ( $attach_id = $_POST['new_ad_image_id'] )
		wp_update_post( array( 'ID' => $attach_id, 'post_parent' => $post_id ) );
		

	// assemble the comma separated hidden fields back into an array so we can save them.
	$metafields = explode( ',', $_POST['custom_fields_vals'] );
	
	// loop through all custom meta fields and update values
	foreach ( $metafields as $name ) {
		
		//echo $name . ' <-- POST NAME<br/>';
		//echo $_POST["$name"] . ' <-- VALUE<br/><br/>';	
	
		if ( !isset($_POST[$name]) ) {
      delete_post_meta($post_id, $name);
    } else if ( is_array($_POST[$name]) ) {
  		delete_post_meta($post_id, $name);
      foreach ( $_POST[$name] as $checkbox_value )
        add_post_meta( $post_id, $name, $checkbox_value );
    } else {
  		update_post_meta( $post_id, $name, $_POST[$name] );
    }
	}	

	
	// give the ad a unique ID if it's a new ad listing
	if ( !$cp_id = get_post_meta($post->ID, 'cp_sys_ad_conf_id', true) ) {	
		$cp_item_id = uniqid( rand(10,1000), false );
		add_post_meta( $post_id, 'cp_sys_ad_conf_id', $cp_item_id, true );
	}
	
	// save the IP address if it's a new ad listing
	if ( !$cp_ip = get_post_meta($post->ID, 'cp_sys_userIP', true) ) {	
		add_post_meta( $post_id, 'cp_sys_userIP', appthemes_get_ip(), true );
	}
	
	// set stats to zero so we at least have some data
	if ( !$cp_dcount = get_post_meta($post->ID, 'cp_daily_count', true) ) {
		add_post_meta( $post_id, 'cp_daily_count', '0', true );
	}
	
	if ( !$cp_tcount = get_post_meta( $post->ID, 'cp_total_count', true) ) {
		add_post_meta( $post_id, 'cp_total_count', '0', true );
	}

  // set default ad duration, will need it to renew
	if ( !$cp_ad_duration = get_post_meta( $post->ID, 'cp_sys_ad_duration', true) ) {
    $ad_length = get_option('cp_prun_period');
		add_post_meta( $post_id, 'cp_sys_ad_duration', $ad_length, true );
	}

  // set ad cost to zero, will need it for free renew
	if ( !$cp_tcost = get_post_meta( $post->ID, 'cp_sys_total_ad_cost', true) ) {
		add_post_meta( $post_id, 'cp_sys_total_ad_cost', '0.00', true );
	}

}
add_action('save_post', 'cp_save_meta_box');


?>
