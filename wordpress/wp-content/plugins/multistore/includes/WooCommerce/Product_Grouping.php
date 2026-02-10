<?php
/**
 * Summary.
 *
 * Description.
 *
 * @since Version 3 digits
 */

namespace MultiStore\Plugin\WooCommerce;

use MultiStore\Plugin\Database\Product_Relations_Table;

class Product_Grouping {

	const TAXONOMY          = 'product_grouping';
	const MAIN_PRODUCT_TERM = 'main-product';

	private $parsed_block;

	public function __construct() {

		$this->add_grouping_taxonomy();

		// add_action( 'woocommerce_product_query', array( $this, 'group_products' ) );

		add_filter( 'posts_clauses', array( $this, 'group_products_query' ), 10, 2 );

		// Add checkbox to product general tab.
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_main_product_checkbox' ) );
		// Save checkbox value.
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_main_product_checkbox' ) );
	}

	public function group_products_query( $clauses, \WP_Query $query ) {

		if ( is_admin() ) {
			return $clauses;
		}
		if ( ! $query->is_main_query() ) {
			return $clauses;
		}
		if ( ! is_shop() && ! is_product_category() ) {
			return $clauses;
		}

		$term = get_term_by( 'slug', self::MAIN_PRODUCT_TERM, self::TAXONOMY );
		if ( ! $term ) {
			return $clauses;
		}

		$relation_table = Product_Relations_Table::get_table_name();

		if ( self::is_grouping_enabled() ) {
			$query->set( '_orig_join', $clauses['join'] );
			$query->set( '_orig_groupby', $clauses['groupby'] );

			$clauses['join'] .= "
				/* @wp:posts_clauses product_grouping_join BEGIN */
				LEFT JOIN {$relation_table} as pg_pr ON (wp_posts.ID = pg_pr.product_id AND wp_posts.ID = pg_pr.related_product_id)
				LEFT JOIN wp_term_relationships AS pg_main ON (wp_posts.ID = pg_main.object_id AND pg_main.term_taxonomy_id = ' . $term->term_id . ')
				/* @wp:posts_clauses product_grouping_join END */

			";

			$clauses['groupby'] = '/* @wp:posts_clauses product_grouping_groupby BEGIN */
			pg_pr.product_group_id
			/* @wp:posts_clauses product_grouping_groupby END */';

			$clauses['orderby'] = '
			/* @wp:posts_clauses product_grouping_orderby BEGIN */
			pg_main.object_id DESC, 
			/* @wp:posts_clauses product_grouping_orderby END */
			' . $clauses['orderby'];
		}

		remove_filter( 'posts_clauses', array( $this, 'group_products_query' ), 10, 2 );
		return $clauses;
	}

	public function group_products( \WP_Query $query ) {
		if (
			is_admin()
			&& ! $query->is_main_query()
			&& ! is_shop()
			&& ! is_product_category()
		) {
			return $query;
		}

		$tax_query = $query->get( 'tax_query' );
		if ( empty( $tax_query ) ) {
			$tax_query = array();
		}
		$tax_query[] = array(
			'taxonomy' => self::TAXONOMY,
			'field'    => 'slug',
			'terms'    => self::MAIN_PRODUCT_TERM,
			'operator' => 'IN',
		);

		$query->set( 'tax_query', $tax_query );

		return $query;
	}

	public function add_grouping_taxonomy() {
		register_taxonomy(
			self::TAXONOMY,
			'product',
			array(
				'label'             => 'Product grouping',
				'public'            => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'show_admin_column' => false,
				'hierarchical'      => false,
				'query_var'         => true,
				'rewrite'           => false,
			)
		);

		if ( ! term_exists( self::MAIN_PRODUCT_TERM, self::TAXONOMY ) ) {
			wp_insert_term(
				'Main Product',
				self::TAXONOMY,
				array(
					'slug' => self::MAIN_PRODUCT_TERM,
				)
			);
		}
	}

	public static function set_as_main_product( $product_id ) {
		wp_set_object_terms( $product_id, self::MAIN_PRODUCT_TERM, self::TAXONOMY );
	}

	public static function is_main_product( $product_id ) {
		$terms = wp_get_object_terms( $product_id, self::TAXONOMY );
		if ( empty( $terms ) ) {
			return false;
		}

		foreach ( $terms as $term ) {
			if ( self::MAIN_PRODUCT_TERM === $term->slug ) {
				return true;
			}
		}

		return false;
	}

	public static function remove_main_product_term( $product_id ) {
		wp_remove_object_terms( $product_id, self::MAIN_PRODUCT_TERM, self::TAXONOMY );
	}

	/**
	 * Add main product checkbox to general product data tab.
	 *
	 * @return void
	 */
	public function add_main_product_checkbox() {
		global $post;

		$is_main = self::is_main_product( $post->ID );

		woocommerce_wp_checkbox(
			array(
				'id'          => '_main_product',
				'label'       => __( 'Produkt główny', 'multistore' ),
				'description' => __( 'Zaznacz, jeśli ten produkt ma być widoczny na liście produktów', 'multistore' ),
				'value'       => $is_main ? 'yes' : 'no',
			)
		);
	}

	/**
	 * Save main product checkbox value.
	 *
	 * @param int $product_id Product ID.
	 * @return void
	 */
	public function save_main_product_checkbox( $product_id ) {
		$is_main = isset( $_POST['_main_product'] ) ? 'yes' : 'no';

		if ( 'yes' === $is_main ) {
			self::set_as_main_product( $product_id );
		} else {
			self::remove_main_product_term( $product_id );
		}
	}

	/**
	 * Check if product grouping is enabled based on cookie value.
	 *
	 * @return bool True if grouping is enabled, false otherwise.
	 */
	public static function is_grouping_enabled(): bool {
		// Check if cookie exists and get its value.
		if ( isset( $_COOKIE['multistore_product_grouping'] ) ) {
			return 'on' === $_COOKIE['multistore_product_grouping'];
		}

		// Default to enabled if cookie doesn't exist.
		return true;
	}
}
