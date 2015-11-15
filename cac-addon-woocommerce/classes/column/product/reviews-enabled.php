<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_Reviews_Enabled extends CPAC_Column_Post_Comment_Status {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// Properties
		$this->properties['type']	= 'column-wc-reviews_enabled';
		$this->properties['label']	= __( 'Reviews enabled', 'cpac' );
		$this->properties['group']	= 'woocommerce-custom';
	}

}