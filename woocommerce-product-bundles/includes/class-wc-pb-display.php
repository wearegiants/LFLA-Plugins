<?php
/**
 * Product Bundle front-end functions and filters.
 *
 * @class 	WC_PB_Display
 * @version 4.7.0
 * @since   4.5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class WC_PB_Display {

	/**
	 * Setup class
	 */
	function __construct() {

		global $woocommerce;

		// Single product template for product bundles
		add_action( 'woocommerce_bundle_add_to_cart', array( $this, 'woo_bundles_add_to_cart' ) );

		// Filter add_to_cart_url & add_to_cart_text when product type is 'bundle'
		if ( version_compare( $woocommerce->version, '2.0.22' ) < 0 ) {
			add_filter( 'add_to_cart_url', array( $this, 'woo_bundles_loop_add_to_cart_url' ), 10 );
			add_filter( 'add_to_cart_class', array( $this, 'woo_bundles_add_to_cart_class' ), 10 );
			add_filter( 'add_to_cart_text', array( $this, 'woo_bundles_add_to_cart_text' ), 10 );
		} else {
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'woo_bundles_loop_add_to_cart_link' ), 10, 2 );
		}

		// Change the tr class attributes when displaying bundled items in templates
		if ( version_compare( $woocommerce->version, '2.0.22' ) > 0 ) {
			add_filter( 'woocommerce_cart_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
			add_filter( 'woocommerce_order_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
		} else {
		// Deprecated
			add_filter( 'woocommerce_cart_table_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
			add_filter( 'woocommerce_order_table_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
			add_filter( 'woocommerce_checkout_table_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
		}

		// Front end variation select box jquery for multiple variable products
		add_action( 'wp_enqueue_scripts', array( $this, 'woo_bundles_frontend_scripts' ), 100 );

		// QuickView support
		add_action( 'wc_quick_view_enqueue_scripts', array( $this, 'woo_bundles_qv' ) );
	}

	/**
	 * Add-to-cart template for bundle type products.
	 * @return void
	 */
	function woo_bundles_add_to_cart() {

		global $woocommerce, $woocommerce_bundles, $product, $post;

		// Enqueue variation scripts
		wp_enqueue_script( 'wc-add-to-cart-bundle' );

		wp_enqueue_style( 'wc-bundle-css' );

		$bundled_items = $product->get_bundled_items();

		if ( $bundled_items )
			wc_bundles_get_template( 'single-product/add-to-cart/bundle.php', array(
				'available_variations' 		=> $product->get_available_bundle_variations(),
				'attributes'   				=> $product->get_bundle_variation_attributes(),
				'selected_attributes' 		=> $product->get_selected_bundle_variation_attributes(),
				'bundle_price_data' 		=> $product->get_bundle_price_data(),
				'bundled_items' 			=> $bundled_items,
				'bundled_item_quantities' 	=> $product->get_bundled_item_quantities()
			), false, $woocommerce_bundles->woo_bundles_plugin_path() . '/templates/' );

	}

	/**
	 * Replaces add_to_cart button url with something more appropriate.
	 **/
	function woo_bundles_loop_add_to_cart_url( $url ) {

		global $product;

		if ( $product->is_type( 'bundle' ) )
			return $product->add_to_cart_url();

		return $url;
	}

	/**
	 * Adds product_type_simple class for Ajax add to cart when all items are simple.
	 **/
	function woo_bundles_add_to_cart_class( $class ) {

		global $product;

		if ( $product->is_type( 'bundle' ) ) {

			if ( $product->has_variables() )
				return '';
			else
				return $class . ' product_type_simple';
		}

		return $class;
	}

	/**
	 * Replaces add_to_cart text with something more appropriate.
	 **/
	function woo_bundles_add_to_cart_text( $text ) {

		global $product;

		if ( $product->is_type( 'bundle' ) )
			return $product->add_to_cart_text();

		return $text;
	}

	/**
	 * Adds QuickView support
	 */
	function woo_bundles_loop_add_to_cart_link( $link, $product ) {

		if ( $product->is_type( 'bundle' ) ) {

			if ( $product->is_in_stock() && $product->all_items_in_stock() && ! $product->has_variables() )
				return str_replace( 'product_type_bundle', 'product_type_bundle product_type_simple', $link );
			else
				return str_replace( 'add_to_cart_button', '', $link );
		}

		return $link;
	}

	/**
	 * Change the tr class of bundled items in all templates to allow their styling.
	 *
	 * @param  string   $classname      original classname
	 * @param  array    $values         cart item data
	 * @param  string   $cart_item_key  cart item key
	 * @return string                   modified class string
	 */
	function woo_bundles_table_item_class( $classname, $values, $cart_item_key ) {

		if ( isset( $values[ 'bundled_by' ] ) )
			return $classname . ' bundled_table_item';
		elseif ( isset( $values[ 'stamp' ] ) )
			return $classname . ' bundle_table_item';

		return $classname;
	}

	/**
	 * Frontend scripts.
	 *
	 * @return void
	 */
	function woo_bundles_frontend_scripts() {

		global $woocommerce_bundles;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wc-add-to-cart-bundle', $woocommerce_bundles->woo_bundles_plugin_url() . '/assets/js/add-to-cart-bundle' . $suffix . '.js', array( 'jquery', 'wc-add-to-cart-variation' ), $woocommerce_bundles->version, true );
		wp_register_style( 'wc-bundle-css', $woocommerce_bundles->woo_bundles_plugin_url() . '/assets/css/bundles-frontend.css', false, $woocommerce_bundles->version );
		wp_register_style( 'wc-bundle-style', $woocommerce_bundles->woo_bundles_plugin_url() . '/assets/css/bundles-style.css', false, $woocommerce_bundles->version );
		wp_enqueue_style( 'wc-bundle-style' );

		$params = array(
			'i18n_free'                     => __( 'Free!', 'woocommerce' ),
			'i18n_total'                    => __( 'Total', 'woocommerce-product-bundles' ) . ': ',
			'i18n_partially_out_of_stock'   => __( 'Insufficient stock', 'woocommerce-product-bundles' ),
			'i18n_partially_on_backorder'   => __( 'Available on backorder', 'woocommerce-product-bundles' ),
			'i18n_select_options'           => sprintf( __( '<p class="price"><span class="bundle_error">%s</span></p>', 'woocommerce-product-bundles' ), __( 'To continue, please choose product options&hellip;', 'woocommerce-product-bundles' ) ),
			'i18n_unavailable_text'         => sprintf( __( '<p class="price"><span class="bundle_error">%s</span></p>', 'woocommerce-product-bundles' ), __( 'Sorry, this product cannot be purchased at the moment.', 'woocommerce-product-bundles' ) ),
			'currency_symbol'               => get_woocommerce_currency_symbol(),
			'currency_position'             => esc_attr( stripslashes( get_option( 'woocommerce_currency_pos' ) ) ),
			'currency_format_num_decimals'  => absint( get_option( 'woocommerce_price_num_decimals' ) ),
			'currency_format_decimal_sep'   => esc_attr( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ) ),
			'currency_format_thousand_sep'  => esc_attr( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ) ),
			'currency_format_trim_zeros'    => false == apply_filters( 'woocommerce_price_trim_zeros', false ) ? 'no' : 'yes'
		);

		wp_localize_script( 'wc-add-to-cart-bundle', 'wc_bundle_params', $params );

	}

	/**
	 * Load quickview script.
	 */
	function woo_bundles_qv() {

		global $woocommerce_bundles;

		if ( ! is_product() ) {

			$this->woo_bundles_frontend_scripts();

			wp_enqueue_script( 'wc-add-to-cart-bundle' );
			wp_enqueue_style( 'wc-bundle-css' );

		}

	}

}
