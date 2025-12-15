<?php
/**
 * Hero Slider Block template.
 *
 * @param array $attributes The block attributes.
 * @param WPBlock $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 *
 * @package mutistore
 */

namespace MultiStore\Plugin\Block\Slider;

$anchor     = '';
$class_name = 'multistore-block-slider';
if ( ! empty( $block->anchor ) ) {
	$anchor = esc_attr( $block->anchor );
}


$block_attributes = get_block_wrapper_attributes(
	array(
		'id'         => $anchor,
		'class'      => esc_attr( $class_name ),
		'aria-label' => 'slider',
	)
);

// Build slider configuration from block attributes.
$slider_params      = build_splide_config( $attributes );
$arrow_path         = get_arrow_path( $attributes );
$arrows_classes     = get_arrow_classes( $attributes );
$pagination_classes = get_pagination_classes( $attributes );
$data_attr          = get_slider_data_attributes( $attributes );

?>

<div <?php echo wp_kses_data( $block_attributes ); ?>>
	<div class="splide"
		data-splide='<?php echo wp_json_encode( $slider_params ); ?>'
		<?php echo wp_kses_data( $data_attr ); ?>
	>
		<div class="<?php echo esc_attr( $arrows_classes ); ?>">
			<button class="splide__arrow splide__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Previous slide', 'multistore' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40" aria-hidden="true">
					<path d="<?php echo esc_attr( $arrow_path ); ?>"></path>
				</svg>
			</button>
			<button class="splide__arrow splide__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Next slide', 'multistore' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40" aria-hidden="true">
					<path d="<?php echo esc_attr( $arrow_path ); ?>"></path>
				</svg>
			</button>
		</div>

		<ul class="<?php echo esc_attr( $pagination_classes ); ?>" role="tablist"></ul>

		<div class="splide__track">
			<ul class="splide__list">
				<?php echo $content; ?>
			</ul>
		</div>
	</div>
</div>
