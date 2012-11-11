<?php

class APP_Paypal_PDT{

	static private $options;
	static private $post_urls = array(
		'sandbox' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
		'live' => 'https://www.paypal.com/cgi-bin/webscr'
	);

	static public function init( $options ){
		self::$options = $options;
	}
	
	static public function is_valid_transaction( $order, $options ){
		
		$transaction_id = $_GET['tx'];
		$identity_token = $options['pdt_key'];
		
		if( !self::validate_transaction( $order, $transaction_id, $identity_token ) ){
			return false;
		}
		
		wp_update_post( array(
			"ID" => $order->get_id(),
			"post_content" => $transaction_id
		));
		
		return true;
	}
	
	static private function validate_transaction( $order, $transaction_id, $identity_token ) {

		$data = array(
			'cmd' => '_notify-synch',
			'tx' => $transaction_id,
			'at' => $identity_token
		);

		$response = self::get_transaction( $data );
		if( $response == false )
			return false;
		
		// Check that the transaction is for the right order id
		if( $response['item_number'] != $order->get_id() )
			return false;

		return true;

	}
	
	static private function get_transaction( $data, $sandbox = false ){

		$url = ( self::is_sandbox() ) ? self::$post_urls['sandbox'] : self::$post_urls['live'];	
		$options = array(
			'method' => 'POST',
			'body' => $data,
			'sslverify' => false,
		);

		$response =  self::get_url( $url, $options );
	
		$values = array();
		if ( strpos( $response, 'SUCCESS' ) !== 0 )
			return $values;
			
		$lines = explode( "\n", $response );

		foreach($lines as $string){
		
			$key_value_string = explode( '=', $string );
			
			if( array_key_exists(1, $key_value_string ) )
				$value = $key_value_string[1];
			else
				$value = '';
			
			$values[ $key_value_string[0] ] = $value;
		
		}
		
		return $values;
	}
	
	// Returns true if PDT is enabled
	static public function is_enabled(){
		return !empty( self::$options['pdt_enabled'] );
	}

	// Returns true if the request is handled by PDT
	static public function can_be_handled(){
		return isset( $_GET['tx'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'paypal');
	}
	
	// Returns true if in sandbox mode
	static public function is_sandbox(){
		return !empty( self::$options['sandbox_enabled'] );
	}
	
	// Returns the body of the requested URL
	static private function get_url( $url, $options ){
		$response = wp_remote_post( $url, $options );
		return $response['body'];
	}

}

?>