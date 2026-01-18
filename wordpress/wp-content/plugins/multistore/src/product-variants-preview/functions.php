<?php
/**
 * Product Variants Preview Block Functions
 *
 * @package MultiStore\Plugin\Block\ProductVariantsPreview
 */

namespace MultiStore\Plugin\Block\ProductVariantsPreview;

use MultiStore\Plugin\Repository\Relations_Repository;
use MultiStore\Plugin\Utils\Debug;

function get_product_relations_groups_opt( $id, $context = 'archive', &$relation_map = array(), &$groups = array() ) {

	$disabled = true;
	if ( $disabled ) {
		$groups_wip = get_product_relations_groups_wip( $id, $context, $relation_map, $groups );
		return $groups_wip;
	}

	$relation_repository = new Relations_Repository();
	$product_sku         = $relation_repository->get_product_sku( $id );
	if ( '13147303' !== $product_sku ) {
		return array();
	}

	// get base relations for the product, to generate relation map based on skus.
	// get all products related to this product sku.
	$products_sku = $relation_repository->get_all_related_products_sku( $product_sku );

	$relation_map = array();
	$groups       = array();
	$product_skus = array();
	$relations    = $relation_repository->get_product_relations_by_skus( $products_sku );

	$groups = array();
	foreach ( $relations as $relation ) {
		$group_id            = $relation->group_id;
		$product_sku         = $relation->product_sku;
		$related_product_sku = $relation->related_product_sku;

		if ( ! isset( $groups[ $relation->group_id ] ) ) {
			$groups[ $relation->group_id ] = array(
				'group_id'     => $relation->group_id,
				'group_name'   => $relation->group_name,
				'attribute_id' => $relation->attribute_id,
				'layout'       => $relation->display_style_archive,
				'relations'    => array(),
			);

			if ( ! isset( $groups[ $group_id ]['relations'][ $product_sku ] ) ) {
				$groups[ $group_id ]['relations'][ $product_sku ] = array();
			}

			$groups[ $group_id ]['relations'][ $product_sku ][] = $relation;
		}
	}

	Debug::dump( $groups );

	$old_version = get_product_relations_groups_wip( $id, $context, $relation_map, $groups );
	// Debug::dd( $old_version );
	return $groups;
}

function get_product_relations_groups_wip( $id, $context = 'archive', &$relation_map = array(), &$groups = array() ) {
	$relation_repository = new Relations_Repository();
	$relations           = $relation_repository->get_product_relations_by_context( $id, $context );
	if ( empty( $relations ) ) {
		return array();
	}

	$relation_map = array();
	$groups       = array();
	$product_skus = array();
	foreach ( $relations as $relation ) {
		$related_product_id = $relation_repository->get_product_id_by_sku( $relation->related_product_sku );

		$related_relations = $relation_repository->get_product_relations_by_context( $related_product_id, $context );
		foreach ( $related_relations as $related_relation ) {
			$group_id            = $related_relation->group_id;
			$product_sku         = $related_relation->product_sku;
			$related_product_sku = $related_relation->related_product_sku;
			$group_id            = $related_relation->group_id;
			// $product             = wc_get_product( $product_id );

			$product_skus[] = $product_sku;
			$product_skus[] = $related_product_sku;

			if ( ! isset( $relation_map[ $related_product_sku ] ) ) {
				$relation_map[ $related_product_sku ] = array();
			}

			$relation_map[ $related_product_sku ][ $group_id ] = $product_sku;

			if ( ! isset( $groups[ $group_id ] ) ) {
				$groups[ $group_id ] = array(
					'group_id'     => $group_id,
					'group_name'   => $related_relation->group_name,
					'attribute_id' => $related_relation->attribute_id,
					'layout'       => $related_relation->display_style_archive,
					'relations'    => array(),
				);
			}

			if ( ! isset( $groups[ $group_id ]['relations'][ $related_product_sku ] ) ) {
				$product_id        = $relation_repository->get_product_id_by_sku( $related_product_sku );
				$product_relations = $relation_repository->get_product_relations( $product_id );
				$relation_matrix   = array();
				foreach ( $product_relations as $group_relation_id => $group_relations ) {
					$relation_matrix[ $group_relation_id ] = array_map(
						function ( $group_relation ) {
							return $group_relation->related_product_sku;
						},
						$group_relations
					);
				}

				$data = array(
					'product_id'    => $product_id,
					'product_title' => get_the_title( $product_id ),
					'product_url'   => get_permalink( $product_id ),
					'product_sku'   => $related_product_sku,
					'settings_id'   => $related_relation->settings_id,
					'sort_order'    => $related_relation->sort_order,
					'relations'     => $relation_matrix,

					// 'price'         => get_post_meta( $product_id, '_price', true ),
					// 'regular_price' => get_post_meta( $product_id, '_regular_price', true ),
					// 'sale_price'    => get_post_meta( $product_id, '_sale_price', true ),
					// 'lowest_price'  => get_post_meta( $product_id, '_lowes_price', true ),
					// 'image_id'      => get_post_thumbnail_id( $product_id ),
				);
				
				$groups[ $group_id ]['relations'][ $related_product_sku ] = array_merge( $data, json_decode( $related_relation->settings, true ) );
			}
		}
	}

	foreach ( $groups as $group_id => $group ) {
		foreach ( $group['relations'] as $related_product_sku => $relation ) {
			$groups[ $group_id ]['relations'][ $related_product_sku ]['dupa'] = 'test';
		}
	}

	return $groups;
}

function get_product_relations_groups( $id, $context = 'archive', &$relation_map = array(), &$groups = array() ) {
	if ( '89.64.248.20' === $_SERVER['REMOTE_ADDR'] ) {
		return get_product_relations_groups_opt( $id, $context, $relation_map, $groups );
	}

	return get_product_relations_groups_wip( $id, $context, $relation_map, $groups );
}
