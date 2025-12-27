<?php
/**
 * Custom Comment Block Template
 *
 * @package MultiStore\Plugin\Block\CustomComment
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\CustomComment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$author_name  = isset( $attributes['authorName'] ) ? $attributes['authorName'] : 'Mateusz';
$rating       = isset( $attributes['rating'] ) ? absint( $attributes['rating'] ) : 5;
$product_name = isset( $attributes['productName'] ) ? $attributes['productName'] : 'Fotel SENSE7 Nobu Czarny';
$time_ago     = isset( $attributes['timeAgo'] ) ? $attributes['timeAgo'] : '2 miesiące temu';
$comment_content = isset( $attributes['content'] ) ? $attributes['content'] : '';

// Ensure rating is between 1 and 5.
$rating = max( 1, min( 5, $rating ) );

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'multistore-block-custom-comment',
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<div class="multistore-block-custom-comment__header">
		<div class="multistore-block-custom-comment__author">
			<?php echo esc_html( $author_name ); ?>
		</div>
		<div class="multistore-block-custom-comment__rating">
			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<span class="multistore-block-custom-comment__star <?php echo $i <= $rating ? 'multistore-block-custom-comment__star--filled' : ''; ?>">
					★
				</span>
			<?php endfor; ?>
		</div>
	</div>
	<div class="multistore-block-custom-comment__meta">
		<span class="multistore-block-custom-comment__product">
			<?php echo esc_html( $product_name ); ?>
		</span>
		<span class="multistore-block-custom-comment__time">
			<?php echo esc_html( $time_ago ); ?>
		</span>
	</div>
	<?php if ( ! empty( $comment_content ) ) : ?>
		<p class="multistore-block-custom-comment__content">
			<?php echo wp_kses_post( $comment_content ); ?>
		</p>
	<?php endif; ?>
</div>
