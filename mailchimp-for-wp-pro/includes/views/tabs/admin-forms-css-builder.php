<?php
if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
?>
<div class="mc4wp-column" style="width:55%">

	<p><?php _e( 'Use the fields below to create custom styling rules for your forms.', 'mailchimp-for-wp' ); ?> </p>

    <form action="" method="get">
        <table class="form-table">
            <tr valign="top">
				<th style="width: 250px"><label><?php _e( 'Select form to build styles for:', 'mailchimp-for-wp' ); ?></label></th>
                <td>
					<?php if( count( $forms ) > 0 ) { ?>
                        <select name="form_id" id="select_form_id" class="widefat">
							<?php foreach( $forms as $form ) {
								$title = strlen( $form->post_title ) > 40 ? substr( $form->post_title, 0, 40 ) . '..' : $form->post_title;
								?>
								<option value="<?php echo $form->ID; ?>" <?php selected( $form->ID, $form_id ); ?>><?php echo "Form #{$form->ID}: {$title}"; ?></option>
							<?php } ?>
                        </select>
					<?php } else { ?>
						<p><?php _e( 'Create at least one form first.', 'mailchimp-for-wp' ); ?></p>
					<?php } ?>
                </td>
            </tr>
        </table>

        <input type="hidden" name="page" value="mailchimp-for-wp-form-settings" />
        <input type="hidden" name="tab" value="css-builder" />
        <input type="submit" value="Apply" style="display: none;" />
    </form>

    <form action="options.php" method="post">

	<?php settings_fields( 'mc4wp_form_styles_settings' ); ?>

	<noscript><?php _e( 'You need to have JavaScript enabled to see a preview of your form.', 'mailchimp-for-wp' ); ?></noscript>

    <div class="mc4wp-accordion" id="mc4wp-css-form">
        <h4>Form container style</h4>
        <div>
            <table class="form-table">
                <tr valign="top">
                    <th>Form width<br /><span class="help">px or %</span></th>
					<td class="nowrap"><input id="form-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_width]" type="text" value="<?php echo esc_attr( $styles['form_width'] ); ?>" /></td>
                    <th></th>
                    <td class="nowrap"></td>
                </tr>
                <tr valign="top">
                    <th width="1">Background color</th>
					<td class="nowrap"><input id="form-background-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_background_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['form_background_color'] ); ?>" /></td>
                    <th width="1">Padding</th>
                    <td>
						<label>Horizontal <input id="form-horizontal-padding" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_horizontal_padding]" type="number" class="small-text" max="99" min="0" value="<?php echo esc_attr( $styles['form_horizontal_padding'] ); ?>"  /></label> &nbsp;
						<label>Vertical <input id="form-vertical-padding" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_vertical_padding]" type="number" class="small-text" value="<?php echo esc_attr( $styles['form_vertical_padding'] ); ?>"  /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th width="1">Border color</th>
					<td class="nowrap"><input id="form-border-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_border_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['form_border_color'] ); ?>" /></td>
                    <th width="1">Border width</th>
					<td><input id="form-border-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_border_width]" type="number" class="small-text" max="99" min="0" value="<?php echo esc_attr( $styles['form_border_width'] ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th width="1">Text color</th>
					<td class="nowrap"><input id="form-font-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_font_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['form_font_color'] ); ?>" /></td>
                    <th>Text size</th>
					<td><input id="form-font-size" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_font_size]" type="number" class="small-text" max="99" min="0" value="<?php echo esc_attr( $styles['form_font_size'] ); ?>"  /></td>
                </tr>
                <tr>
                    <th width="1">Text alignment</th>
                    <td>
						<select name="mc4wp_form_styles[form-<?php echo $form_id; ?>][form_text_align]" id="form-text-align">
							<option value="" <?php selected( $styles['form_text_align'], '' ); ?>>Choose alignment</option>
							<option value="left" <?php selected( $styles['form_text_align'], 'left' ); ?>>Left</option>
							<option value="center" <?php selected( $styles['form_text_align'], 'center' ); ?>>Center</option>
							<option value="right" <?php selected( $styles['form_text_align'], 'right' ); ?>>Right</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <h4>Label styles</h4>
        <div>
            <table class="form-table">
                <tr valign="top">
                    <th>Label width<br /><span class="help">px or %</span></th>
					<td class="nowrap"><input id="labels-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_width]" type="text" value="<?php echo esc_attr( $styles['labels_width'] ); ?>" /></td>
                    <th></th>
                    <td class="nowrap"></td>
                </tr>
                <tr valign="top">
                    <th>Text color</th>
					<td class="nowrap"><input id="labels-font-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_font_color]" value="<?php echo esc_attr( $styles['labels_font_color'] ); ?>" type="text" class="color-field" /></td>
                    <th>Text size</th>
					<td><input id="labels-font-size" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_font_size]" type="number" class="small-text" max="99" min="0" value="<?php echo esc_attr( $styles['labels_font_size'] ); ?>"  /></td>
                </tr>
                <tr valign="top">
                    <th>Text style?</th>
                    <td>
						<select id="labels-font-style" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_font_style]">
							<option value="" <?php selected( $styles['labels_font_style'], '' ); ?>>Choose text style..</option>
							<option value="normal" <?php selected( $styles['labels_font_style'], 'normal' ); ?>>Normal</option>
							<option value="bold" <?php selected( $styles['labels_font_style'], 'bold' ); ?>>Bold</option>
							<option value="italic" <?php selected( $styles['labels_font_style'], 'italic' ); ?>>Italic</option>
							<option value="bolditalic" <?php selected( $styles['labels_font_style'], 'bolditalic' ); ?>>Bold & Italic</option>
                        </select>
                    </td>
                    <th>Display inline or on new line?</th>
                    <td id="labels-display">
						<label><input type="radio" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_display]" value="inline-block" <?php checked( $styles['labels_display'], 'inline-block' ); ?> /> Inline</label>
						<label><input type="radio" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][labels_display]" value="block" <?php checked( $styles['labels_display'], 'block' ); ?> /> New line</label>
                    </td>
                </tr>
            </table>
        </div>

        <h4>Field styles</h4>
        <div>
            <table class="form-table">
                <tr valign="top">
                    <th>Field width<br /><span class="help">px or %</span></th>
					<td class="nowrap"><input id="fields-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_width]" type="text" value="<?php echo esc_attr( $styles['fields_width'] ); ?>" /></td>
                    <th>Field height</th>
					<td class="nowrap"><input id="fields-height" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_height]" min="0" type="number" class="small-text" value="<?php echo esc_attr( $styles['fields_height'] ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th>Border color</th>
					<td class="nowrap"><input id="fields-border-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_border_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['fields_border_color'] ); ?>" /></td>
                    <th>Border width</th>
					<td><input id="fields-border-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_border_width]" type="number" class="small-text" max="99" min="0" value="<?php echo esc_attr( $styles['fields_border_width'] ); ?>" /></td>
                </tr>
                <tr>
                    <th>Display inline or on new line?</th>
                    <td id="fields-display">
						<label><input type="radio" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_display]" value="inline-block" <?php checked( $styles['fields_display'], 'inline-block' ); ?> /> Inline</label>
						<label><input type="radio" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][fields_display]" value="block" <?php checked( $styles['fields_display'], 'block' ); ?> /> New line</label>
                    </td>
                </tr>
            </table>
        </div>

        <h4>Button styles</h4>
        <div>
            <table class="form-table">
                <tr valign="top">
                    <th>Button width<br /><span class="help">px or %</span></th>
					<td class="nowrap"><input id="buttons-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_width]" type="text" value="<?php echo esc_attr( $styles['buttons_width'] ); ?>" /></td>
                    <th>Button height</th>
					<td class="nowrap"><input id="buttons-height" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_height]" min="0" type="number" class="small-text" value="<?php echo esc_attr( $styles['buttons_height'] ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th width="1">Background color</th>
					<td class="nowrap"><input id="buttons-background-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_background_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['buttons_background_color'] ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th>Border color</th>
					<td class="nowrap"><input id="buttons-border-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_border_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['buttons_border_color'] ); ?>" /></td>
                    <th>Border width</th>
					<td><input id="buttons-border-width" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_border_width]" type="number" class="small-text" max="99" min="0" value="<?php echo esc_attr( $styles['buttons_border_width'] ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th>Text color</th>
					<td class="nowrap"><input id="buttons-font-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_font_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['buttons_font_color'] ); ?>" /></td>
                    <th>Text size</th>
					<td><input id="buttons-font-size" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_font_size]" type="number" class="small-text" max="99" min="0" value="<?php echo esc_attr( $styles['buttons_font_size'] ); ?>"  /></td>
                </tr>
            </table>
        </div>

        <h4>Button (hovered) styles</h4>
        <div>
            <table class="form-table">
                <tr valign="top">
                    <th width="1">Background color</th>
					<td class="nowrap"><input id="buttons-hover-background-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_hover_background_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['buttons_hover_background_color'] ); ?>" /></td>
                    <th>Border color</th>
					<td class="nowrap"><input id="buttons-hover-border-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_hover_border_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['buttons_hover_border_color'] ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th>Text color</th>
					<td class="nowrap"><input id="buttons-hover-font-color" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][buttons_hover_font_color]" type="text" class="color-field" value="<?php echo esc_attr( $styles['buttons_hover_font_color'] ); ?>" /></td>
                    <th></th>
                    <td></td>
                </tr>
            </table>
        </div>

        <h4>Error and success messages</h4>
        <div>
            <table class="form-table">
                <tr valign="top">
                    <th>Success text color</th>
					<td class="nowrap"><input id="messages-font-color-success" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][messages_font_color_success]" type="text" class="color-field" value="<?php echo esc_attr( $styles['messages_font_color_success'] ); ?>" /></td>
                    <th>Error text color</th>
					<td class="nowrap"><input id="messages-font-color-error" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][messages_font_color_error]" type="text" class="color-field" value="<?php echo esc_attr( $styles['messages_font_color_error'] ); ?>" /></td>
                </tr>
            </table>
        </div>

        <h4>Advanced</h4>
        <div>
            <table class="form-table">
                <tr valign="top">
                    <th><label>CSS Selector Prefix</label></th>
					<td><input type="text" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][selector_prefix]" value="<?php echo esc_attr( $styles['selector_prefix'] ); ?>" placeholder="Example: #content" /></td>
                    <td class="desc">Use this to create a more specific (and thus more "important") CSS selector.</td>
                </tr>
                <tr>
                    <th colspan="1">Manual CSS</th><td colspan="2" class="desc">The CSS rules you enter here will be appended to the custom stylesheet.</td>
                </tr>
                <tr>
                    <td colspan="3">
						<textarea class="widefat" rows="6" cols="50" name="mc4wp_form_styles[form-<?php echo $form_id; ?>][manual]" id="mc4wp-css-textarea" placeholder="Example: .mc4wp-form { background: url('http://...'); }"><?php echo esc_textarea( $styles['manual'] ); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
					<td><button type="submit" name="_mc4wp_delete_form_styles" value="<?php echo $form_id; ?>" class="button-secondary" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete all custom styles for this form?', 'mailchimp-for-wp' ); ?>');"><?php _e( 'Delete Form Styles', 'mailchimp-for-wp' ); ?></button></td>
					<td class="desc" colspan="2"><?php _e( 'Use to delete all styles for this form', 'mailchimp-for-wp' ); ?></td>
                </tr>
    </table>
        
        </div>
    </div>

	<?php submit_button( __( 'Build CSS File', 'mailchimp-for-wp' ) ); ?>

		<?php
			$tips = array(
				'Tip: use as few CSS settings as possible to reach the look you desire. In other words, leave as many options empty as possible.',
				"Tip: make sure your form mark-up is compatible with the look you desire. Don't wrap your field in paragraph elements if you want a single-line form."
			);
			echo '<p class="help">'. $tips[ array_rand( $tips ) ] . '</p>';
		?>
    </form>
</div>
<div class="mc4wp-column mc4wp-column-right" style="width:42.5%;">
    <h3>Form preview</h3>
	<iframe id="mc4wp-css-preview" data-src-url="<?php echo esc_attr( $preview_url ); ?>" src="<?php echo esc_attr( $preview_url ) ?>"></iframe>
</div>

<br class="clear" />
