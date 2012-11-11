<?php

// scbFramework
foreach ( array(
	'scbUtil', 'scbOptions', 'scbForms', 'scbTable',
	'scbWidget', 'scbAdminPage', 'scbBoxesPage',
	'scbCron', 'scbHooks',
) as $className ) {
	if ( !class_exists( $className ) ) {
		include dirname( __FILE__ ) . '/scb/' . substr( $className, 3 ) . '.php';
	}
}

if ( !function_exists( 'scb_init' ) ) :
function scb_init( $callback ) {
	call_user_func( $callback );
}
endif;

require dirname( __FILE__ ) . '/kernel/functions.php';
require dirname( __FILE__ ) . '/kernel/hooks.php';

appthemes_load_textdomain();

require dirname( __FILE__ ) . '/kernel/geo.php';

require dirname( __FILE__ ) . '/kernel/controller-types.php';
require dirname( __FILE__ ) . '/kernel/page-edit-profile.php';

if ( is_admin() ) {
	require dirname( __FILE__ ) . '/admin/functions.php';
	require dirname( __FILE__ ) . '/admin/updater.php';
	require dirname( __FILE__ ) . '/admin/class-dashboard.php';
	require dirname( __FILE__ ) . '/admin/class-tabs-page.php';
}

new APP_User_Profile;

add_filter( 'wp_title', 'appthemes_title_tag', 9 );

add_action( 'wp_head', 'appthemes_favicon' );
add_action( 'admin_head', 'appthemes_favicon' );

