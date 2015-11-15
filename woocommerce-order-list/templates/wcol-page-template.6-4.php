<?php $this->order_ids = $order_ids = explode('x',$_GET['order_ids']); ?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">	
	<?php
		require_once(ABSPATH . 'wp-admin/admin.php');
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'media' );
		wp_enqueue_script( 'jquery' );
		do_action('admin_print_styles');
		do_action('admin_print_scripts');
	?>
	<style type="text/css">
	<?php include( $this->get_template_path( 'wcol-styles.css' ) ); ?>
	<?php do_action( 'wpo_wcol_template_styles' ); ?>
	</style>
</head>
<body>

<?php if (isset($this->settings['print_summary']) && $this->settings['print_summary'] == 'first') { ?>
<?php echo $this->get_template( $this->get_template_path( 'wcol-summary-template.php' ) ); ?>
<br/>
<div style="page-break-before: always;"></div>
<?php } ?>

<?php do_action( 'wpo_wcol_before_order_list', $order_ids ); ?>

<table class="order-list">
<thead>
	<tr class="header">
		<?php
		if (isset($this->settings['show_billing_address'])) echo '<th class="billing-address-header">' . __( 'Billing address', 'wpo_wcol' ) . '</th>';
		if (isset($this->settings['show_shipping_address'])) echo '<th class="shipping-address-header">' . __( 'Shipping address', 'wpo_wcol' ) . '</th>';
		if (isset($this->settings['show_order_items'])) echo '<th class="order-items-header">' . __( 'Order items', 'wpo_wcol' ) . '</th>';
		?>
	</tr>
</thead>
<tbody>
	<?php
	foreach ($order_ids as $order_id) {
		// collect order item list and order data
		$order_items = $this->get_order_items( $order_id );
		$order_data = $this->get_order_data( $order_id );
		/****************************** Start Miles Modification **************************/
		$field25 = get_post_meta($order_id, 'myfield25', true );
		$order = new WC_Order($order_id); 
		$fees = $order->get_fees();
		//echo '<pre>'.print_r($fees,true).'</pre>';
		/****************************** End Miles Modification   **************************/
		

		?>
		<tr class="order">
			<div style="page-break-inside: avoid;">
				<table class="order-data">
					<tr class="order-header">
						<td colspan="3">
							<span class="order-number"><?php echo __( 'Order number', 'wpo_wcol' ).': '.$order_data['order_number']; ?></span> 
							<span class="order-date"><?php echo $order_data['order_date']; ?></span>, 
							<span class="order-time"><?php echo $order_data['order_time']; ?></span>
							<span class="checkbox"><input type="checkbox"></span><br/>

							<span class="shipping-method"><?php echo __( 'Shipping method', 'wpo_wcol' ).': '.$order_data['shipping_method']; ?></span> | 
							<span class="payment-method"><?php echo __( 'Payment method', 'wpo_wcol' ).': '.$order_data['payment_method']; ?></span> | 
							<span class="order-status"><?php echo __( 'Status', 'wpo_wcol' ).': '.$order_data['status']; ?></span>

							<?php do_action( 'wpo_wcol_order_header', $order_id ); ?>
						</td>
					</tr>
					<tr>
						<?php if (isset($this->settings['show_billing_address'])) { ?>
						<td class="billing-address">
							<?php echo $order_data['billing_myfield4']. ' ' .$order_data['billing_address']; ?><br/>
							<?php echo $order_data['billing_phone']; ?><br/>
							<?php echo $order_data['billing_email']; ?>
							<?php do_action( 'wpo_wcol_billing_address', $order_id ); ?>
						</td>
						<?php } // endif billing
						if (isset($this->settings['show_shipping_address'])) { ?>
						<td class="shipping-address">
							<?php echo $order_data['billing_myfield4']. ' ' .$order_data['shipping_address']; ?>
							<?php do_action( 'wpo_wcol_shipping_address', $order_id ); ?>
						</td>
						<?php } // endif shipping
						if (isset($this->settings['show_order_items'])) { ?>
						<td class="order-items-cell">
							<table class="widefat order-items">
								<thead>
									<tr>
										<th class="quantity">#</th>
										<?php if (isset($this->settings['show_order_item_sku'])) echo '<th class="sku">' . __( 'SKU', 'wpo_wcol' ) . '</th>'; ?>
										<th><?php _e( 'Product', 'wpo_wcol' ); ?></th>
										<?php if (isset($this->settings['show_order_item_price'])) echo '<th class="price">' . __( 'Price', 'wpo_wcol' ) . '</th>'; ?>
										<?php if (isset($this->settings['show_order_item_weight'])) echo '<th class="weight">' . __( 'Weight', 'wpo_wcol' ) . '</th>'; ?>
									</tr>
								</thead>
								<tbody>
								<?php
		/****************************** Start Miles Modification **************************/
								foreach ($fees as $fee) {
									?>
									<tr>
										<td>1x</td>
										<?php if (isset($this->settings['show_order_item_sku'])) echo '<td></td>'; ?>
										<td><?php echo $fee['name']; ?></td>
										<td>$<?php echo number_format($fee['wc_checkout_add_on_value'],2); ?></td>
									</tr>
								<?php
								}
		/****************************** End Miles Modification   **************************/

								?>
								<?php foreach ($order_items as $product) { ?>
									<tr>
										<td><?php echo $product['quantity'].'x'; ?></td>
										<?php if (isset($this->settings['show_order_item_sku'])) echo '<td>' . $product['sku'] . '</td>'; ?>
										<td>
											<?php
											echo $product['name'];
											if ( isset($this->settings['show_order_item_meta']) && $this->settings['show_order_item_meta'] == 'full' ) {
												echo $product['meta'];
											} else {
												echo $product['variation'];
											}
											do_action( 'wpo_wcol_after_product_description', $order_id );
											?>
										</td>
										<?php if (isset($this->settings['show_order_item_price'])) echo '<td>' . $product['order_price'] . '</td>'; ?>
										<?php if (isset($this->settings['show_order_item_weight'])) echo '<td>' . $product['total_weight'] . get_option('woocommerce_weight_unit') . '</td>'; ?>
									</tr>
								<?php } ?>
								</tbody>
								<?php if (isset($this->settings['show_order_item_price']) || isset($this->settings['show_order_item_weight'])) { ?>
								<tfoot>
									<tr>
										<td></td>
										<?php if (isset($this->settings['show_order_item_sku'])) echo '<td></td>'; ?>
										<td><?php _e( 'Total', 'wpo_wcol' ); ?></td>
										<?php if (isset($this->settings['show_order_item_price'])) echo '<td>' . $order_data['order_total'] . '</td>'; ?>
										<?php if (isset($this->settings['show_order_item_weight'])) echo '<td>' . $order_data['order_weight'] . get_option('woocommerce_weight_unit') . '</td>'; ?>
										
									</tr>
								</tfoot>
								<?php } // endif price or weight ?>
							</table>
						</td>
						<?php } // endif items ?>
					</tr>
					<?php if (!empty($order_data['shipping_notes']) && isset($this->settings['show_shipping_notes'])) { ?>
					<tr class="notes">
						<td colspan="3"><?php echo $order_data['shipping_notes']; ?></td>
					</tr>
					<?php } ?>
					<?php if ( has_action( 'wpo_wcol_custom_row' ) ) { ?>
					<tr class="custom">
						<td colspan="3"><?php do_action( 'wpo_wcol_custom_row', $order_id ); ?></td>
					</tr>
					<?php } 
					
					/****************************** Start Miles Modification **************************/
					?>
					<tr>
						<td colspan="3">Why I chose to give: <?php echo $field25; ?></td>
					</tr>
					<?php
					/****************************** Start Miles Modification **************************/
					?>
				</table>
			</div>
		</tr>
	<?php } ?>
</tbody>
</table>

<?php do_action( 'wpo_wcol_after_order_list', $order_ids ); ?>

<?php if (isset($this->settings['print_summary']) && $this->settings['print_summary'] == 'last') { ?>
<div style="page-break-before: always;"></div>
<?php echo $this->get_template( $this->get_template_path( 'wcol-summary-template.php' ) ); ?>
<?php } ?>

</body>
</html>