<?php
/**
 * Product Downloads Block Functions
 *
 * @package MultiStore\Plugin\Block\ProductDownloads
 */

namespace MultiStore\Plugin\Block\ProductDownloads;

/**
 * Get product downloadable files
 *
 * @param int $product_id Product ID.
 * @return array Array of downloadable files with name, url, and icon.
 */
function get_product_downloads( int $product_id ): array {
	$downloads_ids = get_post_meta( $product_id, '_multistore_product_downloads', true );

	if ( ! is_array( $downloads_ids ) || empty( $downloads_ids ) ) {
		return array();
	}

	$downloads = array();

	foreach ( $downloads_ids as $attachment_id ) {
		$attachment_id = absint( $attachment_id );

		if ( $attachment_id <= 0 ) {
			continue;
		}

		$file_url = wp_get_attachment_url( $attachment_id );

		if ( ! $file_url ) {
			continue;
		}

		$file_path = get_attached_file( $attachment_id );
		$file_name = basename( $file_path );
		$icon_url  = wp_mime_type_icon( $attachment_id );

		$downloads[] = array(
			'id'   => $attachment_id,
			'name' => $file_name,
			'url'  => $file_url,
			'icon' => $icon_url,
		);
	}

	return $downloads;
}
