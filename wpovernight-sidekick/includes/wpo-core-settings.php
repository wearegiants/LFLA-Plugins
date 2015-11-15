<?php
/**
 * Register Settings
 *
 * @package     wpocore
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2013, Jeremiah Prummer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
//if ( !defined( 'ABSPATH' ) ) exit;

class WPOCore_Settings {
	public $options_page_hook;

	public function __construct() {
		add_filter( 'wpocore_settings_sanitize_text', array(&$this, 'wpocore_sanitize_text_field' ));
		add_action('admin_menu', array(&$this, 'wpmenucart_add_page'));
		add_action('admin_init', array(&$this, 'wpocore_register_settings'), 0);

		$this->wpocore_options = $this->wpocore_get_settings();
		
	}

	/**
	 * Get Settings
	 *
	 * Retrieves all plugin settings
	 *
	 * @since 1.0
	 * @return array wpocore settings
	 */
	public function wpocore_get_settings() {

		$settings = get_option( 'wpocore_settings' );
		if( empty( $settings ) ) {
			// Update old settings with new single option
			$general_settings = is_array( get_option( 'wpocore_settings_general' ) ) ? get_option( 'wpocore_settings_general' ) : array();

			// add default licenses to the settings
			$defaults = array (
					'wpo_core_license'	=> 'b945b2e6a0ef88d5cb4b57e38ae97add',
				);

			$settings = array_merge( $general_settings, $defaults );
			update_option( 'wpocore_settings', $settings );
		}
		return apply_filters( 'wpocore_get_settings', $settings );
	}

	/**
	 * Add all settings sections and fields
	 *
	 * @since 1.0
	 * @return void
	*/
	public function wpocore_register_settings() {

		if ( false == get_option( 'wpocore_settings' ) ) {
			add_option( 'wpocore_settings' );
		}



		foreach( $this->wpocore_get_registered_settings() as $tab => $settings ) {

			add_settings_section(
				'wpocore_settings_' . $tab,
				__return_null(),
				'__return_false',
				'wpocore_settings_' . $tab
			);

			foreach ( $settings as $option ) {
				add_settings_field(
					'wpocore_settings[' . $option['id'] . ']',
					$option['name'],
					array(&$this,'wpocore_' . $option['type'] . '_callback'),
					'wpocore_settings_' . $tab,
					'wpocore_settings_' . $tab,
					array(
						'id'      => $option['id'],
						'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'    => $option['name'],
						'section' => $tab,
						'size'    => isset( $option['size'] ) ? $option['size'] : null,
						'options' => isset( $option['options'] ) ? $option['options'] : '',
						'std'     => isset( $option['std'] ) ? $option['std'] : ''
					)
				);
			}

		}

		// Creates our settings in the options table
		register_setting( 'wpocore_settings', 'wpocore_settings', array(&$this,'wpocore_settings_sanitize') );

	}

	public function wpocore_get_registered_settings() {

		/**
		 * 'Whitelisted' wpocore settings, filters are provided for each settings
		 * section to allow extensions and other plugins to add their own settings
		 */
		$wpocore_licenses = array(
			/** General Settings */
			'general' => apply_filters( 'wpocore_licenses_general',
				array(
					'wpo_licenses' => array(
						'id' => 'wpo_licenses',
						'name' => '<strong>' . __( 'Plugin Licenses', 'wpocore' ) . '</strong>',
						'desc' => __( 'Enter Your License Keys Below', 'wpocore' ),
						'type' => 'header',
						'std'  => ''
					),
					'wpo_core_license' => array(
						'id' => 'wpo_core_license',
						'name' => __( 'WP Overnight Sidekick', 'wpocore' ),
						'desc' => __( '', 'wpocore' ),
						'type' => 'text',
						'size' => 'regular',
						'std'  => 'b945b2e6a0ef88d5cb4b57e38ae97add'
					)
				)
			)
		);
		
		return $wpocore_licenses;
	}

	/**
	 * Header Callback
	 *
	 * Renders the header.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @return void
	 */
	public function wpocore_header_callback( $args ) {
		echo '';
	}

	/**
	 * Text Callback
	 *
	 * Renders text fields.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * $this->wpocore_options Array of all the wpocore Options
	 * @return void
	 */
	public function wpocore_text_callback( $args ) {	
		if ( isset( $this->wpocore_options[ $args['id'] ] ) ) {
			$value = $this->wpocore_options[ $args['id'] ];
			$saved = $this->wpocore_options[ $args['id'] ];
		}	
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$status ='';	
		$status = get_option($args['id']);
		
		$html = '<input type="text" class="' . $size . '-text" id="wpocore_settings_' . $args['section'] . '[' . $args['id'] . ']" name="wpocore_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		if ( !empty( $this->wpocore_options[ $args['id'] ] ) ) {
			if( $status !== false && $status == 'valid') {
				$html .= '<span class="wpo-active-license">'. __('active') .'</span>';
				wp_nonce_field( 'eddlic_sample_nonce', 'eddlic_sample_nonce' );
				$html .='<input type="submit" class="button-secondary wpo-activation-button" name="edd_license_deactivate_'. $args['id'] .'" value="'. __('Deactivate License').'"/>';
			} else {
				wp_nonce_field( 'eddlic_sample_nonce', 'eddlic_sample_nonce' );
				$html .= '<input type="submit" class="button-secondary wpo-activation-button" name="edd_license_activate_'. $args['id'] .'" value="'. __('Activate License') .'"/>';
			}
		}
		$html .= '<label for="wpocore_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	/**
	 * Settings Sanitization
	 *
	 * Adds a settings error (for the updated message)
	 * At some point this will validate input
	 *
	 * @since 1.0.8.2
	 * @param array $input The value inputted in the field
	 * @return string $input Sanitizied value
	 */
	public function wpocore_settings_sanitize( $input = array() ) {
		parse_str( $_POST['_wp_http_referer'], $referrer );

		$output    = array();
		$settings  = $this->wpocore_get_registered_settings();
		$tab       = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
		$post_data = isset( $_POST[ 'wpocore_settings_' . $tab ] ) ? $_POST[ 'wpocore_settings_' . $tab ] : array();

		$input = apply_filters( 'wpocore_settings_' . $tab . '_sanitize', $post_data );

		// Loop through each setting being saved and pass it through a sanitization filter
		foreach( $input as $key => $value ) {

			// Get the setting type (checkbox, select, etc)
			$type = isset( $settings[ $key ][ 'type' ] ) ? $settings[ $key ][ 'type' ] : false;

			if( $type ) {
				// Field type specific filter
				$output[ $key ] = apply_filters( 'wpocore_settings_sanitize_' . $type, $value, $key );
			}

			// General filter
			$output[ $key ] = apply_filters( 'wpocore_settings_sanitize', $value, $key );
		}


		// Loop through the whitelist and unset any that are empty for the tab being saved
		if( ! empty( $settings[ $tab ] ) ) {
			foreach( $settings[ $tab ] as $key => $value ) {

				// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
				if( is_numeric( $key ) ) {
					$key = $value['id'];
				}

				if( empty( $_POST[ 'wpocore_settings_' . $tab ][ $key ] ) ) {
					unset( $this->wpocore_options[ $key ] );
				}

			}
		}

		// Merge our new settings with the existing
		$output = array_merge( $this->wpocore_options, $output );

		add_settings_error( 'wpocore-notices', '', __( 'Settings Updated', 'wpocore' ), 'updated' );

		return $output;

	}

	/**
	 * Sanitize text fields
	 *
	 * @since 1.8
	 * @param array $input The field value
	 * @return string $input Sanitizied value
	 */
	public function wpocore_sanitize_text_field( $input ) {
		return trim( $input );
	}
	//add_filter( 'wpocore_settings_sanitize_text', 'wpocore_sanitize_text_field' );

	/**
	 * Add menu page
	 */
	public function wpmenucart_add_page() {
		$this->options_page_hook = add_submenu_page(
			'wpo-core-menu',
			'Manage Licenses',
			'Manage Licenses',
			'manage_options',
			'wpo-license-page',
			array(&$this,'wpo_license_page')
		);
	}

	public function wpo_license_page() {
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br /></div>
			<h2><?php _e('License Key Admin', 'wpocore') ?></h2>
		<?php
		//use for debugging
		//$option = get_option( 'wpocore_settings' );
		//print_r($option);
		?>
			<form method="post" action="options.php">
			<?php
			settings_fields('wpocore_settings');
			do_settings_sections('wpocore_settings_general');

			submit_button('Save License Key Changes');
			?>
			</form>
		</div>
		<?php
	}
}
