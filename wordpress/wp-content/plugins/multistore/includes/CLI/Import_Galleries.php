<?php

namespace MultiStore\Plugin\CLI;

use WP_CLI;

class Import_Galleries {

	public static $command = 'import:galleries';

	public function __invoke() {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// get all products.
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'lang'           => 'pl',
			'fields'         => 'ids',
			'numberposts'    => -1,
		);

		$products = get_posts( $args );
		$total    = count( $products );

		WP_CLI::line( sprintf( 'Starting import galleries of %d products...', $total ) );
		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing product galleries', $total );
		foreach ( $products as $product_id ) {
			$this->import_galleries( $product_id );

			$progress->tick();
			usleep( wp_rand( 500000, 1000000 ) );
		}

		$progress->finish();
	}

	private function import_galleries( $product_id ) {
		global $wpdb;

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			WP_CLI::line( sprintf( 'Product %d not found...', $product_id ) );
			return;
		}

		$ids = $product->get_gallery_image_ids();
		if ( ! empty( $ids ) ) {
			WP_CLI::line( sprintf( 'Product %d already has galleries...', $product_id ) );
			return;
		}

		$sku = $product->get_sku();
		if ( empty( $sku ) ) {
			WP_CLI::line( sprintf( 'Product %d has no SKU...', $product_id ) );
			return;
		}

		// $product_id = 366;
		// $sku        = 13147302;
		// $product    = wc_get_product( $product_id );

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
			WP_CLI::error( $response->get_error_message() );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			WP_CLI::line( sprintf( 'Response code: %d', $response_code ) );
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

		// find .gallery-holder .swpier-slide.
		$query = "
		//div[contains(concat(' ', normalize-space(@class), ' '), ' gallery-holder ')]
		//*[contains(concat(' ', normalize-space(@class), ' '), ' swiper-slide ')]
		";

		$slides = $xpath->query( $query );
		if ( ! $slides->length ) {
			return;
		}

		$images = array();
		foreach ( $slides as $slide ) {
			/** @var \DOMElement $slide */
			if ( $slide->hasAttribute( 'data-src' ) ) {
				$src = $slide->getAttribute( 'data-src' );
				if ( str_contains( $src, 'images.morele.net' ) ) {
					$images[] = $src;
				}
			}
		}

		if ( empty( $images ) ) {
			WP_CLI::line( 'No images found.' );
			return;
		}

		$attachment_ids = array();
		foreach ( $images as $image ) {
			$file_name = basename( $image );
			$image_id  = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM $wpdb->posts WHERE post_type='attachment' AND guid LIKE %s",
					'%' . $wpdb->esc_like( $file_name ) . '%'
				)
			);

			// Pobierz obraz (zwraca HTML albo WP_Error).
			if ( empty( $image_id ) ) {
				$image_id = media_sideload_image( $image, $product_id, null, 'id' );
				usleep( wp_rand( 100000, 300000 ) );
			}
			$attachment_ids[] = $image_id;
		}
		$product->set_gallery_image_ids( $attachment_ids );
		$product->save();
	}
}
