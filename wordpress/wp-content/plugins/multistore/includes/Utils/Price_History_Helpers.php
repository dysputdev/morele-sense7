<?php
/**
 * Price History Helper Functions
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Utils;

use MultiStore\Plugin\WooCommerce\Price_History;

/**
 * Helper functions for easy access to price history functionality
 *
 * @since 1.0.0
 */
class Price_History_Helpers {

	/**
	 * Get lowest price for a product from last 30 days
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return array|null Lowest price data or null.
	 */
	public static function get_lowest_price( int $product_id ): ?array {
		$price_history = new Price_History();
		return $price_history->get_lowest_price( $product_id );
	}

	/**
	 * Get formatted lowest price string
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return string Formatted price or empty string.
	 */
	public static function get_formatted_lowest_price( int $product_id ): string {
		$data = self::get_lowest_price( $product_id );

		if ( ! $data ) {
			return '';
		}

		return wc_price( (float) $data['price'] );
	}

	/**
	 * Display lowest price info
	 *
	 * @since 1.0.0
	 * @param int    $product_id Product ID.
	 * @param string $format     Format: 'full', 'price', 'simple'.
	 */
	public static function display_lowest_price( int $product_id, string $format = 'full' ): void {
		$data = self::get_lowest_price( $product_id );

		if ( ! $data ) {
			return;
		}

		$price = (float) $data['price'];
		$recorded_at = $data['recorded_at'];
		$days_ago = self::get_days_ago( $recorded_at );

		switch ( $format ) {
			case 'price':
				echo wc_price( $price );
				break;

			case 'simple':
				printf(
					/* translators: %s: formatted price */
					esc_html__( 'Lowest 30d: %s', 'multistore' ),
					wc_price( $price )
				);
				break;

			case 'full':
			default:
				printf(
					/* translators: 1: formatted price, 2: number of days */
					esc_html__( 'Lowest price in the last 30 days: %1$s (%2$d days ago)', 'multistore' ),
					wc_price( $price ),
					(int) $days_ago
				);
				break;
		}
	}

	/**
	 * Check if product has lowest price info
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return bool True if has info, false otherwise.
	 */
	public static function has_lowest_price( int $product_id ): bool {
		$price_history = new Price_History();
		return $price_history->has_price_history( $product_id );
	}

	/**
	 * Get price history for a product
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @param int $days       Number of days.
	 * @return array Array of price records.
	 */
	public static function get_price_history( int $product_id, int $days = 30 ): array {
		$price_history = new Price_History();
		return $price_history->get_price_history( $product_id, $days );
	}

	/**
	 * Get price statistics
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return array Statistics array.
	 */
	public static function get_price_stats( int $product_id ): array {
		$price_history = new Price_History();
		return $price_history->get_price_statistics( $product_id );
	}

	/**
	 * Calculate days ago from a date
	 *
	 * @since 1.0.0
	 * @param string $date Date string.
	 * @return int Number of days ago.
	 */
	private static function get_days_ago( string $date ): int {
		$recorded_timestamp = strtotime( $date );
		$current_timestamp  = current_time( 'timestamp' );
		$diff               = $current_timestamp - $recorded_timestamp;

		return (int) floor( $diff / DAY_IN_SECONDS );
	}
}
