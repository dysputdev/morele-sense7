<?php
/**
 * Product Grouping Toggle Block Template
 *
 * @package MultiStore\Plugin\Block\ProductGroupingToggle
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\ProductGroupingToggle;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes.
$show_label = $attributes['showLabel'] ?? true;
$label_on   = $attributes['labelOn'] ?? __( 'Grupuj produkty', 'multistore' );
$label_off  = $attributes['labelOff'] ?? __( 'Pokaż wszystkie warianty', 'multistore' );

// Get current state from cookie.
$is_grouped = isset( $_COOKIE['multistore_product_grouping'] ) ? ( 'on' === $_COOKIE['multistore_product_grouping'] ) : true;

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'        => 'multistore-block-product-grouping-toggle',
		'data-grouped' => $is_grouped ? 'true' : 'false',
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<button
		type="button"
		class="multistore-block-product-grouping-toggle__button <?php echo $is_grouped ? 'multistore-block-product-grouping-toggle__button--on' : 'multistore-block-product-grouping-toggle__button--off'; ?>"
		aria-pressed="<?php echo $is_grouped ? 'true' : 'false'; ?>"
		data-label-on="<?php echo esc_attr( $label_on ); ?>"
		data-label-off="<?php echo esc_attr( $label_off ); ?>"
	>
		<?php if ( $is_grouped ) : ?>
			<!-- Grouped icon (compact grid) -->
			<svg class="multistore-block-product-grouping-toggle__icon multistore-block-product-grouping-toggle__icon--grouped" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<rect x="2" y="2" width="7" height="7" rx="1" fill="currentColor"/>
				<rect x="11" y="2" width="7" height="7" rx="1" fill="currentColor"/>
				<rect x="2" y="11" width="7" height="7" rx="1" fill="currentColor"/>
				<rect x="11" y="11" width="7" height="7" rx="1" fill="currentColor"/>
			</svg>
		<?php else : ?>
			<!-- Ungrouped icon (list with spacing) -->
			<svg class="multistore-block-product-grouping-toggle__icon multistore-block-product-grouping-toggle__icon--ungrouped" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<rect x="2" y="2" width="16" height="3" rx="1" fill="currentColor"/>
				<rect x="2" y="8.5" width="16" height="3" rx="1" fill="currentColor"/>
				<rect x="2" y="15" width="16" height="3" rx="1" fill="currentColor"/>
			</svg>
		<?php endif; ?>

		<?php if ( $show_label ) : ?>
			<span class="multistore-block-product-grouping-toggle__label">
				<?php echo esc_html( $is_grouped ? $label_on : $label_off ); ?>
			</span>
		<?php endif; ?>
	</button>
</div>
