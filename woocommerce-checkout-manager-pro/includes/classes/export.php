<?php
/**
 * WooCommerce Checkout Manager Pro
 *
 *
 * Copyright (C) 2014 Ephrain Marchan, trottyzone
 *
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function wooccm_additional_gen( $tab, $abbr, $section ) {
	global $woocommerce, $wpdb;
    $options = get_option( 'wccs_settings' );
	
	$args = array(
		'post_type'		=> 'shop_order',
		'posts_per_page' 	=> -1,
        	'post_status' => array( 'wc-processing', 'wc-completed' )
			);
			
	$loop = new WP_Query( $args );
    $csv_output = '';
	
	if( !empty($abbr) && $section == 1 ) {
		if ( $tab == 'additional' ) {
				while ( $loop->have_posts() ) : 
					$loop->the_post();
					$order_id = $loop->post->ID;
					$order = new WC_Order($order_id);
				
					if ( get_post_meta($order_id, $abbr, true) ) {
						$csv_output .= '["'.$order->billing_first_name.' '.$order->billing_last_name.'", "'.get_post_meta($order_id, $abbr, true).'" ], ';
					}
				endwhile;		
		}elseif ($tab == 'heading' ) {		
						$csv_output .= '["Name","'.$abbr.'"]';
		}
	} elseif( empty($abbr) && $section == 2 ) {
		if ( $tab == 'additional' ) {
				
				while ( $loop->have_posts() ) : 
					$loop->the_post();
					$order_id = $loop->post->ID;
					$order = new WC_Order($order_id);
					foreach( $options['buttons'] as $name ) {
						if ( get_post_meta($order_id, $name['cow'], true) ) {
							$listida[] = $order_id;	
						}
					}
				endwhile;
				$csv_output = array_unique($listida);
		}elseif ($tab == 'heading' ) {
				while ( $loop->have_posts() ) : 
					$loop->the_post();
					$order_id = $loop->post->ID;
					$order = new WC_Order($order_id);
					foreach( $options['buttons'] as $n) {
							if ( get_post_meta($order_id, $n['cow'], true) ) {	
									$lista[] = $n['label'];
							}
					}
				endwhile;
				$csv_output = array_unique($lista);
		}
	}

	
return $csv_output;
}


function wooccm_csvall_heading($heading) {
	$csv_output .= '["Name", ';
	foreach($heading as $data ){
		$csv_output .= '"'.$data.'", ';
	}
	$csv_output .= ']';
	
	return $csv_output;
}

function wooccm_csvall_info($orderids){
$options = get_option( 'wccs_settings' );
	
	foreach( $orderids as $order_id ) {
		$csv_output .= '["'.get_post_meta($order_id, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true).'", ';
								
		foreach( $options['buttons'] as $name2 ) {
			$csv_output .= '"'.get_post_meta($order_id, $name2['cow'], true).'", ';
		}
		
		$csv_output .= '], ';
	}
	return $csv_output;
}


function wooccm_advance_export(){ 
$options = get_option( 'wccs_settings' );
global $woocommerce;
	
	$args = array(
		'post_type'		=> 'shop_order',
		'posts_per_page' 	=> -1,
        	'post_status' => array( 'wc-processing', 'wc-completed' )
			);
			
	$loop = new WP_Query( $args );
					
if ( isset($_POST['single-download']) && !empty($_POST['single-download']) ) {
	
	$csv = wooccm_additional_gen('additional', $_POST['selectedval'], 1);
	$heading = wooccm_additional_gen('heading', $_POST['selectedval'], 1);	
?> 

<script type="text/javascript">
jQuery(document).ready(function($) {

var A = [<?php echo $heading.','.$csv; ?>];  // initialize array of rows with header row as 1st item

var csvRows = [];
for(var i=0, l=A.length; i<l; ++i){ // for each array( [..] ), join with commas for csv
for (index = 0; index < A[i].length; ++index) {
    A[i][index] = '"'+A[i][index]+'"'; // add back quotes for each string, to store special characters and commas
}
    csvRows.push( A[i] );   // put data in a java useable array
}

var csvString = csvRows.join("\n"); // make rows for each array

var a = document.createElement('a');

a.href     = 'data:attachment/csv,' + encodeURIComponent(csvString);
a.target   = '_blank';
a.download = 'only_additional_fieldname.csv';
document.body.appendChild(a);
a.click();
	                   
});
</script>

<?php } 
	
if ( isset($_POST['all-download']) && !empty($_POST['all-download']) ) {
	
	$abbr = '';
	$csv = wooccm_additional_gen('additional', $abbr, 2);
	$csv = wooccm_csvall_info($csv);
	$heading = wooccm_additional_gen('heading', $abbr, 2);	
	$heading = wooccm_csvall_heading($heading);
?> 

<script type="text/javascript">
jQuery(document).ready(function($) {

var A = [<?php echo $heading.','.$csv; ?>];  // initialize array of rows with header row as 1st item

var csvRows = [];
for(var i=0, l=A.length; i<l; ++i){ // for each array( [..] ), join with commas for csv
for (index = 0; index < A[i].length; ++index) {
    A[i][index] = '"'+A[i][index]+'"'; // add back quotes for each string, to store special characters and commas
}
    csvRows.push( A[i] );   // put data in a java useable array
}

var csvString = csvRows.join("\n"); // make rows for each array

var a = document.createElement('a');

a.href     = 'data:attachment/csv,' + encodeURIComponent(csvString);
a.target   = '_blank';
a.download = 'only_additional_fieldname.csv';
document.body.appendChild(a);
a.click();
	                   
});
</script>

<?php } ?>


<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery(function () {
						jQuery(".button.single-add").click(function() {
							jQuery("input[name=single-add]").val("go");
							jQuery("#additional_export").submit();
						});
						
						jQuery(".button.single-download").click(function() {
							jQuery("input[name=single-download]").val("go");
							jQuery("#additional_export").submit();
						});
						
						jQuery(".button.all-download").click(function() {
							jQuery("input[name=all-download]").val("go");
							jQuery("#additional_export").submit();
						});
						
					});
					
				});
</script>
				

<div class="wrap">

<div id="welcome-panel" class="welcome-panel heading">
<h1 class="heading-blue"><?php _e( 'Additional Export', 'woocommerce-checkout-manager-pro'); ?></h1>
</div>


<div id="welcome-panel" class="welcome-panel heading">
<form name="additionalexport" method="post" action="" id="additional_export">
<input type="hidden" name="single-add" val="" />
<input type="hidden" name="all-add" val="" />
<input type="hidden" name="single-download" val="" />
<input type="hidden" name="all-download" val="" />

		
<div id="welcome-panel" class="welcome-panel left">
        <div class="welcome-panel-content">
                <p class="about-description"><?php _e( 'Export All Orders with only additional field name ', 'woocommerce-checkout-manager-pro'); ?>
					<select name="selectedval">
					<?php foreach( $options['buttons'] as $name ) { ?>
						<option value="<?php echo $name['cow']; ?>"><?php echo $name['cow']; ?></option>
					<?php } ?>
					</select>
				</p>
				<hr />
                
                <div class="welcome-panel-column-container">
                	<div class="welcome-panel-column">
                        <ul>
                			<a class="button button-primary button-hero single-download" href="#"><?php _e( 'Download', 'woocommerce-checkout-manager-pro'); ?></a>
							<a class="button view button-primary button-hero single-add" href="#"><?php _e( 'View', 'woocommerce-checkout-manager-pro'); ?></a>
                		</ul>  
                    </div>
                </div>
				
			<div class="sheet single-add">
			<span class="disappear">
			<?php 
			
			if ( isset($_POST['single-add']) && !empty($_POST['single-add']) ) {
				
				if ( wooccm_mul_array2( $_POST['selectedval'] ) ) {
					while ( $loop->have_posts() ) : 
						$loop->the_post();
						$order_id = $loop->post->ID;
						$order = new WC_Order($order_id);
					
						if ( get_post_meta($order_id, $_POST['selectedval'], true) ) {
							echo 'Name : '.$order->billing_first_name.' '.$order->billing_last_name.'<br />';
							echo $_POST['selectedval'].' : '.get_post_meta($order_id, $_POST['selectedval'], true).'<br />';
							
							?> <div class="sep"></div> <?php
						}
						
					endwhile;
				} else {
					echo __('No results was found.', 'woocommerce-checkout-manager-pro' );
				}
				
			}
			?>
			</span>
			</div>
				
				<p class="about-description"><?php _e( 'Export All Orders with all additional fields', 'woocommerce-checkout-manager-pro'); ?>
				</p>
				<hr />
                
                <div class="welcome-panel-column-container">
                	<div class="welcome-panel-column">
                        <ul>
                			<a class="button button-primary button-hero all-download" href="#"><?php _e( 'Download', 'woocommerce-checkout-manager-pro'); ?></a>	
							<a class="button view button-primary button-hero all-add" href="#"><?php _e( 'View', 'woocommerce-checkout-manager-pro'); ?></a>
                		</ul>  
                    </div>
                </div>
					
			<div class="sheet all-add">
			<span class="disappear">
			<?php 
			
			
			if ( isset($_POST['all-add']) && !empty($_POST['all-add']) ) {
			if ( wooccm_does_existw($options['buttons']) ){
			$abbr = '';
			$orderids = wooccm_additional_gen('additional', $abbr, 2);
	
				foreach( $orderids as $order_id ) {
						echo 'Name : '.get_post_meta($order_id, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true).'<br />';
						
						foreach( $options['buttons'] as $name2 ) {
							if( get_post_meta($order_id, $name2['cow'], true) ) {
								echo $name2['label'].' : '.get_post_meta($order_id, $name2['cow'], true).'<br />';
							}
						}
						
						?> <div class="sep"></div> <?php
				}
			} else {
				echo __('No additional fields exist', 'woocommerce-checkout-manager-pro');
			}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery(function () {
						jQuery(".button.all-add").click(function() {
							jQuery(".disappear").toggle();
						});
					});
				});
			</script>
			<?php
			} else {
				?>
				<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery(function () {
						jQuery(".button.all-add").click(function() {
							jQuery("input[name=all-add]").val("go");
							jQuery("#additional_export").submit();
						});
					});
					
				});
				</script>
				<?php
			}
			?>
			</span>
			</div>
			
				
	    </div>
</div>  

</form>
</div>
</div>

<?php }