<?php
/**
 * WP-CLI command for importing products from CSV
 *
 * @package MultiStore\Plugin\CLI
 */

namespace MultiStore\Plugin\CLI;

use WP_CLI;

/**
 * Import products from CSV file
 */
class Import_Price {

	/**
	 * Import products from products.csv located in wp-content/uploads/products.csv.
	 *
	 * File structure:
	 * product_sku,price,price_regular,shipping,name,category
	 *
	 * ## EXAMPLES
	 *
	 *     wp multistore import products
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		$upload_dir = wp_upload_dir();
		$csv_file   = $upload_dir['basedir'] . '/multistore-import-data/price/pl.csv';

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

		WP_CLI::line( sprintf( 'Starting import of %d products pricing...', $total ) );

		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing products pricing', $total );

		foreach ( $csv as $row ) {
			if ( count( $row ) < 5 ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			list( $sku, $price, $price_regular, $shipping, $name ) = $row;

			$product = wc_get_product_id_by_sku( $sku );
			$product = $product ? wc_get_product( $product ) : null;

			if ( ! $product ) {
				WP_CLI::warning( sprintf( 'Product with SKU %s not found.', $sku ) );
				++$skipped;
				$progress->tick();
				continue;
			}

			if ( (float) $price < (float) $price_regular ) {
				$product->set_sale_price( $price );
				$product->set_regular_price( $price_regular );
				$product->set_price( $price_regular );
			} else {
				$product->set_price( $price );
			}

			// Save product.
			$result = $product->save();
			if ( $result ) {
				++$success;
			} else {
				++$skipped;
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( sprintf( 'Imported %d products, skipped %d.', $success, $skipped ) );
	}
}
