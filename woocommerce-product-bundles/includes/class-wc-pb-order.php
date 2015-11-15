<?php
/**
 * Product Bundle order functions and filters.
 *
 * @class 	WC_PB_Order
 * @version 4.7.0
 * @since   4.5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class WC_PB_Order {

	/**
	 * Setup order class
	 */
	function __construct() {

		global $woocommerce;

		// Filter price output shown in cart, review-order & order-details templates
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'woo_bundles_order_item_subtotal' ), 10, 3 );

		// Bundle containers should not affect order status
		add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'woo_bundles_container_items_need_no_processing' ), 10, 3 );

		// Modify order items to include bundle meta - 3rd argument exists only in WC 2.1
		if ( version_compare( $woocommerce->version, '2.0.22' ) > 0 )
			add_action( 'woocommerce_add_order_item_meta', array( $this, 'woo_bundles_add_order_item_meta' ), 10, 3 );
		else
			add_action( 'woocommerce_add_order_item_meta', array( $this, 'woo_bundles_add_order_item_meta' ), 10, 2 );

		// Hide bundle configuration metadata in order line items
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woo_bundles_hidden_order_item_meta' ) );

		// Filter order item count
		add_filter( 'woocommerce_get_item_count',  array( $this, 'woo_bundles_order_item_count' ), 10, 3 );
		add_filter( 'woocommerce_admin_order_item_count',  array( $this, 'woo_bundles_order_item_count_string' ), 10, 2 );
		add_filter( 'woocommerce_admin_html_order_item_class',  array( $this, 'woo_bundles_html_order_item_class' ), 10, 2 );
		add_filter( 'woocommerce_admin_order_item_class',  array( $this, 'woo_bundles_html_order_item_class' ), 10, 2 );
	}

	/**
	 * Find the parent of a bundled item in an order.
	 *
	 * @param  array    $item
	 * @param  WC_Order $order
	 * @return array
	 */
	function get_bundled_order_item_container( $item, $order ) {

		global $woocommerce_bundles;

		// find container item
		foreach ( $order->get_items() as $order_item ) {

			if ( $woocommerce_bundles->helpers->is_wc_21() && isset( $order_item[ 'bundle_cart_key' ] ) )
				$is_parent = $item[ 'bundled_by' ] == $order_item[ 'bundle_cart_key' ] ? true : false;
			else
				$is_parent = isset( $order_item[ 'stamp' ] ) && $order_item[ 'stamp' ] == $item[ 'stamp' ] && ! isset( $order_item[ 'bundled_by' ] ) ? true : false;

			if ( $is_parent ) {

				$parent_item = $order_item;

				return $parent_item;
			}
		}

		return false;
	}

	/**
	 * Modify the subtotal of order-items (order-details.php) depending on the bundles's pricing strategy.
	 *
	 * @param  string   $subtotal   the item subtotal
	 * @param  array    $item       the items
	 * @param  WC_Order $order      the order
	 * @return string               modified subtotal string.
	 */
	function woo_bundles_order_item_subtotal( $subtotal, $item, $order ) {

		global $woocommerce_bundles;

		// If it's a bundled item
		if ( isset( $item[ 'bundled_by' ] ) ) {

			// find bundle parent
			$parent_item = $this->get_bundled_order_item_container( $item, $order );

			$per_product_pricing = ! empty( $parent_item ) && isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $bundle_product_id, '_per_product_pricing_active', true );

			if ( $per_product_pricing == 'no' || isset( $parent_item[ 'composite_parent' ] ) )

				return '';

			else {

				/*------------------------------------------------------------------------------------------------------------------------------------------*/
				/* woocommerce_order_formatted_line_subtotal is filtered by WC_Subscriptions_Order::get_formatted_line_total.
				/* The filter is temporarily unhooked internally, when the function calls get_formatted_line_subtotal to fetch the recurring price string.
				/* Here, we check whether it's unhooked to avoid displaying the "Subtotal" string next to the recurring part.
				/*------------------------------------------------------------------------------------------------------------------------------------------*/

				if ( $woocommerce_bundles->compatibility->is_item_subscription( $order, $item ) && ! has_filter( 'woocommerce_order_formatted_line_subtotal', 'WC_Subscriptions_Order::get_formatted_line_total' ) )
					return $subtotal;
				else
					return  '<small>' . __( 'Subtotal', 'woocommerce-product-bundles' ) . ': ' . $subtotal . '</small>';
			}
		}

		// If it's a bundle (parent item)
		if ( ! isset( $item[ 'bundled_by' ] ) && isset( $item[ 'stamp' ] ) ) {

			if ( isset( $item[ 'subtotal_updated' ] ) )
				return $subtotal;

			$sub_item_id	= '';

			foreach ( $order->get_items() as $order_item_id => $order_item ) {

				if ( $woocommerce_bundles->helpers->is_wc_21() && isset( $order_item[ 'bundle_cart_key' ] ) )
					$is_child = in_array( $order_item[ 'bundle_cart_key' ], unserialize( $item[ 'bundled_items' ] ) ) ? true : false;
				else
					$is_child = isset( $order_item[ 'stamp' ] ) && $order_item[ 'stamp' ] == $item[ 'stamp' ] && isset( $order_item[ 'bundled_by' ] ) ? true : false;

				if ( $is_child ) {

					if ( $woocommerce_bundles->compatibility->is_item_subscription( $order, $order_item ) )
						$sub_item_id = $order_item_id;

					$item[ 'line_subtotal' ] 		+= $order_item[ 'line_subtotal' ];
					$item[ 'line_subtotal_tax' ] 	+= $order_item[ 'line_subtotal_tax' ];

				}
			}

			$item[ 'subtotal_updated' ] = 'yes';

			if ( ! empty( $sub_item_id ) ) {

				// order's price string with modified bundle line subtotal
				return WC_Subscriptions_Order::get_formatted_order_total( $order->get_formatted_line_subtotal( $item ), $order );

			} else {

				// modified bundle line subtotal
				return $order->get_formatted_line_subtotal( $item );
			}

		}

		return $subtotal;
	}

	/**
	 * Filters the reported number of order items.
	 * Do not count bundled items.
	 *
	 * @param  int          $count      initial reported count
	 * @param  string       $type       line item type
	 * @param  WC_Order     $order      the order
	 * @return int                      modified count
	 */
	function woo_bundles_order_item_count( $count, $type, $order ) {

		$subtract = 0;

		foreach ( $order->get_items() as $item ) {

			// If it's a bundled item
			if ( isset( $item[ 'bundled_by' ] ) )
				$subtract += $item[ 'qty' ];
		}

		$new_count = $count - $subtract;

		return $new_count;

	}

	/**
	 * Filters the string of order item count.
	 * Include bundled items as a suffix.
	 *
	 * @see  woo_bundles_order_item_count
	 * @param  int          $count      initial reported count
	 * @param  WC_Order     $order      the order
	 * @return int                      modified count
	 */
	function woo_bundles_order_item_count_string( $count, $order ) {

		$add = 0;

		foreach ( $order->get_items() as $item ) {

			// If it's a bundled item
			if ( isset( $item[ 'bundled_by' ] ) )
				$add += $item[ 'qty' ];
		}

		if ( $add > 0 )
			return sprintf( __( '%1$s, %2$s bundled', 'woocommerce-product-bundles' ), $count, $add );

		return $count;

	}

	/**
	 * Filters the order item admin class.
	 *
	 * @param  string       $class     class
	 * @param  array        $item      the order item
	 * @return string                  modified class
	 */
	function woo_bundles_html_order_item_class( $class, $item ) {

		// If it's a bundled item
		if ( isset( $item[ 'bundled_by' ] ) )
			return $class . ' bundled_item';

		return $class;

	}

	/**
	 * Bundle Containers need no processing - let it be decided by bundled items only.
	 *
	 * @param  boolean      $is_needed   product needs processing: true/false
	 * @param  WC_Product   $product     the product
	 * @param  int          $order_id    the order id
	 * @return boolean                   modified product needs processing status
	 */
	function woo_bundles_container_items_need_no_processing( $is_needed, $product, $order_id ) {

		if ( $product->is_type( 'bundle' ) ) {
			return false;
		}

		return $is_needed;
	}

	/**
	 * Hides bundle metadata.
	 *
	 * @param  array    $hidden     hidden meta strings
	 * @return array                modified hidden meta strings
	 */
	function woo_bundles_hidden_order_item_meta( $hidden ) {

		return array_merge( $hidden, array( '_bundled_by', '_per_product_pricing', '_per_product_shipping', '_bundle_cart_key' ) );
	}

	/**
	 * Add bundle info meta to order items.
	 *
	 * @param  int      $order_item_id      order item id
	 * @param  array    $cart_item_values   cart item data
	 * @return void
	 */
	function woo_bundles_add_order_item_meta( $order_item_id, $cart_item_values, $cart_item_key = '' ) {

		global $woocommerce;

		if ( isset( $cart_item_values[ 'bundled_by' ] ) ) {

			wc_bundles_add_order_item_meta( $order_item_id, '_bundled_by', $cart_item_values[ 'bundled_by' ] );

			// not really necessary since we know its going to be there

			$product_key = $woocommerce->cart->find_product_in_cart( $cart_item_values[ 'bundled_by' ] );

			if ( ! empty( $product_key ) ) {

				$product_name = $woocommerce->cart->cart_contents[ $product_key ][ 'data' ]->get_title();

				wc_bundles_add_order_item_meta( $order_item_id, __( 'Included with', 'woocommerce-product-bundles' ), $product_name );
			}

		}

		if ( isset( $cart_item_values[ 'stamp' ] ) && ! isset( $cart_item_values[ 'bundled_by' ] ) ) {

			if ( isset( $cart_item_values[ 'bundled_items' ] ) && ! empty( $cart_item_values[ 'bundled_items' ] ) )
				wc_bundles_add_order_item_meta( $order_item_id, '_bundled_items', $cart_item_values[ 'bundled_items' ] );

			if ( $cart_item_values[ 'data' ]->is_priced_per_product() == true )
				wc_bundles_add_order_item_meta( $order_item_id, '_per_product_pricing', 'yes' );
			else
				wc_bundles_add_order_item_meta( $order_item_id, '_per_product_pricing', 'no' );

			if ( $cart_item_values[ 'data' ]->per_product_shipping_active == true )
				wc_bundles_add_order_item_meta( $order_item_id, '_per_product_shipping', 'yes' );
			else
				wc_bundles_add_order_item_meta( $order_item_id, '_per_product_shipping', 'no' );
		}

		if ( isset( $cart_item_values[ 'stamp' ] ) ) {
			wc_bundles_add_order_item_meta( $order_item_id, '_stamp', $cart_item_values[ 'stamp' ] );

			if ( $cart_item_key !== '' )
				wc_bundles_add_order_item_meta( $order_item_id, '_bundle_cart_key', $cart_item_key );

		}
	}

}
