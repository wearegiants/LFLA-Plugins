<?php
/**
 * Admin cancelled order email
 *
 * @author  WooThemes
 * @package WooCommerce/Templates/Emails
 * @version 2.3.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php include( dirname(__FILE__).'/settings.php' ); ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( 'The order #%d from %s has been cancelled. The order was as follows:', 'woocommerce' ), $order->get_order_number(), $order->billing_first_name . ' ' . $order->billing_last_name ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', $order, true, false ); ?>

<h2><a href="<?php echo admin_url( 'post.php?post=' . $order->id . '&action=edit' ); ?>"><?php printf( __( 'Order: %s', 'woocommerce'), $order->get_order_number() ); ?></a> (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order->order_date ) ), date_i18n( wc_date_format(), strtotime( $order->order_date ) ) ); ?>)</h2>

<table cellspacing="0" cellpadding="6" style="border-collapse:collapse; width: 100%; border: 1px solid <?php echo $bordercolor;?>;" border="1" bordercolor="<?php echo $bordercolor;?>">	<thead>
		<tr>
			<th scope="col" width="50%" style="<?php echo $missingstyle;?>text-align:left; border: 1px solid <?php echo $bordercolor;?>;"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th scope="col" width="25%" style="<?php echo $missingstyle;?>text-align:center; border: 1px solid <?php echo $bordercolor;?>;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th scope="col" width="25%" style="<?php echo $missingstyle;?>text-align:center; border: 1px solid <?php echo $bordercolor;?>;"><?php _e( 'Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php echo $order->email_order_items_table( false, true, false, $displayimage, array($imgsize, $imgsize) ); ?>
	</tbody>
	<?php include( dirname(__FILE__).'/tfoot.php' ); ?>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, true, false ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, true, false ); ?>

<?php do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_footer' ); ?>

<?php include( dirname(__FILE__).'/treatments.php' ); ?>
