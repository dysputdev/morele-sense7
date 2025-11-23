<?php
/**
 * Price History Database Table
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Database;

/**
 * Class Price_History_Table
 *
 * Handles creation and management of price history database table
 * Used for tracking product prices over time (Omnibus directive compliance)
 *
 * @since 1.0.0
 */
class Price_History_Table implements Table_Interface {

	/**
	 * Table name (without prefix)
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const TABLE_NAME = 'multistore_price_history';

	/**
	 * Database version for table schema
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Database version key stored in options.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION_OPTION = 'multistore_price_history_db_version';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_create_table' ) );
	}

	/**
	 * Get table name with prefix
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Check if table needs to be created or updated
	 *
	 * @since 1.0.0
	 */
	public function maybe_create_table(): void {
		$installed_version = get_option( self::DB_VERSION_OPTION );

		if ( $installed_version !== self::DB_VERSION ) {
			$this->create_table();
			update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
		}
	}

	/**
	 * Create price history table
	 *
	 * @since 1.0.0
	 */
	public function create_table(): void {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			product_id bigint(20) UNSIGNED NOT NULL,
			sku varchar(100) DEFAULT NULL,
			price decimal(10,2) NOT NULL,
			regular_price decimal(10,2) DEFAULT NULL,
			sale_price decimal(10,2) DEFAULT NULL,
			recorded_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY product_id (product_id),
			KEY sku (sku),
			KEY recorded_at (recorded_at),
			KEY product_date (product_id, recorded_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Drop table (use with caution)
	 *
	 * @since 1.0.0
	 */
	public static function drop_table(): void {
		global $wpdb;
		$table_name = self::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
		delete_option( self::DB_VERSION_OPTION );
	}
}
