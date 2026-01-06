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
$currency_format = isset( $attributes['currencyFormat'] ) ? $attributes['currencyFormat'] : 'plain';
$prefix          = isset( $attributes['prefix'] ) ? $attributes['prefix'] : '';
$suffix          = isset( $attributes['suffix'] ) ? $attributes['suffix'] : '';
$show_empty      = isset( $attributes['showEmpty'] ) ? $attributes['showEmpty'] : false;

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

$css_class = array(
	'multistore-block-price-regular',
);
if ( $is_promotion ) {
	$css_class[] = 'multistore-block-price-regular--has-promotion';
}

$price = ( ! empty( $regular_price ) && $is_promotion ) ? format_price( $regular_price, $currency_format ) : '';

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'           => implode( ' ', $css_class ),
		'data-product-id' => $product_id,
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<span class="multistore-block-price-regular__wrapper">
		<?php if ( ! empty( $prefix ) ) : ?>
			<span class="multistore-block-price-regular__prefix">
				<?php echo esc_html( $prefix ); ?>
			</span>
		<?php endif; ?>

		<span class="multistore-block-price-regular__value"><?php echo wp_kses_post( $price ); ?></span>

		<?php if ( ! empty( $suffix ) ) : ?>
			<span class="multistore-block-price-regular__suffix">
				<?php echo esc_html( $suffix ); ?>
			</span>
		<?php endif; ?>
	</span>
</div>
