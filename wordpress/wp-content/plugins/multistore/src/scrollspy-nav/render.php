<?php

namespace Multistore\Plugin\Block\ScrollSpyNav;

$wrapper_id = $block->context['multistore/scrollspy-wrapper-id'] ?? '';
$is_sticky  = $attributes['isSticky'] ?? false;
$sticky_top = $attributes['stickyTop'] ?? 0;

$class = 'multistore-block-scrollspy-nav';
if ( $is_sticky ) {
	$class .= ' is-sticky';
}

$style = $is_sticky ? sprintf( 'top: %dpx;', $sticky_top ) : '';

// Find all sections in the wrapper.
$sections = array();
if ( ! empty( $block->parsed_block['innerBlocks'] ) ) {
	$parent_blocks = $block->parsed_block;
	// Navigate up to find wrapper.
	while ( $parent_blocks && 'multistore/scrollspy-wrapper' !== $parent_blocks['blockName'] ) {
		$parent_blocks = $parent_blocks['parent'] ?? null;
	}

	if ( $parent_blocks && ! empty( $parent_blocks['innerBlocks'] ) ) {
		foreach ( $parent_blocks['innerBlocks'] as $inner_block) {
			if ( 'multistore/scrollspy-section' === $inner_block['blockName'] ) {
				$sections[] = array(
					'id'    => $inner_block['attrs']['sectionId'] ?? '',
					'label' => $inner_block['attrs']['label'] ?? 'Section',
				);
			}
		}
	}
}
?>

<nav class="<?php echo esc_attr( $class ); ?>" style="<?php echo esc_attr( $style ); ?>">
	<ul class="multistore-block-scrollspy-nav__list">
		<?php foreach ( $sections as $section ): ?>
			<li class="multistore-block-scrollspy-nav__item">
				<a href="#<?php echo esc_attr( $section['id'] ); ?>" class="multistore-block-scrollspy-nav__link">
					<?php echo esc_html( $section['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>