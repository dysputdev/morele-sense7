<?php
/**
 * Simplified Product Name Block Functions
 *
 * @package MultiStore\Plugin\Block\SimplifiedProductName
 */

namespace MultiStore\Plugin\Block\SimplifiedProductName;

/**
 * Add custom field to product general tab
 *
 * @return void
 */
function add_simplified_product_name_field(): void {
	woocommerce_wp_text_input(
		array(
			'id'          => '_simplified_product_name',
			'label'       => __( 'Uproszczona nazwa produktu', 'multistore' ),
			'placeholder' => __( 'Wprowadź uproszczoną nazwę produktu', 'multistore' ),
			'desc_tip'    => true,
			'description' => __( 'Opcjonalna uproszczona nazwa produktu, która może być wyświetlana zamiast pełnej nazwy.', 'multistore' ),
		)
	);
}
add_action( 'woocommerce_product_options_general_product_data', __NAMESPACE__ . '\add_simplified_product_name_field' );

/**
 * Save custom field value
 *
 * @param int $post_id Product ID.
 * @return void
 */
function save_simplified_product_name_field( int $post_id ): void {
	$simplified_name = isset( $_POST['_simplified_product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_simplified_product_name'] ) ) : '';
	update_post_meta( $post_id, '_simplified_product_name', $simplified_name );
}
add_action( 'woocommerce_process_product_meta', __NAMESPACE__ . '\save_simplified_product_name_field' );

/**
 * Get simplified product name or fallback to product title
 *
 * @param int $product_id Product ID.
 * @return string Product name.
 */
function get_product_name( int $product_id ): string {
	$simplified_name = get_post_meta( $product_id, '_simplified_product_name', true );

	if ( ! empty( $simplified_name ) ) {
		return $simplified_name;
	}

	return get_the_title( $product_id );
}
