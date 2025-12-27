<?php
/**
 * Product Gallery Block template.
 *
 * @param array    $attributes The block attributes.
 * @param WP_Block $block The block settings and attributes.
 * @param string   $content The block inner HTML (empty).
 *
 * @package multistore
 */

namespace MultiStore\Plugin\Block\Product_Gallery;

$anchor     = '';
$class_name = 'multistore-block-product-gallery';
if ( ! empty( $block->anchor ) ) {
	$anchor = esc_attr( $block->anchor );
}

$block_attributes = get_block_wrapper_attributes(
	array(
		'id'    => $anchor,
		'class' => esc_attr( $class_name ),
	)
);

// Get product ID from block context.
$product_id = isset( $block->context['postId'] ) ? $block->context['postId'] : get_the_ID();
$post_type  = isset( $block->context['postType'] ) ? $block->context['postType'] : get_post_type( $product_id );

// Only show gallery for WooCommerce products.
if ( 'product' !== $post_type ) {
	return;
}

// Get WooCommerce product.
$product = wc_get_product( $product_id );

if ( ! $product ) {
	return;
}

// Get images from WooCommerce product.
$images              = array();
$show_featured_image = isset( $attributes['showFeaturedImage'] ) ? $attributes['showFeaturedImage'] : true;

// Add featured image first.
if ( $show_featured_image ) {
	$featured_image_id = $product->get_image_id();
	if ( $featured_image_id ) {
		$images[] = array(
			'id'  => $featured_image_id,
			'url' => wp_get_attachment_image_url( $featured_image_id, 'full' ),
			'alt' => get_post_meta( $featured_image_id, '_wp_attachment_image_alt', true ),
		);
	}
}

// Add gallery images.
$gallery_image_ids = $product->get_gallery_image_ids();
if ( ! empty( $gallery_image_ids ) ) {
	foreach ( $gallery_image_ids as $image_id ) {
		$images[] = array(
			'id'  => $image_id,
			'url' => wp_get_attachment_image_url( $image_id, 'full' ),
			'alt' => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
		);
	}
}

// Build slider configurations.
$main_slider_config      = isset( $attributes['mainSlider'] ) ? $attributes['mainSlider'] : array();
$thumbnail_slider_config = isset( $attributes['thumbnailSlider'] ) ? $attributes['thumbnailSlider'] : array();

$main_config      = build_slider_config( $main_slider_config );
$thumbnail_config = build_slider_config( $thumbnail_slider_config );

// Get arrow settings.
$arrow_path          = get_arrow_path( $attributes );
$show_arrows         = show_thumbnail_arrows( $attributes );
$arrow_position      = get_arrow_position( $attributes );
$arrow_position_attr = 'data-arrows-position="' . esc_attr( $arrow_position ) . '"';

// Generate unique IDs for this gallery instance.
$unique_id    = 'product-gallery-' . wp_unique_id();
$main_id      = $unique_id . '-main';
$thumbnail_id = $unique_id . '-thumbnail';

?>

<div <?php echo wp_kses_data( $block_attributes ); ?>>
	<?php if ( ! empty( $images ) ) : ?>

		<!-- Main Slider -->
		<div class="multistore-block-product-gallery__main">
			<div
				id="<?php echo esc_attr( $main_id ); ?>"
				class="splide multistore-block-product-gallery__main-slider"
				data-splide='<?php echo wp_json_encode( $main_config ); ?>'
			>
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $images as $image ) : ?>
							<li class="splide__slide">
								<?php if ( isset( $image['url'] ) ) : ?>
									<img
										src="<?php echo esc_url( $image['url'] ); ?>"
										alt="<?php echo esc_attr( isset( $image['alt'] ) ? $image['alt'] : '' ); ?>"
										class="multistore-block-product-gallery__image"
									/>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>

		<!-- Thumbnail Slider -->
		<div class="multistore-block-product-gallery__thumbnails">
			<div
				id="<?php echo esc_attr( $thumbnail_id ); ?>"
				class="splide multistore-block-product-gallery__thumbnail-slider"
				data-splide='<?php echo wp_json_encode( $thumbnail_config ); ?>'
				data-main-slider="#<?php echo esc_attr( $main_id ); ?>"
			>
				<div class="splide__track">
					<ul class="splide__list">
						<?php foreach ( $images as $image ) : ?>
							<li class="splide__slide">
								<?php if ( isset( $image['url'] ) ) : ?>
									<img
										src="<?php echo esc_url( $image['url'] ); ?>"
										alt="<?php echo esc_attr( isset( $image['alt'] ) ? $image['alt'] : '' ); ?>"
										class="multistore-block-product-gallery__thumbnail"
									/>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>

	<?php else : ?>
		<p class="multistore-block-product-gallery__empty">
			<?php esc_html_e( 'No images added to gallery.', 'multistore' ); ?>
		</p>
	<?php endif; ?>
</div>
