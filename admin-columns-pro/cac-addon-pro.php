<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The Admin Columns Pro plugin class
 *
 * @since 1.0
 */
class CAC_Addon_Pro {

	/**
	 * Basename of the plugin, retrieved through plugin_basename function
	 *
	 * @since 1.0
	 * @access private
	 * @var string
	 */
	private $plugin_basename;

	/**
	 * License manager class instance
	 *
	 * @since 1.0
	 * @access private
	 * @var Codepress_Licence_Manager_Settings
	 */
	private $licence_manager;

	/**
	 * @since 1.0
	 */
	function __construct() {

		$this->plugin_basename = plugin_basename( __FILE__ );

		$this->define_constants();

		// Load modules
		$this->init();

		// Hooks
		add_action( 'init', array( $this, 'localize' ) );
		add_action( 'cac/loaded', array( $this, 'init_after_cac_loaded' ) );
		add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 1, 2 );
		add_action( 'wp_loaded', array( $this, 'third_party' ) );
		add_action( 'wp_loaded', array( $this, 'after_setup' ) );
	}

	/**
	 * Define constants
	 *
	 * @since 3.1.2
	 */
	public function define_constants() {

		define( 'CAC_PRO_VERSION', 	ACP_VERSION );
		define( 'CAC_PRO_URL', 		plugin_dir_url( __FILE__ ) );
		define( 'CAC_PRO_DIR', 		plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Handle localization
	 *
	 * @since 1.0.1
	 * @uses load_plugin_textdomain()
	 */
	public function localize() {

		load_plugin_textdomain( 'cpac', false, dirname( $this->plugin_basename ) . '/languages/' );
	}

	/**
	 * Fire callbacks for admin columns setup completion
	 *
	 * @since 2.2
	 */
	public function after_setup() {

		/**
		 * Fires when Admin Columns is fully loaded
		 * Use this for setting up addon functionality
		 *
		 * @since 2.0
		 * @param CPAC $cpac_instance Main Admin Columns plugin class instance
		 */
		do_action( 'cac/pro/loaded', $this );
	}

	/**
	 * General plugin initialization, loading plugin module files
	 *
	 * @since 1.0
	 */
	public function init() {

		if ( ! class_exists('CAC_Export_Import') ) {
			include_once 'classes/export-import/export-import.php';
		}

		if ( ! class_exists('CAC_Addon_Filtering') ) {
			include_once 'classes/filtering/filtering.php';
		}

		if ( ! class_exists('CAC_Addon_Sortable') ) {
			include_once 'classes/sortable/sortable.php';
		}

		if ( ! class_exists('CAC_Storage_Model_Taxonomy') ) {
			include_once 'classes/taxonomy/taxonomy.php';
		}

		if ( ! class_exists('CPAC_Storage_Model_MS_User') ) {
			include_once 'classes/ms-user/ms-user.php';
		}

		if ( ! class_exists('CACIE_Addon_InlineEdit') ) {
			include_once 'classes/inline-edit/cac-addon-inline-edit.php';
		}

		if ( ! class_exists('CACIE_Addon_Columns') ) {
			// @todo: move pro columns here
			//include_once 'classes/columns/cac-addon-columns.php';
		}
	}

	/**
	 * Load third party add-ons
	 *
	 * @since 3.4.1
	 */
	public function third_party() {

		// WordPress SEO
		include_once 'classes/third-party/wordpress-seo.php';
	}

	/**
	 * Init callback after main plugin (CPAC) has been fully loaded.
	 *
	 * @since 1.0
	 */
	public function init_after_cac_loaded( $cpac ) {

		if ( ! class_exists('Codepress_Licence_Manager_Settings') ) {

			include_once 'classes/licence-manager-settings.php';

			// When used into Admin Columns Pro use it's root path...
			$this->licence_manager = new Codepress_Licence_Manager_Settings( ACP_FILE, $cpac );

			if ( defined( 'ACP_LICENCE' ) ) {
				$this->licence_manager->set_licence_key( ACP_LICENCE );
			}
		}
	}

	/**
	 * @since 1.0
	 * @see filter:plugin_action_links
	 */
	public function add_settings_link( $links, $file ) {

		if ( ( ! $this->is_cpac_enabled() ) || ( $file != plugin_basename( __FILE__ ) ) ) {
			return $links;
		}

		array_unshift( $links, '<a href="' . admin_url("options-general.php") . '?page=codepress-admin-columns&tab=settings">' . __( 'Settings' ) . '</a>' );
		return $links;
	}

	/**
	 * Check if main plugin is enabled
	 *
	 * @since 1.0.3
	 */
	public function is_cpac_enabled() {

		return class_exists('CPAC');
	}

	/**
	 * Get licence manager
	 *
	 * @since 3.1.1
	 */
	public function get_licence_manager() {
		return $this->licence_manager;
	}
}

new CAC_Addon_Pro();
