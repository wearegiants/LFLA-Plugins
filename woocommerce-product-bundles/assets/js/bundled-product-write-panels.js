jQuery( function($) {

	// bundle type move stock msg up
	$( '.bundle_stock_msg' ).insertBefore( '._manage_stock_field' );

	// bundle type specific options
	$( 'body' ).on( 'woocommerce-product-type-change', function( event, select_val, select ) {

		if ( select_val == 'bundle' ) {

			$( 'input#_downloadable' ).prop( 'checked', false );
			$( 'input#_virtual' ).removeAttr( 'checked' );

			$( '.show_if_simple' ).show();
			$( '.show_if_external' ).hide();
			$( '.show_if_bundle' ).show();

			$( 'input#_downloadable' ).closest( '.show_if_simple' ).hide();
			$( 'input#_virtual').closest('.show_if_simple' ).hide();

			$( 'input#_manage_stock' ).change();
			$( 'input#_per_product_pricing_active' ).change();
			$( 'input#_per_product_shipping_active' ).change();

			$( '#_nyp' ).change();
		} else {

			$( '.show_if_bundle' ).hide();
		}

	} );

	$( 'select#product-type' ).change();

	// non-bundled shipping
	$( 'input#_per_product_shipping_active' ).change( function() {

		if ( $( 'select#product-type' ).val() == 'bundle' ) {

			if ( $( 'input#_per_product_shipping_active' ).is( ':checked' ) ) {
				$( '.show_if_virtual' ).show();
				$( '.hide_if_virtual' ).hide();
				if ( $( '.shipping_tab' ).hasClass( 'active' ) )
					$( 'ul.product_data_tabs li:visible' ).eq(0).find('a').click();
			} else {
				$( '.show_if_virtual' ).hide();
				$( '.hide_if_virtual' ).show();
			}
		}

	} ).change();

	// show options if pricing is static
	$( 'input#_per_product_pricing_active' ).change( function() {

		if ( $( 'select#product-type' ).val() == 'bundle' ) {

			if ( $(this).is( ':checked' ) ) {

		        $( '#_regular_price' ).val('');
		        $( '#_sale_price' ).val('');

				$( '._tax_class_field' ).closest( '.options_group' ).hide();
				$('.pricing').hide();

				$( '#bundled_product_data .wc-bundled-item .item-data .discount input.bundle_discount' ).each( function() {
					$(this).attr( 'disabled', false );
				} );

			} else {

				$( '._tax_class_field' ).closest( '.options_group' ).show();

				if ( ! $( '#_nyp' ).is( ':checked' ) )
					$( '.pricing' ).show();

				$( '#bundled_product_data .wc-bundled-item .item-data .discount input.bundle_discount' ).each( function() {
					$(this).attr( 'disabled', 'disabled' );
				} );
			}
		}

	} ).change();

	// nyp support
	$( '#_nyp' ).change( function() {

		if ( $( 'select#product-type' ).val() == 'bundle' ) {

			if ( $( '#_nyp' ).is( ':checked' ) ) {
				$( 'input#_per_product_pricing_active' ).prop( 'checked', false );
				$( '.bundle_pricing' ).hide();
			} else {
				$( '.bundle_pricing' ).show();
			}

			$( 'input#_per_product_pricing_active' ).change();
		}

	} ).change();

	init_wc_bundle_metaboxes();

	function bundle_row_indexes() {
		$( '.wc-bundled-items .wc-bundled-item' ).each(function( index, el ){
			$( '.bundled_item_position', el ).val( parseInt( $(el).index( '.wc-bundled-items .wc-bundled-item' ) ) );
		} );
	}

	function init_wc_bundle_metaboxes() {

		// variation filtering options
		$( '.filter_variations input' ).change( function() {
			if ( $(this).is( ':checked' ) )
				$(this).closest( 'div.item-data' ).find( 'div.bundle_variation_filters' ).show();
			else
				$(this).closest( 'div.item-data' ).find( 'div.bundle_variation_filters' ).hide();
		} ).change();

		// selection defaults options
		$( '.override_defaults input' ).change( function() {
			if ( $(this).is( ':checked' ) )
				$(this).closest( 'div.item-data' ).find( 'div.bundle_selection_defaults' ).show();
			else
				$(this).closest( 'div.item-data' ).find( 'div.bundle_selection_defaults' ).hide();
		} ).change();

		// custom title options
		$( '.override_title > p input' ).change( function() {
			if ( $(this).is( ':checked' ) )
				$(this).closest( 'div.item-data' ).find( 'div.custom_title' ).show();
			else
				$(this).closest( 'div.item-data' ).find( 'div.custom_title' ).hide();
		} ).change();

		// custom description options
		$( '.override_description > p input' ).change( function() {
			if ( $(this).is( ':checked' ) )
				$(this).closest( 'div.item-data' ).find( 'div.custom_description' ).show();
			else
				$(this).closest( 'div.item-data' ).find( 'div.custom_description' ).hide();
		} ).change();

		// visibility
		$( '.item_visibility select' ).change( function() {

			if ( $(this).val() == 'visible' ) {
				$(this).closest( 'div.item-data' ).find( '.override_title, .override_description, .images' ).show();
				$(this).closest( 'div.item-data' ).find( '.override_title > p input' ).change();
				$(this).closest( 'div.item-data' ).find( '.override_description > p input' ).change();
			} else {
				$(this).closest( 'div.item-data' ).find( '.override_title, .custom_title, .override_description, .custom_description, .images' ).hide();
			}

		} ).change();

		// Initial order
		var bundled_items = $( '.wc-bundled-items' ).find( '.wc-bundled-item' ).get();

		bundled_items.sort( function( a, b ) {
		   var compA = parseInt( $(a).attr( 'rel' ) );
		   var compB = parseInt( $(b).attr( 'rel' ) );
		   return ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : 0;
		} );

		$(bundled_items).each( function( idx, itm ) {
			$( '.wc-bundled-items' ).append( itm );
		} );

		// Item ordering
		$( '.wc-bundled-items' ).sortable( {
			items:'.wc-bundled-item',
			cursor:'move',
			axis:'y',
			handle: 'h3',
			scrollSensitivity:40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'wc-metabox-sortable-placeholder',
			start:function(event,ui){
				ui.item.css( 'background-color','#f6f6f6' );
			},
			stop:function(event,ui){
				ui.item.removeAttr( 'style' );
				bundle_row_indexes();
			}
		} );

		$( '#bundled_product_data .expand_all' ).click( function() {
			$(this).closest( '.wc-metaboxes-wrapper' ).find( '.wc-metabox > .item-data' ).show();
			return false;
		} );

		$( '#bundled_product_data .close_all' ).click( function() {
			$(this).closest( '.wc-metaboxes-wrapper' ).find( '.wc-metabox > .item-data').hide();
			return false;
		} );

	}

	// Save bundle data and update configuration options via ajax
	$( '.save_bundle' ).on( 'click', function() {

		$( '#bundled_product_data' ).block( { message: null, overlayCSS: { background: '#fff url(' + woocommerce_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } } );
		$( '.wc-bundled-items .wc-bundled-item' ).find('*').off();

		var data = {
			post_id: 		woocommerce_admin_meta_boxes.post_id,
			data:			$( '#bundled_product_data' ).find( 'input, select, textarea' ).serialize(),
			action: 		'woocommerce_product_bundles_save',
			security: 		$( '#wc_save_bundle_nonce' ).val()
		};

		$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( post_response ) {

			var this_page = window.location.toString();

			this_page = this_page.replace( 'post-new.php?', 'post.php?post=' + woocommerce_admin_meta_boxes.post_id + '&action=edit&' );

			$.get( this_page, function( response ) {

				$( '.wc-bundle-metaboxes-wrapper' ).html( $(response).find( '#wc-bundle-metaboxes-wrapper-inner' ).parent().html() );
				$( '.wc-bundled_products .bundled_products_selector' ).html( $(response).find( 'select#bundled_ids' ).parent().html() );

				init_wc_bundle_metaboxes();

				$( '.wc-bundle-metaboxes-wrapper' ).find( '.wc-metabox-content' ).hide();
				$( 'input#_per_product_pricing_active' ).change();

				$( '.wc-bundle-metaboxes-wrapper .tips, .wc-bundle-metaboxes-wrapper .help_tip' ).tipTip( {
			    	'attribute' : 'data-tip',
			    	'fadeIn' : 50,
			    	'fadeOut' : 50,
			    	'delay' : 200
			    } );

			    $( '.wc-bundle-metaboxes-wrapper select.chosen_select' ).chosen();


			    $( '.wc-bundled_products select#bundled_ids' ).ajaxChosen( {
					method: 		'GET',
					url: 			woocommerce_admin_meta_boxes.ajax_url,
					dataType: 		'json',
					afterTypeDelay: 100,
					data: 			{
						action: 		'woocommerce_json_search_products',
						security: 		woocommerce_admin_meta_boxes.search_products_nonce
				    }
				}, function ( data ) {

					var terms = {};

				    $.each( data, function ( i, val ) {
				        terms[i] = val;
				    } );

				    return terms;
				} );

				if ( post_response.length > 0 )
					alert( post_response.join( '\n\n' ) );

				$( '#bundled_product_data' ).unblock();
			} );

		}, 'json' );

	} );

} );
