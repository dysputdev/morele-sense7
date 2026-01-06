<?php
/**
 * Price Regular Block Functions
 *
 * @package MultiStore\Plugin\Block\PriceRegular
 */

namespace MultiStore\Plugin\Block\PriceRegular;

/**
 * Format price based on currency format setting
 *
 * @param float  $price  Price to format.
 * @param string $format Format type.
 * @return string Formatted price.
 */
function format_price( $price, $format = 'default' ): string {
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
