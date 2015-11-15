<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * CPAC_ACF_Column_ACF_Field
 *
 * @since 1.0
 */
class CPAC_WC_Column_Post_Weight extends CPAC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-weight';
		$this->properties['label']	= __( 'Weight', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::apply_conditional()
	 * @since 2.2
	 */
	public function apply_conditional() {
		if ( function_exists( 'wc_product_weight_enabled' ) ) {
			return wc_product_weight_enabled();
		}
		return true;
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 1.0
	 */
	public function get_value( $post_id ) {

		return strval( $this->get_raw_value( $post_id ) + 0 ) . ' ' . get_option( 'woocommerce_weight_unit' );
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.0
	 */
	public function get_raw_value( $post_id ) {

		$product = get_product( $post_id );

		if ( $product->is_virtual() ) {
			return;
		}

		return $product->has_weight() ? floatval( $product->get_weight() ) : '';
	}

}