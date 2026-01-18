<?php
/**
 * Helper Functions
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Utils;

/**
 * Class Helpers
 *
 * Collection of helper functions
 *
 * @since 1.0.0
 */
class Helpers {
	/**
	 * Format price based on currency format setting
	 *
	 * @param float  $price  Price to format.
	 * @param string $format Format type.
	 * @return string Formatted price.
	 */
	public static function format_price( $price, $format = 'default' ): string {
		if ( ! function_exists( 'wc_price' ) ) {
			return $price;
		}

		switch ( $format ) {
			case 'no_symbol':
				return wc_format_localized_price( $price );

			case 'symbol_only':
				return get_woocommerce_currency_symbol();

			case 'code':
				return get_woocommerce_currency();

			case 'formatted':
				return wc_price( $price );

			case 'default':
			default:
				return wp_strip_all_tags( wc_price( $price ) );
		}
	}
	/**
	 * Calculate days ago from a date
	 *
	 * @param string $date Date string.
	 * @return int Number of days ago.
	 */
	public static function get_days_ago( string $date ): int {
		$recorded_timestamp = strtotime( $date );
		$current_timestamp  = current_time( 'timestamp' );
		$diff               = $current_timestamp - $recorded_timestamp;

		return (int) floor( $diff / DAY_IN_SECONDS );
	}
}
