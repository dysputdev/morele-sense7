<?php
/**
 * Hero Slider Item Block template.
 *
 * @param array $attributes The block attributes.
 * @param WPBlock $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 *
 * @package multistore
 */

namespace MultiStore\Plugin\Block\SliderSlide;

$anchor     = '';
$class_name = 'multistore-block-slider-slide splide__slide';

if ( ! empty( $block->anchor ) ) {
	$anchor = esc_attr( $block->anchor );
}

$block_attributes = get_block_wrapper_attributes(
	array(
		'data-slide-id' => $anchor,
		'class'         => esc_attr( $class_name ),
	)
);
?>

<li <?php echo wp_kses_data( $block_attributes ); ?> aria-label="slide-item">
	<?php echo $content; ?>
</li>
