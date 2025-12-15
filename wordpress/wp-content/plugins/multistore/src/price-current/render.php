<?php
/**
 * Price Current Block Template
 *
 * @package MultiStore\Plugin\Block\PriceCurrent
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\PriceCurrent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$currency_format = isset( $attributes['currencyFormat'] ) ? $attributes['currencyFormat'] : 'default';
$prefix          = isset( $attributes['prefix'] ) ? $attributes['prefix'] : '';
$suffix          = isset( $attributes['suffix'] ) ? $attributes['suffix'] : '';

// Get postId from context.
$post_id = $block->context['multistore/postId'] ?? $block->context['postId'] ?? get_the_ID();

if ( ! $post_id ) {
	return;
}

// Get product.
$product = wc_get_product( $post_id );

if ( ! $product ) {
	return;
}

// Get current/sale price.
$current_price = $product->get_price();
$is_promotion  = $product->is_on_sale();

if ( empty( $current_price ) ) {
	return;
}

// Format price based on currency format.
$formatted_price = format_price( $current_price, $currency_format );

if ( empty( $formatted_price ) ) {
	return;
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $is_promotion ? 'multistore-block-price-current multistore-block-price-current--promotion' : 'multistore-block-price-current',
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<span class="multistore-block-price-current__wrapper">
		<?php if ( ! empty( $prefix ) ) : ?>
			<span class="multistore-block-price-current__prefix"><?php echo esc_html( $prefix ); ?> </span>
		<?php endif; ?>
		<span class="multistore-block-price-current__value"><?php echo wp_kses_post( $formatted_price ); ?></span>
		<?php if ( ! empty( $suffix ) ) : ?>
			<span class="multistore-block-price-current__suffix"> <?php echo esc_html( $suffix ); ?></span>
		<?php endif; ?>
	</span>
</div>
