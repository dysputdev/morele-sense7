<?php
/**
 * Product Relations Database Table
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Database;

/**
 * Class Product_Relations_Table
 *
 * Handles creation and management of product relations database table.
 * Stores bidirectional relationships between products within groups.
 *
 * When adding a relation between product A and B, both A→B and B→A
 * relations should be created with the same group_id and settings_id.
 *
 * @since 1.0.0
 */
class Product_Relations_Table implements Table_Interface {

	/**
	 * Table name (without prefix)
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const TABLE_NAME = 'multistore_product_relations';

	/**
	 * Database version for table schema
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION = '2.2.0';

	/**
	 * Database version key stored in options.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION_OPTION = 'multistore_product_relations_db_version';

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

		if ( self::DB_VERSION !== $installed_version ) {
			$this->create_table();
			update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
		}
	}

	/**
	 * Create product relations table
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
			related_product_id bigint(20) UNSIGNED NOT NULL,
			product_group_id bigint(20) UNSIGNED DEFAULT NULL,
			group_id int(11) UNSIGNED NOT NULL,
			settings_id int(11) UNSIGNED DEFAULT NULL,
			sort_order int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			KEY idx_product_id (product_id),
			KEY idx_related_product_id (related_product_id),
			KEY idx_product_group_id (product_group_id),
			KEY idx_group_id (group_id),
			KEY idx_settings_id (settings_id),
			KEY idx_product_id_group (product_id, group_id),
			KEY idx_sort_order (sort_order),
			UNIQUE KEY unique_relation_product (product_id, related_product_id, group_id)
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
