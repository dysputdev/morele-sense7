<?php
/**
 * Product Specifications Block Functions
 *
 * @package MultiStore\Plugin\Block\ProductSpecifications
 */

namespace MultiStore\Plugin\Block\ProductSpecifications;

/**
 * Get product visible attributes for specifications table
 *
 * @param int $product_id Product ID.
 * @return array Array of visible attributes with labels and values.
 */
function get_product_specifications( int $product_id ): array {
	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		return array();
	}

	$attributes      = $product->get_attributes();
	$specifications = array();

	foreach ( $attributes as $attribute ) {
		// Skip if attribute is not visible.
		if ( ! $attribute->get_visible() ) {
			continue;
		}

		$label = '';
		$value = '';

		if ( $attribute->is_taxonomy() ) {
			// Global attribute.
			$taxonomy = $attribute->get_taxonomy_object();
			if ( $taxonomy ) {
				$label = $taxonomy->attribute_label;
			}

			$terms = wp_get_post_terms( $product_id, $attribute->get_name(), array( 'fields' => 'names' ) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$value = implode( ', ', $terms );
			}
		} else {
			// Custom attribute.
			$label = $attribute->get_name();
			$value = implode( ', ', $attribute->get_options() );
		}

		// Skip if no value.
		if ( empty( $value ) ) {
			continue;
		}

		$specifications[] = array(
			'label' => $label,
			'value' => $value,
		);
	}

	return $specifications;
}
