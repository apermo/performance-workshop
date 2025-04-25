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
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );
