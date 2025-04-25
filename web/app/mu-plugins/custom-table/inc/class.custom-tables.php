<?php
/**
 * A class to create custom tables within WordPress.
 *
 * @see https://gist.github.com/apermo/0fb0cbca1f57625ba6753ef3b7f73ffa
 *
 * @version 1.0.1
 */

namespace apermo\WPTools;

use Exception;

class Custom_Tables {
	/**
	 * The option key to store the version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private string $version_key;

	/**
	 * The version to update to.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private int $version;

	/**
	 * The debug mode flag.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	private bool $debug = false;

	/**
	 * The tables to create.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private array $tables = [];

	/**
	 * The debug messages.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private array $debug_messages = [];

	/**
	 * Constructor.
	 *
	 * @param string $version_key The option key to store the version.
	 * @param int $version The version to update to.
	 * @param bool $debug The debug mode flag. Default false.
	 *
	 * @since 1.0.0
	 */
	public function __construct( string $version_key, int $version, bool $debug = false ) {
		$this->version_key = $version_key;
		$this->version     = $version;
		$this->debug       = $debug;
	}

	/**
	 * Adds a table.
	 *
	 * The table name will be prefixed with the WordPress table prefix.
	 *
	 * Example:
	 *
	 * CREATE TABLE %1$s (
	 * id mediumint(8) unsigned NOT NULL auto_increment,
	 * post_id bigint(20) unsigned NOT NULL,
	 * some_text varchar(255) NULL,
	 * PRIMARY KEY (id)
	 * )
	 * %2$s
	 *
	 * Mind that new lines are important for the proper use with dbDelta()
	 *
	 * @param string $table_name The table name without prefix.
	 * @param string $create_sql The SQL to create the table. Use %1$s for the table name and optionally %2$s for the SQL collation.
	 *
	 * @throws Exception If the table name is invalid or the SQL is invalid.
	 * @since 1.0.0
	 */
	public function add( string $table_name, string $create_sql ): void {
		if ( $table_name !== sanitize_key( $table_name ) ) {
			throw new Exception( 'Invalid table name' );
		}

		global $wpdb;
		$wpdb->$table_name = $wpdb->prefix . $table_name;
		$wpdb->tables[]    = $table_name;

		if ( str_contains( $create_sql, '%1$s' ) === false ) {
			throw new Exception( 'Invalid SQL, add %1$s for the table name and optionally %2$s for the SQL collation' );
		}

		$create_sql = sprintf( $create_sql, $wpdb->prefix . $table_name, 'COLLATE ' . $wpdb->collate );

		$this->tables[ $table_name ] = $create_sql;
	}

	/**
	 * Creates and updates the tables.
	 *
	 * @param bool $force Whether to force the update. Default false.
	 *
	 * @since 1.0.0
	 */
	public function create_and_update_tables( bool $force = false ): void {
		global $wpdb;
		$current_version = get_option( $this->version_key, 0 );

		$this->debug_messages[] = 'Creating tables...';
		$this->debug_messages[] = 'Current version: ' . $current_version;
		$this->debug_messages[] = 'Target version: ' . $this->version;

		if ( $current_version >= $this->version && $force !== true ) {
			$this->debug_messages[] = 'Tables up to date...';
			$this->store_debug_messages();

			return;
		}

		$this->debug_messages[] = 'Creating tables...';

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		foreach ( $this->tables as $table_name => $create_sql ) {
			$this->debug_messages[] = 'Creating table ' . $wpdb->$table_name . '...';
			$dbDeltaResponse        = dbDelta( $create_sql );
			foreach ( $dbDeltaResponse as $key => $dbDeltaResponseLine ) {
				$this->debug_messages[] = '[' . $key . '] ' . $dbDeltaResponseLine;
			}

			$table_check = $wpdb->get_var(
				$wpdb->prepare(
					'SHOW TABLES LIKE %s',
					$wpdb->$table_name
				)
			);

			if ( $table_check !== $wpdb->$table_name ) {
				$this->debug_messages[] = 'Creating table ' . $wpdb->$table_name . ' failed.';
			} else {
				$this->debug_messages[] = 'Table ' . $wpdb->$table_name . ' created.';
			}
		}

		for ( $i = $current_version + 1; $i <= $this->version; $i++ ) {
			$this->debug_messages[] = 'Triggering update to version ' . $i . '...';
			$this->debug_messages[] = 'do_action(\'apermo\custom_table\update\\' . $this->version_key . '\\' . $i . '\')';
			do_action( 'apermo\custom_table\update\\' . $this->version_key . '\\' . $i );
			$this->debug_messages[] = 'Updated to version ' . $i . '.';
		}

		update_option( $this->version_key, $this->version, false );

		$this->store_debug_messages();
	}

	/**
	 * Stores the debug messages.
	 *
	 * @since 1.0.0
	 */
	private function store_debug_messages(): void {
		if ( $this->debug === true ) {
			add_option( $this->version_key . '_debug_' . gmdate( 'Ymd-His' ), $this->debug_messages, '', 'no' );
		}
	}

	/**
	 * Clears all previously stored debug messages.
	 *
	 * @return int The number of deleted rows.
	 * @throws Exception If something went wrong while deleting debug messages.
	 * @since 1.0.0
	 */
	public function clear_debug_messages(): int {
		global $wpdb;
		$num_rows = $wpdb->delete( $wpdb->options, [ 'option_name' => $this->version_key . '_debug_%' ] );
		if ( $num_rows === false ) {
			throw new Exception( 'Something went wrong while deleting debug messages.' );
		}

		return $num_rows;
	}

	/**
	 * Drops a table.
	 *
	 * @param string $table_name The table name without prefix.
	 *
	 * @return bool If the table was dropped successfully.
	 * @throws Exception If something went wrong while dropping the table.
	 * @since 1.0.0
	 */
	public function drop_table( string $table_name ): bool {
		global $wpdb;
		$result = $wpdb->query(
			$wpdb->prepare(
				'DROP TABLE IF EXISTS %i',
				$wpdb->prefix . $table_name
			)
		);

		if ( $result === false ) {
			throw new Exception( 'Something went wrong while dropping table ' . esc_attr( $table_name ) . '.' );
		}

		return (bool) $wpdb->rows_affected;
	}
}
