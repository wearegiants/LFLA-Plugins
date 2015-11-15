<?php
if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Handles form submissions
 */
class MC4WP_Form_Request {

	/**
	 * @var int
	 */
	private $form_instance_number = 0;

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @var bool
	 */
	private $success = false;

	/**
	 * @var string
	 */
	private $mailchimp_error = '';

	/**
	 * @var int The ID of the form that was submitted
	 */
	private $form_id = 0;

	/**
	 * @var array
	 */
	private $form_options = array();

	/**
	 * @var bool
	 */
	public $is_valid;

	/**
	 * @var string
	 */
	private $error_code = 'error';

	/**
	 * @var array
	 */
	private $lists_fields_map = array();

	/**
	 * @var array
	 */
	private $unmapped_fields = array();

	/**
	 * @var array
	 */
	private $global_fields = array();

	/**
	 * @return bool
	 */
	public function is_successful() {
		return $this->success;
	}

	/**
	 * @return int
	 */
	public function get_form_instance_number() {
		return $this->form_instance_number;
	}

	/**
	 * @return int
	 */
	public function get_form_id() {
		return $this->form_id;
	}

	/**
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->data = array_change_key_case( $_POST, CASE_UPPER );

		// store number of submitted form
		$this->form_instance_number = absint( $this->data['_MC4WP_FORM_INSTANCE'] );
		$this->form_id = absint( $this->data['_MC4WP_FORM_ID'] );
		$this->form_options = mc4wp_get_form_settings( $this->form_id, true );

		// validate request
		$this->is_valid = $this->validate();

		// normalize posted data
		$this->data = $this->sanitize();

		// only proceed if request is valid
		if( $this->is_valid ) {

			// add some data to the posted data, like FNAME and LNAME
			$this->guess_missing_fields( $this->data );

			// map fields to corresponding MailChimp lists
			// abandon if mapping failed (missing required field, etc..)
			if( $this->map_data() ) {

				// subscribe using the processed data
				$this->success = $this->subscribe( $this->lists_fields_map );
			}

		}

		// format a HTTP / AJAX response
		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->send_ajax_response();
		} else {
			$this->send_http_response();
		}


		return $this->success;
	}

	/**
	 * Validates the request
	 *
	 * - Nonce validity
	 * - Honeypot
	 * - Captcha
	 * - Email address
	 * - Lists (POST and options)
	 * - Additional validation using a filter.
	 *
	 * @return bool
	 */
	private function validate() {

		// detect caching plugin
		$using_caching = ( defined( 'WP_CACHE' ) && WP_CACHE );

		// validate form nonce, but only if not using caching
		if ( ! $using_caching && ( ! isset( $this->data['_MC4WP_FORM_NONCE'] ) || ! wp_verify_nonce( $this->data['_MC4WP_FORM_NONCE'], '_mc4wp_form_nonce' ) ) ) {
			$this->error_code = 'invalid_nonce';
			return false;
		}

		// ensure honeypot was not filed
		if ( isset( $this->data['_MC4WP_REQUIRED_BUT_NOT_REALLY'] ) && ! empty( $this->data['_MC4WP_REQUIRED_BUT_NOT_REALLY'] ) ) {
			$this->error_code = 'spam';
			return false;
		}

		// check if captcha was present and valid
		if( isset( $this->data['_MC4WP_HAS_CAPTCHA'] ) && $this->data['_MC4WP_HAS_CAPTCHA'] == 1 && function_exists( 'cptch_check_custom_form' ) && cptch_check_custom_form() !== true ) {
			$this->error_code = 'invalid_captcha';
			return false;
		}

		// validate email
		if( ! isset( $this->data['EMAIL'] ) || ! is_string( $this->data['EMAIL'] ) || ! is_email( $this->data['EMAIL'] ) ) {
			$this->error_code = 'invalid_email';
			return false;
		}

		// get lists to subscribe to
		$lists = $this->get_lists();

		if ( empty( $lists ) ) {
			$this->error_code = 'no_lists_selected';
			return false;
		}

		/**
		 * @filter mc4wp_valid_form_request
		 *
		 * Use this to perform custom form validation.
		 * Return true if the form is valid or an error string if it isn't.
		 * Use the `mc4wp_form_messages` filter to register custom error messages.
		 */
		$valid_form_request = apply_filters( 'mc4wp_valid_form_request', true, $this->data );
		if( $valid_form_request !== true ) {
			$this->error_code = $valid_form_request;
			return false;
		}

		return true;
	}

	/**
	 * Sanitizes the request data.
	 *
	 * - Strips internal MailChimp for WP variables
	 * - Strips ignored fields
	 * - Sanitizes scalar values
	 * - Strips slashes on everything
	 *
	 * @return array
	 */
	private function sanitize() {

		$data = array();

		// Ignore those fields, we don't need them
		$ignored_fields = array( 'CPTCH_NUMBER', 'CNTCTFRM_CONTACT_ACTION', 'CPTCH_RESULT', 'CPTCH_TIME', 'ACTION' );

		foreach( $this->data as $key => $value ) {

			// Sanitize key
			$key = trim( $key );

			// Skip field if it starts with _ or if it's in ignored_fields array
			if( $key[0] === '_' || in_array( $key, $ignored_fields ) ) {
				continue;
			}

			// Sanitize value
			$value = ( is_scalar( $value ) ) ? sanitize_text_field( $value ) : $value;

			// Add value to array
			$data[ $key ] = $value;
		}

		// strip slashes on everything
		$data = stripslashes_deep( $data );

		// store data somewhere safe
		return $data;
	}

	/**
	 * Guesses the value of some fields.
	 *
	 * - FNAME and LNAME, if NAME is given
	 *
	 * @param array $data
	 * @return array
	 */
	public function guess_missing_fields( $data ) {

		// fill FNAME and LNAME if they're not set, but NAME is.
		if( isset( $data['NAME'] ) && ! isset( $data['FNAME'] ) && ! isset( $data['LNAME'] ) ) {

			$strpos = strpos( $data['NAME'], ' ' );
			if( $strpos !== false ) {
				$data['FNAME'] = substr( $data['NAME'], 0, $strpos );
				$data['LNAME'] = substr( $data['NAME'], $strpos );
			} else {
				$data['FNAME'] = $data['NAME'];
			}
		}

		return $data;
	}

	/**
	 * Send AJAX response
	 */
	public function send_ajax_response() {

		// build response
		$data = array(
			'data' => $this->data,
			'html' => $this->get_response_html()
		);


		// clear output, some plugins might have thrown errors by now.
		if( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		// successful sign-up?
		if( $this->success ) {

			if( ! empty( $this->form_options['redirect'] ) ) {

				$redirect_url = add_query_arg( array( 'mc4wp_email' => urlencode( $this->data['EMAIL'] ) ), $this->form_options['redirect'] );
				$data['redirect'] = array(
					'url' => $redirect_url,
					'delay' => apply_filters( 'mc4wp_form_redirect_delay', 2.5 ) * 1000
				);

			} else {
				$data['redirect'] = false;
			}

			$data['hide_form'] = ( (bool) $this->form_options['hide_after_success'] );

			wp_send_json_success( $data );
			exit;
		}

		// an error occured, throw error response
		$data['error'] = array(
			'type' => $this->error_code
		);

		wp_send_json_error( $data );
		exit;
	}

	/**
	 * Send HTTP response
	 */
	public function send_http_response() {

		// do stuff on success, non-AJAX only
		if( $this->success ) {

			// check if we want to redirect the visitor
			if ( ! empty( $this->form_options['redirect'] ) ) {

				$redirect_url = add_query_arg( array( 'mc4wp_email' => urlencode( $this->data['EMAIL'] ) ), $this->form_options['redirect'] );
				wp_redirect( $redirect_url );
				exit;
			}

		} else {

			/**
			 * @action mc4wp_form_error_{ERROR_CODE}
			 *
			 * Use to hook into various sign-up errors. Hook names are:
			 *
			 * - mc4wp_form_error_error                     General errors
			 * - mc4wp_form_error_invalid_email             Invalid email address
			 * - mc4wp_form_error_already_subscribed        Email is already on selected list(s)
			 * - mc4wp_form_error_required_field_missing    One or more required fields are missing
			 * - mc4wp_form_error_no_lists_selected         No MailChimp lists were selected
			 *
			 * @param   int     $form_id        The ID of the submitted form
			 * @param   string  $email          The email of the subscriber
			 * @param   array   $data           Additional list fields, like FNAME etc (if any)
			 */
			do_action( 'mc4wp_form_error_' . $this->error_code, $this->form_id, $this->data['EMAIL'], $this->data );
		}

	}

	/**
	 * Maps the received data to MailChimp lists
	 *
	 * @return array
	 */
	private function map_data() {

		$data = $this->data;

		$map = array();
		$mapped_fields = array( 'EMAIL' );
		$unmapped_fields = array();

		$mailchimp = new MC4WP_MailChimp();

		// loop through selected lists
		foreach( $this->get_lists() as $list_id ) {

			$list = $mailchimp->get_list( $list_id, false, true );

			// skip this list if it's unexisting
			if( ! is_object( $list ) || ! isset( $list->merge_vars ) ) {
				continue;
			}

			// start with empty list map
			$list_map = array();

			// loop through other list fields
			foreach( $list->merge_vars as $field ) {

				// skip EMAIL field
				if( $field->tag === 'EMAIL' ) {
					continue;
				}

				// check if field is required
				if( $field->req ) {
					if( ! isset( $data[ $field->tag ] ) || '' === $data[ $field->tag ] ) {
						$this->error_code = 'required_field_missing';
						return false;
					}
				}

				// if field is not set, continue.
				if( ! isset( $data[ $field->tag ] ) ) {
					continue;
				}

				// grab field value from data
				$field_value = $data[ $field->tag ];

				$field_value = $this->format_field_value( $field_value, $field->field_type );

				// add field value to map
				$mapped_fields[] = $field->tag;
				$list_map[ $field->tag ] = $field_value;
			}

			// loop through list groupings if GROUPINGS data was sent
			if( isset( $data['GROUPINGS'] ) && is_array( $data['GROUPINGS'] ) && ! empty( $list->interest_groupings ) ) {

				$list_map['GROUPINGS'] = array();

				foreach( $list->interest_groupings as $grouping ) {

					// check if data for this group was sent
					if( isset( $data['GROUPINGS'][$grouping->id] ) ) {
						$group_data = $data['GROUPINGS'][$grouping->id];
					} elseif( isset( $data['GROUPINGS'][$grouping->name] ) ) {
						$group_data = $data['GROUPINGS'][$grouping->name];
					} else {
						// no data for this grouping was sent, just continue.
						continue;
					}

					// format new grouping
					$grouping = array(
						'id' => $grouping->id,
						'groups' => $group_data
					);

					// make sure groups is an array
					if( ! is_array( $grouping['groups'] ) ) {
						$grouping['groups'] = sanitize_text_field( $grouping['groups'] );
						$grouping['groups'] = explode( ',', $grouping['groups'] );
					}

					$list_map['GROUPINGS'][] = $grouping;

				}

				// unset GROUPINGS if no grouping data was found for this list
				if( 0 === count( $list_map['GROUPINGS'] ) ) {
					unset( $list_map['GROUPINGS'] );
				}
			}

			// add to total map
			$map[ $list_id ] = $list_map;
		}

		// map global fields
		$global_field_names = array( 'MC_LOCATION', 'MC_NOTES', 'MC_LANGUAGE' );
		foreach( $global_field_names as $field_name ) {
			if( isset( $data[ $field_name ] ) ) {
				$this->global_fields[ $field_name ] = $data[ $field_name ];
			}
		}

		// is there still unmapped data left?
		$total_fields_mapped = count( $mapped_fields ) + count( $this->global_fields );
		if( $total_fields_mapped < count( $data ) ) {
			foreach( $data as $field_key => $field_value ) {
				if( ! in_array( $field_key, $mapped_fields ) ) {
					$unmapped_fields[ $field_key ] = $field_value;
				}
			}
		}

		// add built arrays to instance properties
		$this->unmapped_fields = $unmapped_fields;
		$this->lists_fields_map = $map;
		return true;
	}

	/**
	 * Format field value according to its type
	 *
	 * @param $field_type
	 * @param $field_value
	 *
	 * @return array|string
	 */
	private function format_field_value( $field_value, $field_type ) {

		$field_type = strtolower( $field_type );

		switch( $field_type ) {

			// birthday fields need to be MM/DD for the MailChimp API
			case 'birthday':
				$field_value = (string) date( 'm/d', strtotime( $field_value ) );
				break;

			case 'address':

				// auto-format if addr1 is not set
				if( ! isset( $field_value['addr1'] ) ) {

					// addr1, addr2, city, state, zip, country
					$address_pieces = explode( ',', $field_value );

					// try to fill it.... this is a long shot
					$field_value = array(
						'addr1' => $address_pieces[0],
						'city'  => ( isset( $address_pieces[1] ) ) ?   $address_pieces[1] : '',
						'state' => ( isset( $address_pieces[2] ) ) ?   $address_pieces[2] : '',
						'zip'   => ( isset( $address_pieces[3] ) ) ?   $address_pieces[3] : ''
					);

				}

				break;
		}

		/**
		 * @filter `mc4wp_format_field_value`
		 * @param mixed $field_value
		 * @param string $field_type
		 * @expects mixed
		 *
		 *          Format a field value according to its MailChimp field type
		 */
		$field_value = apply_filters( 'mc4wp_format_field_value', $field_value, $field_type );

		return $field_value;
	}

	/**
	 * Adds global fields like OPTIN_IP, MC_LANGUAGE, OPTIN_DATE, etc to the list of user-submitted field data.
	 *
	 * @param string $list_id
	 * @param array $list_field_data
	 * @return array
	 */
	private function get_list_merge_vars( $list_id, $list_field_data ) {

		$merge_vars = array();

		// add OPTIN_IP, we do this here as the user shouldn't be allowed to set this
		$merge_vars['OPTIN_IP'] = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );

		// make sure MC_LANGUAGE matches the requested format. Useful when getting the language from WPML etc.
		if( isset( $this->global_fields['MC_LANGUAGE'] ) ) {
			$merge_vars['MC_LANGUAGE'] = strtolower( substr( $this->global_fields['MC_LANGUAGE'], 0, 2 ) );
		}

		$merge_vars = array_merge( $merge_vars, $list_field_data );

		/**
		 * @filter `mc4wp_merge_vars`
		 * @expects array
		 * @param int $form_id
		 * @param string $list_id
		 *
		 * Can be used to filter the merge variables sent to a given list
		 */
		$merge_vars = apply_filters( 'mc4wp_merge_vars', $merge_vars, $this->form_id, $list_id );

		return (array) $merge_vars;
	}



	/**
	 * Subscribes the given email and additional list fields
	 *
	 * @param array $lists_data
	 * @return bool
	 */
	private function subscribe( $lists_data ) {

		$api = mc4wp_get_api();

		do_action( 'mc4wp_before_subscribe', $this->data['EMAIL'], $this->data, $this->form_id );

		$result = false;
		$email_type = $this->get_email_type();

		foreach ( $lists_data as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$list_merge_vars = $this->get_list_merge_vars( $list_id, $list_field_data );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $this->data['EMAIL'], $list_merge_vars, $email_type, $this->form_options['double_optin'], $this->form_options['update_existing'], $this->form_options['replace_interests'], $this->form_options['send_welcome'] );
			do_action( 'mc4wp_subscribe', $this->data['EMAIL'], $list_id, $list_merge_vars, $result, 'form', 'form', $this->form_id );

		}

		do_action( 'mc4wp_after_subscribe', $this->data['EMAIL'], $this->data, $this->form_id, $result );

		if ( $result !== true ) {
			// subscribe request failed, store error.
			$this->success = false;
			$this->error_code = $result;
			$this->mailchimp_error = $api->get_error_message();
			return false;
		}

		// subscription succeeded

		// store user email in a cookie
		$this->set_email_cookie( $this->data['EMAIL'] );

		// send an email copy if that is desired
		if( $this->form_options['send_email_copy'] ) {
			$this->send_email();
		}

		// Store success result
		$this->success = true;

		return true;
	}

	/**
	 * Send an email with a subscription summary to a given email address
	 */
	private function send_email() {

		// bail if receiver is empty
		if( '' === $this->form_options['email_copy_receiver'] ) {
			return;
		}

		// email receiver
		$to = explode( ',', str_replace( ' ', '', $this->form_options['email_copy_receiver'] ) );

		$form = get_post( $this->form_id );
		$form_name = $form->post_title;

		// email subject
		$subject = __( 'New MailChimp Sign-Up', 'mailchimp-for-wp' ) . ' - ' . get_bloginfo( 'name' );

		$mailchimp = new MC4WP_MailChimp();

		// build email message
		ob_start();

		?>
		<h3>MailChimp for WordPress: <?php _e( 'New Sign-Up', 'mailchimp-for-wp' ); ?></h3>
		<p><?php printf( __( '<strong>%s</strong> signed-up at %s on %s using the form "%s".', 'mailchimp-for-wp' ), $this->data['EMAIL'], date( 'H:i' ), date( 'd/m/Y' ), $form_name ); ?></p>
		<table cellspacing="0" cellpadding="10" border="0" style="border: 1px solid #EEEEEE;">
			<tbody>
				<?php foreach( $this->lists_fields_map as $list_id => $field_data ) { ?>
					<tr>
						<td colspan="2"><h4 style="border-bottom: 1px solid #efefef; margin-bottom: 0; padding-bottom: 5px;"><?php echo __( 'List', 'mailchimp-for-wp' ) . ': ' . $mailchimp->get_list_name( $list_id ); ?></h4></td>
					</tr>
					<tr>
						<td><strong><?php _e( 'Email address:', 'mailchimp-for-wp' ); ?></strong></td>
						<td><?php echo $this->data['EMAIL']; ?></td>
					</tr>
					<?php
					foreach( $field_data as $field_tag => $field_value ) {

						if( $field_tag === 'GROUPINGS' && is_array( $field_value ) && count( $field_value ) > 0 ) {

							foreach( $field_value as $grouping ) {

								$groups = implode( ', ', $grouping['groups'] ); ?>
								<tr>
									<td><strong><?php echo $mailchimp->get_list_grouping_name_by_id( $list_id, $grouping['id'] ); ?></strong></td>
									<td><?php echo esc_html( $groups ); ?></td>
								</tr>
							<?php
							}

						} else {
							$field_name = $mailchimp->get_list_field_name_by_tag( $list_id, $field_tag );

							// convert array values to comma-separated string value
							if( is_array( $field_value ) ) {
								$field_value = implode( ', ', $field_value );
							}
							?>
							<tr>
								<td><strong><?php echo esc_html( $field_name ); ?></strong></td>
								<td><?php echo esc_html( $field_value ); ?></td>
							</tr>
							<?php
						}
					} ?>
				<?php } ?>
				<?php
				if( count( $this->unmapped_fields ) > 0 ) { ?>
					<tr>
						<td colspan="2"><h4 style="border-bottom: 1px solid #efefef; margin-bottom: 0; padding-bottom: 5px;"><?php _e( 'Other fields', 'mailchimp-for-wp' ); ?></h4></td>
					</tr>
					<?php
					foreach( $this->unmapped_fields as $field_tag => $field_value ) {
						?>
						<tr>
							<td><strong><?php echo esc_html( ucfirst( strtolower( $field_tag ) ) ); ?></strong></td>
							<td><?php echo esc_html( $field_value ); ?></td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>

		<?php  if( $this->form_options['double_optin'] ) { ?>
			<p style="color:#666;"><?php printf( __( 'Note that you\'ve enabled double opt-in for the "%s" form. The user won\'t be added to the selected MailChimp lists until they confirm their email address.', 'mailchimp-for-wp' ), $form_name ); ?></p>
		<?php } ?>
		<p style="color:#666;"><?php _e( 'This email was auto-sent by the MailChimp for WordPress plugin.', 'mailchimp-for-wp' ); ?></p>
		<?php
		$message = ob_get_contents();
		ob_end_clean();

		/**
		 * @filter mc4wp_email_summary_receiver
		 * @expects string|array String or array of emails
		 * @param   int     $form_id        The ID of the submitted form
		 * @param   string  $email          The email of the subscriber
		 * @param   array   $lists_data     Additional list fields, like FNAME etc (if any)
		 *
		 * Use to set email addresses to send the email summary to
		 */
		$receivers = apply_filters( 'mc4wp_email_summary_receiver', $to, $this->form_id, $this->data['EMAIL'], $this->lists_fields_map );

		/**
		 * @filter mc4wp_email_summary_subject
		 * @expects string|array String or array of emails
		 * @param   int     $form_id        The ID of the submitted form
		 * @param   string  $email          The email of the subscriber
		 * @param   array   $lists_data     Additional list fields, like FNAME etc (if any)
		 *
		 * Use to set subject of email summaries
		 */
		$subject = apply_filters( 'mc4wp_email_summary_subject', $subject, $this->form_id, $this->data['EMAIL'], $this->lists_fields_map );

		/**
		 * @filter mc4wp_email_summary_message
		 * @expects string|array String or array of emails
		 * @param   int     $form_id        The ID of the submitted form
		 * @param   string  $email          The email of the subscriber
		 * @param   array   $lists_data     Additional list fields, like FNAME etc (if any)
		 *
		 * Use to set or customize message of email summaries
		 */
		$message = apply_filters( 'mc4wp_email_summary_message', $message, $this->form_id, $this->data['EMAIL'], $this->lists_fields_map );


		// send email
		wp_mail( $receivers, $subject, $message, 'Content-Type: text/html' );
	}

	/**
	 * Gets the email_type
	 *
	 * @return string The email type to use for subscription coming from this form
	 */
	private function get_email_type( ) {

		$email_type = 'html';

		// get email type from form
		if( isset( $this->data['_MC4WP_EMAIL_TYPE'] ) ) {
			$email_type = sanitize_text_field( $_POST['_MC4WP_EMAIL_TYPE'] );
		}

		// allow plugins to override this email type
		$email_type = apply_filters( 'mc4wp_email_type', $email_type, $this->form_id );

		return (string) $email_type;
	}

	/**
	 * Get MailChimp List(s) to subscribe to
	 *
	 * @return array Array of selected MailChimp lists
	 */
	private function get_lists() {

		static $lists;

		if( is_null( $lists ) ) {

			$lists = $this->form_options['lists'];

			// get lists from form, if set.
			if( isset( $this->data['_MC4WP_LISTS'] ) && ! empty( $this->data['_MC4WP_LISTS'] ) ) {

				$lists = $this->data['_MC4WP_LISTS'];

				// make sure lists is an array
				if( ! is_array( $lists ) ) {
					$lists = sanitize_text_field( $lists );
					$lists = array( $lists );
				}

			}

			// allow plugins to alter the lists to subscribe to
			$lists = apply_filters( 'mc4wp_lists', $lists, $this->form_id );
		}

		return (array) $lists;
	}

	/**
	 * Stores the given email in a cookie for 30 days
	 *
	 * @param string $email
	 */
	private function set_email_cookie( $email ) {

		/**
		 * @filter `mc4wp_cookie_expiration_time`
		 * @expects timestamp
		 *
		 * Timestamp indicating when the email cookie expires, defaults to 30 days
		 */
		$expiration_time = apply_filters( 'mc4wp_cookie_expiration_time', strtotime( '+30 days' ) );

		setcookie( 'mc4wp_email', $email, $expiration_time, '/' );
	}

	/**
	 * Returns the HTML for success or error messages
	 *
	 * @param int $form_id
	 *
	 * @return string
	 */
	public function get_response_html( ) {

		// get all form messages
		$messages = $this->get_form_messages();

		// retrieve correct message
		$type = ( $this->success ) ? 'success' : $this->error_code;
		$message = ( isset( $messages[ $type ] ) ) ? $messages[ $type ] : $messages['error'];

		/**
		 * @filter mc4wp_form_error_message
		 * @deprecated 2.0.5
		 * @use mc4wp_form_messages
		 *
		 * Used to alter the error message, don't use. Use `mc4wp_form_messages` instead.
		 */
		$message['text'] = apply_filters( 'mc4wp_form_error_message', $message['text'], $this->error_code );

		$html = '<div class="mc4wp-alert mc4wp-'. $message['type'].'">' . $message['text'] . '</div>';

		// show additional MailChimp API errors to administrators
		if( ! $this->success && current_user_can( 'manage_options' ) ) {

			if( '' !== $this->mailchimp_error ) {
				$html .= '<div class="mc4wp-alert mc4wp-error"><strong>Admin notice:</strong> '. $this->mailchimp_error . '</div>';
			}
		}

		return $html;
	}

	/**
	 * Returns the various error and success messages in array format
	 *
	 * Example:
	 * array(
	 *      'invalid_email' => array(
	 *          'type' => 'css-class',
	 *          'text' => 'Message text'
	 *      ),
	 *      ...
	 * );
	 *
	 * @return array
	 */
	public function get_form_messages( ) {

		$messages = array(
			'already_subscribed' => array(
				'type' => 'notice',
				'text' => $this->form_options['text_already_subscribed']
			),
			'error' => array(
				'type' => 'error',
				'text' => $this->form_options['text_error']
			),
			'invalid_email' => array(
				'type' => 'error',
				'text' => $this->form_options['text_invalid_email']
			),
			'success' => array(
				'type' => 'success',
				'text' => $this->form_options['text_success']
			),
			'invalid_captcha' => array(
				'type' => 'error',
				'text' => $this->form_options['text_invalid_captcha']
			),
			'required_field_missing' => array(
				'type' => 'error',
				'text' => $this->form_options['text_required_field_missing']
			)
		);

		/**
		 * @filter mc4wp_form_messages
		 * @expects array
		 *
		 * Allows registering custom form messages, useful if you're using custom validation using the `mc4wp_valid_form_request` filter.
		 */
		$messages = apply_filters( 'mc4wp_form_messages', $messages, $this->form_id );

		return (array) $messages;
	}

}