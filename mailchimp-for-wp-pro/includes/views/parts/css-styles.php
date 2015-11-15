<?php
if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

?>


/*
	Styles for form #<?php echo $form_id; ?>
*/

<?php echo $selector_prefix . $form_selector; ?>,
<?php echo $selector_prefix . $form_selector; ?> label,
<?php echo $selector_prefix . $form_selector; ?> input,
<?php echo $selector_prefix . $form_selector; ?> textarea,
<?php echo $selector_prefix . $form_selector; ?> select,
<?php echo $selector_prefix . $form_selector; ?> button {
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
}
/* form container */
<?php echo $selector_prefix . $form_selector; ?> {
    display: block;
    border: 0;
    border-style: solid;
<?php
if( ! empty($form_border_color)) { echo "\tborder-color: {$form_border_color} !important;\n"; } else { echo "\tborder-color: transparent;\n"; }
if( ! empty($form_border_width)) { echo "\tborder-width: {$form_border_width}px;\n";  }
if( ! empty($form_horizontal_padding)) { echo "\tpadding-left: {$form_horizontal_padding}px; padding-right: {$form_horizontal_padding}px;\n"; }
if( ! empty($form_vertical_padding)) {  echo "\tpadding-top: {$form_vertical_padding}px; padding-bottom: {$form_vertical_padding}px;\n"; }
if( ! empty($form_background_color)) { echo "\tbackground: {$form_background_color} !important;\n"; }
if( ! empty($form_font_color)) { echo "\tcolor: {$form_font_color} !important;\n"; }
if( ! empty($form_text_align)) { echo "\ttext-align: {$form_text_align};\n"; }
if( ! empty($form_width)) { echo "\twidth: 100%; max-width: {$form_width} !important;\n"; }
?>
}

/* labels */
<?php echo $selector_prefix . $form_selector; ?> label {
    vertical-align: top;
    margin-bottom: 6px;
<?php
if( ! empty( $labels_width ) ) { echo "\twidth: {$labels_width};\n"; }
if( ! empty( $labels_font_color ) ) { echo "\tcolor: {$labels_font_color};\n"; }
if( ! empty( $labels_font_style ) ) {
	if( $labels_font_style === 'italic' || $labels_font_style === 'bolditalic') {
		echo "\tfont-style: italic;\n";
	} else {
		echo "\tfont-style: normal;\n";
	}

	if( $labels_font_style === 'bold' || $labels_font_style === 'bolditalic') {
		echo "\tfont-weight: bold;\n";
	} else {
		echo "\tfont-weight: normal;\n";
	}
}
if( ! empty($labels_font_size)) { echo "\tfont-size: {$labels_font_size}px;\n"; }
if( ! empty($labels_display)) { echo "\tdisplay: {$labels_display};\n"; }
if( ! empty($labels_vertical_margin)) { echo "\tmargin-top: {$labels_vertical_margin}px; margin-bottom: {$labels_vertical_margin}px;\n"; }
if( ! empty($labels_horizontal_margin)) { echo "\tmargin-left: {$labels_horizontal_margin}px; margin-right: {$labels_horizontal_margin}px;\n"; }
?>
}

/* fields */
<?php echo $selector_prefix . $form_selector; ?> input[type="text"],
<?php echo $selector_prefix . $form_selector; ?> input[type="email"],
<?php echo $selector_prefix . $form_selector; ?> input[type="url"],
<?php echo $selector_prefix . $form_selector; ?> input[type="tel"],
<?php echo $selector_prefix . $form_selector; ?> input[type="number"],
<?php echo $selector_prefix . $form_selector; ?> input[type="date"],
<?php echo $selector_prefix . $form_selector; ?> select,
<?php echo $selector_prefix . $form_selector; ?> textarea {
    vertical-align: top;
    padding:6px 12px;
    margin-bottom: 6px;
<?php
if( ! empty($fields_width)) { echo "\twidth: {$fields_width};\n"; }
if( ! empty($fields_height)) { echo "\tline-height: ". ($fields_height - 12) . "px; height: {$fields_height}px;\n"; }
if( ! empty($fields_border_color)) { echo "\tborder-color: {$fields_border_color} !important;\n"; }
if( ! empty($fields_border_width)) { echo "\tborder-width: {$fields_border_width}px; border-style:solid;\n"; }
if( ! empty($fields_display)) { echo "\tdisplay: {$fields_display};\n"; }
?>
}

/* choice fields */
<?php echo $selector_prefix . $form_selector; ?> input[type="radio"],
<?php echo $selector_prefix . $form_selector; ?> input[type="checkbox"] {
    margin-right: 6px;
    display: inline-block;
}

/* buttons */
<?php echo $selector_prefix . $form_selector; ?> input[type="submit"],
<?php echo $selector_prefix . $form_selector; ?> button,
<?php echo $selector_prefix . $form_selector; ?> input[type="button"],
<?php echo $selector_prefix . $form_selector; ?> input[type="reset"] {
    vertical-align: top;
    text-shadow:none;
    padding:6px 12px;
    cursor: pointer;
    text-align:center;
    border:0;
    border-style: solid;
    line-height: normal;
<?php
if( ! empty($buttons_background_color)) { echo "\tbackground:none; filter: none; background: {$buttons_background_color} !important;\n"; }
if( ! empty($buttons_font_color)) { echo "\tcolor: {$buttons_font_color} !important;\n"; }
if( ! empty($buttons_font_size)) { echo "\tfont-size: {$buttons_font_size}px;\n"; }
if( ! empty($buttons_border_color)) { echo "\tborder-color: {$buttons_border_color} !important;\n"; }
if( ! empty($buttons_border_width)) { echo "\tborder-width: {$buttons_border_width}px;\n"; }
if( ! empty($buttons_width)) { echo "\twidth: {$buttons_width};\n"; }
if( ! empty($buttons_height)) { echo "\theight: {$buttons_height}px;\n"; }
if( ! empty($buttons_display)) { echo "\tdisplay: inline-block !important;\n"; }
?>
}

<?php echo $selector_prefix . $form_selector; ?> input[type="submit"]:hover,
<?php echo $selector_prefix . $form_selector; ?> button:hover,
<?php echo $selector_prefix . $form_selector; ?> input[type="button"]:hover,
<?php echo $selector_prefix . $form_selector; ?> input[type="reset"]:hover {
<?php
if( ! empty($buttons_hover_background_color)) { echo "\tbackground:none; filter: none; background: {$buttons_hover_background_color} !important;\n"; }
if( ! empty($buttons_hover_font_color)) { echo "\tcolor: {$buttons_hover_font_color} !important;\n"; }
if( ! empty($buttons_hover_border_color)) { echo "\tborder-color: {$buttons_hover_border_color} !important;\n"; }
?>
}

/* messages */
<?php echo $selector_prefix . $form_selector; ?> .mc4wp-success{
<?php
if( ! empty($messages_font_color_success)) { echo "\tcolor: $messages_font_color_success;\n"; }
?>
}
<?php echo $selector_prefix . $form_selector; ?> .mc4wp-error{
<?php
if( ! empty($messages_font_color_error)) { echo "\tcolor: $messages_font_color_error;\n"; }
?>
}

<?php echo $manual; ?>
