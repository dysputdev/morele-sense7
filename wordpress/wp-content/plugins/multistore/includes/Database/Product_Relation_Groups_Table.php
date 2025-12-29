<?php
/**
 * Product Relation Groups Database Table
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Database;

/**
 * Class Product_Relation_Groups_Table
 *
 * Handles creation and management of product relation groups database table.
 * Stores groups that categorize product relations by attributes.
 *
 * @since 1.0.0
 */
class Product_Relation_Groups_Table implements Table_Interface {

	/**
	 * Table name (without prefix)
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const TABLE_NAME = 'multistore_product_relation_groups';

	/**
	 * Database version for table schema
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION = '1.1.0';

	/**
	 * Database version key stored in options.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION_OPTION = 'multistore_product_relation_groups_db_version';

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
	 * Create product relation groups table
	 *
	 * @since 1.0.0
	 */
	public function create_table(): void {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			attribute_id bigint(20) UNSIGNED DEFAULT NULL,
			display_on_list tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
			display_style_single varchar(50) DEFAULT 'image_product',
			display_style_archive varchar(50) DEFAULT 'image_product',
			sort_order int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY attribute_id (attribute_id),
			KEY sort_order (sort_order),
			KEY display_on_list (display_on_list)
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
