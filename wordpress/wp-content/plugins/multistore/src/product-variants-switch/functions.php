<?php
/**
 * Product Variants Block Functions
 *
 * @package MultiStore\Plugin\Block\ProductVariants
 */

namespace MultiStore\Plugin\Block\ProductVariants;

use MultiStore\Plugin\WooCommerce\Product_Variants;

/**
 * Get product variants data for display
 *
 * @param int $product_id Product ID.
 * @return array Array of variants with product data.
 */
function get_variants_data( int $product_id ): array {
	$variants_manager = new Product_Variants();
	$variants_config  = $variants_manager->get_product_variants( $product_id );

	if ( empty( $variants_config ) ) {
		return array();
	}

	$variants_data = array();

	foreach ( $variants_config as $variant_config ) {
		$attribute_id = $variant_config['attribute_id'];

		// Get attribute data.
		$attribute = get_attribute_by_id( $attribute_id );

		if ( ! $attribute ) {
			continue;
		}

		$variant_products = array();

		// Add current product first.
		$current_product = wc_get_product( $product_id );
		if ( $current_product ) {
			$current_attribute_value = get_product_attribute_value( $current_product, $attribute->attribute_name );

			$variant_products[] = array(
				'product_id'      => $product_id,
				'name'            => $current_product->get_name(),
				'url'             => get_permalink( $product_id ),
				'attribute_value' => $current_attribute_value,
				'display_type'    => 'attribute',
				'custom_text'     => '',
				'image_url'       => '',
				'is_current'      => true,
			);
		}

		// Add related products.
		foreach ( $variant_config['products'] as $product_data ) {
			$related_product_id = $product_data['product_id'];
			$product            = wc_get_product( $related_product_id );

			if ( ! $product ) {
				continue;
			}

			$attribute_value = get_product_attribute_value( $product, $attribute->attribute_name );
			$display_type    = $product_data['display_type'];
			$custom_text     = $product_data['custom_text'];
			$image_url       = '';

			if ( 'image' === $display_type && ! empty( $product_data['image_id'] ) ) {
				$image_url = wp_get_attachment_image_url( $product_data['image_id'], 'thumbnail' );
			}

			$variant_products[] = array(
				'product_id'      => $related_product_id,
				'name'            => $product->get_name(),
				'url'             => get_permalink( $related_product_id ),
				'attribute_value' => $attribute_value,
				'display_type'    => $display_type,
				'custom_text'     => $custom_text,
				'image_url'       => $image_url,
				'is_current'      => false,
			);
		}

		if ( ! empty( $variant_products ) ) {
			$variants_data[] = array(
				'attribute_id'    => $attribute_id,
				'attribute_name'  => $attribute->attribute_label,
				'attribute_slug'  => $attribute->attribute_name,
				'show_in_list'    => $variant_config['show_in_list'],
				'label'           => ! empty( $variant_config['label'] ) ? $variant_config['label'] : $attribute->attribute_label,
				'products'        => $variant_products,
			);
		}
	}

	return $variants_data;
}

/**
 * Get attribute by ID
 *
 * @param int $attribute_id Attribute ID.
 * @return object|null Attribute object or null.
 */
function get_attribute_by_id( int $attribute_id ) {
	$attribute_taxonomies = wc_get_attribute_taxonomies();

	foreach ( $attribute_taxonomies as $attribute ) {
		// Use loose comparison as attribute_id might be string.
		if ( $attribute->attribute_id == $attribute_id ) {
			return $attribute;
		}
	}

	return null;
}

/**
 * Get product attribute value
 *
 * @param \WC_Product $product        Product object.
 * @param string      $attribute_name Attribute name.
 * @return string Attribute value.
 */
function get_product_attribute_value( \WC_Product $product, string $attribute_name ): string {
	$attributes = $product->get_attributes();

	// Try with 'pa_' prefix (for global attributes).
	$taxonomy_name = 'pa_' . $attribute_name;

	if ( isset( $attributes[ $taxonomy_name ] ) ) {
		$attribute = $attributes[ $taxonomy_name ];

		if ( $attribute->is_taxonomy() ) {
			$terms = wp_get_post_terms( $product->get_id(), $taxonomy_name, array( 'fields' => 'names' ) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				return implode( ', ', $terms );
			}
		} else {
			return $attribute->get_options()[0] ?? '';
		}
	}

	// Try without prefix (for custom attributes).
	if ( isset( $attributes[ $attribute_name ] ) ) {
		$attribute = $attributes[ $attribute_name ];
		if ( is_array( $attribute->get_options() ) ) {
			return implode( ', ', $attribute->get_options() );
		}
		return $attribute->get_options();
	}

	return '';
}
