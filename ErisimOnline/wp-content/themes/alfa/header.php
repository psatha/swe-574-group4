<?php global $app_abbr; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

    <head profile="http://gmpg.org/xfn/11">

        <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

        <title><?php wp_title('|',true,'right'); ?><?php bloginfo('name'); ?></title>

        <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php if ( get_option('feedburner_url') <> "" ) echo get_option('feedburner_url'); else echo get_bloginfo_rss('rss2_url').'?post_type='.APP_POST_TYPE; ?>" />
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
        <?php if ( file_exists(TEMPLATEPATH.'/images/favicon.ico') ) { ?><link rel="shortcut icon" href="<?php bloginfo('stylesheet_directory'); ?>/images/favicon.ico" type="image/x-icon" /><?php } ?>

        <?php if ( is_singular() && get_option('thread_comments') ) wp_enqueue_script('comment-reply'); ?>
            
        <?php wp_head(); ?>


		<?php if ( is_single() ) { ?>
    		<meta property="og:image" content="<?php rb_get_image_url_feat($post->ID, 'thumbnail', 'preview', 1); ?>"/>
        	<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
    	<?php } else { ?>
    		<meta property="og:image" content="<?php echo get_option('home'); ?>/wp-content/themes/lynchburglist/images/no_pic_150x150.png"/>
    	<?php } ?>


    </head>


    <body <?php body_class(); ?>>
	
	<div id="wrapper">

    <div class="container">

		<?php if ( get_option('cp_debug_mode') == 'yes' ) { ?><div class="debug"><h3><?php _e('Debug Mode On','appthemes'); ?></h3><?php print_r($wp_query->query_vars); ?></div><?php } ?>

      <!-- HEADER -->
      <div class="header">

        <div class="header_top">

          <div class="header_top_res">

              <?php if ( is_user_logged_in() ) { ?>
              <p>
              Hoşgeldin Sayın <strong><?php global $current_user; get_currentuserinfo(); echo $current_user->user_login; ?></strong>,&nbsp;&nbsp;
              <a class="rb_dashboard" href="<?php echo CP_DASHBOARD_URL ?>">Bana Özel</a>&nbsp;&nbsp;
              <a class="rb_logout" href="<?php echo wp_logout_url(); ?>"><?php _e('Log out','appthemes'); ?></a>&nbsp;&nbsp;
              <a href="<?php if (get_option('cp_feedburner_url')) echo get_option('cp_feedburner_url'); else echo get_bloginfo_rss('rss2_url').'?post_type='.APP_POST_TYPE; ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/icon_rss.gif" width="16" height="16" alt="rss" class="srvicon" /></a>
              <?php if ( get_option('cp_twitter_username') ) : ?>
              &nbsp;
              <a href="http://twitter.com/<?php echo get_option('cp_twitter_username'); ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/icon_twitter.gif" width="16" height="16" alt="tw" class="srvicon" /></a>
              <?php endif; ?>
              </p>
              
              <?php } else { ?>
              
              <div class="rb_login_form">
              	<div class="rb_rss">
              		<a href="<?php if (get_option('cp_feedburner_url')) echo get_option('cp_feedburner_url'); else echo get_bloginfo_rss('rss2_url').'?post_type='.APP_POST_TYPE; ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/icon_rss.gif" width="16" height="16" alt="rss" class="srvicon" /></a>
                    <?php if ( get_option('cp_twitter_username') ) : ?>
                    &nbsp;
                    <a href="http://twitter.com/<?php echo get_option('cp_twitter_username'); ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/icon_twitter.gif" width="16" height="16" alt="tw" class="srvicon" /></a>
                    <?php endif; ?>
                </div>
              	<form action="<?php echo get_option('home'); ?>/wp-login.php" method="post" class="rb_loginform">
                    	<div class="rb_user">
                        	<label for="login_username">Adınız: </label>
                            <input type="text" class="text" name="log" id="login_username" value="<?php if (isset($posted['login_username'])) echo $posted['login_username']; ?>" />
                        </div>
                        <div class="rb_password">
                        	<label for="login_password">Şifre: </label>
                            <input type="password" class="text" name="pwd" id="login_password" value="" />
                        </div>
                        <div class="rb_btn_login">
                        	<input type="submit" class="rb_btn_login" name="login" id="login" value="Giriş Yap" />
                            <input type="hidden" name="redirect_to" value="<?php echo get_option('home')?>" />
                            <input type="hidden" name="testcookie" value="1" />
                        </div>
                </form>&nbsp;&nbsp;
              <p class="rb_login_text">Hoşgeldin <strong>Ziyaretçi</strong>&nbsp;&nbsp;<a class="rb_dashboard" href="<?php bloginfo('url'); ?>/wp-login.php?action=register">Hemen Üye Ol</a>&nbsp;&nbsp;&nbsp;&nbsp;Zaten Üye iseniz? </p>
              </div>
              
              <?php } ?>

          </div><!-- /header_top_res -->

        </div><!-- /header_top -->


        <div class="header_main">

          <div class="header_main_bg">

            <div class="header_main_res">

                <div id="logo">

                    <?php if ( get_option('cp_use_logo') != 'no' ) { ?>

                        
                        <?php if ( get_option('cp_logo') ) { ?>
                            <a href="<?php bloginfo('url'); ?>"><img src="<?php echo get_option('cp_logo'); ?>" alt="<?php bloginfo('name'); ?>" class="header-logo" /></a>
                        <?php } else { ?>
                            <a href="/"><div class="cp_logo"></div></a>
                        <?php } ?>

                    <?php } else { ?>

                        <h1><a href="<?php echo get_option('home'); ?>/"><?php bloginfo('name'); ?></a></h1>
                        <div class="description"><?php bloginfo('description'); ?></div>

                    <?php } ?>

                    
                </div>

                <?php if ( get_option('cp_adcode_468x60_enable') == 'yes' ) { ?>

                    <div class="adblock">

                        <?php appthemes_header_ad_468x60();?>

                    </div><!-- /adblock -->

              <?php } ?>

             <div class="clr"></div>

            </div><!-- /header_main_res -->

          </div><!-- /header_main_bg -->

        </div><!-- /header_main -->


        <div class="header_menu">

          <div class="header_menu_res">

              <a href="<?php echo CP_ADD_NEW_URL ?>" class="obtn btn_orange"><?php _e('Post an Ad', 'appthemes') ?></a>

            <ul id="nav"> 
			  			
              <li class="<?php if (is_home()) echo 'page_item current_page_item'; ?>"><a href="<?php echo get_option('home')?>"><?php _e('Home','appthemes'); ?></a></li>
              <li class="mega"><a href="#"><?php _e('Categories','appthemes'); ?></a>
                  <div class="adv_categories" id="adv_categories">

                        <?php echo cp_cat_menu_drop_down( get_option('cp_cat_menu_cols'), get_option('cp_cat_menu_sub_num') ); ?>

                  </div><!-- /adv_categories -->
              </li>
			
            </ul>
            
			<?php wp_nav_menu( array('theme_location' => 'primary', 'fallback_cb' => 'appthemes_default_menu', 'container' => false) ); ?>


            <div class="clr"></div>

            

          </div><!-- /header_menu_res -->

        </div><!-- /header_menu -->

      </div><!-- /header -->

	<?php include_once( TEMPLATEPATH . '/includes/theme-searchbar.php' ); ?>