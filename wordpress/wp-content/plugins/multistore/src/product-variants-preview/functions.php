<?php
/**
 * Product Variants Preview Block Functions
 *
 * @package MultiStore\Plugin\Block\ProductVariantsPreview
 */

namespace MultiStore\Plugin\Block\ProductVariantsPreview;

// use MultiStore\Plugin\WooCommerce\Product_Variants;

// /**
//  * Get product variants data for preview display
//  *
//  * Returns only variants that are marked as show_in_list.
//  * Includes current product as first variant with is_current flag.
//  *
//  * @param int $product_id Product ID.
//  * @return array Array of variants grouped by attribute.
//  */
// function get_preview_variants_data( int $product_id ): array {
// 	$variants_manager = new Product_Variants();
// 	$variants_config  = $variants_manager->get_product_variants( $product_id );

// 	if ( empty( $variants_config ) ) {
// 		return array();
// 	}

// 	$preview_variants = array();

// 	foreach ( $variants_config as $variant_config ) {
// 		// Skip if not marked for display in list.
// 		if ( empty( $variant_config['show_in_list'] ) ) {
// 			continue;
// 		}

// 		$attribute_id = $variant_config['attribute_id'];

// 		// Get attribute data.
// 		$attribute = get_attribute_by_id( $attribute_id );

// 		if ( ! $attribute ) {
// 			continue;
// 		}

// 		$variant_products = array();

// 		// Add current product first as active variant.
// 		$current_product = wc_get_product( $product_id );
// 		if ( $current_product ) {
// 			$current_attribute_value = get_product_attribute_value( $current_product, $attribute->attribute_name );
// 			$image_id                = $current_product->get_image_id();
// 			$thumbnail_url           = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
// 			$image_url               = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) : '';
// 			$price_data              = get_product_price_data( $current_product );

// 			$variant_products[] = array(
// 				'product_id'      => $product_id,
// 				'name'            => $current_product->get_name(),
// 				'url'             => get_permalink( $product_id ),
// 				'attribute_value' => $current_attribute_value,
// 				'attribute_label' => $attribute->attribute_label,
// 				'display_type'    => 'attribute',
// 				'custom_text'     => '',
// 				'thumbnail_url'   => $thumbnail_url,
// 				'image_url'       => $image_url,
// 				'price_data'      => $price_data,
// 				'is_current'      => true,
// 			);
// 		}

// 		// Add related products.
// 		foreach ( $variant_config['products'] as $product_data ) {
// 			$related_product_id = $product_data['product_id'];
// 			$product            = wc_get_product( $related_product_id );

// 			if ( ! $product ) {
// 				continue;
// 			}

// 			$attribute_value = get_product_attribute_value( $product, $attribute->attribute_name );
// 			$display_type    = $product_data['display_type'];
// 			$custom_text     = $product_data['custom_text'];
// 			$thumbnail_url   = '';

// 			// Get thumbnail for variant.
// 			if ( 'image' === $display_type && ! empty( $product_data['image_id'] ) ) {
// 				$thumbnail_url = wp_get_attachment_image_url( $product_data['image_id'], 'thumbnail' );
// 			}

// 			// Fallback to product image if no custom thumbnail.
// 			if ( empty( $thumbnail_url ) ) {
// 				$thumbnail_id  = $product->get_image_id();
// 				$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : '';
// 			}

// 			// Get main product image.
// 			$image_id  = $product->get_image_id();
// 			$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) : '';

// 			// Get price data.
// 			$price_data = get_product_price_data( $product );

// 			$variant_products[] = array(
// 				'product_id'      => $related_product_id,
// 				'name'            => $product->get_name(),
// 				'url'             => get_permalink( $related_product_id ),
// 				'attribute_value' => $attribute_value,
// 				'attribute_label' => $attribute->attribute_label,
// 				'display_type'    => $display_type,
// 				'custom_text'     => $custom_text,
// 				'thumbnail_url'   => $thumbnail_url,
// 				'image_url'       => $image_url,
// 				'price_data'      => $price_data,
// 				'is_current'      => false,
// 			);
// 		}

// 		if ( ! empty( $variant_products ) ) {
// 			$preview_variants[] = array(
// 				'attribute_id'    => $attribute_id,
// 				'attribute_name'  => $attribute->attribute_label,
// 				'attribute_slug'  => $attribute->attribute_name,
// 				'show_in_list'    => $variant_config['show_in_list'],
// 				'label'           => ! empty( $variant_config['label'] ) ? $variant_config['label'] : $attribute->attribute_label,
// 				'products'        => $variant_products,
// 			);
// 		}
// 	}

// 	return $preview_variants;
// }

// /**
//  * Get current product data
//  *
//  * @param int $product_id Product ID.
//  * @return array|null Product data or null.
//  */
// function get_current_product_data( int $product_id ) {
// 	$product = wc_get_product( $product_id );

// 	if ( ! $product ) {
// 		return null;
// 	}

// 	// Get main product image.
// 	$image_id  = $product->get_image_id();
// 	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) : '';

// 	// Get thumbnail.
// 	$thumbnail_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';

// 	// Get price data.
// 	$price_data = get_product_price_data( $product );

// 	return array(
// 		'product_id'    => $product_id,
// 		'name'          => $product->get_name(),
// 		'url'           => get_permalink( $product_id ),
// 		'image_url'     => $image_url,
// 		'thumbnail_url' => $thumbnail_url,
// 		'price_data'    => $price_data,
// 	);
// }

// /**
//  * Get product price data
//  *
//  * @param \WC_Product $product Product object.
//  * @return array Price data.
//  */
// function get_product_price_data( \WC_Product $product ): array {
// 	$regular_price = $product->get_regular_price();
// 	$sale_price    = $product->get_sale_price();
// 	$price         = $product->get_price();

// 	// Get lowest price from price history if available.
// 	$lowest_price = get_post_meta( $product->get_id(), '_multistore_lowest_price_30_days', true );

// 	return array(
// 		'regular_price'          => $regular_price ? wc_price( $regular_price ) : '',
// 		'sale_price'             => $sale_price ? wc_price( $sale_price ) : '',
// 		'price'                  => $price ? wc_price( $price ) : '',
// 		'lowest_price'           => $lowest_price ? wc_price( $lowest_price ) : '',
// 		'regular_price_raw'      => $regular_price,
// 		'sale_price_raw'         => $sale_price,
// 		'price_raw'              => $price,
// 		'lowest_price_raw'       => $lowest_price,
// 		'is_on_sale'             => $product->is_on_sale(),
// 	);
// }

// /**
//  * Get attribute by ID
//  *
//  * @param int $attribute_id Attribute ID.
//  * @return object|null Attribute object or null.
//  */
// function get_attribute_by_id( int $attribute_id ) {
// 	$attribute_taxonomies = wc_get_attribute_taxonomies();

// 	foreach ( $attribute_taxonomies as $attribute ) {
// 		// Use loose comparison as attribute_id might be string.
// 		if ( $attribute->attribute_id == $attribute_id ) {
// 			return $attribute;
// 		}
// 	}

// 	return null;
// }

// /**
//  * Get product attribute value
//  *
//  * @param \WC_Product $product        Product object.
//  * @param string      $attribute_name Attribute name.
//  * @return string Attribute value.
//  */
// function get_product_attribute_value( \WC_Product $product, string $attribute_name ): string {
// 	$attributes = $product->get_attributes();

// 	// Try with 'pa_' prefix (for global attributes).
// 	$taxonomy_name = 'pa_' . $attribute_name;

// 	if ( isset( $attributes[ $taxonomy_name ] ) ) {
// 		$attribute = $attributes[ $taxonomy_name ];

// 		if ( $attribute->is_taxonomy() ) {
// 			$terms = wp_get_post_terms( $product->get_id(), $taxonomy_name, array( 'fields' => 'names' ) );
// 			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
// 				return implode( ', ', $terms );
// 			}
// 		} else {
// 			return $attribute->get_options()[0] ?? '';
// 		}
// 	}

// 	// Try without prefix (for custom attributes).
// 	if ( isset( $attributes[ $attribute_name ] ) ) {
// 		$attribute = $attributes[ $attribute_name ];
// 		if ( is_array( $attribute->get_options() ) ) {
// 			return implode( ', ', $attribute->get_options() );
// 		}
// 		return $attribute->get_options();
// 	}

// 	return '';
// }
