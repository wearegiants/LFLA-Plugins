<?php
/**
 * Bundles WC 2.1 Compatibility Functions
 * @version 4.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

function wc_bundles_delimiter() {

	return WC_DELIMITER;
}

function wc_bundles_attribute_label( $arg ) {

	return wc_attribute_label( $arg );
}

function wc_bundles_attribute_order_by( $arg ) {

	return wc_attribute_orderby( $arg );
}

function wc_bundles_get_template( $file, $data, $empty, $path ) {

	return wc_get_template( $file, $data, $empty, $path );
}

function wc_bundles_price( $arg ) {

	return wc_price( $arg );
}

function wc_bundles_add_order_item_meta( $id, $name, $meta ) {

	return wc_add_order_item_meta( $id, $name, $meta );
}

function wc_bundles_add_admin_error( $error ) {

	WC_Admin_Meta_Boxes::add_error( $error );

	return $error;
}

function wc_bundles_add_notice( $message, $notice_type ) {

	return wc_add_notice( $message, $notice_type );

}

function wc_bundles_format_decimal( $number ) {

	return wc_format_decimal( $number, '' );

}
