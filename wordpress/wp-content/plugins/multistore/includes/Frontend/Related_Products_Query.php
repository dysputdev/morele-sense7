<?php
/**
 * Related Products Query Modifier
 *
 * Modifies WP_Query for related products slider variation
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Frontend;

use MultiStore\Plugin\WooCommerce\Product_Variants;

/**
 * Class Related_Products_Query
 *
 * Modifies query to show only related products for the slider variation
 *
 * @since 1.0.0
 */
class Related_Products_Query {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'query_loop_block_query_vars', array( $this, 'modify_query' ), 10, 2 );
	}

	/**
	 * Modify query to show related products
	 *
	 * @since 1.0.0
	 * @param array    $query Query vars.
	 * @param WP_Block $block Block instance.
	 * @return array Modified query vars.
	 */
	public function modify_query( $query, $block ): array {
		// Check if this is our related products slider variation.
		if ( ! isset( $block->context['namespace'] ) || 'multistore/related-products-slider' !== $block->context['namespace'] ) {
			return $query;
		}

		// Detect product ID from context.
		$product_id = $this->get_product_id_from_context( $block );

		if ( ! $product_id ) {
			// No product context - return empty results.
			$query['post__in'] = array( 0 );
			return $query;
		}

		// Get related products.
		$variants_manager = new Product_Variants();
		$related_ids      = $variants_manager->get_all_related_products( $product_id );

		if ( empty( $related_ids ) ) {
			// No related products - return empty results.
			$query['post__in'] = array( 0 );
		} else {
			// Set query to show only related products.
			$query['post__in'] = $related_ids;
			// Preserve order of related products.
			$query['orderby'] = 'post__in';
		}

		return $query;
	}

	/**
	 * Get product ID from context
	 *
	 * @since 1.0.0
	 * @param WP_Block $block Block instance.
	 * @return int|null Product ID or null if not found.
	 */
	private function get_product_id_from_context( $block ): ?int {
		// Try to get from block context (if set explicitly).
		if ( isset( $block->context['postId'] ) ) {
			$post_id = absint( $block->context['postId'] );
			if ( 'product' === get_post_type( $post_id ) ) {
				return $post_id;
			}
		}

		// Try to get from current query (single product page).
		if ( is_singular( 'product' ) ) {
			return get_queried_object_id();
		}

		// Try global post.
		global $post;
		if ( $post && 'product' === $post->post_type ) {
			return $post->ID;
		}

		return null;
	}
}
