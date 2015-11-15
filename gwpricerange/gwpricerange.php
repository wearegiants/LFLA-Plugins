<?php

/**
* Plugin Name: GP Price Range
* Description: Specify a minimum/maximum price for "User Defined Price" product fields.
* Plugin URI: http://gravitywiz.com/
* Version: 1.0.1
* Author: David Smith
* Author URI: http://gravitywiz.com/
* License: GPL2
* Perk: True
*/

/**
* Saftey net for individual perks that are active when core Gravity Perks plugin is inactive.
*/
$gw_perk_file = __FILE__;
if(!require_once(dirname($gw_perk_file) . '/safetynet.php'))
    return;

class GWPriceRange extends GWPerk {
    
    public static $version_info;
    public static $email_errors;
    
    function init() {
        
        $this->enqueue_field_settings();
        $this->add_tooltip( "{$this->slug}_price_range", '<h6>' . __('Price Range', 'gwsnippets') . '</h6>' . __( 'Specify a minimum and/or maximum price users can enter for this field.', 'gravityperks' ) );
        
        add_filter( 'gform_validation', array( $this, 'range_validation' ) );
        
    }
    
    function field_settings_ui() {
        ?>
        
        <li class="price_range_setting gwp_field_setting field_setting">
            <div style="clear:both;"><?php _e('Price Range', 'gravityperks'); ?> <?php gform_tooltip("{$this->slug}_price_range"); ?></div>
            <div style="width:90px; float:left;">
                <input type="text" onkeyup="SetFieldProperty('priceRangeMin', gperk.cleanPriceRange(this.value));" size="10" id="price_range_min">
                <label for="price_range_min"><?php _e('Min', 'gravityperks'); ?></label>
            </div>
            <div style="width:90px; float:left;">
                <input type="text" onkeyup="SetFieldProperty('priceRangeMax', gperk.cleanPriceRange(this.value));" size="10" id="price_range_max">
                <label for="price_range_max"><?php _e('Max', 'gravityperks'); ?></label>
            </div>
            <br class="clear" />
        </li>
        
        <?php
    }

    function field_settings_js() {
        ?>
        <script type="text/javascript">
        
        fieldSettings['price'] += ", .price_range_setting";
        
        jQuery(document).bind('gwsFieldTabSelected', function(event) {
        	
            var currency = GetCurrentCurrency();
            
            jQuery('#price_range_min').val(field.priceRangeMin ? currency.toMoney(field.priceRangeMin) : '');
            jQuery('#price_range_max').val(field.priceRangeMax ? currency.toMoney(field.priceRangeMax) : '');
            
            jQuery('.price_range_setting input').blur(function() {
                
                var number = jQuery(this).val();
                var price = currency.toMoney(number);
               	
               	console.log( number, price );
               	 
                if(price)
                    jQuery(this).val(price);
                
            });
            
        });
        
        gperk.cleanPriceRange = function(value) {
            
            var currency = GetCurrentCurrency();
            var price = currency.toMoney(value);
            
            return price ? currency.toNumber(price) : '';
        }
        
        </script>
        <?php
        }
    
    function range_validation($validation_result) {
        
        $form = $validation_result['form'];
        $has_error = false;
        
        if(!GFCommon::get_fields_by_type($form, array('product')))
            return $validation_result;
        
        foreach($form['fields'] as &$field) {
            
            if(RGFormsModel::get_input_type($field) != 'price')
                continue;
            
            $raw_value = rgpost( "input_{$field['id']}" );

            if( rgblank( $raw_value ) ) {
                continue;
            }

            $price = GFCommon::to_number( $raw_value );
            $min = rgar($field, 'priceRangeMin');
            $max = rgar($field, 'priceRangeMax');
            
            if( ( $min && $price < $min ) || ( $max && $price > $max ) ) {
                $has_error = true;
                $field['failed_validation'] = true;
                
                if($min && $max) {
                    $field['validation_message'] = sprintf(__('Please enter a price between <strong>%s</strong> and <strong>%s</strong>.'), GFCommon::to_money($min), GFCommon::to_money($max));
                } else if($min) {
                    $field['validation_message'] = sprintf(__('Please enter a price greater than or equal to <strong>%s</strong>.'), GFCommon::to_money($min));
                } else if($max) {
                    $field['validation_message'] = sprintf(__('Please enter a price less than or equal to <strong>%s</strong>.'), GFCommon::to_money($max));
                }
            }
            
        }
        
        if(!$has_error)
            return $validation_result;
        
        $validation_result['is_valid'] = false;
        $validation_result['form'] = $form;
        
        return $validation_result;
    }

    public function documentation() {
        return array( 
            'type' => 'url', 
            'value' => 'http://gravitywiz.com/documentation/gp-price-range/' 
            );
    }
    
}   