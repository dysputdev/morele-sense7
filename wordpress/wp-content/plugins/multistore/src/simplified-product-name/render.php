<?php
/**
 * Simplified Product Name Block Template
 *
 * @package MultiStore\Plugin\Block\SimplifiedProductName
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\SimplifiedProductName;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$tag_name    = isset( $attributes['tagName'] ) ? $attributes['tagName'] : 'h2';
$is_link     = isset( $attributes['isLink'] ) ? $attributes['isLink'] : false;
$custom_name = isset( $attributes['customName'] ) ? trim( $attributes['customName'] ) : '';

// Validate tag name for security.
$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div' );
if ( ! in_array( $tag_name, $allowed_tags, true ) ) {
	$tag_name = 'h2';
}

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

// Get product name (simplified or regular).
if ( ! empty( $custom_name ) ) {
	$product_name = $custom_name;
} else {
	$product_name = get_product_name( $product_id );
}

if ( empty( $product_name ) ) {
	return;
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'multistore-block-simplified-product-name',
	)
);

// Get product permalink if link is enabled.
$product_link = $is_link ? get_permalink( $product_id ) : '';
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<<?php echo esc_html( $tag_name ); ?> class="multistore-block-simplified-product-name__title">
		<?php if ( $is_link && ! empty( $product_link ) ) : ?>
			<a href="<?php echo esc_url( $product_link ); ?>" class="multistore-block-simplified-product-name__link">
				<?php echo esc_html( $product_name ); ?>
			</a>
		<?php else : ?>
			<?php echo esc_html( $product_name ); ?>
		<?php endif; ?>
	</<?php echo esc_html( $tag_name ); ?>>
</div>
