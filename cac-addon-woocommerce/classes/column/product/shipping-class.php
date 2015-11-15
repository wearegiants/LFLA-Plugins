<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.1
 */
class CPAC_WC_Column_Post_Shipping_Class extends CPAC_WC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.1
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-shipping_class';
		$this->properties['label']	= __( 'Shipping Class', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 1.1
	 */
	public function get_value( $post_id ) {

		$label = '';
		if ( $term = get_term_by( 'id', $this->get_raw_value( $post_id ), 'product_shipping_class' ) ) {
			$label = sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' );
		}
		return $label;
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.1
	 */
	public function get_raw_value( $post_id ) {

		$shipping_id = false;
		if ( $product = $this->get_product( $post_id ) ) {
			$shipping_id = $product->get_shipping_class_id();
		}
		return $shipping_id;
	}
}