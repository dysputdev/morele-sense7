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
use MultiStore\Plugin\WooCommerce\Product_Group;

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
// $relation_repository = new Relations_Repository();
// $relations           = $relation_repository->get_full_relations_by_product_id( $product_id );
// $relations           = $relation_repository->get_grouped_product_relations_by_context( $product_id, 'single' );

$product_group = new Product_Group( $product_id, 'single' );
if ( ! $product_group->get_relations() ) {
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
	<?php foreach ( $product_group->get_groups() as $group_id => $group ) : ?>
		<div class="multistore-block-product-variants-switch__group" 
			data-attribute-id="<?php echo esc_attr( $group_id ); ?>"
		>
			
			<?php if ( $show_label ) : ?>
				<div class="multistore-block-product-variants-switch__label">
					<?php echo esc_html( $group['group_name'] ); ?>:
				</div>
			<?php endif; ?>

			<div class="multistore-block-product-variants-switch__options  multistore-block-product-variants-switch__options--<?php echo esc_attr( $group['layout'] ); ?>">
				<?php
				foreach ( $group['relations'] as $_product_id ) :

					$_product = $product_group->get_product( $_product_id ) ?? null;
					if ( ! $_product || ! isset( $_product->product_id ) ) {
						continue;
					}

					$is_current   = (int) $_product_id === (int) $product_id;
					$option_class = array( 'multistore-block-product-variants-switch__option' );
					if ( $is_current ) {
						$option_class[] = 'multistore-block-product-variants-switch__option--current';
					}
					?>
					<a href="<?php echo esc_url( get_permalink( $_product_id ) ); ?>"
						class="<?php echo esc_attr( implode( ' ', $option_class ) ); ?>"
					>
						<?php
						$label_class = array( 'multistore-block-product-variants-switch__variant-label' );
						// if ( ! empty( $relation['custom_label'] ) && 'custom' === $relation['label_source'] ) {
						// 	$label_class[] = 'multistore-block-product-variants-switch__variant-label--custom';
						// } else {
						// 	$attribute_values = $relation_repository->get_product_attribute_values( $relation['product_id'], $group['attribute_id'] );
						// 	$label_class[]    = ( ! empty( $attribute_values ) ) ? 'multistore-block-product-variants-switch__variant-label--attribute' : 'multistore-block-product-variants-switch__variant-label--product';
						// }

						if ( in_array( $group['layout'], array( 'image_product', 'image_custom' ), true ) ) {
							$image = $product_group->get_product_swatch_image( $_product_id, $group_id, 'swatch', array( 'class' => 'multistore-block-product-variants-switch__variant-image' ) );
							echo wp_kses_post( $image );
						}
						?>
						<span class="<?php echo esc_attr( implode( ' ', $label_class ) ); ?>">
							<?php
							$label = $product_group->get_product_label( $_product_id, $group_id );
							echo esc_html( $label );
							?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
