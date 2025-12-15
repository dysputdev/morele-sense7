<?php
/**
 * Price Current Block Functions
 *
 * @package MultiStore\Plugin\Block\PriceCurrent
 */

namespace MultiStore\Plugin\Block\PriceCurrent;

/**
 * Format price based on currency format setting
 *
 * @param float  $price  Price to format.
 * @param string $format Format type.
 * @return string Formatted price.
 */
function format_price( $price, $format = 'default' ): string {
	if ( ! function_exists( 'wc_price' ) ) {
		return '';
	}

	switch ( $format ) {
		case 'no_symbol':
			return wc_format_localized_price( $price );

		case 'symbol_only':
			return get_woocommerce_currency_symbol();

		case 'code':
			return get_woocommerce_currency();

		case 'default':
		default:
			return wc_price( $price );
	}
}
