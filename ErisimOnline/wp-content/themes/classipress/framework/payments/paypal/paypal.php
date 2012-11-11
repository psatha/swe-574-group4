<?php

class APP_PayPal extends APP_Gateway{

	private $post_urls = array(
		'sandbox' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
		'live' => 'https://www.paypal.com/cgi-bin/webscr'
	);

	public function __construct() {
		parent::__construct( 'paypal', __( 'PayPal', APP_TD ) );
	}

	public function process( $order, $options ) {

		// If available, use PDT as the validator
		APP_Paypal_PDT::init( $options );
		if( APP_Paypal_PDT::is_enabled() ){
			$this->handle_pdt( $order, $options );
			return;
		}

		// Otherwise, validate regularly
		if( $this->can_be_handled() )
			$this->complete_order( $order );
		else
			$this->display_form( $order, $options );

	}

	private function handle_pdt( $order, $options ){

		// Check if it looks like a PDT transaction
		if( APP_Paypal_PDT::can_be_handled() ){

			if( APP_Paypal_PDT::is_valid_transaction( $order, $options ) )
				$this->complete_order( $order );
			else
				$this->fail_order( __( 'PayPal has responded to your transaction as invalid. Please contact site owner.', APP_TD ) );

		}

		// Otherwise send to Paypal
		else
			$this->display_form( $order, $options );

	}

	/**
	 * Displays the Form for Customer Submission
	 */
	public function display_form( $order, $options ) {

		$defaults = array(
			'email_address' => '',
			'currency_code' => 'USD',
			'sandbox_enabled' => false
		);

		$options = wp_parse_args( $options, $defaults );

		$fields = array(

			// No Shipping Required
			'noshipping' => 1,

			// Disable the 'Add Note' Paypal Capability
			'no_note' => 1,

			// Return the buyer to our website via POST, and include variables
			'rm' => 0,

			// Use the 'Buy Now' button as the method of purchase
			'cmd' => '_xclick',

			'charset' => 'utf-8',
		);

		// Item Information
		$first_item = $order->get_item();
		$fields['item_name'] = $first_item['post']->post_title;
		$fields['item_number'] = $order->get_id();

		// Seller Options
		$fields['business'] = $options['email_address'];
		$fields['currency_code'] = APP_Gateway_Registry::get_options()->currency_code;

		// Paypal Options
		$fields['cbt'] = sprintf( __( 'Continue to %s', APP_TD ), get_bloginfo( 'name' ) );

		$site = !empty( $options['sandbox_enabled'] ) ? 'sandbox' : 'live';
		$post_url = $this->post_urls[ $site ];

		$fields['amount'] = $order->get_total();
		$fields['return'] = wp_nonce_url( $order->get_return_url(), 'paypal' );
		$fields['cancel_return'] = $order->get_cancel_url();
		
		echo $this->create_form( $fields, $post_url );
		echo html( 'script', array(), 'setTimeout( \'document.paypal_payform.submit();\' );' );

	}

	private function create_form( $fields, $action ) {

		$html = '';

		foreach ( $fields as $name => $value ) {

			$html .= html( 'input', array(
					'type' => 'hidden',
					'name' => $name,
					'value' => $value
				) );

		}

		$html .= html( 'input', array(
				'type' => 'submit',
				'style' => 'display: none;'
			) );

		$html .= html( 'span', array(
				'class' => 'redirect-text'
				), __( 'You are now being redirected to PayPal.', APP_TD ) );

		return html( 'form', array(
				'action' => $action,
				'name' => 'paypal_payform',
				'id' => 'create-listing',
			), $html );

	}

	public function form() {

		$general = array(
			'title' => __( 'General Information', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'PayPal Email', APP_TD ),
					'tip' => __( 'Enter your PayPal account email address. This is where your money gets sent.', APP_TD ),
					'type' => 'text',
					'name' => 'email_address',
					'extra' => array( 'size' => 50 )
				),
				array(
					'title' => __( 'Sandbox Mode', APP_TD ),
					'desc' => sprintf( __( "You must have a <a target='_new' href='%s'>PayPal Sandbox</a> account setup before using this feature.", APP_TD ), 'http://developer.paypal.com/' ),
					'tip' => __( 'By default PayPal is set to live mode. If you would like to test and see if payments are being processed correctly, check this box to switch to sandbox mode.', APP_TD ),
					'type' => 'checkbox',
					'name' => 'sandbox_enabled'
				)
			)
		);

		$pdt = array(
				'title' => __( 'Payment Data Transfer (PDT)', APP_TD ),
				'fields' => array(
					array(
						'title' => __( 'Enable PDT', APP_TD ),
						'desc' => sprintf( __( 'See our <a href="%s">tutorial</a> on enabling Payment Data Transfer.', APP_TD ), 'http://docs.appthemes.com/tutorials/enable-paypal-pdt-payment-data-transfer/' ),
						'type' => 'checkbox',
						'name' => 'pdt_enabled'
					),
					array(
						'title' => __( 'Identity Token', APP_TD ),
						'type' => 'text',
						'name' => 'pdt_key'
					),
				)
		);

		return array( $general, $pdt );

	}

	private function can_be_handled(){
		return wp_verify_nonce( $_GET['_wpnonce'], 'paypal');
	}

	private function complete_order( $order ){
		$order->complete();
		
		$listing_item = $order->get_item();
		$url = get_permalink( $listing_item['post']->ID );
		$this->js_redirect( $url, __( 'You are now being redirected to your listing.', APP_TD ) );
	}

	private function fail_order( $message ){
		appthemes_display_notice( 'error', $message );
	}

	private function js_redirect( $url, $text ){

		$attributes = array(
			'class' => 'redirect-text'
		);

		echo html( 'span', $attributes, $text );
		echo html( 'script', array(), 'location.href="' . $url . '"' );

	}

}
appthemes_register_gateway( 'APP_PayPal' );
