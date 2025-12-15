<?php
/**
 * Price Lowest Block Template
 *
 * @package MultiStore\Plugin\Block\PriceLowest
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\PriceLowest;

use MultiStore\Plugin\Utils\Price_History_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$currency_format = isset( $attributes['currencyFormat'] ) ? $attributes['currencyFormat'] : 'default';
$prefix          = isset( $attributes['prefix'] ) ? $attributes['prefix'] : __( 'Najniższa cena: ', 'multistore' );
$suffix          = isset( $attributes['suffix'] ) ? $attributes['suffix'] : '';
$show_days_ago   = isset( $attributes['showDaysAgo'] ) ? $attributes['showDaysAgo'] : false;
$show_empty      = isset( $attributes['showEmpty'] ) ? $attributes['showEmpty'] : true;

// Get postId from context.
$product_id = $block->context['multistore/postId'] ?? $block->context['postId'] ?? get_the_ID();

if ( ! $product_id ) {
	return;
}

// Get lowest price data using global helper function.
$lowest_price_data = Price_History_Helpers::get_lowest_price( $product_id );

if ( ( ! $lowest_price_data || ! isset( $lowest_price_data['price'] ) ) && ! $show_empty ) {
	return;
}

$lowest_price    = '';
$recorded_at     = '';
$formatted_price = '';
$is_empty        = true;

if ( isset( $lowest_price_data['price'] ) ) {
	$is_empty     = false;
	$lowest_price = (float) $lowest_price_data['price'];
	$recorded_at  = $lowest_price_data['recorded_at'] ?? '';

	// Format price based on currency format.
	$formatted_price = format_price( $lowest_price, $currency_format );

	if ( empty( $formatted_price ) ) {
		return;
	}

	// Calculate days ago if needed.
	$days_ago = 0;
	if ( $show_days_ago && ! empty( $recorded_at ) ) {
		$days_ago = get_days_ago( $recorded_at );
	}
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'multistore-block-price-lowest',
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<span class="multistore-block-price-lowest__wrapper">
		<?php if ( ! empty( $prefix ) && ! $is_empty ) : ?>
			<span class="multistore-block-price-lowest__prefix"><?php echo esc_html( $prefix ); ?> </span>
		<?php endif; ?>
		<span class="multistore-block-price-lowest__value"><?php echo wp_kses_post( $formatted_price ); ?></span>
		<?php if ( ! empty( $suffix ) && ! $is_empty ) : ?>
			<span class="multistore-block-price-lowest__suffix"> <?php echo esc_html( $suffix ); ?></span>
		<?php endif; ?>
		<?php if ( $show_days_ago && $days_ago > 0 && ! $is_empty ) : ?>
			<span class="multistore-block-price-lowest__days">
				(<?php
					printf(
						/* translators: %d: number of days */
						esc_html( _n( '%d dzień temu', '%d dni temu', $days_ago, 'multistore' ) ),
						(int) $days_ago
					);
				?>)
			</span>
		<?php endif; ?>
	</span>
</div>
