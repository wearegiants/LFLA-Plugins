<?php
/**
 * Bundled Item Title.
 * @version 4.7.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( $title === '' ) return;

?><h4 class="bundled_product_title product_title"><?php
		echo $title . ( $quantity > 1 ? ' &times; ' . $quantity : '' ) . ( $optional ? __( ' &ndash; optional', 'woocommerce-product-bundles' ) : '' );
?></h4>
