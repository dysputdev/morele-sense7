<?php
/**
 * Dodaje obsługę atrybutu megamenuId w bloku core/navigation-link
 */

namespace MultiStore\Plugin\Block\Megamenu;

function add_megamenu_support_to_navigation_block() {
	// Rejestracja atrybutu na serwerze.
	register_block_type(
		'core/navigation-link',
		array(
			'attributes' => array(
				'megamenuId' => array(
					'type' => 'string',
					'default' => '',
				),
			),
		)
	);
}
add_action( 'init', 'add_megamenu_support_to_navigation_block' );

/**
 * Renderuje megamenu w bloku nawigacji.
 *
 * @param string $block_content - Zawartość bloku.
 * @param array  $block - Dane bloku.
 */
function render_navigation_with_megamenu( $block_content, $block ) {
	// Sprawdzamy czy to blok core/navigation-link z megamenu.
	if ( 'core/navigation-submenu' !== $block['blockName'] || empty( $block['attrs']['megamenuId'] ) ) {
		return $block_content;
	}

	$megamenu_id = sanitize_key( $block['attrs']['megamenuId'] );;

	if ( empty( $megamenu_id ) ) {
		return $block_content;
	}

	$block_content = str_replace(
		'<a',
		'<a data-megamenu="' . esc_attr( $megamenu_id ) . '"',
		$block_content
	);

	return $block_content;
}
add_filter( 'render_block', __NAMESPACE__ . '\render_navigation_with_megamenu', 10, 2 );
