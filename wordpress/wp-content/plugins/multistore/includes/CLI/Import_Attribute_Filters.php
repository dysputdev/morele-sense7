<?php

namespace MultiStore\Plugin\CLI;

use MultiStore\Plugin\Database\Product_Relation_Groups_Table;
use MultiStore\Plugin\Database\Product_Relation_Settings_Table;
use MultiStore\Plugin\Database\Product_Relations_Table;
use MultiStore\Plugin\Repository\Relations_Repository;
use MultiStore\Plugin\Utils\Debug;
use WC_Product_Attribute;
use WP_CLI;

class Import_Attribute_Filters {

	public static $command = 'import:filters';

	private $skip_columns = array(
		'Adres',
		'Nazwa produktu',
		'ID produktu',
		'Produkt',
		'URL',
		'Kategoria',
		'Typ'
	);

	public function __invoke( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		$upload_dir = wp_upload_dir();
		$csv_dir    = $upload_dir['basedir'] . '/multistore-import-data/filtry/';

		$drop_attributes   = isset( $assoc_args['drop'] ) ? filter_var( $assoc_args['drop'], FILTER_VALIDATE_BOOLEAN ) : false;
		$import_taxonomies = isset( $assoc_args['taxonomies'] ) ? filter_var( $assoc_args['taxonomies'], FILTER_VALIDATE_BOOLEAN ) : false;
		$import_attributes = isset( $assoc_args['attributes'] ) ? filter_var( $assoc_args['attributes'], FILTER_VALIDATE_BOOLEAN ) : false;
		$update_products   = isset( $assoc_args['update-products'] ) ? filter_var( $assoc_args['update-products'], FILTER_VALIDATE_BOOLEAN ) : false;

		if ( $drop_attributes ) {
			// Drop existing attributes and terms.
			$attributes = wc_get_attribute_taxonomies();
			foreach ( $attributes as $attribute ) {
				// remove attribute.
				wc_delete_attribute( $attribute->attribute_id );
				WP_CLI::warning( sprintf( 'Deleted attribute: %s', $attribute->attribute_name ) );
			}
			WP_CLI::success( 'Dropped all existing attributes.' );
			return;
		}

		if ( ! file_exists( $csv_dir ) ) {
			WP_CLI::error( sprintf( 'Directory not found: %s', $csv_dir ) );
			return;
		}

		$files = glob( $csv_dir . '*.csv' );
		if ( empty( $files ) ) {
			WP_CLI::error( 'No CSV files found in the directory.' );
			return;
		}

		// Track attributes for each product.
		$attributes_to_create = array();
		$product_attributes   = array();

		// read each line.
		foreach ( $files as $file ) {
			$handle = fopen( $file, 'r' );
			if ( false !== $handle ) {

				$header = fgetcsv( $handle, 0, ',', '\\' );
				if ( $import_taxonomies ) {
					$this->create_global_attributes( $header );
				}

				if ( $import_attributes ) {
					while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
						$product_data = array_combine( $header, $data );
						$this->import_attributes( $product_data );
					}
				}
			}
			fclose( $handle );
		}

		// Now update product attributes for each product.
		$update_products = isset( $assoc_args['update-products'] ) ? filter_var( $assoc_args['update-products'], FILTER_VALIDATE_BOOLEAN ) : false;
		if ( $update_products ) {
			WP_CLI::warning( 'Updating product attributes...' );
			foreach ( $product_attributes as $product_id => $product_attrs ) {
				$this->update_product_attributes( $product_id, $product_attrs );
				WP_CLI::success( sprintf( 'Updated attributes for product ID: %d', $product_id ) );
			}
		}
	}

	private function create_global_attributes( $attributes ) {
		$result = array();
		foreach ( $attributes as $attr_name ) {
			$attr_name = trim( $attr_name );
			if ( in_array( $attr_name, array( 'Adres', 'Nazwa produktu', 'ID produktu', 'Produkt', 'URL', 'Kategoria', 'Typ' ) ) ) {
				continue;
			}

			if ( 'Kolor 1' === $attr_name || 'Kolor 2' === $attr_name ) {
				$attr_name = 'Kolor';
			}

			if ( 'Materiał obicia 1' === $attr_name || 'Materiał obicia 2' === $attr_name ) {
				$attr_name = 'Materiał obicia';
			}

			$name = wc_sanitize_taxonomy_name( $attr_name );
			$slug = wc_attribute_taxonomy_name( $name );

			// skip already existing taxonomies.
			if ( wc_attribute_taxonomy_id_by_name( $attr_name ) ) {
				WP_CLI::warning( sprintf( 'Attribute already exists: %s', $attr_name ) );
				continue;
			}

			$attribute_data = array(
				'name'         => $attr_name,
				'slug'         => $slug,
				'type'         => 'select',
				'orderby'      => 'menu_order',
				'has_archives' => false,
			);

			$result = wc_create_attribute( $attribute_data );

			if ( is_wp_error( $result ) ) {
				WP_CLI::warning( 'Błąd tworzenie atrybutu: ' . $result->get_error_message() );
			} else {
				register_taxonomy(
					$slug,
					'product',
					array(
						'labels' => array( 'name' => $attr_name )
					)
				);
				WP_CLI::line( sprintf( 'Created attribute: %s', $attr_name ) );
			}
		}

		return $result;
	}

	private function import_attributes( $product_data ) {

		$product_sku = $product_data['ID produktu'] ?? null;
		if ( ! $product_sku ) {
			WP_CLI::warning( sprintf( 'No product SKU: %s' ) );
			return;
		}

		$product_id = wc_get_product_id_by_sku( $product_sku );
		if ( ! $product_id ) {
			WP_CLI::warning( sprintf( 'Product not found for SKU: %s', $product_sku ) );
			return;
		}

		$product    = wc_get_product( $product_id );
		$attributes = $product->get_attributes();
		foreach ( $product_data as $attr_name => $attr_value ) {
			$attr_name  = trim( $attr_name );
			$attr_value = trim( $attr_value );

			if ( in_array( $attr_name, $this->skip_columns, true ) ) {
				continue;
			}

			if ( '' === $attr_value || '' === $attr_value ) {
				continue;
			}

			if ( 'Kolor 1' === $attr_name || 'Kolor 2' === $attr_name ) {
				$attr_name = 'Kolor';
			}

			if ( 'Materiał obicia 1' === $attr_name || 'Materiał obicia 2' === $attr_name ) {
				$attr_name = 'Materiał obicia';
			}

			$name = wc_sanitize_taxonomy_name( $attr_name );
			$slug = wc_attribute_taxonomy_name( $name );

			$attribute_id = wc_attribute_taxonomy_id_by_name( $attr_name );
			if ( ! $attribute_id ) {
				continue;
			}

			if ( isset( $attributes[ $slug ] ) ) {
				$attribute = $attributes[ $slug ];
			} else {
				$attribute = new WC_Product_Attribute();
				$attribute->set_id( $attribute_id );
				$attribute->set_name( $slug );
				$attribute->set_visible( false );
				$attribute->set_variation( false );
			}

			$term = term_exists( $attr_value, $slug );
			if ( ! $term ) {
				$attr_slug = sanitize_title( str_replace( ',', '_', $attr_value ) );
				$term      = wp_insert_term(
					$attr_value,
					$slug,
					array( 'slug' => $attr_slug )
				);

				if ( ! is_wp_error( $term ) ) {
					$term_id = $term['term_id'];
				}
			} else {
				$term_id = $term['term_id'];
			}

			if ( ! $term_id ) {
				continue;
			}

			$options = $attribute->get_options();
			if ( ! in_array( (int) $term_id, $options, true ) ) {
				$options[] = (int) $term_id;
			}
			$attribute->set_options( $options );
			$attributes[ $slug ] = $attribute;
		}

		if ( empty( $attributes ) ) {
			WP_CLI::warning( sprintf( 'No attributes found for product: %s', $product_sku ) );
			return;
		}

		$product->set_attributes( $attributes );
		$product->save();

		WP_CLI::success( sprintf( 'Updated attributes for product: %s', $product_sku ) );
	}

	/**
	 * Update product attributes in WooCommerce.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $attributes Array of attributes to add.
	 */
	private function update_product_attributes( $product_id, $attributes ) {
		// Get product object.
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		// Get existing attributes.
		$existing_attributes = $product->get_attributes();

		// Process each new attribute.
		foreach ( $attributes as $taxonomy_slug => $attribute_data ) {
			// Create WC_Product_Attribute object.
			$attribute = new WC_Product_Attribute();
			$attribute->set_id( wc_attribute_taxonomy_id_by_name( str_replace( 'pa_', '', $taxonomy_slug ) ) );
			$attribute->set_name( $taxonomy_slug );
			$attribute->set_options( wp_get_object_terms( $product_id, $taxonomy_slug, array( 'fields' => 'names' ) ) );
			$attribute->set_visible( true );
			$attribute->set_variation( false );

			// Add to existing attributes.
			$existing_attributes[ $taxonomy_slug ] = $attribute;
		}

		// Save attributes using WooCommerce API.
		$product->set_attributes( $existing_attributes );
		$product->save();
	}
}
