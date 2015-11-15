<?php
if ( !class_exists( 'WooCommerce_Order_List_Writepanels' ) ) {

	class WooCommerce_Order_List_Writepanels {
		public function __construct() {
			add_action(	'admin_footer', array( &$this, 'export_actions' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts' ) );
		}

		/**
		 * Add print order list action to bulk action drop down menu
		 *
		 * Using Javascript until WordPress core fixes: http://core.trac.wordpress.org/ticket/16031
		 *
		 * @access public
		 * @return void
		 */
		public function export_actions() {
			global $post_type;

			if ( 'shop_order' == $post_type ) {
				?>
				<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('order-list').text('<?php _e( 'Print order list', 'wpo_wcol' )?>').appendTo("select[name='action']");
					jQuery('<option>').val('order-list').text('<?php _e( 'Print order list', 'wpo_wcol' )?>').appendTo("select[name='action2']");
				});
				</script>
				<?php
			}
		}

		/**
		 * JS for print action
		 */
		public function load_scripts() {
		 	global $post_type;
			if( $post_type == 'shop_order' ) {
				wp_register_script(
					'wcol-print',
					plugins_url( 'js/wcol-print.js' , dirname(__FILE__) ),
					array( 'jquery' )
				);
				wp_enqueue_script( 'wcol-print' );

				$settings = get_option( 'wpo_wcol_settings' );
				wp_localize_script(  
					'wcol-print',  
					'wcol_print',  
					array(  
						'preview'	=> isset($settings['preview']) ? 'true' : 'false',
					)  
				);  
			}

		}

	} // end class
} // end class_exists