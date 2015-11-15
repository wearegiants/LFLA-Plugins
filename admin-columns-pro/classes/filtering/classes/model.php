<?php

/**
 * Addon class
 *
 * @since 1.0
 */
abstract class CAC_Filtering_Model {

	protected $storage_model;

	protected $filter_values;

	protected $has_dropdown = false;

	protected $wpdb;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct( $storage_model ) {

		global $wpdb;

		$this->storage_model = $storage_model;
		$this->wpdb = $wpdb;

		// enable filtering per column
		add_action( "cac/columns/storage_key={$this->storage_model->key}", array( $this, 'enable_filtering' ) );
	}

	/**
	 * @since 3.5
	 */
	protected function get_user_ids() {
		return $this->wpdb->get_col( "SELECT ID FROM {$this->wpdb->users}" );
	}

	/**
	 * @since 3.5
	 */
	protected function get_comment_ids() {
		return $this->wpdb->get_col( "SELECT comment_ID FROM {$this->wpdb->comments}" );
	}

	/**
	 * @since 3.5
	 */
	protected function get_comment_fields( $field ) {
		return (array) $this->wpdb->get_col( "SELECT " . sanitize_key( $field ) . " FROM {$this->wpdb->comments} WHERE " . sanitize_key( $field ) . " <> ''" );
	}

	/**
	 * @since 3.5
	 */
	protected function set_filter_value( $key, $value ) {
		$this->filter_values[ $key ] = $value;
	}

	protected function get_filter_value( $key ) {
		return isset( $this->filter_values[ $key ] ) ? $this->filter_values[ $key ] : false;
	}

	/**
	 * @since 3.5
	 */
	protected function get_non_filterable_acf_types() {
		return array();
	}

	/**
	 * @since 3.5
	 */
	protected function get_cache_id() {
		return 'filtering' . $this->storage_model->type;
	}

	/**
	 * @since 3.5
	 */
	protected function enable_filterable_custom_field( $column ) {
		if ( 'column-meta' === $column->properties->type ) {
			if ( in_array( $column->options->field_type, array( '', 'checkmark', 'color', 'date', 'excerpt', 'image', 'library_id', 'numeric', 'title_by_id', 'user_by_id' ) ) ) {
				$column->set_properties( 'is_filterable', true );
			}
		}
	}

	/**
	 * @since 3.5
	 */
	protected function enable_filterable_acf_field( $column ) {
		if ( ! class_exists( 'CPAC_Addon_ACF' ) || ( 'column-acf_field' !== $column->properties->type ) || ! method_exists( $column, 'get_field' ) ) {
			return;
		}

		$field = $column->get_field();

		switch ( $field['type'] ) {

			case 'post_object' :
			case 'select' :
				// only allow single values
				if ( 0 === $field['multiple'] ) {
					$column->set_properties( 'is_filterable', true );
				}
			break;

			case 'taxonomy' :
				// only allow single values
				if ( in_array( $field['field_type'], array( 'radio', 'select' ) ) ) {
					$column->set_properties( 'is_filterable', true );
				}
			break;

			case 'number' :
			case 'email' :
			case 'password' :
			case 'text' :
			case 'image' :
			case 'file' :
			case 'url' :
			case 'radio' :
			case 'true_false' :
			case 'page_link' :
			case 'user' :
			case 'date_picker' :
			case 'color_picker' :
				$column->set_properties( 'is_filterable', true );
			break;

			// not supported
			// these fields are stored serialised
			// checkbox, textarea, wysiwyg, gallery, relationship, google_map
		}
	}

	/**
	 * Indents any object as long as it has a unique id and that of its parent.
	 *
	 * @since 1.0
	 *
	 * @param type $array
	 * @param type $parentId
	 * @param type $parentKey
	 * @param type $selfKey
	 * @param type $childrenKey
	 * @return array Indented Array
	 */
	protected function indent( $array, $parentId = 0, $parentKey = 'post_parent', $selfKey = 'ID', $childrenKey = 'children' ) {
		$indent = array();

		$i = 0;
		foreach( $array as $v ) {

			if ( $v->$parentKey == $parentId ) {
				$indent[$i] = $v;
				$indent[$i]->$childrenKey = $this->indent( $array, $v->$selfKey, $parentKey, $selfKey );

				$i++;
			}
		}

		return $indent;
	}

	/**
	 * @since 3.5
	 */
	protected function get_meta_options( $column ) {
		$options = array();
		$empty_option = true;

		if ( $values = $this->get_values_by_meta_key( $column->options->field ) ) {
			foreach ( $values as $value ) {

				// serialized data can not be filtered using WP_Query or in an efficient way, no point of displaying it.
				if ( is_serialized( $value[0] ) ) {
					continue;
				}

				$label = $value[0];

				switch ( $column->options->field_type ) :

					case "date" :
					case "user_by_id" :
					case "title_by_id" :
						if ( $_value = $column->get_value_by_meta( $value[0] ) ) {
							$label = $_value;
						}
						break;

				endswitch;

				$options[ $value[0] ] = trim( strip_tags( $label ) );
			}
		}

		return array(
			'empty_option' => $empty_option,
			'options' => $options,
		);
	}

	/**
	 * @since 3.5
	 */
	protected function get_acf_options( $column ) {
		if ( ! method_exists( $column, 'get_field_key' ) ) {
			return false;
		}

		$acf_field_obj = get_field_object( $column->get_field_key() );
		if ( ! $acf_field_obj ) {
			return false;
		}

		$values = $this->get_values_by_meta_key( $acf_field_obj['name'] );
		if ( ! $values ) {
			return false;
		}

		$field = $column->get_field();
		$field_type = $column->get_field_type();

		if ( 'repeater' == $field_type ) {
			return false;
		}

		$options = array();
		$empty_option = true;

		foreach ( $values as $value ) {

			$field_value = $value[0];
			if ( is_serialized( $field_value ) ) {
				continue;
			}

			switch ( $field_type ) :

				case "select" :
				case "checkbox" :
				case "radio" :
					if ( isset( $field['choices'] ) && isset( $field['choices'][ $value[0] ] ) ) {
						$field_value = $field['choices'][ $value[0] ];
					}
					break;
				case "true_false" :
					$empty_option = false;
					if ( 0 == $value[0] ) { $field_value = __( 'False', 'cpac' ); }
					if ( 1 == $value[0] ) { $field_value = __( 'True', 'cpac' ); }
					break;
				case "page_link" :
				case "post_object" :
					$field_value = get_the_title( $value[0] );
					break;
				case "taxonomy" :
					$term = get_term( $value[0], $field['taxonomy'] );
					if ( $term && ! is_wp_error( $term ) ) {
						$field_value = $term->name;
					}
					break;
				case "user" :
					if ( $user = get_userdata( $value[0] ) ) {
						$field_value = $user->display_name;
					}
					break;

			endswitch;

			$options[ $value[0] ] = $field_value;
		}

		return array(
			'empty_option' => $empty_option,
			'options' => $options,
		);
	}

	/**
	 * Get values by meta key
	 *
	 * @since 3.5
	 */
	protected function get_values_by_meta_key( $meta_key ) {

		$sql = "
			SELECT DISTINCT meta_value
			FROM {$this->wpdb->postmeta} pm
			INNER JOIN {$this->wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.post_type = %s
			AND pm.meta_key = %s
			AND pm.meta_value != ''
			ORDER BY 1
		";
		$values = $this->wpdb->get_results( $this->wpdb->prepare( $sql, $this->storage_model->post_type, $meta_key ), ARRAY_N );
		if ( is_wp_error( $values ) || ! $values ) {
			return array();
		}
		return $values;
	}

	/**
	 * Get values by post field
	 *
	 * @since 1.0
	 */
	public function get_post_fields( $post_field ) {

		$post_field = sanitize_key( $post_field );
		$sql = "
			SELECT DISTINCT {$post_field}
			FROM {$this->wpdb->posts}
			WHERE post_type = %s
			AND {$post_field} <> ''
			ORDER BY 1
		";

		$values = $this->wpdb->get_col( $this->wpdb->prepare( $sql, $this->storage_model->post_type ) );
		if ( is_wp_error( $values ) || ! $values ) {
			return array();
		}
		return $values;
	}

	/**
	 * Get taxonomy filter vars
	 *
	 * @since 3.4.3
	 *
	 * @param string $value Column value
	 * @param string $taxonomy Taxonomy name
	 * @return array WP_Query Tax Query vars
	 */
	protected function get_taxonomy_tax_query( $value, $taxonomy, $query_args = '' ) {

		$tax_query = array();

		if ( ! empty( $query_args['tax_query'] ) ) {
			$tax_query = $query_args['tax_query'];
			$tax_query['relation'] = 'AND';
		}

		if ( in_array( $value, array( 'cpac_empty', 'cpac_not_empty' ) ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'terms'    => get_terms( $taxonomy, array( 'fields' => 'ids'  ) ),
				'operator' => 'cpac_empty' == $value ? 'NOT IN' : 'IN'
			);
		}
		else {
			$tax_query[] = array(
				'taxonomy'	=> $taxonomy,
				'field'		=> 'slug',
				'terms'		=> $value
			);
		}

		return $tax_query;
	}

	/**
	 * Applies indenting markup for taxonomy dropdown
	 *
	 * @since 1.0
	 *
	 * @param array $array
	 * @param int $level
	 * @param array $ouput
	 * @return array Output
	 */
	protected function apply_indenting_markup( $array, $level = 0, $output = array() ) {
		foreach ( $array as $v ) {

			$prefix = '';
			for( $i=0; $i<$level; $i++ ) {
				$prefix .= '&nbsp;&nbsp;';
			}

			$output[ $v->slug ] = $prefix . $v->name;

			if ( ! empty( $v->children ) ) {
				$output = $this->apply_indenting_markup( $v->children, ( $level + 1 ), $output );
			}
		}

		return $output;
	}

	/**
	 * Create dropdown
	 *
	 * @since 1.0
	 *
	 * @param string $name Attribute Name
	 * @param string $label Label
	 * @param array $options Array with options
	 * @param string $selected Current item
	 * @param bool $add_empty_option Add two options for filtering on 'EMPTY' and 'NON EMPTY' values
	 * @return string Dropdown HTML select element
	 */
	protected function dropdown( $column, $options, $add_empty_option = false, $top_label = '' ) {

		/**
		 * Filter all dropdown options
		 *
		 * @since 3.0.8.5
		 * @param array $options All the filtering options: value => label
		 * @param CPAC_Column $column_instance Column class instance
		 */
		$options = apply_filters( 'cac/addon/filtering/options', $options, $column );

		if ( empty( $options ) ) {
			return false;
		}

		$this->has_dropdown = true;

		$current = isset( $_GET['cpac_filter'] ) && isset( $_GET['cpac_filter'][ $column->properties->name ] ) ? urldecode( $_GET['cpac_filter'][ $column->properties->name ] ) : '';
		?>

		<select class="postform" name="cpac_filter[<?php echo $column->properties->name; ?>]">

		<?php

		if ( ! $top_label ) {
			$top_label = sprintf( __( 'All %s', 'cpac' ), $column->options->label );
		}

		/**
		 * Filter the top label of the dropdown menu
		 *
		 * @param string $label
		 * @param CPAC_Column $column_instance Column class instance
		 */
		if ( $top_label = apply_filters( 'cac/addon/filtering/dropdown_top_label', $top_label, $column ) ) : ?>
			<option value="">
				<?php echo $top_label; ?>
			</option>
		<?php endif; ?>
			<?php foreach ( $options as $value => $label ) : ?>
				<?php

				if ( strlen( $label ) > 60 ) {
					$label = substr( $label, 0, 58 ) . '..';
				}

				// deprecated filter, use 'cac/addon/filtering/options'
				$label = apply_filters( 'cac/addon/filtering/dropdown_label', $label, $value, $column->options, $column->storage_model->key, $column );
				?>
				<option value="<?php echo esc_attr( urlencode( $value ) ); ?>" <?php selected( $value, $current ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
			<?php
			/**
			 * Filter empty option
			 *
			 * @param bool True / False
			 * @param CPAC_Column $column_instance Column class instance
			 */
			if ( apply_filters( 'cac/addon/filtering/dropdown_empty_option', $add_empty_option, $column ) ) : ?>
				<option disabled>──────────</option>
				<option value="cpac_empty" <?php selected( 'cpac_empty', $current ); ?>><?php _e( 'Empty', 'cpac' ); ?></option>
				<option value="cpac_not_empty" <?php selected( 'cpac_not_empty', $current ); ?>><?php _e( 'Not empty', 'cpac' ); ?></option>
			<?php endif; ?>
		</select>
		<?php
	}
}