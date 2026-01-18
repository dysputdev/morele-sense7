<?php

namespace MultiStore\Plugin\CLI;

use WP_CLI;

class Import_Files {
	public function __invoke()
	{
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// get all products.
		$lang = PLL()->model->get_default_language();

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'lang'           => $lang->slug,
			'fields'         => 'ids',
			'numberposts'    => -1,
		);
		$products = get_posts( $args );
		$total    = count( $products );

		WP_CLI::line( sprintf( 'Starting import files of %d products...', $total ) );
		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing product files', $total );
		foreach ( $products as $product_id ) {
			$this->import_files( $product_id );

			$progress->tick();
		}

		$progress->finish();
	}

	public function import_files( $product_id ) {
		global $wpdb;

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			WP_CLI::line( sprintf( 'Product %d not found...', $product_id ) );
			return;
		}

		$sku = $product->get_sku();
		$downloads_ids = $product->get_meta( '_multistore_product_downloads' );
		$downloads_ids = empty( $downloads_ids ) ? array() : $downloads_ids;

		if ( ! empty( $downloads_ids ) ) {
			WP_CLI::line( sprintf( 'Product %d already has files...', $product_id ) );
			return;
		}

		if ( empty( $sku ) ) {
			WP_CLI::line( sprintf( 'Product %d without sku...', $product_id ) );
			return;
		}

		usleep( wp_rand( 500000, 1000000 ) );
		// fake url to get product details.
		$morele_product_url = 'https://morele.net/product-' . $sku;

		$response = wp_remote_get(
			$morele_product_url,
			array(
				'timeout'    => 30,
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			)
		);

		// Sprawdź czy request się powiódł.
		if ( is_wp_error( $response ) ) {
			WP_CLI::line( $response->get_error_message() );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			WP_CLI::line( sprintf( 'Response code: %d for product %d (sku: %s)', $response_code, $product_id, $sku ) );
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		if ( empty( $html ) ) {
			WP_CLI::error( 'HTML is empty.' );
			return false;
		}

		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( $html );
		libxml_clear_errors();

		$xpath = new \DOMXPath( $dom );

		$query = "//ul[contains(concat(' ', normalize-space(@class), ' '), ' instruction-files ')]/li/a";

		$files = $xpath->query( $query );
		if ( $files->length === 0 ) {
			WP_CLI::line( sprintf( 'Product %d has no files...', $product_id ) );
			return;
		}

		WP_CLI::line( sprintf( 'Downloading file of product %d (sku: %s)...', $product_id, $sku ) );
		foreach ( $files as $file ) {
			/** @var \DOMElement $file */
			$file_url = $file->getAttribute( 'href' );
			if ( empty( $file_url ) ) {
				continue;
			}
			$file_text = trim( $file->textContent );

			// check if file already imported.
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_source_url' AND meta_value = %s", $file_url ) );
			if ( $exists ) {
				$downloads_ids[] = $exists;
				continue;
			}
			WP_CLI::line( sprintf( 'Downloading file from %s...', $file_url ) );

			$file_name = wp_basename( $file_url );
			$temp_file = download_url( $file_url );

			// skip if error downloading, stop update other files?
			if ( is_wp_error( $temp_file ) ) {
				WP_CLI::line( $temp_file->get_error_message() );
				return;
			}

			$file_array = array(
				'name'     => $file_name,
				'tmp_name' => $temp_file,
			);
			// Download file to temp location.
			$id = media_handle_sideload( $file_array, $product_id, $file_text );

			// If error unlink and ?stop other files?.
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
				WP_CLI::line( sprintf( 'Error storing file of %d products...', $product_id ) );
				return;
			}

			// Store the original attachment source in meta.
			add_post_meta( $id, '_source_url', $file_url );
			$downloads_ids[] = $id;
		}

		update_post_meta( $product_id, '_multistore_product_downloads', array_unique( $downloads_ids ) );
	}
}
