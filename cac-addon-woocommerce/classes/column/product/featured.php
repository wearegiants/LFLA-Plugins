<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.2
 */
class CPAC_WC_Column_Post_Featured extends CPAC_Column_Default {

	public function init() {

		parent::init();

		// define properties
		$this->properties['type']	= 'column-wc-featured';
		$this->properties['label']	= __( 'Featured', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	public function get_value( $post_id ) {
		$is_featured = $this->get_raw_value( $post_id );
		return $is_featured ? $this->get_asset_image( 'checkmark.png' ) : $this->get_asset_image( 'no.png' );
	}

	public function get_raw_value( $post_id ) {
		$product = get_product( $post_id );
		return $product->is_featured();
	}
}