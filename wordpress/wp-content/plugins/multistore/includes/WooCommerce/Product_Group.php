<?php

namespace MultiStore\Plugin\WooCommerce;

use MultiStore\Plugin\Database\Product_Relations_Table;
use MultiStore\Plugin\Repository\Relations_Repository;
use MultiStore\Plugin\Utils\Helpers;
use MultiStore\Plugin\Utils\Price_History_Helpers;

class Product_Group {

	private $product_id;

	private $context;

	private $relations = array();

	private $groups = array();

	private $visible_product_ids = array();

	private $products = array();

	private $product_details = array();

	private $relation_map = array();

	private $relation_repository;

	private $grouping_enabled = false;

	private $currency_format = 'default';

	public function __construct( $product_id, $context = 'archive' ) {
		$this->product_id       = (int) $product_id;
		$this->grouping_enabled = Product_Grouping::is_grouping_enabled();
		$this->context          = $context;

		$this->relation_repository = new Relations_Repository();
		if ( 'archive' === $context && $this->grouping_enabled ) {
			$visible_product_ids = $this->get_queried_products();
			$this->set_available_products( $visible_product_ids );
			// $relations = $this->relation_repository->get_full_relations_by_product_id( $product_id, $context );

			$products = $this->relation_repository->get_products_in_group( $product_id );

			$this->relations = array();
			foreach ( $products as $product_id ) {
				$this->relations += $this->relation_repository->get_product_relations( $product_id, $context );
			}
		} else if ( 'single' === $context ) {
			$this->relations = $this->relation_repository->get_product_relations( $product_id, $context );
		}
	}

	public function set_available_products( $product_ids ) {
		$this->visible_product_ids = is_array( $product_ids ) ? $product_ids : array( $product_ids );

		return $this;
	}

	public function set_currency_format( $format ) {
		$this->currency_format = $format;
	}

	public function get_queried_products() {
		global $wpdb;

		$relation_table = Product_Relations_Table::get_table_name();
		$main_query     = \WC()->query->get_main_query();
		$product_query  = $main_query->request;

		// Remove LEFT JOIN and GROUP BY.
		$product_query = str_replace( "LEFT JOIN {$relation_table} as pr ON (wp_posts.ID = pr.product_id AND wp_posts.ID = pr.related_product_id)", '', $product_query );
		$product_query = str_replace( 'GROUP BY pr.product_group_id', 'GROUP BY wp_posts.ID', $product_query );

		$product_ids = $wpdb->get_col( $product_query, 0 ); // phpcs:ignore
		$product_ids = array_map( 'intval', $product_ids );

		return $product_ids;
	}

	public function get_relations() {
		return $this->relations;
	}

	public function get_group( $group_id ) {
		$groups = $this->get_groups();

		return isset( $groups[ $group_id ] ) ? $groups[ $group_id ] : null;
	}

	public function get_groups() {

		if ( ! empty( $this->groups ) ) {
			return $this->groups;
		}

		$groups = array();
		foreach ( $this->relations as $group ) {
			if ( 'archive' === $this->context && ! $group->display_on_list ) {
				continue;
			}

			$gid   = (int) $group->group_id;
			$pid   = (int) $group->product_id;
			$relid = (int) $group->related_product_id;

			if ( ! empty( $this->visible_product_ids ) && ! in_array( $pid, $this->visible_product_ids, true ) ) {
				continue;
			}

			if ( ! isset( $groups[ $gid ] ) ) {
				$groups[ $gid ] = array(
					'group_id'              => $gid,
					'group_name'            => $group->group_name,
					'attribute_id'          => (int) $group->attribute_id,
					'display_on_list'       => (bool) $group->display_on_list,
					'display_style_single'  => $group->display_style_single,
					'display_style_archive' => $group->display_style_archive,
					'layout'                => 'archive' === $this->context ? $group->display_style_archive : $group->display_style_archive,
					'relations'             => array(),
				);
			}

			if ( ! in_array( $pid, $groups[ $gid ]['relations'], true ) ) {
				$groups[ $gid ]['relations'][ $pid ] = $pid;
			}
		}

		$this->groups = $groups;

		return $this->groups;
	}

	public function get_relations_map() {

		if ( ! empty( $this->relation_map ) ) {
			return $this->relation_map;
		}

		$matrix = array();
		foreach ( $this->relations as $id => $product ) {
			if ( 'archive' === $this->context && ! $product->display_on_list ) {
				continue;
			}

			$gid   = (int) $product->group_id;
			$pid   = (int) $product->product_id;
			$relid = (int) $product->related_product_id;

			if ( ! isset( $matrix[ $pid ] ) ) {
				$matrix[ $pid ] = array(
					// 'prod' => '[' . $products[ $pid ]->sku . '] ' . $products[ $pid ]->post_title,
				);
			}

			if ( ! isset( $matrix[ $pid ][ $gid ] ) ) {
				$matrix[ $pid ][ $gid ] = array();
			}

			if ( ! in_array( $relid, $matrix[ $pid ][ $gid ], true ) ) {
				$matrix[ $pid ][ $gid ][] = $relid;
			}
		}

		$this->relation_map = $matrix;

		return $this->relation_map;
	}

	public function get_product_relations_map( $product_id, $group_id = null ) {
		$relation_map = $this->get_relations_map();

		if ( ! empty( $group_id ) ) {
			return isset( $relation_map[ $product_id ][ $group_id ] ) ? $relation_map[ $product_id ][ $group_id ] : array();
		}

		return isset( $relation_map[ $product_id ] ) ? $relation_map[ $product_id ] : array();
	}

	public function get_products() {

		if ( ! empty( $this->products ) ) {
			return $this->products;
		}

		$products = array();
		foreach ( $this->relations as $id => $product ) {
			if ( 'archive' === $this->context && ! $product->display_on_list ) {
				continue;
			}

			$pid      = (int) $product->product_id;
			$rid      = (int) $product->related_product_id;
			$gid      = (int) $product->group_id;
			$settings = $product->settings;

			if ( ! isset( $products[ $pid ] ) ) {
				$product->settings = array();
				$product->is_main  = has_term( 'main-product', 'product_grouping', $pid );
				$product->product  = wc_get_product( $pid );
				$products[ $pid ]  = $product;
			}

			if ( $pid === $rid && ! isset( $products[ $pid ]->settings[ $gid ] ) ) {
				$products[ $pid ]->settings[ $gid ] = json_decode( $settings, true );
			}
		}

		$this->products = $products;

		return $this->products;
	}

	public function get_product( $id ) {
		$products = $this->get_products();

		return isset( $products[ $id ] ) ? $products[ $id ] : null;
	}

	public function get_product_details( $product_id, $key = null ) {

		if ( isset( $this->product_details[ $product_id ] ) ) {
			if ( null === $key ) {
				return $this->product_details[ $product_id ];
			}

			return $this->product_details[ $product_id ][ $key ] ?? null;
		}

		$product = $this->get_product( $product_id );

		if ( ! $product ) {
			return null;
		}

		$current_price = $product->product->get_price();
		$regular_price = $product->product->get_regular_price();
		$is_promotion  = $product->product->is_on_sale();
		$lowest_price  = '';
		if ( $is_promotion ) {
			$lowest_price_data = Price_History_Helpers::get_lowest_price( $product->product_id );
			if ( $lowest_price_data && isset( $lowest_price_data['price'] ) ) {
				$lowest_price = (float) $lowest_price_data['price'];
			}
		}

		$this->product_details[ $product_id ] = array(
			// base data.
			'title'         => get_the_title( $product_id ),
			'simple_title'  => get_post_meta( $product_id, '_simplified_product_name', true ),
			'image_url'     => get_the_post_thumbnail_url( $product_id, 'listing' ),
			'image'         => get_the_post_thumbnail( $product_id, 'listing' ),
			'url'           => get_the_permalink( $product_id ),

			// price data.
			'current_price' => $current_price ? Helpers::format_price( $current_price, 'default' ) : '',
			'is_promotion'  => $is_promotion,
			'currency'      => get_woocommerce_currency(),
			'regular_price' => ( ! empty( $regular_price ) && $is_promotion ) ? Helpers::format_price( $regular_price, $this->currency_format ) : '',
			'lowest_price'  => ( ! empty( $lowest_price ) && $is_promotion ) ? Helpers::format_price( $lowest_price, $this->currency_format ) : '',
		);

		return $this->product_details[ $product_id ];
	}

	public function get_product_label( $product_id, $group_id ) {

		$product  = $this->get_product( $product_id );
		$settings = $product->settings[ $group_id ] ?? array();

		if ( ! empty( $settings['custom_label'] ) && 'custom' === $settings['label_source'] ) {
			$label = $settings['custom_label'];
		} else {
			$attribute_values = $this->relation_repository->get_product_attribute_values( $product_id, $product->attribute_id );
			$label            = ! empty( $attribute_values ) ? join( ',', $attribute_values ) : get_the_title( $product_id );
		}

		return $label;
	}

	public function get_product_swatch_image( $product_id, $group_id, $size = 'swatch', $attributes = array() ) {

		$product = $this->get_product( $product_id );
		$group   = $this->get_group( $group_id );
		if ( ! $product || ! $group ) {
			return '';
		}

		$settings = $product->settings[ $group_id ] ?? array();

		$image_id = 'image_custom' === $group['layout'] && ! empty( $settings['custom_image_id'] ) ? $settings['custom_image_id'] : 0;

		if ( $image_id ) {
			$image = wp_get_attachment_image( $image_id, $size, $attributes );
		} else {
			$image = $product->product->get_image( $size, $attributes );
		}

		return $image;
	}
}
