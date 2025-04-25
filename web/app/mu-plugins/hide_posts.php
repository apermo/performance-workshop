<?php
/**
 * Plugin Name: Filter Category Bass by Meta Field
 * Description: Must-Use plugin to filter posts in category 2228 or 2310 to only show those with post_hide_from_archive = 0.
 * Author: Christoph Daum
 * Version: 1.0
 */

/**
 * Plugin Name: Filter Category Bass by Custom Meta
 * Description: MU plugin to filter category 2228/2310 using custom table's post_hide_from_archive.
 * Author: Christoph Daum
 * Version: 2.0
 */

add_filter( 'posts_clauses', function ( $clauses, $query ) {
	// Only modify frontend main queries
	if ( is_admin() || ! $query->is_main_query() ) {
		return $clauses;
	}

	// Only filter specific category archives
	if ( $query->is_category( [2228, 2310] ) ) {
		global $wpdb;

		if ( ! str_contains( $clauses['join'], $wpdb->custom_meta ) ) {
			// Add JOIN with custom meta table if needed.
			$clauses['join'] .= " LEFT JOIN {$wpdb->custom_meta} ON {$wpdb->posts}.ID = {$wpdb->custom_meta}.post_id";
		}

		// Filter using custom table column
		$clauses['where'] .= " AND {$wpdb->custom_meta}.post_hide_from_archive = 0";
	}

	return $clauses;
}, 10, 2 );

/*
add_action( 'pre_get_posts', function ( $query ) {
	// Only modify frontend main queries
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Only filter category archive for category ID 2228 or 2310
	if ( $query->is_category( 2228 ) || $query->is_category( 2310 ) ) {
		$meta_query = [
			[
				'key'   => 'post_hide_from_archive',
				'value' => '0',
			]
		];

		// Merge with existing meta_query if present
		if ( $existing = $query->get( 'meta_query' ) ) {
			$meta_query = array_merge( $existing, $meta_query );
		}

		$query->set( 'meta_query', $meta_query );
	}
} );
*/