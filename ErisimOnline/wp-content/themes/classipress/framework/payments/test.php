<?php

class APP_TestGateway extends APP_Gateway{

	public function __construct() {
		parent::__construct( 'test-gateway', 'TestGateway' );
	}

	public function form() {

		$form_values = array(

			array(
				'title' => __( 'Empty Field', APP_TD ),
				'type' => 'text',
				'name' => 'empty_field',
				'extra' => array( 'size' => 50 )
			),

		);

		$return_array = array(
			"title" => "General Information",
			"fields" => $form_values
		);

		return $return_array;

	}

	public function process( $order, $options ) {

		echo "<br>-------<br>";

		echo "Total Cost: " . $order->get_total() . "</br>";

		echo "<a href='" . $order->get_return_url() . "&response=approve'>Approve</a></br>";

		if ( !empty( $_GET['response'] ) ) {
			if ( $_GET['response'] == "approve" ) {
				// for debugging
				wp_update_post( array(
					"ID" => $order->get_id(),
					"post_status" => 'draft'
				) );

				$order->complete();
			}
		}
	}
}

appthemes_register_gateway( 'APP_TestGateway' );

