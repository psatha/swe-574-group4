<?php

// include all the core admin ClassiPress files
require_once ('admin-values.php');
require_once ('admin-notices.php');
require_once ('admin-addons.php');
require_once ('admin-updates.php');


// load and create all the CP admin pages
function appthemes_admin_options() {
	global $wpdb, $app_abbr, $app_theme;

	if ( !current_user_can('manage_options') ) return;

	add_menu_page($app_theme, $app_theme, 'manage_options', basename(__FILE__), 'cp_dashboard', FAVICON, THE_POSITION );
	add_submenu_page( basename(__FILE__), __('Dashboard','appthemes'), __('Dashboard','appthemes'), 'manage_options', basename(__FILE__), 'cp_dashboard' );
	add_submenu_page( basename(__FILE__), __('General Settings','appthemes'), __('Settings','appthemes'), 'manage_options', 'settings', 'cp_settings' );
	add_submenu_page( basename(__FILE__), __('Emails','appthemes'), __('Emails','appthemes'), 'manage_options', 'emails', 'cp_emails' );
	//add_submenu_page( basename(__FILE__), __('Pricing Settings','appthemes'), __('Pricing','appthemes'), 'manage_options', 'pricing', 'cp_pricing' );
	//add_submenu_page( basename(__FILE__), __('Packages','appthemes'), __('Packages','appthemes'), 'manage_options', 'packages', 'cp_ad_packs' );
	//add_submenu_page( basename(__FILE__), __('Coupons','appthemes'), __('Coupons','appthemes'), 'manage_options', 'coupons', 'cp_coupons' );
	//add_submenu_page( basename(__FILE__), __('Payment Gateway Options','appthemes'), __('Gateways','appthemes'), 'manage_options', 'gateways', 'cp_gateways' );
	add_submenu_page( basename(__FILE__), __('Form Layouts','appthemes'), __('Form Layouts','appthemes'), 'manage_options', 'layouts', 'cp_form_layouts' );
	add_submenu_page( basename(__FILE__), __('Custom Fields','appthemes'), __('Custom Fields','appthemes'), 'manage_options', 'fields', 'cp_custom_fields' );
	//add_submenu_page( basename(__FILE__), __('Transactions','appthemes'), __('Transactions','appthemes'), 'manage_options', 'transactions', 'cp_transactions' );
	//add_submenu_page( basename(__FILE__), __('System Info','appthemes'), __('System Info','appthemes'), 'manage_options', 'sysinfo', 'cp_system_info' );

	do_action( 'appthemes_add_submenu_page' );
}
add_action('admin_menu', 'appthemes_admin_options');



// update all the admin options on save
function cp_update_options($options) {
    $toolsMessage = '';

    if (isset($_POST['submitted']) && $_POST['submitted'] == 'yes') {

            foreach ( $options as $value ) {
                if ( isset($_POST[$value['id']]) ) {
                    //echo $value['id'] . '<-- value ID | ' . $_POST[$value['id']] . '<-- $_POST value ID <br/><br/>'; // FOR DEBUGGING
                    update_option( $value['id'], appthemes_clean($_POST[$value['id']]) );
                } else {
                    @delete_option( $value['id'] );
                }
            }

            // do a separate update for price per cats since it's not in the $options array
            if ( isset($_POST['catarray']) ) {
                foreach ( $_POST['catarray'] as $key => $value ) {
                    // echo $key .'<-- key '. $value .'<-- value<br/>'; // FOR DEBUGGING
                    update_option( $key, appthemes_clean($value) );
                }
            }

            // clean all values from the post and store them into a wordpress option as a serialized array of cat ID's
            if ( isset($_POST['catreqarray']) ) {
                foreach ( $_POST['catreqarray'] as $key => $value ) {
                    $catreqarray[absint($value)] = '';
                }
                update_option('cp_required_categories', $catreqarray);
            } else if (isset($_POST['cp_required_membership_type'])){
                delete_option('cp_required_categories');
            }

			if ( get_option('cp_tools_run_expiredcheck') == 'yes' ) {
					update_option('cp_tools_run_expiredcheck', 'no');
					cp_check_expired_cron();
					$toolsMessage = '';
					$toolsMessage .= __('Ads Expired Check was executed.');
			}

			// flush out the cache so changes can be visible
			cp_flush_all_cache();

            echo '<div class="updated"><p>'.__('Your settings have been saved.','appthemes'). ' ' . $toolsMessage . '</p></div>';

    } elseif ( isset($_POST['submitted']) && $_POST['submitted'] == 'convertToCustomPostType' ) {
		update_option('cp_tools_run_convertToCustomPostType', 'no');
		$toolsMessage .= cp_convert_posts2Ads();
		echo $toolsMessage;
	}
}

// creates the category checklist box
function cp_category_checklist($checkedcats, $exclude = '') {

	if (empty($walker) || !is_a($walker, 'Walker'))
		$walker = new Walker_Category_Checklist;

	$args = array();

    if (is_array( $checkedcats ))
        $args['selected_cats'] = $checkedcats;
    else
        $args['selected_cats'] = array();

    $args['popular_cats'] = array();
    $categories = get_categories( array('hide_empty' => 0,
                                       'taxonomy' 	 => APP_TAX_CAT,
                                       'exclude' 	 => $exclude) );

	return call_user_func_array( array(&$walker, 'walk'), array($categories, 0, $args) );
}


// this grabs the cats that should be excluded
function cp_exclude_cats ($id = NULL) {
    global $wpdb;

    $output = array();

    if ( $id )
        $sql = $wpdb->prepare( "SELECT form_cats FROM $wpdb->cp_ad_forms WHERE id != %s", $id );
    else
        $sql = $wpdb->prepare( "SELECT form_cats FROM $wpdb->cp_ad_forms" );

    $records = $wpdb->get_results( $sql );

    if ( $records ) :

        foreach ( $records as $record )
            $output[] = implode( ',',unserialize($record->form_cats) );

    endif;

    $exclude = cp_unique_str( ',', (join( ',', $output )) );

    return $exclude;
}


// find a category match and then output it
function cp_match_cats($form_cats) {
    global $wpdb;
    $out = array();

    $terms = get_terms( APP_TAX_CAT, array(
        'include' => $form_cats
    ));

    if ( $terms ) :

        foreach ( $terms as $term ) {
            $out[] = '<a href="edit-tags.php?action=edit&taxonomy='.APP_TAX_CAT.'&post_type='.APP_POST_TYPE.'&tag_ID='. $term->term_id .'">'. $term->name .'</a>';
        }

    endif;

    return join( ', ', $out );
}


function cp_unique_str($separator, $str) {

    $str_arr = explode($separator, $str);
    $result = array_unique($str_arr);
    $unique_str = implode(',', $result);

    return $unique_str;
}


/**
* Take field input label value and make custom name
* Strip out everything excepts chars & numbers
* Used for WP custom field name i.e. Middle Name = cp_middle_name
*/
function cp_make_custom_name($cname) {

	$cname = preg_replace('/[^a-zA-Z0-9\s]/', '', $cname);
	$cname = 'cp_' . str_replace(' ', '_', strtolower(substr(appthemes_clean($cname), 0, 30)));

	return $cname;
}

// delete the custom form and the meta custom field data
function cp_delete_form($form_id) {
    global $wpdb;

	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->cp_ad_forms WHERE id = %s", $form_id ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->cp_ad_meta WHERE form_id = %s", $form_id ) );
}


function cp_admin_formbuilder($results) {
	global $wpdb;

	foreach ( $results as $result ) :
	?>

		<tr class="even" id="<?php echo $result->meta_id; ?>"><!-- id needed for jquery sortable to work -->
			<td style="min-width:100px;"><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?></td>
			<td>

		<?php

		switch ( $result->field_type ) {

		case 'text box':
		?>

			<input name="<?php echo $result->field_name; ?>" type="text" style="min-width:200px;" value="" disabled />

		<?php
		break;

		case 'text area':

		?>

			<textarea rows="4" cols="23" disabled></textarea>

		<?php
		break;

		case 'radio':

			$options = explode( ',', $result->field_values );
			foreach ( $options as $label ) {
			?>
				<input type="radio" name="radiobutton" value="" disabled />&nbsp;<?php echo $label; ?><br />

		<?php
			}
		break;

		case 'checkbox':

			$options = explode( ',', $result->field_values );
			foreach ( $options as $label ) {
			?>
				<input type="checkbox" name="checkbox" value="" disabled />&nbsp;<?php echo $label; ?><br />

		<?php
			}
		break;

		default: // used for drop-downs, radio buttons, and checkboxes
		?>

			<select name="dropdown">

			<?php
			$options = explode( ',', $result->field_values );

			foreach ( $options as $option ) {
			?>

				<option style="min-width:177px" value="<?php echo $option; ?>" disabled><?php echo $option; ?></option>

			<?php
			}
			?>

			</select>

		<?php

		} //end switch
		?>

			</td>

			<td style="text-align:center;">

			    <?php
			    // only show the advanced search checkbox for price, city, and zipcode since they display the sliders
				// all other text fields are not intended for advanced search use
				$ad_search = '';
				if ( $result->field_name == 'cp_price' || $result->field_name == 'cp_city' || $result->field_name == 'cp_zipcode' )
                    $ad_search = '';
				elseif ( $result->field_perm == 1 || $result->field_type == 'text area' || $result->field_type == 'text box' )
				    $ad_search = 'disabled="disabled"';
				?>

				<input type="checkbox" name="<?php echo $result->meta_id; ?>[field_search]" id="" <?php if ( $result->field_search ) echo 'checked="yes"' ?> <?php if ( $result->field_search ) echo 'checked="yes"' ?> <?php echo $ad_search; ?> value="1" style="" />

			</td>

			<td style="text-align:center;">

				<input type="checkbox" name="<?php echo $result->meta_id; ?>[field_req]" id="" <?php if ( $result->field_req ) echo 'checked="yes"' ?> <?php if ( $result->field_req ) echo 'checked="yes"' ?> <?php if ( $result->field_perm == 1 ) echo 'disabled="disabled"'; ?> value="1" style="" />
				<?php if ($result->field_perm == 1) { ?>
					<input type="hidden" name="<?php echo $result->meta_id; ?>[field_req]" checked="yes" value="1" />
				<?php } ?>

			</td>

			<td style="text-align:center;">

				<input type="hidden" name="id[]" value="<?php echo $result->meta_id; ?>" />
				<input type="hidden" name="<?php echo $result->meta_id; ?>[id]" value="<?php echo $result->meta_id; ?>" />

				<?php if ( $result->field_perm == 1 ) { ?>
				<img src="<?php bloginfo('template_directory'); ?>/images/remove-row-gray.png" alt="<?php  _e('Cannot remove from layout','appthemes') ?>" title="<?php  _e('Cannot remove from layout','appthemes') ?>" />
				<?php } else { ?>
				<a onclick="return confirmBeforeRemove();" href="?page=layouts&amp;action=formbuilder&amp;id=<?php echo $result->form_id ?>&amp;del_id=<?php echo $result->meta_id ?>&amp;title=<?php echo urlencode($_GET['title']) ?>"><img src="<?php bloginfo('template_directory'); ?>/images/remove-row.png" alt="<?php  _e('Remove from layout','appthemes') ?>" title="<?php  _e('Remove from layout','appthemes') ?>" /></a>
				<?php } ?>

			</td>
		</tr>

	<?php
	endforeach;

}

// this creates the default fields when a form layout is created
function cp_add_core_fields($form_id) {
	global $wpdb;

    // check to see if any rows already exist for this form. If so, don't insert any data
    $wpdb->get_results( $wpdb->prepare( "SELECT form_id FROM $wpdb->cp_ad_meta WHERE form_id  = %s", $form_id ) );

    // no fields yet so let's add the defaults
    if ( $wpdb->num_rows == 0 ) {

        $insert = "INSERT INTO $wpdb->cp_ad_meta" .
        " (form_id, field_id, field_req, field_pos) " .
        "VALUES ('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('1'). "','" // post_title
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('1')
          . "'),"
          . "('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('2'). "','" // cp_price
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('2')
          . "'),"
          . "('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('3'). "','" // cp_street
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('3')
          . "'),"
          . "('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('4'). "','" // cp_city
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('4')
          . "'),"
          . "('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('5'). "','" // cp_state
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('5')
          . "'),"
          . "('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('6'). "','" // cp_country
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('6')
          . "'),"
          . "('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('7'). "','" // cp_zipcode
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('7')
          . "'),"
          . "('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('8'). "','" // tags_input
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('8')
          . "'),"
          . "('"
          . $wpdb->escape($form_id). "','"
          . $wpdb->escape('9'). "','" // post_content
          . $wpdb->escape('1'). "','"
          . $wpdb->escape('9')
          . "')";

        $results = $wpdb->query( $insert );

    }
}


function cp_admin_db_fields($options, $cp_table, $cp_id) {
    global $wpdb;

    // gat all the admin fields
    $results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ". $wpdb->prefix . $cp_table . " WHERE ". $cp_id ." = %d", $_GET['id'] ) );

    // If the pack has a type, check if it satisfies.
    if( isset( $results->pack_type ) && strpos( $results->pack_type, "required_" ) === 0 ){
    	$results->pack_satisfies_required = "required_";
      $results->pack_type = mb_substr($results->pack_type, 9, strlen($results->pack_type));
    }else{
    	$results->pack_satisfies_required = "";
    }

    ?>

    <table class="widefat fixed" id="tblspacer" style="width:850px;">

    <?php

    foreach ( $options as $value ) {

      if ( $results ) {

          // foreach ($results as $result):

          // check to prevent "Notice: Undefined property: stdClass::" error when php strict warnings is turned on
          if ( !isset($results->field_type) ) $field_type = ''; else $field_type = $results->field_type;
          if ( !isset($results->field_perm) ) $field_perm = ''; else $field_perm = $results->field_perm;

          switch($value['type']) {

            case 'title':
            ?>

                <thead>
                    <tr>
                        <th scope="col" width="200px"><?php echo $value['name'] ?></th><th scope="col">&nbsp;</th>
                    </tr>
                </thead>

            <?php

            break;

            case 'text':

            ?>

	       <tr id="<?php echo $value['id'] ?>_row" <?php if ($value['vis'] == '0') echo ' style="display:none;"'; ?>>
                    <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                    <td class="forminp"><input name="<?php echo $value['id'] ?>" id="<?php echo $value['id'] ?>" type="<?php echo $value['type'] ?>" style="<?php echo $value['css'] ?>" value="<?php echo $results->$value['id'] ?>" <?php if ($value['req']) { ?> class="required <?php if (!empty($value['altclass'])) echo $value['altclass'] ?>" <?php } ?><?php if ($value['min']) ?> minlength="<?php echo $value['min'] ?>" <?php if($value['id'] == 'field_name') { ?>readonly="readonly"<?php } ?> /><br /><small><?php echo $value['desc'] ?></small></td>
                </tr>

            <?php

            break;

            case 'select':

            ?>

               <tr id="<?php echo $value['id'] ?>_row">
                   <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                   <td class="forminp"><select <?php if ($value['js']) echo $value['js']; ?> <?php if(($field_perm == 1) || ($field_perm == 2)) { ?>DISABLED<?php } ?> name="<?php echo $value['id'] ?>" id="<?php echo $value['id'] ?>" style="<?php echo $value['css'] ?>">

                       <?php foreach ( $value['options'] as $key => $val ) { ?>

                             <option value="<?php echo $key ?>"<?php if (isset($results->$value['id']) && $results->$value['id'] == $key) { ?> selected="selected" <?php $field_type_out = $field_type; } ?>><?php echo $val; ?></option>

                       <?php } ?>

                       </select><br />
                       <small><?php echo $value['desc'] ?></small>

                       <?php
                       // have to submit this field as a hidden value if perms are 1 or 2 since the DISABLED option won't pass anything into the $_POST
                       if ( ($field_perm == 1) || ($field_perm == 2) ) { ?><input type="hidden" name="<?php echo $value['id'] ?>" value="<?php echo $field_type_out; ?>" /><?php } ?>

                   </td>
               </tr>

            <?php

            break;

            case 'textarea':

            ?>

               <tr id="<?php echo $value['id'] ?>_row"<?php if($value['id'] == 'field_values') { ?> style="display: none;" <?php } ?>>
                   <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                   <td class="forminp"><textarea <?php if((($field_perm == 1) || ($field_perm == 2)) && ($value['id'] != 'field_tooltip') && $value['id'] != 'field_values') { ?>readonly="readonly"<?php } ?> name="<?php echo $value['id']?>" id="<?php echo $value['id'] ?>" style="<?php echo $value['css'] ?>"><?php echo $results->$value['id'] ?></textarea>
                       <br /><small><?php echo $value['desc'] ?></small></td>
               </tr>

            <?php

            break;

            case 'checkbox':
            ?>

                <tr id="<?php echo $value['id'] ?>_row">
                    <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                    <td class="forminp"><input type="checkbox" name="<?php echo $value['id'] ?>" id="<?php echo $value['id'] ?>" value="1" style="<?php echo $value['css']?>" <?php if($results->$value['id']) { ?>checked="checked"<?php } ?> />
                        <br /><small><?php echo $value['desc'] ?></small>
                    </td>
                </tr>

            <?php
            break;

            case 'cat_checklist':

            ?>

               <tr id="<?php echo $value['id'] ?>_row">
                   <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                   <td class="forminp">
                       <div id="categorydiv">
                           <div class="tabs-panel" id="categories-all" style="<?php echo $value['css'] ?>">
                               <ul class="list:category categorychecklist form-no-clear" id="categorychecklist">

                                   <?php echo cp_category_checklist( unserialize($results->form_cats),(cp_exclude_cats($results->id)) ); ?>

                               </ul>
                           </div>
                       </div>
                       <br /><small><?php echo $value['desc'] ?></small>
                   </td>
               </tr>

            <?php

            break;


        } // end switch

      } // end $results

    } // endforeach

    ?>
    
    </table>

<?php
}


function cp_admin_fields($options) {
	global $shortname, $app_abbr;
?>


<div id="tabs-wrap">


    <?php

    // first generate the page tabs
    $counter = 0;

    echo '<ul class="tabs">'. "\n";
    foreach ( $options as $value ) {

        if ( in_array('tab', $value) ) :
            echo '<li><a href="#'.$value['type'].$counter.'">'.$value['tabname'].'</a></li>'. "\n";
            $counter = $counter + 1;
        endif;

    }
    echo '</ul>'. "\n\n";


     // now loop through all the options
    $counter = 0;
    $table_width = get_option('cp_table_width');

    foreach ( $options as $value ) {

        switch ( $value['type'] ) {

            case 'tab':

                echo '<div id="'.$value['type'].$counter.'">'. "\n\n";
                echo '<table class="widefat fixed" style="width:'.$table_width.'; margin-bottom:20px;">'. "\n\n";

            break;

            case 'notab':

                echo '<table class="widefat fixed" style="width:'.$table_width.'; margin-bottom:20px;">'. "\n\n";

            break;

            case 'title':
            ?>

                <thead><tr><th scope="col" width="200px"><?php echo $value['name'] ?></th><th scope="col"><?php if ( isset( $value['desc'] ) ) echo $value['desc'] ?>&nbsp;</th></tr></thead>

            <?php
            break;

            case 'text':
            ?>

            <?php if ( $value['id'] <> 'field_name' ) { // don't show the meta name field used by WP. This is automatically created by CP. ?>
                <tr <?php if ($value['vis'] == '0') { ?>id="<?php if ( !empty($value['visid']) ) { echo $value['visid']; } else { echo 'field_values'; } ?>" style="display:none;"<?php } else { ?>id="<?php echo $value['id'] ?>_row"<?php } ?>>
                    <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                    <td class="forminp"><input name="<?php echo $value['id'] ?>" id="<?php echo $value['id'] ?>" type="<?php echo $value['type'] ?>" style="<?php echo $value['css'] ?>" value="<?php if (get_option( $value['id'])) echo get_option( $value['id'] ); else echo $value['std'] ?>"<?php if ($value['req']) { ?> class="required <?php if ( !empty($value['altclass']) ) echo $value['altclass'] ?>" <?php } ?> <?php if ( $value['min'] ) { ?> minlength="<?php echo $value['min'] ?>"<?php } ?> /><br /><small><?php echo $value['desc'] ?></small></td>
                </tr>
            <?php } ?>

            <?php
            break;

            case 'select':
            ?>

                <tr id="<?php echo $value['id'] ?>_row">
                    <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                    <td class="forminp"><select <?php if ( !empty( $value['js'] ) ) echo $value['js']; ?> name="<?php echo $value['id'] ?>" id="<?php echo $value['id'] ?>" style="<?php echo $value['css'] ?>"<?php if ( $value['req'] ) { ?> class="required"<?php } ?>>

                        <?php
                        foreach ($value['options'] as $key => $val) {
                        ?>

                            <option value="<?php echo $key ?>" <?php if ( get_option($value['id']) == $key ) { ?> selected="selected" <?php } ?>><?php echo ucfirst($val) ?></option>

                        <?php
                        }
                        ?>

                       </select><br /><small><?php echo $value['desc'] ?></small>
                    </td>
                </tr>

            <?php
            break;

            case 'checkbox':
            ?>

                <tr id="<?php echo $value['id'] ?>_row">
                    <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                    <td class="forminp"><input type="checkbox" name="<?php echo $value['id'] ?>" id="<?php echo $value['id'] ?>" value="true" style="<?php echo $value['css']?>" <?php if(get_option($value['id'])) { ?>checked="checked"<?php } ?> />
                        <br /><small><?php echo $value['desc'] ?></small>
                    </td>
                </tr>

            <?php
            break;

            case 'textarea':
            ?>
                <tr id="<?php echo $value['id'] ?>_row"<?php if ( $value['id'] == 'field_values' ) { ?> style="display: none;" <?php } ?>>
                    <td class="titledesc"><?php if ( $value['tip'] ) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                    <td class="forminp">
                        <textarea name="<?php echo $value['id'] ?>" id="<?php echo $value['id'] ?>" style="<?php echo $value['css'] ?>" <?php if ($value['req']) { ?> class="required" <?php } ?><?php if ( $value['min'] ) { ?> minlength="<?php echo $value['min'] ?>"<?php } ?>><?php if ( get_option($value['id']) ) echo stripslashes( get_option($value['id']) ); else echo $value['std']; ?></textarea>
                        <br /><small><?php echo $value['desc'] ?></small>
                    </td>
                </tr>

            <?php
            break;

            case 'cat_checklist':
            ?>

                <tr id="<?php echo $value['id'] ?>_row">
                    <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
                    <td class="forminp">
                        <div id="categorydiv">
                            <div class="tabs-panel" id="categories-all" style="<?php echo $value['css'] ?>">
                                <ul class="list:category categorychecklist form-no-clear" id="categorychecklist">
                                <?php $catcheck = cp_category_checklist(0,cp_exclude_cats()); ?>
                                <?php if($catcheck) echo $catcheck; else wp_die( '<p style="color:red;">' .__('All your categories are currently being used. You must remove at least one category from another form layout before you can continue.','appthemes') .'</p>' ); ?>
                                </ul>
                            </div>
                        </div>
                        <br /><small><?php echo $value['desc'] ?></small>
                    </td>
                </tr>

            <?php
            break;

			case 'upload':
			?>
				<tr>
					<td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>
					<td class="forminp">
						<input id="<?php echo $value['id'] ?>" class="upload_image_url" type="text" style="<?php echo $value['css'] ?>" name="<?php echo $value['id'] ?>" value="<?php if (get_option( $value['id'])) echo get_option( $value['id'] ); else echo $value['std'] ?>" />
						<input id="upload_image_button" class="upload_button button" rel="<?php echo $value['id'] ?>" type="button" value="<?php _e('Upload Image', 'appthemes') ?>" />
						<?php if (get_option( $value['id'])){ ?>
						    <input name="<?php echo $value['id'] ?>" value="Clear Image" id="delete_image_button" class="delete_button button" rel="<?php echo $value['id'] ?>" type="button" />
						<?php } ?>
						<br /><small><?php echo $value['desc'] ?></small>
						<div id="<?php echo $value['id'] ?>_image" class="<?php echo $value['id'] ?>_image upload_image_preview"><?php if (get_option( $value['id'])) echo '<img src="' .get_option( $value['id'] ) . '" />'; ?></div>

					</td>
                </tr>

			<?php
			break;

            case 'logo':
            ?>
                <tr id="<?php echo $value['id'] ?>_row">
                    <td class="titledesc"><?php echo $value['name'] ?></td>
                    <td class="forminp">&nbsp;</td>
                </tr>

            <?php
            break;

            case 'price_per_cat':
            ?>
                <tr id="<?php echo $value['id'] ?>_row"  class="cat-row">
                    <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>

                    <td class="forminp">

                        <table style="width:100%;">

                        <?php

                        $categories = get_categories('orderby=name&order=asc&hide_empty=0&taxonomy='.APP_TAX_CAT);
                        $i = 0;

                        foreach ($categories as $cat) {

                            if (($i % 2) == 0) { ?>
                                <tr>
                            <?php
                            }

                            // if the category price is empty, put a zero in it so it doesn't error out
                            $cat_price = get_option('cp_cat_price_'.$cat->cat_ID);
                            if ($cat_price == '') {
                                $cat_price = '0';
                            }
                            ?>

                            <td nowrap style="padding-top:15px; text-align: right;"><?php echo $cat->cat_name; ?>:</td>
                            <td nowrap style="color:#bbb;"><input name="catarray[cp_cat_price_<?php echo $cat->cat_ID; ?>]" type="text" size="10" maxlength="100" value="<?php echo $cat_price ?>" />&nbsp;<?php echo get_option($app_abbr.'_curr_pay_type') ?></td>
                            <td cellspan="2" width="100">&nbsp;</td>

                            <?php
                            if (($i % 2) != 0) { ?>
                                </tr>
                            <?php
                            }

                            $i++;

                        } // end foreach
                        ?>

                        </table>

                    </td>
                </tr>


            <?php
            break;

			case 'required_per_cat':
            ?>
                <tr id="<?php echo $value['id'] ?>_row"  class="cat-row">
                    <td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" tabindex="99"><div class="helpico"></div></a><?php } ?><?php echo $value['name'] ?>:</td>

                    <td class="forminp">

                        <table style="width:100%;">

                        <?php

                        $categories = get_categories('orderby=name&order=asc&hide_empty=0&taxonomy='.APP_TAX_CAT);
						$required_categories = get_option('cp_required_categories');
                        $i = 0;

                        foreach ($categories as $cat) {

                            if (($i % 2) == 0) { ?>
                                <tr>
                            <?php
                            }

                            ?>

                            <td nowrap style="padding-top:15px; text-align: right;"><?php echo $cat->cat_name; ?>:</td>
                            <td nowrap style="color:#bbb;"><input name="catreqarray[cp_cat_req_<?php echo $cat->cat_ID; ?>]" type="checkbox" value="<?php echo $cat->cat_ID; ?>" <?php if(isset($required_categories[$cat->cat_ID])) echo 'checked="checked"'; ?> /></td>
                            <td cellspan="2" width="100">&nbsp;</td>

                            <?php
                            if (($i % 2) != 0) { ?>
                                </tr>
                            <?php
                            }

                            $i++;

                        } // end foreach
                        ?>

                        </table>

                    </td>
                </tr>


            <?php
            break;

            case 'tabend':

                echo '</table>'. "\n\n";
                echo '</div> <!-- #tab'.$counter.' -->'. "\n\n";
                $counter = $counter + 1;

            break;

            case 'notabend':

                echo '</table>'. "\n\n";

            break;

        } // end switch

    } // end foreach
    ?>

   </div> <!-- #tabs-wrap -->

<?php
}


do_action( 'appthemes_add_submenu_page_content' );


function cp_dashboard() {
		global $wpdb, $app_edition, $app_rss_feed;
		global $app_twitter_rss_feed, $app_forum_rss_feed, $options_dashboard;

		$date_today = date('Y-m-d');
		$date_yesterday = date('Y-m-d', strtotime('-1 days'));

		$ad_counts = wp_count_posts( APP_POST_TYPE );
		$ad_count_live = $ad_counts->publish;
		$ad_count_pending = $ad_counts->pending;
		$capabilities_meta = $wpdb->prefix . 'capabilities';

		$ad_rev_total = $wpdb->get_var( $wpdb->prepare( "SELECT sum(mc_gross) FROM $wpdb->cp_order_info" ) );
		$customers_today = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->users.user_registered >= %s", $capabilities_meta, '%administrator%', $date_today ) );
		$customers_yesterday = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = %s AND ($wpdb->usermeta.meta_value NOT LIKE %s) AND $wpdb->users.user_registered BETWEEN %s AND %s", $capabilities_meta, '%administrator%', $date_yesterday, $date_today ) );
		$countusers = count_users();
?>


        <div class="wrap">
        <div class="icon32" id="icon-themes"><br /></div>
        <h2><?php _e('ClassiPress Dashboard', 'appthemes') ?></h2>

        <?php cp_admin_info_box(); ?>

        <div class="dash-left metabox-holder">

        <div class="dash-wrap">

			<div class="postbox">

				<div class="statsico"></div>
				<h3 class="hndle"><span><?php _e('ClassiPress Info', 'appthemes') ?></span></h3>

					<div class="inside" id="boxy">

                        <?php
                            // $cp_edition = get_option('cp_edition');
                            $cp_version = get_option('cp_version');
                        ?>
                        <div class="stats-info">
                            <ul>
                                <li><?php _e('Total Live Ads', 'appthemes')?>: <a href="edit.php?post_status=publish&post_type=<?php echo APP_POST_TYPE ?>"><strong><?php echo $ad_count_live; ?></strong></a></li>
                                <li><?php _e('Total Pending Ads', 'appthemes')?>: <a href="edit.php?post_status=pending&post_type=<?php echo APP_POST_TYPE ?>"><strong><?php echo $ad_count_pending; ?></strong></a></li>
                                <li><?php _e('Total Users', 'appthemes')?>: <a href="users.php?orderby=id&order=desc"><strong><?php echo number_format_i18n( $countusers['total_users'] ); ?></strong></a></li>
                                <li><?php _e('Total Revenue', 'appthemes')?>: <strong><?php echo cp_pos_price( number_format( $ad_rev_total, 2 ) ); ?></strong></li>
                                <li><?php _e('Product Support', 'appthemes')?>:  <a href="http://forums.appthemes.com/" target="_blank"><?php _e('Forum','appthemes')?></a> | <a href="http://docs.appthemes.com/" target="_blank"><?php _e('Documentation','appthemes')?></a></li>
                            </ul>
	                    </div>


					<div class="stats_overview">
                        <h3><?php _e('New Registrations', 'appthemes') ?></h3>
                        <div class="overview_today">
                            <p class="overview_day"><?php _e('Today', 'appthemes') ?></p>
                            <p class="overview_count"><?php echo number_format_i18n($customers_today); ?></p>
                            <p class="overview_type"><em><?php _e('Customers', 'appthemes') ?></em></p>
                        </div>

                        <div class="overview_previous">
                            <p class="overview_day"><?php _e('Yesterday', 'appthemes') ?></p>
                            <p class="overview_count"><?php echo number_format_i18n($customers_yesterday); ?></p>
                            <p class="overview_type"><em><?php _e('Customers', 'appthemes') ?></em></p>
                        </div>
                    </div>

                    </div><!-- /inside -->

                    <div class="clear"></div>

			</div> <!-- /postbox -->


		</div> <!-- /dash-wrap -->

	</div> <!-- /dash-left -->



	<div class="dash-right metabox-holder">

		<div class="dash-wrap">

			<div class="postbox">

				<div class="statsico"></div>
				<h3 class="hndle" id="poststuff"><span><?php _e('Stats - Last 30 Days', 'appthemes') ?></span></h3>

				<div class="inside" id="boxy">

					<?php cp_dashboard_charts(); ?>

				</div> <!-- /inside -->

			</div> <!-- /postbox -->


		</div> <!-- /dash-wrap -->

	</div> <!-- /dash-right -->

</div> <!-- /wrap -->

<?php
}


function cp_settings() {
    global $options_settings;

    cp_update_options($options_settings);
    ?>
	<script type="text/javascript">
		/* upload logo and images */
		//<![CDATA[
		jQuery(document).ready(function() {
			jQuery('.upload_button').click(function() {
				formfield = jQuery(this).attr('rel');
				tb_show('', 'media-upload.php?type=image&amp;post_id=0&amp;TB_iframe=true');
				return false;
			});

			/* send the uploaded image url to the field */
			window.send_to_editor = function(html) {
				imgurl = jQuery('img',html).attr('src'); // get the image url
				imgoutput = '<img src="' + imgurl + '" />'; //get the html to output for the image preview
				jQuery('#' + formfield).val(imgurl);
				jQuery('#' + formfield).siblings('.upload_image_preview').slideDown().html(imgoutput);
				tb_remove();
			}
		});
		//]]>
	</script>

    <div class="wrap">

        <div class="icon32" id="icon-tools"><br/></div>
        <h2><?php _e('General Settings','appthemes') ?></h2>

        <?php cp_admin_info_box(); ?>

        <form method="post" id="mainform" action="">

            <p class="submit btop"><input name="save" type="submit" value="Degisiklikleri Kaydet" /></p>

            <?php cp_admin_fields($options_settings); ?>

            <p class="submit bbot"><input name="save" type="submit" value="Degisiklikleri Kaydet" /></p>

            <input name="submitted" type="hidden" value="yes" />
            <input name="setTabIndex" type="hidden" value="0" id="setTabIndex" />

        </form>

    </div><!-- /wrap -->

<?php

}


function cp_emails() {
    global $options_emails;

    cp_update_options($options_emails);
    ?>

    <div class="wrap">

        <div class="icon32" id="icon-tools"><br/></div>
        <h2><?php _e('Email Settings','appthemes') ?></h2>

        <?php cp_admin_info_box(); ?>

        <form method="post" id="mainform" action="">

            <p class="submit btop"><input name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" /></p>

            <?php cp_admin_fields($options_emails); ?>

            <p class="submit bbot"><input name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" /></p>

            <input name="submitted" type="hidden" value="yes" />
            <input name="setTabIndex" type="hidden" value="0" id="setTabIndex" />

        </form>

    </div><!-- /wrap -->

<?php

}



function cp_pricing() {
    global $options_pricing;

    cp_update_options($options_pricing);
    ?>

    <script type="text/javascript">
    jQuery(function($) {

        // show/hide for the pricing tab
        var
            $select = $('select#cp_price_scheme'),
            old_val = $select.val();

            $('tr#cp_price_per_cat_row').hide();
            $('tr#cp_percent_per_ad_row').hide();

            if (old_val == 'category') {
                $('tr#cp_price_per_cat_row').show();
            } else if (old_val == 'percentage') {
                $('tr#cp_percent_per_ad_row').show();
            }

        $select.change(function() {
            var new_val = $(this).val();

            if (new_val == 'category') {
                $('tr#cp_price_per_cat_row').fadeIn('fast');
                $('tr#cp_percent_per_ad_row').hide();
            } else if (new_val == 'percentage') {
                $('tr#cp_percent_per_ad_row').fadeIn('fast');
                $('tr#cp_price_per_cat_row').hide();

            } else {
                $('tr#cp_price_per_cat_row').hide();
                $('tr#cp_percent_per_ad_row').hide();
            }

            old_val = new_val;
        });



        // show/hide for the membership tab
        var
            $select2 = $('select#cp_required_membership_type'),
            old_val2 = $select2.val();

            $('tr#cp_required_per_cat_row').hide();

            if (old_val2 == 'category') {
                $('tr#cp_required_per_cat_row').show();
            }

        $select2.change(function() {
            var new_val2 = $(this).val();

            if (new_val2 == 'category') {
                $('tr#cp_required_per_cat_row').fadeIn('fast');
            } else {
                $('tr#cp_required_per_cat_row').hide();
            }

            old_val2 = new_val2;
        });


    });
    </script>

    <div class="wrap">

        <div class="icon32" id="icon-options-general"><br/></div>
        <h2><?php _e('Pricing Options','appthemes') ?></h2>

        <?php cp_admin_info_box(); ?>

        <form method="post" id="mainform" action="">

            <p class="submit btop"><input name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" /></p>

            <?php cp_admin_fields($options_pricing); ?>

            <p class="submit bbot"><input name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" /></p>

            <input name="submitted" type="hidden" value="yes" />
            <input name="setTabIndex" type="hidden" value="0" id="setTabIndex" />

        </form>

    </div><!-- /wrap -->

<?php
}



// show the ad packages admin page
function cp_ad_packs() {
    global $app_abbr, $wpdb, $current_user;

    $current_user = wp_get_current_user();

    // check to prevent php "notice: undefined index" msg
    if(isset($_GET['action'])) $theswitch = $_GET['action']; else $theswitch ='';
	?>

	<script type="text/javascript">
	/* <![CDATA[ */
	/* initialize the form validation */
	jQuery(document).ready(function($) {
		$("#mainform").validate({errorClass: "invalid"});
	});
	/* ]]> */
    </script>

	<?php
	if(isset($_GET['type']) && $_GET['type'] == 'membership')
		$options_new_pack = $GLOBALS['options_new_membership_pack'];
	else
		$options_new_pack = $GLOBALS['options_new_ad_pack'];

    switch ( $theswitch ) {

    case 'addpack':
    ?>

        <div class="wrap">
            <div class="icon32" id="icon-themes"><br/></div>
            <h2><?php if($_GET['type'] == 'membership') _e('New Membership Pack','appthemes'); else _e('New Ad Pack','appthemes'); ?></h2>

            <?php cp_admin_info_box(); ?>

        <?php
        // check and make sure the form was submitted
        if ( isset($_POST['submitted']) ) {

			//setup optional variables for the package
			if(isset($_POST['pack_satisfies_required'])) $post_pack_satisfies_required = $_POST['pack_satisfies_required']; else $post_pack_satisfies_required = '';
			if(isset($_POST['pack_type'])) $post_pack_type = $post_pack_satisfies_required.$_POST['pack_type']; else $post_pack_type = '';
			if(isset($_POST['pack_membership_price'])) $post_pack_membership_price = $_POST['pack_membership_price']; else $post_pack_membership_price = '';

			$values = array(
				"pack_name" => appthemes_clean($_POST['pack_name']),
				"pack_desc" => appthemes_clean($_POST['pack_desc']),
				"pack_price" => appthemes_clean_price($_POST['pack_price'], 'float'),
				"pack_duration" => appthemes_clean($_POST['pack_duration']),
				"pack_status" => appthemes_clean($_POST['pack_status']),
				"pack_type" => appthemes_clean($post_pack_type),
				"pack_membership_price" => appthemes_clean_price($_POST['pack_membership_price'], 'float'),
				"pack_owner" => appthemes_clean($_POST['pack_owner']),
				"pack_modified" => gmdate('Y-m-d H:i:s'),
			);

			$results = $wpdb->insert( $wpdb->cp_ad_packs, $values);


            if ($results !== false) :
            ?>

                <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Creating your ad package.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
                <meta http-equiv="refresh" content="0; URL=?page=packages">

            <?php
            endif;

        } else {
        ?>

			<form method="post" id="mainform" action="">

				<?php cp_admin_fields($options_new_pack) ?>

				<p class="submit"><input class="btn button-primary" name="save" type="submit" value="<?php _e('Create New Ad Package','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
					<input name="cancel" type="button" onClick="location.href='?page=packages'" value="<?php _e('Cancel','appthemes') ?>" /></p>
				<input name="submitted" type="hidden" value="yes" />
				<input name="pack_owner" type="hidden" value="<?php echo $current_user->user_login ?>" />

			</form>

        <?php
        }
        ?>

        </div><!-- end wrap -->

    <?php
    break;

    case 'editpack':
    ?>

        <div class="wrap">
            <div class="icon32" id="icon-themes"><br/></div>
            <h2><?php _e('Edit Ad Package','appthemes') ?></h2>

            <?php cp_admin_info_box(); ?>

        <?php
        if ( isset($_POST['submitted']) && $_POST['submitted'] == 'yes' ) {

	    $values = array(
		    "pack_name" => appthemes_clean($_POST['pack_name']),
		    "pack_desc" => appthemes_clean($_POST['pack_desc']),
		    "pack_price" => appthemes_clean_price($_POST['pack_price'], 'float'),
		    "pack_duration" => appthemes_clean($_POST['pack_duration']),
		    "pack_status" => appthemes_clean($_POST['pack_status']),
		    "pack_type" => appthemes_clean($_POST['pack_satisfies_required'].$_POST['pack_type']),
		    "pack_membership_price" => appthemes_clean_price($_POST['pack_membership_price'], 'float'),
		    "pack_owner" => appthemes_clean($_POST['pack_owner']),
		    "pack_modified" => gmdate('Y-m-d H:i:s'),
	    );

	    $where = array(
		    "pack_id" => $_GET['id']
	    );

            $wpdb->update( $wpdb->cp_ad_packs, $values, $where);

            ?>

            <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Saving your changes.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
            <meta http-equiv="refresh" content="0; URL=?page=packages">

        <?php
        } else {
        ?>


            <form method="post" id="mainform" action="">

            <?php
		    cp_admin_db_fields($options_new_pack, 'cp_ad_packs', 'pack_id');
	    ?>

                <p class="submit">
                    <input class="btn button-primary" name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
                    <input name="cancel" type="button" onClick="location.href='?page=packages'" value="<?php _e('Cancel','appthemes') ?>" />
                    <input name="submitted" type="hidden" value="yes" />
                    <input name="pack_owner" type="hidden" value="<?php echo $current_user->user_login ?>" />
                </p>

            </form>

        <?php } ?>

        </div><!-- end wrap -->

    <?php
    break;

    case 'delete':

        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->cp_ad_packs WHERE pack_id = %s", $_GET['id'] ) );
    ?>

        <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Deleting ad package.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
        <meta http-equiv="refresh" content="0; URL=?page=packages">

    <?php
    break;

    default:

        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->cp_ad_packs ORDER BY pack_id desc" ) );

    ?>

        <div class="wrap">
        <div class="icon32" id="icon-themes"><br/></div>
        <h2><?php _e('Ad Packs','appthemes') ?>&nbsp;<a class="button add-new-h2" href="?page=packages&amp;action=addpack&amp;type=ad"><?php _e('Add New','appthemes') ?></a></h2>

        <?php cp_admin_info_box(); ?>

        <?php if ( get_option( $app_abbr.'_price_scheme') != 'single' ) { ?>
	        <div class="error"><p><?php printf(__('Ad Packs are disabled. Change the <a href="%1$s">pricing model</a> to enable Ad Packs.', 'appthemes'), 'admin.php?page=pricing#tab1' ); ?></p></div>
        <?php } ?>

        <p class="admin-msg"><?php _e('Ad Packs allow you to create bundled listing options for your customers to choose from. For example, instead of only offering a set price for xx days (30 days for $5), you could also offer discounts for longer terms (60 days for $7). These only work if you are selling ads and using the "Fixed Price Per Ad" price model.','appthemes') ?></p>

        <table id="tblspacer" class="widefat fixed">

            <thead>
                <tr>
					<th scope="col" style="width:35px;">&nbsp;</th>
                    <th scope="col"><?php _e('Name','appthemes') ?></th>
                    <th scope="col"><?php _e('Description','appthemes') ?></th>
                    <th scope="col"><?php _e('Price Per Ad','appthemes') ?></th>
                    <th scope="col"><?php _e('Duration','appthemes') ?></th>
                    <th scope="col" style="width:150px;"><?php _e('Modified','appthemes') ?></th>
                    <th scope="col" style="width:75px;"><?php _e('Status','appthemes') ?></th>
                    <th scope="col" style="text-align:center;width:100px;"><?php _e('Actions','appthemes') ?></th>
                </tr>
            </thead>

            <?php
            if ( $results ) {
                $rowclass = '';
                $i=1;
            ?>

              <tbody id="list">

            <?php
                foreach ( $results as $result ) {
	                if ( $result->pack_status == 'active' || $result->pack_status == 'inactive' ) :
                	$rowclass = 'even' == $rowclass ? 'alt' : 'even';
              ?>

                <tr class="<?php echo $rowclass ?>">
                    <td style="padding-left:10px;"><?php echo $i++; ?>.</td>
                    <td><a href="?page=packages&amp;action=editpack&amp;type=ad&amp;id=<?php echo $result->pack_id ?>"><strong><?php echo stripslashes($result->pack_name); ?></strong></a></td>
                    <td><?php echo $result->pack_desc ?></td>
                    <td><?php echo cp_pos_price( $result->pack_price ) ?></td>
                    <td><?php echo $result->pack_duration ?>&nbsp;<?php _e('days','appthemes') ?></td>
                    <td><?php echo mysql2date( get_option('date_format') .' '. get_option('time_format'), $result->pack_modified ) ?> <?php _e('by','appthemes') ?> <?php echo $result->pack_owner; ?></td>
                    <td><?php echo ucwords( $result->pack_status ) ?></td>
                    <td style="text-align:center">
                        <a href="?page=packages&amp;action=editpack&amp;type=ad&amp;id=<?php echo $result->pack_id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/edit.png" alt="<?php echo  _e('Edit ad package','appthemes') ?>" title="<?php echo _e('Edit ad package','appthemes') ?>" /></a>&nbsp;&nbsp;&nbsp;
                        <a onclick="return confirmBeforeDelete();" href="?page=packages&amp;action=delete&amp;id=<?php echo $result->pack_id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/cross.png" alt="<?php echo _e('Delete ad package','appthemes') ?>" title="<?php echo _e('Delete ad package','appthemes') ?>" /></a>
                    </td>
                </tr>

              <?php
				endif; //end if('active' || 'inactive')

                } // end for each
				unset($i);
              ?>

              </tbody>

            <?php

            } else {

            ?>

                <tr>
                    <td colspan="7"><?php _e('No ad packs found.','appthemes') ?></td>
                </tr>

            <?php
            } // end $results
            ?>

            </table>


        </div><!-- end wrap for ad packs -->

        <div id="membership-packs" class="wrap">
        <div class="icon32" id="icon-themes"><br/></div>
        <h2><?php _e('Membership Packs','appthemes') ?>&nbsp;<a class="button add-new-h2" href="?page=packages&amp;action=addpack&amp;type=membership"><?php _e('Add New','appthemes') ?></a></h2>

        <?php cp_admin_info_box(); ?>

        <p class="admin-msg"><?php printf(__('Membership Packs allow you to setup subscription-based pricing packages. This enables your customers to post unlimited ads for a set period of time or until the membership becomes inactive. These memberships affect pricing regardless of the ad packs or pricing model you have set as long as you have the <a href="%1$s">enable membership packs</a> option set to yes.','appthemes'), 'admin.php?page=pricing#tab2'); ?></p>

        <table id="tblspacer" class="widefat fixed">

            <thead>
                <tr>
					<th scope="col" style="width:35px;">&nbsp;</th>
                    <th scope="col"><?php _e('Name','appthemes') ?></th>
                    <th scope="col"><?php _e('Description','appthemes') ?></th>
                    <th scope="col"><?php _e('Price Modifier','appthemes') ?></th>
                    <th scope="col"><?php _e('Terms','appthemes') ?></th>
                    <th scope="col" style="width:150px;"><?php _e('Modified','appthemes') ?></th>
                    <th scope="col" style="width:75px;"><?php _e('Status','appthemes') ?></th>
                    <th scope="col" style="text-align:center;width:100px;"><?php _e('Actions','appthemes') ?></th>
                </tr>
            </thead>

            <?php
            if ( $results ) {
                $rowclass = '';
                $i=1;
            ?>

              <tbody id="list">

            <?php
                foreach ( $results as $result ) {
	                if ( $result->pack_status == 'active_membership' || $result->pack_status == 'inactive_membership' ) :
	                $rowclass = 'even' == $rowclass ? 'alt' : 'even';
            ?>

                <tr class="<?php echo $rowclass ?>">
                    <td style="padding-left:10px;"><?php echo $i++ ?>.</td>
                    <td><a href="?page=packages&amp;action=editpack&amp;type=membership&amp;id=<?php echo $result->pack_id; ?>"><strong><?php echo stripslashes($result->pack_name); ?></strong></a></td>
                    <td><?php echo $result->pack_desc; ?></td>
                    <td>
						<?php switch ($result->pack_type) {
							case 'percentage':
								echo preg_replace('/.00$/', '', $result->pack_price).'% '.__('of price','appthemes'); //remove decimal when decimal is .00
								break;
							case 'discount':
								echo cp_pos_price($result->pack_price).__('\'s less per ad','appthemes');
								break;
							case 'required_static':
								if ( (float)$result->pack_price == 0 ) echo __('Free','appthemes');
								else echo cp_pos_price( $result->pack_price ).__(' per ad','appthemes');
								echo ' ('.__('required to post','appthemes').')';
								break;
							case 'required_discount':
								echo cp_pos_price( $result->pack_price ).__('\'s less per ad','appthemes');
								echo ' ('.__('required to post','appthemes').')';
								break;
							case 'required_percentage':
								echo preg_replace( '/.00$/', '', $result->pack_price ).'% '.__('of price','appthemes'); //remove decimal when decimal is .00
								echo ' ('.__('required to post','appthemes').')';
								break;
							default: //likely 'static'
								if ( (float)$result->pack_price == 0 ) echo __('Free','appthemes');
								else echo cp_pos_price( $result->pack_price ).__(' per ad','appthemes');
						}
						?>
                    </td>
                    <td><?php echo cp_pos_price( $result->pack_membership_price ).' / '.$result->pack_duration.' '.__('days','appthemes'); ?></td>
                    <td><?php echo mysql2date( get_option('date_format') .' '. get_option('time_format'), $result->pack_modified ) ?> <?php _e('by','appthemes') ?> <?php echo $result->pack_owner; ?></td>
                    <td><?php echo ucwords(preg_replace('/\_(.*)/', '', $result->pack_status)) ?></td>
                    <td style="text-align:center">
                        <a href="?page=packages&amp;action=editpack&amp;type=membership&amp;id=<?php echo $result->pack_id ?>"><img src="<?php echo bloginfo('template_directory'); ?>/images/edit.png" alt="<?php echo  _e('Edit ad package','appthemes'); ?>" title="<?php echo _e('Edit ad package','appthemes') ?>" /></a>&nbsp;&nbsp;&nbsp;
                        <a onclick="return confirmBeforeDelete();" href="?page=packages&amp;action=delete&amp;id=<?php echo $result->pack_id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/cross.png" alt="<?php echo _e('Delete ad package','appthemes'); ?>" title="<?php echo _e('Delete ad package','appthemes') ?>" /></a>
                    </td>
                </tr>

              <?php
				endif; //end if('active_membership' || 'inactive_membership')

                } // end for each
				unset($i);
              ?>

              </tbody>

            <?php

            } else {

            ?>

                <tr>
                    <td colspan="7"><?php _e('No ad packs found.','appthemes') ?></td>
                </tr>

            <?php
            } // end $results
            ?>

            </table>


        </div><!-- end wrap for membership packs-->

    <?php
    } // end switch
    ?>
    <script type="text/javascript">
        /* <![CDATA[ */
            function confirmBeforeDelete() { return confirm("<?php _e('Are you sure you want to delete this ad package?', 'appthemes'); ?>"); }
        /* ]]> */
    </script>

<?php

}


// show the ad packages admin page
function cp_coupons() {
    global $options_new_coupon, $wpdb, $current_user, $app_version;

    $current_user = wp_get_current_user();

    // check to prevent php "notice: undefined index" msg
    if(isset($_GET['action'])) $theswitch = $_GET['action']; else $theswitch ='';
	?>

	<script type="text/javascript">
		//<![CDATA[
		/* initialize the datepicker feature */
		jQuery(document).ready(function($) {
			/* initialize the form validation */
			$("#mainform").validate({errorClass: "invalid"});

			$('form#mainform .datepicker').datepicker({
				showOn: 'button',
				dateFormat: 'yy-mm-dd',
				minDate: 0,
				buttonImageOnly: true,
				buttonText: '',
				buttonImage: '../wp-includes/images/blank.gif' // calling the real calendar image in the admin-style.css. need a blank placeholder image b/c of IE.
			});
		});
		//]]>
	</script>

	<?php
    switch ( $theswitch ) {

    case 'addcoupon':
    ?>

        <div class="wrap">
            <div class="icon32" id="icon-edit-pages"><br/></div>
            <h2><?php _e('New Coupon','appthemes') ?></h2>
            <?php
            //if your database is not at least version 3.1, you must upgrade first.
            if ( get_option('cp_version') != $app_version ) {
                echo '<div class="error">' . __('Error: Your ClassiPress database is not updated to match your version of ClassiPress.','appthemes') . '</div>';
                echo __('Product Version', 'appthemes') . ': <strong>' . get_option('cp_version') . '</strong> ';
                if ( get_option('cp_version') != $app_version )
                        echo __('(You upgraded to version ') . $app_version . '. <a href="admin.php?page=admin-options.php&upgrade=yes">Click here to finish your upgrade.</a>)';
                die();
            }
            ?>

            <?php cp_admin_info_box(); ?>

        <?php
        // check and make sure the form was submitted
        if ( isset($_POST['submitted']) ) {

		//echo $_POST['coupon_expire_date'] . '<-- expire date';

	    // @todo Switch to
            // adding $wpdb->prepare causes the query to be empty for some reason
            $insert = "INSERT INTO $wpdb->cp_coupons" .
            " (coupon_code, coupon_desc, coupon_discount, coupon_discount_type, coupon_start_date, coupon_expire_date, coupon_status, coupon_max_use_count, coupon_owner, coupon_created, coupon_modified) " .
            "VALUES ('" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_code'])) . "','" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_desc'])) . "','" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_discount'])) . "','" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_discount_type'])) . "','" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_start_date'])) . "','" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_expire_date'])) . "','" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_status'])) . "','" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_max_use_count'])) . "','" .
                    $wpdb->escape(appthemes_clean($_POST['coupon_owner'])) . "','" .
                    gmdate('Y-m-d H:i:s') . "','" .
                    gmdate('Y-m-d H:i:s') .
                    "')";

            $results = $wpdb->query( $insert );


            if ( $results ) :
            ?>

                <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Creating your coupon.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
                <meta http-equiv="refresh" content="0; URL=?page=coupons">

            <?php
            endif;


        } else {
        ?>

                <form method="post" id="mainform" action="">

                    <?php cp_admin_fields($options_new_coupon) ?>

                    <p class="submit"><input class="btn button-primary" name="save" type="submit" value="<?php _e('Create New Coupon','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
                    <input name="cancel" type="button" onClick="location.href='?page=coupons'" value="<?php _e('Cancel','appthemes') ?>" /></p>
                    <input name="submitted" type="hidden" value="yes" />
                    <input name="coupon_owner" type="hidden" value="<?php echo $current_user->user_login ?>" />

                </form>

        <?php
        }
        ?>

        </div><!-- end wrap -->

    <?php
    break;

    case 'editcoupon':
    ?>

        <div class="wrap">
            <div class="icon32" id="icon-themes"><br/></div>
            <h2><?php _e('Edit Coupon','appthemes') ?></h2>

            <?php cp_admin_info_box(); ?>

        <?php
        if ( isset($_POST['submitted']) && $_POST['submitted'] == 'yes' ) {

             // adding $wpdb->prepare causes the query to be empty for some reason
            $update = "UPDATE $wpdb->cp_coupons SET" .
                    " coupon_code = '" . $wpdb->escape(appthemes_clean($_POST['coupon_code'])) . "'," .
                    " coupon_desc = '" . $wpdb->escape(appthemes_clean($_POST['coupon_desc'])) . "'," .
                    " coupon_discount = '" . $wpdb->escape(appthemes_clean($_POST['coupon_discount'])) . "'," .
                    " coupon_discount_type = '" . $wpdb->escape(appthemes_clean($_POST['coupon_discount_type'])) . "'," .
                    " coupon_start_date = '" . $wpdb->escape(appthemes_clean($_POST['coupon_start_date'])) . "'," .
                    " coupon_expire_date = '" . $wpdb->escape(appthemes_clean($_POST['coupon_expire_date'])) . "'," .
                    " coupon_status = '" . $wpdb->escape(appthemes_clean($_POST['coupon_status'])) . "'," .
                    " coupon_max_use_count = '" . $wpdb->escape(appthemes_clean($_POST['coupon_max_use_count'])) . "'," .
                    " coupon_owner = '" . $wpdb->escape(appthemes_clean($_POST['coupon_owner'])) . "'," .
                    " coupon_modified = '" . gmdate('Y-m-d H:i:s') . "'" .
                    " WHERE coupon_id ='" . $wpdb->escape($_GET['id']) ."'";

            $results = $wpdb->get_row( $update );
            ?>

            <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Saving your changes.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
            <meta http-equiv="refresh" content="0; URL=?page=coupons">

        <?php
        } else {
        ?>


            <form method="post" id="mainform" action="">

            <?php cp_admin_db_fields($options_new_coupon, 'cp_coupons', 'coupon_id') ?>

                <p class="submit">
                    <input class="btn button-primary" name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
                    <input name="cancel" type="button" onClick="location.href='?page=coupons'" value="<?php _e('Cancel','appthemes') ?>" />
                    <input name="submitted" type="hidden" value="yes" />
                    <input name="coupon_owner" type="hidden" value="<?php echo $current_user->user_login ?>" />
                </p>

            </form>

        <?php } ?>

        </div><!-- end wrap -->

    <?php
    break;

    case 'delete':

        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->cp_coupons WHERE coupon_id = %s", $_GET['id'] ) );
    ?>

        <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Deleting coupon.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
        <meta http-equiv="refresh" content="0; URL=?page=coupons">

    <?php
    break;

    default:

		$results = cp_get_coupons();

    ?>

        <div class="wrap">
        <div class="icon32" id="icon-edit-pages"><br/></div>
        <h2><?php _e('Coupons','appthemes') ?>&nbsp;<a class="button add-new-h2" href="?page=coupons&amp;action=addcoupon"><?php _e('Add New','appthemes') ?></a></h2>

        <?php cp_admin_info_box(); ?>


        <p class="admin-msg"><?php _e('Create coupons to offer special discounts to your customers.','appthemes') ?></p>

        <table id="tblspacer" class="widefat fixed">

            <thead>
                <tr>
                    <th scope="col" style="width:35px;">&nbsp;</th>
                    <th scope="col"><?php _e('Code','appthemes') ?></th>
                    <th scope="col"><?php _e('Description','appthemes') ?></th>
                    <th scope="col"><?php _e('Discount','appthemes') ?></th>
					<th scope="col"><?php _e('Usage','appthemes') ?></th>
					<th scope="col"><?php _e('Valid','appthemes') ?></th>
                    <th scope="col"><?php _e('Expires','appthemes') ?></th>
                    <th scope="col" style="width:150px;"><?php _e('Modified','appthemes') ?></th>
                    <th scope="col" style="width:75px;"><?php _e('Status','appthemes') ?></th>
                    <th scope="col" style="text-align:center;width:100px;"><?php _e('Actions','appthemes') ?></th>
                </tr>
            </thead>

            <?php
            if ( $results ) {
                $rowclass = '';
                $i=1;
            ?>

              <tbody id="list">

            <?php
                foreach ( $results as $result ) {

                $rowclass = 'even' == $rowclass ? 'alt' : 'even';
              ?>

                <tr class="<?php echo $rowclass ?>">
                    <td style="padding-left:10px;"><?php echo $i ?>.</td>
                    <td><a href="?page=coupons&amp;action=editcoupon&amp;id=<?php echo $result->coupon_id ?>"><strong><?php echo $result->coupon_code ?></strong></a></td>
                    <td><?php echo $result->coupon_desc ?></td>
                    <td><?php if (($result->coupon_discount_type) == '%') echo number_format($result->coupon_discount,0) . '%'; else echo cp_pos_price($result->coupon_discount); ?></td>
					<td><?php echo $result->coupon_use_count ?><?php if (($result->coupon_max_use_count) <> 0) echo '/' . $result->coupon_max_use_count ?></td>
					<td><?php echo mysql2date(get_option('date_format') .' '. get_option('time_format'), $result->coupon_start_date) ?></td>
					<td><?php echo mysql2date(get_option('date_format') .' '. get_option('time_format'), $result->coupon_expire_date) ?></td>
                    <td><?php echo mysql2date(get_option('date_format') .' '. get_option('time_format'), $result->coupon_modified) ?> <br /><?php _e('by','appthemes') ?> <?php echo $result->coupon_owner; ?></td>
                    <td><?php echo ucfirst($result->coupon_status) ?></td>
                    <td style="text-align:center">
                        <a href="?page=coupons&amp;action=editcoupon&amp;id=<?php echo $result->coupon_id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/edit.png" alt="<?php echo  _e('Edit coupon','appthemes') ?>" title="<?php echo _e('Edit coupon','appthemes') ?>" /></a>&nbsp;&nbsp;&nbsp;
                        <a onclick="return confirmBeforeDelete();" href="?page=coupons&amp;action=delete&amp;id=<?php echo $result->coupon_id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/cross.png" alt="<?php echo _e('Delete coupon','appthemes') ?>" title="<?php echo _e('Delete coupon','appthemes') ?>" /></a>
                    </td>
                </tr>

              <?php

                $i++;

                } // end for each
              ?>

              </tbody>

            <?php

            } else {

            ?>

                <tr>
                    <td>&nbsp;</td><td colspan="8"><?php _e('No coupons found.','appthemes') ?></td>
                </tr>

            <?php
            } // end $results
            ?>

            </table>


        </div><!-- end wrap -->

    <?php
    } // end switch
    ?>
    <script type="text/javascript">
        /* <![CDATA[ */
            function confirmBeforeDelete() { return confirm("<?php _e('Are you sure you want to delete this coupon?', 'appthemes'); ?>"); }
        /* ]]> */
    </script>

<?php

}



function cp_gateways() {
    global $options_gateways;

    cp_update_options($options_gateways);
    ?>

    <div class="wrap">

        <div class="icon32" id="icon-options-general"><br/></div>
        <h2><?php _e('Payment Gateways','appthemes') ?></h2>

        <?php cp_admin_info_box(); ?>

        <form method="post" id="mainform" action="">

            <p class="submit btop"><input name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" /></p>

            <?php cp_admin_fields($options_gateways); ?>

            <p class="submit bbot"><input name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" /></p>

            <input name="submitted" type="hidden" value="yes" />
            <input name="setTabIndex" type="hidden" value="0" id="setTabIndex" />

        </form>

    </div>

<?php
}


function cp_form_layouts() {
    global $options_new_form, $wpdb, $current_user;

    $current_user = wp_get_current_user();

    // check to prevent php "notice: undefined index" msg when php strict warnings is on
    if ( isset($_GET['action']) ) $theswitch = $_GET['action']; else $theswitch ='';
	?>

	<script type="text/javascript">
	/* <![CDATA[ */
	/* initialize the form validation */
	jQuery(document).ready(function($) {
		$("#mainform").validate({errorClass: "invalid"});
	});
	/* ]]> */
    </script>

	<?php
    switch ( $theswitch ) {

    case 'addform':
    ?>

        <div class="wrap">
            <div class="icon32" id="icon-themes"><br/></div>
            <h2><?php _e('New Form Layout','appthemes') ?></h2>

            <?php cp_admin_info_box(); ?>

        <?php
        // check and make sure the form was submitted and the hidden fcheck id matches the cookie fcheck id
        if ( isset($_POST['submitted']) ) {

            if ( !isset($_POST['post_category']) )
                wp_die( '<p style="color:red;">' .__("Error: Please select at least one category. <a href='#' onclick='history.go(-1);return false;'>Go back</a>",'appthemes') .'</p>' );

	    // @todo Change to Insert
            $insert = $wpdb->prepare( "INSERT INTO $wpdb->cp_ad_forms" .
                    " (form_name, form_label, form_desc, form_cats, form_status, form_owner, form_created) " .
                    "VALUES ( %s, %s, %s, %s, %s, %s, %s)",
                    appthemes_clean(cp_make_custom_name($_POST['form_label'])),
                    appthemes_clean($_POST['form_label']),
                    appthemes_clean($_POST['form_desc']),
                    serialize($_POST['post_category']),
                    appthemes_clean($_POST['form_status']),
                    appthemes_clean($_POST['form_owner']),
                    gmdate('Y-m-d H:i:s')
                );

            $results = $wpdb->query( $insert );


            if ( $results ) {
                         ?>

                <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Creating your form.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
                <meta http-equiv="refresh" content="0; URL=?page=layouts">

            <?php
            } // end $results

        } else {
        ?>

            <form method="post" id="mainform" action="">

                <?php echo cp_admin_fields($options_new_form); ?>

                <p class="submit"><input class="btn button-primary" name="save" type="submit" value="<?php _e('Create New Form','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
                <input name="cancel" type="button" onClick="location.href='?page=layouts'" value="<?php _e('Cancel','appthemes') ?>" /></p>
                <input name="submitted" type="hidden" value="yes" />
                <input name="form_owner" type="hidden" value="<?php echo $current_user->user_login ?>" />

            </form>

        <?php
        } // end isset $_POST
        ?>

        </div><!-- end wrap -->

    <?php
    break;


    case 'editform':
    ?>

        <div class="wrap">
        <div class="icon32" id="icon-themes"><br/></div>
        <h2><?php _e('Edit Form Properties','appthemes') ?></h2>

        <?php
        if ( isset($_POST['submitted']) && $_POST['submitted'] == 'yes' ) {

            if ( !isset($_POST['post_category']) )
                wp_die( '<p style="color:red;">' .__("Error: Please select at least one category. <a href='#' onclick='history.go(-1);return false;'>Go back</a>",'appthemes') .'</p>' );


	    // @todo Change to Update
            $update = $wpdb->prepare( "UPDATE $wpdb->cp_ad_forms SET" .
                            " form_label    = %s," .
                            " form_desc     = %s," .
                            " form_cats     = %s," .
                            " form_status   = %s," .
                            " form_owner    = %s," .
                            " form_modified = %s" .
                            " WHERE id      = %s",
                            appthemes_clean($_POST['form_label']),
                            appthemes_clean($_POST['form_desc']),
                            serialize($_POST['post_category']),
                            appthemes_clean($_POST['form_status']),
                            $_POST['form_owner'],
                            gmdate( 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) ),
                            $_GET['id']);

            $results = $wpdb->get_row( $update );

            ?>

            <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Saving your changes.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
            <meta http-equiv="refresh" content="0; URL=?page=layouts">

        <?php
        } else {
        ?>

            <form method="post" id="mainform" action="">

            <?php echo cp_admin_db_fields($options_new_form, 'cp_ad_forms', 'id'); ?>

                <p class="submit"><input class="btn button-primary" name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
                <input name="cancel" type="button" onClick="location.href='?page=layouts'" value="<?php _e('Cancel','appthemes') ?>" /></p>
                <input name="submitted" type="hidden" value="yes" />
                <input name="form_owner" type="hidden" value="<?php echo $current_user->user_login ?>" />

            </form>

        <?php
        } // end isset $_POST
        ?>

        </div><!-- end wrap -->

    <?php
    break;


    /**
    * Form Builder Page
    * Where fields are added to form layouts
    */

    case 'formbuilder':
    ?>

        <div class="wrap">
        <div class="icon32" id="icon-themes"><br/></div>
        <h2><?php _e('Edit Form Layout','appthemes') ?></h2>

        <?php cp_admin_info_box(); ?>

        <?php
        // add fields to page layout on left side
        if ( isset($_POST['field_id']) ) {

            // take selected checkbox array and loop through ids
            foreach ( $_POST['field_id'] as $value ) {

		// @todo Change to Insert
                $insert = $wpdb->prepare( "INSERT INTO $wpdb->cp_ad_meta" .
                        " (form_id, field_id) VALUES ( %s, %s)",
                        appthemes_clean($_POST['form_id']),
                        appthemes_clean($value)
                );

                $results = $wpdb->query( $insert );

            } // end foreach

        } // end $_POST



        // update form layout positions and required fields on left side.
        if ( isset($_POST['formlayout']) ) {

            // loop through the post array and update the required checkbox and field position
            foreach ( $_POST as $key => $value ) :

                // since there's some $_POST values we don't want to process, only give us the
                // numeric ones which means it contains a meta_id and we want to update it
                if ( is_numeric($key) ) {

                    // quick hack to prevent php "notice: undefined index:" msg when php strict warnings is on
                    if ( !isset($value['field_req']) ) $value['field_req'] = '';
                    if ( !isset($value['field_search']) ) $value['field_search'] = '';

                    $update = "UPDATE $wpdb->cp_ad_meta SET "
                            . "field_req = '" . $wpdb->escape(appthemes_clean($value['field_req'])) . "', "
							. "field_search = '" . $wpdb->escape(appthemes_clean($value['field_search'])) . "' "
                            . "WHERE meta_id ='" . $wpdb->escape($key) ."'";

                    $wpdb->query( $update );

                } // end if_numeric

            endforeach; // end for each

            echo '<p class="info">'. __('Your changes have been saved.', 'appthemes') .'</p>';

        } // end isset $_POST


        // check to prevent php "notice: undefined index" msg when php strict warnings is on
        if ( isset($_GET['del_id']) ) $theswitch = $_GET['del_id']; else $theswitch ='';


        // Remove items from form layout
        if ( $theswitch ) $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->cp_ad_meta WHERE meta_id = %s", $_GET['del_id'] ) );


	// @todo Change to Update
        //update the forms modified date
        $update = $wpdb->prepare( "UPDATE $wpdb->cp_ad_forms SET" .
                " form_modified = %s WHERE id = %s",
                gmdate( 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) ),
                $_GET['id']
        );

        $results = $wpdb->get_row( $update );

        ?>


        <table>
            <tr style="vertical-align:top;">
                <td style="width:800px;padding:0 20px 0 0;">


                <h3><?php _e('Form Name','appthemes') ?> - <?php echo ucfirst(urldecode($_GET['title'])) ?>&nbsp;&nbsp;&nbsp;&nbsp;<span id="loading"></span></h3>

                <form method="post" id="mainform" action="">

                    <table class="widefat">
                        <thead>
                            <tr>
                                <th scope="col" colspan="2"><?php _e('Form Preview','appthemes') ?></th>
								<th scope="col" style="width:75px;text-align:center;" title="<?php _e('Show field in the category refine search sidebar','appthemes') ?>"><?php _e('Advanced Search','appthemes') ?></th>
                                <th scope="col" style="width:75px;text-align:center;"><?php _e('Required','appthemes') ?></th>
                                <th scope="col" style="width:75px;text-align:center;"><?php _e('Remove','appthemes') ?></th>
                            </tr>
                        </thead>



                        <tbody class="sortable">

                        <?php

                            // If this is the first time this form is being customized then auto
                            // create the core fields and put in cp_meta db table
                            echo cp_add_core_fields( $_GET['id'] );


                            // Then go back and select all the fields assigned to this
                            // table which now includes the added core fields.
                            $sql = $wpdb->prepare( "SELECT f.field_label, f.field_name, f.field_type, f.field_values, f.field_perm, m.meta_id, m.field_pos, m.field_search, m.field_req, m.form_id "
                                 . "FROM $wpdb->cp_ad_fields f "
                                 . "INNER JOIN $wpdb->cp_ad_meta m "
                                 . "ON f.field_id = m.field_id "
                                 . "WHERE m.form_id = %s "
                                 . "ORDER BY m.field_pos asc",
                                 $_GET['id']
                            );

                            $results = $wpdb->get_results( $sql );

                            if ( $results ) {

                                echo cp_admin_formbuilder( $results );

                            } else {

                        ?>

                        <tr>
                            <td colspan="5" style="text-align: center;"><p><br/><?php _e('No fields have been added to this form layout yet.','appthemes') ?><br/><br/></p></td>
                        </tr>

                        <?php
                            } // end $results
                            ?>

                        </tbody>

                    </table>

                    <p class="submit">
                        <input class="btn button-primary" name="save" type="submit" value="<?php _e('Save Changes','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
                        <input name="cancel" type="button" onClick="location.href='?page=layouts'" value="<?php _e('Cancel','appthemes') ?>" />
                        <input name="formlayout" type="hidden" value="yes" />
                        <input name="form_owner" type="hidden" value="<?php $current_user->user_login ?>" />
                    </p>
                </form>

                </td>
                <td>

                <h3><?php _e('Available Fields','appthemes') ?></h3>

                <form method="post" id="mainform" action="">


                <div class="fields-panel">

                    <table class="widefat">
                        <thead>
                            <tr>
                                <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
                                <th scope="col"><?php _e('Field Name','appthemes') ?></th>
                                <th scope="col"><?php _e('Type','appthemes') ?></th>
                            </tr>
                        </thead>


                        <tbody>

                        <?php
                        // Select all available fields not currently on the form layout.
                        // Also exclude any core fields since they cannot be removed from the layout.
                        $sql = $wpdb->prepare( "SELECT f.field_id,f.field_label,f.field_type "
                             . "FROM $wpdb->cp_ad_fields f "
                             . "WHERE f.field_id "
                             . "NOT IN (SELECT m.field_id "
                             . "FROM $wpdb->cp_ad_meta m "
                             . "WHERE m.form_id =  %s) "
                             . "AND f.field_perm <> '1'",
                             $_GET['id']);

                        $results = $wpdb->get_results( $sql );

                        if ( $results ) {

                            foreach ( $results as $result ) {
                        ?>

                        <tr class="even">
                            <th class="check-column" scope="row"><input type="checkbox" value="<?php echo $result->field_id; ?>" name="field_id[]"/></th>
                            <td><?php echo esc_html( translate( $result->field_label, 'appthemes' ) ); ?></td>
                            <td><?php echo $result->field_type; ?></td>
                        </tr>

                        <?php
                            } // end foreach

                        } else {
                        ?>

                        <tr>
                            <td colspan="4" style="text-align: center;"><p><br /><?php _e('No fields are available.','appthemes') ?><br /><br /></p></td>
                        </tr>

                        <?php
                        } // end $results
                        ?>

                        </tbody>

                    </table>

                </div>

                    <p class="submit"><input class="btn button-primary" name="save" type="submit" value="<?php _e('Add Fields to Form Layout','appthemes') ?>" /></p>
                        <input name="form_id" type="hidden" value="<?php echo $_GET['id']; ?>" />
                        <input name="submitted" type="hidden" value="yes" />


                </form>

                </td>
            </tr>
        </table>

    </div><!-- /wrap -->

    <?php

    break;



    case 'delete':

        // delete the form based on the form id
        cp_delete_form($_GET['id']);
        ?>
        <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Deleting form layout.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
        <meta http-equiv="refresh" content="0; URL=?page=layouts">

    <?php
    break;

    default:

        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->cp_ad_forms ORDER BY id desc" ) );

    ?>

        <div class="wrap">
        <div class="icon32" id="icon-themes"><br /></div>
        <h2><?php _e('Form Layouts','appthemes'); ?>&nbsp;<a class="button add-new-h2" href="?page=layouts&amp;action=addform"><?php _e('Add New','appthemes') ?></a></h2>

        <?php cp_admin_info_box(); ?>

        <p class="admin-msg"><?php _e('Form layouts allow you to create your own custom ad submission forms. Each form is essentially a container for your fields and can be applied to one or all of your categories. If you do not create any form layouts, the default one will be used. To change the default form, create a new form layout and apply it to all categories.','appthemes') ?></p>

        <table id="tblspacer" class="widefat fixed">

            <thead>
                <tr>
                    <th scope="col" style="width:35px;">&nbsp;</th>
                    <th scope="col"><?php _e('Name','appthemes') ?></th>
                    <th scope="col"><?php _e('Description','appthemes') ?></th>
                    <th scope="col"><?php _e('Categories','appthemes') ?></th>
                    <th scope="col" style="width:150px;"><?php _e('Modified','appthemes') ?></th>
                    <th scope="col" style="width:75px;"><?php _e('Status','appthemes') ?></th>
                    <th scope="col" style="text-align:center;width:100px;"><?php _e('Actions','appthemes') ?></th>
                </tr>
            </thead>

            <?php
            if ( $results ) {
              $rowclass = '';
              $i=1;
            ?>

              <tbody id="list">

            <?php
                foreach ( $results as $result ) {

                $rowclass = 'even' == $rowclass ? 'alt' : 'even';
              ?>

                <tr class="<?php echo $rowclass ?>">
                    <td style="padding-left:10px;"><?php echo $i ?>.</td>
                    <td><a href="?page=layouts&amp;action=editform&amp;id=<?php echo $result->id ?>"><strong><?php echo $result->form_label ?></strong></a></td>
                    <td><?php echo $result->form_desc ?></td>
                    <td><?php echo cp_match_cats( unserialize($result->form_cats) ) ?></td>
                    <td><?php echo mysql2date( get_option('date_format') .' '. get_option('time_format'), $result->form_modified ) ?> <?php _e('by','appthemes') ?> <?php echo $result->form_owner; ?></td>
                    <td><?php echo ucfirst( $result->form_status ) ?></td>
                    <td style="text-align:center"><a href="?page=layouts&amp;action=formbuilder&amp;id=<?php echo $result->id ?>&amp;title=<?php echo urlencode($result->form_label) ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/layout_add.png" alt="<?php echo _e('Edit form layout','appthemes') ?>" title="<?php echo _e('Edit form layout','appthemes') ?>" /></a>&nbsp;&nbsp;&nbsp;
                        <a href="?page=layouts&amp;action=editform&amp;id=<?php echo $result->id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/edit.png" alt="<?php echo  _e('Edit form properties','appthemes') ?>" title="<?php echo _e('Edit form properties','appthemes') ?>" /></a>&nbsp;&nbsp;&nbsp;
                        <a onclick="return confirmBeforeDelete();" href="?page=layouts&amp;action=delete&amp;id=<?php echo $result->id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/cross.png" alt="<?php echo _e('Delete form layout','appthemes') ?>" title="<?php echo _e('Delete form layout','appthemes') ?>" /></a></td>
                </tr>

              <?php

                $i++;

                } // end for each
              ?>

              </tbody>

            <?php

            } else {

            ?>

                <tr>
                    <td colspan="7"><?php _e('No form layouts found.','appthemes') ?></td>
                </tr>

            <?php
            } // end $results
            ?>

            </table>


        </div><!-- end wrap -->

    <?php
    } // end switch
    ?>
    <script type="text/javascript">
        /* <![CDATA[ */
            function confirmBeforeDelete() { return confirm("<?php _e('Are you sure you want to delete this?', 'appthemes'); ?>"); }
            function confirmBeforeRemove() { return confirm("<?php _e('Are you sure you want to remove this?', 'appthemes'); ?>"); }
        /* ]]> */
    </script>

<?php

} // end function


function cp_custom_fields() {
    global $options_new_field, $wpdb, $current_user;

    $current_user = wp_get_current_user();
    ?>

    <!-- show/hide the dropdown field values tr -->
    <script type="text/javascript">
		/* <![CDATA[ */
			jQuery(document).ready(function() {
				jQuery("#mainform").validate({errorClass: "invalid"});
			});

			function show(o){
				if(o){switch(o.value){
					case 'drop-down': jQuery('#field_values_row').show(); jQuery('#field_min_length_row').hide(); break;
					case 'radio': jQuery('#field_values_row').show(); jQuery('#field_min_length_row').hide(); break;
					case 'checkbox': jQuery('#field_values_row').show(); jQuery('#field_min_length_row').hide(); break;
					case 'text box': jQuery('#field_min_length_row').show(); jQuery('#field_values_row').hide(); break;
					default: jQuery('#field_values_row').hide();jQuery('#field_min_length_row').hide();
				}}
			}

			//show/hide immediately on document load
			jQuery(document).ready(function() {
				show(jQuery('#field_type').get(0));
			});

			//hide unwanted options for cp_currency field
			jQuery(document).ready(function() {
				var field_name = jQuery('#field_name').val();
				if(field_name == 'cp_currency'){
					jQuery("#field_type option[value='text box']").attr("disabled","disabled");
					jQuery("#field_type option[value='text area']").attr("disabled","disabled");
					jQuery("#field_type option[value='checkbox']").attr("disabled","disabled");
				}
			});
		/* ]]> */
    </script>

    <?php

    // check to prevent php "notice: undefined index" msg when php strict warnings is on
    if ( isset( $_GET['action'] ) ) $theswitch = $_GET['action']; else $theswitch = '';

    switch ( $theswitch ) {

    case 'addfield':
    ?>

        <div class="wrap">
            <div class="icon32" id="icon-themes"><br /></div>
            <h2><?php _e('New Custom Field','appthemes') ?></h2>

            <?php cp_admin_info_box(); ?>

        <?php
        // check and make sure the form was submitted
        if ( isset( $_POST['submitted'] ) ) {

            $_POST['field_search'] = ''; // we aren't using this field so set it to blank for now to prevent notice

            $insert = "INSERT INTO $wpdb->cp_ad_fields ( field_name, field_label, field_desc, field_tooltip, field_type, field_values, field_search, field_owner, field_max_value, field_min_value, field_created, field_modified ) VALUES ( '" .
                        $wpdb->escape(appthemes_clean(cp_make_custom_name($_POST['field_label']))) . "','" .
                        $wpdb->escape(appthemes_clean($_POST['field_label'])) . "','" .
                        $wpdb->escape(appthemes_clean($_POST['field_desc'])) . "','" .
                        $wpdb->escape(esc_attr(appthemes_clean($_POST['field_tooltip']))) . "','" .
                        $wpdb->escape(appthemes_clean($_POST['field_type'])) . "','" .
                        $wpdb->escape(appthemes_clean($_POST['field_values'])) . "','" .
                        $wpdb->escape(appthemes_clean($_POST['field_search'])) . "','" .
                        $wpdb->escape(appthemes_clean($_POST['field_owner'])) . "','" .
                        
                        $wpdb->escape(appthemes_clean($_POST['field_max_value'])) . "','" .
                        $wpdb->escape(appthemes_clean($_POST['field_min_value'])) . "','" .
                        
                        current_time('mysql') . "','" .
                        current_time('mysql') .
                    "' )";

            $results = $wpdb->query( $insert );


            if ( $results ) :

                //$lastid = $wpdb->insert_id;
                //echo $lastid;
            ?>

                <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Creating your field.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
                <meta http-equiv="refresh" content="0; URL=?page=fields">

            <?php
            endif;

            die;

        } else {
        ?>

            <form method="post" id="mainform" action="">

                <?php cp_admin_fields( $options_new_field ) ?>

                <p class="submit"><input class="btn button-primary" name="save" type="submit" value="<?php _e('Create New Field','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
                    <input name="cancel" type="button" onClick="location.href='?page=fields'" value="<?php _e('Cancel','appthemes') ?>" /></p>
                <input name="submitted" type="hidden" value="yes" />
                <input name="field_owner" type="hidden" value="<?php echo $current_user->user_login ?>" />

            </form>

        <?php
        }
        ?>

        </div><!-- end wrap -->

    <?php
    break;


    case 'editfield':
    ?>

        <div class="wrap">
            <div class="icon32" id="icon-themes"><br /></div>
            <h2><?php _e('Edit Custom Field','appthemes') ?></h2>

            <?php cp_admin_info_box(); ?>

        <?php
        if ( isset( $_POST['submitted'] ) && $_POST['submitted'] == 'yes' ) {

	    // @todo Change to Update
            $update = $wpdb->prepare( "UPDATE $wpdb->cp_ad_fields SET" .
                    " field_name = %s," .
                    " field_label = %s," .
                    " field_desc = %s," .
                    " field_tooltip = %s," .
                    " field_type = %s," .
                    " field_values = %s," .
                    " field_min_length = %s," .
                    // " field_search = '" . $wpdb->escape(appthemes_clean($_POST['field_search'])) . "'," .
                    " field_owner = %s," .
                    " field_modified = %s" .
                    " WHERE field_id = %s",
                    appthemes_clean($_POST['field_name']),
                    appthemes_clean($_POST['field_label']),
                    appthemes_clean($_POST['field_desc']),
                    esc_attr(appthemes_clean($_POST['field_tooltip'])),
                    appthemes_clean($_POST['field_type']),
                    appthemes_clean($_POST['field_values']),
                    appthemes_clean($_POST['field_min_length']),
                    appthemes_clean($_POST['field_owner']),
                    current_time('mysql'),
                    $_GET['id']
                    );

            $results = $wpdb->query(  $update );

            ?>

            <p style="text-align:center;padding-top:50px;font-size:22px;">

                <?php _e('Saving your changes.....', 'appthemes') ?><br /><br />
                <img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" />

            </p>

            <meta http-equiv="refresh" content="0; URL=?page=fields">

        <?php
        } else {
        ?>


            <form method="post" id="mainform" action="">

            <?php cp_admin_db_fields($options_new_field, 'cp_ad_fields', 'field_id') ?>

                <p class="submit">
                    <input class="btn button-primary" name="save" type="submit" value="<?php _e('Save changes','appthemes') ?>" />&nbsp;&nbsp;&nbsp;
                    <input name="cancel" type="button" onClick="location.href='?page=fields'" value="<?php _e('Cancel','appthemes') ?>" />
                    <input name="submitted" type="hidden" value="yes" />
                    <input name="field_owner" type="hidden" value="<?php echo $current_user->user_login ?>" />
                </p>

            </form>

        <?php } ?>

        </div><!-- end wrap -->

    <?php
    break;


    case 'delete':

        // check and make sure this fields perms allow deletion
        $sql = "SELECT field_perm "
             . "FROM $wpdb->cp_ad_fields "
             . "WHERE field_id = '". $_GET['id'] ."' LIMIT 1";

        $results = $wpdb->get_row( $sql );

        // if it's not greater than zero, then delete it
        if ( !$results->field_perm > 0 ) {

            $delete = "DELETE FROM $wpdb->cp_ad_fields WHERE field_id = '". $_GET['id'] ."'";

            $wpdb->query( $delete );
        }
        ?>
        <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Deleting custom field.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
        <meta http-equiv="refresh" content="0; URL=?page=fields">

    <?php

    break;


    // cp_custom_fields() show the table of all custom fields
    default:

         $sql = "SELECT field_id, field_name, field_label, field_desc, field_tooltip, field_type, field_perm, field_owner, field_modified "
             . "FROM $wpdb->cp_ad_fields "
             . "ORDER BY field_name desc";

        $results = $wpdb->get_results($sql);
        ?>

        <div class="wrap">
        <div class="icon32" id="icon-tools"><br /></div>
        <h2><?php _e('Custom Fields','appthemes') ?>&nbsp;<a class="button add-new-h2" href="?page=fields&amp;action=addfield"><?php _e('Add New','appthemes') ?></a></h2>

        <?php cp_admin_info_box(); ?>

        <p class="admin-msg"><?php _e('Custom fields allow you to customize your ad submission forms and collect more information. Each custom field needs to be added to a form layout in order to be visible on your website. You can create unlimited custom fields and each one can be used across multiple form layouts. It is highly recommended to NOT delete a custom field once it is being used on your ads because it could cause ad editing problems for your customers.','appthemes') ?></p>

        <table id="tblspacer" class="widefat fixed">

            <thead>
                <tr>
                    <th scope="col" style="width:35px;">&nbsp;</th>
                    <th scope="col"><?php _e('Name','appthemes') ?></th>
                    <th scope="col" style="width:100px;"><?php _e('Type','appthemes') ?></th>
                    <th scope="col"><?php _e('Description','appthemes') ?></th>
                    <th scope="col" style="width:150px;"><?php _e('Modified','appthemes') ?></th>
                    <th scope="col" style="text-align:center;width:100px;"><?php _e('Actions','appthemes') ?></th>
                </tr>
            </thead>

            <?php
            if ($results) {
            ?>

                <tbody id="list">

                  <?php
                  $rowclass = '';
                  $i=1;

                  foreach($results as $result) {

                    $rowclass = 'even' == $rowclass ? 'alt' : 'even';
                    ?>

                    <tr class="<?php echo $rowclass ?>">
                        <td style="padding-left:10px;"><?php echo $i ?>.</td>
                        <td><a href="?page=fields&amp;action=editfield&amp;id=<?php echo $result->field_id ?>"><strong><?php echo esc_html( translate( $result->field_label, 'appthemes') ); ?></strong></a></td>
                        <td><?php echo $result->field_type ?></td>
                        <td><?php echo esc_html( translate( $result->field_desc, 'appthemes' ) ); ?></td>
                        <td><?php echo mysql2date(get_option('date_format') .' '. get_option('time_format'), $result->field_modified) ?> <?php _e('by', 'appthemes') ?> <?php echo $result->field_owner; ?></td>
                        <td style="text-align:center">

                            <?php
                            // show the correct edit options based on perms
                            switch($result->field_perm) {

                                case '1': // core fields no editing
                                ?>

                                    <a href="?page=fields&amp;action=editfield&amp;id=<?php echo $result->field_id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/edit.png" alt="" /></a>&nbsp;&nbsp;&nbsp;
                                    <img src="<?php echo bloginfo('template_directory'); ?>/images/cross-grey.png" alt="" />

                                <?php
                                break;

                                case '2': // core fields some editing
                                ?>

                                    <a href="?page=fields&amp;action=editfield&amp;id=<?php echo $result->field_id ?>"><img src="<?php echo bloginfo('template_directory') ?>/images/edit.png" alt="" /></a>&nbsp;&nbsp;&nbsp;
                                    <img src="<?php echo bloginfo('template_directory') ?>/images/cross-grey.png" alt="" />

                                <?php
                                break;

                                default: // regular fields full editing
                                    // don't change these two lines to plain html/php. Get t_else error msg
                                    echo '<a href="?page=fields&amp;action=editfield&amp;id='. $result->field_id .'"><img src="'. get_bloginfo('template_directory') .'/images/edit.png" alt="" /></a>&nbsp;&nbsp;&nbsp;';
                                    echo '<a onclick="return confirmBeforeDelete();" href="?page=fields&amp;action=delete&amp;id='. $result->field_id .'"><img src="'. get_bloginfo('template_directory') .'/images/cross.png" alt="" /></a>';

                           } // endswitch
                           ?>

                        </td>
                    </tr>

                <?php
                    $i++;

                  } //end foreach;
                  //} // mystery bracket which makes it work
                  ?>

              </tbody>

            <?php
            } else {
            ?>

                <tr>
                    <td colspan="5"><?php _e('No custom fields found. This usually means your install script did not run correctly. Go back and try reactivating the theme again.','appthemes') ?></td>
                </tr>

            <?php
            } // end $results
            ?>

        </table>

        </div><!-- end wrap -->

    <?php
    } // endswitch
    ?>



    <script type="text/javascript">
        /* <![CDATA[ */
            function confirmBeforeDelete() { return confirm("<?php _e('WARNING: Deleting this field will prevent any existing ads currently using this field from displaying the field value. Deleting fields is NOT recommended unless you do not have any existing ads using this field. Are you sure you want to delete this field?? (This cannot be undone)', 'appthemes'); ?>"); }
        /* ]]> */
    </script>

<?php

} // end function


// deletes all the ClassiPress database tables
function cp_delete_db_tables() {
    global $wpdb, $app_db_tables;

    echo '<p class="info">';

    foreach ( $app_db_tables as $key => $value ) {
        $sql = "DROP TABLE IF EXISTS ". $wpdb->prefix . $value;
        $wpdb->query($sql);

        printf( __("Table '%s' has been deleted.", 'appthemes'), $value);
        echo '<br/>';
    }

    echo '</p>';
}


// deletes all the ClassiPress database tables
function cp_delete_all_options() {
    global $wpdb;

    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name like 'cp_%'" );
    echo '<p class="info">' . __('All ClassiPress options have been deleted from the WordPress options table.', 'appthemes') . '</p>';
}

// flushes the caches
function cp_flush_all_cache() {
	global $wpdb, $app_transients;

    $output = '';

	foreach ( $app_transients as $key => $value ) :
		delete_transient($value);
		$output .= sprintf('<br />'.__("ClassiPress '%s' cache has been flushed.", 'appthemes' . '<br />'), $value);
	endforeach;

	return $output;

}

// show all the order transactions
function cp_transactions() {
    global $wpdb;
		include_once (TEMPLATEPATH . '/includes/forms/step-functions.php');

		if (isset($_GET['p'])) $page = (int)$_GET['p']; else $page = 1;
		$per_page = 10;
		$start = ($per_page * $page) - $per_page;

    // check to prevent php "notice: undefined index" msg when php strict warnings is on
    if ( isset( $_GET['action'] ) ) $theswitch = $_GET['action']; else $theswitch = '';

    switch ( $theswitch ) {

    // mark transaction as paid
    case 'setPaid':

            $update = "UPDATE $wpdb->cp_order_info SET payment_status = 'Completed' WHERE id = '". $_GET['id'] ."'";
            $wpdb->query( $update );
        ?>
        <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Updating transaction entry.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
        <meta http-equiv="refresh" content="0; URL=?page=transactions">

    <?php

    break;


    // mark transaction as unpaid
    case 'unsetPaid':

            $update = "UPDATE $wpdb->cp_order_info SET payment_status = 'Pending' WHERE id = '". $_GET['id'] ."'";
            $wpdb->query( $update );
        ?>
        <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Updating transaction entry.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
        <meta http-equiv="refresh" content="0; URL=?page=transactions">

    <?php

    break;


    // delete transaction entry
    case 'delete':

            $delete = "DELETE FROM $wpdb->cp_order_info WHERE id = '". $_GET['id'] ."'";
            $wpdb->query( $delete );
        ?>
        <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Deleting transaction entry.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
        <meta http-equiv="refresh" content="0; URL=?page=transactions">

    <?php

    break;


    // activate membership, update transaction entry
    case 'activateMembership':

            $orders = get_user_orders('',$_GET['oid']);
            if(!empty($orders)){
                $order_id = get_order_id($orders);
                $storedOrder = get_option($orders);
                $user_id = get_order_userid($orders);
                $the_user = get_userdata($user_id);
                //activate membership
                $order_processed = appthemes_process_membership_order($the_user, $storedOrder);
                //send email to user
                if($order_processed)
                    cp_owner_activated_membership_email($the_user, $order_processed);
                //update transaction entry
                $update = "UPDATE $wpdb->cp_order_info SET payment_status = 'Completed' WHERE custom = '". $_GET['oid'] ."'";
                $wpdb->query( $update );
            }
        ?>
        <p style="text-align:center;padding-top:50px;font-size:22px;"><?php _e('Activating membership plan.....','appthemes') ?><br /><br /><img src="<?php echo bloginfo('template_directory') ?>/images/loader.gif" alt="" /></p>
        <meta http-equiv="refresh" content="0; URL=?page=transactions">

    <?php

    break;


    // show the table of all transactions
    default:
?>
    <div class="wrap">
        <div class="icon32" id="icon-themes"><br /></div>
        <h2><?php _e('Order Transactions','appthemes') ?></h2>

        <?php cp_admin_info_box(); ?>

        <table id="tblspacer" class="widefat fixed">

            <thead>
                <tr>
                    <th scope="col" style="width:35px;">&nbsp;</th>
                    <th scope="col"><?php _e('Payer Name','appthemes') ?></th>
                    <th scope="col" style="text-align: center;"><?php _e('Payer Status','appthemes') ?></th>
                    <th scope="col"><?php _e('Ad Title','appthemes') ?></th>
                    <th scope="col"><?php _e('Item Description','appthemes') ?></th>
                    <th scope="col" style="width:125px;"><?php _e('Transaction ID','appthemes') ?></th>
                    <th scope="col"><?php _e('Payment Type','appthemes') ?></th>
                    <th scope="col"><?php _e('Payment Status','appthemes') ?></th>
                    <th scope="col"><?php _e('Total Amount','appthemes') ?></th>
                    <th scope="col" style="width:150px;"><?php _e('Date Paid','appthemes') ?></th>
                    <th scope="col" style="text-align:center;width:100px;"><?php _e('Actions','appthemes') ?></th>
                </tr>
            </thead>

    <?php
        // must be higher than personal edition so let's query the db
        $sql = "SELECT SQL_CALC_FOUND_ROWS o.*, p.post_title "
             . "FROM $wpdb->cp_order_info o, $wpdb->posts p "
             . "WHERE o.ad_id = p.id "
             . "ORDER BY o.id DESC LIMIT $start,$per_page";

        $results = $wpdb->get_results( $sql );

				$total_pages = $wpdb->get_var( $wpdb->prepare("SELECT FOUND_ROWS()") );
				$total_pages = ceil($total_pages/$per_page);

        if ( $results ) {
              $rowclass = '';
              $i=1;
            ?>

              <tbody id="list">

            <?php
                foreach ( $results as $result ) {

                $rowclass = 'even' == $rowclass ? 'alt' : 'even';
              ?>

                <tr class="<?php echo $rowclass ?>">
                    <td style="padding-left:10px;"><?php echo $i ?>.</td>

                    <td><strong><?php echo $result->first_name ?> <?php echo $result->last_name ?></strong><br /><a href="mailto:<?php echo $result->payer_email ?>"><?php echo $result->payer_email ?></a></td>
                    <td style="text-align: center;">
                        <?php if ($result->payer_status == 'verified') { ?><img src="<?php bloginfo('template_directory'); ?>/images/paypal_verified.gif" alt="" title="" /><br /><?php } ?>
                        <?php echo ucfirst($result->payer_status) ?>
                    </td>
                    <td><a href="post.php?action=edit&post=<?php echo $result->ad_id ?>"><?php echo $result->post_title ?></a></td>
                    <td><?php echo $result->item_name ?></td>
                    <td><?php echo $result->txn_id ?></td>
                    <td><?php echo ucfirst($result->payment_type) ?></td>
                    <td><?php echo ucfirst($result->payment_status) ?></td>
                    <td><?php echo $result->mc_gross ?> <?php echo $result->mc_currency ?></td>
                    <td><?php echo mysql2date(get_option('date_format') .' '. get_option('time_format'), $result->payment_date) ?></td>
                    <td style="text-align:center">
                      <?php
                        echo '<a onclick="return confirmBeforeDelete();" href="?page=transactions&amp;action=delete&amp;id='. $result->id .'" title="'. __('Delete', 'appthemes') .'"><img src="'. get_bloginfo('template_directory') .'/images/cross.png" alt="'. __('Delete', 'appthemes') .'" /></a>&nbsp;&nbsp;&nbsp;';
                        if(strtolower($result->payment_status) == 'completed')
                          echo '<br /><a href="?page=transactions&amp;action=unsetPaid&amp;id='. $result->id .'" title="'. __('Mark as Unpaid', 'appthemes') .'">'. __('Unmark Paid', 'appthemes') .'</a>';
                        else
                          echo '<br /><a href="?page=transactions&amp;action=setPaid&amp;id='. $result->id .'" title="'. __('Mark as Paid', 'appthemes') .'">'. __('Mark Paid', 'appthemes') .'</a>';
                      ?>
                    </td>
                </tr>

              <?php

                $i++;

                } // end for each
              ?>

            </tbody>

        <?php

        } else {

        ?>

            <tr>
                <td>&nbsp;</td><td colspan="10"><?php _e('No transactions found.','appthemes') ?></td>
            </tr>

        <?php
        } // end $results
        ?>

        </table> <!-- this is ok -->

				<div class="tablenav">
					<div class="tablenav-pages alignright">
						<?php
							if ( $total_pages > 1 ) {
								echo paginate_links( array(
									'base' => 'admin.php?page=transactions%_%',
									'format' => '&p=%#%',
									'prev_text' => __('&laquo; Previous'),
									'next_text' => __('Next &raquo;'),
									'total' => $total_pages,
									'current' => $page,
									'end_size' => 1,
									'mid_size' => 5,
								));
							}
						?>	
					</div> 
				</div>
				<div class="clear"></div>


        <div class="icon32" id="icon-themes"><br /></div>
        <h2><?php _e('Membership Orders','appthemes') ?></h2>
        <table id="tblspacer" class="widefat fixed">

            <thead>
                <tr>
                    <th scope="col" style="width:35px;">&nbsp;</th>
                    <th scope="col"><?php _e('Payer Name','appthemes') ?></th>
                    <th scope="col" style="text-align: center;"><?php _e('Payer Status','appthemes') ?></th>
                    <th scope="col"><?php _e('Item Description','appthemes') ?></th>
                    <th scope="col" style="width:125px;"><?php _e('Transaction ID','appthemes') ?></th>
                    <th scope="col"><?php _e('Payment Type','appthemes') ?></th>
                    <th scope="col"><?php _e('Payment Status','appthemes') ?></th>
                    <th scope="col"><?php _e('Total Amount','appthemes') ?></th>
                    <th scope="col" style="width:150px;"><?php _e('Date Paid','appthemes') ?></th>
                    <th scope="col" style="text-align:center;width:100px;"><?php _e('Actions','appthemes') ?></th>
                </tr>
            </thead>


		<?php
        // seperate table for membership orders
        $sql = "SELECT SQL_CALC_FOUND_ROWS * "
             . "FROM $wpdb->cp_order_info "
             . "WHERE ad_id = 0 "
             . "ORDER BY id DESC LIMIT $start,$per_page";

        $results = $wpdb->get_results($sql);

				$total_pages = $wpdb->get_var( $wpdb->prepare("SELECT FOUND_ROWS()") );
				$total_pages = ceil($total_pages/$per_page);

            if ($results) {
              $rowclass = '';
              $i=1;
            ?>

              <tbody id="list">

            <?php
                foreach ( $results as $result ) {

                $rowclass = 'even' == $rowclass ? 'alt' : 'even';
              ?>

                <tr class="<?php echo $rowclass ?>">
                    <td style="padding-left:10px;"><?php echo $i ?>.</td>
					<?php $payer = get_user_by('email', $result->payer_email); ?>
                    <?php //TODO - LOOKUP CUSTOMER BY PAYPAL EMAIL CUSTOM PROFILE FIELD ?>
                    <td><strong><?php echo $result->first_name ?> <?php echo $result->last_name ?></strong><br /><a href="<?php if(isset($payer->ID) && $payer) echo get_bloginfo('url').'/wp-admin/user-edit.php?user_id='.$payer->ID; else echo 'mailto:'.$result->payer_email; ?>"><?php echo $result->payer_email ?></a></td>
                    <td style="text-align: center;">
                        <?php if ($result->payer_status == 'verified') { ?><img src="<?php bloginfo('template_directory'); ?>/images/paypal_verified.gif" alt="" title="" /><br /><?php } ?>
                        <?php echo ucfirst($result->payer_status) ?>
                    </td>
                    <td><?php echo $result->item_name ?></td>
                    <td><?php echo $result->txn_id ?></td>
                    <td><?php echo ucfirst($result->payment_type) ?></td>
                    <td><?php echo ucfirst($result->payment_status) ?></td>
                    <td><?php echo $result->mc_gross ?> <?php echo $result->mc_currency ?></td>
                    <td><?php echo mysql2date(get_option('date_format') .' '. get_option('time_format'), $result->payment_date) ?></td>
                    <td style="text-align:center">
											<?php
												echo '<a onclick="return confirmBeforeDelete();" href="?page=transactions&amp;action=delete&amp;id='. $result->id .'" title="'. __('Delete', 'appthemes') .'"><img src="'. get_bloginfo('template_directory') .'/images/cross.png" alt="'. __('Delete', 'appthemes') .'" /></a>&nbsp;&nbsp;&nbsp;';
												if(strtolower($result->payment_status) == 'completed')
													echo '<br /><a href="?page=transactions&amp;action=unsetPaid&amp;id='. $result->id .'" title="'. __('Mark as Unpaid', 'appthemes') .'">'. __('Unmark Paid', 'appthemes') .'</a>';
												else {
													echo '<br /><a href="?page=transactions&amp;action=setPaid&amp;id='. $result->id .'" title="'. __('Mark as Paid', 'appthemes') .'">'. __('Mark Paid', 'appthemes') .'</a>';
													if(!empty($result->custom)) $orders = get_user_orders('',$result->custom); else $orders = '';
													if(!empty($orders))
														echo '<br /><a href="?page=transactions&amp;action=activateMembership&amp;oid='. $result->custom .'" title="'. __('Activate membership', 'appthemes') .'">'. __('Activate membership', 'appthemes') .'</a>';
												}
											?>
                    </td>
                </tr>

              <?php

                $i++;

                } // end for each
              ?>

              </tbody>

            <?php

            } else {

            ?>

                <tr>
                    <td>&nbsp;</td><td colspan="9"><?php _e('No transactions found.','appthemes') ?></td>
                </tr>

            <?php
            } // end $results
            ?>

				</table> <!-- this is ok -->

				<div class="tablenav">
					<div class="tablenav-pages alignright">
						<?php
							if ( $total_pages > 1 ) {
								echo paginate_links( array(
									'base' => 'admin.php?page=transactions%_%',
									'format' => '&p=%#%',
									'prev_text' => __('&laquo; Previous'),
									'next_text' => __('Next &raquo;'),
									'total' => $total_pages,
									'current' => $page,
									'end_size' => 1,
									'mid_size' => 5,
								));
							}
						?>	
					</div> 
				</div>
				<div class="clear"></div>


        </div><!-- end wrap -->

    <?php
    } // endswitch
    ?>



    <script type="text/javascript">
        /* <![CDATA[ */
            function confirmBeforeDelete() { return confirm("<?php _e('WARNING: Are you sure you want to delete this transaction entry?? (This cannot be undone)', 'appthemes'); ?>"); }
        /* ]]> */
    </script>

<?php

}


// system information page
function cp_system_info() {
    global $wpdb, $system_info, $app_version;
?>

        <div class="wrap">
            <div class="icon32" id="icon-options-general"><br/></div>
            <h2><?php _e('ClassiPress System Info','appthemes') ?></h2>

            <?php cp_admin_info_box(); ?>

            <?php
            // delete all the db tables if the button has been pressed.
            if ( isset($_POST['deletetables']) )
                cp_delete_db_tables();

            // delete all the cp config options from the wp_options table if the button has been pressed.
            if ( isset($_POST['deleteoptions']) )
                cp_delete_all_options();

			// flush the cache if the button has been pressed.
			if ( isset($_POST['flushcache']) )
				echo cp_flush_all_cache();

			// reinstall completed
			if ( isset($_GET['reinstall']) )
			    echo '<p class="info">'. __('ClassiPress was successfully reinstalled.', 'appthemes') . '</p>';
            ?>

			<script type="text/javascript">
			jQuery(function() {
				jQuery("#tabs-wrap").tabs({
					fx: {
						opacity: 'toggle',
						duration: 200
					}
				});
			});
			</script>

		<div id="tabs-wrap">
			<ul class="tabs">
				<li><a href="#tab0"><?php _e('Debug Info','appthemes')?></a></li>
				<li><a href="#tab1"><?php _e('Cron Jobs','appthemes')?></a></li>
				<li><a href="#tab2"><?php _e('Advanced','appthemes')?></a></li>
			</ul>

			<div id="tab0">


                <table class="widefat fixed" style="width:850px;">

                    <thead>
                        <tr>
                            <th scope="col" width="200px"><?php _e('Theme Info','appthemes')?></th>
                            <th scope="col">&nbsp;</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="titledesc"><?php _e('ClassiPress Version','appthemes')?></td>
                            <td class="forminp"><?php echo $app_version; ?></td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php _e('ClassiPress DB Version','appthemes')?></td>
                            <td class="forminp"><?php echo get_option('cp_db_version'); ?></td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php _e('WordPress Version','appthemes')?></td>
                            <td class="forminp"><?php if (function_exists('bloginfo')) echo bloginfo('version'); ?> <?php if ( is_multisite() ) echo '- '.__('Multisite', 'appthemes'); ?></td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php _e('Theme Path','appthemes')?></td>
                            <td class="forminp"><?php if (function_exists('bloginfo')) echo bloginfo('template_url'); ?></td>
                        </tr>

                    <thead>
                        <tr>
                            <th scope="col" width="200px"><?php _e('Server Info','appthemes')?></th>
                            <th scope="col">&nbsp;</th>
                        </tr>
                    </thead>

                        <tr>
                            <td class="titledesc"><?php _e('PHP Version','appthemes')?></td>
                            <td class="forminp"><?php if (function_exists('phpversion')) echo phpversion(); ?></td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php _e('Server Software','appthemes')?></td>
                            <td class="forminp"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php _e('UPLOAD_MAX_FILESIZE','appthemes')?></td>
                            <td class="forminp"><?php if (function_exists('phpversion')) echo ini_get('upload_max_filesize'); ?></td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php _e('DISPLAY_ERRORS','appthemes')?></td>
                            <td class="forminp"><?php if (function_exists('phpversion')) echo ini_get('display_errors'); ?></td>
                        </tr>


                    <thead>
                        <tr>
                            <th scope="col" width="200px"><?php _e('Image Support','appthemes')?></th>
                            <th scope="col">&nbsp;</th>
                        </tr>
                    </thead>

                        <tr>
                            <td class="titledesc"><?php _e('GD Library Check','appthemes')?></td>
                            <td class="forminp"><?php if (extension_loaded('gd') && function_exists('gd_info')) echo '<font color="green">' . __('Your server supports the GD Library.', 'appthemes'). '</font>'; else echo '<font color="red">' . __('Your server does not have the GD Library enabled so the legacy image resizer script (TimThumb) will not work. Most servers with PHP 4.3+ includes this by default.', 'appthemes'). '</font>'; ?></td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php _e('Image Upload Path','appthemes')?></td>
                            <td class="forminp"><?php $uploads = wp_upload_dir(); echo $uploads['url'];?>
                            <?php if ( !appthemes_is_wpmu() ) printf( ' - <a href="%s">' . __('(change this)', 'appthemes') . '</a>', 'options-media.php' ); ?></td>
                        </tr>

                   <!--

                        <tr>
                            <td class="titledesc"><?php // _e('Image Dir Check','appthemes')?></td>
                            <td class="forminp">
                                <?php
                                // if (!is_dir(CP_UPLOAD_DIR)) {
                                //    printf( '<font color="red">' . __('Image upload directory DOES NOT exist. Create a classipress folder in your %s folder.', 'appthemes'), WP_UPLOAD_DIR ) . '</font>';
                                // } else {
                                //    echo '<font color="green">' . __('Image upload directory exists.','appthemes') . '</font>';
                                // }
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php // _e('Image Dir Writable','appthemes')?></td>
                            <td class="forminp">
                            <?php
                            // if (!is_writable(CP_UPLOAD_DIR)) {
                            //    printf( '<font color="red">' . __('Image upload directory is NOT writable. Make sure you have the correct permissions set (CHMOD 777) on your %s folder.', 'appthemes'), CP_UPLOAD_DIR ) . '</font>';
                            // } else {
                            //    echo '<font color="green">' . __('Image upload directory is writable.','appthemes') . '</font>';
                            // }
                            ?>
                            </td>
                        </tr>
                -->

                    <thead>
                        <tr>
                            <th scope="col" width="200px"><?php _e('PayPal IPN Check','appthemes')?></th>
                            <th scope="col">&nbsp;</th>
                        </tr>
                    </thead>

                        <tr>
                            <td class="titledesc"><?php _e('FSOCKOPEN Check','appthemes')?></td>
                            <td class="forminp"><?php if ( function_exists('fsockopen') ) echo '<span style="color:green">' . __('Your server has fsockopen enabled.', 'appthemes'). '</span>'; else echo '<span style="color:red">' . __('Your server does not have fsockopen enabled so PayPal IPN will not work. Contact your host provider to have it enabled.', 'appthemes'). '</span>'; ?></td>
                        </tr>

						<tr>
                            <td class="titledesc"><?php _e('OPENSSL Check','appthemes')?></td>
                            <td class="forminp"><?php if ( function_exists('openssl_open') ) echo '<span style="color:green">' . __('Your server has openssl_open enabled. Also make sure port 443 is open on the firewall.', 'appthemes'). '</span>'; else echo '<span style="color:red">' . __('Your server does not have openssl_open enabled so PayPal IPN will not work. Contact your host provider to have it enabled.', 'appthemes'). '</span>'; ?></td>
                        </tr>

                        <?php if ( function_exists( 'wp_remote_post' ) ) : ?>
                            <tr>
                                <td class="titledesc"><?php _e('WP Remote Post Check','appthemes')?></td>
                                <td class="forminp"><?php
                                    $paypal_adr = 'https://www.paypal.com/cgi-bin/webscr';
                                    $params = array(
                                        'timeout' 	=> 10
                                    );
                                    $response = wp_remote_post( $paypal_adr, $params );

                                    // Retry
                                    if ( is_wp_error($response) ) {
                                        $params['sslverify'] = false;
                                        $response = wp_remote_post( $paypal_adr, $params );
                                    }

                                    if ( !is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) echo '<span style="color:green">' . __('The wp_remote_post() test to PayPal was successful.', 'appthemes'). '</span>'; else echo '<span style="color:red">' . __('The wp_remote_post() test to PayPal failed. Sorry, PayPal IPN won\'t work with your server.', 'appthemes'). '</span>';
                                ?></td>
                            </tr>
                        <?php endif; ?>

                        <thead>
                        <tr>
                            <th scope="col" width="200px"><?php _e('Other Checks','appthemes')?></th>
                            <th scope="col">&nbsp;</th>
                        </tr>
                    </thead>

                        <tr>
                            <td class="titledesc"><?php _e('CURL Check','appthemes')?></td>
                            <td class="forminp"><?php if ( function_exists('curl_init') ) echo '<span style="color:green">' . __('Your server has curl enabled.', 'appthemes'). '</span>'; else echo '<span style="color:red">' . __('Your server does not have curl enabled so some functions will not work. Contact your host provider to have it enabled.', 'appthemes'). '</span>'; ?></td>
                        </tr>

                        <tr>
                            <td class="titledesc"><?php _e('JSON DECODE Check','appthemes')?></td>
                            <td class="forminp"><?php if ( function_exists('json_decode') ) echo '<span style="color:green">' . __('Your server has json_decode enabled.', 'appthemes'). '</span>'; else echo '<span style="color:red">' . __('Your server does not have json_decode enabled so some functions will not work. Contact your host provider to have it enabled.', 'appthemes'). '</span>'; ?></td>
                        </tr>


                    </tbody>

                </table>

				</div> <!-- # tab0 -->

				<div id="tab1">

					<table class="widefat fixed" style="width:850px;">
						<thead>
							<tr>
								<th scope="col"><?php _e('Next Run Date','appthemes')?></th>
								<th scope="col"><?php _e('Frequency','appthemes')?></th>
								<th scope="col"><?php _e('Hook Name','appthemes')?></th>
							</tr>
						</thead>
						<tbody>
							<?php
                            $cron = _get_cron_array();
                            $schedules = wp_get_schedules();
                            $date_format = _x( 'M j, Y @ G:i','appthemes');
                            foreach ( $cron as $timestamp => $cronhooks ) {
                                foreach ( (array) $cronhooks as $hook => $events ) {
                                    foreach ( (array) $events as $key => $event ) {
                                        $cron[ $timestamp ][ $hook ][ $key ][ 'date' ] = date_i18n( $date_format, $timestamp );
                                    }
                                }
                            }
							?>
							<?php foreach ( $cron as $timestamp => $cronhooks ) { ?>
								<?php foreach ( (array) $cronhooks as $hook => $events ) { ?>
									<?php foreach ( (array) $events as $event ) { ?>
										<tr>
											<th scope="row"><?php echo $event[ 'date' ]; ?></th>
											<td>
                                            <?php
                                            if ( $event[ 'schedule' ] ) {
                                                echo $schedules [ $event[ 'schedule' ] ][ 'display' ];
                                            } else {
                                                ?><em><?php _e('One-off event','appthemes')?></em><?php
                                            }
                                            ?>
											</td>
											<td><?php echo $hook; ?></td>
										</tr>
									<?php } ?>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>

				</div> <!-- # tab1 -->

				<div id="tab2">

				<table class="widefat fixed" style="width:850px;">


				<thead>
					<tr>
						<th scope="col" width="200px"><?php _e('Theme Cache','appthemes')?></th>
						<th scope="col">&nbsp;</th>
					</tr>
                </thead>

				<form method="post" id="mainform" action="">
                    <tr>
                        <td class="titledesc"><?php _e('Flush Theme Cache','appthemes')?></td>
                        <td class="forminp">
                            <p class="submit"><input name="save" type="submit" value="<?php _e('Flush Entire ClassiPress Cache','appthemes') ?>" /><br />
                        <?php _e("Sometimes you may have changed something and it hasn't been updated on your site. Flushing the cache will empty anything that ClassiPress has stored in the cache (i.e. category drop-down menu, home page directory structure, etc).",'appthemes')?>
                            </p>
                            <input name="flushcache" type="hidden" value="yes" />
                        </td>
                    </tr>
                </form>

					<thead>
							<tr>
								<th scope="col" width="200px"><?php _e('Uninstall Theme','appthemes')?></th>
								<th scope="col">&nbsp;</th>
							</tr>
						</thead>

					<form method="post" id="mainform" action="">
						<tr>
							<td class="titledesc"><?php _e('Delete Database Tables','appthemes')?></td>
							<td class="forminp">
								<p class="submit"><input onclick="return confirmBeforeDeleteTbls();" name="save" type="submit" value="<?php _e('Delete ClassiPress Database Tables','appthemes') ?>" /><br />
							<?php _e('Do you wish to completely delete all ClassiPress database tables? Once you do this you will lose any custom fields, forms, ad packs, etc that you have created.','appthemes')?>
								</p>
								<input name="deletetables" type="hidden" value="yes" />
							</td>
						</tr>
					</form>

					<form method="post" id="mainform" action="">
						<tr>
							<td class="titledesc"><?php _e('Delete Config Options','appthemes')?></td>
							<td class="forminp">
								<p class="submit"><input onclick="return confirmBeforeDeleteOptions();" name="save" type="submit" value="<?php _e('Delete ClassiPress Config Options','appthemes') ?>" /><br />
							<?php _e('Do you wish to completely delete all ClassiPress configuration options? This will delete all values saved on the settings, pricing, gateways, etc admin pages from the wp_options database table.','appthemes')?>
								</p>
								<input name="deleteoptions" type="hidden" value="yes" />
							</td>
						</tr>
					</form>

				<thead>
					<tr>
						<th scope="col" width="200px"><?php _e('Theme','appthemes')?></th>
						<th scope="col">&nbsp;</th>
					</tr>
                </thead>
<!--
                    <tr>
                        <td class="titledesc"><?php _e('Rerun Install Script','appthemes')?></td>
                        <td class="forminp">
                            <form action="?page=sysinfo&reinstall=yes" id="reinstall-form" method="post">
                                <p class="submit btop">
                                    <input type="submit" value="<?php _e('Reinstall ClassiPress','appthemes')?>" name="convert" onclick="return confirmUpdate();" /><br />
			                        <?php _e("Any website administrators that are developeres may have a desire to run the install script again. This is the same thing that occurs when you move between ClassiPress versions and click to update your database version.",'appthemes')?>
                                </p>
                                <input type="hidden" value="resintall" name="submitted" />
                            </form>
                        </td>
                    </tr>
-->
                    <tr>
                        <td class="titledesc"><?php _e('Rerun Migration Script','appthemes')?></td>
                        <td class="forminp">
                            <form action="admin.php?page=settings" id="reinstall-form" method="post">
                                <p class="submit btop">
                                    <input type="submit" value="<?php _e('Rerun ClassiPress Migration Script','appthemes')?>" name="migrate" /><br />
			                        <?php _e("If you're still using ClassiPress version 3.0.4 (or earlier) and were not prompted to upgrade to 3.0.5 or the script timed out, click this button. It will attempt to rerun the migration script again. Running this script won't do any harm if you aren't sure about it.",'appthemes'); ?> <br /><br />
                                </p>
                                <input type="hidden" value="convertToCustomPostType" name="submitted" />
                            </form>
                        </td>
                    </tr>




				</table>

			</div> <!-- # tab2 -->

		</div><!-- #tab-wrap -->


        </div>

        <script type="text/javascript">
        /* <![CDATA[ */
            function confirmBeforeDeleteTbls() { return confirm("<?php _e('WARNING: You are about to completely delete all ClassiPress database tables. Are you sure you want to proceed? (This cannot be undone)', 'appthemes'); ?>"); }
            function confirmBeforeDeleteOptions() { return confirm("<?php _e('WARNING: You are about to completely delete all ClassiPress configuration options from the wp_options database table. Are you sure you want to proceed? (This cannot be undone)', 'appthemes'); ?>"); }
        /* ]]> */
        </script>

<?php
}


?>
