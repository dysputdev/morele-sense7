<?php
/**
 * Product Variants Block Template
 *
 * @package MultiStore\Plugin\Block\ProductVariants
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\ProductVariants;

use MultiStore\Plugin\Repository\Relations_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_label = true;

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

// Get variants data.
$relation_repository = new Relations_Repository();
$relations           = $relation_repository->get_grouped_product_relations_by_context( $product_id, 'single' );

if ( empty( $relations ) ) {
	return;
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'           => 'multistore-block-product-variants-switch',
		'data-product-id' => $product_id,
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<?php foreach ( $relations as $group_id => $group ) : ?>
		<div class="multistore-block-product-variants-switch__group" 
			data-attribute-id="<?php echo esc_attr( $group_id ); ?>"
		>
			
			<?php if ( $show_label ) : ?>
				<div class="multistore-block-product-variants-switch__label">
					<?php echo esc_html( $group['group_name'] ); ?>:
				</div>
			<?php endif; ?>

			<div class="multistore-block-product-variants-switch__options  multistore-block-product-variants-switch__options--<?php echo esc_attr( $group['layout'] ); ?>">
				<?php foreach ( $group['relations'] as $index => $relation ) : ?>
					<a href="<?php echo esc_url( get_permalink( $relation['product_id'] ) ); ?>"
						class="multistore-block-product-variants-switch__option"
					>
						<?php
						$label_class = array( 'multistore-block-product-variants-switch__variant-label' );
						if ( ! empty( $relation['custom_label'] ) && 'custom' === $relation['label_source'] ) {
							$label         = $relation['custom_label'];
							$label_class[] = 'multistore-block-product-variants-switch__variant-label--custom';
						} else {
							$attribute_values = $relation_repository->get_product_attribute_values( $relation['product_id'], $group['attribute_id'] );
							$label            = ! empty( $attribute_values ) ? $attribute_values[0] : get_the_title( $relation['product_id'] );
							$label_class[]    = ( ! empty( $attribute_values ) ) ? 'multistore-block-product-variants-switch__variant-label--attribute' : 'multistore-block-product-variants-switch__variant-label--product';
						}

						if ( 'image_product' === $group['layout'] || 'image_custom' === $group['layout'] ) {
							$layout   = 'image';
							$image_id = 'image_custom' === $group['layout'] && ! empty( $relation['custom_image_id'] ) ? $relation['custom_image_id'] : 0;
							if ( ! empty( $image_id ) ) {
								$image = wp_get_attachment_image( $image_id, 'swatch', false, array( 'class' => 'multistore-block-product-variants-switch__variant-image' ) );
							} else {
								// get product post featured image.
								$image = get_the_post_thumbnail( $relation['product_id'], 'swatch', array( 'class' => 'multistore-block-product-variants-switch__variant-image' ) );
							}

							echo wp_kses_post( $image );
						}
						?>
						<span class="<?php echo esc_attr( implode( ' ', $label_class ) ); ?>">
							<?php echo esc_html( $label ); ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
