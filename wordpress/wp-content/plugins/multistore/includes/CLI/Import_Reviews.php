<?php
/**
 * WP-CLI command for importing reviews from CSV
 *
 * @package MultiStore\Plugin\CLI
 */

namespace MultiStore\Plugin\CLI;

use WP_CLI;
use WC_Comments;

/**
 * Import product reviews from CSV file
 */
class Import_Reviews {

	public static $command = 'import:reviews';

	/**
	 * Import product reviews from reviews.csv located in wp-content/uploads/reviews.csv.
	 *
	 * File structure:
	 * SKU,Nick autora,Nazwa,Ocena,Treść,Zalety,Wady
	 *
	 * ## EXAMPLES
	 *
	 *     wp multistore import reviews
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		$upload_dir = wp_upload_dir();
		$csv_file   = $upload_dir['basedir'] . '/multistore-import-data/reviews.csv';

		if ( ! file_exists( $csv_file ) ) {
			WP_CLI::error( sprintf( 'File not found: %s', $csv_file ) );
			return;
		}

		$csv = array_map( 'str_getcsv', file( $csv_file ) );
		if ( empty( $csv ) ) {
			WP_CLI::error( 'CSV file is empty.' );
			return;
		}

		// Skip header row.
		array_shift( $csv );

		$total   = count( $csv );
		$success = 0;
		$skipped = 0;

		WP_CLI::line( sprintf( 'Starting import of %d reviews...', $total ) );

		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing reviews', $total );

		foreach ( $csv as $row ) {
			if ( count( $row ) < 7 ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			list( $sku, $author_nick, $title, $rating, $content, $pros, $cons ) = $row;

			$product_id = wc_get_product_id_by_sku( $sku );
			// Skip if product not found.
			if ( ! $product_id ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			// Prepare review content with pros and cons.
			$review_content = $content;
			if ( ! empty( $pros ) || ! empty( $cons ) ) {
				$review_content .= "\n\n";
				if ( ! empty( $pros ) ) {
					$review_content .= '<strong>Zalety:</strong> ' . $pros;
				}
				if ( ! empty( $cons ) ) {
					if ( ! empty( $pros ) ) {
						$review_content .= "\n";
					}
					$review_content .= '<strong>Wady:</strong> ' . $cons;
				}
			}

			// Prepare comment data.
			$comment_data = array(
				'comment_post_ID'      => $product_id,
				'comment_author'       => ! empty( $author_nick ) ? sanitize_text_field( $author_nick ) : 'Klient',
				'comment_author_email' => 'noreply@example.com',
				'comment_content'      => wp_kses_post( $review_content ),
				'comment_type'         => 'review',
				'comment_parent'       => 0,
				'comment_approved'     => 1,
				'comment_date'         => current_time( 'mysql' ),
				'comment_date_gmt'     => current_time( 'mysql', 1 ),
			);

			// Insert comment.
			$comment_id = wp_insert_comment( $comment_data );

			if ( $comment_id && is_numeric( $rating ) ) {
				// Add rating meta.
				update_comment_meta( $comment_id, 'rating', intval( $rating ) );

				// Update product rating count and average.
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$product->set_rating_counts( array() );
					$product->set_average_rating( '' );
					$product->set_review_count( 0 );
					WC_Comments::clear_transients( $product_id );
					$product->save();
				}

				$success++;
			} else {
				$skipped++;
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( sprintf( 'Imported %d reviews, skipped %d.', $success, $skipped ) );
	}
}
