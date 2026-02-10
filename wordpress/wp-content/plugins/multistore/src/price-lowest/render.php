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

use MultiStore\Plugin\Utils\Helpers;
use MultiStore\Plugin\Utils\Price_History_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$currency_format = isset( $attributes['currencyFormat'] ) ? $attributes['currencyFormat'] : 'default';
$prefix          = isset( $attributes['prefix'] ) ? $attributes['prefix'] : __( 'Najniższa cena: ', 'multistore' );
$suffix          = isset( $attributes['suffix'] ) ? $attributes['suffix'] : '';
$tooltip         = isset( $attributes['tooltip'] ) ? $attributes['tooltip'] : '';
$show_days_ago   = isset( $attributes['showDaysAgo'] ) ? $attributes['showDaysAgo'] : false;
$show_empty      = isset( $attributes['showEmpty'] ) ? $attributes['showEmpty'] : true;

// Get postId from context.
$product_id = $block->context['multistore/postId'] ?? $block->context['postId'] ?? get_the_ID();

if ( ! $product_id ) {
	return;
}

// Get product.
$product = wc_get_product( $product_id );

if ( ! $product ) {
	return;
}

$is_promotion = $product->is_on_sale();
if ( ! $is_promotion && ! $show_empty ) {
	return;
}

// Get lowest price data using global helper function.
$lowest_price_data = Price_History_Helpers::get_lowest_price( $product_id );

if ( ( ! $lowest_price_data || ! isset( $lowest_price_data['price'] ) ) && ! $show_empty ) {
	return;
}

$lowest_price = (float) $lowest_price_data['price'];
$recorded_at  = $lowest_price_data['recorded_at'] ?? '';
$days_ago     = 0;
$days_ago     = ( $show_days_ago && ! empty( $recorded_at ) ) ? Helpers::get_days_ago( $recorded_at ) : 0;
$price        = ( ! empty( $lowest_price ) && $is_promotion ) ? Helpers::format_price( $lowest_price, $currency_format ) : '';
$tooltip      = str_replace( '{price}', $price, $tooltip );

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'           => 'multistore-block-price-lowest',
		'data-product-id' => $product_id,
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<span class="multistore-block-price-lowest__wrapper">
		<?php if ( ! empty( $prefix ) ) : ?>
			<span class="multistore-block-price-lowest__prefix">
				<?php echo esc_html( $prefix ); ?>
			</span>
		<?php endif; ?>

		<span class="multistore-block-price-lowest__value"><?php echo wp_kses_post( $price ); ?></span>

		<?php if ( ! empty( $suffix ) ) : ?>
			<span class="multistore-block-price-lowest__suffix">
				<?php echo esc_html( $suffix ); ?>
			</span>
		<?php endif; ?>

		<?php if ( ! empty( $tooltip ) ) :
			multistore_template_part( 'elements/tooltip', null, array( 'tooltip' => $tooltip ) );
		endif; ?>

		<?php if ( $show_days_ago && $days_ago > 0 ) : ?>
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
