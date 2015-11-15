<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CAC_ADDON_COLUMNS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * @since 3.4.3
 */
class CACIE_Addon_Columns {

	/**
	 * @since 3.4.3
	 */
	public function __construct() {
		add_filter( 'cac/columns/custom', array( $this, 'set_columns' ), 10, 2 );
	}

	/**
	 * Columns
	 *
	 * @since 3.4.3
	 */
	public function set_columns( $columns, $storage_model ) {
		// @todo add columns
		return $columns;
	}
}

new CACIE_Addon_Columns();
