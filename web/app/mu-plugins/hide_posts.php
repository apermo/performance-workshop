<?php
/**
 * Plugin Name: Filter Category Bass by Meta Field
 * Description: Must-Use plugin to filter posts in category 2228 or 2310 to only show those with post_hide_from_archive = 0.
 * Author: Christoph Daum
 * Version: 1.0
 */

// Must-Use plugins go in wp-content/mu-plugins/

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
