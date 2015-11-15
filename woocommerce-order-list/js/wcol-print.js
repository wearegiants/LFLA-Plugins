jQuery(document).ready(function($) {		
	// Click bulk actions
	$("#doaction, #doaction2").click( function (event) {
		var actionselected = $(this).attr("id").substr(2);

		if ( $('select[name="' + actionselected + '"]').val() == "order-list") {
			event.preventDefault();

			// Get array of checked orders (order_ids)
			var checked = [];
			$('tbody th.check-column input[type="checkbox"]:checked').each(
				function() {
					checked.push($(this).val());
				}
			);
			
			//implode order_ids array
			var order_ids=checked.join('x');
			var iframe = null;
			var url = 'edit.php?&action=wcol&order_ids='+order_ids;

			var unsupported_browser = ( $.browser.opera || ($.browser.msie && parseInt($.browser.version) > 9) );
			var preview = wcol_print.preview;

			// open the link in a new tab when preview enabled or iframe printing is not supported			
			if( unsupported_browser || preview == 'true') {
				window.open(url, '_blank');
				return;
			}
			
			// show spinner
			$('#print-order-list-box .loading').show();
			$(this).parent().find('.loading').show();

			// print the page with a hidden preview window (iframe)
			if(!$('#printPreview')[0]) {
				// create a new iframe
				var iframe = '<iframe id="printPreview" name="printPreview" src=' + url + ' style="position:absolute;top:-9999px;left:-9999px;border:0px;overfow:none; z-index:-1"></iframe>';
				$('body').append(iframe);

				// print when the iframe is loaded
				$('#printPreview').on('load',function() {  
					$('#print-order-list-box .loading').hide();
					$(this).parent().find('.loading').hide();
					frames['printPreview'].focus();
					frames['printPreview'].print();
				});
			} else {
				// change the iframe src when the iframe is already appended
				$('#printPreview').attr('src', url);
			}
		}			
	});
});