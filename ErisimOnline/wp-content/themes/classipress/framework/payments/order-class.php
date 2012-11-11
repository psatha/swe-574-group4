<?php

final class APP_Order {

	private $id, $author, $ip_addres;
	private $gateway, $state, $currency, $total, $return_url, $cancel_url;
	private $items = array();

	private static $status_map = array(
		'pending' => 'draft',
		'failed' => 'pending',
		'completed' => 'publish'
	);

	public function __construct( $post, $gateway, $ip_address, $currency, $items, $return_url, $cancel_url ) {
	
		$this->id = $post->ID;
		$this->author = $post->post_author;
		$this->ip_address = $ip_address;
		
		$this->state = $post->post_status;

		$this->gateway = $gateway;
		$this->currency = $currency;
		$this->items = $items;

		$this->refresh_total();
		$this->return_url = $return_url;
		$this->cancel_url = $cancel_url;

	}
	
	public function get_id(){
		return $this->id;
	}
	
	public function get_author(){
		return $this->author;
	}
	
	public function get_ip_address(){
		return $this->ip_address;
	}
	
	public function get_return_url(){
		return $this->return_url;
	}
	
	public function get_cancel_url(){
		return $this->cancel_url;
	}
	
	public function get_item( $index = 0 ){
		if( isset( $this->items[ $index ] ) )
			return $this->items[ $index ];
		else
			return false;		
	}
	
	public function get_items(){
		return $this->items;
	}

	public function add_item( $post_id, $price, $addons = array() ) {
	
		$p2p_id = p2p_type( APPTHEMES_ORDER_CONNECTION )->connect( $this->id, $post_id );
		p2p_add_meta( $p2p_id, 'price', $price, true );
		
		$item = array(
			'price' => $price,
			'post' => get_post( $post_id ),
			'addons' => array()
		);

		foreach ( $addons as $addon_id => $addon_price ){
			$item['addons'][] = $this->add_addon( $p2p_id, $addon_id, $addon_price );
		}
			
		$this->items[] = $item;
		$this->refresh_total();

	}

	
	private function add_addon( $connection_id, $addon_id, $price ){
	
		p2p_add_meta( $connection_id, 'addon', $addon_id );
		p2p_add_meta( $connection_id, $addon_id, $addon_price );
		
		return array(
			'addon_id' => $addon_id,
			'price' => $price
		);
	
	}
	
	
	public function get_gateway(){
		return $this->gateway;
	}
	
	public function set_gateway( $gateway_id ) {

		$gateway = appthemes_get_registered_gateway( $gateway_id );
		if ( $gateway == false )
			return false;

		update_post_meta( $this->id, 'gateway', $gateway->identifier() );
		$this->gateway = $gateway->identifier();

		return true;
	}
	
	public function clear_gateway(){
	
		$this->gateway = '';
		update_post_meta( $this->id, 'gateway', '' );
		
		return true;		
	}
	
	public function get_total(){
		return $this->total;
	}
	
	public function get_currency(){
		return $this->currency;
	}
	
	public function refresh_total() {

		$this->total = 0;
		foreach( $this->items as $item ){
		
			$this->total += (int) $item['price'];
			foreach( $item['addons'] as $addon ){
				$this->total += (int) $addon['price'];
			}
		}

		update_post_meta( $this->id, 'total_price', $this->total );
		
	}
	
	public function get_status(){
		foreach( self::$status_map as $state => $status ){
			if( $status == $this->state )
				return $state;
		}
	}
	
	public function complete() {
		$this->set_status( 'completed' );
	}

	private function set_status( $state ) {
		$status = self::$status_map[ $state ];

		if ( $this->state == $status )
			return;

		// Mark Order Status
		wp_update_post( array(
			"ID" => $this->id,
			"post_status" => $status
		) );

		do_action( 'app_transaction_' . $state, $this );
	}
	
}
