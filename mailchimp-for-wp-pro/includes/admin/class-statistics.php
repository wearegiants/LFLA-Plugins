<?php

if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Statistics {

	/**
	* @var string
	*/
	private $table_name;

	/**
	* Constructor
	*/
	public function __construct() {
		global $wpdb;

		$this->table_name = $wpdb->prefix . 'mc4wp_log';
	}


	/**
	* @param string $range
	* @return array
	*/
	public function get_range_times( $range ) {

		switch ( $range ) {
			case 'today':
				$start = strtotime( 'now midnight' );
				$end = strtotime( 'tomorrow midnight' );
				$step = 'hour';
			break;

			case 'yesterday':
				$start = strtotime( 'yesterday midnight' );
				$end = strtotime( 'today midnight' );
				$step = 'hour';
			break;

			case 'last_week':
				$start = strtotime( '-1 week midnight' );
				$end = strtotime( '+1 day midnight' );
				$step = 'day';
			break;

			case 'last_month':
				$start = strtotime( '-1 month midnight' );
				$end = strtotime( '+1 day midnight' );
				$step = 'day';
			break;

			case 'last_quarter':
				$start = strtotime( '-3 months midnight', strtotime( 'last tuesday' ) );
				$end = strtotime( '+1 week midnight', strtotime( 'last tuesday' ) );
				$step = 'week';
			break;

			case 'last_year':
				$start = strtotime( '-1 year midnight' );
				$end = strtotime( '+1 month midnight' );
				$step = 'month';
			break;

			default:
				$start = strtotime( '-1 week midnight' );
				$end = strtotime( 'tomorrow midnight' );
				$step = 'day';
			break;
		}

		return compact( 'start', 'end', 'step', 'given_day' );
	}

	/**
	* @param int $start
	* @param int $end
	*
	* @return string
	*/
	public function get_step_size( $start, $end ) {
		$difference = $end - $start;
		$dayseconds = 86400;
		$monthseconds = 2592000;

		if ( $difference > ( $monthseconds * 3 ) ) {
			$step = 'month';
		} elseif ( $difference >= ( $monthseconds ) ) {
			$step = 'week';
		} elseif ( $difference > ( $dayseconds * 2 ) ) {
			$step = 'day';
		} else {
			$step = 'hour';
		}

		return $step;
	}

	/**
	* @return array
	*/
	private function get_default_args() {
		return array(
			'start' => strtotime( '1 month ago midnight' ),
			'end' => strtotime( 'tomorrow midnight' ),
			'step' => 'day',
			'given_day' => date( 'd' )
		);
	}

	public function get_statistics( $args = array() ) {
		global $wpdb;
		extract( wp_parse_args( $args, $this->get_default_args() ) );

		// setup array of dates with 0's
		$current = $start;
		$steps = array();

		while ( $current <= $end ) {
			$steps["{$current}"] = array( $current * 1000, 0 );
			$current = strtotime( "+1 $step", $current );
		}

		$forms = get_posts( 'post_type=mc4wp-form&numberposts=-1' );

		$stats = array(
			'totals' => array(
				'label' => 'Total subscriptions',
				'data' => array(),
				'id' => 'total',
				'total_count' => 0
			),
			'form' => array(
				'label' => 'Using a form',
				'data' => array(),
				'id' => 'form-subscriptions',
				'total_count' => 0
			),
			'checkbox' => array(
				'label' => 'Using a checkbox',
				'data' => array(),
				'id' => 'checkbox-subscriptions',
				'total_count' => 0
			)
		);

		if ( is_array( $forms ) ) {
			foreach ( $forms as $f ) {
				$title = strlen( $f->post_title ) > 20 ? substr( $f->post_title, 0, 20 ) . '..' : $f->post_title;

				$stats["form_{$f->ID}"] = array(
					'label' => "Form #{$f->ID}: {$title}",
					'data' => array(),
					'id' => "form-{$f->ID}-subscriptions",
					'total_count' => 0
				);
			}
		}

		$date_formats = array(
			'hour' => '%Y-%m-%d %H:00:00',
			'day' => '%Y-%m-%d 00:00:00',
			'week' => '%YW%v 00:00:00',
			'month' => "%Y-%m-{$given_day} 00:00:00"
		);

		// get all subscriptions in given timeframe
		$query = "SELECT COUNT(id) AS count, DATE_FORMAT(datetime, '{$date_formats[$step]}') AS date FROM {$this->table_name} WHERE `success` = 1 AND UNIX_TIMESTAMP(datetime) > {$start} AND UNIX_TIMESTAMP(datetime) < {$end} GROUP BY date";
		$totals = $wpdb->get_results( $query );

		// get all checkbox subscriptions
		$query = "SELECT COUNT(id) AS count, DATE_FORMAT(datetime, '{$date_formats[$step]}') AS date FROM {$this->table_name} WHERE `success` = 1 AND method = 'checkbox' AND UNIX_TIMESTAMP(datetime) > {$start} AND UNIX_TIMESTAMP(datetime) < {$end} GROUP BY date";
		$checkbox_totals = $wpdb->get_results( $query );

		// get all form ids
		$query = "SELECT COUNT(id) AS count, related_object_ID, DATE_FORMAT(datetime, '{$date_formats[$step]}') AS date FROM {$this->table_name} WHERE `success` = 1 AND method = 'form' AND UNIX_TIMESTAMP(datetime) > {$start} AND UNIX_TIMESTAMP(datetime) < {$end} GROUP BY date, related_object_ID";
		$form_totals = $wpdb->get_results( $query );

		$data = $steps;
		$total_count = 0;
		foreach ( $totals as $day ) {
			$timestamp = strtotime( $day->date );
			$data["{$timestamp}"] = array( $timestamp * 1000, $day->count );
			$total_count += $day->count;
		}
		$stats['totals']['data'] = array_values( $data );
		$stats['totals']['total_count'] = $total_count;

		$data = $steps; $total_count = 0;
		foreach ( $form_totals as $day ) {
			$timestamp = strtotime( $day->date );
			$data["{$timestamp}"] = array( $timestamp * 1000, ( $data["{$timestamp}"][1] + $day->count ) );
			$total_count += $day->count;
		}
		$stats['form']['data'] = array_values( $data );
		$stats['form']['total_count'] = $total_count;

		// fill individual forms
		if ( is_array( $forms ) ) {
			foreach ( $forms as $f ) {

				$data = $steps; $total_count = 0;
				foreach ( $form_totals as $day ) {
					if ( $day->related_object_ID != $f->ID ) { continue; }

					$timestamp = strtotime( $day->date );
					$data["{$timestamp}"] = array( $timestamp * 1000, ( $data["{$timestamp}"][1] + $day->count ) );
					$total_count += $day->count;
				}
				$stats["form_{$f->ID}"]['data'] = array_values( $data );
				$stats["form_{$f->ID}"]['total_count'] = $total_count;
			}
		}

		$data = $steps; $total_count = 0;
		foreach ( $checkbox_totals as $day ) {
			$timestamp = strtotime( $day->date );
			$data["{$timestamp}"] = array( $timestamp * 1000, $day->count );
			$total_count += $day->count;
		}
		$stats['checkbox']['data'] = array_values( $data );
		$stats['checkbox']['total_count'] = $total_count;

		return $stats;
	}
}
