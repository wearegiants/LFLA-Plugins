<?php

if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Gets the MailChimp for WP options from the database
 * Uses default values to prevent undefined index notices.
 *
 * @param string $key
 * @return array
 */
function mc4wp_get_options( $key = '' ) {

	static $options = null;

	if( null === $options) {
		$defaults = array();
		$defaults['general'] = array(
			'api_key' => '',
			'license_key' => ''
		);
		$defaults['checkbox'] = array(
			'label' => __( 'Sign me up for the newsletter!', 'mailchimp-for-wp' ),
			'precheck' => 1,
			'css' => 0,
			'show_at_comment_form' => 0,
			'show_at_registration_form' => 0,
			'show_at_multisite_form' => 0,
			'show_at_buddypress_form' => 0,
			'show_at_edd_checkout' => 0,
			'show_at_woocommerce_checkout' => 0,
			'show_at_bbpress_forms' => 0,
			'lists' => array(),
			'double_optin' => 1,
			'send_welcome' => 0,
			'update_existing' => 0,
			'woocommerce_position' => 'order'
		);
		$defaults['form'] = array(
			'css' => 0,
			'custom_theme_color' => '#1af',
			'ajax' => 1,
			'double_optin' => 1,
			'update_existing' => 0,
			'replace_interests' => 1,
			'send_welcome' => 0,
			'text_success' => __( 'Thank you, your sign-up request was successful! Please check your email inbox to confirm.', 'mailchimp-for-wp' ),
			'text_error' => __( 'Oops. Something went wrong. Please try again later.', 'mailchimp-for-wp' ),
			'text_invalid_email' => __( 'Please provide a valid email address.', 'mailchimp-for-wp' ),
			'text_already_subscribed' => __( 'Given email address is already subscribed, thank you!', 'mailchimp-for-wp' ),
			'text_invalid_captcha' => __( 'Please complete the CAPTCHA.', 'mailchimp-for-wp' ),
			'text_required_field_missing' => __( 'Please fill in the required fields.', 'mailchimp-for-wp' ),
			'redirect' => '',
			'hide_after_success' => 0,
			'send_email_copy' => 0
		);

		$keys_map = array(
			'mc4wp' => 'general',
			'mc4wp_checkbox' => 'checkbox',
			'mc4wp_form' => 'form'
		);

		$options = array();

		foreach ( $keys_map as $db_key => $opt_key ) {

			$option = (array) get_option( $db_key, array() );

			// add option to database to prevent query on every pageload
			if ( count( $option ) === 0 ) {
				add_option( $db_key, $defaults[$opt_key] );
			}

			$options[$opt_key] = array_merge( $defaults[$opt_key], $option );
		}

	}

	if( '' !== $key ) {
		return $options[$key];
	}

	return $options;
}

/**
 * Gets the MailChimp for WP API class and injects it with the given API key
 *
 * @return MC4WP_API
 */
function mc4wp_get_api() {
	return MC4WP::instance()->get_api();
}

/**
 * @param int $form_id
 * @param bool $inherit
 *
 * @return array
 */
function mc4wp_get_form_settings( $form_id, $inherit = false ) {
	$inherited_settings = mc4wp_get_options( 'form' );
	$form_settings = array();

	// set defaults
	$form_settings['lists'] = array();
	$form_settings['email_copy_receiver'] = get_bloginfo( 'admin_email' );

	// fill optional meta keys with empty strings
	$optional_meta_keys = array('double_optin', 'update_existing', 'replace_interests', 'send_welcome', 'ajax', 'hide_after_success', 'redirect', 'text_success', 'text_error', 'text_invalid_email', 'text_already_subscribed', 'send_email_copy', 'text_invalid_captcha', 'text_required_field_missing' );
	foreach( $optional_meta_keys as $meta_key ) {
		if( $inherit ) {
			$form_settings[$meta_key] = $inherited_settings[$meta_key];
		} else {
			$form_settings[$meta_key] = '';
		}
	}

	$meta = get_post_meta( $form_id, '_mc4wp_settings', true );
	if( is_array( $meta ) ) {
		foreach($meta as $key => $value) {
			// only add meta value if not empty
			if( $value != '' ) {
				$form_settings[$key] = $value;
			}
		}
	}

	return $form_settings;
}