<?php
/**
 * Used to create and store a product_id / variation_id representation of a product collection based on the included items' inventory requirements.
 *
 * @class    WC_Bundled_Stock_Data
 * @since    4.7.0
 * @version  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class WC_Bundled_Stock_Data {

	private $items;

	function __construct() {

		$this->items = array();
	}

	/**
	 * Add a product to the collection.
	 *
	 * @param int          $product_id
	 * @param false|int    $variation_id
	 * @param integer      $quantity
	 */
	public function add_item( $product_id, $variation_id = false, $quantity = 1 ) {

		if ( $variation_id ) {

			$variation_stock = get_post_meta( $variation_id, '_stock', true );

			// If stock is managed at variation level
			if ( isset( $variation_stock ) && $variation_stock !== '' ) {

				// If a stock-managed variation is added to the cart multiple times,
				// its stock must be checked for the sum of all quantities.

				if ( isset( $this->items[ $variation_id ] ) ) {

					$this->items[ $variation_id ][ 'quantity' ] += $quantity;

				} else {

					$this->items[ $variation_id ][ 'quantity' ] 	= $quantity;
					$this->items[ $variation_id ][ 'is_variation' ]	= true;
					$this->items[ $variation_id ][ 'product_id' ]	= $product_id;
				}

			} else {

				// Non-stock-managed variations of the same item
				// must not be stock-checked individually, since they pull stock from the parent.

				if ( isset( $this->items[ $product_id ] ) ) {

					$this->items[ $product_id ][ 'quantity' ] += $quantity;

				} else {

					$this->items[ $product_id ][ 'quantity' ] = $quantity;
				}
			}

		} else {

			// Simple products are, ummm, simple.
			if ( isset( $this->items[ $product_id ] ) ) {

				$this->items[ $product_id ][ 'quantity' ] += $quantity;

			} else {

				$this->items[ $product_id ][ 'quantity' ] = $quantity;
			}
		}

	}

	/**
	 * Merge another collection with this one.
	 *
	 * @param WC_Bundled_Stock_Data  $stock
	 */
	public function add_stock( $stock ) {

		if ( ! is_object( $stock ) )
			return false;

		$managed_item_ids = $stock->get_item_ids();

		if ( $managed_item_ids )
			foreach ( $managed_item_ids as $item_id ) {

				$managed_item_product_id 	= $stock->get_item_product_id( $item_id );
				$managed_item_variation_id 	= $stock->get_item_variation_id( $item_id );
				$managed_item_quantity 		= $stock->get_item_quantity( $item_id );

				$this->add_item( $managed_item_product_id, $managed_item_variation_id, $managed_item_quantity );
			}

	}

	/**
	 * Add raw items from another collection.
	 *
	 * @param array|false  $items
	 */
	public function add_items( $items ) {

		if ( empty( $items ) )
			return false;

		foreach ( $items as $item_id => $item_data ) {

			if ( ! empty( $item_data[ 'product_id' ] ) && ! empty( $item_data[ 'quantity' ] ) ) {

				$is_item_variation = isset( $item_data[ 'is_variation' ] ) && $item_data[ 'is_variation' ] ? true : false;

				if ( $is_item_variation )
					$this->add_item( $item_data[ 'product_id' ], $item_id, $item_data[ 'quantity' ] );
				else
					$this->add_item( $item_id, false, $item_data[ 'quantity' ] );
			}
		}
	}

	/**
	 * Return raw items of this collection.
	 *
	 * @return array
	 */
	public function get_items() {

		if ( ! empty( $this->items ) )
			return $this->items;

		return array();
	}

	/**
	 * Return product / variation ids added to this collection.
	 *
	 * @return array|false
	 */
	public function get_item_ids() {

		if ( ! empty( $this->items ) )
			return array_keys( $this->items );

		return false;
	}

	/**
	 * True if the item id belongs to a variation.
	 *
	 * @param  int     $item_id
	 * @return boolean
	 */
	public function is_item_variation( $item_id ) {

		if ( ! empty( $this->items ) && isset( $this->items[ $item_id ] ) && isset( $this->items[ $item_id ][ 'is_variation' ] ) && $this->items[ $item_id ][ 'is_variation' ] )
			return true;

		return false;
	}

	/**
	 * For simple products, it returns the product id. For variations, it returns the id of the parent.
	 *
	 * @param  int    $item_id
	 * @return int
	 */
	public function get_item_product_id( $item_id ) {

		if ( $this->is_item_variation( $item_id ) && ! empty( $this->items[ $item_id ][ 'product_id' ] ) )
			return $this->items[ $item_id ][ 'product_id' ];

		return $item_id;
	}

	/**
	 * Get the variation id of an item if it is a variation, or false otherwise.
	 *
	 * @param  int    $item_id
	 * @return int|false
	 */
	public function get_item_variation_id( $item_id ) {

		if ( $this->is_item_variation( $item_id ) )
			return $item_id;

		return false;
	}

	/**
	 * Get the quantity of an item in the collection.
	 *
	 * @param  int    $item_id
	 * @return int
	 */
	public function get_item_quantity( $item_id ) {

		if ( isset( $this->items[ $item_id ][ 'quantity' ] ) )
			return $this->items[ $item_id ][ 'quantity' ];

		return 0;
	}
}
