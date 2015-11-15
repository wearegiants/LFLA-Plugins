<?php
/**
 * WooCommerce Checkout Manager Pro
 *
 * Copyright (C) 2014 Ephrain Marchan, trottyzone
 *
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// =======================================================
// IMPORT FUNCTION
// =======================================================

$options = get_option( 'wccs_settings' );
$options2 = get_option( 'wccs_settings2' );
$options3 = get_option( 'wccs_settings3' );

if (isset($_FILES['import']) && check_admin_referer('ie-import')) {
    if ($_FILES['import']['error'] > 0) {
    } else {
		$encode_options = $_FILES['import']['tmp_name'];

        if (($handle = fopen( $encode_options , "r")) !== FALSE) {
            
            $rows = 0;
            $header = fgetcsv($handle, 10000, ",");
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $c = 0;
                        foreach($header as $value) { 
                                $options['buttons'][$rows][$value] = $data[$c];
                        $c++;
                        }
            $rows++;
            update_option( 'wccs_settings', $options );       
            }   
            
        fclose($handle);
        }
    }
}

// BILLING IMPORT ===========================================================
// ==========================================================================
if (isset($_FILES['billing-import']) && check_admin_referer('ie-import')) {
    if ($_FILES['billing-import']['error'] > 0) {
	} else {
		$encode_options = $_FILES['billing-import']['tmp_name'];

        if (($handle = fopen( $encode_options , "r")) !== FALSE) {
            
            $rows = 0;
            $header = fgetcsv($handle, 10000, ",");
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $c = 0;
                        foreach($header as $value) { 
                                $options3['billing_buttons'][$rows][$value] = $data[$c];
                        $c++;
                        }
            $rows++;
            update_option( 'wccs_settings3', $options3 );       
            }   
        fclose($handle);
        }       
    }
}


// SHIPPING IMPORT ================================================================
// ================================================================================
if (isset($_FILES['shipping-import']) && check_admin_referer('ie-import')) {
    if ($_FILES['shipping-import']['error'] > 0) {
	} else {
		$encode_options = $_FILES['shipping-import']['tmp_name'];

        if (($handle = fopen( $encode_options , "r")) !== FALSE) {
            
            $rows = 0;
            $header = fgetcsv($handle, 10000, ",");
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $c = 0;
                        foreach($header as $value) { 
                                $options2['shipping_buttons'][$rows][$value] = $data[$c];
                        $c++;
                        }
            $rows++;
            update_option( 'wccs_settings2', $options2 );       
            }   
         fclose($handle);
        }
    }
}


// GENERAL IMPORT =========================================================== ..
// ==========================================================================
if (isset($_FILES['general-import']) && check_admin_referer('ie-import')) {
    if ($_FILES['general-import']['error'] > 0) {
	} else {
		$encode_options = $_FILES['general-import']['tmp_name'];

        if (($handle = fopen( $encode_options , "r")) !== FALSE) {
            
           $rows = 0;
            $header = fgetcsv($handle, 10000, ",");
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $c = 0;
                        foreach($header as $value) { 
                                $options['checkness'][$value] = $data[$c];
                        $c++;
                        }
            $rows++;
            update_option( 'wccs_settings', $options );       
            }   
            
        }
                fclose($handle);
    }
}




// =======================================================================================================
		?>
		
		<form method='post' class='import_form' enctype='multipart/form-data'>
			
				<?php wp_nonce_field('ie-import'); ?>
				<input type="button" class="button button-hero button-secondary import tap_dat12" value="Import" />

                <div id="wp-auth-check-wrap" class="click_showWccm" style="display:none;">
	                <div id="wp-auth-check-bg"></div>
	                <div id="wp-auth-check" style="max-height: 700px;">
	                <div class="wp-auth-check-close" tabindex="0" title="Close"></div>
                    <div class="updated realStop"><p>Please choose CSV file. <br /><span class="make_smalla">Max Upload Size: <?php echo size_format( wp_max_upload_size() ); ?> <br /></span></p></div>
                        <div class="updated jellow">

		<p><span class="heading_smalla">General Settings<br></span><input type='submit' class="button button-hero button-secondary wccm_importer_submit" name='submit' value='<?php _e('Import CSV', 'woocommerce-checkout-manager-pro'); ?>' /> <input type='file' name='general-import' class="wccm_importer" /></p>

				            <p><span class="heading_smalla">Billing fields<br></span><input type='submit' class="button button-hero button-secondary wccm_importer_submit" name='submit' value='<?php _e('Import CSV', 'woocommerce-checkout-manager-pro'); ?>' /> <input type='file' name='billing-import' class="wccm_importer" /></p>

						<p><span class="heading_smalla">Shipping fields<br></span><input type='submit' class="button button-hero button-secondary wccm_importer_submit" name='submit' value='<?php _e('Import CSV', 'woocommerce-checkout-manager-pro'); ?>' /><input type='file' name='shipping-import' class="wccm_importer" /> </p>

						<p><span class="heading_smalla">Additional fields<br></span><input type='submit' class="button button-hero button-secondary wccm_importer_submit" name='submit' value='<?php _e('Import CSV', 'woocommerce-checkout-manager-pro'); ?>' /><input type='file' name='import' class="wccm_importer" /> </p>

                        </div>
	                </div>
	            </div>	
		</form>
	

<script type="text/javascript">
jQuery(document).ready(function() {

    jQuery('.import.tap_dat12').click(function() {
        jQuery('#wp-auth-check-wrap').slideToggle('slow');
    });
});
</script>





<?php
// =======================================================
// EXPORT FUNCTION
// =======================================================
?>

<?php



$csv = wooccm_generate_csv('additional');
$billing_csv = wooccm_generate_csv('billing');
$shipping_csv = wooccm_generate_csv('shipping');
$general_csv = wooccm_generate_csv('general');
$heading = wooccm_generate_csv('heading');
$heading2 = wooccm_generate_csv('heading2');
$heading3 = wooccm_generate_csv('heading3');

?>


<!-- ADDITIONAL CSV -->
<script type="text/javascript">
jQuery(document).ready(function($) {
jQuery('.export.additional_fields').click(function() {

 $("#frm1 input[type=checkbox]").each(function () {

		if ($(this).attr("checked") == undefined) {
                       $(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=" " />');
		} else {
                       $(this).next().remove();
                    }
                 
           });

 $("#frm1 input[type=checkbox]").click(function () {

		if ($(this).attr("checked") == undefined) {
                       $(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=" " />');
		} else {
                       $(this).next().remove();
                    }
                 
           });

$( '#wpbody-content' ).block({message:null,overlayCSS:{background:"#fff url(<?php echo plugins_url('woocommerce/assets/images/ajax-loader.gif'); ?> ) no-repeat center",opacity:.6}});

var form = $('#frm1');
var A = [<?php echo $heading3.','.$csv; ?>];  // initialize array of rows with header row as 1st item

var csvRows = [];
for(var i=0, l=A.length; i<l; ++i){ // for each array( [..] ), join with commas for csv
for (index = 0; index < A[i].length; ++index) {
    A[i][index] = '"'+A[i][index]+'"'; // add back quotes for each string, to store special characters and commas
}
    csvRows.push( A[i] );   // put data in a java useable array
}

var csvString = csvRows.join("\n"); // make rows for each array

var a = document.createElement('a');

 $.ajax( {
      type: "POST",
      url: form.attr( 'action' ),
      data: form.serialize(),
      success: function( response ) {    
        $('#wpbody-content').unblock();

a.href     = 'data:attachment/csv,' + encodeURIComponent(csvString);
a.target   = '_blank';
a.download = 'additional_wooccm_csv.csv';
document.body.appendChild(a);
a.click();
	
      }
    });
                
});
});
</script>


<!-- BILLING CSV -->
<script type="text/javascript">
jQuery(document).ready(function($) {
jQuery('.export.billing_fields').click(function() {

 $("#frm3 input[type=checkbox]").each(function () {

		if ($(this).attr("checked") == undefined) {
                       $(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=" " />');
		} else {
                       $(this).next().remove();
                    }
                 
           });

 $("#frm3 input[type=checkbox]").click(function () {

		if ($(this).attr("checked") == undefined) {
                       $(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=" " />');
		} else {
                       $(this).next().remove();
                    }
                 
           });

$( '#wpbody-content' ).block({message:null,overlayCSS:{background:"#fff url(<?php echo plugins_url('woocommerce/assets/images/ajax-loader.gif'); ?> ) no-repeat center",opacity:.6}});

var form = $('#frm3');

var A = [<?php echo $heading.','.$billing_csv; ?>];  // initialize array of rows with header row as 1st item

var csvRows = [];
for(var i=0, l=A.length; i<l; ++i){ // for each array( [..] ), join with commas for csv

for (index = 0; index < A[i].length; ++index) {
    A[i][index] = '"'+A[i][index]+'"'; // add back quotes for each string, to store special characters and commas
}
    csvRows.push( A[i] );   // put data in a java useable array
}

var csvString = csvRows.join("\n"); // make rows for each array

var a = document.createElement('a');

 $.ajax( {
      type: "POST",
      url: form.attr( 'action' ),
      data: form.serialize(),
      success: function( response ) {    
        $('#wpbody-content').unblock();

a.href     = 'data:attachment/csv,' + encodeURIComponent(csvString);
a.target   = '_blank';
a.download = 'billing_wooccm_csv.csv';
document.body.appendChild(a);
a.click();
	
      }
    });


});
});
</script>



<!-- SHIPPING CSV -->
<script type="text/javascript">
jQuery(document).ready(function($) {
jQuery('.export.shipping_fields').click(function() {


 $("#frm2 input[type=checkbox]").each(function () {

		if ($(this).attr("checked") == undefined) {
                       $(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=" " />');
		} else {
                       $(this).next().remove();
                    }
                 
           });

 $("#frm2 input[type=checkbox]").click(function () {

		if ($(this).attr("checked") == undefined) {
                       $(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=" " />');
		} else {
                       $(this).next().remove();
                    }
                 
           });

$( '#wpbody-content' ).block({message:null,overlayCSS:{background:"#fff url(<?php echo plugins_url('woocommerce/assets/images/ajax-loader.gif'); ?> ) no-repeat center",opacity:.6}});

var form = $('#frm2');

var A = [<?php echo $heading.','.$shipping_csv; ?>];  // initialize array of rows with header row as 1st item

var csvRows = [];
for(var i=0, l=A.length; i<l; ++i){ // for each array( [..] ), join with commas for csv

for (index = 0; index < A[i].length; ++index) {
    A[i][index] = '"'+A[i][index]+'"'; // add back quotes for each string, to store special characters and commas
}
    csvRows.push( A[i] );   // put data in a java useable array
}

var csvString = csvRows.join("\n"); // make rows for each array

var a = document.createElement('a');

$.ajax( {
      type: "POST",
      url: form.attr( 'action' ),
      data: form.serialize(),
      success: function( response ) {    
        $('#wpbody-content').unblock();

a.href     = 'data:attachment/csv,' + encodeURIComponent(csvString);
a.target   = '_blank';
a.download = 'shipping_wooccm_csv.csv';
document.body.appendChild(a);
a.click();

	
      }
    });


});
});
</script>


<!-- GENERAL CSV -->
<script type="text/javascript">
jQuery(document).ready(function($) {
jQuery('.export.general_settings').click(function() {

 $("#frm1 input[type=checkbox]").each(function () {

		if ($(this).attr("checked") == undefined) {
                       $(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=" " />');
		} else {
                       $(this).next().remove();
                    }
                 
           });

 $("#frm1 input[type=checkbox]").click(function () {

		if ($(this).attr("checked") == undefined) {
                       $(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=" " />');
		} else {
                       $(this).next().remove();
                    }
                 
           });

$( '#wpbody-content' ).block({message:null,overlayCSS:{background:"#fff url(<?php echo plugins_url('woocommerce/assets/images/ajax-loader.gif'); ?> ) no-repeat center",opacity:.6}});

var form = $('#frm1');

var A = [<?php echo $heading2.','.$general_csv; ?>];  // initialize array of rows with header row as 1st item

var csvRows = [];
for(var i=0, l=A.length; i<l; ++i){ // for each array( [..] ), join with commas for csv

for (index = 0; index < A[i].length; ++index) {
    A[i][index] = '"'+A[i][index]+'"'; // add back quotes for each string, to store special characters and commas
}
    csvRows.push( A[i] );   // put data in a java useable array
}

var csvString = csvRows.join("\n"); // make rows for each array

var a = document.createElement('a');

$.ajax( {
      type: "POST",
      url: form.attr( 'action' ),
      data: form.serialize(),
      success: function( response ) {    
        $('#wpbody-content').unblock();

a.href     = 'data:attachment/csv,' + encodeURIComponent(csvString);
a.target   = '_blank';
a.download = 'general_wooccm_csv.csv';
document.body.appendChild(a);
a.click();

	
      }
    });


});
});
</script>


<?php
/**
* Converting data to CSV
*/
function wooccm_generate_csv($tab) {

    $options = get_option( 'wccs_settings' );
	$options2 = get_option( 'wccs_settings2' );
		$options3 = get_option( 'wccs_settings3' );

    $csv_output = '';
    
    if ( $tab == 'additional' ) {
        
		if ( !empty($options['buttons']) ) {
		
		$total = count($options['buttons']) - 1;
		
			foreach( $options['buttons'] as $i => $btn) {
				if( $i != 999 && !empty($btn['cow']) ) {
					$csv_output .= '[';
					
						foreach($btn as $n => $dataw) {
								$csv_output .= '"'.$dataw.'",';
						}
					
					if ( $i != $total ) {
						$csv_output .= '], ';
					} else {
						$csv_output .= ']';   
					}
				}
			}
		}
    }elseif ($tab == 'billing' ) {

    	$total = count($options3['billing_buttons']) - 1;

		if (!empty($options3['billing_buttons']) ) {
			foreach( $options3['billing_buttons'] as $i => $btn) {
				if( $i != 999 && !empty($btn['cow']) ) {
					$csv_output .= '[';
					
						foreach($btn as $n => $dataw) {
							$csv_output .= '"'.$dataw.'",';
						}
					
					if ( $i != $total) {
						$csv_output .= '], ';
					} else {
						$csv_output .= ']';   
					}
				}
			}
        } 
    }elseif ( $tab == 'shipping') {
        $total = count($options2['shipping_buttons']) -1;
        
		if( !empty($options2['shipping_buttons']) ) {
			foreach( $options2['shipping_buttons'] as $i => $btn) {
				if( $i != 999 && !empty($btn['cow']) ) {
					$csv_output .= '[';
					
						foreach($btn as $n => $dataw) {
								$csv_output .= '"'.$dataw.'",';	
						}
					
					if ( $i != $total) {
						$csv_output .= '], ';
					} else {
						$csv_output .= ']';   
					}
				}
			}
		}
	}
	elseif ( $tab == 'general') {

		if( !empty($options['checkness']) ) {
			$csv_output .= '[';
			foreach( $options['checkness'] as $i => $btn) {
					$csv_output .= '"'.$btn.'",';					  
			}
			$csv_output .= ']'; 
		}
	}elseif ($tab == 'heading' ) {

		if (!empty($options3['billing_buttons']) ) {
				$csv_output .= '[';
				
					foreach( $options3['billing_buttons'][0] as $n => $dataw) {
						$csv_output .= '"'.$n.'",';
					}
				
					$csv_output .= ']';   
	    }
    }elseif ($tab == 'heading3' ) {

		if (!empty($options['buttons']) ) {
				$csv_output .= '[';
				
					foreach( $options['buttons'][0] as $n => $dataw) {
						$csv_output .= '"'.$n.'",';
					}
				
					$csv_output .= ']';  
        } 
    }elseif ($tab == 'heading2' ) {

    	if (!empty($options['checkness']) ) {
            $csv_output .= '[';
			foreach( $options['checkness'] as $n => $btn) {
                    $csv_output .= '"'.$n.'",';
			}
            $csv_output .= ']'; 
        } 
    }

	
return $csv_output;
}


?>

<form method="post" class="import_form" name="export" >
    <a class="button button-hero button-secondary export general_settings" >Export General</a>
</form>

<form method="post" class="import_form" name="export" >
    <a class="button button-hero button-secondary export billing_fields" >Export Billing</a>
</form>

<form method="post" class="import_form" name="export" >
    <a class="button button-hero button-secondary export shipping_fields" >Export Shipping</a>
</form>

<form method="post" class="import_form" name="export" >
    <a class="button button-hero button-secondary export additional_fields" >Export Additional</a>
</form>