<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_Transaction_ID extends CPAC_Column {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-transaction_id';
		$this->properties['label']	= __( 'Transaction ID', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

	/**
	 * @see CPAC_Column::get_value()
	 * @since 1.0
	 */
	public function get_value( $post_id ) {

		$transaction_id = $this->get_raw_value( $post_id );

		$value = $transaction_id ? $transaction_id : '&ndash';
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.0
	 */
	public function get_raw_value( $post_id ) {

		return get_post_meta( $post_id, '_transaction_id', true );
	}
}