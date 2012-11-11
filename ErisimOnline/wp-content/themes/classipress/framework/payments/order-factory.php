<?php

class APP_Order_Factory {

	private static $addons;
	
	public static function init( $connected_ptype ){
	
		p2p_register_connection_type( array(
			'name' => APPTHEMES_ORDER_CONNECTION,
			'from' => APPTHEMES_ORDER_PTYPE,
			'to' => $connected_ptype,
			'cardinality' => 'many-to-many',
			'admin_box' => false
		) );
		
	}
	
	public static function register_addon( $id, $price, $title ){
	
		self::$addons[ $id ] = array(
			'price' => $price,
			'title' => $title
		);
		
	}

	public static function get_addon_price( $addon_id ) {
	
		return self::$addons[ $addon_id ]['price'];
		
	}
	
	public static function get_addon_title( $addon_id ) {
	
		return self::$addons[ $addon_id ]['title'];
		
	}

	public static function build_new() {

		$order_data = array(
			"post_title" => "Transaction",
			"post_content" => "Transaction Data",
			"post_type" => APPTHEMES_ORDER_PTYPE,
		);
		$order_id = wp_insert_post( $order_data );

		$meta = array(
			"addons" => array(),
			"gateway" => "",
			"ip_address" => $_SERVER['REMOTE_ADDR'],
			"currency" => APP_Gateway_Registry::get_options()->currency_code
		);

		foreach ( $meta as $meta_key => $meta_value )
			add_post_meta( $order_id, $meta_key, $meta_value, true );

		return self::retrieve( $order_id );
		
	}

	public static function retrieve( $order_id ) {

		$order_data = get_post( $order_id );
		if ( $order_data->post_type != APPTHEMES_ORDER_PTYPE ) {
			return false;
		}

		$order_gateway = get_post_meta( $order_id, "gateway", true );
		$order_ip_address = get_post_meta( $order_id, "ip_address", true );
		$order_currency = get_post_meta( $order_id, "currency", true );

		$connected = new WP_Query( array(
			'connected_type' => APPTHEMES_ORDER_CONNECTION,
			'connected_from' => $order_id,
			'post_status' => '-1' // TODO: -1?
		) );

		$items = array();

		foreach ( $connected->posts as $post ) {
		
			$meta = p2p_get_meta( $post->p2p_id );
			$item = array(
				'post' => $post,
				'price' => $meta['price'][0],
				'addons' => array()
			);
			
			if( isset( $meta['addon'] ) ){

				foreach ( $meta['addon'] as $addon ) {
					$item['addons'][] = array(
						"addon_id" => $addon,
						"price" => $meta[$addon][0]
					);
				}
			}
			
			$items[] = $item;
			
		}
		
		$return_url = get_permalink( $order_data->ID );
		$cancel_url = add_query_arg( "cancel", 1, $return_url );

		return new APP_Order(
			$order_data,
			$order_gateway,
			$order_ip_address,
			$order_currency,
			$items,
			$return_url,
			$cancel_url
		);
	}
}
