<?php

namespace MultiStore\Plugin\Block\Product;

function update_post_title_block( $block_content, $block ) {
	if ( 'core/post-title' !== $block['blockName'] ) {
		return $block_content;
	}

	if ( isset( $block['attrs']['useAsParagraph'] ) && true === $block['attrs']['useAsParagraph'] ) {
		return preg_replace(
			'/h([0-6])/',
			'p',
			$block_content,
			1
		);
	}

	return $block_content;
}
add_filter( 'render_block', __NAMESPACE__ . '\update_post_title_block', 10, 2 );
