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
class Import_Shortnames {

	public static $command = 'import:shortnames';

	public function __invoke( $args, $assoc_args ) {
		$upload_dir = wp_upload_dir();
		$csv_file   = $upload_dir['basedir'] . '/multistore-import-data/shortnames.csv';

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

			list( $sku, $simplified_name ) = $row;

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

			update_post_meta( $product_id, '_simplified_product_name', sanitize_text_field( wp_unslash( $simplified_name ) ) );

			++$success;
			$progress->tick();
		}

		$progress->finish();
		WP_CLI::line( sprintf( 'Finished setting main products (%s/%s)!', $success, $total ) );
		WP_CLI::line( sprintf( 'Skipped: %s', $skipped ) );
	}
}
