<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_Payment_Method extends CPAC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-payment_method';
		$this->properties['label']	= __( 'Payment method', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 1.0
	 */
	public function get_value( $post_id ) {

		return get_post_meta( $post_id, '_payment_method_title', true );
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.0
	 */
	public function get_raw_value( $post_id ) {

		return get_post_meta( $post_id, '_payment_method', true );
	}
}