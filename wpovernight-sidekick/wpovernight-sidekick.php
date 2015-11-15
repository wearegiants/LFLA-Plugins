<?php
/*
Plugin Name: WP Overnight Sidekick
Plugin URI: http://wpovernight.com/
Description: WP Overnight Sidekick is an administration plugin for all your WP Overnight Purchases. All WP Overnight themes and plugins will be managed via this plugin.
Version: 1.0.1
Author: Jeremiah Prummer
Author URI: http://wpovernight.com/
License: GPL2
*/

class WPOvernight_Core {
	
	// Setup Variables
	public $main_menu_hook;
	public $get_new_hook;
	public $options_page_hook;

	public function __construct() {
		$this->includes();
		$this->settings = new WPOCore_Settings();

		$this->options = get_option('wpocore-license');

		// Init updater data
		$this->item_name	= 'WP Overnight Sidekick';
		$this->file			= __FILE__;
		$this->license_slug	= 'wpo_core_license';
		$this->version		= '1.0.1';
		$this->author		= 'Jeremiah Prummer';

		$this->updater = new WPO_Updater( $this->item_name, $this->file, $this->license_slug, $this->version, $this->author );

		add_action( 'admin_menu', array( &$this, 'wpo_core_menu' ), 0 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts_styles' ) ); // Load scripts

	}
	
	/**
	 * Load additional classes and functions
	 */
	public function includes() {
		require_once( 'includes/wpo-core-settings.php' );
		require_once( 'includes/wpo-core-updater.php' );
	}

	/** 
	* Add Menu Page 
	*/
	public function wpo_core_menu() {	
		$this->main_menu_hook = add_menu_page('WP Overnight Core', 'WP Overnight', 'manage_options', 'wpo-core-menu', array( &$this, 'wpo_core_page' ), '', '65.001');
		$this->get_new_hook = add_submenu_page( 'wpo-core-menu', 'Get New Plugins', 'Get New Plugins', 'manage_options', 'wpo-plugins-page', array( &$this, 'wpo_plugins_page' ));
	}
	
	/** 
	* Main Page 
	*/
	public function wpo_core_page() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		?>
		<div class="wrap wpocore-main-page">
			<h1>Say Hello to Your WP Overnight Sidekick!</h1>
			<p>With the WP Overnight Sidekick plugin you are always up to date with all our plugins. If you enter your license key in the <a href="?page=wpo-license-page">Manage Licenses</a> section, the plugin will regularly check for updates and notify you of new releases.</p>
			<p>In the <a href="?page=wpo-plugins-page">Get New Plugins</a> section you can find all other plugins and extensions we offer.</p>
			<p>Need help? Send us an email to <a href="mailto:support@wpovernight.com">support@wpovernight.com</a> and we will get back to you as soon as possible.</p>
		</div>
		<?php
	}
	
	/**
	 * Load CSS (and/or scripts)
	 */
	public function load_scripts_styles ( $hook ) {
		global $wp_version;
		
		if( $hook != $this->settings->options_page_hook && $hook != $this->main_menu_hook && $hook != $this->get_new_hook ) 
			return;

		if ( version_compare( $wp_version, '3.8', '>=' ) ) {
			wp_register_style( 'wpovernight-core', plugins_url( '/css/wpovernight-core-mp6.css', __FILE__ ), array(), '', 'all' );
			wp_enqueue_style( 'wpovernight-core' );
		} else {
			wp_register_style( 'wpovernight-core', plugins_url( '/css/wpovernight-core.css', __FILE__ ), array(), '', 'all' );
			wp_enqueue_style( 'wpovernight-core' );
		}

	}

	/** 
	* Get Plugins Page 
	*/
	public function wpo_plugins_page() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<div class="wrap">';
		?>
		<?php include_once(ABSPATH.WPINC.'/feed.php');
			
			$rss = fetch_feed( apply_filters( 'wpovernight_store_url', 'https://wpovernight.com' ) . '/feed/?post_type=download' );
			$maxitems = $rss->get_item_quantity(5);
			$rss_items = $rss->get_items();
		?>
			<ul class="wpo-plugin-shop">
		<?php
			// Loop through each feed item and display each item as a hyperlink.
			foreach ( $rss_items as $item ) :		
		?>
			<li>	
			<a href='<?php echo $item->get_permalink(); ?>' title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>' class='wpo-feed-header'>
			<?php echo $item->get_title(); ?></a>
			<?php echo $item->get_description(); ?>
			<a href='<?php echo $item->get_link(); ?>' class='wpo-read-more'>Read more&rarr;</a>
			</li>
			<?php endforeach; ?>
			</ul>
		<?php
		echo '</div>';
	}
}
$WPO_Core = new WPOvernight_Core();
