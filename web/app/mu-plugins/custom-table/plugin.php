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

}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );
