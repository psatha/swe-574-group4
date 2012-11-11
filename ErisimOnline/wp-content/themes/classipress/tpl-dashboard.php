<?php
/*
 * Template Name: User Dashboard
 *
 * This template must be assigned to a page
 * in order for it to work correctly
 *
*/

auth_redirect_login(); // if not logged in, redirect to login page
nocache_headers();

$current_user = wp_get_current_user(); // grabs the user info and puts into vars
$display_user_name = cp_get_user_name();

// include the payment gateway code
include_once (TEMPLATEPATH . '/includes/gateways/paypal/paypal.php');


// check to see if we want to pause or restart the ad
if(!empty($_GET['action'])) :
    $d = trim($_GET['action']);
    $aid = trim($_GET['aid']);

    // make sure author matches ad. Prevents people from trying to hack other peoples ads
    $sql = $wpdb->prepare("SELECT wposts.post_author "
       . "FROM $wpdb->posts wposts "
       . "WHERE ID = %s "
       . "AND post_author = %s",
       $aid,
       $current_user->ID);

    $checkauthor = $wpdb->get_row($sql);

    if($checkauthor != null) { // author check is ok. now update ad status

        if ($d == 'pause') {
            $my_ad = array();
            $my_ad['ID'] = $aid;
            $my_ad['post_status'] = 'draft';
            wp_update_post($my_ad);
            $action_msg = __('Ad has been paused', 'appthemes');

        } elseif ($d == 'restart') {
            $my_ad = array();
            $my_ad['ID'] = $aid;
            $my_ad['post_status'] = 'publish';
            wp_update_post($my_ad);
            $action_msg = __('Ad has been published', 'appthemes');

    		} elseif ($d == 'delete') { 
            cp_delete_ad_listing($aid);
            $action_msg = __('Ad has been deleted', 'appthemes');

    		} elseif ($d == 'freerenew') { 
            cp_renew_ad_listing($aid);
            $action_msg = __('Ad has been relisted', 'appthemes');

    		} elseif ($d == 'setSold') { 
            update_post_meta($aid, 'cp_ad_sold', 'yes'); 
            $action_msg = __('Ad has been marked as sold', 'appthemes');

    		} elseif ($d == 'unsetSold') { 
            update_post_meta($aid, 'cp_ad_sold', 'no'); 
            $action_msg = __('Ad has been unmarked as sold', 'appthemes');

        } else { //echo "nothing here";
        }

    }

endif;
?>

<?php get_header(); ?>


<!-- CONTENT -->
  <div class="content">

    <div class="content_botbg">

      <div class="content_res">


        <!-- left block -->
        <div class="content_left">

            <div class="shadowblock_out">
            <div class="shadowblock">

                <h1 class="single dotted"><?php printf(__("%s's Dashboard", 'appthemes'), $display_user_name); ?></h1>

                <?php if(isset($action_msg)) { ?><p class="success"><?php echo $action_msg; ?></p><?php } ?>

                <p><?php _e('Below you will find a listing of all your classified ads. Click on one of the options to perform a specific task. If you have any questions, please contact the site administrator.','appthemes');?></p>

                <table border="0" cellpadding="4" cellspacing="1" class="tblwide">
                    <thead>
                        <tr>
                            <th width="5px">&nbsp;</th>
                            <th class="text-left">&nbsp;<?php _e('Title','appthemes');?></th>
							<th width="40px"><?php _e('Views','appthemes');?></th>
                            <th width="80px"><?php _e('Status','appthemes');?></th>
                            <th width="90px"><div style="text-align: center;"><?php _e('Options','appthemes');?></div></th>
                        </tr>
                    </thead>
                    <tbody>

					<?php 
						// setup the pagination and query
						$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
						query_posts(array('posts_per_page' => 10, 'post_type' => APP_POST_TYPE, 'post_status' => 'publish, pending, draft', 'author' => $current_user->ID, 'paged' => $paged));

						// build the row counter depending on what page we're on
						if($paged == 1) $i = 0; else $i = $paged * 10 - 10;
					?>

					<?php if(have_posts()) : ?>

						<?php while(have_posts()) : the_post(); $i++; ?>

                        <?php                     
                            // check to see if ad is legacy or not and then format date based on WP options
                            if(get_post_meta($post->ID, 'expires', true))
                                $expire_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_post_meta($post->ID, 'expires', true)));
                            else
                                $expire_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_post_meta($post->ID, 'cp_sys_expire_date', true)));

                            
                            // get the ad total cost and legacy check
                            if (get_post_meta($post->ID, 'cp_totalcost', true))
                                $total_cost = get_post_meta($post->ID, 'cp_totalcost', true);
                            else
                                $total_cost = get_post_meta($post->ID, 'cp_sys_total_ad_cost', true);

                            // get the prune period and legacy check
                            //  if (get_post_meta($post->ID, 'cp_sys_ad_duration', true))
                            //      $prun_period = get_post_meta($post->ID, 'cp_sys_ad_duration', true);
                            //  else
                            //      $prun_period = get_option('cp_prun_period');

							if (get_post_meta($post->ID, 'cp_total_count', true))
								$ad_views = number_format(get_post_meta($post->ID, 'cp_total_count', true));
							else
								$ad_views = '-';


                            // now let's figure out what the ad status and options should be
                            // it's a live and published ad
                            if ($post->post_status == 'publish') {

                                $poststatus = __('Live','appthemes');
                                $poststatus .= ' ' . __('Until','appthemes') . '<br/><p class="small">(' . $expire_date . ')</p>';

                                $fontcolor = '#33CC33';
                                $postimage = 'pause.png';
                                $postalt =  __('pause ad','appthemes');
                                $postaction = 'pause';

                            // it's a pending ad which gives us several possibilities
                            } elseif ($post->post_status == 'pending') {


                                // ad is free and waiting to be approved
                                if ($total_cost == 0) {
                                    $poststatus = __('awaiting approval','appthemes');
                                    $fontcolor = '#C00202';
                                    $postimage = '';
                                    $postalt = '';
                                    $postaction = 'pending';

                                // ad hasn't been paid for yet
                                } else {
                                    $poststatus = __('awaiting payment','appthemes');
                                    $fontcolor = '#C00202';
                                    $postimage = '';
                                    $postalt = '';
                                    $postaction = 'pending';
                                }

                                

                            } elseif ($post->post_status == 'draft') {
							
							//handling issue where date format needs to be unified
                            if(get_post_meta($post->ID, 'expires', true))
                                $expire_date = get_post_meta($post->ID, 'expires', true);
                            else
                                $expire_date = get_post_meta($post->ID, 'cp_sys_expire_date', true);

                                // current date is past the expires date so mark ad ended
                                if (strtotime(date('Y-m-d H:i:s')) > (strtotime($expire_date))) {
                                    $poststatus = __('ended','appthemes') . '<br/><p class="small">(' . $expire_date . ')</p>';
                                    $fontcolor = '#666666';
                                    $postimage = '';
                                    $postalt = '';
                                    $postaction = 'ended';

                                // ad has been paused by ad owner
                                } else {
                                    $poststatus = __('offline','appthemes');
                                    $fontcolor = '#bbbbbb';
                                    $postimage = 'start-blue.png';
                                    $postalt = __('restart ad','appthemes');
                                    $postaction = 'restart';
                                }

                            } else {
                                    $poststatus = '&mdash;';
                            }
                        ?>


                        <tr class="even">
                            <td class="text-right"><?php echo $i; ?>.</td>
                            <td><h3>
                                <?php if ($post->post_status == 'pending' || $post->post_status == 'draft' || $poststatus == 'ended' || $poststatus == 'offline') { ?>

                                    <?php the_title(); ?>

                                <?php } else { ?>

                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                                <?php } ?>    
                                </h3>

                                <div class="meta"><span class="folder"><?php echo get_the_term_list(get_the_id(), APP_TAX_CAT, '', ', ', ''); ?></span> | <span class="clock"><span><?php the_time(get_option('date_format'))?></span></span></div>

                            </td>

							<td class="text-center"><?php echo $ad_views; ?></td>

                            <td class="text-center"><span style="color:<?php echo $fontcolor ?>;"><?php echo ucfirst($poststatus) ?></span></td>

                            <td class="text-center">
                                <?php 

																if ( $post->post_status == 'pending' && $postaction != 'ended' ) {

																		// show the paypal button if the ad has not been paid for yet
																		if ( ($total_cost != 0) ) {
																				if ( get_option('cp_enable_paypal') == 'yes' ) echo cp_dashboard_paypal_button( $post->ID );
																				cp_action_payment_button( $post->ID );
																		} else {
																				echo '&mdash;';
																		}
																		echo '<a onclick="return confirmBeforeDelete();" href="' . CP_DASHBOARD_URL . '?aid=' . $post->ID . '&amp;action=delete" style="display: block;">' . __('Delete Ad', 'appthemes') . '</a>';

																} elseif ( $post->post_status == 'draft' && $postaction == 'ended' ) {

																		if ( get_option('cp_allow_relist') == 'yes' ) {

																				// show the paypal button so they can relist their ad only
																				// if it's not a legacy ad and they originally paid to list
																				if ( ($total_cost != 0) && get_post_meta($post->ID, 'cp_totalcost', true) == '' ) {
																						if ( get_option('cp_enable_paypal') == 'yes' ) echo cp_dashboard_paypal_button( $post->ID );
																						cp_action_payment_button( $post->ID );
																						if ( get_option('cp_enable_bank') == 'yes' ) _e('Contact us to relist ad', 'appthemes');
																				} else {
																						echo '<a href="' . CP_DASHBOARD_URL . '?aid=' . $post->ID . '&amp;action=freerenew">' . __('Relist Ad', 'appthemes') . '</a>';
																				}

																		} else {
																				echo '&mdash;';
																		}
																		echo '<a onclick="return confirmBeforeDelete();" href="' . CP_DASHBOARD_URL . '?aid=' . $post->ID . '&amp;action=delete" style="display: block;">' . __('Delete Ad', 'appthemes') . '</a>';


																} else { ?>

                              <?php if ( get_option('cp_ad_edit') == 'yes' ) : ?><a href="<?php echo CP_EDIT_URL; ?>?aid=<?php the_id(); ?>"><img src="<?php bloginfo('template_directory'); ?>/images/pencil.png" title="" alt="" border="0" /></a>&nbsp;&nbsp;<?php endif; ?>
                              <a onclick="return confirmBeforeDelete();" href="<?php echo CP_DASHBOARD_URL; ?>?aid=<?php the_id(); ?>&amp;action=delete" title="<?php _e('Delete Ad', 'appthemes'); ?>"><img src="<?php bloginfo('template_directory'); ?>/images/cross.png" title="<?php _e('Delete Ad', 'appthemes'); ?>" alt="<?php _e('Delete Ad', 'appthemes'); ?>" border="0" /></a>&nbsp;&nbsp;
                              <a href="<?php echo CP_DASHBOARD_URL; ?>?aid=<?php the_id(); ?>&amp;action=<?php echo $postaction; ?>"><img src="<?php bloginfo('template_directory'); ?>/images/<?php echo $postimage; ?>" title="" alt="" border="0" /></a><br />
                              <?php if ( get_post_meta(get_the_id(), 'cp_ad_sold', true) != 'yes' ) : ?>
                                <a href="<?php echo CP_DASHBOARD_URL; ?>?aid=<?php the_id(); ?>&amp;action=setSold"><?php _e('Mark Sold', 'appthemes'); ?></a>
                              <?php else : ?>
                                <a href="<?php echo CP_DASHBOARD_URL; ?>?aid=<?php the_id(); ?>&amp;action=unsetSold"><?php _e('Unmark Sold', 'appthemes'); ?></a>
                              <?php endif; ?>
                          <?php } ?>


                            </td>
                        </tr>

                        <?php endwhile; ?>
						
							<tr>
								<td id="paging-td" colspan="5">

									<?php if(function_exists('appthemes_pagination')) appthemes_pagination(); ?>
									
								</td>
							</tr>

              <script type="text/javascript">
                /* <![CDATA[ */
                  function confirmBeforeDelete() { return confirm("<?php _e('Are you sure you want to delete this ad?', 'appthemes'); ?>"); }
                /* ]]> */
              </script>

                    <?php else : ?>

                        <tr class="even">
                            <td colspan="5">

                                <div class="pad10"></div>

								<p class="text-center"><?php _e('You currently have no classified ads.','appthemes');?></p>

								<div class="pad25"></div>

							</td>
                        </tr>

                    <?php endif; ?>

					<?php wp_reset_query(); ?>

                    </tbody>
                </table>


            </div><!-- /shadowblock -->

            </div><!-- /shadowblock_out -->



        </div><!-- /content_left -->


        <?php get_sidebar('user'); ?>

        <div class="clr"></div>


      </div><!-- /content_res -->

    </div><!-- /content_botbg -->

  </div><!-- /content -->


<?php get_footer(); ?>
