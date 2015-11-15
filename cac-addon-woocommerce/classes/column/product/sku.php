<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_SKU extends CPAC_Column_Default {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'sku';
		$this->properties['label']	= __( 'SKU', 'cpac' );
		$this->properties['group']	= 'woocommerce-default';
		$this->properties['handle'] = 'sku';
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.0
	 */
	public function get_raw_value( $post_id ) {

		$product = get_product( $post_id );

		return $product->get_sku();
	}

}