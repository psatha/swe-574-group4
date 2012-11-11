<?php

abstract class APP_Gateway {

	private $display_name;
	private $identifier;

	/**
	 * Creates the Gateway class with the required information to display it
	 *
	 * @param string  $display_name The display name
	 * @param string  $identifier   The unique indentifier used to indentify your payment type
	 */
	public function __construct( $identifier, $display_name ) {

		// Make an array out of a string
		if( !is_array( $display_name ) ){
			$display_name = array(
				'dropdown' => $display_name,
				'admin' => $display_name
			);
		}

		// If not given, use the identifier as the string
		$defaults = array(
			'dropdown' => $identifier,
			'admin' => $identifier
		);

		$this->display_name = wp_parse_args( $display_name, $defaults );

		$this->identifier = $identifier;
	}

	/**
	 * Returns an array representing the form to output for admin configuration
	 */
	public abstract function form();

	/**
	 * Processes a payment using this Gateway
	 */
	public abstract function process( $order, $options );

	/**
	 * Provides the display name for this Gateway
	 *
	 * @return string
	 */
	public final function display_name( $type = 'dropdown' ) {
		return $this->display_name[$type];
	}

	/**
	 * Provides the unique identifier for this Gateway
	 *
	 * @return string
	 */
	public final function identifier() {
		return $this->identifier;
	}

}
