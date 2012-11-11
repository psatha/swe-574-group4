<?php global $app_abbr; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

	<title><?php wp_title(''); ?></title>

    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php if ( get_option('feedburner_url') <> "" ) echo get_option('feedburner_url'); else echo get_bloginfo_rss('rss2_url').'?post_type='.APP_POST_TYPE; ?>" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

    <?php if ( is_singular() && get_option('thread_comments') ) wp_enqueue_script('comment-reply'); ?>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <?php appthemes_before(); ?>

        <div class="container">

		    <?php if ( get_option('cp_debug_mode') == 'yes' ) { ?><div class="debug"><h3><?php _e('Debug Mode On','appthemes'); ?></h3><?php print_r($wp_query->query_vars); ?></div><?php } ?>

            <?php appthemes_before_header(); ?>

            <?php appthemes_header(); ?>

            <?php appthemes_after_header(); ?>

	        <?php include_once( TEMPLATEPATH . '/includes/theme-searchbar.php' ); ?>
