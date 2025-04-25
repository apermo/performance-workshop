<?php
/**
 * Plugin Name: Workshop Plugin - Months with files
 * Description: Speeds up the check for months with files.
 * Version: 1.0
 */

namespace Apermo\PerformanceWorkshop;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

function media_library_months_with_files(): array {
	$months        = unserialize( 'a:11:{i:0;O:8:"stdClass":2:{s:4:"year";s:4:"2018";s:5:"month";s:1:"1";}i:1;O:8:"stdClass":2:{s:4:"year";s:4:"2018";s:5:"month";s:1:"2";}i:2;O:8:"stdClass":2:{s:4:"year";s:4:"2018";s:5:"month";s:1:"4";}i:3;O:8:"stdClass":2:{s:4:"year";s:4:"2018";s:5:"month";s:1:"5";}i:4;O:8:"stdClass":2:{s:4:"year";s:4:"2018";s:5:"month";s:1:"9";}i:5;O:8:"stdClass":2:{s:4:"year";s:4:"2018";s:5:"month";s:2:"11";}i:6;O:8:"stdClass":2:{s:4:"year";s:4:"2019";s:5:"month";s:1:"1";}i:7;O:8:"stdClass":2:{s:4:"year";s:4:"2019";s:5:"month";s:1:"5";}i:8;O:8:"stdClass":2:{s:4:"year";s:4:"2019";s:5:"month";s:2:"10";}i:9;O:8:"stdClass":2:{s:4:"year";s:4:"2019";s:5:"month";s:2:"11";}i:10;O:8:"stdClass":2:{s:4:"year";s:4:"2019";s:5:"month";s:2:"12";}}', [ 'stdClass' ] );
	$current_year  = (int) date( 'Y' );
	$current_month = (int) date( 'n' );
	for ( $year = 2020; $year <= $current_year; $year ++ ) {
		for ( $month = 1; $month <= 12; $month ++ ) {
			if ( $year === $current_year && $month > $current_month ) {
				continue;
			}
			$months[] = (object) [
				'year'  => $year,
				'month' => $month,
			];
		}
	}

	// Reverse the array.
	return array_reverse( $months );
}

add_filter( 'media_library_months_with_files', __NAMESPACE__ . '\\media_library_months_with_files', 999 );