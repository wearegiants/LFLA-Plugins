<?php

/**
 * Filtering Model for Posts ánd Media!
 *
 * @since 1.0
 */
class CAC_Filtering_Model_Post extends CAC_Filtering_Model {

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct( $storage_model ) {

		parent::__construct( $storage_model );

		// handle filtering request
		add_filter( 'request', array( $this, 'handle_filter_requests' ), 2 );

		// add dropdowns
		add_action( 'restrict_manage_posts', array( $this, 'add_filtering_dropdown' ) );
	}

	/**
	 * Enable filtering
	 *
	 * @since 1.0
	 */
	public function enable_filtering( $columns ) {

		$include_types = array(

			// WP default columns
			'categories',
			'tags',

			// Custom columns
			'column-author_name',
			'column-before_moretag',
			'column-comment_count',
			'column-comment_status',
			'column-excerpt',
			'column-featured_image',
			'column-last_modified_author',
			'column-page_template',
			'column-ping_status',
			'column-post_formats',
			'column-roles',
			'column-status',
			'column-sticky',
			'column-taxonomy',

			// WooCommerce columns
			'product_cat', // default
			'product_tag', // default
			'column-wc-featured',
			'column-wc-visibility',
			'column-wc-free_shipping',
			//'column-wc-apply_before_tax',
			'column-wc-shipping_class',
		);

		foreach ( $columns as $column ) {
			if ( in_array( $column->properties->type, $include_types ) ) {
				$column->set_properties( 'is_filterable', true );
			}

			$this->enable_filterable_custom_field( $column );
			$this->enable_filterable_acf_field( $column );
		}
	}

	/**
	 * @since 3.5
	 */
	public function filter_by_author_name( $where ) {
		return $where . $this->wpdb->prepare( "AND {$this->wpdb->posts}.post_author = %s", $this->get_filter_value( 'column-author_name' ) );
	}
	public function filter_by_before_moretag( $where ) {
		return $where . "AND {$this->wpdb->posts}.post_content" . $this->get_sql_value( $this->get_filter_value( 'column-before_moretag' ), '<!--more-->' );
	}
	public function filter_by_comment_count( $where ) {
		$val = $this->get_filter_value( 'column-comment_count' );
		$sql_val = ' = ' . $val;
		if ( 'cpac_not_empty' == $val ) {
			$sql_val = ' != 0';
		}
		else if ( 'cpac_empty' == $val ) {
			$sql_val = ' = 0';
		}
		return "{$where} AND {$this->wpdb->posts}.comment_count" . $sql_val;
	}
	public function filter_by_comment_status( $where ) {
		return $where . $this->wpdb->prepare( "AND {$this->wpdb->posts}.comment_status = %s", $this->get_filter_value( 'column-comment_status' ) );
	}
	public function filter_by_excerpt( $where ) {
		$val = $this->get_filter_value( 'column-excerpt' );
		$sql_val = '1' === $val ? " != ''" : " = ''";
		return "{$where} AND {$this->wpdb->posts}.post_excerpt" . $sql_val;
	}
	public function filter_by_ping_status( $where ) {
		return $where . $this->wpdb->prepare( "AND {$this->wpdb->posts}.ping_status = %s", $this->get_filter_value( 'column-ping_status' ) );
	}
	public function filter_by_sticky( $where ) {
		$val = $this->get_filter_value( 'column-sticky' );
		if ( ! ( $stickies = get_option( 'sticky_posts' ) ) ) {
			return $where;
		}
		$sql_val = '1' === $val ? " IN ('" . implode( "','", $stickies ) . "')" : " NOT IN ('" . implode( "','", $stickies ) . "')";
		return "{$where} AND {$this->wpdb->posts}.ID" . $sql_val;
	}

	/**
	 * Get SQL compare
	 *
	 * @since 1.0
	 *
	 * @param string $filter_value Selected filter value
	 * @param string $value_to_match_empty Overwrite the filter value
	 * @return string SQL compare
	 */
	private function get_sql_value( $filter_value, $value_to_match_empty = '' ) {
		$sql_query_compare = " = '{$filter_value}'";

		if ( 'cpac_not_empty' === $filter_value || '1' === $filter_value ) {
			$val = $value_to_match_empty ? $value_to_match_empty : $filter_value;
			$sql_query_compare = " LIKE '%{$val}%'";
		}
		else if ( 'cpac_empty' == $filter_value || '0' === $filter_value) {
			$val = $value_to_match_empty ? $value_to_match_empty : $filter_value;
			$sql_query_compare = " NOT LIKE '%{$val}%'";
		}

		return $sql_query_compare;
	}

	/**
	 * Handle filter request
	 *
	 * @since 1.0
	 */
	public function handle_filter_requests( $vars ) {

		global $pagenow;

		if ( $this->storage_model->page . '.php' != $pagenow || empty( $_REQUEST['cpac_filter'] ) ) {
			return $vars;
		}

		if ( ! empty( $vars['post_type'] ) && $vars['post_type'] !== $this->storage_model->post_type ) {
			return $vars;
		}

		// go through all filter requests per column
		foreach ( $_REQUEST['cpac_filter'] as $name => $value ) {

			$value = urldecode( $value );

			if ( strlen( $value ) < 1 ) {
				continue;
			}

			if ( ! $column = $this->storage_model->get_column_by_name( $name ) ) {
				continue;
			}

			// add the value to so we can use it in the 'post_where' callback
			$this->set_filter_value( $column->properties->type, $value );

			// meta arguments
			$meta_value 		= in_array( $value, array( 'cpac_empty', 'cpac_not_empty' ) ) ? '' : $value;
			$meta_query_compare = 'cpac_not_empty' == $value ? '!=' : '=';

			switch ( $column->properties->type ) :

				// Default
				case 'tags' :
					$vars['tax_query'] = $this->get_taxonomy_tax_query( $value, 'post_tag', $vars );
					break;

				// Custom
				case 'column-author_name' :
					add_filter( 'posts_where', array( $this, 'filter_by_author_name' ) );
					break;

				case 'column-before_moretag' :
					add_filter( 'posts_where', array( $this, 'filter_by_before_moretag' ) );
					break;

				case 'column-comment_count' :
					add_filter( 'posts_where', array( $this, 'filter_by_comment_count' ) );
					break;

				case 'column-comment_status' :
					add_filter( 'posts_where', array( $this, 'filter_by_comment_status' ) );
					break;

				case 'column-excerpt' :
					add_filter( 'posts_where', array( $this, 'filter_by_excerpt' ) );
					break;

				case 'column-featured_image' :
					// check for keys that dont exist
					if ( 'cpac_empty' == $value ) {
						$meta_query_compare = 'NOT EXISTS';
					}

					$vars['meta_query'][] = array(
						'key'		=> '_thumbnail_id',
						'value' 	=> $meta_value,
						'compare'	=> $meta_query_compare
					);
					break;

				case 'column-last_modified_author' :
					$vars['meta_query'][] = array(
						'key'		=> '_edit_last',
						'value' 	=> $meta_value,
						'compare'	=> $meta_query_compare
					);
					break;

				case 'column-page_template' :
					$vars['meta_query'][] = array(
						'key'		=> '_wp_page_template',
						'value' 	=> $meta_value,
						'compare'	=> $meta_query_compare
					);
					break;

				case 'column-ping_status' :
					add_filter( 'posts_where', array( $this, 'filter_by_ping_status' ) );
					break;

				case 'column-post_formats' :
					$vars['tax_query'][] = array(
						'taxonomy'	=> 'post_format',
						'field'		=> 'slug',
						'terms'		=> $value
					);
					break;

				case 'column-roles' :
					$user_ids = get_users( array( 'role' => $value, 'fields' => 'id' ));
					$vars['author'] = implode( ',', $user_ids );
					break;

				case 'column-sticky' :
					add_filter( 'posts_where', array( $this, 'filter_by_sticky' ) );
					break;

				case 'column-status' :
					$vars['post_status'] = $value;
					break;

				case 'column-taxonomy' :
					$vars['tax_query'] = $this->get_taxonomy_tax_query( $value, $column->options->taxonomy, $vars );
					break;

				// Custom Fields
				case 'column-meta' :
					$vars['meta_query'][] = array(
						'key'		=> $column->options->field,
						'value' 	=> $meta_value,
						'compare'	=> $meta_query_compare
					);
					break;

				// ACF
				case 'column-acf_field' :
					if ( method_exists( $column, 'get_field' ) && ( $acf_field_obj = $column->get_field() ) ) {
						$vars['meta_query'][] = array(
							'key'		=> $acf_field_obj['name'],
							'value' 	=> $meta_value,
							'compare'	=> $meta_query_compare
						);
					}
					break;

				// WooCommerce
				case 'product_cat' :
					$vars['tax_query'][] = array(
						'taxonomy'	=> 'product_cat',
						'field'		=> 'slug',
						'terms'		=> $value
					);
					break;

				case 'product_tag' :
					$vars['tax_query'][] = array(
						'taxonomy'	=> 'product_tag',
						'field'		=> 'slug',
						'terms'		=> $value
					);
					break;

				case 'column-wc-featured' :
					$vars['meta_query'][] = array(
						'key'		=> '_featured',
						'value' 	=> $meta_value,
						'compare'	=> $meta_query_compare
					);
					break;

				case 'column-wc-visibility' :
					$vars['meta_query'][] = array(
						'key'		=> '_visibility',
						'value' 	=> $meta_value,
						'compare'	=> $meta_query_compare
					);
					break;

				case 'column-wc-free_shipping':
					$vars['meta_query'][] = array(
						'key' => 'free_shipping',
						'value' => $meta_value
					);
					break;

				case 'column-wc-shipping_class':
					$vars['tax_query'] = $this->get_taxonomy_tax_query( $value, 'product_shipping_class', $vars );
					break;


			endswitch;

		}

		return $vars;
	}

	/**
	 * Add filtering dropdown
	 *
	 * @since 1.0
	 * @todo: Add support for customfield values longer then 30 characters.
	 */
	public function add_filtering_dropdown() {

		global $post_type_object, $pagenow;

		if ( $this->storage_model->page . '.php' !== $pagenow ) {
			return;
		}

		if ( ! empty( $post_type_object ) && $post_type_object->name !== $this->storage_model->post_type  ) {
			return;
		}

		foreach ( $this->storage_model->columns as $column ) {

			if ( ! $column->properties->is_filterable || 'on' != $column->options->filter ) {
				continue;
			}

			$options = array();
			$empty_option = false;

			// Use cache
			$cache = $this->storage_model->get_cache( 'filtering', $column->properties->name );

			$order = 'ASC';

			if ( $cache && $this->storage_model->is_cache_enabled() ) {
				$options  		= $cache['options'];
				$empty_option 	= $cache['empty_option'];
			}

			// no caching, go fetch :)
			else {

				switch ( $column->properties->type ) :

					// Default
					case 'tags' :
						$empty_option = true;
						$terms_args = apply_filters( 'cac/addon/filtering/taxonomy/terms_args', array() );
						$options = $this->apply_indenting_markup( get_terms( 'post_tag', $terms_args ) );
						break;

					// Custom
					case 'column-sticky' :
						$options = array(
							0 => __( 'Not sticky', 'cpac' ),
							1 => __( 'Sticky', 'cpac' ),
						);
						break;

					case 'column-roles' :
						global $wp_roles;
						foreach( $wp_roles->role_names as $role => $name ) {
							$options[ $role ] = $name;
						}
						break;

					case 'column-page_template' :
						if ( $values = $this->get_values_by_meta_key( '_wp_page_template' ) ) {
							foreach ( $values as $value ) {
								$page_template = $value[0];
								if ( $label = array_search( $page_template, get_page_templates() ) ) {
									$page_template = $label;
								}
								$options[ $value[0] ] = $page_template;
							}
						}
						break;

					case 'column-ping_status' :
						if ( $values = $this->get_post_fields( 'ping_status' ) ) {
							foreach ( $values as $value ) {
								$options[ $value ] = $value;
							}
						}
						break;

					case 'column-post_formats' :
						$options = $this->apply_indenting_markup( $this->indent( get_terms( 'post_format', array( 'hide_empty' => false ) ), 0, 'parent', 'term_id' ) );
						break;

					case 'column-excerpt' :
						$options = array(
							0 => __( 'Empty', 'cpac' ),
							1 => __( 'Has excerpt', 'cpac' ),
						);
						break;

					case 'column-comment_count' :
						$empty_option = true;
						if ( $values = $this->get_post_fields( 'comment_count' ) ) {
							foreach ( $values as $value ) {
								$options[ $value ] = $value;
							}
						}
						break;

					case 'column-before_moretag' :
						$options = array(
							0 => __( 'Empty', 'cpac' ),
							1 => __( 'Has more tag', 'cpac' ),
						);
						break;

					case 'column-author_name' :
						if ( $values = $this->get_post_fields( 'post_author' ) ) {
							foreach ( $values as $value ) {
								$options[ $value ] = $column->get_display_name( $value );
							}
						}
						break;

					case 'column-featured_image' :
						$empty_option = true;
						if ( $values = $this->get_values_by_meta_key( '_thumbnail_id' ) ) {
							foreach ( $values as $value ) {
								$options[ $value[0] ] = $value[0];
							}
						}
						break;

					case 'column-comment_status' :
						if ( $values = $this->get_post_fields( 'comment_status' ) ) {
							foreach ( $values as $value ) {
								$options[ $value ] = $value;
							}
						}
						break;

					case 'column-status' :
						if ( $values = $this->get_post_fields( 'post_status' ) ) {
							foreach ( $values as $value ) {
								if ( 'auto-draft' != $value ) {
									$options[ $value ] = $value;
								}
							}
						}
						break;

					case 'column-taxonomy' :
						if ( $column->options->taxonomy ) {
							$empty_option = true;
							$order = false; // do not sort, messes up the indenting
							$terms_args = apply_filters( 'cac/addon/filtering/taxonomy/terms_args', array() );
							$options = $this->apply_indenting_markup( $this->indent( get_terms( $column->options->taxonomy, $terms_args ), 0, 'parent', 'term_id' ) );
						}
						break;

					case 'column-last_modified_author' :
						if ( $values = $this->get_values_by_meta_key( '_edit_last' ) ) {
							foreach ( $values as $value ) {
								$options[ $value[0] ] = $column->get_display_name( $value[0] );
							}
						}
						break;

					// Custom Field column
					case 'column-meta' :
						if ( $_options = $this->get_meta_options( $column ) ) {
							$empty_option = $_options['empty_option'];
							$options = $_options['options'];
						}
						break;

					// ACF column
					case 'column-acf_field' :
						if ( $_options = $this->get_acf_options( $column ) ) {
							$empty_option = $_options['empty_option'];
							$options = $_options['options'];
						}
						break;


					// WooCommerce columns
					case 'column-wc-featured':
						$options = array(
							'no' => __( 'No' ),
							'yes' => __( 'Yes' )
						);
						break;

					case 'column-wc-visibility':
						$options = $column->get_visibility_options();
						break;

					case 'column-wc-free_shipping':
						$options = array(
							'no' => __( 'No' ),
							'yes' => __( 'Yes' )
						);
						break;

					case 'column-wc-shipping_class':
						$empty_option = true;
						$order = false; // do not sort, messes up the indenting
						$terms_args = apply_filters( 'cac/addon/filtering/taxonomy/terms_args', array() );
						$options = $this->apply_indenting_markup( $this->indent( get_terms( 'product_shipping_class', $terms_args ), 0, 'parent', 'term_id' ) );
						break;

				endswitch;

				// sort the options
				if ( 'ASC' == $order ) {
					asort( $options );
				}
				if ( 'DESC' == $order ) {
					arsort( $options );
				}

				// update cache
				$this->storage_model->set_cache( 'filtering', $column->properties->name, array( 'options' => $options, 'empty_option' =>  $empty_option ) );
			}

			if ( ! $options && ! $empty_option ) {
				continue;
			}

			$this->dropdown( $column, $options, $empty_option );
		}
	}
}