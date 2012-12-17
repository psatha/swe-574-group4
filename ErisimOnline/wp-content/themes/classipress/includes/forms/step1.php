<script src="https://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>
<?php
/**
 * This is step 1 of 3 for the ad submission form
 *
 * @package ClassiPress
 * @subpackage New Ad
 * @author AppThemes
 *
 *
 */

acf_form_head();

global $current_user, $wpdb;

?>
    <style> /* hack on new ad submission page until we fix multi-level dropdown issue with .js */
        div#catlvl0 select#cat.dropdownlist, div#childCategory select#cat.dropdownlist, form#mainform.form_step select {padding:6px;height: 3em; color:#666; font-size:12px; display: block;}
    </style>
    
    <script>
    // <![CDATA[
    jQuery(document).ready(function($) {
        /* remove the select dropdown menus style - hack until we can fix the multi-level dropdown js issue */
        $('select').selectBox('destroy');
    });	
    // ]]>	
    </script>

  <div id="step1">

      <h2 class="dotted"><?php _e('Submit Your Listing','appthemes');?></h2>

            <img src="<?php bloginfo('template_url'); ?>/images/step1.gif" alt="" class="stepimg" />


            <?php 
                // display the custom message
                echo get_option('cp_ads_form_msg');
				//use to debug step 1 post vars
				//echo '<pre>'.print_r($_POST, true).'</pre>';
            ?>

 
            <p class="dotted">&nbsp;</p>

        <?php
            // show the category dropdown when first arriving to this page.
            // Also show it if cat equals -1 which means the 'select one' option was submitted
            if ( !isset($_POST['cat'] ) || ($_POST['cat'] == '-1') )  {

        ?>

                <form name="mainform" id="mainform" class="form_step" action="" method="post">

                    <ol>

                        <li>
                            <div class="labelwrapper"><label><?php _e('Select a Category:','appthemes');?></label></div>
                            <div id="ad-categories" style="display:block; margin-left:170px;">						
                                <div id="catlvl0">
                                <?php
        
                                if ( get_option('cp_price_scheme') == 'category' && get_option('cp_enable_paypal') == 'yes' && get_option('cp_ad_parent_posting') != 'no' ) {
        
                                    cp_dropdown_categories_prices('show_option_none='.__('Select one','appthemes').'&class=dropdownlist&orderby=name&order=ASC&hide_empty=0&hierarchical=1&taxonomy='.APP_TAX_CAT.'&depth=1');
        
                                } else {
        
                                   wp_dropdown_categories('show_option_none='.__('Select one','appthemes').'&class=dropdownlist&orderby=name&order=ASC&hide_empty=0&hierarchical=1&taxonomy='.APP_TAX_CAT.'&depth=1');
        
                                }
        
                                ?>
                                <div style="clear:both;"></div>
                                </div>
                            </div>    
                            <div id="ad-categories-footer" style="display:inline-block; float:left; margin-left:170px; width: 314px;">
                            	<input type="submit" name="getcat" id="getcat" class="btn_orange" value="<?php _e('Go','appthemes'); ?>&rsaquo;&rsaquo;" />
	                            <div id="chosenCategory"><input id="cat" name="cat" type="input" value="-1" /></div>
                                <div style="clear:both;"></div>
                            </div>
                            <div style="clear:both;"></div>               
                        </li>

                    </ol>

                </form>


            <?php } else {

  
                // show the form based on the category selected
                // get the cat nice name and put it into a variable
                $adCategory = get_term_by( 'id',$_POST['cat'], APP_TAX_CAT );
				$_POST['catname'] = $adCategory->name;
            ?>

                <form name="mainform" id="mainform" class="form_step" action="" method="post" enctype="multipart/form-data">

                    <ol>

                        <li>
                        	<div class="labelwrapper">
                            	<label><?php _e('Category','appthemes');?>:</label>
							</div>
                            <strong><?php echo $_POST['catname']; ?></strong>&nbsp;&nbsp;<small><a href=""><?php _e('(change)', 'appthemes') ?></a></small>
                        </li>

                        <?php echo cp_show_form( $_POST['cat'] ); ?>

                        <p class="btn1">
                            <input type="submit" name="step1" id="step1" class="btn_orange" value="<?php _e('Continue &rsaquo;&rsaquo;','appthemes'); ?>" />
                        </p>

                    </ol>

                        <input type="hidden" id="cat" name="cat" value="<?php echo $_POST['cat']; ?>" />
                        <input type="hidden" id="catname" name="catname" value="<?php echo $_POST['catname']; ?>" />
                        <input type="hidden" id="fid" name="fid" value="<?php if(isset($_POST['fid'])) echo $_POST['fid']; ?>" />
                        <input type="hidden" id="oid" name="oid" value="<?php echo $order_id; ?>" />
					
                </form>

            <?php } ?>

</div>