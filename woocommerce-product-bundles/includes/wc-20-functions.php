<?php
/**
 * Bundles WC 2.0 Compatibility Functions
 * @version 4.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

function wc_bundles_delimiter() {

	return '|';
}

function wc_bundles_attribute_label( $arg ) {

	global $woocommerce;

	return $woocommerce->attribute_label( $arg );
}

function wc_bundles_attribute_order_by( $arg ) {

	global $woocommerce;

	return $woocommerce->attribute_orderby( $arg );
}

function wc_bundles_get_template( $file, $data, $empty, $path ) {

	return woocommerce_get_template( $file, $data, $empty, $path );
}

function wc_bundles_price( $arg ) {

	return woocommerce_price( $arg );
}

function wc_bundles_add_order_item_meta( $id, $name, $meta ) {

	return woocommerce_add_order_item_meta( $id, $name, $meta );
}

function wc_bundles_add_admin_error( $error ) {

	global $woocommerce_errors;

	$woocommerce_errors[] = $error;

	return $error;
}

function wc_bundles_add_notice( $message, $notice_type ) {

	global $woocommerce;

	if ( $notice_type == 'success' || $notice_type == 'notice' )
		return $woocommerce->add_message( $message );
	elseif ( $notice_type == 'error' )
		return $woocommerce->add_error( $message );
}

function wc_bundles_format_decimal( $number ) {

	return woocommerce_format_total( $number );

}
