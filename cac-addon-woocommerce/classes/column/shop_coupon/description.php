<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly

/**
 * @since 1.0
 */
class CPAC_WC_Column_Post_Description extends CPAC_Column_Default {

	/**
	 * @see CPAC_Column::init()
	 * @since 1.0
	 */
	public function init() {

		parent::init();

		// define properties
		$this->properties['type']	= 'description';
		$this->properties['label']	= __( 'Description', 'cpac' );
		$this->properties['group']	= 'woocommerce-default';
		$this->properties['handle'] = 'description';
	}

	/**
	 * @see CPAC_Column::get_raw_value()
	 * @since 1.0
	 */
	public function get_raw_value( $post_id ) {

		return get_post_field( 'post_excerpt', $post_id );
	}

}