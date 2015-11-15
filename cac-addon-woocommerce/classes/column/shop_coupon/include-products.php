<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.1
 */
class CPAC_WC_Column_Post_Include_Products extends CPAC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.1
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-include_products';
		$this->properties['label']	= __( 'Included products', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 1.1
	 */
	public function get_value( $post_id ) {

		$product_ids = $this->get_raw_value( $post_id );
		$products = array();

		foreach ( $product_ids as $id ) {
			if ( ! $id ) {
				continue;
			}

			$title = get_the_title( $id );

			if ( $link = get_edit_post_link( $id ) ) {
				$title = "<a href='{$link}'>{$title}</a>";
			}

			$products[] = $title;
		}

		return implode( ', ', $products );
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.1
	 */
	public function get_raw_value( $post_id ) {

		$coupon = new WC_Coupon( get_post_field( 'post_title', $post_id, 'raw' ) );

		return $coupon->product_ids;
	}

}