<?php

// initialize all the sidebars so they are widgetized
function cp_sidebars_init() {

    if ( !function_exists('register_sidebars') )
        return;

    register_sidebar(array(
        'name'          => __('Main Sidebar','appthemes'),
        'id'            => 'sidebar_main',
        'description'   => __('This is your main ClassiPress sidebar.','appthemes'),
        'before_widget' => '<div class="shadowblock_out" id="%2$s"><div class="shadowblock">',
        'after_widget'  => '</div><!-- /shadowblock --></div><!-- /shadowblock_out -->',
        'before_title'  => '<h2 class="dotted">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Ad Sidebar','appthemes'),
        'id'            => 'sidebar_listing',
        'description'   => __('This is your ClassiPress single ad listing sidebar.','appthemes'),
        'before_widget' => '<div class="shadowblock_out" id="%2$s"><div class="shadowblock">',
        'after_widget'  => '</div><!-- /shadowblock --></div><!-- /shadowblock_out -->',
        'before_title'  => '<h2 class="dotted">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Page Sidebar','appthemes'),
        'id'            => 'sidebar_page',
        'description'   => __('This is your ClassiPress page sidebar.','appthemes'),
        'before_widget' => '<div class="shadowblock_out" id="%2$s"><div class="shadowblock">',
        'after_widget'  => '</div><!-- /shadowblock --></div><!-- /shadowblock_out -->',
        'before_title'  => '<h2 class="dotted">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Blog Sidebar','appthemes'),
        'id'            => 'sidebar_blog',
        'description'   => __('This is your ClassiPress blog sidebar.','appthemes'),
        'before_widget' => '<div class="shadowblock_out" id="%2$s"><div class="shadowblock">',
        'after_widget'  => '</div><!-- /shadowblock --></div><!-- /shadowblock_out -->',
        'before_title'  => '<h2 class="dotted">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('User Sidebar','appthemes'),
        'id'            => 'sidebar_user',
        'description'   => __('This is your ClassiPress user sidebar.','appthemes'),
        'before_widget' => '<div class="shadowblock_out" id="%2$s"><div class="shadowblock">',
        'after_widget'  => '</div><!-- /shadowblock --></div><!-- /shadowblock_out -->',
        'before_title'  => '<h2 class="dotted">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer','appthemes'),
        'id'            => 'sidebar_footer',
        'description'   => __('This is your ClassiPress footer. You can have up to four items which will display in the footer from left to right.','appthemes'),
        'before_widget' => '<div class="column" id="%2$s">',
        'after_widget'  => '</div><!-- /column -->',
        'before_title'  => '<h2 class="dotted">',
        'after_title'   => '</h2>',
    ));

}
add_action( 'init', 'cp_sidebars_init' );

?>