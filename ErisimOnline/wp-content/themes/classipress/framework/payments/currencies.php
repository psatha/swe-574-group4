<?php

class APP_Currencies{

	private static $currencies;
	public static function init(){
		self::$currencies = array(
			'USD' => array( 
				'symbol' => '&#36;',
				'name' => __( 'US Dollars', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'EUR' => array( 
				'symbol' => '&euro;',
				'name' => __( 'Euros', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'GBP' => array( 
				'symbol' => '&pound;', 
				'name' => __( 'Pounds Sterling', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'AUD' => array( 
				'symbol' => '&#36;', 
				'name' => __( 'Australian Dollars', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'BRL' => array( 
				'symbol' => '&#36;', 
				'name' => __( 'Brazilian Real', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'CAD' => array( 
				'symbol' => '&#36;', 
				'name' => __( 'Canadian Dollars', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'CZK' => array( 
				'symbol' => 'K&#269;', 
				'name' => __( 'Czech Koruna', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'DKK' => array( 
				'symbol' => 'kr', 
				'name' => __( 'Danish Krone', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'HKD' => array( 
				'symbol' => '&#36;', 
				'name' => __( 'Hong Kong Dollar', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'HUF' => array( 
				'symbol' => 'Ft', 
				'name' => __( 'Hungarian Forint', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'ILS' => array( 
				'symbol' => '&#8362;', 
				'name' => __( 'Israeli Shekel', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'JPY' => array( 
				'symbol' => '&yen;', 
				'name' => __( 'Japanese Yen', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'MYR' => array( 
				'symbol' => 'RM', 
				'name' => __( 'Malaysian Ringgits', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'MXN' => array( 
				'symbol' => '&#36;', 
				'name' => __( 'Mexican Peso', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'NZD' => array( 
				'symbol' => '&#36;', 
				'name' => __( 'New Zealand Dollar', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'NOK' => array( 
				'symbol' => 'kr', 
				'name' => __( 'Norwegian Krone', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'PHP' => array( 
				'symbol' => 'P', 
				'name' => __( 'Philippine Pesos', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'PLN' => array( 
				'symbol' => 'z&#322;', 
				'name' => __( 'Polish Zloty', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'SGD' => array( 
				'symbol' => '&#36;', 
				'name' => __( 'Singapore Dollar', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'SEK' => array( 
				'symbol' => 'kr', 
				'name' => __( 'Swedish Krona', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'CHF' => array( 
				'symbol' => 'Fr', 
				'name' => __( 'Swiss Franc', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'TWD' => array( 
				'symbol' => '&#36;', 
				'name' => __( 'Taiwan New Dollar', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'THB' => array( 
				'symbol' => '&#3647;', 
				'name' => __( 'Thai Baht', APP_TD ),
				'display' => '{symbol}{price}'
			),
			'YTL' => array( 
				'symbol' => '&#8356;', 
				'name' => __( 'Turkish Lira', APP_TD ),
				'display' => '{symbol}{price}'
			),
		);
		
	}
	
	public static function get_display( $currency_code ){
	
		$currency_code = strtoupper( $currency_code );
		if( isset( self::$currencies[ $currency_code ] ) ){
			return self::$currencies[ $currency_code ]['display'];
		}
	
	}
	
	public static function get_symbol( $currency_code ){
	
		$currency_code = strtoupper( $currency_code );
		if( isset( self::$currencies[ $currency_code ] ) ){
			return self::$currencies[ $currency_code ]['symbol'];
		}
	
	}
	
	public static function get_name( $currency_code ){
	
		$currency_code = strtoupper( $currency_code );
		if( isset( self::$currencies[ $currency_code ] ) ){
			return self::$currencies[ $currency_code ]['name'];
		}
	
	}
	
	public static function get_current_name(){
	
		return self::get_name( APP_Gateway_Registry::get_options()->currency_code );
		
	}
	
	public static function get_current_symbol(){
	
		return self::get_symbol( APP_Gateway_Registry::get_options()->currency_code );
		
	}
	
	public static function get_currency_string_array(){
	
		$result = array();
		foreach( self::$currencies as $key => $currency ){
			$result[ $key ] = $currency['name'] . ' (' . $currency['symbol'] . ')';
		}
		return $result;
		
	}
	
	public static function get_price( $number, $currency = '' ){
	
		if( empty( $currency ) ){
			$currency = APP_Gateway_Registry::get_options()->currency_code;
		}
		
		$string = self::get_display( $currency );
		
		$search = array( '{symbol}', '{price}' );
		$replace = array( self::get_symbol( $currency ), $number );
		
		return str_replace( $search, $replace, $string );
	
	}

}
add_action( 'init', array( 'APP_Currencies', 'init' ) );