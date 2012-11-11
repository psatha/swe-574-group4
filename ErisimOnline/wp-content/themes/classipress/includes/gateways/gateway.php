<?php
/*
 * Payment gateway processing happens here
 *
 */

include_once( TEMPLATEPATH . '/includes/gateways/paypal/paypal.php' );
include_once( TEMPLATEPATH . '/includes/gateways/banktransfer/banktransfer.php' );


//membership system does not use $advals for security purposes
if ( !isset( $advals ) )
    $advals = $_POST;


if ( 'banktransfer' != $advals['cp_payment_method'] ) { ?>

    <center>
	    <h2><?php _e('Please wait while we redirect you to our payment page.', 'appthemes');?></h2>
        <div class="payment-loader"></div>
	    <p class="small"><?php _e('(Click the button below if you are not automatically redirected within 5 seconds.)', 'appthemes');?></p>
    </center>

<?php
}


    // determine which payment gateway was selected and serve up the correct script
	if ( !isset( $advals['cp_payment_method'] ) )
	    $advals['cp_payment_method'] = $_POST['cp_payment_method'];


	// membership purchase returns array of order values
	if ( !isset( $post_id ) ) {

	    $order_vals = cp_get_order_pack_vals( $advals );

	// ad listing purchase returns array of order values
	} else {

	    $advals['post_id'] = $post_id;
	    $order_vals = cp_get_order_vals( $advals );

	}

	// do action hook
    cp_action_gateway( $order_vals );



    // switch ( $payment_method ) :
//
//         case 'paypal':
//             if ( file_exists( TEMPLATEPATH . '/includes/gateways/paypal/paypal.php' ) )
//                 include_once( TEMPLATEPATH . '/includes/gateways/paypal/paypal.php' );
//         break;
//
//         case 'banktransfer':
//             if ( file_exists( TEMPLATEPATH . '/includes/gateways/banktransfer/banktransfer.php' ) )
//                 include_once( TEMPLATEPATH . '/includes/gateways/banktransfer/banktransfer.php' );
//         break;
//
//
//
//         default:
//             echo __('Error: No payment gateway can be found or your session has timed out.', 'appthemes');
//         break;
//
//     endswitch;

	//create the checkout button by sending it an appropriate "post or user id" and "pack id"
	//the function will determine the order type based on the pack id, and associate it to the proper ID.
	// echo cp_gateway_button( $related_id );

?>


<div class="pad100"></div>
