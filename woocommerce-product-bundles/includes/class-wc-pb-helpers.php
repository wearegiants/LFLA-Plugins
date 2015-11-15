<?php
/**
 * Product Bundle Helper Functions.
 *
 * @class 	WC_PB_Helpers
 * @version 4.7.0
 * @since   4.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class WC_PB_Helpers {

	public $is_wc_21;
	public $is_wc_22;

	public $wc_option_calculate_taxes;
	public $wc_option_tax_display_shop;
	public $wc_option_prices_include_tax;

	private $variations_cache = array();

	function __construct() {

		global $woocommerce;

		$this->wc_option_calculate_taxes 	= get_option( 'woocommerce_calc_taxes' );
		$this->wc_option_tax_display_shop 	= get_option( 'woocommerce_tax_display_shop' );
		$this->wc_option_prices_include_tax = get_option( 'woocommerce_prices_include_tax' );

		if ( version_compare( $woocommerce->version, '2.0.22' ) > 0 )
			$this->is_wc_21 = true;
		else
			$this->is_wc_21 = false;

		if ( version_compare( $woocommerce->version, '2.1.15' ) > 0 )
			$this->is_wc_22 = true;
		else
			$this->is_wc_22 = false;

		// Wishlists compatibility
		add_filter( 'woocommerce_wishlist_list_item_price', array( $this, 'woo_bundles_wishlist_list_item_price' ), 10, 3 );
		add_action( 'woocommerce_wishlist_after_list_item_name', array( $this, 'woo_bundles_wishlist_after_list_item_name' ), 10, 2 );

		// Fix microdata price in per product pricing mode
		add_action( 'woocommerce_single_product_summary', array( $this, 'woo_bundles_loop_price_9' ), 9 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'woo_bundles_loop_price_11' ), 11 );

		// Price Filter results
		add_filter( 'woocommerce_price_filter_results', array( $this, 'woo_bundles_price_filter_results' ), 10, 3 );

	}

	/**
	 * WC > 2.0.
	 * @return boolean
	 */
	function is_wc_21() {

		return $this->is_wc_21;
	}

	/**
	 * WC > 2.1.
	 * @return boolean
	 */
	function is_wc_22() {

		return $this->is_wc_22;
	}

	/**
	 * Use it to avoid repeated get_child calls for the same variation.
	 *
	 * @param  int                   $variation_id
	 * @param  WC_Product_Variable   $product
	 * @return WC_Product_Variation
	 */
	function get_variation( $variation_id, $product ) {

		if ( isset( $this->variations_cache[ $variation_id ] ) )
			return $this->variations_cache[ $variation_id ];

		$variation = $product->get_child( $variation_id, array(
			'parent_id' => $product->id,
			'parent' 	=> $product
		) );

		$this->variations_cache[ $variation_id ] = $variation;

		return $variation;
	}

	/**
	 * Bundled product availability that takes quantity into account.
	 *
	 * @param  WC_Product   $product    the product
	 * @param  int          $quantity   the quantity
	 * @return array                    availability data
	 */
	function get_bundled_product_availability( $product, $quantity ) {

		$availability = $class = '';

		if ( $product->managing_stock() ) {

			if ( $product->is_in_stock() && $product->get_total_stock() > get_option( 'woocommerce_notify_no_stock_amount' ) && $product->get_total_stock() >= $quantity ) {

				switch ( get_option( 'woocommerce_stock_format' ) ) {

					case 'no_amount' :
						$availability = __( 'In stock', 'woocommerce' );
					break;

					case 'low_amount' :
						if ( $product->get_total_stock() <= get_option( 'woocommerce_notify_low_stock_amount' ) ) {
							$availability = sprintf( __( 'Only %s left in stock', 'woocommerce' ), $product->get_total_stock() );

							if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
								$availability .= ' ' . __( '(can be backordered)', 'woocommerce' );
							}
						} else {
							$availability = __( 'In stock', 'woocommerce' );
						}
					break;

					default :
						$availability = sprintf( __( '%s in stock', 'woocommerce' ), $product->get_total_stock() );

						if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
							$availability .= ' ' . __( '(can be backordered)', 'woocommerce' );
						}
					break;
				}

				$class        = 'in-stock';

			} elseif ( $product->backorders_allowed() && $product->backorders_require_notification() ) {

				if ( $product->get_total_stock() >= $quantity || get_option( 'woocommerce_stock_format' ) == 'no_amount' )
					$availability = __( 'Available on backorder', 'woocommerce' );
				else
					$availability = __( 'Available on backorder', 'woocommerce' ) . ' ' . sprintf( __( '(only %s left in stock)', 'woocommerce-product-bundles' ), $product->get_total_stock() );

				$class        = 'available-on-backorder';

			} elseif ( $product->backorders_allowed() ) {

				$availability = __( 'In stock', 'woocommerce' );
				$class        = 'in-stock';

			} else {

				if ( $product->is_in_stock() && $product->get_total_stock() > get_option( 'woocommerce_notify_no_stock_amount' ) ) {

					if ( get_option( 'woocommerce_stock_format' ) == 'no_amount' )
						$availability = __( 'Insufficient stock', 'woocommerce-product-bundles' );
					else
						$availability = __( 'Insufficient stock', 'woocommerce-product-bundles' ) . ' ' . sprintf( __( '(only %s left in stock)', 'woocommerce-product-bundles' ), $product->get_total_stock() );

					$class        = 'out-of-stock';

				} else {

					$availability = __( 'Out of stock', 'woocommerce' );
					$class        = 'out-of-stock';
				}
			}

		} elseif ( ! $product->is_in_stock() ) {

			$availability = __( 'Out of stock', 'woocommerce' );
			$class        = 'out-of-stock';
		}

		return apply_filters( 'woocommerce_get_bundled_product_availability', array( 'availability' => $availability, 'class' => $class ), $product );
	}

	/**
	 * Updates post_meta v1 storage scheme (scattered post_meta) to v2 (serialized post_meta)
	 * @param  int    $bundle_id     bundle product_id
	 * @return void
	 */
	function serialize_bundle_meta( $bundle_id ) {

		global $wpdb;

		$bundled_item_ids 	= maybe_unserialize( get_post_meta( $bundle_id, '_bundled_ids', true ) );
		$default_attributes = maybe_unserialize( get_post_meta( $bundle_id, '_bundle_defaults', true ) );
		$allowed_variations = maybe_unserialize( get_post_meta( $bundle_id, '_allowed_variations', true ) );

		$bundle_data = array();

		foreach ( $bundled_item_ids as $bundled_item_id ) {

			$bundle_data[ $bundled_item_id ] = array();

			$filtered 			= get_post_meta( $bundle_id, 'filter_variations_' . $bundled_item_id, true );
			$o_defaults			= get_post_meta( $bundle_id, 'override_defaults_' . $bundled_item_id, true );
			$hide_thumbnail		= get_post_meta( $bundle_id, 'hide_thumbnail_' . $bundled_item_id, true );
			$item_o_title 		= get_post_meta( $bundle_id, 'override_title_' . $bundled_item_id, true );
			$item_title 		= get_post_meta( $bundle_id, 'product_title_' . $bundled_item_id, true );
			$item_o_desc 		= get_post_meta( $bundle_id, 'override_description_' . $bundled_item_id, true );
			$item_desc			= get_post_meta( $bundle_id, 'product_description_' . $bundled_item_id, true );
			$item_qty			= get_post_meta( $bundle_id, 'bundle_quantity_' . $bundled_item_id, true );
			$discount			= get_post_meta( $bundle_id, 'bundle_discount_' . $bundled_item_id, true );
			$visibility			= get_post_meta( $bundle_id, 'visibility_' . $bundled_item_id, true );

			$sep = explode( '_', $bundled_item_id );

			$bundle_data[ $bundled_item_id ][ 'product_id' ] 				= $sep[0];


			$bundle_data[ $bundled_item_id ][ 'filter_variations' ] 		= $filtered == 'yes' ? 'yes' : 'no';

			if ( isset( $allowed_variations[ $bundled_item_id ] ) )
				$bundle_data[ $bundled_item_id ][ 'allowed_variations' ] 	= $allowed_variations[ $bundled_item_id ];


			$bundle_data[ $bundled_item_id ][ 'override_defaults' ] 		= $o_defaults == 'yes' ? 'yes' : 'no';

			if ( isset( $default_attributes[ $bundled_item_id ] ) )
				$bundle_data[ $bundled_item_id ][ 'bundle_defaults' ] 		= $default_attributes[ $bundled_item_id ];


			$bundle_data[ $bundled_item_id ][ 'hide_thumbnail' ] 			= $hide_thumbnail == 'yes' ? 'yes' : 'no';


			$bundle_data[ $bundled_item_id ][ 'override_title' ] 			= $item_o_title == 'yes' ? 'yes' : 'no';

			if ( $item_o_title == 'yes' )
				$bundle_data[ $bundled_item_id ][ 'product_title' ] 		= $item_title;


			$bundle_data[ $bundled_item_id ][ 'override_description' ] 		= $item_o_desc == 'yes' ? 'yes' : 'no';

			if ( $item_o_desc == 'yes' )
				$bundle_data[ $bundled_item_id ][ 'product_description' ] 	= $item_desc;


			$bundle_data[ $bundled_item_id ][ 'bundle_quantity' ] 			= $item_qty;
			$bundle_data[ $bundled_item_id ][ 'bundle_discount' ] 			= $discount;

			$bundle_data[ $bundled_item_id ][ 'visibility' ] 				= $visibility == 'hidden' ? 'hidden' : 'visible';

			$bundle_data[ $bundled_item_id ][ 'hide_filtered_variations' ] 	= 'no';
		}

		update_post_meta( $bundle_id, '_bundle_data', $bundle_data );

		$wpdb->query( $wpdb->prepare( "DELETE FROM `$wpdb->postmeta` WHERE `post_id` LIKE %s AND (
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE ('_bundled_ids') OR
			`meta_key` LIKE ('_bundle_defaults') OR
			`meta_key` LIKE ('_allowed_variations')
		)", $bundle_id, 'filter_variations_%', 'override_defaults_%', 'bundle_quantity_%', 'bundle_discount_%', 'hide_thumbnail_%', 'override_title_%', 'product_title_%', 'override_description_%', 'product_description_%', 'hide_filtered_variations_%', 'visibility_%' ) );

		return $bundle_data;
	}

	/**
	 * Calculates bundled product prices incl. or excl. tax depending on the 'woocommerce_tax_display_shop' setting.
	 *
	 * @param  WC_Product   $product    the product
	 * @param  double       $price      the product price
	 * @return double                   modified product price incl. or excl. tax
	 */
	function get_product_price_incl_or_excl_tax( $product, $price ) {

		if ( ! $this->is_wc_21() || $price == 0 )
			return $price;

		if ( $this->wc_option_tax_display_shop == 'excl' )
			$product_price = $product->get_price_excluding_tax( 1, $price );
		else
			$product_price = $product->get_price_including_tax( 1, $price );

		return $product_price;
	}

	/**
	 * Inserts bundle contents after main wishlist bundle item is displayed.
	 *
	 * @param  array    $item       Wishlist item
	 * @param  array    $wishlist   Wishlist
	 * @return void
	 */
	function woo_bundles_wishlist_after_list_item_name( $item, $wishlist ) {

		if ( $item[ 'data' ]->is_type( 'bundle' ) && ! empty( $item[ 'stamp' ] ) ) {
			echo '<dl>';
			foreach ( $item[ 'stamp' ] as $bundled_item_id => $bundled_item_data ) {
				echo '<dt class="bundled_title_meta wishlist_bundled_title_meta">' . get_the_title( $bundled_item_data[ 'product_id' ] ) . ' <strong class="bundled_quantity_meta wishlist_bundled_quantity_meta product-quantity">&times; ' . $bundled_item_data[ 'quantity' ] . '</strong></dt>';
				if ( ! empty ( $bundled_item_data[ 'attributes' ] ) && $this->is_wc_21() ) {
					$attributes = '';
					foreach ( $bundled_item_data[ 'attributes' ] as $attribute_name => $attribute_value ) {

						$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $attribute_name ) ) );

						// If this is a term slug, get the term's nice name
			            if ( taxonomy_exists( $taxonomy ) ) {
			            	$term = get_term_by( 'slug', $attribute_value, $taxonomy );
			            	if ( ! is_wp_error( $term ) && $term && $term->name ) {
			            		$attribute_value = $term->name;
			            	}

			            	$label = wc_attribute_label( $taxonomy );

			            // If this is a custom option slug, get the options name
			            } else {
							$attribute_value	= apply_filters( 'woocommerce_variation_option_name', $attribute_value );
							$bundled_product = get_product( $bundled_item_data[ 'product_id' ] );
							$product_attributes = $bundled_product->get_attributes();
							if ( isset( $product_attributes[ str_replace( 'attribute_', '', $attribute_name ) ] ) ) {
								$label = wc_attribute_label( $product_attributes[ str_replace( 'attribute_', '', $attribute_name ) ][ 'name' ] );
							} else {
								$label = $attribute_name;
							}
						}

						$attributes = $attributes . $label . ': ' . $attribute_value . ', ';
					}
					echo '<dd class="bundled_attribute_meta wishlist_bundled_attribute_meta">' . rtrim( $attributes, ', ' ) . '</dd>';
				}
			}
			echo '</dl>';
			echo '<p class="bundled_notice wishlist_component_notice">' . __( '*', 'woocommerce-product-bundles' ) . '&nbsp;&nbsp;<em>' . __( 'Accurate pricing info available in cart.', 'woocommerce-product-bundles' ) . '</em></p>';
		}
	}

	/**
	 * Modifies wishlist bundle item price - the precise sum cannot be displayed reliably unless the item is added to the cart.
	 *
	 * @param  double   $price      Item price
	 * @param  array    $item       Wishlist item
	 * @param  array    $wishlist   Wishlist
	 * @return string   $price
	 */
	function woo_bundles_wishlist_list_item_price( $price, $item, $wishlist ) {

		if ( $item[ 'data' ]->is_type( 'bundle' ) && ! empty( $item[ 'stamp' ] ) )
			return __( '*', 'woocommerce-product-bundles' );

		return $price;

	}

	/**
	 * Modify microdata get_price call: get_price() will return the base price in per-product pricing mode, or the product price in static pricing mode.
	 * Here we set a flag to modify the output of get_price, which is overridden in the bundle product class.
	 *
	 * @return void
	 */
	function woo_bundles_loop_price_9() {
		global $product;

		if ( $product->is_type( 'bundle' ) && $product->is_priced_per_product() )
			$product->microdata_display = true;
	}

	/**
	 * Modify microdata get_price call: get_price() will return the base price in per-product pricing mode, or the product price in static pricing mode.
	 * After the output of get_price has been modified, we reset the microdata_display flag.
	 *
	 * @return void
	 */
	function woo_bundles_loop_price_11() {
		global $product;

		if ( $product->is_type( 'bundle' ) && $product->is_priced_per_product() )
			$product->microdata_display = false;
	}

	/**
	 * Add detailed bundle price filter results. The price meta stored in the database does not contain the correct price of a bundle in per-product pricing mode.
	 *
	 * @param  array    $results    returned products that match the filter
	 * @param  double   $min        min price
	 * @param  double   $max        max price
	 * @return array                modified price filter results
	 */
	function woo_bundles_price_filter_results( $results, $min, $max ) {

		global $wpdb;

		// Clean out bundles
		$args = array(
			'post_type' 	=> 'product',
			'tax_query' 	=> array(
				array(
					'taxonomy' 	=> 'product_type',
					'field' 	=> 'slug',
					'terms' 	=> 'bundle'
					)
			),
			'fields'        => 'ids'
		);

		$bundle_ids 	= get_posts( $args );
		$clean_results 	= array();

		if ( ! empty ( $bundle_ids ) ) {

			foreach ( $results as $key => $result ) {

				if ( $result->post_type == 'product' && in_array( $result->ID, $bundle_ids ) )
					continue;

				$clean_results[ $key ] = $result;
			}
		} else {

			$clean_results = $results;
		}

		$bundle_results = array();

		$bundle_results = $wpdb->get_results( $wpdb->prepare( "
        	SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
			INNER JOIN $wpdb->postmeta meta_1 ON ID = meta_1.post_id
			INNER JOIN $wpdb->postmeta meta_2 ON ID = meta_2.post_id
			WHERE post_type IN ( 'product' )
				AND post_status = 'publish'
				AND meta_1.meta_key = '_max_bundle_price' AND ( meta_1.meta_value >= %d OR meta_1.meta_value = '' )
				AND meta_2.meta_key = '_min_bundle_price' AND meta_2.meta_value <= %d
		", $min, $max ), OBJECT_K );

		$merged_results = $clean_results + $bundle_results;

		return $merged_results;
	}

}
