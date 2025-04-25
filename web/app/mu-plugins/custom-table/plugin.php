<?php
/**
 * Plugin Name: Custom tables for queries
 * Description: Use a custom table for queries instead of the post meta
 * Version: 1.0.0
 * Author: Christoph Daum
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: custom-tables-for-queries
 * Domain Path: /languages
 */
namespace Apermo\Custom_Table;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/inc/class.custom-tables.php';

/**
 * Initialize the plugin.
 */
function init() {
	// Create an instance of the Custom_Tables class
	$custom_tables = new \apermo\WPTools\Custom_Tables(
		'workshop',
		1
	);

	$custom_tables->add(
		'custom_meta',
		'CREATE TABLE %1$s (
               post_id bigint(20) unsigned NOT NULL,
               post_hide_from_archive tinyint(1) NOT NULL DEFAULT 0,
               _user_rate_mean float NOT NULL DEFAULT 0,
               PRIMARY KEY (post_id)
               )
               %2$s'
	);

	/*
	// To migrate the data use:
INSERT INTO wp_custom_meta (post_id, post_hide_from_archive, _user_rate_mean)
SELECT
    p.ID AS post_id,
    COALESCE(CAST(meta1.meta_value AS UNSIGNED), 0) AS post_hide_from_archive,
    COALESCE(CAST(meta2.meta_value AS FLOAT), 0) AS _user_rate_mean
FROM
    wp_posts p
LEFT JOIN
    wp_postmeta meta1
    ON p.ID = meta1.post_id
    AND meta1.meta_key = 'post_hide_from_archive'
LEFT JOIN
    wp_postmeta meta2
    ON p.ID = meta2.post_id
    AND meta2.meta_key = '_user_rate_mean'
WHERE
    p.post_type = 'post'
    AND p.post_status = 'publish'
GROUP BY
    p.ID;
	 */

	// Create or update the tables
	$custom_tables->create_and_update_tables();

	add_filter( 'update_post_metadata', __NAMESPACE__ . '\sync_post_meta', 10, 5 );
	add_filter( 'delete_post_metadata', __NAMESPACE__ . '\sync_post_meta', 10, 5 );

	// An add to post delete will also be required.
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Syncs the post meta with the custom table.
 *
 * @param $check
 * @param $object_id
 * @param $meta_key
 * @param $meta_value
 * @param $prev_value
 *
 * @return bool|null
 */
function sync_post_meta( $check, $object_id, $meta_key, $meta_value, $prev_value ): ?bool {
	switch ( $meta_key ) {
		default:
			// In all undefined cases, we just return the check and exit here.
			return $check;

		case 'post_hide_from_archive':
			$meta_value = (int) $meta_value;
			break;
		case '_user_rate_mean':
			$meta_value = (float) $meta_value;
			break;
	}

	global $wpdb;
	// Check if there is a row with the post_id in the custom table.
	$result = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT 1 FROM {$wpdb->custom_meta} WHERE post_id = %d LIMIT 1",
			$object_id
		)
	);

	if ( empty( $result ) ) {
		// If there is no row, we insert a new row with the post_id and the meta_value.
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->custom_meta} (post_id, {$meta_key}) VALUES (%d, %s)",
				$object_id,
				$meta_value
			)
		);
	} else {
		// If there is a row, we update the row with the post_id and the meta_value.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->custom_meta} SET {$meta_key} = %s WHERE post_id = %d",
				$meta_value,
				$object_id
			)
		);
	}

	return $check;
}

