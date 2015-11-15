<?php

if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Form_Manager
{
	/**
	 * @var array
	 */
	private $options = array();

	/**
	* @var int
	*/
	private $form_instance_number = 1;

	/**
	* @var boolean
	*/
	private $loaded_ajax_scripts = false;

	/**
	 * @var bool
	 */
	private $inline_js_printed = false;

	/**
	 * @var MC4WP_Form_Request
	 */
	private $form_request;

	/**
	* Constructor
	*/
	public function __construct() {
		$this->options = $opts = mc4wp_get_options( 'form' );

		add_action( 'init', array( $this, 'initialize' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheet' ) );

		// enable shortcodes in text widgets
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode' );

		add_shortcode( 'mc4wp_form', array( $this, 'output_form' ) );

		// deprecated. use mc4wp_form.
		add_shortcode( 'mc4wp-form', array( $this, 'output_form' ) );
	}

	/**
	* Initialize form stuff
	*
	* - Registers post type
	* - Registers scripts
	*/
	public function initialize() {

		// register post type
		register_post_type( 'mc4wp-form', array(
			'labels' => array(
				'name' => 'MailChimp Sign-up Forms',
				'singular_name' => 'Sign-up Form',
				'add_new_item' => 'Add New Form',
				'edit_item' => 'Edit Form',
				'new_item' => 'New Form',
				'all_items' => 'All Forms',
				'view_item' => null
				),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false
			)
		);

		// has a form been submitted, either by ajax or manually?
		if( isset( $_POST['_mc4wp_form_submit'] ) ) {
			$this->form_request = new MC4WP_Form_Request;
		}

		// frontend only
		if( ! is_admin() ) {

			// should we load the minified script version?
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.js' : '.min.js';

			// register placeholder script, which will later be enqueued for IE only
			wp_register_script( 'mc4wp-placeholders', MC4WP_PLUGIN_URL . 'assets/js/third-party/placeholders.min.js', array(), MC4WP_VERSION, true );

			// register ajax script
			wp_register_script( 'mc4wp-ajax-forms', MC4WP_PLUGIN_URL . 'assets/js/ajax-forms' . $suffix, array( 'jquery-form' ), MC4WP_VERSION, true );

			// register non-AJAX script (that handles form submissions)
			wp_register_script( 'mc4wp-form-request', MC4WP_PLUGIN_URL . 'assets/js/form-request' . $suffix, array(), MC4WP_VERSION, true );

			// Load AJAX scripts on all pages if lazy load is disabled
			$lazy_load_ajax = apply_filters( 'mc4wp_lazy_load_ajax_scripts', true );
			if( true !== $lazy_load_ajax ) {
				$this->load_ajax_scripts();
			}

			if( isset( $_GET['_mc4wp_css_preview'] ) ) {
				$this->show_form_preview();
				die();
			}
		}

	}

	/**
	* Loads a basic HTML template to preview forms
	* @return boolean
	*/
	public function show_form_preview() {
		require MC4WP_PLUGIN_DIR . 'includes/views/pages/form-preview.php';
		die();
	}

	/**
	* Tells the plugin which shipped stylesheets to load.
	*
	* @return bool True if a stylesheet was enqueued
	*/
	public function load_stylesheet( ) {

		if( $this->options['css'] == false || isset( $_GET['_mc4wp_css_preview'] ) ) {
			return false;
		}

		$opts = $this->options;
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if( $opts['css'] === 'custom' ) {

			// load the custom stylesheet
			$custom_stylesheet = get_option( 'mc4wp_custom_css_file', false );

			// prevent query on every pageload if option does not exist
			if( false === $custom_stylesheet ) {
				update_option( 'mc4wp_custom_css_file', '' );
			}

			// load stylesheet
			if( is_string( $custom_stylesheet ) && $custom_stylesheet !== '' ) {
				wp_enqueue_style( 'mc4wp-custom-form-css', $custom_stylesheet, array(), MC4WP_VERSION, 'all' );
			}

		} elseif( $opts['css'] !== 1 && $opts['css'] !== 'default' ) {

			if( $opts['css'] === 'custom-color' ) {
				// load the custom color theme
				$custom_color = urlencode( $opts['custom_theme_color'] );
				wp_enqueue_style( 'mailchimp-for-wp-form-theme-' . $opts['css'], MC4WP_PLUGIN_URL . 'assets/css/form-theme-custom.php?custom-color=' . $custom_color, array(), MC4WP_VERSION, 'all' );

			} else {
				// load one of the default form themes
				$form_theme = $opts['css'];
				if( in_array( $form_theme, array( 'blue', 'green', 'dark', 'light', 'red' ) ) ) {
					wp_enqueue_style( 'mailchimp-for-wp-form-theme-' . $opts['css'], MC4WP_PLUGIN_URL . 'assets/css/form-theme-' . $opts['css'] . $suffix . '.css', array(), MC4WP_VERSION, 'all' );
				}
			}

		} else {
			// load just the basic form reset
			wp_enqueue_style( 'mailchimp-for-wp-form', MC4WP_PLUGIN_URL . 'assets/css/form'. $suffix .'.css', array(), MC4WP_VERSION, 'all' );
		}

		return true;
	}

	/**
	* Get CSS classes to add to a form element
	*
	* @param int $form_id
	* @return string
	*/
	public function get_form_css_classes( $form_id ) {

		$settings = mc4wp_get_form_settings( $form_id, true );

		/**
		 * @filter mc4wp_form_css_classes
		 * @expects array
		 *
		 * Can be used to add additional CSS classes to the form container
		 */
		$css_classes = apply_filters( 'mc4wp_form_css_classes', array( 'form' ) );

		// the following classes MUST be used
		$css_classes[] = 'mc4wp-form';
		$css_classes[] = 'mc4wp-form-' . $form_id;

		if( $settings['ajax'] ) {
			$css_classes[] = 'mc4wp-ajax';
		}

		// Add form classes if a Form Request was captured
		if( is_object( $this->form_request ) && $this->form_request->get_form_instance_number() === $this->form_instance_number ) {

			$css_classes[] = 'mc4wp-form-submitted';

			if( $this->form_request->is_successful() ) {
				$css_classes[] = 'mc4wp-form-success';
			} else {
				$css_classes[] = 'mc4wp-form-error';
			}

		}

		return implode( ' ', $css_classes );
	}

	/**
	* Outputs a form with the given ID
	*
	* @param array $atts
	* @param string $content
	* @return string
	*/
	public function output_form( $atts = array(), $content = '' )
	{
		// include the necessary functions file
		if( ! function_exists( 'mc4wp_replace_variables' ) ) {
			include_once MC4WP_PLUGIN_DIR . 'includes/functions/template.php';
		}

		// try to get default form ID if it wasn't specified in the shortcode atts
		if( false === isset( $atts['id'] ) ) {

			// try to get default form id
			$atts['id'] = get_option( 'mc4wp_default_form_id', false );
			if( false === $atts['id'] ) {

				if( current_user_can( 'manage_options' ) ) {
					return '<p>'. sprintf( __( '<strong>Error:</strong> Please specify a form ID. Example: %s.', 'mailchimp-for-wp' ), '<code>[mc4wp_form id="321"]</code>' ) .'</p>';
				}

				return '';
			}
		}

		// Get the form with the specified ID
		$form = get_post( $atts['id'] );

		// did we find a valid form with this ID?
		if( ! is_object( $form ) || $form->post_type !== 'mc4wp-form' ) {

			if( current_user_can( 'manage_options' ) ) {
				return '<p>'. __( '<strong>Error:</strong> Sign-up form not found. Please check if you used the correct form ID.', 'mailchimp-for-wp' ) .'</p>';
			}

			return '';
		}

		// was this form submitted?
		$was_submitted = ( is_object( $this->form_request ) && $this->form_request->get_form_instance_number() === $this->form_instance_number );
		$opts = mc4wp_get_form_settings( $form->ID, true );

		// add some useful css classes
		$css_classes = $this->get_form_css_classes( $form->ID );

		// Start building content string
		$opening_html = '<!-- MailChimp for WP Pro v' . MC4WP_VERSION . ' -->';
		$opening_html .= '<div id="mc4wp-form-' . $this->form_instance_number . '" class="' . $css_classes . '">';

		// Generate before & after fields HTML
		$before_form = apply_filters( 'mc4wp_form_before_form', '' );
		$after_form = apply_filters( 'mc4wp_form_after_form', '' );

		$form_opening_html = '';
		$form_closing_html = '';

		$visible_fields = '';
		$hidden_fields = '';

		$before_fields = apply_filters( 'mc4wp_form_before_fields', '' );
		$after_fields = apply_filters( 'mc4wp_form_after_fields', '' );

		// only generate form & fields HTML if necessary
		if( ! $was_submitted || ! $opts['hide_after_success'] || ! $this->form_request->is_successful() ) {

			/**
			 * @filter mc4wp_form_action
			 * @expects string
			 *
			 * Sets the `action` attribute of the form element. Defaults to the current URL.
			 */
			$form_action = apply_filters( 'mc4wp_form_action', mc4wp_get_current_url() );
			$form_opening_html = '<form method="post" action="' . $form_action . '">';

			// replace special values
			$visible_fields = __( $form->post_content, 'mailchimp-for-wp' );
			$visible_fields = str_ireplace( array( '%N%', '{n}' ), $this->form_instance_number, $visible_fields );
			$visible_fields = mc4wp_replace_variables( $visible_fields, array_values( $opts['lists'] ) );

			// insert captcha
			if( function_exists( 'cptch_display_captcha_custom' ) ) {
				$captcha_fields = '<input type="hidden" name="_mc4wp_has_captcha" value="1" /><input type="hidden" name="cntctfrm_contact_action" value="true" />' . cptch_display_captcha_custom();
				$visible_fields = str_ireplace( array( '{captcha}', '[captcha]' ), $captcha_fields, $visible_fields );
			}

			/**
			 * @filter mc4wp_form_content
			 * @param int $form_id The ID of the form that is being shown
			 * @expects string
			 *
			 * Can be used to customize the content of the form mark-up, eg adding additional fields.
			 */
			$visible_fields = apply_filters( 'mc4wp_form_content', $visible_fields, $form->ID );

			// hidden fields
			$hidden_fields = '<textarea name="_mc4wp_required_but_not_really" style="display: none !important;"></textarea>';
			$hidden_fields .= '<input type="hidden" name="_mc4wp_form_id" value="'. $form->ID .'" />';
			$hidden_fields .= '<input type="hidden" name="_mc4wp_form_instance" value="'. $this->form_instance_number .'" />';
			$hidden_fields .= '<input type="hidden" name="_mc4wp_form_submit" value="1" />';
			$hidden_fields .= '<input type="hidden" name="_mc4wp_form_nonce" value="'. wp_create_nonce( '_mc4wp_form_nonce' ) .'" />';

			$form_closing_html = '</form>';
		}

		$response_html = '';

		// does this form have AJAX enabled?
		if( $opts['ajax'] ) {

			// load ajax scripts (in footer)
			$this->load_ajax_scripts();

			// set placeholder div for ajax response
			$response_html = '<div class="mc4wp-response"></div>';

			// Add AJAX loader span to output
			$hidden_fields .= '<span class="mc4wp-ajax-loader" style="display: none !important;"></span>';
		}

		// was form submited?
		if( $was_submitted) {

			// enqueue scripts (in footer) if form was submited
			wp_enqueue_script( 'mc4wp-form-request' );
			wp_localize_script( 'mc4wp-form-request', 'mc4wpFormRequestData', array(
					'success' => ( $this->form_request->is_successful() ) ? 1 : 0,
					'formId' => $this->form_request->get_form_instance_number(),
					'data' => $this->form_request->get_data()
				)
			);

			// set response html
			$response_html = $this->form_request->get_response_html();
		}

		// add form response to content, if no {response} tag present
		if( '' !== $response_html && ( stristr( $visible_fields, '{response}' ) === false || $opts['hide_after_success'] ) ) {

			/**
			 * @filter mc4wp_form_message_position
			 * @expects string before|after
			 *
			 * Can be used to change the position of the form success & error messages.
			 * Valid options are 'before' or 'after'
			 */
			$message_position = apply_filters( 'mc4wp_form_message_position', 'after' );

			switch( $message_position ) {
				case 'before':
					$before_form = $before_form . $response_html;
					break;

				case 'after':
					$after_form = $response_html . $after_form;
					break;
			}

			// reset response html, we only need it once
			$response_html = '';
		}

		// Always replace {response} tag, either with empty string or actual response
		$visible_fields = str_ireplace( '{response}', $response_html, $visible_fields );

		$closing_html = '</div><!-- / MailChimp for WP Pro -->';

		// increase form instance number in case there is more than one form on a page
		$this->form_instance_number++;

		// make sure scripts are enqueued later
		global $is_IE;
		if( isset( $is_IE ) && $is_IE ) {
			wp_enqueue_script( 'mc4wp-placeholders' );
		}

		add_action( 'wp_footer', array( $this, 'print_js') );

		// concatenate and return the HTML parts
		return $opening_html . $before_form . $form_opening_html . $before_fields . $visible_fields . $hidden_fields . $after_fields . $form_closing_html . $after_form . $closing_html;
	}

	/**
	 * Load the necessary AJAX scripts
	 *
	 * @return bool
	 */
	public function load_ajax_scripts() {

		if( $this->loaded_ajax_scripts ) {
			return false;
		}

		// get ajax scripts to load in the footer
		wp_enqueue_script( 'mc4wp-ajax-forms' );

		// Print vars required by AJAX script
		$scheme = ( is_ssl() ) ? 'https' : 'http';
		wp_localize_script( 'mc4wp-ajax-forms', 'mc4wp_vars', array(
				'ajaxurl' => esc_url( admin_url( 'admin-ajax.php', $scheme ) ),
				'ajaxloader' => array(
					'enabled' => apply_filters( 'mc4wp_print_ajax_loader_styles', true ),
					'imgurl' => esc_url( MC4WP_PLUGIN_URL . 'assets/img/ajax-loader.gif' )
				)
			)
		);

		// set flag to ensure ajax scripts are only loaded once
		$this->loaded_ajax_scripts = true;

		return true;
	}

	/**
	 * Prints some inline JavaScript to enhance the form functionality
	 *
	 * This is only printed on pages that actually contain a form.
	 * Uses jQuery if its loaded, otherwise falls back to vanilla JS.
	 */
	public function print_js() {

		if( $this->inline_js_printed === true ) {
			return false;
		}

		if( wp_script_is( 'jquery', 'done' ) ) {
			// print jQuery
			?><script type="text/javascript">
                jQuery('.mc4wp-form').find('[type="submit"]').click(function () {
                    jQuery(this).parents('.mc4wp-form').addClass('mc4wp-form-submitted');
                });
			</script><?php
		} else {
			// Print vanilla JavaScript
			?><script type="text/javascript">
                (function() {
                    var forms = document.querySelectorAll('.mc4wp-form');
                    for (var i = 0; i < forms.length; i++) {
                        (function(el) {
                            var onclick = function( event ) {
                                el.classList.toggle('mc4wp-form-submitted');
                            };
                            var button = el.querySelector('[type="submit"]');
                            if (button.addEventListener) {
                                button.addEventListener( 'click', onclick);
                            } else {
                                button.attachEvent( 'onclick', onclick);
                            }
                        })(forms[i]);
                    }
                })();
			</script><?php
		}

		$this->inline_js_printed = true;
		return true;
	}

}
