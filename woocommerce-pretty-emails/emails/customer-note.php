<?php
/**
 * Customer note email
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php include( dirname(__FILE__).'/settings.php' ); ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php _e( "Hello, a note has just been added to your order:", 'woocommerce' ); ?></p>

<br><div style="padding:20px;border:solid 1px <?php echo $bordercolor;?>;"><?php echo wpautop( wptexturize( $customer_note ) ) ?></div><br>

<p><?php _e( "For your reference, your order details are shown below.", 'woocommerce' ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text ); ?>

<h2 <?php echo $orderref;?>><?php printf( __( 'Order #%s', 'woocommerce' ), $order->get_order_number() ); ?></h2>

<table cellspacing="0" cellpadding="6" style="border-collapse:collapse; width: 100%; border: 1px solid <?php echo $bordercolor;?>;" border="1" bordercolor="<?php echo $bordercolor;?>">
	<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid <?php echo $bordercolor;?>;"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid <?php echo $bordercolor;?>;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid <?php echo $bordercolor;?>;"><?php _e( 'Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php echo $order->email_order_items_table( $order->is_download_permitted(), true ); ?>
	</tbody>
	<?php include( dirname(__FILE__).'/tfoot.php' ); ?>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_footer' ); ?>

<?php include( dirname(__FILE__).'/treatments.php' ); ?>