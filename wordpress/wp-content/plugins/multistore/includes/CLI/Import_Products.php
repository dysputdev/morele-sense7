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
class Import_Products {

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
		$csv_file   = $upload_dir['basedir'] . '/multistore-import-data/products.csv';

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

		WP_CLI::line( sprintf( 'Starting import of %d products...', $total ) );

		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing products', $total );

		foreach ( $csv as $row ) {
			if ( count( $row ) < 6 ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			list( $sku, $price, $price_regular, $shipping, $name, $category_name ) = $row;

			$product  = wc_get_product_id_by_sku( $sku );
			$product  = $product ? wc_get_product( $product ) : null;
			$category = get_term_by( 'slug', $category_name, 'product_cat' );
			if ( ! $category ) {
				$category = wp_insert_term( $category_name, 'product_cat' );
			}

			if ( ! $product ) {
				$product = new \WC_Product_Simple();
				$product->set_sku( $sku );
			}
			$product->set_name( $name );
			if ( $price_regular > 0 ) {
				$product->set_regular_price( $price_regular );
			}
			$product->set_price( $price );
			$product->set_category_ids( array( $category->term_id ) );

			// Save product.
			$result = $product->save();
			if ( $result ) {
				$success++;
			} else {
				$skipped++;
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( sprintf( 'Imported %d products, skipped %d.', $success, $skipped ) );
	}
}
