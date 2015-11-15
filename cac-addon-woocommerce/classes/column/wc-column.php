<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.2
 */
class CPAC_WC_Column extends CPAC_Column {

	/**
	 * @see CPAC_Column::get_product()
	 * @since 1.2
	 */
	public function get_product( $post_id ) {

		if ( function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post_id );
		}

		// WC 2.0
		else {
			$product = get_product( $post_id );
		}
		return $product;
	}
}