<?php

if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Styles_Builder {

	/**
	 * Array with all available CSS fields, their default value and their type
	 *
	 * @var array
	 */
	public static $fields = array(
		'form_width' => array(
			'default' => '',
			'type' => 'px'
		),
		'form_background_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'form_font_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'form_border_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'form_border_width' => array(
			'default' => '',
			'type' => 'int'
		),
		'form_horizontal_padding' => array(
			'default' => '',
			'type' => 'int'
		),
		'form_vertical_padding' => array(
			'default' => '',
			'type' => 'int'
		),
		'form_text_align' => array(
			'default' => '',
			'type' => ''
		),
		'form_font_size' => array(
			'default' => '',
			'type' => 'int'
		),
		'labels_font_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'labels_font_style' => array(
			'default' => '',
			'type' => ''
		),
		'labels_font_size' => array(
			'default' => '',
			'type' => 'int'
		),
		'labels_display' => array(
			'default' => '',
			'type' => ''
		),
		'labels_vertical_margin' => array(
			'default' => '',
			'type' => 'int'
		),
		'labels_horizontal_margin' => array(
			'default' => '',
			'type' => 'int'
		),
		'labels_width' => array(
			'default' => '',
			'type' => 'px'
		),
		'fields_border_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'fields_border_width' => array(
			'default' => '',
			'type' => 'int'
		),
		'fields_width' => array(
			'default' => '',
			'type' => 'px'
		),
		'fields_height' => array(
			'default' => '',
			'type' => 'int'
		),
		'fields_display' => array(
			'default' => '',
			'type' => ''
		),
		'buttons_background_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_font_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_font_size' => array(
			'default' => '',
			'type' => 'int'
		),
		'buttons_border_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_hover_background_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_hover_font_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_hover_border_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_border_width' => array(
			'default' => '',
			'type' => 'int'
		),
		'buttons_width' => array(
			'default' => '',
			'type' => 'px'
		),
		'buttons_height' => array(
			'default' => '',
			'type' => 'int'
		),
		'messages_font_color_error' => array(
			'default' => '',
			'type' => 'color'
		),
		'messages_font_color_success' => array(
			'default' => '',
			'type' => 'color'
		),
		'selector_prefix' => array(
			'default' => '',
			'type' => 'selector'
		),
		'manual' => array(
			'default' => '',
			'type' => 'text'
		)

	);

	/**
	 * Get the default theme settings
	 *
	 * @return array
	 */
	public static function get_default_form_styles() {
		$default_form_styles = array();

		foreach( self::$fields as $key => $field ) {
			$default_form_styles[ $key ] = $field['default'];
		}

		return $default_form_styles;
	}

	/**
	 * Get all form themes, merged with defaults
	 *
	 * @return array
	 */
	public static function get_all_styles() {

		$all_styles = get_option( 'mc4wp_form_styles', array() );

		if( ! is_array( $all_styles ) ) {
			delete_option( 'mc4wp_form_styles' );
			return array();
		}

		// merge all theme settings with the defaults array
		foreach( $all_styles as $form_id => $form_styles ) {
			$all_styles[ $form_id ] = array_merge( self::get_default_form_styles(), $form_styles );
		}

		return $all_styles;
	}

	/**
	 * Get saved CSS values from option
	 *
	 * @return array
	 */
	public static function get_form_styles( $form_id = 0 ) {
		$all_styles = self::get_all_styles();
		$form_styles = ( isset( $all_styles[ 'form-' . $form_id ] ) ) ? $all_styles[ 'form-' . $form_id ] : self::get_default_form_styles();
		return $form_styles;
	}

	/**
	 * Validate the given CSS values according to their type
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public static function validate( $new_styles = array() ) {

		// get all styles
		$old_styles = self::get_all_styles();

		// was delete button clicked?
		if( isset( $_POST['_mc4wp_delete_form_styles'] ) ) {
			$form_id_to_delete = absint( $_POST['_mc4wp_delete_form_styles'] );

			if( isset( $old_styles['form-' . $form_id_to_delete ] ) ) {
				unset( $old_styles['form-' . $form_id_to_delete ] );
			}

			// build the new css file
			if( ! defined( 'MC4WP_DOING_UPGRADE' ) ) {
				self::build_css_file( $old_styles );
			}

			return $old_styles;
		}

		// start sanitizing new form styles
		foreach( $new_styles as $form_id => $new_form_styles ) {

			// start with empty array of styles
			$sanitized_form_styles = array();

			foreach( $new_form_styles as $key => $value ) {

				// skip field if it's not a valid field
				if( ! isset( self::$fields[ $key ] ) ) {
					continue;
				}

				// add field value to css array
				$sanitized_form_styles[ $key ] = $value;

				// skip if field is empty or has its default value
				if( '' === $value || $value === self::$fields[$key]['default'] ) {
					continue;
				}

				// sanitize field since it's not default
				switch( self::$fields[ $key ]['type'] ) {
					case 'color':
						// make sure colors start with #
						$value = '#' . ltrim( trim( $value ), '#' );
						break;

					case 'px':
						// make sure px and % end with 'px' or '%'
						$value = str_replace( ' ', '', strtolower( $value ) );

						if( substr( $value, -1 ) !== '%' && substr( $value, -2 ) !== 'px') {
							$value .= 'px';
						}

						break;

					case 'int':
						$value = absint( $value );
						break;

					case 'selector':
						$value = trim( $value ) . ' ';
						break;

					case 'text':
						$value = trim( $value );
						break;
				}

				// save css value
				$sanitized_form_styles[ $key ] = $value;
			}

			// save sanitized styles in array with all styles
			$old_styles[ $form_id ] = $sanitized_form_styles;
		}

		// build the new css file
		if( ! defined( 'MC4WP_DOING_UPGRADE' ) ) {
			self::build_css_file( $old_styles );
		}

		return $old_styles;
	}

	/**
	 * Build file with given CSS values
	 *
	 * @param array $css Array containing the values of the various CSS fields
	 *
	 * @return bool
	 */
	public static function build_css_file( array $styles ) {

		$css_string = self::get_css_string( $styles );

		// upload CSS file with CSS string as content
		$file = wp_upload_bits( 'mailchimp-for-wp.css', null, $css_string );

		// Check if file was successfully created
		if( false === $file || ! is_array( $file ) || $file['error'] !== false ) {
			$message = sprintf( __( 'Couldn\'t create the stylesheet. Manually add the generated CSS to your theme stylesheet by using the %sTheme Editor%s or use FTP and edit <em>%s</em>.', 'mailchimp-for-wp' ), '<a href="'. admin_url( 'theme-editor.php' ) .'">', '</a>', get_stylesheet_directory() .'/style.css' );
			$button = sprintf( __( '%sShow generated CSS%s', 'mailchimp-for-wp' ), '<a class="mc4wp-show-css button" href="javascript:void(0);">', '</a>' );
			add_settings_error( 'mc4wp', 'mc4wp-css', $message . ' ' . $button .'</strong><div id="mc4wp_generated_css" style="display:none;"><pre>'. esc_html( $css_string ) .'</pre></div><strong>' );
			return false;
		}

		// store protocol relative url to CSS file in option
		$custom_stylesheet = str_ireplace( array( 'http://', 'https://' ), '//', $file['url'] );
		update_option( 'mc4wp_custom_css_file', $custom_stylesheet );

		// show notice
		$opts = mc4wp_get_options( 'form' );
		$enqueue_text = ( $opts['css'] === 'custom' ) ? '' : sprintf( __( 'To apply these styles on your website, select "load custom form styles" in the %sform settings%s', 'mailchimp-for-wp' ), '<a href="' . admin_url( 'admin.php?page=mailchimp-for-wp-form-settings' ) . '">', '</a>.' );
		add_settings_error( 'mc4wp', 'mc4wp-css', sprintf( __( 'The %sCSS Stylesheet%s has been created.', 'mailchimp-for-wp' ), '<a href="'. $file['url'] .'">', '</a>' ) . ' ' . $enqueue_text, 'updated' );
		return true;
	}

	/**
	 * Turns array of CSS values into CSS stylesheet string
	 *
	 * @param array $css
	 *
	 * @return string
	 */
	private static function get_css_string( array $styles ) {

		// Build CSS String
		$css_string = '';
		ob_start();

		echo '/* CSS Generated by MailChimp for WordPress v'. MC4WP_VERSION .' */' . "\n\n";

		// Loop through all form styles
		foreach( $styles as $form_id => $form_styles ) {

			$form_selector = '.mc4wp-' . $form_id;

			// Build CSS styles for this form
			extract( $form_styles );
			require MC4WP_PLUGIN_DIR . 'includes/views/parts/css-styles.php';


		}

		// get output buffer
		$css_string = ob_get_contents();
		ob_end_clean();

		return $css_string;
	}

}