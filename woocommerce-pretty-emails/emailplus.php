<?php

/**
 * Plugin Name: WooCommerce Pretty Emails
 * Description: Customize your WooCommerce emails.
 * Version: 1.4.3
 * Author: MB Création
 * Author URI: http://www.mbcreation.net
 * License: http://codecanyon.net/licenses/regular_extended
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * WooCommerce_Pretty_Emails
 * 
 * @since 1.0
 */

if ( ! class_exists( 'WooCommerce_Pretty_Emails' ) ) {

class WooCommerce_Pretty_Emails {


		private $_templates = array();
		
		function __construct(){

			$this->_templates = array(
				
				'emails/email-header.php',
				'emails/email-footer.php',
				'emails/customer-completed-order.php',
				'emails/admin-new-order.php',
				'emails/customer-invoice.php',
				'emails/customer-new-account.php',
				'emails/customer-processing-order.php',
				'emails/customer-reset-password.php',
				'emails/customer-note.php',
				'emails/email-addresses.php',
				'emails/email-order-items.php',
				'emails/admin-cancelled-order.php'
			
			); 
			
			load_plugin_textdomain('mbc-pretty-emails', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
			add_action('plugins_loaded', array(&$this, 'hooks') );
			register_activation_hook( __FILE__ , array(&$this , 'install' ) );

		}

		public function hooks(){

			// Arrêter l'execution du plugin si WooCommerce n'exite plus.
			
			if( !class_exists( 'Woocommerce' ) )
			return;

			add_filter('woocommerce_email_settings', array( $this, 'extra_settings') );
			add_filter('wc_get_template', array( $this, 'custom_template') , 999, 5 );
			add_action( 'admin_init', array( $this, 'preview_emails' ) );



		}


		public function extra_settings($settings){

			$settings[] = array( 'title' => __( 'WooCommerce Pretty Emails', 'mbc-pretty-emails' ), 'type' => 'title', 'desc' => __( 'Please feel free to send us an email if you have any issue at support@mbcreation.net', 'mbc-pretty-emails' ), 'id' => 'email_template_options_extra' );

			if ( $this->get_last_valid_order() ) :

			$settings[] = array( 'title' => __( 'Email Preview', 'mbc-pretty-emails' ), 'type' => 'title',
			 'desc' => sprintf(__( 

					 	'<a href="%s" target="_blank">'.__('New order template','mbc-pretty-emails').'</a> |'.
					 	'<a href="%s" target="_blank">'.__('Customer processing order template','mbc-pretty-emails').'</a> |'.
					 	'<a href="%s" target="_blank">'.__('Customer completed order template','mbc-pretty-emails').'</a> |'.
					 	'<a href="%s" target="_blank">'.__('Customer invoice order template','mbc-pretty-emails').'</a> |'.
					 	'<a href="%s" target="_blank">'.__('Customer note order template','mbc-pretty-emails').'</a> |'.
					 	'<a href="%s" target="_blank">'.__('Customer new account','mbc-pretty-emails').'</a>'  
					 	),
					 	wp_nonce_url(admin_url('?pretty_email_admin_new_order=WC_Email_New_Order'), 'pretty-preview-mail'),
					 	wp_nonce_url(admin_url('?pretty_email_admin_new_order=WC_Email_Customer_Processing_Order'), 'pretty-preview-mail'),
					 	wp_nonce_url(admin_url('?pretty_email_admin_new_order=WC_Email_Customer_Completed_Order'), 'pretty-preview-mail'),
					 	wp_nonce_url(admin_url('?pretty_email_admin_new_order=WC_Email_Customer_Invoice'), 'pretty-preview-mail'),
					 	wp_nonce_url(admin_url('?pretty_email_admin_new_order=WC_Email_Customer_Note'), 'pretty-preview-mail'),
					 	wp_nonce_url(admin_url('?pretty_email_admin_new_order=WC_Email_Customer_New_Account'), 'pretty-preview-mail')
			 		), 
			 'id' => 'email_template_options_extra_preview' 
			 );

			else :

			$settings[] = array( 'title' => __( '! Email Preview is disabled !', 'mbc-pretty-emails' ), 'type' => 'title', 'desc' => __( 'You need to have at least one completed order to enable the preview mode.', 'mbc-pretty-emails' ), 'id' => 'email_template_options_extra' );


			endif;

			
			$settings[] = array(
				'title'    => __( 'Header Image Link', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter here an url to make your banner clickable.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_banner_link',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);


			$settings[] = array(
				'title'    => __( 'Company Logo', 'mbc-pretty-emails' ),
				'desc'     => sprintf(__( 'Enter a URL to display your logo. Upload your image using the <a href="%s">media uploader</a>.', 'mbc-pretty-emails' ), admin_url('media-new.php')),
				'id'       => 'woocommerce_email_mbc_logo',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = 	array(
				'title'    => __( 'Footer Logo width', 'mbc-pretty-emails' ),
				'desc'     => __( 'The footer logo width. Default <code>175</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_template_logo_width',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 0,
						'step' => 5
				),
				'css'      => 'width:6em;',
				'default'  => '175',
				'autoload' => true
			);

			$settings[] = 	array(
				'title'    => __( 'Email Template Width', 'mbc-pretty-emails' ),
				'desc'     => __( 'The email template width. Default <code>700</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_template_width',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 320,
						'step' => 10
				),
				'css'      => 'width:6em;',
				'default'  => '700',
				'autoload' => true
			);


			$settings[] = 	array(
				
				'title'    => __( 'Body font size', 'mbc-pretty-emails' ),
				'desc'     => __( 'Body font size in pixels. Default <code>12</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_bodyfontsize',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 9,
						'step' => 1
				),
				'css'      => 'width:6em;',
				'default'  => '12',
				'autoload' => true
			);

			$settings[] = 	array(
				
				'title'    => __( 'Heading 1 font size', 'mbc-pretty-emails' ),
				'desc'     => __( 'H1 tag font size in pixels. Default <code>18</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_h1size',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 9,
						'step' => 1
				),
				'css'      => 'width:6em;',
				'default'  => '18',
				'autoload' => true
			);

			$settings[] = 	array(
				
				'title'    => __( 'Heading 2 font size', 'mbc-pretty-emails' ),
				'desc'     => __( 'H2 tag font size in pixels. Default <code>18</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_h2size',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 9,
						'step' => 1
				),
				'css'      => 'width:6em;',
				'default'  => '16',
				'autoload' => true
			);

			$settings[] = 	array(
				
				'title'    => __( 'Heading 3 font size', 'mbc-pretty-emails' ),
				'desc'     => __( 'H3 tag font size in pixels. Default <code>18</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_h3size',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 9,
						'step' => 1
				),
				'css'      => 'width:6em;',
				'default'  => '14',
				'autoload' => true
			);

			$settings[] = 	array(
				
				'title'    => __( 'Download link font size', 'mbc-pretty-emails' ),
				'desc'     => __( 'Download link font size in pixels. Default <code>12</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_dlsize',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 9,
						'step' => 1
				),
				'css'      => 'width:6em;',
				'default'  => '12',
				'autoload' => true
			);

			$settings[] = 	array(
				
				'title'    => __( 'Download link font color', 'mbc-pretty-emails' ),
				'desc'     => __( 'Download link font color. Default <code>#000</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_dlcolor',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#000000',
				'autoload' => true
			);

			$settings[] = 	array(
				
				'title'    => __( 'Border color', 'mbc-pretty-emails' ),
				'desc'     => __( 'Surrounding the main table, blockquote and order tables. Default <code>#EEE</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_bordercolor',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#eeeeee',
				'autoload' => true
			);


			$settings[] = array(
				'title'   => __( 'Product Thumbnails', 'mbc-pretty-emails' ),
				'desc'    => __( 'Display product thumbnails in email', 'mbc-pretty-emails' ),
				'id'      => 'woocommerce_email_mbc_displayimage',
				'type'    => 'checkbox',
				'default' => 'yes',
			);


			$settings[] = 	array(
				'title'    => __( 'Product Thumbnails Size', 'mbc-pretty-emails' ),
				'desc'     => __( 'Product Thumbnails Size. Default <code>32</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_displayimage_size',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 32,
						'step' => 1
				),
				'css'      => 'width:6em;',
				'default'  => '32',
				'autoload' => true
			);
			



			$settings[] = array(
					'title'    => __( 'Extra link one', 'mbc-pretty-emails' ),
					'id'       => 'woocommerce_email_mbc_extra_link_one',
					'type'     => 'single_select_page',
					'default'  => '',
					'class'    => 'chosen_select_nostd',
					'css'      => 'min-width:300px;',
					'desc_tip' => __( 'Display a link in your email header.', 'mbc-pretty-emails' ),
			);

			$settings[] = array(
					'title'    => __( 'Extra link two', 'mbc-pretty-emails' ),
					'id'       => 'woocommerce_email_mbc_extra_link_two',
					'type'     => 'single_select_page',
					'default'  => '',
					'class'    => 'chosen_select_nostd',
					'css'      => 'min-width:300px;',
					'desc_tip' => __( 'Display a link in your email header.', 'mbc-pretty-emails' ),
			);

			$settings[] = array(
					'title'    => __( 'Extra link three', 'mbc-pretty-emails' ),
					'id'       => 'woocommerce_email_mbc_extra_link_three',
					'type'     => 'single_select_page',
					'default'  => '',
					'class'    => 'chosen_select_nostd',
					'css'      => 'min-width:300px;',
					'desc_tip' => __( 'Display a link in your email header.', 'mbc-pretty-emails' ),
			);

			$settings[] = 	array(
				
				'title'    => __( 'Extra link colors', 'mbc-pretty-emails' ),
				'desc'     => __( 'I you add nav menu in your header (extra links), then you can set a color. Default <code>#0000EE</code>.', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_extra_link_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#0000EE',
				'autoload' => true
			);


			$settings[] = array(
				'title'    => __( 'Facebook page URL', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Facebook page url to display your Facebook logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_facebook',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Facebook custom logo', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Facebook custom image url to display your own Facebook logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_facebook_img',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);


			$settings[] = array(
				'title'    => __( 'Twitter Profil URL', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Twitter profile page url to display your Twitter logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_twitter',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Twitter custom logo', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Twitter custom image url to display your own Twitter logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_twitter_img',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Instagram profile URL', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Instagram profile page url to display Instagram logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_instagram',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Instagram custom logo', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Instagram custom image url to display your own Instagram logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_instagram_img',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Pinterest profile URL', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Pinterest profile page url to display Pinterest logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_pinterest',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Pinterest custom logo', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Pinterest custom image url to display your own Pinterest logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_pinterest_img',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Google+ profile URL', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Google+ profile page url to display Google+ logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_google',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Google + custom logo', 'mbc-pretty-emails' ),
				'desc'     => __( 'Enter your Google + custom image url to display your own Google + logo in footer', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_google_img',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Introduction for customer processing order email', 'mbc-pretty-emails' ),
				'desc'     => __( 'The text to appear in the introduction for the customer processing order email', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_cpo_intro',
				'css'      => 'width:100%; height: 75px;',
				'type'     => 'textarea',
				'default'  => __( "Your order has been received and is now being processed. Your order details are shown below for your reference:", 'woocommerce' ),
				'autoload' => true
			);


			$settings[] = array(
				'title'    => __( 'Introduction for customer completed order email', 'mbc-pretty-emails' ),
				'desc'     => __( 'The text to appear in the introduction for the customer completed order email', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_cco_intro',
				'css'      => 'width:100%; height: 75px;',
				'type'     => 'textarea',
				'default'  => sprintf( __( "Hi there. Your recent order on %s has been completed. Your order details are shown below for your reference:", 'woocommerce' ), get_option( 'blogname' ) ),
				'autoload' => true
			);
			
			$settings[] = array(
				'title'    => __( 'Introduction for customer new account email', 'mbc-pretty-emails' ),
				'desc'     => __( 'The text to appear in the introduction for the customer new account email', 'mbc-pretty-emails' ),
				'id'       => 'woocommerce_email_mbc_cna_intro',
				'css'      => 'width:100%; height: 75px;',
				'type'     => 'textarea',
				'autoload' => true
			);

			$settings[] = array( 'type' => 'sectionend', 'id' => 'email_template_options_extra');

		
			return $settings;

		}

		public function install(){

			// Ne pas installer le plugin si WooCommerce n'est pas installer.

			if( !class_exists( 'Woocommerce' ) )
			wp_die('WooCommerce extension not found !');

		}


		public function custom_template($located, $template_name, $args, $template_path, $default_path){

			if( in_array( $template_name, $this->_templates ) )
			return plugin_dir_path( __FILE__ ).$template_name;

			return $located;
				
		}


		/**
		 * Preview email template
		 * @since 1.4
		 * @return string
		 */

		public function preview_emails() {

			if ( isset( $_GET['pretty_email_admin_new_order'] ) ) {
				
				$_preview_order_id = $this->get_last_valid_order();
				
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'pretty-preview-mail') ) {
					die( 'Security check' );
				}

				if( $_preview_order_id ) :

				switch ($_GET['pretty_email_admin_new_order']) {

					case 'WC_Email_New_Order':

						$email = WC()->mailer();
						$email->emails['WC_Email_New_Order']->object = wc_get_order( $_preview_order_id );
						echo $email->emails['WC_Email_New_Order']->get_content_html();

						break;
					
					case 'WC_Email_Customer_Processing_Order':
						
						$email = WC()->mailer();
						$email->emails['WC_Email_Customer_Processing_Order']->object = wc_get_order( $_preview_order_id );
						echo $email->emails['WC_Email_Customer_Processing_Order']->get_content_html();
						
						break;

						
					case 'WC_Email_Customer_Completed_Order':
						
						$email = WC()->mailer();
						$email->emails['WC_Email_Customer_Completed_Order']->object = wc_get_order( $_preview_order_id );
						echo $email->emails['WC_Email_Customer_Completed_Order']->get_content_html();
						
						break;

					case 'WC_Email_Customer_Invoice':

						$email = WC()->mailer();
						$email->emails['WC_Email_Customer_Invoice']->object = wc_get_order( $_preview_order_id );
						echo $email->emails['WC_Email_Customer_Invoice']->get_content_html();
						
						break;

					case 'WC_Email_Customer_Note':

						$email = WC()->mailer();
						$email->emails['WC_Email_Customer_Note']->object = wc_get_order( $_preview_order_id );
						$email->emails['WC_Email_Customer_Note']->customer_note = 'Lorem Ipsum';
						echo $email->emails['WC_Email_Customer_Note']->get_content_html();
						
					break;

					case 'WC_Email_Customer_New_Account':

						$email = WC()->mailer();
						$email->emails['WC_Email_Customer_New_Account']->object = wc_get_order( $_preview_order_id );
						$email->emails['WC_Email_Customer_New_Account']->user_login = 'Login';
						$email->emails['WC_Email_Customer_New_Account']->user_pass = 'Pass';
						$email->emails['WC_Email_Customer_New_Account']->password_generated = '******';
						echo $email->emails['WC_Email_Customer_New_Account']->get_content_html();
						
					break;
						
				}

				endif;
			
				exit;
			}
		}

		public function get_last_valid_order(){

			$args = array(
				'posts_per_page'   => 1,
				'offset'           => 0,
				'orderby'          => 'post_date',
				'order'            => 'DESC',
				'post_type'        => 'shop_order',
				'post_status'      => 'wc-completed',
				'suppress_filters' => true );

			$orders = get_posts( $args );
			if( !empty($orders) )
				return  $orders[0]->ID;

			return false;
			
		}


	}
	
	
	$load = new WooCommerce_Pretty_Emails();

}