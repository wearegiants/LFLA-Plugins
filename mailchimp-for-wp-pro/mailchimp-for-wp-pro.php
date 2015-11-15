<?php
/*
Plugin Name: MailChimp for WordPress Pro
Plugin URI: https://mc4wp.com/
Description: Pro version of MailChimp for WordPress. Adds various sign-up methods to your website.
Version: 2.6
Author: Danny van Kooten
Author URI: https://dannyvankooten.com
License: GPL v3
Text Domain: mailchimp-for-wp

MailChimp for WordPress alias MC4WP
Copyright (C) 2012-2015, Danny van Kooten, hi@dannyvankooten.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

function mc4wp_pro_load_plugin() {

	define( 'MC4WP_VERSION', '2.6' );
	define( 'MC4WP_PLUGIN_FILE', __FILE__ );
	define( 'MC4WP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'MC4WP_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

	 // Global Functions
	require_once MC4WP_PLUGIN_DIR . 'includes/functions/general.php';
	require_once MC4WP_PLUGIN_DIR . 'includes/functions/template.php';

	// Initialize Plugin Class
	require_once MC4WP_PLUGIN_DIR . 'includes/class-plugin.php';
	MC4WP::init();
	$GLOBALS['mc4wp'] = MC4WP::instance();

	// Only load the Admin class on admin requests, excluding AJAX.
	if( is_admin() && ( false === defined( 'DOING_AJAX' ) || false === DOING_AJAX ) ) {
		// Initialize Admin Class
		require_once MC4WP_PLUGIN_DIR . 'includes/admin/class-admin.php';
		new MC4WP_Admin();
	}

}

add_action( 'plugins_loaded', 'mc4wp_pro_load_plugin', 10 );

// Only add these hooks on Admin requests
if( is_admin() && ( false === defined( 'DOING_AJAX' ) || false === DOING_AJAX ) ) {

	// activation & deactivation hooks
	require_once dirname( __FILE__ ) . '/includes/functions/install.php';
	register_activation_hook( __FILE__, 'mc4wp_pro_install' );
}
