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
use MultiStore\Plugin\Utils\Debug;
use MultiStore\Plugin\Utils\Helpers;
use MultiStore\Plugin\Utils\Price_History_Helpers;
use MultiStore\Plugin\WooCommerce\Product_Group;
use MultiStore\Plugin\WooCommerce\Product_Grouping;

use function MultiStore\Plugin\Block\SimplifiedProductName\get_product_name;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post_id;

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

$product_group = new Product_Group( $product_id );
if ( ! $product_group->get_relations() ) {
	return;
}

$visibility_layout = $attributes['visibility'] ?? 'visible';
$button_text       = $attributes['buttonText'] ?? __( 'Wiecej opcji', 'multistore' );
$show_group_label  = $attributes['showGroupLabel'] ?? false;
$show_item_label   = $attributes['showItemLabel'] ?? true;
$currency_format   = $attributes['currencyFormat'] ?? 'default';

$product_group->set_currency_format( $currency_format );

$css_class = array(
	'multistore-block-product-variants-preview',
	'multistore-block-product-variants-preview--layout-' . $visibility_layout,
);

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'           => implode( ' ', $css_class ),
		'data-product-id' => $product_id,
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>

	<?php if ( 'hidden' === $visibility_layout ) : ?>
		<div class="multistore-block-product-variants-preview__more">
			<button type="button" class="multistore-block-product-variants-preview__more-button">
				<?php echo esc_attr( $button_text ); ?>
			</button>
		</div>
	<?php endif; ?>
	
	<div class="multistore-block-product-variants-preview__groups">
	<?php foreach ( $product_group->get_groups() as $group_id => $group ) : ?>
		<div class="multistore-block-product-variants-preview__group"
			data-group-id="<?php echo esc_attr( $group_id ); ?>"
			data-attribute-id="<?php echo esc_attr( $group['attribute_id'] ); ?>"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
		>
			<?php if ( $show_group_label ) : ?>
				<div class="multistore-block-product-variants-switch__label">
					<?php echo esc_html( $group['group_name'] ); ?>:
				</div>
			<?php endif; ?>

			<div class="multistore-block-product-variants-preview__options multistore-block-product-variants-preview__options--<?php echo esc_attr( $group['layout'] ); ?>">

				<?php foreach ( $group['relations'] as $_product_id ) : ?>

					<?php

					$_product = $product_group->get_product( $_product_id ) ?? null;
					if ( ! $_product || ! isset( $_product->product_id ) ) {
						continue;
					}

					$option_class = array(
						'multistore-block-product-variants-preview__option',
						'multistore-block-product-variants-preview__option--' . $group['layout'],
					);

					if ( (int) $product_id === (int) $_product_id ) {
						$option_class[] = 'is-current';
						$option_class[] = 'is-active';
					}

					$product_map = $product_group->get_product_relations_map( $_product_id );
					if ( isset( $product_map[ $group_id ] ) && ! in_array( $product_id, $product_map[ $group_id ], true ) ) {
						$option_class[] = 'is-hidden';
					}

					$product_details = $product_group->get_product_details( $_product_id );

					?>
					<a href="<?php echo esc_url( get_permalink( $_product_id ) ); ?>"
						title="<?php echo esc_attr( get_the_title( $_product_id ) ); ?>"
						class="<?php echo esc_attr( implode( ' ', $option_class ) ); ?>"
						data-group-id="<?php echo esc_attr( $group_id ); ?>"
						data-product-id="<?php echo esc_attr( $_product_id ); ?>"
						data-related="<?php echo esc_attr( wp_json_encode( $product_map ) ); ?>"
						data-product-details="<?php echo esc_attr( wp_json_encode( $product_details ) ); ?>"
						>
						<?php
						if ( in_array( $group['layout'], array( 'image_product', 'image_custom' ), true ) ) {
							$image = $product_group->get_product_swatch_image( $_product_id, $group_id, 'swatch', array( 'class' => 'multistore-block-product-variants-preview__option-image' ) );
							echo wp_kses_post( $image );
						}
						?>
						
						<?php if ( ! in_array( $group['layout'], array( 'image_product', 'image_custom' ), true ) || $show_item_label ) : ?>
							<span class="multistore-block-product-variants-preview__option-label">
								<?php
								$label = $product_group->get_product_label( $_product_id, $group_id );
								echo esc_html( $label );
								?>
							</span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>

			</div>
		</div>
	<?php endforeach; ?>
	</div>
</div>