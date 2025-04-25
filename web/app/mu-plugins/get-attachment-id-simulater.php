<?php
/**
 * Plugin Name: Get image id issue simulator
 * Description: Simulates the get image ID issue.
 * Author: Christoph Daum
 * Version: 1.0
 */

add_action( 'save_post', function ( $post_id, $post ) {
	// Prevent recursion and unnecessary processing
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Match all resized image URLs in content
	preg_match_all(
		'#https?://[^/]+/wp-content/uploads/(\d{4}/\d{2}/[^-]+)(-\d+x\d+)?(\.\w+)#',
		$post->post_content,
		$matches,
		PREG_SET_ORDER
	);

	foreach ( $matches as $match ) {
		$original_url = "{$match[1]}{$match[3]}";
		$full_url     = content_url( "/uploads/$original_url" );

		// Get attachment ID from original URL.
		// This was done by an ACF block in the reference project.
		attachment_url_to_postid( $full_url );
	}
}, 10, 2 );
