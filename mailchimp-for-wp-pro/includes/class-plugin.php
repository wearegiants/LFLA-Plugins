<?php

if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP {

	/**
	* @var MC4WP_Form_Manager
	*/
	private $form_manager;

	/**
	* @var MC4WP_Checkbox_Manager
	*/
	private $checkbox_manager;

	/**
	* @var MC4WP_API
	*/
	private $api = null;

	/**
	* @var MC4WP_Logger
	*/
	private $log;

	/**
	 * @var
	 */
	private static $instance;

	/**
	 * @return MC4WP
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Create an instance of the plugin
	 */
	public static function init() {

		if( self::$instance instanceof MC4WP ) {
			return false;
		}

		self::$instance = new MC4WP();
		return true;
	}

	/**
	* Constructor
	*/
	private function __construct() {

		spl_autoload_register( array( $this, 'autoload') );

		// init checkboxes
		$this->checkbox_manager = new MC4WP_Checkbox_Manager();

		// init forms
		$this->form_manager = new MC4WP_Form_Manager();

		// init logger, only if it's not disabled
		$disable_logging = apply_filters( 'mc4wp_disable_logging', false );
		if( false === $disable_logging ) {
			// initialize logging class
			$this->log = new MC4WP_Logger();
		}

		// init widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

	public function autoload( $class_name ) {

		static $classes = null;

		// build array with all plugin classes
		if( $classes === null ) {

			$classes = array(
				'MC4WP_API'                             => 'class-api.php',
				'MC4WP_Checkbox_Manager'                => 'class-checkbox-manager.php',
				'MC4WP_Form_Manager'                    => 'class-form-manager.php',
				'MC4WP_Form_Request'                    => 'class-form-request.php',
				'MC4WP_Logger'                             => 'class-logger.php',
				'MC4WP_Statistics'                      => 'admin/class-statistics.php',
				'MC4WP_Widget'                          => 'class-widget.php',
				'MC4WP_MailChimp'                       => 'class-mailchimp.php',
				'MC4WP_Styles_Builder'                  => 'admin/class-styles-builder.php',

				// license manager
				'DVK_License_Manager'                   => 'admin/license-manager/class-license-manager.php',
				'DVK_Plugin_License_Manager'            => 'admin/license-manager/class-plugin-license-manager.php',
				'DVK_Product'                           => 'admin/license-manager/class-product.php',
				'MC4WP_Product'                         => 'class-product.php',

				// tables
				'MC4WP_Forms_Table'                     => 'tables/class-forms-table.php',
				'MC4WP_Log_Table'                       => 'tables/class-log-table.php',

				// integrations
				'MC4WP_Integration'                     => 'integrations/class-integration.php',
				'MC4WP_bbPress_Integration'             => 'integrations/class-bbpress.php',
				'MC4WP_BuddyPress_Integration'          => 'integrations/class-buddypress.php',
				'MC4WP_CF7_Integration'                 => 'integrations/class-cf7.php',
				'MC4WP_Events_Manager_Integration'      => 'integrations/class-events-manager.php',
				'MC4WP_Comment_Form_Integration'        => 'integrations/class-comment-form.php',
				'MC4WP_EDD_Integration'                 => 'integrations/class-edd.php',
				'MC4WP_General_Integration'             => 'integrations/class-general.php',
				'MC4WP_MultiSite_Integration'           => 'integrations/class-multisite.php',
				'MC4WP_Registration_Form_Integration'   => 'integrations/class-registration-form.php',
				'MC4WP_WooCommerce_Integration'         => 'integrations/class-woocommerce.php'
			);
		}

		// Require the requested class
		if( isset( $classes[$class_name] ) ) {
			require_once MC4WP_PLUGIN_DIR . 'includes/' . $classes[$class_name];
		}
	}

	/**
	* @return MC4WP_Form_Manager
	*/
	public function get_form_manager() {
		return $this->form_manager;
	}

	/**
	* @return MC4WP_Checkbox_Manager
	*/
	public function get_checkbox_manager() {
		return $this->checkbox_manager;
	}

	/**
	* Returns an instance of the MailChimp for WordPress API class
	*
	* @return MC4WP_API
	*/
	public function get_api() {

		if( $this->api === null ) {
			$opts = mc4wp_get_options( 'general' );
			$this->api = new MC4WP_API( $opts['api_key'] );
		}

		return $this->api;
	}

	/**
	 * @return MC4WP_Logger
	 */
	public function get_log() {
		return $this->log;
	}

	/**
	* Register the MC4WP_Widget
	*/
	public function register_widget() {
		register_widget( 'MC4WP_Widget' );
	}

}
