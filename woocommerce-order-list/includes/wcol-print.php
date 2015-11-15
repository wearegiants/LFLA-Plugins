<?php
if ( !class_exists( 'WooCommerce_Order_List_Print' ) ) {

	class WooCommerce_Order_List_Print {
		public $order_id;

		/**
		 * Construct.
		 */
				
		public function __construct() {
			$this->template_base_path = WooCommerce_Order_List::$plugin_path . 'templates/';
			$this->template_base_url = WooCommerce_Order_List::$plugin_url . 'templates/';
			$this->settings = get_option( 'wpo_wcol_settings' );

			add_action( 'load-edit.php', array( &$this, 'print_list' ) );
		}

		/**
		 * Print labels for selected orders
		 *
		 * @access public
		 * @return void
		 */
		public function print_list() {
			if ( !isset($_REQUEST['action']) )
				return;

			if ( $_REQUEST['action'] != 'wcol' )
				return;

			if ( empty($_GET['order_ids']) )
				die( __('You have not selected any orders!', 'wpo_wcol') );


			$page_template = $this->get_template_path( 'wcol-page-template.php' );
			$output = $this->get_template( $page_template );

			echo $output;

			die();
		}

		/**
		 * Return evaluated template contents
		 */
		public function get_template( $file ) {
			ob_start();
		    if (file_exists($file)) {
				include($file);
			}
			return ob_get_clean();
		}

		/**
		 * Get the template path for a file. locate by file existience
		 * and then return the corresponding file path.
		 */
		public function get_template_path( $name ) {
			$plugin_template_path = $this->template_base_path;
			$child_theme_template_path = get_stylesheet_directory() . '/woocommerce/order-list/';
			$theme_template_path = get_template_directory() . '/woocommerce/order-list/';
	
			if( file_exists( $child_theme_template_path . $name ) ) {
				$filepath = $child_theme_template_path . $name;
			} elseif( file_exists( $theme_template_path . $name ) ) {
				$filepath = $theme_template_path . $name;
			} else {
				$filepath = $plugin_template_path . $name;
			}
			
			return apply_filters( 'wcol_custom_template_path', $filepath, $name );
		}

		/**
		 * Get the template path for a file. locate by file existience
		 * and then return the corresponding file path.
		 */
		public function get_template_url( $name ) {
			// paths for file_exists check
			$child_theme_template_path = get_stylesheet_directory() . '/woocommerce/order-list/';
			$theme_template_path = get_template_directory() . '/woocommerce/order-list/';

			$plugin_template_url = $this->template_base_url;
			$child_theme_template_url = get_stylesheet_directory_uri() . '/woocommerce/order-list/';
			$theme_template_url = get_template_directory_uri() . '/woocommerce/order-list/';

			if( file_exists( $child_theme_template_path . $name ) ) {
				$fileurl = $child_theme_template_url . $name;
			} elseif( file_exists( $theme_template_path . $name ) ) {
				$fileurl = $theme_template_url . $name;
			} else {
				$fileurl = $plugin_template_url . $name;
			}
			
			return apply_filters( 'wcol_custom_template_url', $fileurl, $name );
		}

		/**
		 * Get the current order items
		 */
		public function get_order_items( $order_id ) {
			global $woocommerce;
			$this->order = new WC_Order( $order_id );
			//global $_product;
			$items = $this->order->get_items();
			$data_list = array();
		
			if( sizeof( $items ) > 0 ) {
				foreach ( $items as $item_id => $item ) {
					// Array with data for the printing template
					$data = array();

					// Set ID's
					$data['product_id'] = $item['product_id'];
					$data['variation_id'] = $item['variation_id'];
					
					// Create the product
					$product = $this->order->get_product_from_item( $item );

					// Set the variation
					if( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) {
						$data['variation'] = woocommerce_get_formatted_variation( $product->get_variation_attributes() );
					} else {
						$data['variation'] = null;
					}
										
					// Set item name
					$data['name'] = $item['name'];
					
					// Set item quantity
					$data['quantity'] = $item['qty'];
																								
					$data['order_price'] = $this->order->get_formatted_line_subtotal( $item ); // formatted according to WC settings

					// Set item meta
					$meta = new WC_Order_Item_Meta( $item['item_meta'] );	
					$data['meta'] = $meta->display( false, true );

					if(!empty($product)) {
						// Set item SKU
						$data['sku'] = $product->get_sku();

						// Set item weight
						$data['weight'] = $product->get_weight();
						$data['total_weight'] = $data['quantity']*$data['weight'];
						
						// Set item dimensions
						$data['dimensions'] = $product->get_dimensions();
					} else {
						$data['sku'] = '';
						$data['total_weight'] = '';
					}

					// pass complete item and product
					$data['item'] = $item;
					$data['product'] = $product;
															
					$data_list[$item_id] = $data;
				}
			}

			return apply_filters( 'wpo_wcol_order_items', $data_list, $this->order );
		}

		/**
		 * Get the current order data
		 */
		public function get_order_data( $order_id ) {
			$this->order = new WC_Order( $order_id );
			// get order meta
			$order_meta = get_post_meta( $order_id );

			// print_r($order_meta);die();
			// flatten values
			foreach ($order_meta as $key => &$value) {
				$value = $value[0];
			}
			// remove reference!
			unset($value);

			// get full countries & states
			$countries = new WC_Countries;
			$shipping_country	= isset($order_meta['_shipping_country'])?$order_meta['_shipping_country']:'';
			$billing_country	= isset($order_meta['_billing_country'])?$order_meta['_billing_country']:'';
			$shipping_state		= isset($order_meta['_shipping_state'])?$order_meta['_shipping_state']:'';
			$billing_state		= isset($order_meta['_billing_state'])?$order_meta['_billing_state']:'';

			$shipping_state_full	= ( $shipping_country && $shipping_state && isset( $countries->states[ $shipping_country ][ $shipping_state ] ) ) ? $countries->states[ $shipping_country ][ $shipping_state ] : $shipping_state;
			$billing_state_full		= ( $billing_country && $billing_state && isset( $countries->states[ $billing_country ][ $billing_state ] ) ) ? $countries->states[ $billing_country ][ $billing_state ] : $billing_state;
			$shipping_country_full	= ( $shipping_country && isset( $countries->countries[ $shipping_country ] ) ) ? $countries->countries[ $shipping_country ] : $shipping_country;
			$billing_country_full	= ( $billing_country && isset( $countries->countries[ $billing_country ] ) ) ? $countries->countries[ $billing_country ] : $billing_country;
			unset($countries);

			// get order status
			if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ) {
				$order_status = wc_get_order_status_name( $this->order->get_status() );
			} else {
				$status = get_term_by( 'slug', $this->order->status, 'shop_order_status' );
				$order_status = __( $status->name, 'woocommerce' );
			}

			// add 'missing meta'
			$order_meta['shipping_address']			= $this->order->get_formatted_shipping_address();
			$order_meta['shipping_country_code']	= $shipping_country;
			$order_meta['shipping_state_code']		= $shipping_state;
			$order_meta['_shipping_country']		= $shipping_country_full;
			$order_meta['_shipping_state']			= $shipping_state_full;

			$order_meta['billing_address']			= $this->order->get_formatted_billing_address();
			$order_meta['billing_country_code']		= $billing_country;
			$order_meta['billing_state_code']		= $billing_state;
			$order_meta['_billing_country']			= $billing_country_full;
			$order_meta['_billing_state']			= $billing_state_full;

			$order_meta['order_total']				= $this->order->get_formatted_order_total();
			$order_meta['order_time']				= date_i18n( get_option( 'time_format' ), strtotime( $this->order->order_date ) );
			$order_meta['payment_method']			= $this->order->payment_method_title;
			$order_meta['shipping_method']			= $this->order->get_shipping_method();
			$order_meta['status']					= $order_status;
			$order_meta['shipping_notes']			= wpautop( wptexturize( $this->order->customer_note ) );
			$order_meta['customer_note']			= $order_meta['shipping_notes'];
			$order_meta['order_number']				= ltrim($this->order->get_order_number(), '#');
			$order_meta['order_date']				= date_i18n( get_option( 'date_format' ), strtotime( $this->order->order_date ) );
			$order_meta['date']						= date_i18n( get_option( 'date_format' ) );

			// calculate total weight
			$total_weight = 0;
			$items = $this->order->get_items();
			if( sizeof( $items ) > 0 ) {
				foreach ( $items as $item ) {
					$product = $this->order->get_product_from_item( $item );
					if (!empty($product)) {
						$total_weight += $item['qty']*$product->get_weight();
					}
				}
			}
			$order_meta['order_weight']				= $total_weight;

			// strip leading underscores, put in new $order_data array
			foreach ($order_meta as $key => $value) {
				$order_data[ltrim($key,'_')] = $value;
			}
		
			return apply_filters( 'wpo_wcol_order_data', $order_data, $this->order );
		}

		/**
		 * Get summary list of all products in the selected orders
		 */
		public function get_summary ( $order_ids ) {
			$summary = array();

			foreach ( $order_ids as $order_id ) {
				$order_items = $this->get_order_items( $order_id );
				if( sizeof( $order_items ) > 0 ) {
					foreach ( $order_items as $item_id => $item ) {
						// determine product id to use
						if ( isset($item['variation_id']) && $item['variation_id'] != 0 ) {
							$id = $item['variation_id'];
						} else {
							$id = $item['product_id'];
						}

						// copy item data to summary
						if ( !isset( $summary[$id] ) ) {
							$summary[$id] = $item;
							$summary[$id]['orders'] = array( $order_id ); 
							$summary[$id]['item_ids'] = array( $item_id ); 
						} else {
							$summary[$id]['quantity'] += $item['quantity'];
							$summary[$id]['total_weight'] += $item['total_weight'];
							$summary[$id]['orders'][] = $order_id;
							$summary[$id]['item_ids'][] = $item_id;
						}
					}
				}
			}

			return apply_filters( 'wpo_wcol_summary', $summary, $order_ids );
		}

	} // end class
} // end class_exists
