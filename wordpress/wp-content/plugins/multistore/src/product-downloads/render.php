<?php
/**
 * Product Downloads Block Template
 *
 * @package MultiStore\Plugin\Block\ProductDownloads
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\ProductDownloads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$show_title = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
$title      = isset( $attributes['title'] ) ? $attributes['title'] : 'Do pobrania';

// Get postId from context.
$post_id = $block->context['multistore/postId'] ?? $block->context['postId'] ?? get_the_ID();

if ( ! $post_id ) {
	return;
}

// Get downloads data.
$downloads = get_product_downloads( $post_id );

if ( empty( $downloads ) ) {
	return;
}

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'multistore-block-product-downloads',
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<?php if ( $show_title && ! empty( $title ) ) : ?>
		<h2 class="multistore-block-product-downloads__title">
			<?php echo esc_html( $title ); ?>
		</h2>
	<?php endif; ?>

	<ul class="multistore-block-product-downloads__list">
		<?php foreach ( $downloads as $download ) : ?>
			<li class="multistore-block-product-downloads__item">
				<a href="<?php echo esc_url( $download['url'] ); ?>"
				   class="multistore-block-product-downloads__link"
				   target="_blank"
				   rel="noopener noreferrer"
				   download>
					<span class="multistore-block-product-downloads__icon">
						<?php if ( ! empty( $download['icon'] ) ) : ?>
							<img src="<?php echo esc_url( $download['icon'] ); ?>" alt="">
						<?php else : ?>
							📄
						<?php endif; ?>
					</span>
					<span class="multistore-block-product-downloads__name">
						<?php echo esc_html( $download['name'] ); ?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
