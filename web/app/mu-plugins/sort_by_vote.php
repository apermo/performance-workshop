<?php
/**
 * Plugin Name: Sort Keyboard Category by Rating
 * Description: MU plugin to sort category 2232 or 2310 by _user_rate_mean in descending order.
 * Author: Christoph Daum
 * Version: 1.0
 */

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
