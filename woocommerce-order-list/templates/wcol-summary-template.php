<table class="widefat summary">
	<thead>
		<tr class="header">
			<th class="sku"><?php _e( 'SKU', 'wpo_wcol' ); ?></th>
			<th class="product"><?php _e( 'Product', 'wpo_wcol' ); ?></th>
			<th class="quantity"><?php _e( 'Quantity', 'wpo_wcol' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$summary = $this->get_summary( $this->order_ids );
		foreach ($summary as $id => $data) {
		?>
		<tr>
			<td class="sku"><?php echo $data['sku']; ?></td>
			<td class="product">
				<div class="product-name"><?php echo $data['name']; ?></div>
				<?php if (isset($data['variation'])) echo $data['variation']; ?>
			</td>
			<td class="quantity"><?php echo $data['quantity']; ?></td>
		</tr>
		<?php } // end foreach summary ?>
	</tbody>
</table>