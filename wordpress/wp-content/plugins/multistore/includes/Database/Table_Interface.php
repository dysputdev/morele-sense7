<?php
/**
 * Product Relations Database Table
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Database;

/**
 * Interface Table_Interface
 *
 * @since 1.0.0
 */
interface Table_Interface {

	/**
	 * Table name (without prefix)
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const TABLE_NAME = '';

	/**
	 * Database version for table schema
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION = '';

	/**
	 * Database version key stored in options.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION_OPTION = '';

	/**
	 * Get table name.
	 *
	 * @since 1.0.0
	 */
	public static function get_table_name(): string;

	/**
	 * Check if table needs to be created or updated
	 *
	 * @since 1.0.0
	 */
	public function maybe_create_table(): void;

	/**
	 * Create product relations table
	 *
	 * @since 1.0.0
	 */
	public function create_table(): void;

	/**
	 * Drop table (use with caution)
	 *
	 * @since 1.0.0
	 */
	public static function drop_table(): void;
}
