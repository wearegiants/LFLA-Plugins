(function($) {

	// vars
	var $iframe = $("#mc4wp-css-preview");
	var $form, $labels, $fields, $buttons, $choiceFields, $notices;

	// functions
	function init()
	{
		$form = $iframe.contents().find('.mc4wp-form');
		$labels = $form.find('label');
		$fields = $form.find('input[type="text"], input[type="email"], input[type="url"], input[type="number"], input[type="date"], select, textarea').not('textarea[name="_mc4wp_required_but_not_really"]');
		$choiceFields = $form.find('input[type="radio"], input[type="checkbox"]');
		$buttons = $form.find('input[type="submit"], input[type="button"], button');
		$notices = $form.find('.mc4wp-alert');
	}

	function baseCSS() {
		if( $form === undefined ) {
			init();
		}

		$form.css('display', 'block');

		$labels.css({
			"margin-bottom": "6px",
			"box-sizing": "border-box",
			"vertical-align": "top"
		});

		$fields.css({
			"padding": '6px 12px',
			"margin-bottom": "6px",
			"box-sizing": "border-box",
			"vertical-align": "top"
		});

		$choiceFields.css({
			'display': 'inline-block',
			'margin-right': '6px'
		});

		$buttons.css({
			"text-align": "center",
			"cursor": "pointer",
			"box-sizing": "border-box",
			"padding": "6px 12px",
			"text-shadow": "none",
			"box-sizing": "border-box",
			"line-height": "normal",
			"vertical-align": "top"
		});
	}

	function clearCSS()
	{
		if( $form === undefined ) {
			init();
		}

		$form.removeAttr('style');
		$labels.removeAttr('style');
		$fields.removeAttr('style');
		$buttons.removeAttr('style');
	}

	function maybeSet(option, $element, styleRules, type, callback) {
		var $option = $("#" + option);

		if($option.length == 0) {
			return false;
		}

		if( $option.is(":input") && $option.val() === '' ) {
			return false;
		}

		var value;

		switch( type ) {

			case 'int':
				value = getIntValue( $option ) + 'px';
			break;

			case 'radio':
				value = getRadioValue( $option );
			break;

			case 'color':
				value = getColor( $option );
			break;

			case 'default':
			case undefined:
				value = $option.val();
			break;

			case 'length':
				value = "" + $option.val();
				
				var lastChar = value.charAt( value.length - 1 );
				if( lastChar !== 'x' && lastChar !== '%' ) {
					value += 'px';
				}

			break;
		}

		if(typeof styleRules == "object") {
			for( var i = 0; i < styleRules.length; i++ ) {
				$element.css( styleRules[i], value );
			}
		} else {
			$element.css( styleRules, value );
		}

		return true;		
	}

	function applyCSS()
	{

		clearCSS();
		baseCSS();

		/* form container */
		maybeSet('form-width', $form, 'width', 'length');
		maybeSet('form-text-align', $form, 'text-align');
		maybeSet('form-font-size', $form, 'font-size', 'int');
		maybeSet('form-font-color', $form, 'color', 'color');
		maybeSet('form-background-color', $form, 'background', 'color');
		maybeSet('form-border-color', $form, 'border-color', 'color');
		maybeSet('form-border-width', $form, 'border-width', 'int');
		maybeSet('form-vertical-padding', $form, ['padding-top', 'padding-bottom'], 'int');
		maybeSet('form-horizontal-padding', $form, ['padding-left', 'padding-right'], 'int');

		// add border style if border-width is set and bigger than 0
		if(getIntValue($("#form-border-width"), false)) {
			$form.css('border-style', 'solid');
		}

		/* labels */
		maybeSet('labels-font-color', $labels, 'color', 'color');
		maybeSet('labels-font-size', $labels, 'font-size', 'int');
		maybeSet('labels-display', $labels, 'display', 'radio');
		maybeSet('labels-width', $labels, 'width', 'length');
		
		// only set label text style if it is set
		var labelsTextStyle = $("#labels-font-style").val();
		if(labelsTextStyle.length > 0) {
			$labels.css({
				"font-weight": (labelsTextStyle == 'bold' || labelsTextStyle == 'bolditalic') ? 'bold' : 'normal',
				"font-style": (labelsTextStyle == 'italic' || labelsTextStyle == 'bolditalic') ? 'italic' : 'normal',
			});
		}

		/* fields */
		maybeSet('fields-border-width', $fields, 'border-width', 'int');
		maybeSet('fields-border-color', $fields, 'border-color', 'color');
		maybeSet('fields-display', $fields, 'display', 'radio');
		maybeSet('fields-width', $fields, 'width', 'length');
		maybeSet('fields-height', $fields, 'height', 'length');

		/* buttons */
		maybeSet('buttons-border-width', $buttons, 'border-width', 'int');
		maybeSet('buttons-border-color', $buttons, 'border-color', 'color');
		maybeSet('buttons-width', $buttons, 'width', 'length');
		maybeSet('buttons-background-color', $buttons, 'background', 'color');
		maybeSet('buttons-height', $buttons, 'height', 'length');
		maybeSet('buttons-font-color', $buttons, 'color', 'color');
		maybeSet('buttons-font-size', $buttons, 'font-size', 'int');
		maybeSet('buttons-display', $buttons, 'display', 'radio');

		$buttons.hover(function() {
			maybeSet('buttons-hover-background-color', $buttons, 'background', 'color');
			maybeSet('buttons-hover-font-color', $buttons, 'color', 'color');
			maybeSet('buttons-hover-border-color', $buttons, 'border-color', 'color');
		}, function () {
			maybeSet('buttons-background-color', $buttons, 'background', 'color');
			maybeSet('buttons-font-color', $buttons, 'color', 'color');
			maybeSet('buttons-border-color', $buttons, 'border-color', 'color');
		});

		// add background reset only if custom background color has been set
		if($("#buttons-background-color").wpColorPicker('color') && $("#buttons-background-color").wpColorPicker('color').length > 0) {
			$buttons.css({
				"background-image": "none",
				"filter": "none",
			});
		}	

		// add border style if border-width is set and bigger than 0
		if($("#buttons-border-width").val().length > 0 && $("#buttons-border-width").val() > 0) {
			$buttons.css('border-style', 'solid');
		}

		/* notices */
		$notices.filter('.mc4wp-success').css({
			'color': getColor($("#messages-font-color-success"))
		})
		$notices.filter(".mc4wp-error").css({
			'color': getColor($("#messages-font-color-error"))
		});

		/* custom css */
		$iframe.contents().find('#custom-css').html($("#mc4wp-css-textarea").val());

	}

	function getRadioValue($parentEl, retval)
	{
		var value = $parentEl.find(":input:checked").val();
		if(value) {
			return value;
		} else {
			return (retval !== undefined) ? retval : '';
		}
	}

	function getIntValue($el, retval) 
	{
		if($el.val()) {
			return parseInt($el.val());
		} else {
			return (retval !== undefined) ? retval : 0;
		}
	}

	function getColor($el, retval)
	{
		if($el.val().length > 0) {
			return $el.wpColorPicker('color');
		} else {
			return (retval !== undefined) ? retval : '';
		}
	}


	// events
	$("#select_form_id").change( function() {
		$(this).parents('form').submit();
	});

	$('input.color-field').wpColorPicker({ change: function() { applyCSS() }, clear: function() { applyCSS(); } });

	$("#mc4wp-css-form :input").change(applyCSS).keydown(function() {
		poll(applyCSS, 1000);
	});

	$('#mc4wp-css-form input[type="radio"]').mousedown(function(e){
	  var $self = $(this);
	  if( $self.is(':checked') ){
	    var uncheck = function(){
	      setTimeout(function(){$self.removeAttr('checked');},0);
	    };
	    var unbind = function(){
	      $self.unbind('mouseup',up);
	    };
	    var up = function(){
	      uncheck();
	      unbind();
	    };
	    $self.bind('mouseup',up);
	    $self.one('mouseout', unbind);
	  }
	});

	$( ".mc4wp-accordion" ).accordion({ 
		header: "h4", 
		collapsible: true,
		active: false

	});

	$iframe.load(function() {
		init();
		applyCSS();
	});

	$("#setting-error-mc4wp-css .mc4wp-show-css").click(function() {

		$generatedCss = $("#mc4wp_generated_css").toggle();

		if( $generatedCss.is(":visible")) {
			$(this).text("Hide generated CSS");
		} else {
			$(this).text("Show generated CSS");
		}
	});


	// helper functions
	var poll = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

})(jQuery);