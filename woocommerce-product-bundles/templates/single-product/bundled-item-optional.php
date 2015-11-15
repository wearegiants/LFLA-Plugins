<?php
/**
 * Optional Bundled Item Checkbox.
 * @version 4.7.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

?>
<label class="bundled_product_optional_checkbox">
	<input class="bundled_product_checkbox" type="checkbox" name="<?php echo apply_filters( 'woocommerce_product_bundle_field_prefix', '', $bundled_item->product_id ); ?>bundle_selected_optional_<?php echo $bundled_item->item_id; ?>" value="" <?php checked( $bundled_item->is_optional_checked() && $is_in_stock, true ); echo $is_in_stock ? '' : 'disabled="disabled"'; ?> /> <?php

		if ( $bundled_item->is_priced_per_product() )
			echo sprintf( __( 'Add &quot;%1$s%2$s&quot; for', 'woocommerce-product-bundles' ), $bundled_item->get_raw_title(), ( $quantity > 1 ? ' &times; '. $quantity : '' ) ) . ' ' . $bundled_item->product->get_price_html();
		else
			echo sprintf( __( 'Add &quot;%1$s%2$s&quot;', 'woocommerce-product-bundles' ), $bundled_item->get_raw_title(), ( $quantity > 1 ? ' &times; '. $quantity : '' ) );
	?>
</label>
