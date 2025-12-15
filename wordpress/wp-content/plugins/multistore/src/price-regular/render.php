<?php
/**
 * Price Regular Block Template
 *
 * @package MultiStore\Plugin\Block\PriceRegular
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\PriceRegular;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$currency_format = isset( $attributes['currencyFormat'] ) ? $attributes['currencyFormat'] : 'default';
$prefix          = isset( $attributes['prefix'] ) ? $attributes['prefix'] : '';
$suffix          = isset( $attributes['suffix'] ) ? $attributes['suffix'] : '';
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
// Get regular price.
$regular_price = $product->get_regular_price();

if ( empty( $regular_price ) && ! $show_empty ) {
	return;
}

$formatted_price = '';
$is_empty        = true;
if ( ! empty( $regular_price ) ) {
	$is_empty = false;
	// Format price based on currency format.
	$formatted_price = format_price( $regular_price, $currency_format );

	if ( empty( $formatted_price ) ) {
		return;
	}
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'multistore-block-price-regular',
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<span class="multistore-block-price-regular__wrapper">
		<?php if ( ! empty( $prefix ) && ! $is_empty ) : ?>
			<span class="multistore-block-price-regular__prefix"><?php echo esc_html( $prefix ); ?> </span>
		<?php endif; ?>
		<span class="multistore-block-price-regular__value"><?php echo wp_kses_post( $formatted_price ); ?></span>
		<?php if ( ! empty( $suffix ) && ! $is_empty) : ?>
			<span class="multistore-block-price-regular__suffix"> <?php echo esc_html( $suffix ); ?></span>
		<?php endif; ?>
	</span>
</div>
