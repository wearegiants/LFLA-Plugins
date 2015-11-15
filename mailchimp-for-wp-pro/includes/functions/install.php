<?php

/**
 * Runs on plugin activations
 * - Transfer any settings which may have been set in the Lite version of the plugin
 * - Creates a post type 'mc4wp-form' and enters the form mark-up from the Lite version
 */
function mc4wp_pro_install() {

	// check if PRO option exists and contains data entered by user
	$pro_options = get_option( 'mc4wp', false );
	if ( $pro_options !== false ) {
		return false;
	}

	$default_options = array();
	$default_options['general'] = array(
		'api_key' => '',
		'license_key' => ''
	);
	$default_options['checkbox'] = array(
		'label' => 'Sign me up for the newsletter!',
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
		'send_welcome' => 0
	);
	$default_options['form'] = array(
		'css' => 0,
		'custom_theme_color' => '#1af',
		'ajax' => 1,
		'double_optin' => 1,
		'update_existing' => 0,
		'replace_interests' => 1,
		'send_welcome' => 0,
		'text_success' => 'Thank you, your sign-up request was successful! Please check your e-mail inbox.',
		'text_error' => 'Oops. Something went wrong. Please try again later.',
		'text_invalid_email' => 'Please provide a valid email address.',
		'text_already_subscribed' => 'Given email address is already subscribed, thank you!',
		'redirect' => '',
		'hide_after_success' => 0,
		'send_email_copy' => 0
	);

	$lite_settings = array(
		'general' => (array) get_option( 'mc4wp_lite' ),
		'checkbox' => (array) get_option( 'mc4wp_lite_checkbox' ),
		'form' => (array) get_option( 'mc4wp_lite_form' )
	);

	foreach ( $default_options as $group_key => $options ) {
		foreach ( $options as $option_key => $option_value ) {
			if ( isset( $lite_settings[$group_key][$option_key] ) && ! empty( $lite_settings[$group_key][$option_key] ) ) {
				$default_options[$group_key][$option_key] = $lite_settings[$group_key][$option_key];
			}
		}
	}

	// Transfer form from Lite, but only if no Pro forms exist yet.
	$forms = get_posts(
		array(
			'post_type' => 'mc4wp-form',
			'post_status' => 'publish'
		)
	);

	if ( false == $forms ) {
		// no forms found, try to transfer from lite.
		$form_markup = ( isset( $lite_settings['form']['markup'] ) ) ? $lite_settings['form']['markup'] : "<p>\n\t<label for=\"mc4wp_email\">Email address: </label>\n\t<input type=\"email\" id=\"mc4wp_email\" name=\"EMAIL\" required placeholder=\"Your email address\" />\n</p>\n\n<p>\n\t<input type=\"submit\" value=\"Sign up\" />\n</p>";
		$form_ID = wp_insert_post( array(
			'post_type' => 'mc4wp-form',
			'post_title' => 'Sign-Up Form #1',
			'post_content' => $form_markup,
			'post_status' => 'publish'
		) );

		$lists = isset( $lite_settings['form']['lists'] ) ? $lite_settings['form']['lists'] : array();
		update_post_meta( $form_ID, '_mc4wp_settings', array( 'lists' => $lists ) );
		update_option( 'mc4wp_default_form_id', $form_ID );
	}

	// store options
	update_option( 'mc4wp', $default_options['general'] );
	update_option( 'mc4wp_checkbox', $default_options['checkbox'] );
	update_option( 'mc4wp_form', $default_options['form'] );
}