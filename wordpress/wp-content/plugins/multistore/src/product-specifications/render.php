<?php
/**
 * Product Specifications Block Template
 *
 * @package MultiStore\Plugin\Block\ProductSpecifications
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\ProductSpecifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$show_title = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
$title      = isset( $attributes['title'] ) ? $attributes['title'] : 'Specyfikacja';

// Get postId from context.
$product_id = $block->context['multistore/postId'] ?? $block->context['postId'] ?? get_the_ID();

if ( ! $product_id ) {
	return;
}

// Get specifications data.
$specifications = get_product_specifications( $product_id );

if ( empty( $specifications ) ) {
	return;
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'multistore-block-product-specifications',
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<?php if ( $show_title && ! empty( $title ) ) : ?>
		<h2 class="multistore-block-product-specifications__title">
			<?php echo esc_html( $title ); ?>
		</h2>
	<?php endif; ?>

	<table class="multistore-block-product-specifications__table">
		<tbody class="multistore-block-product-specifications__tbody">
			<?php foreach ( $specifications as $spec ) : ?>
				<tr class="multistore-block-product-specifications__row">
					<td class="multistore-block-product-specifications__label">
						<?php echo esc_html( $spec['label'] ); ?>
					</td>
					<td class="multistore-block-product-specifications__value">
						<?php echo esc_html( $spec['value'] ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
