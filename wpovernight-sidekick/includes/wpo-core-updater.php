<?php

class WPO_Updater {
	
	private $wpo_store_url;
	private $plugin_item_name;
	private $plugin_file;
	private $plugin_license_slug;
	private $plugin_version;
	private $plugin_author;
	
	public function __construct( $_item_name, $_file, $_license_slug, $_version, $_author ) {
		$this->wpo_store_url		= apply_filters( 'wpovernight_store_url', 'https://wpovernight.com' );
		$this->plugin_item_name		= $_item_name;
		$this->plugin_file			= $_file;
		$this->plugin_license_slug	= $_license_slug;
		$this->plugin_version		= $_version;
		$this->plugin_author		= $_author;

		add_action('admin_init', array( &$this, 'activate_deactivate_license'), 0 );
		
		if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			// load the EDD updater class
			include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
		}

		$this->call_edd_updater();
		add_filter( 'wpocore_licenses_general', array( &$this, 'add_license' ));
	}

	/**
	 * Register License settings
	 */
	public function add_license( $settings ) {
		$menucart_license = array(
			$this->plugin_license_slug => array(
				'id' => $this->plugin_license_slug,
				'name' => $this->plugin_item_name, // currently not translated, this would require passing an additional paramter because the item_name has to correspond to the updater item name
				'desc' => __( '', 'wpocore' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => ''
			)
		);

		return array_merge( $settings, $menucart_license );
	}
	
	/**
	 * Send API data to the EDD Updater class
	 */
	public function call_edd_updater() {
		$wpocore_settings = get_option('wpocore_settings');

		$license_key = isset($wpocore_settings[$this->plugin_license_slug])?trim( $wpocore_settings[$this->plugin_license_slug] ):'';

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( $this->wpo_store_url, $this->plugin_file, array( 
				'version' 	=> $this->plugin_version, 				// current version number
				'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
				'item_name' => $this->plugin_item_name, 	// name of this plugin
				'author' 	=> $this->plugin_author  // author of this plugin
			)
		);
	}


	/************************************
	* Activate/deactivate license key
	*************************************/
	function activate_deactivate_license() {
		// listen for our activate button to be clicked
		if( isset( $_POST[ 'edd_license_activate_' . $this->plugin_license_slug ] ) ) 
			$edd_action = 'activate_license';

		// listen for our deactivate button to be clicked
		if( isset( $_POST[ 'edd_license_deactivate_' . $this->plugin_license_slug ] ) )
			$edd_action = 'deactivate_license';
	
		if( isset($edd_action) ) {
			// run a quick security check 
			if( ! check_admin_referer( 'eddlic_sample_nonce', 'eddlic_sample_nonce' ) ) 	
				return; // get out if we didn't click the Activate button
	 
			// retrieve the license from the database
			$wpocore_settings = get_option('wpocore_settings');
			$license = trim( $wpocore_settings[ $this->plugin_license_slug ] );
	 
			// data to send in our API request
			$api_params = array( 
				'edd_action'=> $edd_action, 
				'license' 	=> $license, 
				'item_name' => urlencode( $this->plugin_item_name ) // the name of our product in EDD
			);
	 
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, $this->wpo_store_url ), array( 'timeout' => 15, 'sslverify' => false ) );
	 
	 		// file_put_contents( dirname(dirname(__FILE__)) .'/request.txt', add_query_arg( $api_params, $this->wpo_store_url ) ); // API debugging
	 		// file_put_contents( dirname(dirname(__FILE__)) .'/response.txt', print_r($response,true)); // API debugging

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;
	 
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			switch ( $license_data->license ) {
				case 'valid':
					update_option( $this->plugin_license_slug, $license_data->license );
					break;
				case 'deactivated':
					delete_option($this->plugin_license_slug);
					break;
				default:
					// failed?
					break;
			}
	 	 
		}
	}

}