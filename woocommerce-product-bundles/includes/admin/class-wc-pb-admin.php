<?php
/**
 * Product Bundles Admin Class.
 *
 * Loads admin tabs and adds related hooks / filters.
 *
 * @class WC_PB_Admin
 * @version 4.5.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class WC_PB_Admin {

	var $save_errors = array();

	/**
	 * Setup admin class
	 */
	public function __construct() {

		// Admin jquery
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_bundles_admin_scripts' ), 11 );

		// Creates the admin panel tab 'Bundled Products'
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'woo_bundles_product_write_panel_tab' ) );

		// Creates the panel for selecting bundled product options
		add_action( 'woocommerce_product_write_panels', array( $this, 'woo_bundles_product_write_panel' ) );
		add_action( 'woocommerce_product_options_stock', array( $this, 'woo_bundles_stock_group' ) );

		add_filter( 'product_type_options', array( $this, 'woo_bundles_type_options' ) );

		// Processes and saves the necessary post metas from the selections made above
		add_action( 'woocommerce_process_product_meta_bundle', array( $this, 'woo_bundles_process_bundle_meta' ) );

		// Allows the selection of the 'bundled product' type
		add_filter( 'product_type_selector', array( $this, 'woo_bundles_product_selector_filter' ) );

		// Ajax save bundle config
		add_action( 'wp_ajax_woocommerce_product_bundles_save', array( $this, 'woo_bundles_ajax_save' ) );

		// Template override scan path
		add_filter( 'woocommerce_template_overrides_scan_paths', array( $this, 'woo_bundles_template_scan_path' ) );

		// Bundle term reserved fix - not needed anymore
		// add_action( 'admin_init', array( $this, 'woo_bundles_admin_init' ) );
	}


	/**
	 * Admin writepanel scripts.
	 * @return void
	 */
	function woo_bundles_admin_scripts() {

		global $woocommerce_bundles;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$css_version = '';

		if ( $woocommerce_bundles->helpers->is_wc_22() ) {
			$writepanel_dependency = 'wc-admin-meta-boxes';
		} elseif ( $woocommerce_bundles->helpers->is_wc_21() ) {
			$writepanel_dependency = 'woocommerce_admin_meta_boxes';
		} else {
			$writepanel_dependency = 'woocommerce_writepanel';
			$css_version = '-20';
		}

		wp_register_script( 'woo_bundles_writepanel', $woocommerce_bundles->woo_bundles_plugin_url() . '/assets/js/bundled-product-write-panels' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', $writepanel_dependency ), $woocommerce_bundles->version );
		wp_register_style( 'woo_bundles_css', $woocommerce_bundles->woo_bundles_plugin_url() . '/assets/css/bundles-write-panels' . $css_version . '.css', array( 'woocommerce_admin_styles' ), $woocommerce_bundles->version );

		if ( $woocommerce_bundles->helpers->is_wc_21() )
			wp_register_style( 'woo_bundles_edit_order_css', $woocommerce_bundles->woo_bundles_plugin_url() . '/assets/css/bundles-edit-order.css', array( 'woocommerce_admin_styles' ), $woocommerce_bundles->version );

		// Get admin screen id
		$screen = get_current_screen();

		// WooCommerce admin pages
		if ( in_array( $screen->id, array( 'product' ) ) )
			wp_enqueue_script( 'woo_bundles_writepanel' );

		if ( in_array( $screen->id, array( 'edit-product', 'product' ) ) )
			wp_enqueue_style( 'woo_bundles_css' );

		if ( $woocommerce_bundles->helpers->is_wc_21() && in_array( $screen->id, array( 'shop_order', 'edit-shop_order' ) ) )
			wp_enqueue_style( 'woo_bundles_edit_order_css' );
	}

	/**
	 * Add Bundled Products write panel tab.
	 * @return void
	 */
	function woo_bundles_product_write_panel_tab() {

		echo '<li class="bundled_product_tab show_if_bundle bundled_product_options linked_product_options"><a href="#bundled_product_data">'.__( 'Bundled Products', 'woocommerce-product-bundles' ).'</a></li>';
	}

	/**
	 * Write panel for Product Bundles.
	 * @return void
	 */
	function woo_bundles_product_write_panel() {

		global $woocommerce_bundles, $woocommerce, $post, $wpdb;

		?>
			<div id="bundled_product_data" class="panel woocommerce_options_panel">

				<div class="options_group">

					<div class="wc-bundled_products">

						<div class="bundled_products_info">

						<?php _e( 'Bundled Products', 'woocommerce-product-bundles' ); echo '<img class="help_tip" data-tip="' . __( 'Select the products that you wish to include in your bundle, kit, or assembly. Only simple or variable products can be bundled. Variable products may be bundled and configured in multiple, separate instances - to bundle a variable product multiple times, remember to make a new search for every instance you wish to add to your bundle.', 'woocommerce-product-bundles' ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />'; ?>

						</div>

						<div class="bundled_products_selector">

							<select id="bundled_ids" name="bundled_ids[]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-product-bundles' ); ?>">
								<?php
									$bundle_data = maybe_unserialize( get_post_meta( $post->ID, '_bundle_data', true ) );

									$bundled_variable_num = 0;

									if ( ! empty( $bundle_data  ) ) {

										foreach ( $bundle_data as $item_id => $item_data ) {

											$product_id = $item_data[ 'product_id' ];

											$sep = explode( '_', $item_id );

											$terms 			= get_the_terms( $product_id, 'product_type' );
											$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

											if ( $product_type == 'variable' ) { $bundled_variable_num++; }

											$title 	= get_the_title( $product_id ) . ( (string) $product_id != (string) $item_id ? ' #' . $sep[1] : '' );
											$sku 	= get_post_meta( $product_id, '_sku', true );

											if ( ! $title )
												continue;

											if ( isset( $sku ) && $sku ) $sku = ' (SKU: ' . $sku . ')';
											echo '<option value="' . $product_id . '" selected="selected">' . $title . $sku . '</option>';
										}
									}
								?>
							</select>
						</div>
						<?php
						if ( $woocommerce_bundles->helpers->is_wc_21() ) {
							echo '<button type="button" class="button save_bundle">' . __( 'Save Configuration', 'woocommerce-product-bundles' ) . '</button>';
							wp_nonce_field( 'wc_save_bundle', 'wc_save_bundle_nonce', false );
						} else {
							echo '<div class="clear"></div>';
						}
						?>
					</div>
				</div>
				<div class="options_group wc-metaboxes-wrapper wc-bundle-metaboxes-wrapper">

					<div id="wc-bundle-metaboxes-wrapper-inner">
						<?php
						if ( ! empty( $bundle_data ) ) {
						?>

						<p class="toolbar">
							<?php echo '<span class="wc-bundled-items-title">' . __( 'Bundle Configuration', 'woocommerce-product-bundles' ) . '</span>'; echo '<img class="help_tip" data-tip="' . __( 'Use these settings to configure bundled product parameters: Set item quantities / discounts, mark items as optional, apply variation filters & default attribute selection overrides and fine-tune the layout / appearance of your bundle.', 'woocommerce-product-bundles' ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />'; ?>
							<a href="#" class="close_all"><?php _e('Close all', 'woocommerce'); ?></a>
							<a href="#" class="expand_all"><?php _e('Expand all', 'woocommerce'); ?></a>
						</p>

						<?php
						}
						?>

						<div class="wc-bundled-items wc-metaboxes">

							<?php
							if ( ! empty( $bundle_data ) ) {

								$sorting = 0;

								foreach ( $bundle_data as $item_id => $item_data ) {

									$allowed_variations = isset( $item_data[ 'allowed_variations' ] ) ? $item_data[ 'allowed_variations' ] : '';
									$default_attributes = isset( $item_data[ 'bundle_defaults' ] ) ? $item_data[ 'bundle_defaults' ] : '';

									$sep 		= explode( '_', $item_id );
									$product_id = $item_data[ 'product_id' ];

									$title 	= get_the_title( $product_id ) . ( (string) $product_id != (string) $item_id ? ' #' . $sep[1] : '' );
									$sku 	= get_post_meta( $product_id, '_sku', true );

									if ( isset( $sku ) && $sku ) $sku = ' &ndash; SKU: ' . $sku;

									if ( ! $title )
										continue;
									?>

									<div class="wc-bundled-item wc-metabox closed" rel="<?php echo $sorting; ?>">
										<h3>
											<div class="handlediv" title="<?php echo __( 'Click to toggle', 'woocommerce' ); ?>"></div>
											<strong class="item-title"><?php echo $title . ' &ndash; #'. $product_id . ( ! empty( $sku ) ? $sku : '' ); ?></strong>
										</h3>
										<div class="item-data wc-metabox-content">
											<input type="hidden" name="bundle_order[<?php echo $item_id; ?>]" class="bundled_item_position" value="<?php echo $sorting; ?>" />
											<?php
												$bundled_product = get_product( $product_id );

												if ( $bundled_product->product_type == 'variable' ) { ?>

													<div class="filtering">

													<?php

													$filtered = ( $item_data[ 'filter_variations' ] == 'yes' ) ? true : false;

													?>

													<p class="tip"><a class="tips" data-tip="<?php _e( 'Check to enable only a subset of the available variations.', 'woocommerce-product-bundles' ); ?>" href="#" >[?]</a></p>

													<?php woocommerce_wp_checkbox( array( 'id' => 'filter_variations_' . $item_id, 'value' => $item_data[ 'filter_variations' ], 'wrapper_class' => 'filter_variations', 'label' => __( 'Filter Variations', 'woocommerce-product-bundles' ), 'description' => '' ) ); ?>

													</div>


													<div class="bundle_variation_filters indented">

														<select multiple="multiple" name="allowed_variations[<?php echo $item_id; ?>][]" style="width: 450px; display: none; " data-placeholder="Choose variations…" title="Variations" class="chosen_select" > <?php

														$args = array(
															'post_type'		=> 'product_variation',
															'post_status' 	=> array( 'private', 'publish' ),
															'numberposts' 	=> -1,
															'orderby' 		=> 'menu_order',
															'order' 		=> 'asc',
															'post_parent' 	=> $product_id,
															'fields' 		=> 'ids'
														);

														$variations = get_posts( $args );
														$attributes = maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );

														// filtered variation attributes
														$filtered_attributes = array();

														foreach ( $variations as $variation ) {

															$description = '';

															$variation_data = get_post_meta( $variation );

															foreach ( $attributes as $attribute ) {

																// Only deal with attributes that are variations
																if ( ! $attribute[ 'is_variation' ] )
																	continue;

																// Get current value for variation (if set)
																$variation_selected_value = isset( $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ][0] ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ][0] : '';

																// Name will be something like attribute_pa_color
																$description_name 	= esc_html( wc_bundles_attribute_label( $attribute[ 'name' ] ) );
																$description_value 	= __( 'Any', 'woocommerce' ) . ' ' . $description_name;

																// Get terms for attribute taxonomy or value if its a custom attribute
																if ( $attribute[ 'is_taxonomy' ] ) {

																	$post_terms = wp_get_post_terms( $product_id, $attribute[ 'name' ] );

																	foreach ( $post_terms as $term ) {

																		if ( $variation_selected_value == $term->slug ) {
																			$description_value = apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) );
																		}

																		if ( $variation_selected_value == $term->slug || $variation_selected_value == '' ) {
																			if ( $filtered && is_array( $allowed_variations ) && in_array( $variation, $allowed_variations ) ) {
																				if ( ! isset( $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																					$filtered_attributes[ $attribute[ 'name' ] ] [] = $variation_selected_value;
																				} elseif ( ! in_array( $variation_selected_value, $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																					$filtered_attributes[ $attribute[ 'name' ] ] [] = $variation_selected_value;
																				}
																			}
																		}

																	}

																} else {

																	$options = array_map( 'trim', explode( wc_bundles_delimiter(), $attribute[ 'value' ] ) );

																	foreach ( $options as $option ) {
																		if ( sanitize_title( $variation_selected_value ) == sanitize_title( $option ) ) {
																			$description_value = esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) );
																		}

																		if ( sanitize_title( $variation_selected_value ) == sanitize_title( $option ) || $variation_selected_value == '' ) {
																			if ( $filtered && is_array( $allowed_variations ) && in_array( $variation, $allowed_variations ) ) {
																				if ( ! isset( $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																					$filtered_attributes[ $attribute[ 'name' ] ] [] = sanitize_title( $variation_selected_value );
																				} elseif ( ! in_array( sanitize_title( $variation_selected_value ), $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																					$filtered_attributes[ $attribute[ 'name' ] ] [] = sanitize_title( $variation_selected_value );
																				}
																			}
																		}

																	}

																}

																$description .= $description_name . ': ' . $description_value . ', ';

															}

															if ( is_array( $allowed_variations ) && in_array( $variation, $allowed_variations ) )
																$selected = 'selected="selected"';
															else $selected = '';

															echo '<option value="' . $variation . '" ' . $selected . '>#' . $variation . ' - ' . rtrim( $description, ', ') . '</option>';
														} ?>

														</select>


														<?php
															//woocommerce_wp_checkbox( array( 'id' => 'hide_filtered_variations_'.$item_id, 'wrapper_class' => 'hide_filtered_variations', 'label' => __('Hide Filtered-Out Options', 'woocommerce-product-bundles'), 'description' => '<img class="help_tip" data-tip="' . __('Check to remove any filtered-out variation options from this item\'s drop-downs. If you leave the box unchecked, the options corresponding to filtered-out variations will be disabled but still visible.', 'woocommerce-product-bundles') .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />' ) );
														?>

													</div>


													<div class="defaults">

														<p class="tip"><a class="tips" data-tip="<?php _e( 'In effect for this bundle only. The available options are in sync with the filtering settings above. Always save any changes made above before configuring this section.', 'woocommerce-product-bundles' ); ?>" href="#" >[?]</a></p>

														<?php woocommerce_wp_checkbox( array( 'id' => 'override_defaults_' . $item_id, 'value' => $item_data[ 'override_defaults' ], 'wrapper_class' => 'override_defaults', 'label' => __( 'Override Default Selections', 'woocommerce-product-bundles' ), 'description' => '' ) ); ?>

													</div>

													<div class="bundle_selection_defaults indented"> <?php

															foreach ( $attributes as $attribute ) {

																// Only deal with attributes that are variations
																if ( ! $attribute['is_variation'] )
																	continue;

																// Get current value for variation (if set)
																$variation_selected_value = ( isset( $default_attributes[ sanitize_title( $attribute[ 'name' ] ) ] ) ) ? $default_attributes[ sanitize_title( $attribute[ 'name' ] ) ] : '';

																// Name will be something like attribute_pa_color
																echo '<select name="default_attributes[' . $item_id . '][' . sanitize_title( $attribute['name'] ).']"><option value="">'.__( 'No default', 'woocommerce' ) . ' ' . wc_bundles_attribute_label( $attribute['name'] ).'&hellip;</option>';

																// Get terms for attribute taxonomy or value if its a custom attribute
																if ( $attribute[ 'is_taxonomy' ] ) {

																	$post_terms = wp_get_post_terms( $product_id, $attribute[ 'name' ] );

																	sort( $post_terms );
																	foreach ( $post_terms as $term ) {
																		if ( $filtered && isset( $filtered_attributes[ $attribute[ 'name' ] ] ) && ! in_array( '', $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																			if ( ! in_array( $term->slug, $filtered_attributes[ $attribute[ 'name' ] ] ) )
																				continue;
																		}
																		echo '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) ) . '</option>';
																	}

																} else {

																	$options = array_map( 'trim', explode( wc_bundles_delimiter(), $attribute[ 'value' ] ) );

																	sort( $options );
																	foreach ( $options as $option ) {
																		if ( $filtered && isset( $filtered_attributes[ $attribute[ 'name' ] ] ) && ! in_array( '', $filtered_attributes[ $attribute['name'] ] ) ) {
																			if ( ! in_array( sanitize_title( $option ), $filtered_attributes[ $attribute['name'] ] ) )
																				continue;
																		}
																		echo '<option ' . selected( sanitize_title( $variation_selected_value ), sanitize_title( $option ), false ) . ' value="' . esc_attr( sanitize_title( $option ) ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
																	}

																}

																echo '</select>';
															}
															?>
													</div>
												<?php

												}

												$item_quantity = $item_data[ 'bundle_quantity' ];

												if ( empty( $item_quantity ) )
													$item_quantity = 1;

												$item_discount = $item_data[ 'bundle_discount' ];

												$per_product_pricing = get_post_meta( $post->ID, '_per_product_pricing_active', true ) == 'yes' ? true : false;

												$is_sub = $woocommerce_bundles->compatibility->is_subscription( $product_id );
											?>

											<?php if ( ! $is_sub ) : ?>

											<div class="optional">

												<p class="tip"><a class="tips" data-tip="<?php _e( 'Check this option to mark the bundled product as optional.', 'woocommerce-product-bundles' ); ?>" href="#" >[?]</a></p>

												<?php woocommerce_wp_checkbox( array( 'id' => 'optional_' . $item_id, 'value' => isset( $item_data[ 'optional' ] ) ? $item_data[ 'optional' ] : 'no', 'wrapper_class' => 'optional', 'label' => __( 'Optional', 'woocommerce-product-bundles' ), 'description' => '' ) ); ?>

											</div>

											<?php endif ?>

											<div class="quantity">

												<p class="tip"><a class="tips" data-tip="<?php _e( 'Defines the quantity of this bundled product.', 'woocommerce-product-bundles' ); ?>" href="#" >[?]</a></p>

												<p class="form-field">
													<label><?php echo __( 'Quantity', 'woocommerce' ); ?></label>
													<input type="number" class="bundle_quantity" size="6" name="bundle_quantity_<?php echo $item_id; ?>" value="<?php echo $item_quantity; ?>" step="any" min="0" />
												</p>

											</div>

											<div class="discount">

												<p class="tip"><a class="tips" data-tip="<?php _e( 'Discount applied to the regular price of this bundled product when Per-Item Pricing is active. Note: If a Discount is applied to a bundled product which has a sale price defined, the sale price will be overridden.', 'woocommerce-product-bundles' ); ?>" href="#" >[?]</a></p>

												<p class="form-field">
													<label><?php echo __( 'Discount %', 'woocommerce' ); ?></label>
													<input type="text" <?php echo $per_product_pricing ? '' : 'disabled="disabled"'; ?> class="input-text bundle_discount wc_input_decimal" size="5" name="bundle_discount_<?php echo $item_id; ?>" value="<?php echo $item_discount; ?>" />
												</p>

											</div>

											<div class="item_visibility">

												<p class="tip"><a class="tips" data-tip="<?php _e( 'Hides this bundled product from the front-end. Not recommended for variable products, unless default attribute selections (or default selection overrides) have been set.', 'woocommerce-product-bundles' ); ?>" href="#" >[?]</a></p>
												<p class="form-field">
													<label for="item_visibility"><?php _e( 'Front-End Visibility', 'woocommerce-product-bundles' ); ?></label>
													<select name="visibility_<?php echo $item_id; ?>">
														<?php
														$visible = ( $item_data[ 'visibility' ] == 'hidden' ) ? false : true;
														echo '<option ' . selected( $visible, true, false ) .' value="visible">' . __( 'Visible', 'woocommerce-product-bundles' ) . '</option>';
														echo '<option ' . selected( $visible, false, false ) .' value="hidden">' . __( 'Hidden', 'woocommerce-product-bundles' ) . '</option>';
														?>
													</select>
												</p>
											</div>

											<div class="images">

												<p class="tip"><a class="tips" data-tip="<?php _e( 'Check this option to hide the thumbnail image of this bundled product.', 'woocommerce-product-bundles' ); ?>" >[?]</a></p>

												<?php woocommerce_wp_checkbox( array( 'id' => 'hide_thumbnail_' . $item_id, 'value' => $item_data[ 'hide_thumbnail' ], 'wrapper_class' => 'hide_thumbnail', 'label' => __( 'Hide Product Thumbnail', 'woocommerce-product-bundles' ), 'description' => '' ) ); ?>

											</div>

											<div class="override_title">

												<p class="tip"><a class="tips" data-tip="<?php _e( 'Check this option to override the default product title.', 'woocommerce-product-bundles' ); ?>" href="#" >[?]</a></p>

												<?php woocommerce_wp_checkbox( array( 'id' => 'override_title_' . $item_id, 'value' => $item_data[ 'override_title' ], 'wrapper_class' => 'override_title', 'label' => __( 'Override Title', 'woocommerce-product-bundles' ), 'description' => '' ) ); ?>

											</div>

											<div class="custom_title">

												<?php woocommerce_wp_text_input( array( 'id' => 'product_title_' . $item_id, 'value' => isset( $item_data[ 'product_title' ] ) ? $item_data[ 'product_title' ] : '', 'class' => 'product_title', 'label' => __( 'Product Title:', 'woocommerce-product-bundles' ) ) ); ?>

											</div>

											<div class="override_description">

												<p class="tip"><a class="tips" data-tip="<?php _e( 'Check this option to override the default short product description.', 'woocommerce-product-bundles' ); ?>" href="#" >[?]</a></p>

												<?php woocommerce_wp_checkbox( array( 'id' => 'override_description_' . $item_id, 'value' => $item_data[ 'override_description' ], 'wrapper_class' => 'override_description', 'label' => __( 'Override Short Description', 'woocommerce-product-bundles' ), 'description' => '' ) ); ?>

											</div>

											<div class="custom_description">

												<?php woocommerce_wp_textarea_input(  array( 'id' => 'product_description_' . $item_id, 'value' => isset( $item_data[ 'product_description' ] ) ? $item_data[ 'product_description' ] : '', 'class' => 'product_description', 'label' => __( 'Product Short Description:', 'woocommerce-product-bundles' ) ) ); ?>

											</div>
										</div>
									</div>
								<?php
								$sorting++;
								}
							} else { ?>
								<div id="bundle-options-message" class="inline woocommerce-message">
									<div class="squeezer">
										<?php echo $woocommerce_bundles->helpers->is_wc_21() ? '<p>' : '<h4>'; _e( 'To configure additional options, first select some products and then save your changes.', 'woocommerce-product-bundles' ); echo $woocommerce_bundles->helpers->is_wc_21() ? '</p>' : '</h4>'; ?>
										<p class="submit"><a class="button-primary" href="<?php echo 'http://docs.woothemes.com/document/bundles'; ?>" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a></p>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div>

				</div> <!-- options group -->
			</div>
			<?php
	}

	/**
	 * Add Bundled Products stock note.
	 * @return void
	 */
	function woo_bundles_stock_group() {
		global $woocommerce, $post; ?>

		<p class="form-field show_if_bundle bundle_stock_msg">
			<label><?php _e( 'Note', 'woocommerce-product-bundles' ); ?></label>
			<span class="note"><?php _e( 'Use these settings to enable stock management at bundle level.' ); echo '<img class="help_tip" data-tip="' . __( 'By default, the sale of a product within a bundle has the same effect on its stock as an individual sale. There are no separate inventory settings for bundled items. However, this pane can be used to enable stock management at bundle level. This can be very useful for allocating bundle stock quota, or for keeping track of bundled item sales.', 'woocommerce-product-bundles' ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />'; ?></span>
		</p><?php

	}

	/**
	 * Product bundle options for post-1.6.2 product data section.
	 * @param  array    $options    product options
	 * @return array                modified product options
	 */
	function woo_bundles_type_options( $options ) {

		$options[ 'per_product_shipping_active' ] = array(
			'id' 			=> '_per_product_shipping_active',
			'wrapper_class' => 'show_if_bundle',
			'label' 		=> __( 'Non-Bundled Shipping', 'woocommerce-product-bundles' ),
			'description' 	=> __( 'If your bundle consists of items that are assembled or packaged together, leave the box un-checked and just define the shipping properties of the product bundle below. If, however, the bundled items are shipped individually, their shipping properties must be retained. In this case, the box must be checked. \'Non-Bundled Shipping\' should also be selected when the bundle consists of virtual items, which are not shipped.', 'woocommerce-product-bundles' ),
			'default'		=> 'no'
		);

		$options[ 'per_product_pricing_active' ] = array(
			'id' 			=> '_per_product_pricing_active',
			'wrapper_class' => 'show_if_bundle bundle_pricing',
			'label' 		=> __( 'Per-Item Pricing', 'woocommerce-product-bundles' ),
			'description' 	=> __( 'When enabled, the bundle will be priced per-item, based on standalone item prices and tax rates.', 'woocommerce-product-bundles' ),
			'default'		=> 'no'
		);

		return $options;
	}

	/**
	 * Process, verify and save bundle type product data.
	 * @param  int    $post_id    the product post id
	 * @return void
	 */
	function woo_bundles_process_bundle_meta( $post_id ) {

		global $woocommerce;

		// Per-Item Pricing

		if ( isset( $_POST[ '_per_product_pricing_active' ] ) ) {
			update_post_meta( $post_id, '_per_product_pricing_active', 'yes' );
			update_post_meta( $post_id, '_regular_price', '' );
			update_post_meta( $post_id, '_sale_price', '' );
			update_post_meta( $post_id, '_price', '' );
		} else {
			update_post_meta( $post_id, '_per_product_pricing_active', 'no' );
		}

		// Shipping
		// Non-Bundled (per-item) Shipping

		if ( isset( $_POST[ '_per_product_shipping_active' ] ) ) {
			update_post_meta( $post_id, '_per_product_shipping_active', 'yes' );
			update_post_meta( $post_id, '_virtual', 'yes' );
			update_post_meta( $post_id, '_weight', '' );
			update_post_meta( $post_id, '_length', '' );
			update_post_meta( $post_id, '_width', '' );
			update_post_meta( $post_id, '_height', '' );
		} else {
			update_post_meta( $post_id, '_per_product_shipping_active', 'no' );
			update_post_meta( $post_id, '_virtual', 'no' );
			update_post_meta( $post_id, '_weight', stripslashes( $_POST['_weight'] ) );
			update_post_meta( $post_id, '_length', stripslashes( $_POST['_length'] ) );
			update_post_meta( $post_id, '_width', stripslashes( $_POST['_width'] ) );
			update_post_meta( $post_id, '_height', stripslashes( $_POST['_height'] ) );
		}

		if ( false === $bundle_data = $this->build_bundle_config( $post_id, $_POST ) ) {

			delete_post_meta( $post_id, '_bundle_data' );

			wc_bundles_add_admin_error( __( 'Please add at least one product to the bundle before publishing. To add products, click on the Bundled Products tab.', 'woocommerce-product-bundles' ) );

			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );

			return;

		} else {

			update_post_meta( $post_id, '_bundle_data', $bundle_data );
		}

	}

	/**
	 * Update bundle post_meta on save
	 * @return 	mixed     bundle data array configuration or false if unsuccessful
	 */
	function build_bundle_config( $post_id, $posted_bundle_data ) {

		global $woocommerce_bundles;

		$bundled_product_ids = array();

		if ( ! empty( $posted_bundle_data ) && ! empty( $posted_bundle_data[ 'bundled_ids' ] ) )
			$bundled_product_ids = array_map ( 'absint', $posted_bundle_data[ 'bundled_ids' ] );

		// Process Bundled Product Configuration
		$bundle_data 			= array();
		$ordered_bundle_data 	= array();

		$bundle_data_old 		= get_post_meta( $post_id, '_bundle_data', true );

		$bundled_subs = 0;

		// Now start saving new data
		$bundled_ids 	= array();
		$times 			= array();
		$save_defaults 	= array();

		if ( ! empty( $bundled_product_ids ) ) {

			foreach ( $bundled_product_ids as $id ) {

				$terms 			= get_the_terms( $id, 'product_type' );
				$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

				$is_sub = $woocommerce_bundles->compatibility->is_subscription( $id );

				if ( ( $id && $id > 0 ) && ( $product_type == 'simple' || $product_type == 'variable' || $is_sub ) && ( $post_id != $id ) ) {

					// only allow saving 1 sub
					if ( $is_sub ) {

						if ( $bundled_subs > 0 ) {

							$this->save_errors[] = wc_bundles_add_admin_error( sprintf( __( '\'%s\' (#%s) was not saved. Only one simple Subscription per Bundle is supported.', 'woocommerce-product-bundles' ), get_the_title( $id ), $id ) );
							continue;

						} else
							$bundled_subs++;
					}

					// allow bundling the same item id multiple times by adding a suffix
					if ( ! isset( $times[ $id ] ) ) {

						$times[ $id ] 	= 1;
						$val 			= $id;

					} else {

						// only allow multiple instances of non-sold-individually items
						if ( get_post_meta( $id, '_sold_individually', true ) == 'yes' ) {

							$this->save_errors[] = wc_bundles_add_admin_error( sprintf( __( '\'%s\' (#%s) is sold individually and cannot be bundled more than once.', 'woocommerce-product-bundles' ), get_the_title( $id ), $id ) );
							continue;

						}

						$times[ $id ] += 1;
						$val = $id . '_' . $times[$id];

					}

					$bundled_ids[] = $val;

					$bundle_data[ $val ] = array();

					$bundle_data[ $val ][ 'product_id' ] = $id;

					// Save thumbnail preferences first
					if ( isset( $posted_bundle_data[ 'hide_thumbnail_' . $val ] ) ) {
						$bundle_data[ $val ][ 'hide_thumbnail' ] = 'yes';
					} else {
						$bundle_data[ $val ][ 'hide_thumbnail' ] = 'no';
					}

					// Save title preferences
					if ( isset( $posted_bundle_data[ 'override_title_'.$val ] ) ) {
						$bundle_data[ $val ][ 'override_title' ] = 'yes';
						$bundle_data[ $val ][ 'product_title' ] = isset( $posted_bundle_data[ 'product_title_' . $val ] ) ? $posted_bundle_data[ 'product_title_' . $val ] : '';
					} else {
						$bundle_data[ $val ][ 'override_title' ] = 'no';
					}

					// Save description preferences
					if ( isset( $posted_bundle_data[ 'override_description_' . $val ] ) ) {
						$bundle_data[ $val ][ 'override_description' ] = 'yes';
						$bundle_data[ $val ][ 'product_description' ] = isset( $posted_bundle_data[ 'product_description_' . $val ] ) ? wp_kses_post( stripslashes( $posted_bundle_data[ 'product_description_' . $val ] ) ) : '';
					} else {
						$bundle_data[ $val ][ 'override_description' ] = 'no';
					}

					// Save optional
					if ( isset( $posted_bundle_data[ 'optional_' . $val ] ) ) {
						$bundle_data[ $val ][ 'optional' ] = 'yes';
					} else {
						$bundle_data[ $val ][ 'optional' ] = 'no';
					}

					// Save quantity data
					if ( isset( $posted_bundle_data[ 'bundle_quantity_' . $val ] ) ) {

						if ( is_numeric( $posted_bundle_data[ 'bundle_quantity_' . $val ] ) ) {

							$quantity = (int) $posted_bundle_data[ 'bundle_quantity_' . $val ];
							if ( $quantity > 0 && $posted_bundle_data[ 'bundle_quantity_' . $val ] - $quantity == 0 ) {

								if ( $quantity != 1 && ( get_post_meta( $id, '_sold_individually', true ) == 'yes' || ( get_post_meta( $id, '_downloadable', true ) == 'yes' && get_post_meta( $id, '_virtual', true ) == 'yes' && get_option( 'woocommerce_limit_downloadable_product_qty' ) == 'yes' ) ) ) {

									$this->save_errors[] = wc_bundles_add_admin_error( sprintf( __( '\'%s\' (#%s) is sold individually and cannot be bundled with a quantity higher than 1.', 'woocommerce-product-bundles' ), get_the_title( $id ), $id ) );
									$bundle_data[ $val ][ 'bundle_quantity' ] = 1;

								} else {
									$bundle_data[ $val ][ 'bundle_quantity' ] = (int) $posted_bundle_data[ 'bundle_quantity_'.$val ];
								}
							} else
								$this->save_errors[] = wc_bundles_add_admin_error( sprintf( __( 'The quantity you entered for \'%s%s\' (#%s) was not valid and has been reset. Please enter a positive integer value.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
						}

					} else {
						// if its not there, it means the product was just added
						$bundle_data[ $val ][ 'bundle_quantity' ] = 1;
					}

					// Save sale price data
					if ( isset( $posted_bundle_data[ 'bundle_discount_' . $val ] ) ) {

						if ( is_numeric( $posted_bundle_data[ 'bundle_discount_' . $val ] ) ) {

							$discount = ( float ) wc_bundles_format_decimal( $posted_bundle_data[ 'bundle_discount_' . $val ] );

							if ( $discount < 0 || $discount > 100 ) {

								$this->save_errors[] = wc_bundles_add_admin_error( sprintf( __( 'The discount value you entered for \'%s%s\' (#%s) was not valid and has been reset. Please enter a positive number between 0-100.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
								$bundle_data[ $val ][ 'bundle_discount' ] = '';

							} else {
								$bundle_data[ $val ][ 'bundle_discount' ] = $discount;
							}
						} else {
							$bundle_data[ $val ][ 'bundle_discount' ] = '';
						}
					} else {
						$bundle_data[ $val ][ 'bundle_discount' ] = '';
					}

					// Save data related to variable items
					if ( $product_type == 'variable' ) {

						// Save variation filtering options
						if ( isset( $posted_bundle_data[ 'filter_variations_' . $val ] ) ) {

							if ( isset( $posted_bundle_data[ 'allowed_variations' ][ $val ] ) && count( $posted_bundle_data[ 'allowed_variations' ][ $val ] ) > 0 ) {

								$bundle_data[ $val ][ 'filter_variations' ] = 'yes';

								$bundle_data[ $val ][ 'allowed_variations' ] = $posted_bundle_data[ 'allowed_variations' ][ $val ];

								if ( isset( $posted_bundle_data[ 'hide_filtered_variations_' . $val ] ) )
									$bundle_data[ $val ][ 'hide_filtered_variations' ] = 'yes';
								else
									$bundle_data[ $val ][ 'hide_filtered_variations' ] = 'no';
							}
							else {
								$bundle_data[ $val ][ 'filter_variations' ] = 'no';
								$this->save_errors[] = wc_bundles_add_admin_error( __('Please select at least one variation for each bundled product you want to filter.', 'woocommerce-product-bundles') );
							}
						} else {
							$bundle_data[ $val ][ 'filter_variations' ] = 'no';
						}

						// Save defaults options
						if ( isset( $posted_bundle_data[ 'override_defaults_' . $val ] ) ) {

							if ( isset( $posted_bundle_data[ 'default_attributes' ][ $val ] ) ) {

								// if filters are set, check that the selections are valid

								if ( isset( $posted_bundle_data[ 'filter_variations_' . $val ] ) && isset( $posted_bundle_data[ 'allowed_variations' ][ $val ] ) ) {

									$allowed_variations = $posted_bundle_data[ 'allowed_variations' ][ $val ];

									// the array to store all valid attribute options of the iterated product
									$filtered_attributes = array();

									// populate array with valid attributes
									foreach ( $allowed_variations as $variation ) {

										$product_custom_fields = get_post_custom( $variation );

										foreach ( $product_custom_fields as $name => $value ) :

											if ( ! strstr( $name, 'attribute_' ) ) continue;
											$attribute_name = substr( $name, strlen('attribute_') );

											// ( populate array )
											if ( ! isset( $filtered_attributes[ $attribute_name ] ) ) {
												$filtered_attributes[ $attribute_name ][] = $value[0];
											} elseif ( ! in_array( $value[0], $filtered_attributes[ $attribute_name ] ) ) {
												$filtered_attributes[ $attribute_name ][] = $value[0];
											}

										endforeach;

									}

									// check validity
									foreach ( $posted_bundle_data[ 'default_attributes' ][ $val ] as $name => $value ) {
										if ( $value == '' ) continue;
										if ( ! in_array( $value, $filtered_attributes[ sanitize_title( $name ) ] ) && ! in_array( '', $filtered_attributes[ sanitize_title( $name ) ] ) ) {

											// set option to "Any"
											$posted_bundle_data[ 'default_attributes' ][ $val ][ sanitize_title( $name ) ] = '';

											// throw an error
											$this->save_errors[] = wc_bundles_add_admin_error( sprintf( __( 'The \'%s\' default option that you selected for \'%s%s\' (#%s) is inconsistent with the set of active variations. Always double-check your preferences before saving, and always save any changes made to the variation filters before choosing new defaults.', 'woocommerce-product-bundles' ), ucwords( wc_bundles_attribute_label($name) ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );

											continue;
										}
									}

								}

								$bundle_data[ $val ][ 'override_defaults' ] = 'yes';
							}
						} else {

							$bundle_data[ $val ][ 'override_defaults' ] = 'no';
						}
					}

					// Save visibility preferences
					if ( isset( $posted_bundle_data[ 'visibility_' . $val ] ) ) {

						if ( $posted_bundle_data[ 'visibility_' . $val ] == 'visible' ) {

							$bundle_data[ $val ][ 'visibility' ] = 'visible';

						} elseif ( $posted_bundle_data[ 'visibility_' . $val ] == 'hidden' ) {

							if ( $product_type == 'variable' ) {

								if ( isset( $posted_bundle_data[ 'default_attributes' ][ $val ] ) ) {

									foreach ( $posted_bundle_data[ 'default_attributes' ][ $val ] as $default_name => $default_value ) {

										if ( ! $default_value ) {

											$posted_bundle_data[ 'visibility_' . $val ] = 'visible';
											$this->save_errors[] = wc_bundles_add_admin_error( sprintf( __( '\'%s%s\' (#%s) cannot be hidden unless all default options of the product are defined.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
										}
									}

									$bundle_data[ $val ][ 'visibility' ] = $posted_bundle_data[ 'visibility_' . $val ];

								} else {
									$bundle_data[ $val ][ 'visibility' ] = 'visible';
								}

							} else {
								$bundle_data[ $val ][ 'visibility' ] = 'hidden';
							}

						}

					} else {

						$bundle_data[ $val ][ 'visibility' ] = 'visible';
					}

				}

			}

			if ( isset( $posted_bundle_data[ 'default_attributes' ] ) ) {
				// take out empty attributes (any set) to prepare for saving

				foreach ( $posted_bundle_data[ 'default_attributes' ] as $item_id => $defaults ) {

					if ( ! isset( $bundle_data[ $item_id ] ) )
						continue;

					$bundle_data[ $item_id ][ 'bundle_defaults' ] = array();

					foreach ( $defaults as $default_name => $default_value ) {
						if ( $default_value ) {
							$bundle_data[ $item_id ][ 'bundle_defaults' ][ sanitize_title( $default_name ) ] = $default_value;
						}
					}
				}
			}


			// Ordering

			if ( isset( $posted_bundle_data[ 'bundle_order' ] ) ) {

				$sort_data 	= array_map ( 'stripslashes', $posted_bundle_data[ 'bundle_order' ] );
				asort( $sort_data );

				foreach ( $sort_data as $sorted_item_id => $item_order ) {

					if ( isset( $bundle_data[ $sorted_item_id ] ) ) {
						$ordered_bundle_data[ $sorted_item_id ] = $bundle_data[ $sorted_item_id ];
					}
				}

				foreach ( $bundle_data as $item_id => $item_data ) {

					if ( ! isset( $ordered_bundle_data[ $item_id ] ) ) {
						$ordered_bundle_data[ $item_id ] = $bundle_data[ $item_id ];
					}
				}

			} else {

				$ordered_bundle_data = $bundle_data;
			}

			return $ordered_bundle_data;

		} else {

			return false;
		}

	}

	/**
	 * Add the 'bundle' product type to the product type dropdown.
	 * @param  array    $options    product types array
	 * @return array                modified product types array
	 */
	function woo_bundles_product_selector_filter( $options ) {

		$options[ 'bundle' ] = __( 'Product bundle', 'woocommerce-product-bundles' );

		return $options;
	}

	/**
	 * Handles saving bundle configuration via ajax.
	 * @return void
	 */
	function woo_bundles_ajax_save() {

		check_ajax_referer( 'wc_save_bundle', 'security' );

		parse_str( $_POST[ 'data' ], $posted_data );

		$post_id = absint( $_POST[ 'post_id' ] );

		if ( false !== $bundle_data = $this->build_bundle_config( $post_id, $posted_data ) )

			update_post_meta( $post_id, '_bundle_data', $bundle_data );
		else
			delete_post_meta( $post_id, '_bundle_data' );

		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $this->save_errors );
		die();
	}

	/**
	 * Support scanning for template overrides in extension.
	 * @param  array   $paths paths to check
	 * @return array          modified paths to check
	 */
	function woo_bundles_template_scan_path( $paths ) {

		global $woocommerce_bundles;

		$paths[ 'WooCommerce Product Bundles' ] = $woocommerce_bundles->woo_bundles_plugin_path() . '/templates/';

		return $paths;
	}

	/**
	 * Activation script - if 'bundle' term exists, get rid of it. Not needed anymore.
	 * @return void
	 **/
	function woo_bundles_admin_init() {

		// if 'bundle' term exists, get rid of it
		$bundle_term_id = term_exists( 'bundle' );

		if ( $bundle_term_id && ! get_term_by( 'slug', 'bundle', 'product_type' ) ) {

			$taxonomies = get_taxonomies( '', 'names' );

			foreach ( $taxonomies as $taxonomy ) {
				$bundle_term = get_term_by( 'id', $bundle_term_id, $taxonomy );
				if ( $bundle_term ) {
					wp_update_term( $bundle_term->term_id, $taxonomy, array( 'slug' => 'bundle-99' ) );
					return;
				}
			}
		}

	}

}
