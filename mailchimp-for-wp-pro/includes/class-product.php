<?php

class MC4WP_Product extends DVK_Product {

	public function __construct() {
		parent::__construct(
			'https://mc4wp.com/api/edd-licenses/',
			'MailChimp for WordPress Pro',
			plugin_basename( MC4WP_PLUGIN_FILE ),
			MC4WP_VERSION,
			'https://mc4wp.com/',
			'admin.php?page=mailchimp-for-wp',
			'mailchimp-for-wp',
			'Danny van Kooten',
			'mc4wp_'
		);
	}

}