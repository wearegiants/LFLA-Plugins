<?php
/**
 * Plugin Name: WooCommerce Print Order List
 * Plugin URI: http://www.wpovernight.com
 * Description: Print out a list of selected WooCommerce order with all the information needed to collect the ordered products
 *  or contact the customer
 * Version: 1.3.1
 * Author: Ewout Fernhout
 * Author URI: http://www.wpovernight.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: wpo_wcol
 */

if ( !class_exists( 'WooCommerce_Order_List' ) ) {

	class WooCommerce_Order_List {
		public static $plugin_basename;
		public static $plugin_path;
		public static $plugin_url;

		public $writepanels;
		public $settings;
		public $print;

		/**
		 * Constructor
		 */
		public function __construct() {
			self::$plugin_basename = plugin_basename(__FILE__);
			self::$plugin_path = plugin_dir_path( __FILE__ );
			self::$plugin_url = plugin_dir_url( __FILE__ );

			// load the localisation & classes
			add_action( 'plugins_loaded', array( $this, 'translations' ) ); // or use init?
			add_action( 'init', array( $this, 'load_classes' ) );

			// Init updater data
			$this->item_name	= 'WooCommerce Print Order List';
			$this->file			= __FILE__;
			$this->license_slug	= 'wcol_license';
			$this->version		= '1.3.1';
			$this->author		= 'Ewout Fernhout';

			add_action( 'init', array( &$this, 'load_updater' ), 0 );
		}

		/**
		 * Run the updater scripts from the Sidekick
		 * @return void
		 */
		public function load_updater() {
			// Check if sidekick is loaded
			if (class_exists('WPO_Updater')) {
				$this->updater = new WPO_Updater( $this->item_name, $this->file, $this->license_slug, $this->version, $this->author );
			}
		}

		/**
		 * Load the translation / textdomain files
		 */
		public function translations() {
			load_plugin_textdomain( 'wpo_wcol', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
		}

		/**
		 * Load the main plugin classes and functions
		 */
		public function includes() {
			include_once( 'includes/wcol-settings.php' );
			include_once( 'includes/wcol-writepanels.php' );
			include_once( 'includes/wcol-print.php' );
		}
		

		/**
		 * Instantiate classes when woocommerce is activated
		 */
		public function load_classes() {
			if ( $this->is_woocommerce_activated() ) {
				$this->includes();
				$this->settings = new WooCommerce_Order_List_Settings();
				$this->writepanels = new WooCommerce_Order_List_Writepanels();
				$this->print = new WooCommerce_Order_List_Print();
			} else {
				// display notice instead
				add_action( 'admin_notices', array ( $this, 'need_woocommerce' ) );
			}

		}

		/**
		 * Check if woocommerce is activated
		 */
		public function is_woocommerce_activated() {
			$blog_plugins = get_option( 'active_plugins', array() );
			$site_plugins = get_site_option( 'active_sitewide_plugins', array() );

			if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * WooCommerce not active notice.
		 *
		 * @return string Fallack notice.
		 */
		 
		public function need_woocommerce() {
			$error = sprintf( __( 'WooCommerce Print Order List requires %sWooCommerce%s to be installed & activated!' , 'wpo_wcol' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );

			$message = '<div class="error"><p>' . $error . '</p></div>';
		
			echo $message;
		}

	}
}

// Load main plugin class
$wpo_wcol = new WooCommerce_Order_List();

/**
 * WPOvernight updater admin notice
 */
if ( ! class_exists( 'WPO_Updater' ) && ! function_exists( 'wpo_updater_notice' ) ) {

	if ( ! empty( $_GET['hide_wpo_updater_notice'] ) ) {
		update_option( 'wpo_updater_notice', 'hide' );
	}

	/**
	 * Display a notice if the "WP Overnight Sidekick" plugin hasn't been installed.
	 * @return void
	 */
	function wpo_updater_notice() {
		$wpo_updater_notice = get_option( 'wpo_updater_notice' );

		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$plugin = 'wpovernight-sidekick/wpovernight-sidekick.php';

		if ( in_array( $plugin, $blog_plugins ) || isset( $site_plugins[$plugin] ) || $wpo_updater_notice == 'hide' ) {
			return;
		}

		echo '<div class="updated fade"><p>Install the <strong>WP Overnight Sidekick</strong> plugin to receive updates for your WP Overnight plugins - check your order confirmation email for more information. <a href="'.add_query_arg( 'hide_wpo_updater_notice', 'true' ).'">Hide this notice</a></p></div>' . "\n";
	}

	add_action( 'admin_notices', 'wpo_updater_notice' );
}

