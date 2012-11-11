<?php
/**
 * This is step 1 of 2 for the membership purchase submission form
 *
 * @package ClassiPress
 * @subpackage Purchase Membership
 * @author AppThemes
 *
 *
 */


global $current_user, $wpdb, $app_abbr;
?>


  <div id="step1"></div>

      <h2 class="dotted"><?php _e('Purchase a Membership Pack','appthemes');?></h2>

            <img src="<?php bloginfo('template_url'); ?>/images/step1.gif" alt="" class="stepimg" />

            <?php 
                // display the custom message
                echo get_option('cp_membership_form_msg');
				//use to debug step 1 post vars
				
				if(isset($_GET['membership']) && $_GET['membership'] == 'required'):
            ?>
            
					<p class="info"><?php _e('Membership is currently required','appthemes'); ?><?php if(!empty($_GET['cat']) && $_GET['cat'] != 'all') { _e(' in order to post to category ', 'appthemes');
					$theTerm = get_term_by('term_id ', $_GET['cat'], APP_TAX_CAT); 
					echo ' <a href="/'.get_option($app_abbr.'_ad_cat_tax_permalink').'/'.$theTerm->slug.'/">'.$theTerm->name.'</a>';} ?>.</p>
 			
            <?php endif; ?>
 
            <p class="dotted">&nbsp;</p>

                <form name="mainform" id="mainform" class="form_membership_step" action="" method="post" enctype="multipart/form-data">




		<?php 
            $sql = "SELECT * "
                 . "FROM $wpdb->cp_ad_packs "
                 . "ORDER BY pack_id desc";
            
            $results = $wpdb->get_results($sql);
        ?>

        <div id="membership-packs" class="wrap">

        <table id="memberships" class="widefat fixed">

            <thead style="text-align:left;">
                <tr>
                    <th scope="col"><?php _e('Name','appthemes') ?></th>
                    <th scope="col"><?php _e('Membership Benefit','appthemes') ?></th>
                    <th scope="col"><?php _e('Subscription','appthemes') ?></th>
                    <th scope="col" style="width:75px;"></th>
                </tr>
            </thead>

            <?php
            if ($results) {
                $rowclass = '';
                $i=1;
            ?>

              <tbody id="list">

            <?php
                foreach( $results as $result ) {
					unset($rowclass, $requiredClass);
	                if($result->pack_status == 'active_membership') :
	                //$rowclass = 'even' == $rowclass ? 'alt' : 'even';
					$rowclass = 'even';
					$benefit = get_pack_benefit($result);
					if(stristr($result->pack_type, 'required')) {
						$requiredClass = 'required';
					}
              ?>

                <tr class="<?php echo $rowclass.' '.$requiredClass; ?>">
                    <?php $i++; ?>
                    <td><strong><?php echo stripslashes($result->pack_name); ?></strong><a class="tip" tip="<?php echo $result->pack_desc; ?>" tabindex="99"><div class="helpico"></div></a></td>
                    <td><?php echo $benefit; ?></td>
                    <td><?php echo cp_pos_price($result->pack_membership_price).' / '.$result->pack_duration.' '.__('days','appthemes'); ?></td>
                    <td><input type="submit" name="step1" id="step1" class="btn_orange" onclick="document.getElementById('pack').value=<?php echo $result->pack_id; ?>;" value="<?php _e('Buy Now &rsaquo;&rsaquo;','appthemes'); ?>" style="margin-left: 5px; margin-bottom: 5px;" /></td>
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
                    <td colspan="7"><?php _e('No membership packs found.','appthemes') ?></td>
                </tr>

            <?php
            } // end $results
            ?>

            </table>


        </div><!-- end wrap for membership packs-->

                        <input type="hidden" id="oid" name="oid" value="<?php echo $order_id; ?>" />
                        <input type="hidden" id="pack" name="pack" value="<?php if(isset($_POST['pack'])) echo $_POST['pack']; ?>" />

                </form>