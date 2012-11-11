<?php

require dirname( __FILE__ ) . '/load-p2p.php';

require dirname( __FILE__ ) . '/payments/currencies.php';

require dirname( __FILE__ ) . '/payments/gateway-class.php';
require dirname( __FILE__ ) . '/payments/gateway-registry.php';
require dirname( __FILE__ ) . '/payments/gateway-functions.php';
require dirname( __FILE__ ) . '/payments/order-class.php';
require dirname( __FILE__ ) . '/payments/order-functions.php';
require dirname( __FILE__ ) . '/payments/order-factory.php';
require dirname( __FILE__ ) . '/payments/paypal/paypal.php';
require dirname( __FILE__ ) . '/payments/paypal/paypal-pdt.php';

if ( defined( 'WP_DEBUG' ) && WP_DEBUG )
	require dirname( __FILE__ ) . '/payments/test.php';

