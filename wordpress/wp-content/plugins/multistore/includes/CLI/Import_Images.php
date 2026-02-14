<?php
/**
 * WP-CLI command for importing products from CSV
 *
 * @package MultiStore\Plugin\CLI
 */

namespace MultiStore\Plugin\CLI;

use WP_CLI;

/**
 * Import product featured images from CSV file
 */
class Import_Images {

	public static $command = 'import:images';

	/**
	 * Import products featured images from products.csv located in wp-content/uploads/images.csv.
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
		$csv_file   = $upload_dir['basedir'] . '/multistore-import-data/images.csv';

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
			if ( count( $row ) < 2 ) {
				$skipped++;
				$progress->tick();
				continue;
			}
			
			list( $sku, $url ) = $row;

			$product_id = wc_get_product_id_by_sku( $sku );
			$product    = $product_id ? wc_get_product( $product_id ) : null;

			if ( ! $product ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			$scraped_data = $this->scrap_product_data( $url );

			if ( ! $scraped_data || empty( $scraped_data['image'] ) ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			if ( $product->get_image_id() ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			// download image to media library/and attach to product.
			$attachment_id = $this->download_image( $scraped_data['image'] );
			if ( $attachment_id ) {
				$product->set_image_id( $attachment_id );
			}

			// Save product.
			$result = $product->save();
			if ( $result ) {
				$success++;
			} else {
				$skipped++;
			}

			sleep(1); // to avoid being blocked by remote server

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( sprintf( 'Imported %d products, skipped %d.', $success, $skipped ) );
	}

	public function download_image( $image_url ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Download image to media library.
		$attachment_id = media_sideload_image( $image_url, 0, null, 'id' );

		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}

		return $attachment_id;
	}

	public function scrap_product_data( $url ) {
		$result   = array();
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 30,
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			)
		);

		// Sprawdź czy request się powiódł.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		if ( empty( $html ) ) {
			return false;
		}

		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( $html );
		libxml_clear_errors();

		$xpath = new \DOMXPath( $dom );

		$og_image = $xpath->query( "//meta[@property='og:image']" );
		$image    = $og_image->length ? $og_image->item( 0 )->attributes['content']->value : null;

		$result['image'] = $image;

		return $result;
	}
}
