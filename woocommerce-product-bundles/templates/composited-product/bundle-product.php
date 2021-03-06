<?php
/**
 * Composited Product Bundle Template
 * @version  4.7.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

global $woocommerce, $woocommerce_bundles, $woocommerce_composite_products;

if ( $hide_product_title != 'yes' )
	wc_composite_get_template( 'composited-product/title.php', array(
		'title'      => $product->get_title(),
		'product_id' => $product->id,
		'quantity'   => $quantity_min == $quantity_max && $quantity_min > 1 && $product->sold_individually !== 'yes' ? $quantity_min : ''
	), '', $woocommerce_composite_products->plugin_path() . '/templates/' );

if ( $hide_product_thumbnail != 'yes' )
	wc_composite_get_template( 'composited-product/image.php', array(
		'product_id' => $product->id
	), '', $woocommerce_composite_products->plugin_path() . '/templates/' );

?><div class="details component_data" data-component_set="" data-price="0" data-regular_price="0" data-product_type="bundle"><?php

	if ( $hide_product_description != 'yes' )
		wc_composite_get_template( 'composited-product/excerpt.php', array(
			'product_description' => $product->post->post_excerpt,
			'product_id' => $product->id
		), '', $woocommerce_composite_products->plugin_path() . '/templates/' );

	foreach ( $bundled_items as $bundled_item ) {

		$bundled_item_id 	= $bundled_item->item_id;
		$bundled_product 	= $bundled_item->product;
		$item_quantity 		= $bundled_item->quantity;

		?><div class="bundled_product bundled_product_summary product" <?php echo ( ! $bundled_item->is_visible() ? 'style=display:none;' : '' ); ?> ><?php

			// title template
			wc_bundles_get_template( 'single-product/bundled-item-title.php', array(
					'quantity'   => $item_quantity,
					'title'      => $bundled_item->get_title(),
					'optional'   => $bundled_item->is_optional()
				), false, $woocommerce_bundles->woo_bundles_plugin_path() . '/templates/' );


			if ( $bundled_item->is_visible() ) {

				// image template
				if ( $bundled_item->is_thumbnail_visible() )
					wc_bundles_get_template( 'single-product/bundled-item-image.php', array( 'post_id' => $bundled_product->id ), false, $woocommerce_bundles->woo_bundles_plugin_path() . '/templates/' );
			}

			?><div class="details"><?php

				// description template
				wc_bundles_get_template( 'single-product/bundled-item-description.php', array(
						'description' 			=> $bundled_item->get_description()
					), false, $woocommerce_bundles->woo_bundles_plugin_path() . '/templates/' );

				// Availability
				$availability = $woocommerce_bundles->helpers->get_bundled_product_availability( $bundled_product, $item_quantity );

				if ( $bundled_product->product_type == 'simple' ) {

					$bundled_item->add_price_filters();

					if ( $bundled_item->is_optional() ) {

						// optional checkbox template
						wc_bundles_get_template( 'single-product/bundled-item-optional.php', array(
								'quantity'       => $item_quantity,
								'bundled_item'   => $bundled_item,
								'is_in_stock'    => isset( $availability[ 'class' ] ) && $availability[ 'class' ] != 'out-of-stock'
							), false, $woocommerce_bundles->woo_bundles_plugin_path() . '/templates/' );

					} else {

						wc_bundles_get_template( 'single-product/bundled-item-price.php', array(
							'bundled_item' => $bundled_item ), false, $woocommerce_bundles->woo_bundles_plugin_path() . '/templates/' );
					}

					?><div class="cart" data-optional="<?php echo $bundled_item->is_optional() ? true : false; ?>" data-type="<?php echo $bundled_product->product_type; ?>" data-bundled-item-id="<?php echo $bundled_item_id; ?>" data-product_id="<?php echo $product->id . str_replace( '_', '', $bundled_item_id ); ?>" data-bundle-id="<?php echo $product->id; ?>"><?php

						if ( $availability[ 'availability' ] )
							echo apply_filters( 'woocommerce_stock_html', '<p class="stock '. $availability[ 'class' ] .'">' . $availability[ 'availability' ] . '</p>', $availability[ 'availability' ] );

						?><div class="bundled_item_wrap">
							<div class="bundled_item_optional_content" style="<?php echo $bundled_item->is_optional() && ! $bundled_item->is_optional_checked() ? 'display:none;' : ''; ?>"><?php

								// Compatibility with plugins that normally hook to woocommerce_before_add_to_cart_button
								do_action( 'woocommerce_bundled_product_add_to_cart', $bundled_product->id, $bundled_item );

								$bundled_item->remove_price_filters();

								?><div class="quantity" style="display:none;"><input class="qty bundled_qty" type="hidden" name="bundled_item_quantity" value="<?php echo $item_quantity; ?>" /></div>
							</div>
						</div>
					</div><?php

				} elseif ( $bundled_product->product_type == 'variable' ) {

					$bundled_item->add_price_filters();

					if ( $bundled_item->is_optional() ) {

						// optional checkbox template
						wc_bundles_get_template( 'single-product/bundled-item-optional.php', array(
								'quantity'       => $item_quantity,
								'bundled_item'   => $bundled_item,
								'is_in_stock'    => isset( $availability[ 'class' ] ) && $availability[ 'class' ] != 'out-of-stock'
							), false, $woocommerce_bundles->woo_bundles_plugin_path() . '/templates/' );

					}

					?><div class="cart bundled_item_optional_content" style="<?php echo $bundled_item->is_optional() && ! $bundled_item->is_optional_checked() ? 'display:none;' : ''; ?>" data-optional="<?php echo $bundled_item->is_optional() ? true : false; ?>" data-type="<?php echo $bundled_product->product_type; ?>" data-product_variations="<?php echo esc_attr( json_encode( $available_variations[ $bundled_item_id ] ) ); ?>" data-bundled-item-id="<?php echo $bundled_item_id; ?>" data-product_id="<?php echo $product->id . str_replace('_', '', $bundled_item_id); ?>" data-bundle-id="<?php echo $product->id; ?>">
						<table class="variations" cellspacing="0">
							<tbody><?php
							$loop = 0;
							foreach ( $attributes[ $bundled_item_id ] as $name => $options ) {
								$loop++; ?>
								<tr>
									<td class="label">
										<label for="<?php echo sanitize_title( $name ) . '_' . $bundled_item_id; ?>"><?php if ( function_exists( 'ssc_remove_accents' ) ) { echo ssc_remove_accents( wc_bundles_attribute_label( $name ) ); } else { echo wc_bundles_attribute_label( $name ); } ?> <abbr class="required" title="required">*</abbr></label>
									</td>
									<td class="value">
										<select id="<?php echo esc_attr( sanitize_title( $name ) . '_' . $bundled_item_id ); ?>" name="attribute_<?php echo sanitize_title( $name ); ?>">
											<option value=""><?php echo __( 'Choose an option', 'woocommerce' ) ?>&hellip;</option><?php

											if ( is_array( $options ) ) {

												if ( isset( $_REQUEST[ 'component_' . $component_id . '_bundle_attribute_' . sanitize_title( $name ) . '_' . $bundled_item_id ] ) ) {
													$selected_value = $_REQUEST[ 'component_' . $component_id . '_bundle_attribute_' . sanitize_title( $name ) . '_' . $bundled_item_id ];
												} elseif ( isset( $selected_attributes[ $bundled_item_id ][ sanitize_title( $name ) ] ) ) {
													$selected_value = $selected_attributes[ $bundled_item_id ][ sanitize_title( $name ) ];
												} else {
													$selected_value = '';
												}

												// Placeholder: Do not show filtered-out (disabled) options

												if ( taxonomy_exists( sanitize_title( $name ) ) ) {

													$orderby = wc_bundles_attribute_order_by( sanitize_title( $name ) );

													switch ( $orderby ) {
														case 'name' :
															$args = array( 'orderby' => 'name', 'hide_empty' => false, 'menu_order' => false );
														break;
														case 'id' :
															$args = array( 'orderby' => 'id', 'order' => 'ASC', 'menu_order' => false );
														break;
														case 'menu_order' :
															$args = array( 'menu_order' => 'ASC' );
														break;
													}

													$terms = get_terms( sanitize_title( $name ), $args );

													foreach ( $terms as $term ) {
														if ( ! in_array( $term->slug, $options ) )
															continue;

														echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $term->slug ), false ) . '>' . apply_filters( 'woocommerce_variation_option_name', $term->name ) . '</option>';
													}
												} else {

													foreach ( $options as $option ) {
														echo '<option value="' . esc_attr( sanitize_title( $option ) ) . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $option ), false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
													}
												}
											}
										?></select><?php

										if ( sizeof( $attributes[ $bundled_item_id ] ) == $loop ) {
											echo '<a class="reset_variations" href="#reset_' . $bundled_item_id .'">'.__( 'Clear selection', 'woocommerce' ).'</a>';
										}
									?></td>
								</tr><?php

								}

							?></tbody>
						</table><?php

						// Compatibility with plugins that normally hook to woocommerce_before_add_to_cart_button
						do_action( 'woocommerce_bundled_product_add_to_cart', $bundled_product->id, $bundled_item );

						$bundled_item->remove_price_filters();

						?>

						<div class="single_variation_wrap bundled_item_wrap" style="display:none;">
							<div class="single_variation"></div>
							<div class="variations_button">
								<input type="hidden" name="variation_id" value="" />
								<input class="qty bundled_qty" type="hidden" name="bundled_item_quantity" value="<?php echo $item_quantity; ?>" />
							</div>
						</div>

					</div><?php
				}

			?></div>
		</div><?php
	}

	?><div class="cart bundle_data bundle_data_<?php echo $product->id; ?>" data-button_behaviour="<?php echo esc_attr( apply_filters( 'woocommerce_bundles_button_behaviour', 'old', $product ) ); ?>" data-bundle_price_data="<?php echo esc_attr( json_encode( $bundle_price_data ) ); ?>" data-bundled_item_quantities="<?php echo esc_attr( json_encode( $bundled_item_quantities ) ); ?>" data-bundle-id="<?php echo $product->id; ?>"><?php

		// Add-ons
		do_action( 'woocommerce_composite_product_add_to_cart', $product->id, $component_id, $product );

		?><div class="bundle_wrap component_wrap" style="<?php echo apply_filters( 'woocommerce_bundles_button_behaviour', 'old', $product ) == 'new' ? '' : 'display:none'; ?>">
			<div class="bundle_price"></div><?php

				// Bundle Availability
				$availability = $product->get_availability();

				if ( $availability[ 'availability' ] )
					echo apply_filters( 'woocommerce_stock_html', '<p class="stock ' . $availability[ 'class' ] . '">' . $availability[ 'availability' ] . '</p>', $availability[ 'availability' ] );

			?><div class="bundle_button"><?php

				foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

					$bundled_item_id = $bundled_item->item_id;
					$bundled_product = $bundled_item->product;

					if ( $bundled_product->product_type == 'variable' ) {

						?><input type="hidden" name="component_<?php echo $component_id; ?>_bundle_variation_id_<?php echo $bundled_item_id; ?>" class="bundle_variation_id_<?php echo $bundled_item_id; ?>" value="" /><?php
							foreach ( $attributes[ $bundled_item_id ] as $name => $options ) { ?>
								<input type="hidden" name="component_<?php echo $component_id; ?>_bundle_attribute_<?php echo sanitize_title( $name ); ?>_<?php echo $bundled_item_id; ?>" class="bundle_attribute_<?php echo sanitize_title( $name ); ?>_<?php echo $bundled_item_id; ?>" value=""><?php
							}
					}
				}

				wc_composite_get_template( 'composited-product/quantity.php', array(
					'quantity_min' => $quantity_min,
					'quantity_max' => $quantity_max,
					'component_id' => $component_id,
					'product'      => $product
				), '', $woocommerce_composite_products->plugin_path() . '/templates/' );

			?></div>
		</div>
	</div>
</div>
