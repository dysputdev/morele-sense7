<?php
/**
 * WP-CLI command for importing products data from CSV
 *
 * @package MultiStore\Plugin\CLI
 */

namespace MultiStore\Plugin\CLI;

use MultiStore\Plugin\WooCommerce\Product_Grouping;
use WP_CLI;

/**
 * Import products from CSV file
 */
class Import_Main_Product {

	public static $command = 'import:main';

	public function __invoke( $args, $assoc_args ) {
		$upload_dir = wp_upload_dir();
		$csv_file   = $upload_dir['basedir'] . '/multistore-import-data/main.csv';

		if ( ! file_exists( $csv_file ) ) {
			WP_CLI::error( sprintf( 'File not found: %s', $csv_file ) );
			return;
		}

		$csv = array_map( 'str_getcsv', file( $csv_file ) );
		if ( empty( $csv ) ) {
			WP_CLI::error( 'CSV file is empty.' );
			return;
		}

		$total   = count( $csv );
		$success = 0;
		$skipped = 0;

		WP_CLI::line( sprintf( 'Starting setting main products (%s)...', $total ) );

		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing products', $total );

		foreach ( $csv as $row ) {

			$sku = $row[0];

			if ( empty( $sku ) ) {
				WP_CLI::line( 'Product SKU is empty.' );
				++$skipped;
				continue;
			}

			$product_id = wc_get_product_id_by_sku( $sku );

			if ( ! $product_id ) {
				WP_CLI::line( sprintf( 'Product %s not found...', $sku ) );
				++$skipped;
				continue;
			}

			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				WP_CLI::line( sprintf( 'Product %s not found...', $sku ) );
				++$skipped;
				continue;
			}

			$terms = wp_get_object_terms( $product_id, Product_Grouping::TAXONOMY );
			if ( ! empty( $terms ) ) {
				WP_CLI::line( sprintf( 'Product %s is already main product...', $sku ) );
				++$skipped;
				continue;
			}

			// set Product_Grupping taxonomy.
			wp_set_object_terms( $product_id, Product_Grouping::MAIN_PRODUCT_TERM, Product_Grouping::TAXONOMY );
		}
	}
}
