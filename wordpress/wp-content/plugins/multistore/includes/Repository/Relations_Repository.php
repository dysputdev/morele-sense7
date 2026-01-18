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
	 * Get product SKU by ID
	 *
	 * @since 2.0.0
	 * @param int $product_id Product ID.
	 * @return string
	 */
	public function get_product_sku( int $product_id ): string {

		// Check cache first.
		if ( isset( self::$cache['id_to_sku'][ $product_id ] ) ) {
			return self::$cache['id_to_sku'][ $product_id ];
		}

		$product = wc_get_product( $product_id );
		$sku = $product ? $product->get_sku() : '';

		// Cache the result.
		self::$cache['id_to_sku'][ $product_id ] = $sku;

		return $sku;
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

		// global $wpdb;
		// $product_id = $wpdb->get_var(
		// 	$wpdb->prepare(
		// 		"SELECT post_id FROM {$wpdb->postmeta}
		// 		WHERE meta_key = '_sku' AND meta_value = %s
		// 		LIMIT 1",
		// 		$sku
		// 	)
		// );

		$product_id = wc_get_product_id_by_sku( $sku );
		$product_id = $product_id ? (int) $product_id : 0;

		// Cache the result.
		self::$cache['sku_to_id'][ $sku ] = $product_id;

		return $product_id;
	}

	/**
	 * Get product relations
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return array
	 */
	public function get_product_relations( int $product_id ): array {

		if ( isset( self::$cache['product_relations'][ $product_id ] ) ) {
			return self::$cache['product_relations'][ $product_id ];
		}

		$product_sku = $this->get_product_sku( $product_id );

		if ( empty( $product_sku ) ) {
			return array();
		}

		global $wpdb;
		$table_name = Product_Relations_Table::get_table_name();

		$relations = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE product_sku = %s ORDER BY sort_order ASC",
				$product_sku
			)
		);

		// Group by group_id and resolve SKU to current language product ID.
		$grouped = array();
		if ( $relations ) {
			foreach ( $relations as $relation ) {
				// Resolve related product SKU to ID in current language.
				$relation->related_product_id = $this->get_product_id_by_sku( $relation->related_product_sku );

				// Skip if product not found.
				if ( 0 === $relation->related_product_id ) {
					continue;
				}

				$grouped[ $relation->group_id ][] = $relation;
			}
		}

		// Cache the result.
		self::$cache['product_relations'][ $product_id ] = $grouped;

		return $grouped;
	}

	public function create_relation( string $product_sku, string $related_product_sku, int $group_id, int $settings_id = 0, int $sort_order = 0 ): void {
		global $wpdb;
		$relations_table = Product_Relations_Table::get_table_name();

		// Insert relation: product_sku_1 -> product_sku_2.
		$wpdb->insert(
			$relations_table,
			array(
				'product_sku'         => $product_sku,
				'related_product_sku' => $related_product_sku,
				'group_id'            => $group_id,
				'settings_id'         => $settings_id > 0 ? $settings_id : null,
				'sort_order'          => $sort_order,
			),
			array( '%s', '%s', '%d', '%d', '%d' )
		);
	}

	/**
	 * Create bidirectional relation by SKU
	 *
	 * @since 2.0.0
	 * @param string $product_sku         Product SKU 1.
	 * @param string $related_product_sku Product SKU 2.
	 * @param int    $group_id            Group ID.
	 * @param int    $settings_id         Settings ID.
	 * @param int    $sort_order          Sort order.
	 */
	public function create_bidirectional_relation( string $product_sku, string $related_product_sku, int $group_id, int $settings_id = 0, int $sort_order = 0 ): void {
		global $wpdb;
		$relations_table = Product_Relations_Table::get_table_name();

		// Insert relation: product_sku_1 -> product_sku_2.
		$this->create_relation( $product_sku, $related_product_sku, $group_id, $settings_id, $sort_order );

		// Insert relation: product_sku_2 -> product_sku_1.
		$this->create_relation( $related_product_sku, $product_sku, $group_id, $settings_id, $sort_order );
	}

	/**
	 * Remove bidirectional relation by SKU
	 *
	 * @since 2.0.0
	 * @param string $product_sku Product SKU 1.
	 * @param string $related_product_sku Product SKU 2.
	 * @param int    $group_id      Group ID.
	 */
	public function remove_bidirectional_relation( string $product_sku, string $related_product_sku, int $group_id ): void {
		global $wpdb;
		$relations_table = Product_Relations_Table::get_table_name();

		// Remove both directions using SKU.
		$wpdb->delete(
			$relations_table,
			array(
				'product_sku'         => $product_sku,
				'related_product_sku' => $related_product_sku,
				'group_id'            => $group_id,
			),
			array( '%s', '%s', '%d' )
		);

		$wpdb->delete(
			$relations_table,
			array(
				'product_sku'         => $related_product_sku,
				'related_product_sku' => $product_sku,
				'group_id'            => $group_id,
			),
			array( '%s', '%s', '%d' )
		);
	}

	/**
	 * Create relation settings
	 *
	 * @since 1.0.0
	 * @param array $settings_data Settings data.
	 * @return int Settings ID.
	 */
	public function create_relation_settings( array $settings_data ): int {
		global $wpdb;
		$settings_table = Product_Relation_Settings_Table::get_table_name();

		$wpdb->insert(
			$settings_table,
			array( 'settings' => wp_json_encode( $settings_data ) ),
			array( '%s' )
		);

		return $wpdb->insert_id;
	}

	/**
	 * Update relation settings
	 *
	 * @since 1.0.0
	 * @param int   $settings_id   Settings ID.
	 * @param array $settings_data Settings data.
	 */
	public function update_relation_settings( int $settings_id, array $settings_data ): void {
		global $wpdb;
		$settings_table = Product_Relation_Settings_Table::get_table_name();

		$wpdb->update(
			$settings_table,
			array( 'settings' => wp_json_encode( $settings_data ) ),
			array( 'id' => $settings_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Get relation settings
	 *
	 * @since 1.0.0
	 * @param int $settings_id Settings ID.
	 * @return array
	 */
	public function get_relation_settings( int $settings_id ): array {
		if ( 0 === $settings_id ) {
			return array();
		}

		global $wpdb;
		$table_name = Product_Relation_Settings_Table::get_table_name();

		$settings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT settings FROM {$table_name} WHERE id = %d",
				$settings_id
			)
		);

		if ( ! $settings ) {
			return array();
		}

		$decoded = json_decode( $settings, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Get single group
	 *
	 * @since 1.0.0
	 * @param int $group_id Group ID.
	 * @return object|null
	 */
	public function get_group( int $group_id ) {
		global $wpdb;
		$table_name = Product_Relation_Groups_Table::get_table_name();

		$group = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d",
				$group_id
			)
		);

		return $group;
	}

	/**
	 * Get all relation groups
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_all_groups(): array {
		global $wpdb;
		$table_name = Product_Relation_Groups_Table::get_table_name();

		$groups = $wpdb->get_results(
			"SELECT * FROM {$table_name} ORDER BY sort_order ASC, name ASC"
		);

		return $groups ? $groups : array();
	}

	/**
	 * Get product attribute values
	 *
	 * @since 1.0.0
	 * @param int $product_id   Product ID.
	 * @param int $attribute_id Attribute ID.
	 * @return array
	 */
	public function get_product_attribute_values( int $product_id, int $attribute_id ): array {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return array();
		}

		global $wpdb;
		$attribute = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %d",
				$attribute_id
			)
		);

		if ( ! $attribute ) {
			return array();
		}

		$taxonomy = 'pa_' . $attribute->attribute_name;
		$terms    = wp_get_post_terms( $product_id, $taxonomy, array( 'fields' => 'names' ) );

		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Get product attributes
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_product_attributes(): array {
		global $wpdb;

		$attributes = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies ORDER BY attribute_name ASC"
		);

		return $attributes ? $attributes : array();
	}

	/**
	 * Get relation for unique product-related-group.
	 *
	 * @param string  $product_sku - Product SKU.
	 * @param string  $related_product_sku - Related product SKU.
	 * @param integer $group_id - Group ID.
	 * @return object|null
	 */
	public function get_relation( $product_sku, $related_product_sku, $group_id ) {
		global $wpdb;

		$table_name = Product_Relations_Table::get_table_name();

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE product_sku = %s AND related_product_sku = %s AND group_id = %d",
				$product_sku,
				$related_product_sku,
				$group_id
			)
		);
	}

	public function get_all_related_products_sku( $product_sku ) {
		global $wpdb;

		$relations_table = Product_Relations_Table::get_table_name();

		$cache_key = md5( 'all_related_products_sku_' . $product_sku );
		// wp_cache_get( $cache_key, 'multistore_relations' );

		$relations = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT related_product_sku
				FROM {$relations_table}
				WHERE product_sku IN (
					SELECT related_product_sku
					FROM {$relations_table}
					WHERE product_sku LIKE %s
				)
				GROUP BY related_product_sku",
				$product_sku
			),
		);

		// wp_cache_set( $cache_key, $relations, 'multistore_relations' );

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
		global $wpdb;
		$relations_table = Product_Relations_Table::get_table_name();

		$relations = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$relations_table} WHERE product_sku = %s ORDER BY sort_order ASC",
				$product_sku
			),
			OBJECT_K
		);

		return $relations ? $relations : array();
	}

	/**
	 * Get current relations by related product sku
	 *
	 * @since 2.0.0
	 * @param string $product_sku Related Product SKU.
	 * @return array Relations indexed by ID.
	 */
	public function get_relations_by_related_sku( string $product_sku, int $group_id ): array {
		global $wpdb;

		$relations_table = Product_Relations_Table::get_table_name();

		$relations = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, product_sku, related_product_sku, group_id, settings_id, sort_order
					FROM {$relations_table} 
					WHERE related_product_sku = %s AND group_id = %d",
				$product_sku,
				$group_id
			),
			OBJECT_K
		);

		return $relations ? $relations : array();
	}

	/**
	 * Update relation sort order and settings
	 *
	 * @since 2.0.0
	 * @param int $relation_id Relation ID.
	 * @param int $sort_order  Sort order.
	 * @param int $settings_id Settings ID.
	 * @return bool True on success, false on failure.
	 */
	public function update_relation( int $relation_id, int $sort_order, int $settings_id ): bool {
		global $wpdb;
		$relations_table = Product_Relations_Table::get_table_name();

		$result = $wpdb->update(
			$relations_table,
			array(
				'sort_order'  => $sort_order,
				'settings_id' => $settings_id > 0 ? $settings_id : null,
			),
			array( 'id' => $relation_id ),
			array( '%d', '%d' ),
			array( '%d' )
		);

		return false !== $result;
	}

	public function get_product_relations_by_skus( array $product_skus ): array {
		if ( empty( $product_skus ) ) {
			return array();
		}

		global $wpdb;

		$relations_table = Product_Relations_Table::get_table_name();
		$groups_table    = Product_Relation_Groups_Table::get_table_name();
		$settings_table  = Product_Relation_Settings_Table::get_table_name();

		$placeholders = implode( ',', array_fill( 0, count( $product_skus ), '%s' ) );

		$query = $wpdb->prepare(
			"SELECT
				r.id as relation_id,
				r.product_sku,
				r.related_product_sku,
				r.group_id,
				r.settings_id,
				r.sort_order,
				g.name as group_name,
				g.attribute_id,
				g.display_style_single,
				g.display_style_archive,
				s.settings
			FROM {$relations_table} r
			INNER JOIN {$groups_table} g ON r.group_id = g.id
			LEFT JOIN {$settings_table} s ON r.settings_id = s.id
			WHERE r.product_sku IN ({$placeholders})
			ORDER BY g.sort_order ASC, r.sort_order ASC",
			$product_skus
		);

		$results = $wpdb->get_results( $query );

		if ( ! $results ) {
			return array();
		}

		return $results;
	}

	/**
	 * Get product relations filtered by context (single or archive)
	 *
	 * @since 1.0.0
	 * @param int    $product_id Product ID.
	 * @param string $context    Context: 'single' or 'archive'.
	 * @return array Grouped relations data.
	 */
	public function get_product_relations_by_context( int $product_id, string $context = 'single' ): array {
		$product_sku = $this->get_product_sku( $product_id );

		if ( empty( $product_sku ) ) {
			return array();
		}

		global $wpdb;

		$relations_table = Product_Relations_Table::get_table_name();
		$groups_table    = Product_Relation_Groups_Table::get_table_name();
		$settings_table  = Product_Relation_Settings_Table::get_table_name();

		// Build query based on context.
		$where_clause = 'r.product_sku = %s';

		if ( 'archive' === $context ) {
			// Only show groups marked for display on list.
			$where_clause .= ' AND g.display_on_list = 1';
		}

		$query = $wpdb->prepare(
			"SELECT
				r.id as relation_id,
				r.product_sku,
				r.related_product_sku,
				r.group_id,
				r.settings_id,
				r.sort_order,
				g.name as group_name,
				g.attribute_id,
				g.display_style_single,
				g.display_style_archive,
				s.settings
			FROM {$relations_table} r
			INNER JOIN {$groups_table} g ON r.group_id = g.id
			LEFT JOIN {$settings_table} s ON r.settings_id = s.id
			WHERE {$where_clause}
			ORDER BY g.sort_order ASC, r.sort_order ASC",
			$product_sku
		);

		$results = $wpdb->get_results( $query );

		if ( ! $results ) {
			return array();
		}

		return $results;
	}
	/**
	 * Get product relations filtered by context (single or archive)
	 *
	 * @since 1.0.0
	 * @param int    $product_id Product ID.
	 * @param string $context    Context: 'single' or 'archive'.
	 * @return array Grouped relations data.
	 */
	public function get_grouped_product_relations_by_context( int $product_id, string $context = 'single' ): array {
		$results = $this->get_product_relations_by_context( $product_id, $context );

		if ( ! $results ) {
			return array();
		}

		// Group by group_id.
		$grouped = array();

		foreach ( $results as $row ) {
			if ( ! isset( $grouped[ $row->group_id ] ) ) {
				$grouped[ $row->group_id ] = array(
					'group_id'     => $row->group_id,
					'group_name'   => $row->group_name,
					'attribute_id' => $row->attribute_id,
					'layout'       => 'single' === $context ? $row->display_style_single : $row->display_style_archive,
					'relations'    => array(),
				);
			}

			$settings = array();
			if ( $row->settings ) {
				$decoded  = json_decode( $row->settings, true );
				$settings = is_array( $decoded ) ? $decoded : array();
			}

			$related_product = $row->related_product_sku;

			$related_product_id = $this->get_product_id_by_sku( $related_product );
			if ( $related_product_id ) {
				$relation = array_merge(
					array(
						'relation_id' => $row->relation_id,
						'product_id'  => $this->get_product_id_by_sku( $related_product ),
						'product_sku' => $row->related_product_sku,
					),
					$settings
				);

				$grouped[ $row->group_id ]['relations'][] = $relation;
			}
		}

		return $grouped;
	}

	public function get_product_relation_map_by_context( $product_id, $context = 'single' ) {
		$product_sku = $this->get_product_sku( $product_id );

		if ( empty( $product_sku ) ) {
			return array();
		}

		global $wpdb;

		$relations_table = Product_Relations_Table::get_table_name();
		$groups_table    = Product_Relation_Groups_Table::get_table_name();
		$settings_table  = Product_Relation_Settings_Table::get_table_name();

		$query = $wpdb->prepare(
			"SELECT
				r.id as relation_id,
				r.product_sku,
				r.related_product_sku,
				r.group_id,
				r.settings_id,
				r.sort_order,
				g.name as group_name,
				g.attribute_id,
				g.display_style_single,
				g.display_style_archive,
				s.settings
			FROM {$relations_table} r
			INNER JOIN {$groups_table} g ON r.group_id = g.id
			LEFT JOIN {$settings_table} s ON r.settings_id = s.id
			WHERE r.product_sku IN (
				SELECT related_product_sku FROM {$relations_table} WHERE product_sku = %s OR related_product_sku = %s
			)
			ORDER BY g.sort_order ASC, r.sort_order ASC",
			$product_sku,
			$product_sku
		);

		$results = $wpdb->get_results( $query );
		if ( ! $results ) {
			return array();
		}

		return $results;
	}
}
