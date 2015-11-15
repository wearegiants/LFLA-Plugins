<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_Backorders_Allowed extends CPAC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-backorders_allowed';
		$this->properties['label']	= __( 'Backorders Allowed', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 1.0
	 */
	public function get_value( $post_id ) {

		$backorders_status = $this->get_raw_value( $post_id );

		switch ( $backorders_status ) {
			case 'no':
				$value = '<div class="dashicons dashicons-no cpac-tip" data-tip="' . __( 'No' ) . '"></div>';
				break;
			case 'yes':
				$value = '<div class="dashicons dashicons-yes cpac-tip" data-tip="' . __( 'Yes' ) . '"></div>';
				break;
			case 'notify':
				$value = '<div class="cpac-tip" data-tip="' . __( 'Yes, but notify customer', 'woocommerce' ) . '"><div class="dashicons dashicons-yes"></div><div class="dashicons dashicons-email-alt"></div></div>';
				break;
		}

		return $value;
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.0
	 */
	public function get_raw_value( $post_id ) {

		$product = get_product( $post_id );

		return $product->backorders;
	}

}