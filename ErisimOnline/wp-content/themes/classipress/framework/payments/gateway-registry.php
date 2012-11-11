<?php

class APP_Gateway_Registry{

	public static $options, $gateways;
	
	public static function register_gateway( $class_name ){

		$instance = new $class_name;
		$identifier = $instance->identifier();

		self::$gateways[$identifier] = $instance;
	
	}
	
	public static function get_gateway( $gateway_id ){

		if ( !array_key_exists( $gateway_id, self::$gateways ) )
			return false;

		return self::$gateways[$gateway_id];
	
	}
	
	public static function get_gateways(){
	
		return self::$gateways;
	
	}
	
	public static function get_active_gateways(){
	
		$gateways = array();
		foreach ( self::$gateways as $gateway ) {

			if ( !self::is_gateway_enabled( $gateway->identifier() ) )
				continue;

			$gateways[ $gateway->identifier() ] = $gateway;
		}

		return $gateways;
	
	}
	
	public static function register_options( scbOptions $options ){
	
		self::$options = $options;
		
	}
	
	public static function get_options(){
	
		return self::$options;
		
	}
	
	public static function get_gateway_options( $gateway_id ){

		if ( array_key_exists( $gateway_id, self::$options->gateways ) )
			return self::$options->gateways[$gateway_id];

		return array();
	
	}
	
	public static function is_gateway_enabled( $gateway_id ){

		$enabled_gateways = self::$options->gateways['enabled'];
		return isset( $enabled_gateways[$gateway_id] ) && $enabled_gateways[$gateway_id];
		
	}

}

?>