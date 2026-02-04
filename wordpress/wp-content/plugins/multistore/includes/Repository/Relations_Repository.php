<?php
/**
 * Product Relations Manager
 *
 * Manages CRUD operations for product relations
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Repository;

use MultiStore\Plugin\Database\Product_Relation_Groups_Table;
use MultiStore\Plugin\Database\Product_Relations_Table;
use MultiStore\Plugin\Database\Product_Relation_Settings_Table;

/**
 * Class Relations_Repository
 *
 * Handles CRUD operations for product relations
 *
 * @since 1.0.0
 */
class Relations_Repository {

	/**
	 * Database connection
	 *
	 * @var object
	 */
	private $wpdb;

	/**
	 * In-memory cache for batch operations
	 *
	 * @var array
	 */
	private static $cache = array(
		'sku_to_id'         => array(),
		'id_to_sku'         => array(),
		'product_relations' => array(),
		'batch_relations'   => array(),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * Clear cache
	 *
	 * @since 2.0.0
	 */
	public static function clear_cache(): void {
		self::$cache = array(
			'sku_to_id'         => array(),
			'id_to_sku'         => array(),
			'product_relations' => array(),
			'batch_relations'   => array(),
		);
	}

	/**
	 * Get all relation groups
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_all_groups(): array {
		$table_name = Product_Relation_Groups_Table::get_table_name();

		$groups = $this->wpdb->get_results(
			"SELECT * FROM {$table_name} ORDER BY sort_order ASC, name ASC"
		);

		return $groups ? $groups : array();
	}

	/**
	 * Get product ID by SKU (in current language)
	 *
	 * @since 2.0.0
	 * @param string $sku Product SKU.
	 * @return int
	 */
	public function get_product_id_by_sku( string $sku ): int {
		if ( empty( $sku ) ) {
			return 0;
		}

		// Check cache first.
		if ( isset( self::$cache['sku_to_id'][ $sku ] ) ) {
			return self::$cache['sku_to_id'][ $sku ];
		}

		$product_id = wc_get_product_id_by_sku( $sku );
		$product_id = $product_id ? (int) $product_id : 0;

		// Cache the result.
		self::$cache['sku_to_id'][ $sku ] = $product_id;

		return $product_id;
	}

	/**
	 * Get current relations from database
	 *
	 * @param integer $product_id Product ID.
	 * @return array
	 */
	public function get_relations( int $product_id ): array {
		$relations_table = Product_Relations_Table::get_table_name();

		$relations = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$relations_table} WHERE product_id = %d ORDER BY sort_order ASC",
				(int) $product_id
			),
			OBJECT_K
		);

		return $relations ? $relations : array();
	}

	public function get_product_relations( int $product_id, ?string $context = 'single' ): array {
		global $wpdb;

		$context_where = ( 'archive' === $context ) ? ' AND g.display_on_list = 1' : '';

		$relations_table = Product_Relations_Table::get_table_name();
		$groups_table    = Product_Relation_Groups_Table::get_table_name();
		$settings_table  = Product_Relation_Settings_Table::get_table_name();

		$relations = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT DISTINCT
					r.id as relation_id,
					r.product_id,
					r.related_product_id,
					p.post_title,
					r.product_group_id,
					r.group_id,
					r.settings_id,
					r.sort_order,
					g.name as group_name,
					g.attribute_id,
					g.display_on_list,
					g.display_style_single,
					g.display_style_archive,
					s.settings
				FROM {$relations_table} r
				INNER JOIN {$wpdb->prefix}posts p ON r.product_id = p.ID
				INNER JOIN {$groups_table} g ON r.group_id = g.id
				LEFT JOIN {$settings_table} s ON r.settings_id = s.id
				WHERE r.related_product_id = %d {$context_where}
				ORDER BY g.sort_order ASC, r.sort_order ASC",
				$product_id
			),
			OBJECT_K
		);

		return $relations ? $relations : array();
	}

	/**
	 * Get current relations from database by SKU
	 *
	 * @since 2.0.0
	 * @param string $product_sku Product SKU.
	 * @return array Relations indexed by ID.
	 */
	public function get_relations_by_sku( string $product_sku ): array {
		$product_id = $this->get_product_id_by_sku( $product_sku );
		if ( ! $product_id ) {
			return array();
		}

		return $this->get_relations( $product_id );
	}

	/**
	 * Get all related relations
	 *
	 * @param integer $product_id Product ID.
	 * @param integer $group_id Group ID.
	 * @return array
	 */
	public function get_related_relations( int $product_id, int $group_id ): array {
		$relations_table = Product_Relations_Table::get_table_name();

		$relations = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$relations_table}
				WHERE related_product_id = %d AND group_id = %d 
				ORDER BY sort_order ASC",
				(int) $product_id,
				(int) $group_id
			),
			OBJECT_K
		);

		return $relations ? $relations : array();
	}

	/**
	 * Get all related relations by SKU.
	 *
	 * @param string  $product_sku Product SKU.
	 * @param integer $group_id Group ID.
	 * @return array
	 */
	public function get_related_relations_by_sku( string $product_sku, int $group_id ): array {
		$product_id = $this->get_product_id_by_sku( $product_sku );
		if ( ! $product_id ) {
			return array();
		}

		return $this->get_related_relations( $product_id, $group_id );
	}

	/**
	 * Create relation settings
	 *
	 * @since 1.0.0
	 * @param array $settings_data Settings data.
	 * @return int Settings ID.
	 */
	public function create_relation_settings( array $settings_data ): int {
		$settings_table = Product_Relation_Settings_Table::get_table_name();

		$this->wpdb->insert(
			$settings_table,
			array( 'settings' => wp_json_encode( $settings_data ) ),
			array( '%s' )
		);

		return $this->wpdb->insert_id;
	}

	/**
	 * Get product group ID for a product
	 *
	 * @since 2.2.0
	 * @param int $product_id Product ID.
	 * @return int|null Product group ID or null if not found.
	 */
	public function get_product_group_id( int $product_id ) {
		$relations_table = Product_Relations_Table::get_table_name();

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT product_group_id FROM {$relations_table}
				WHERE product_id = %d AND product_group_id IS NOT NULL
				LIMIT 1",
				$product_id
			)
		);

		return $result ? (int) $result : null;
	}

	/**
	 * Generate new unique product group ID
	 *
	 * @since 2.2.0
	 * @return int New product group ID.
	 */
	public function generate_product_group_id(): int {
		$relations_table = Product_Relations_Table::get_table_name();

		$max_id = $this->wpdb->get_var(
			"SELECT MAX(product_group_id) FROM {$relations_table}"
		);

		return $max_id ? (int) $max_id + 1 : 1;
	}

	/**
	 * Merge two product groups into one
	 *
	 * @since 2.2.0
	 * @param int $from_group_id Source group ID.
	 * @param int $to_group_id Target group ID.
	 * @return bool Success.
	 */
	public function merge_product_groups( int $from_group_id, int $to_group_id ): bool {
		$relations_table = Product_Relations_Table::get_table_name();

		$result = $this->wpdb->update(
			$relations_table,
			array( 'product_group_id' => $to_group_id ),
			array( 'product_group_id' => $from_group_id ),
			array( '%d' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Get or create product group ID for a relation
	 *
	 * @since 2.2.0
	 * @param int $product_id Product ID.
	 * @param int $related_product_id Related product ID.
	 * @return int Product group ID.
	 */
	public function get_or_create_product_group_id( int $product_id, int $related_product_id ): int {
		$product_group_id         = $this->get_product_group_id( $product_id );
		$related_product_group_id = $this->get_product_group_id( $related_product_id );

		// Both products already in the same group.
		if ( $product_group_id && $related_product_group_id && $product_group_id === $related_product_group_id ) {
			return $product_group_id;
		}

		// Both products in different groups - merge them.
		if ( $product_group_id && $related_product_group_id && $product_group_id !== $related_product_group_id ) {
			// Use the smaller ID as the target.
			$target_group_id = min( $product_group_id, $related_product_group_id );
			$source_group_id = max( $product_group_id, $related_product_group_id );
			$this->merge_product_groups( $source_group_id, $target_group_id );
			return $target_group_id;
		}

		// One product has a group, use it.
		if ( $product_group_id ) {
			return $product_group_id;
		}

		if ( $related_product_group_id ) {
			return $related_product_group_id;
		}

		// Neither product has a group, generate new one.
		return $this->generate_product_group_id();
	}

	/**
	 * Create relation.
	 *
	 * @param integer $product_id - Product ID.
	 * @param integer $related_product_id - Related product ID.
	 * @param integer $group_id - Group ID.
	 * @param integer $settings_id - Settings ID.
	 * @param integer $sort_order - Sort order.
	 * @param integer $product_group_id - Product group ID (optional, will be auto-generated if not provided).
	 * @return bool
	 */
	public function create_relation( int $product_id, int $related_product_id, int $group_id, int $settings_id = 0, int $sort_order = 0, int $product_group_id = 0 ): bool {
		$relations_table = Product_Relations_Table::get_table_name();

		// Auto-generate product_group_id if not provided.
		if ( 0 === $product_group_id ) {
			$product_group_id = $this->get_or_create_product_group_id( $product_id, $related_product_id );
		}

		$insert = $this->wpdb->insert(
			$relations_table,
			array(
				'product_id'         => $product_id,
				'related_product_id' => $related_product_id,
				'product_group_id'   => $product_group_id,
				'group_id'           => $group_id,
				'settings_id'        => $settings_id > 0 ? $settings_id : null,
				'sort_order'         => $sort_order,
			),
			array( '%d', '%d', '%d', '%d', '%d', '%d' )
		);

		return $insert;
	}

	/**
	 * Create relation by SKU.
	 *
	 * @param string  $product_sku - Product SKU.
	 * @param string  $related_product_sku - Related product SKU.
	 * @param integer $group_id - Group ID.
	 * @param integer $settings_id - Settings ID.
	 * @param integer $sort_order - Sort order.
	 * @param integer $product_group_id - Product group ID (optional).
	 * @return boolean
	 */
	public function create_relation_by_sku( string $product_sku, string $related_product_sku, int $group_id, int $settings_id = 0, int $sort_order = 0, int $product_group_id = 0 ): bool {
		$product_id         = $this->get_product_id_by_sku( $product_sku );
		$related_product_id = $this->get_product_id_by_sku( $related_product_sku );

		return $this->create_relation( $product_id, $related_product_id, $group_id, $settings_id, $sort_order, $product_group_id );
	}


	/**
	 * Get all products in the same group as given product
	 * Uses product_group_id for fast lookup
	 *
	 * @since 2.2.0
	 * @param int $product_id Product ID.
	 * @return array Array of product IDs.
	 */
	public function get_products_in_group( int $product_id ): array {
		$product_group_id = $this->get_product_group_id( $product_id );

		if ( ! $product_group_id ) {
			return array( $product_id );
		}

		$relations_table = Product_Relations_Table::get_table_name();

		$product_ids = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT DISTINCT product_id
				FROM {$relations_table}
				WHERE product_group_id = %d
				UNION
				SELECT DISTINCT related_product_id
				FROM {$relations_table}
				WHERE product_group_id = %d",
				$product_group_id,
				$product_group_id
			)
		);

		return $product_ids ? array_map( 'intval', $product_ids ) : array( $product_id );
	}

	/**
	 * Get all related products (legacy method using recursive CTE)
	 * @deprecated Use get_products_in_group() instead for better performance.
	 *
	 * @param int $product_id Product ID.
	 * @return array Array of product IDs.
	 */
	public function get_related_products( $product_id ) {
		// Use new method if product has product_group_id.
		$product_group_id = $this->get_product_group_id( $product_id );
		if ( $product_group_id ) {
			return $this->get_products_in_group( $product_id );
		}

		// Fallback to old recursive method for legacy data.
		$relations_table = Product_Relations_Table::get_table_name();

		global $wpdb;

		$cache_key        = 'related_products_by_id_' . $product_id;
		$related_products = wp_cache_get( $cache_key, 'multistore_relations' );
		if ( false === $related_products ) {
			$related_products = $wpdb->get_col(
				$wpdb->prepare(
					"WITH RECURSIVE product_group AS (

						-- 1. punkt startowy
						SELECT
							pr.product_id,
							pr.related_product_id
						FROM {$relations_table} pr
						WHERE pr.product_id = %d
						OR pr.related_product_id = %d

						UNION

						-- 2. rozszerzanie grafu
						SELECT
							pr.product_id,
							pr.related_product_id
						FROM {$relations_table} pr
						JOIN product_group rp
						ON pr.product_id = rp.related_product_id
						OR pr.related_product_id = rp.product_id
					)
					SELECT DISTINCT
						p_id AS product_id
					FROM (
						SELECT product_id AS p_id FROM product_group
						UNION
						SELECT related_product_id FROM product_group
						UNION
						SELECT %d
					) x",
					(int) $product_id,
					(int) $product_id,
					(int) $product_id
				)
			);
		}

		return $related_products;
	}

	/**
	 * Get full relations data for product group
	 *
	 * @since 2.2.0
	 * @param int $product_id Product ID.
	 * @return array Full relations data.
	 */
	public function get_full_relations_by_product_id( $product_id ) {
		global $wpdb;

		$relations_table = Product_Relations_Table::get_table_name();
		$groups_table    = Product_Relation_Groups_Table::get_table_name();
		$settings_table  = Product_Relation_Settings_Table::get_table_name();

		$product_group_id = $this->get_product_group_id( $product_id );

		if ( ! $product_group_id ) {
			return array();
		}

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT
					r.id as relation_id,
					r.product_id,
					r.related_product_id,
					p.post_title,
					r.product_group_id,
					r.group_id,
					r.settings_id,
					s.settings,
					g.name as group_name,
					g.attribute_id,
					g.display_on_list,
					g.display_style_single,
					g.display_style_archive,
					g.sort_order as group_sort_order,
					r.sort_order as relation_sort_order
				FROM {$relations_table} r
				INNER JOIN {$wpdb->prefix}posts p ON r.related_product_id = p.ID
				INNER JOIN {$groups_table} g ON r.group_id = g.id
				LEFT JOIN {$settings_table} s ON r.settings_id = s.id
				WHERE r.product_group_id = %d
				ORDER BY g.sort_order ASC, r.sort_order ASC",
				$product_group_id
			)
		);

		return $result;
	}

	/**
	 * Get product SKU by product ID
	 *
	 * @since 2.0.0
	 * @param int $product_id Product ID.
	 * @return string
	 */
	public function get_product_sku( int $product_id ): string {
		if ( empty( $product_id ) ) {
			return '';
		}

		// Check cache first.
		if ( isset( self::$cache['id_to_sku'][ $product_id ] ) ) {
			return self::$cache['id_to_sku'][ $product_id ];
		}

		$product = wc_get_product( $product_id );
		$sku     = $product ? $product->get_sku() : '';

		// Cache the result.
		self::$cache['id_to_sku'][ $product_id ] = $sku;

		return $sku;
	}

	/**
	 * Get all product attributes
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_product_attributes(): array {
		$attributes = wc_get_attribute_taxonomies();
		return $attributes ? $attributes : array();
	}

	/**
	 * Get product relations grouped by group_id
	 *
	 * @since 2.0.0
	 * @param int $product_id Product ID.
	 * @return array Relations grouped by group_id.
	 */
	public function get_grouped_product_relations( int $product_id ): array {
		$relations = $this->get_relations( $product_id );

		$grouped = array();
		foreach ( $relations as $relation ) {
			if ( ! isset( $grouped[ $relation->group_id ] ) ) {
				$grouped[ $relation->group_id ] = array();
			}
			$grouped[ $relation->group_id ][] = $relation;
		}

		return $grouped;
	}

	/**
	 * Get relation settings
	 *
	 * @since 2.0.0
	 * @param int $settings_id Settings ID.
	 * @return array
	 */
	public function get_relation_settings( int $settings_id ): array {
		if ( empty( $settings_id ) ) {
			return array();
		}

		$settings_table = Product_Relation_Settings_Table::get_table_name();

		$settings = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT settings FROM {$settings_table} WHERE id = %d",
				$settings_id
			)
		);

		return $settings ? json_decode( $settings, true ) : array();
	}

	/**
	 * Get group by ID
	 *
	 * @since 2.0.0
	 * @param int $group_id Group ID.
	 * @return object|null
	 */
	public function get_group( int $group_id ) {
		$groups_table = Product_Relation_Groups_Table::get_table_name();

		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$groups_table} WHERE id = %d",
				$group_id
			)
		);
	}

	/**
	 * Get product attribute values
	 *
	 * @since 2.0.0
	 * @param int $product_id Product ID.
	 * @param int $attribute_id Attribute ID.
	 * @return array
	 */
	public function get_product_attribute_values( int $product_id, int $attribute_id ): array {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return array();
		}

		$attribute_name = wc_attribute_taxonomy_name_by_id( $attribute_id );
		if ( ! $attribute_name ) {
			return array();
		}

		$attribute = $product->get_attribute( $attribute_name );
		if ( ! $attribute ) {
			return array();
		}

		return explode( ', ', $attribute );
	}

	/**
	 * Get relations by related product ID and group
	 *
	 * @since 2.0.0
	 * @param int $related_product_id Related product ID.
	 * @param int $group_id Group ID.
	 * @return array
	 */
	public function get_relations_by_related_id( int $related_product_id, int $group_id ): array {
		$relations_table = Product_Relations_Table::get_table_name();

		$relations = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$relations_table}
				WHERE related_product_id = %d AND group_id = %d
				ORDER BY sort_order ASC",
				$related_product_id,
				$group_id
			),
			OBJECT_K
		);

		return $relations ? $relations : array();
	}

	/**
	 * Get relations by related product SKU and group
	 *
	 * @since 2.0.0
	 * @param string  $related_product_sku Related product SKU.
	 * @param integer $group_id Group ID.
	 * @return array
	 */
	public function get_relations_by_related_sku( string $related_product_sku, int $group_id ): array {
		$related_product_id = $this->get_product_id_by_sku( $related_product_sku );
		if ( ! $related_product_id ) {
			return array();
		}

		return $this->get_relations_by_related_id( $related_product_id, $group_id );
	}

	/**
	 * Update relation settings
	 *
	 * @since 2.0.0
	 * @param int   $settings_id Settings ID.
	 * @param array $settings_data Settings data.
	 * @return bool
	 */
	public function update_relation_settings( int $settings_id, array $settings_data ): bool {
		if ( empty( $settings_id ) ) {
			return false;
		}

		$settings_table = Product_Relation_Settings_Table::get_table_name();

		$result = $this->wpdb->update(
			$settings_table,
			array( 'settings' => wp_json_encode( $settings_data ) ),
			array( 'id' => $settings_id ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Update relation
	 *
	 * @since 2.0.0
	 * @param int $relation_id Relation ID.
	 * @param int $sort_order Sort order.
	 * @param int $settings_id Settings ID.
	 * @return bool
	 */
	public function update_relation( int $relation_id, int $sort_order, int $settings_id = 0 ): bool {
		if ( empty( $relation_id ) ) {
			return false;
		}

		$relations_table = Product_Relations_Table::get_table_name();

		$data = array(
			'sort_order' => $sort_order,
		);

		$formats = array( '%d' );

		if ( $settings_id > 0 ) {
			$data['settings_id'] = $settings_id;
			$formats[]           = '%d';
		}

		$result = $this->wpdb->update(
			$relations_table,
			$data,
			array( 'id' => $relation_id ),
			$formats,
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Get relation by product IDs and group
	 *
	 * @since 2.0.0
	 * @param int $product_id Product ID.
	 * @param int $related_product_id Related product ID.
	 * @param int $group_id Group ID.
	 * @return object|null
	 */
	public function get_relation_by_ids( int $product_id, int $related_product_id, int $group_id ) {
		$relations_table = Product_Relations_Table::get_table_name();

		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$relations_table}
				WHERE product_id = %d AND related_product_id = %d AND group_id = %d",
				$product_id,
				$related_product_id,
				$group_id
			)
		);
	}

	/**
	 * Get relation by product SKUs and group
	 *
	 * @since 2.0.0
	 * @param string $product_sku Product SKU.
	 * @param string $related_product_sku Related product SKU.
	 * @param int    $group_id Group ID.
	 * @return object|null
	 */
	public function get_relation( string $product_sku, string $related_product_sku, int $group_id ) {
		$product_id         = $this->get_product_id_by_sku( $product_sku );
		$related_product_id = $this->get_product_id_by_sku( $related_product_sku );

		if ( ! $product_id || ! $related_product_id ) {
			return null;
		}

		return $this->get_relation_by_ids( $product_id, $related_product_id, $group_id );
	}

	/**
	 * Migrate legacy relations to use product_group_id
	 * Should be run once after adding product_group_id column
	 *
	 * @since 2.2.0
	 * @return array Migration statistics.
	 */
	public function migrate_product_group_ids(): array {
		$relations_table = Product_Relations_Table::get_table_name();

		// Get all relations without product_group_id.
		$relations = $this->wpdb->get_results(
			"SELECT DISTINCT product_id
			FROM {$relations_table}
			WHERE product_group_id IS NULL"
		);

		$processed_products = array();
		$groups_created     = 0;
		$relations_updated  = 0;

		foreach ( $relations as $relation ) {
			$product_id = (int) $relation->product_id;

			// Skip if already processed.
			if ( in_array( $product_id, $processed_products, true ) ) {
				continue;
			}

			// Get all related products using old recursive method.
			$related_product_ids = $this->get_related_products( $product_id );

			// Generate new product_group_id.
			$product_group_id = $this->generate_product_group_id();
			$groups_created++;

			// Update all relations for these products.
			foreach ( $related_product_ids as $related_id ) {
				$result = $this->wpdb->query(
					$this->wpdb->prepare(
						"UPDATE {$relations_table}
						SET product_group_id = %d
						WHERE (product_id = %d OR related_product_id = %d)
						AND product_group_id IS NULL",
						$product_group_id,
						$related_id,
						$related_id
					)
				);

				if ( $result ) {
					$relations_updated += $result;
				}

				$processed_products[] = $related_id;
			}
		}

		return array(
			'groups_created'     => $groups_created,
			'relations_updated'  => $relations_updated,
			'products_processed' => count( $processed_products ),
		);
	}
}
