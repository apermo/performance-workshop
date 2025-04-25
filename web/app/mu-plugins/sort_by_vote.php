<?php
/**
 * Plugin Name: Sort Keyboard Category by Rating
 * Description: MU plugin to sort category 2232 or 2310 by _user_rate_mean from custom table.
 * Author: Christoph Daum
 * Version: 2.0
 */

add_filter( 'posts_clauses', function ( $clauses, $query ) {
	// Only modify frontend main queries
	if ( is_admin() || ! $query->is_main_query() ) {
		return $clauses;
	}

	// Only filter specific category archives
	if ( $query->is_category( [ 2232, 2310 ] ) ) {
		global $wpdb;

		if ( ! str_contains( $clauses['join'], $wpdb->custom_meta ) ) {
			// Add JOIN with custom meta table if needed.
			$clauses['join'] .= " LEFT JOIN {$wpdb->custom_meta} ON {$wpdb->posts}.ID = {$wpdb->custom_meta}.post_id";
		}
		// Modify ordering using custom table column
		$clauses['orderby'] = "{$wpdb->custom_meta}._user_rate_mean DESC";

		// Add fallback ordering for posts with same rating
		$clauses['orderby'] .= ", {$wpdb->posts}.post_date DESC";
	}

	return $clauses;
}, 10, 2 );

/*
add_action( 'pre_get_posts', function ( $query ) {
	// Only modify frontend main queries
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Only filter category archive for category ID 2232 or 2310
	if ( $query->is_category( 2232 ) || $query->is_category( 2310 ) ) {
		$query->set( 'meta_key', '_user_rate_mean' );
		$query->set( 'orderby', 'meta_value_num' ); // Use numeric sorting
		$query->set( 'order', 'DESC' );
	}
} );
*/