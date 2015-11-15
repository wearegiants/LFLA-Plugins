<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_Apply_Before_Tax extends CPAC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-apply_before_tax';
		$this->properties['label']	= __( 'Applied before tax', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 2.0.0
	 */
	public function get_value( $post_id ) {

		$applied_before_tax = $this->get_raw_value( $post_id );

		if ( $applied_before_tax == 'yes' ) {
			$value = '<span class="cpac-tip" data-tip="' . esc_attr__( 'The coupon is applied before calculating cart tax.', 'cpac' ) . '">' . $this->get_asset_image( 'checkmark.png', $applied_before_tax ) . '</span>';
		}
		else {
			$value = '<span class="cpac-tip" data-tip="' . esc_attr__( 'The coupon is applied after calculating cart tax.', 'cpac' ) . '">' . $this->get_asset_image( 'no.png', $applied_before_tax ) . '</span>';
		}

		return $value;
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 2.0.3
	 */
	public function get_raw_value( $post_id ) {

		$coupon = new WC_Coupon( get_post_field( 'post_title', $post_id, 'raw' ) );

		return $coupon->apply_before_tax() ? 'yes' : 'no';
	}

}