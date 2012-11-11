<?php

add_action( 'init', 'appthemes_load_gateways', 9 );

function appthemes_load_gateways(){

	do_action( 'appthemes_load_gateways' );

}

function appthemes_register_gateway( $class_name ) {

	return APP_Gateway_Registry::register_gateway( $class_name );	
	
}

function appthemes_get_registered_gateways() {

	return APP_Gateway_Registry::get_gateways();
	
}

function appthemes_get_registered_gateway( $gateway_id ) {

	return APP_Gateway_Registry::get_gateway( $gateway_id );
	
}

function appthemes_get_gateway_options( $gateway_id ) {

	return APP_Gateway_Registry::get_gateway_options( $gateway_id );
	
}

function appthemes_get_active_gateways() {

	return APP_Gateway_Registry::get_active_gateways();

}

function appthemes_is_gateway_enabled( $identifier ) {
	
	return APP_Gateway_Registry::is_gateway_enabled( $identifier );
	
}

function appthemes_process_gateway( $gateway_id, $order ) {

	$options = APP_Gateway_Registry::get_gateway_options( $gateway_id );
	$gateway = APP_Gateway_Registry::get_gateway( $gateway_id );

	$gateway->process( $order, $options );
}

function appthemes_list_gateway_dropdown( $input_name = 'payment_gateway' ) {

	$gateways = array();
	foreach ( APP_Gateway_Registry::get_gateways() as $gateway ) {

		// Skip disabled gateways
		if ( !APP_Gateway_registry::is_gateway_enabled( $gateway->identifier() ) ) {
			continue;
		}
		$gateways[ $gateway->identifier() ] = $gateway->display_name( 'dropdown' );
	}

	echo scbForms::input( array(
		'type' => 'select',
		'name' => $input_name,
		'values' => $gateways,
		'extra' => array( 'class' => 'required' )
	) );
}