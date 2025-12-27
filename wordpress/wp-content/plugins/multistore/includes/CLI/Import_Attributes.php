<?php

namespace MultiStore\Plugin\CLI;

use WC_Product_Attribute;
use WP_CLI;

/**
 * WP-CLI command for importing products from CSV
 *
 * @package MultiStore\Plugin\CLI
 */
class Import_Attributes
{
	public function __invoke( $args, $assoc_args ) {

		$upload_dir = wp_upload_dir();
		$import_map = array_map( 'str_getcsv', file( $upload_dir['basedir'] . '/multistore-import-data/attributes.csv' ) );
		if ( empty( $import_map ) ) {
			WP_CLI::error( 'Import data file is empty.' );
			return;
		}

		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing product attributes', count( $this->import_map ) );
		foreach ( $this->import_map as $sku => $filename ) {

			$csv_file = $upload_dir['basedir'] . '/multistore-import-data/attributes/' . $filename;
			if ( ! file_exists( $csv_file ) ) {
				WP_CLI::error( sprintf( 'File not found: %s', $csv_file ) );
				return;
			}

			$lines = file( $csv_file );
			if ( empty( $lines ) ) {
				WP_CLI::error( 'Import data file is empty.' );
				return;
			}

			$attributes = array();
			foreach ( $lines as $line ) {
				if ( empty( trim( $line ) ) ) {
					continue;
				}

				list( $attribute_name, $attribute_value ) = explode( ';', $line );

				if ( isset( $attributes[ $attribute_name ] ) ) {
					$attributes[ $attribute_name ] .= PHP_EOL . $attribute_value;
				} else {
					$attributes[ $attribute_name ] = $attribute_value;
				}
			}

			$this->import_attributes( $sku, $attributes );
			$progress->tick();
		}

		$progress->finish();
	}

	public function import_attributes( $sku, $attributes ) {

		// find product by sku.
		$product_id = wc_get_product_id_by_sku( $sku );

		if ( empty( $product_id ) ) {
			WP_CLI::error( sprintf( 'Product not found: %s', $sku ) );
			return;
		}

		$wc_attributes = array();
		foreach ( $attributes as $attr_name => $attr_value ) {
			$attribute = new WC_Product_Attribute();

			$attribute->set_name( $attr_name );
			$attribute->set_options( array_map( 'trim', explode( PHP_EOL, $attr_value ) ) );
			$attribute->set_visible( true );
			$attribute->set_variation( false );
			$attribute->set_position( 0 );

			$wc_attributes[] = $attribute;
		}

		// save attributes.
		$product = wc_get_product( $product_id );
		$product->set_attributes( $wc_attributes );
		$product->save();
	}
}
