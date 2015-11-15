<?php
/**
 * Email Order Items
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include( dirname(__FILE__).'/settings.php' );

foreach ( $items as $item ) :
	$_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
	$item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );
	/****************************** Start Miles Modification **************************/
	$item_post_meta = get_post_meta($item['item_meta']['_product_id'][0]);
	$event_start_string = '';
	if(isset($item_post_meta['_tribe_wooticket_for_event'])) {
		$ticket_meta = get_post_meta($item_post_meta['_tribe_wooticket_for_event'][0]);
		//$event_start_string = '<small>'.date("m-d-Y h:i:s a",strtotime($ticket_meta['_EventStartDate'][0])).'</small>';
		$event_start_string = ''.date("l, M j, h:i a",strtotime($ticket_meta['_EventStartDate'][0])).'';
	}
	/****************************** End Miles Modification   **************************/
	?>
	<tr>
		<td style="<?php echo $missingstyle;?>text-align:left; vertical-align:middle; border: solid 1px <?php echo $bordercolor;?>; word-wrap:break-word;"><?php
			// Show title/image etc
			if ( $show_image ) {
				echo apply_filters( 'woocommerce_order_item_thumbnail', '<img src="' . ( $_product->get_image_id() ? current( wp_get_attachment_image_src( $_product->get_image_id(), 'thumbnail') ) : wc_placeholder_img_src() ) .'" alt="' . __( 'Product Image', 'woocommerce' ) . '" height="' . esc_attr( $image_size[1] ) . '" width="' . esc_attr( $image_size[0] ) . '" style="vertical-align:middle; float:left; margin-right: 10px; border-right:10px solid #fff" />', $item );
			}

			// Product name
			echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );

			// SKU
			if ( $show_sku && is_object( $_product ) && $_product->get_sku() ) {
				echo ' (#' . $_product->get_sku() . ')';
			}

			// File URLs
			if ( $show_download_links && is_object( $_product ) && $_product->exists() && $_product->is_downloadable() ) {

				$download_files = $order->get_item_downloads( $item );
				$i              = 0;

				foreach ( $download_files as $download_id => $file ) {
					$i++;

					if ( count( $download_files ) > 1 ) {
						$prefix = sprintf( __( 'Download %d', 'woocommerce' ), $i );
					} elseif ( $i == 1 ) {
						$prefix = __( 'Download', 'woocommerce' );
					}

					echo '<br/><small style="'.$dlstyle.'">' . $prefix . ': <a href="' . esc_url( $file['download_url'] ) . '" target="_blank" style="'.$dlstyle.'">' . esc_html( $file['name'] ) . '</a></small>';
				}
			}

			// Variation
			if ( $item_meta->meta ) {
				echo '<br/><small style="'.$dlstyle.'">' . nl2br( $item_meta->display( true, true ) ) . '</small>';
			}
		/****************************** Start Miles Modification **************************/
		echo $event_start_string;
		/****************************** End Miles Modification   **************************/
		?></td>
		<td align="center" style="<?php echo $missingstyle;?>text-align:center; vertical-align:middle; border: 1px solid <?php echo $bordercolor;?>;"><?php echo $item['qty'] ;?></td>
		<td align="center" style="<?php echo $missingstyle;?>text-align:center; vertical-align:middle; border: 1px solid <?php echo $bordercolor;?>;"><?php echo $order->get_formatted_line_subtotal( $item ); ?></td>
	</tr>

	<?php if ( $show_purchase_note && is_object( $_product ) && $purchase_note = get_post_meta( $_product->id, '_purchase_note', true ) ) : ?>
		<tr>
			<td colspan="3" style="<?php echo $missingstyle;?>text-align:left; vertical-align:middle; border: 1px solid <?php echo $bordercolor;?>;"><?php echo wpautop( do_shortcode( $purchase_note ) ); ?></td>
		</tr>
	<?php endif; ?>

<?php endforeach; ?>
