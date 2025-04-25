<?php
/**
 * Plugin Name: Workshop Plugin - Heartbeat adjustments
 * Description: Adjusts the hearbeat.
 * Version: 1.0
 */

namespace Apermo\PerformanceWorkshop;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

function filter_hearbeat( array $settings ): array {
	$request_uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL );

	if ( str_contains( $request_uri, '/wp-admin/post.php' ) ) {
		// Im Editor.
		$interval = 60;
	} elseif ( is_admin() ) {
		// Im Backend.
		$interval = 180;
	} else {
		// Im Frontend.
		$interval = 3600;
	}

	$settings['interval'] = $interval;

	return $settings;
}


add_filter( 'heartbeat_settings', __NAMESPACE__ . '\\filter_hearbeat', 10, 1 );