<?php
if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
?>

    <form method="get">
        <div class="tablenav top">
            <div class="alignleft actions">
                <input type="hidden" name="page" value="mailchimp-for-wp-reports" />

                <select id="mc4wp-graph-range" name="range">
					<option value="today" <?php selected( $range, 'today' ); ?>>Today</option>
					<option value="yesterday" <?php selected( $range, 'yesterday' ); ?>>Yesterday</option>
					<option value="last_week" <?php selected( $range, 'last_week' ); ?>>Last Week</option>
					<option value="last_month" <?php selected( $range, 'last_month' ); ?>>Last Month</option>
					<option value="last_quarter" <?php selected( $range, 'last_quarter' ); ?>>Last Quarter</option>
					<option value="last_year" <?php selected( $range, 'last_year' ); ?>>Last Year</option>
					<option value="custom" <?php selected( $range, 'custom' ); ?>>Custom</option>
                </select>

				<div id="mc4wp-graph-custom-range-options" <?php if($range != 'custom') { ?>style="display:none;"<?php } ?>>
                    <span>From </span>
                    <select name="start_day">
						<?php for($day = 1; $day <= 31; $day++) { ?>
							<option <?php selected( $start_day, $day ); ?>><?php echo $day++; ?></option>
						<?php } ?>
                    </select>
                    <select name="start_month">
						<option value="1" <?php selected( $start_month, '1' ); ?>>Jan</option>
						<option value="2" <?php selected( $start_month, '2' ); ?>>Feb</option>
						<option value="3" <?php selected( $start_month, '3' ); ?>>Mar</option>
						<option value="4" <?php selected( $start_month, '4' ); ?>>Apr</option>
						<option value="5" <?php selected( $start_month, '5' ); ?>>May</option>
						<option value="6" <?php selected( $start_month, '6' ); ?>>Jun</option>
						<option value="7" <?php selected( $start_month, '7' ); ?>>Jul</option>
						<option value="8" <?php selected( $start_month, '8' ); ?>>Aug</option>
						<option value="9" <?php selected( $start_month, '9' ); ?>>Sep</option>
						<option value="10" <?php selected( $start_month, '10' ); ?>>Oct</option>
						<option value="11" <?php selected( $start_month, '11' ); ?>>Nov</option>
						<option value="12" <?php selected( $start_month, '12' ); ?>>Dec</option>
                    </select>
                    <select name="start_year">
                        <option value="2013" selected="selected">2013</option>
                    </select>
                    <span>To </span>
                    <select name="end_day">
						<?php for($day = 1; $day <= 31; $day++) { ?>
							<option <?php selected( $end_day, $day ); ?>><?php echo $day; ?></option>
						<?php } ?>
                    </select>
                    <select name="end_month">
						<option value="1" <?php selected( $end_month, '1' ); ?>>Jan</option>
						<option value="2" <?php selected( $end_month, '2' ); ?>>Feb</option>
						<option value="3" <?php selected( $end_month, '3' ); ?>>Mar</option>
						<option value="4" <?php selected( $end_month, '4' ); ?>>Apr</option>
						<option value="5" <?php selected( $end_month, '5' ); ?>>May</option>
						<option value="6" <?php selected( $end_month, '6' ); ?>>Jun</option>
						<option value="7" <?php selected( $end_month, '7' ); ?>>Jul</option>
						<option value="8" <?php selected( $end_month, '8' ); ?>>Aug</option>
						<option value="9" <?php selected( $end_month, '9' ); ?>>Sep</option>
						<option value="10" <?php selected( $end_month, '10' ); ?>>Oct</option>
						<option value="11" <?php selected( $end_month, '11' ); ?>>Nov</option>
						<option value="12" <?php selected( $end_month, '12' ); ?>>Dec</option>
                    </select>
                    <select  name="end_year">
						<option value="2013" <?php selected( $end_year, '2013' ); ?>>2013</option>
						<option value="2014" <?php selected( $end_year, '2014' ); ?>>2014</option>
                    </select>
                </div>
                <input type="submit" class="button-secondary" value="Filter">
            </div>
        </div>
    </form>

    <div id="mc4wp-graph"></div>
        
    <h3>Show these lines: </h3>
    
    <p id="mc4wp-graph-line-toggles">
		<?php foreach($statistics_data as $key => $data) { ?>
			<label <?php if($data['total_count'] == 0) { echo 'class="disabled"'; } ?>><input type="checkbox" name="mc4wp_graph_toggle" value="<?php echo $key; ?>" <?php if($data['total_count'] == 0) { echo 'disabled '; } checked( $key, 'totals' ); ?>/> <?php echo $data['label']; ?> (<?php echo $data['total_count']; ?>)</label>
		<?php } ?>
    </p>
    
    <div id="mc4wp-graph-summary">
		<?php /*<p>Total sign-ups in shown period: <?php echo $totals['all']; ?></p>
        <p>Total form sign-ups in shown period: <?php echo $totals['form']; ?></p>
		<p>Total checkbox sign-ups in shown period: <?php echo $totals['checkbox']; ?></p> */ ?>
    </div>
