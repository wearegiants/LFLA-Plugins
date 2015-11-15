<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CAC_FC_URL', plugins_url( '', __FILE__ ) );
define( 'CAC_FC_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Addon class
 *
 * @since 1.0
 */
class CAC_Addon_Filtering {

	private $cpac;

	function __construct() {

		// init addon
		add_action( 'cac/loaded', array( $this, 'init_addon_filtering' ) );

		// styling & scripts
		add_action( "admin_print_styles-settings_page_codepress-admin-columns", array( $this, 'scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts_listings' ) );

		// Add column properties
		add_filter( 'cac/column/properties', array( $this, 'set_column_default_properties' ) );

		// Add column options
		add_filter( 'cac/column/default_options', array( $this, 'set_column_default_options' ) );

		// Add setting field
		add_action( 'cac/column/settings_after', array( $this, 'add_settings_field' ), 9 );

		// add setting filtering indicator
		add_action( 'cac/column/settings_meta', array( $this, 'add_label_filter_indicator' ), 9 );

		// clears all column cache for filtering when updating posts, terms or profile.
		add_action( 'cac/inline-edit/after_ajax_column_save', array( $this, 'clear_cache_by_column' ) );
		add_action( 'cac/storage_model/columns_stored', array( $this, 'clear_cache' ) );
		add_action( 'delete_post', array( $this, 'clear_cache' ) );
		add_action( 'created_term', array( $this, 'clear_cache' ) );
		add_action( 'edited_term', array( $this, 'clear_cache' ) );
		add_action( 'delete_term', array( $this, 'clear_cache' ) );
		add_action( 'profile_update', array( $this, 'clear_cache' ) );
		add_action( 'edit_post', array( $this, 'clear_cache' ) ); // do not use save_post
	}

	/**
	 * @since 1.0
	 */
	public function scripts() {
		wp_enqueue_style( 'cac-addon-filtering-css', CAC_FC_URL . '/assets/css/filtering.css', array(), CAC_PRO_VERSION, 'all' );
	}

	/**
	 * @since 3.4.4
	 */
	public function scripts_listings() {
		if ( $this->cpac->is_columns_screen() ) {
			wp_enqueue_style( 'cac-addon-filtering-listings-css', CAC_FC_URL . '/assets/css/listings_screen.css', array(), CAC_PRO_VERSION, 'all' );
		}
	}

	/**
	 * @since 1.0
	 */
	public function set_column_default_properties( $properties ) {

		$properties['is_filterable'] = false;
		return $properties;
	}

	/**
	 * @since 1.0
	 */
	public function set_column_default_options( $options ) {

		$options['filter'] = 'off';
		return $options;
	}

	/**
	 * @since 1.0
	 */
	public function add_settings_field( $column ) {

		if ( ! $column->properties->is_filterable ) {
			return false;
		}

		$sort = isset( $column->options->filter ) ? $column->options->filter : '';

		?>

		<tr class="column_filtering">
			<?php $column->label_view( __( 'Enable filtering?', 'cpac' ), __( 'This will make the column support filtering.', 'cpac' ), 'filter' ); ?>
			<td class="input" data-toggle-id="<?php $column->attr_id( 'filter' ); ?>">
				<label for="<?php $column->attr_id( 'filter' ); ?>-on">
					<input type="radio" value="on" name="<?php $column->attr_name( 'filter' ); ?>" id="<?php $column->attr_id( 'filter' ); ?>-on"<?php checked( $column->options->filter, 'on' ); ?>>
					<?php _e( 'Yes'); ?>
				</label>
				<label for="<?php $column->attr_id( 'filter' ); ?>-off">
					<input type="radio" value="off" name="<?php $column->attr_name( 'filter' ); ?>" id="<?php $column->attr_id( 'filter' ); ?>-off"<?php checked( $column->options->filter, '' ); ?><?php checked( $column->options->filter, 'off' ); ?>>
					<?php _e( 'No'); ?>
				</label>
			</td>
		</tr>

	<?php
	}

	/**
	 * @since 1.0
	 */
	public function add_label_filter_indicator( $column ) {
		if ( $column->properties->is_filterable ) : ?>
		<span title="<?php esc_attr_e( 'filter', 'cpac' ); ?>" class="filtering <?php echo $column->options->filter; ?>"  data-indicator-id="<?php $column->attr_id( 'filter' ); ?>"></span>
		<?php
		endif;
	}

	/**
	 * Init Addons
	 *
	 * @since 1.0
	 */
	public function init_addon_filtering( $cpac ) {

		$this->cpac = $cpac;

		// Abstract
		include_once 'classes/model.php';

		// Childs
		include_once 'classes/media.php';
		include_once 'classes/post.php';
		include_once 'classes/user.php';
		include_once 'classes/comment.php';

		// Posts
		foreach ( $cpac->get_post_types() as $post_type ) {
			if ( $storage_model = $cpac->get_storage_model( $post_type ) ) {
				new CAC_Filtering_Model_Post( $storage_model );
			}
		}

		// User
		if ( $storage_model = $cpac->get_storage_model( 'wp-users' ) ) {
			new CAC_Filtering_Model_User( $storage_model );
		}

		// Media
		if ( $storage_model = $cpac->get_storage_model( 'wp-media' ) ) {
			new CAC_Filtering_Model_Media( $storage_model );
		}

		// Comment
		if ( $storage_model = $cpac->get_storage_model( 'wp-comments' ) ) {
			new CAC_Filtering_Model_Comment( $storage_model );
		}
	}

	/**
	 * Clear cache when inline-edit is being used.
	 *
	 * @param object CPAC_Column
	 */
	public function clear_cache_by_column( $column ) {
		if ( isset( $column->options->filter ) && 'on' == $column->options->filter ) {
			$column->storage_model->delete_cache( 'filtering', $column->properties->name );
		}
	}

	/**
	 * Clears all column cache
	 *
	 * @since 1.0
	 */
	public function clear_cache() {

		// prevents the multiple flusing of cache by inline-edit, it uses it's own callback.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( ! $this->is_loaded() ) {
			return;
		}

		// by default storage models are only loaded on listings screen, we need to make sure they are set
		if ( empty( $this->cpac->storage_models ) ) {
			$this->cpac->set_storage_models();
		}

		foreach ( $this->cpac->storage_models as $storage_model ) {
			if ( $columns = $storage_model->get_stored_columns() ) {
				foreach ( $columns as $column ) {
					if ( isset( $column['column-name'] ) && isset( $column['filter'] ) && 'on' == $column['filter'] ) {
						$storage_model->delete_cache( 'filtering', $column['column-name'] );
					}
				}
			}
		}
	}

	/**
	 * Check whether the plugin is loaded
	 * Loading is done when the cpac property is set, which usually occurs on the cac/loaded action
	 *
	 * @since 3.0.8.4
	 *
	 * @return bool Whether Admin Columns is loaded
	 */
	public function is_loaded() {

		return ( ! empty( $this->cpac ) );
	}
}

new CAC_Addon_Filtering;