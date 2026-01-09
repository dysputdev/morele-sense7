<?php
/**
 * Product Variants Preview Block Template
 *
 * @package MultiStore\Plugin\Block\ProductVariantsPreview
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\ProductVariantsPreview;

use MultiStore\Plugin\Repository\Relations_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post_id;

$show_label = false;

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
$relations           = $relation_repository->get_product_relations_by_context( $product_id, 'single' );

if ( empty( $relations ) ) {
	return;
}
// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'           => 'multistore-block-product-variants-preview',
		'data-product-id' => $product_id,
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<?php foreach ( $relations as $group_id => $group ) : ?>
		<div class="multistore-block-product-variants-preview__group" 
			data-attribute-id="<?php echo esc_attr( $group['attribute_id'] ); ?>"
		>
		
			<?php if ( $show_label ) : ?>
				<div class="multistore-block-product-variants-switch__label">
					<?php echo esc_html( $group['group_name'] ); ?>:
				</div>
			<?php endif; ?>
		

			<div class="multistore-block-product-variants-preview__options multistore-block-product-variants-preview__options--<?php echo esc_attr( $group['layout'] ); ?>">
			
				<?php foreach ( $group['relations'] as $index => $relation ) : ?>

					<?php
					if ( ! empty( $relation['custom_label'] ) && 'custom' === $relation['label_source'] ) {
						$label = $relation['custom_label'];
					} else {
						$attribute_values = $relation_repository->get_product_attribute_values( $relation['product_id'], $group['attribute_id'] );
						$label            = ! empty( $attribute_values ) ? join( ',', $attribute_values ) : get_the_title( $relation['product_id'] );
					}
					$relation_product = wc_get_product( $relation['product_id'] );

					$option_class = array(
						'multistore-block-product-variants-preview__option',
						'multistore-block-product-variants-preview__option--' . $group['layout'],
					);
					if ( (int) $relation['product_id'] === (int) $product_id ) {
						$option_class[] = 'is-current';
						$option_class[] = 'is-active';
					}
					?>
					<a href="<?php echo esc_url( get_permalink( $relation['product_id'] ) ); ?>"
						title="<?php echo esc_attr( get_the_title( $relation['product_id'] ) ); ?>"
						class="<?php echo esc_attr( implode( ' ', $option_class ) ); ?>"
						data-group-id="<?php echo esc_attr( $group_id ); ?>"
						data-product-id="<?php echo esc_attr( $relation['product_id'] ); ?>"
						data-product-sku="<?php echo esc_attr( $relation['product_sku'] ); ?>"
						>
						<?php
						if ( 'image_product' === $group['layout'] || 'image_custom' === $group['layout'] ) {
							$layout   = 'image';
							$image_id = 'image_custom' === $group['layout'] && ! empty( $relation['custom_image_id'] ) ? $relation['custom_image_id'] : 0;
							if ( ! empty( $image_id ) ) {
								$image = wp_get_attachment_image( $image_id, 'swatch', false, array( 'class' => 'multistore-block-product-variants-preview__option-image' ) );
							} else {
								// get product post featured image.
								$image = $relation_product->get_image( 'swatch', array( 'class' => 'multistore-block-product-variants-preview__option-image' ) );
							}
							echo wp_kses_post( $image );
						}
						?>
						<span class="multistore-block-product-variants-preview__option-label">
							<?php echo esc_html( $label ); ?>
						</span>
					</a>
				<?php endforeach; ?>

			</div>
		</div>
	<?php endforeach; ?>
</div>
