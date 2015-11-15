<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_Stock_Status extends CPAC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-stock-status';
		$this->properties['label']	= __( 'Stock status', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 2.0.0
	 */
	public function get_value( $post_id ) {

		$stock_status = $this->get_raw_value( $post_id );

		$value = '&ndash;';

		$data_tip = '';
		$product = get_product( $post_id );

		if ( $product->is_type( 'variable', 'grouped', 'external' ) ) {
			$data_tip = ' (<em>' . __( 'Stock status editing not supported for variable, grouped and external products.', 'cpac' ) . '</em>)';
		}

		if ( 'instock' == $stock_status ) {
			$value = '<span class="cpac-tip" data-tip="' . esc_attr__( 'In stock', 'cpac' ) . $data_tip . '">' . $this->get_asset_image( 'checkmark.png', $stock_status ) . '</span>';
		}
		else if ( 'outofstock' == $stock_status ) {
			$value = '<span class="cpac-tip" data-tip="' . esc_attr__( 'Out of stock', 'woocommerce' ) . $data_tip . '">' . $this->get_asset_image( 'no.png', $stock_status ) . '</span>';
		}
		else {
		}

		return $value;
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 2.0.3
	 */
	public function get_raw_value( $post_id ) {

		$product = get_product( $post_id );

		return $product->stock_status;
	}

}