<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.1
 */
class CPAC_WC_Column_Post_Order_Status extends CPAC_Column_Default {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.1
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'order_status';
		$this->properties['label']	= __( 'Order Status', 'cpac' );
		$this->properties['group']	= 'woocommerce-default';
		$this->properties['handle']	= 'order_status';
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.1
	 */
	public function get_raw_value( $post_id ) {

		$order = new WC_Order( $post_id );
		return $order->get_status();
	}
}