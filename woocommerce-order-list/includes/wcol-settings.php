<?php
if ( !class_exists( 'WooCommerce_Order_List_Settings' ) ) {

	class WooCommerce_Order_List_Settings {
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'menu' ) ); // Add menu.
			add_action( 'admin_init', array( &$this, 'init_settings' ) ); // Registers settings
			add_filter( 'plugin_action_links_'.WooCommerce_Order_List::$plugin_basename, array( &$this, 'add_settings_link' ) );

			add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts_styles' ) ); // Load scripts & styles
		}

		public function menu() {
			if (class_exists('WPOvernight_Core')) {
				$parent_slug = 'wpo-core-menu';
			} else {
				$parent_slug = 'woocommerce';
			}

			$this->options_page_hook = add_submenu_page(
				$parent_slug,
				__( 'Order List', 'wpo_wcol' ),
				__( 'Order List', 'wpo_wcol' ),
				'manage_options',
				'wpo_wcol_options_page',
				array( $this, 'settings_page' )
			);
		}
		
		/**
		 * Scrips & styles for settings page
		 */
		public function load_scripts_styles($hook) {
			if( $hook != $this->options_page_hook ) 
				return;

			/* NOT USED
			wp_register_style(
				'wcol-admin-styles', // handle
				plugins_url( '/css/wcol-admin-styles.css', dirname(__FILE__) ), // source
				array(), // dependencies
				'', // version
				'all' // media
			);
			wp_enqueue_style( 'wcol-admin-styles' );

			wp_register_script(
				'wcol-admin-scripts',
				plugins_url( 'js/wcol-admin-scripts.js' , dirname(__FILE__) ),
				array( 'jquery' )
			);
			wp_enqueue_script( 'wcol-admin-scripts' );
			*/

		}

		/**
		 * Add settings link to plugins page
		 */
		public function add_settings_link( $links ) {
		    $settings_link = '<a href="admin.php?page=wpo_wcol_options_page">'. __( 'Settings', 'woocommerce' ) . '</a>';
		  	array_push( $links, $settings_link );
		  	return $links;
		}
		
		public function settings_page() {
			$theme_path = get_stylesheet_directory();
			$theme_template_path = substr($theme_path, strpos($theme_path, 'wp-content')) . '/woocommerce/order-list/';
			$plugin_template_path = 'wp-content/plugins/woocommerce-order-list/templates/';
			?>
				<div class="wrap">
					<div class="icon32" id="icon-options-general"><br /></div>
					<h2><?php _e( 'WooCommerce Print Order List', 'wpo_wcol' ); ?></h2>
					<br />
					<div class="wcol-settings">
						<form method="post" action="options.php">

						<?php
							settings_fields( 'wpo_wcol_settings' );
							do_settings_sections( 'wpo_wcol_settings' );

							submit_button();
						?>

						</form>
					</div>
					<?php printf(__( 'Want finer control over the order list (contents and appearance)? This plugin is based on a HTML/CSS/PHP template, which you can customize to your needs. Copy all the files from <code>%1$s</code> to <code>%2$s</code> before customizing, this way they will not be overwritten on plugin updates' , 'wpo_wcol' ), $plugin_template_path, $theme_template_path ); ?>
				</div>
			<?php
		}
		
		/**
		 * User settings.
		 */
		
		public function init_settings() {
			$option = 'wpo_wcol_settings';
		
			// Create option in wp_options.
			if ( false === get_option( $option ) ) {
				add_option( $option );
				$this->default_settings();
			}

			// Section.
			add_settings_section(
				'general',
				__( 'General settings', 'wpo_wcol' ),
				array( &$this, 'section_options_callback' ),
				$option
			);

			add_settings_field(
				'preview',
				__( 'Enable preview', 'wpo_wcol' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'general',
				array(
					'menu'			=> $option,
					'id'			=> 'preview',
					'description'	=> __( 'Open the order list in a new browser tab instead of printing directly', 'wpo_wcol' ),
					// 'default'		=> 1,
				)
			);		
	
			// Section.
			add_settings_section(
				'list_contents',
				__( 'Order list contents', 'wpo_wcol' ),
				array( &$this, 'list_contents_section' ),
				$option
			);


			add_settings_field(
				'show_billing_address',
				__( 'Billing address', 'wpo_wcol' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'show_billing_address',
					// 'default'		=> 1,
				)
			);

			add_settings_field(
				'show_shipping_address',
				__( 'Shipping address', 'wpo_wcol' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'show_shipping_address',
					// 'default'		=> 1,
				)
			);
			
			add_settings_field(
				'show_order_items',
				__( 'Order items', 'wpo_wcol' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'show_order_items',
					// 'default'		=> 1,
				)
			);

			add_settings_field(
				'show_order_item_price',
				__( 'Order item price', 'wpo_wcol' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'show_order_item_price',
					// 'default'		=> 1,
				)
			);

			add_settings_field(
				'show_order_item_weight',
				__( 'Order item weight', 'wpo_wcol' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'show_order_item_weight',
					// 'default'		=> 1,
				)
			);

			add_settings_field(
				'show_order_item_sku',
				__( 'Order item SKU', 'wpo_wcol' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'show_order_item_sku',
					// 'default'		=> 1,
				)
			);


			add_settings_field(
				'show_order_item_meta',
				__( 'Order item meta', 'wpo_wcol' ),
				array( &$this, 'radio_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'show_order_item_meta',
					'options' 		=> array(
						'variation'	=> __( 'Only show variation' , 'wpo_wcol' ),
						'full'		=> __( 'Show all item info (item meta)' , 'wpo_wcol' ),
					),
					'default'		=> 'variation',
				)
			);


			add_settings_field(
				'show_shipping_notes',
				__( 'Customer/Shipping notes', 'wpo_wcol' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'show_shipping_notes',
					// 'default'		=> 1,
				)
			);


			add_settings_field(
				'print_summary',
				__( 'Print order list summary', 'wpo_wcol' ),
				array( &$this, 'radio_element_callback' ),
				$option,
				'list_contents',
				array(
					'menu'			=> $option,
					'id'			=> 'print_summary',
					'options' 		=> array(
						'none'		=> __( 'No' , 'wpo_wcol' ),
						'first'		=> __( 'On first page' , 'wpo_wcol' ),
						'last'		=> __( 'On last page' , 'wpo_wcol' ),
					),
					'default'		=> 'none',
				)
			);

			// Register settings.
			register_setting( $option, $option, array( &$this, 'validate_options' ) );
		}
		
		/**
		 * Set default settings.
		 * 
		 * @return void.
		 */
		public function default_settings() {
			$default_template_settings = array(
				'show_billing_address'		=> 1,
				'show_shipping_address'		=> 1,
				'show_order_items'			=> 1,
				'show_order_item_price'		=> 1,
				'show_order_item_sku'		=> 1,
				'show_order_item_meta'		=> 'variation',
				'show_shipping_notes'		=> 1,
			);

			update_option( 'wpo_wcol_settings', $default_template_settings );
		}

		/**
		 * Checkbox field callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Checkbox field.
		 */
		public function checkbox_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s />', $id, $menu, checked( 1, $current, false ) );
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
		
			echo $html;
		}

		/**
		 * Displays a radio settings field
		 *
		 * @param array   $args settings field args
		 */
		public function radio_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}

			foreach ( $args['options'] as $key => $label ) {
				printf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $menu, $id, $key, checked( $current, $key, false ) );
				printf( '<label for="%1$s[%2$s][%3$s]"> %4$s</label><br>', $menu, $id, $key, $label);
			}
			
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}
	
		}

		/**
		 * Text element callback.
		 * @param  array $args Field arguments.
		 * @return string	   Text input field.
		 */
		public function text_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$size = isset( $args['size'] ) ? $args['size'] : '25';
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		

			$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"/>', $id, $menu, $current, $size );
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
		
			echo $html;
		}

		/**
		 * Multiple text element callback.
		 * @param  array $args Field arguments.
		 * @return string	   Text input field.
		 */
		public function multiple_text_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$fields = $args['fields'];
			$options = get_option( $menu );

			foreach ($fields as $name => $field) {
				$label = $field['label'];
				$size = $field['size'];

				if (isset($field['label_width'])) {
					$style = sprintf( 'style="display:inline-block; width:%1$s;"', $field['label_width'] );
				} else {
					$style = '';
				}

				// output field label
				printf( '<label for="%1$s_%2$s" %3$s>%4$s</label>', $id, $name, $style, $label );

				// die(var_dump($options));

				if ( isset( $options[$id][$name] ) ) {
					$current = $options[$id][$name];
				} else {
					$current = isset( $args['default'] ) ? $args['default'] : '';
				}
				
				// output field
				printf( '<input type="text" id="%1$s_%3$s" name="%2$s[%1$s][%3$s]" value="%4$s" size="%5$s"/><br/>', $id, $menu, $name, $current, $size );
	
			}
		
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}
		
		}

		// Text area element callback.
		public function textarea_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$width = $args['width'];
			$height = $args['height'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			printf( '<textarea id="%1$s" name="%2$s[%1$s]" cols="%4$s" rows="%5$s"/>%3$s</textarea>', $id, $menu, $current, $width, $height );
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}
		}

		/**
		 * Select element callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Select field.
		 */
		public function select_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			printf( '<select id="%1$s" name="%2$s[%1$s]">', $id, $menu );
	
			foreach ( $args['options'] as $key => $label ) {
				printf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
			}
	
			echo '</select>';

			if (isset($args['custom'])) {
				$custom = $args['custom'];

				echo '<br/><br/>';

				switch ($custom['type']) {
					case 'text_element_callback':
						$this->text_element_callback( $custom['args'] );
						break;		
					case 'multiple_text_element_callback':
						$this->multiple_text_element_callback( $custom['args'] );
						break;		
					default:
						break;
				}
			}
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}

		}

		/**
		 * Section null callback.
		 *
		 * @return void.
		 */
		public function section_options_callback() {
		}

		/**
		 * List contents section callback.
		 *
		 * @return void.
		 */
		public function list_contents_section() {
			_e( 'Check the elements that you want to show in the order list' , 'wpo_wcol' );			
		}

		
		/**
		 * Validate options.
		 *
		 * @param  array $input options to valid.
		 *
		 * @return array		validated options.
		 */
		public function validate_options( $input ) {
			// Create our array for storing the validated options.
			$output = array();
		
			// Loop through each of the incoming options.
			foreach ( $input as $key => $value ) {
		
				// Check to see if the current option has a value. If so, process it.
				if ( isset( $input[$key] ) ) {
		
					// Strip all HTML and PHP tags and properly handle quoted strings.
					if ( is_array( $input[$key] ) ) {
						foreach ( $input[$key] as $sub_key => $sub_value ) {
							$output[$key][$sub_key] = $input[$key][$sub_key];
						}

					} else {
						$output[$key] = $input[$key];
					}
				}
			}
		
			// Return the array processing any additional functions filtered by this action.
			return apply_filters( 'wpo_wcol_validate_input', $output, $input );
		}

	} // end class
} // end class_exists