<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_Order_Discount extends CPAC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-order_discount';
		$this->properties['label']	= __( 'Order Discount', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 1.0
	 */
	public function get_value( $post_id ) {
		$order = new WC_Order( $post_id );

		if ( ! cpac_is_wc_version_gte( '2.3' ) ) {
			return $order->get_order_discount_to_display();
		}
		return $order->get_discount_to_display();
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.0
	 */
	public function get_raw_value( $post_id ) {
		$order = new WC_Order( $post_id );

		if ( ! cpac_is_wc_version_gte( '2.3' ) ) {
			return $order->get_order_discount();
		}
		return $order->get_discount();
	}
}