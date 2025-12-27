<?php

namespace Multistore\Plugin\Block\ScrollSpyWrapper;

use function Multistore\Plugin\Block\ScrollSpyNav\render_scrollspy_nav;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attributes = array();

// if ( $block_attributes['anchor'] ) {
// 	$block_attributes['id'] = esc_attr( $block_attributes['anchor'] );
// }

$class_names = array( 'multistore-block-scrollspy' );
if ( ! empty( $attributes['className'] ) ) {
	$class_names[] = $attributes['className'];
}

$block_attributes['class'] = implode( ' ', $class_names );

$wrapper_attributes = get_block_wrapper_attributes( $block_attributes );

?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<?php echo render_scrollspy_nav( $attributes, $content, $block ); ?>

	<?php echo $content; ?>
</div>
