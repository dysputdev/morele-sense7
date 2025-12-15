<?php
/**
 * Render callback dla bloku Simple Product
 *
 * @param array $attributes Atrybuty bloku.
 * @param string $content   Zawartość bloku.
 * @package multistore
 */

$product_id = isset( $attributes['postId'] ) ? intval( $attributes['postId'] ) : 0;

// Wsparcie dla Polylang.
if ( function_exists( 'pll_current_language' ) ) {
	$current_lang          = pll_current_language();
	$translated_product_id = pll_get_post( $product_id, $current_lang );
	if ( $translated_product_id ) {
		$product_id = $translated_product_id;
	}
}

if ( ! $product_id ) {
	return '';
}

$product = wc_get_product( $product_id );

if ( ! $product ) {
	return '';
}

global $post;
$original_post = $post;
$post          = get_post( $product_id );
setup_postdata( $post );

$wrapper_attributes = array(
	'class'           => 'multistore-block-product',
	'data-product-id' => $product_id,
);

$is_link  = isset( $attributes['isLink'] ) && $attributes['isLink'];
$tag_name = $is_link ? 'a' : 'div';
if ( $is_link ) {
	$wrapper_attributes['class'] .= ' is-link';
	$wrapper_attributes['href']   = get_permalink( $product_id );
	$wrapper_attributes['title']  = get_the_title( $product_id );
}

$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attributes );

$block_instance              = $block->parsed_block;
$block_instance['blockName'] = 'core/null';

$filter_block_context = static function ( $context ) use ( $post ) {
	$context['postType'] = $post->post_type;
	$context['postId']   = $post->ID;
	return $context;
};

add_filter( 'render_block_context', $filter_block_context, 1 );
$block_content = render_block( $block_instance );
remove_filter( 'render_block_context', $filter_block_context, 1 );

// printf( '<%s %s>', esc_attr( $tag ), wp_kses_data( $wrapper_attributes ) );
// echo $block_content;
// printf( '</%s>', esc_attr( $tag ) );

?>

<<?php echo esc_attr( $tag_name ); ?> <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<?php echo $block_content; ?>
</<?php echo esc_attr( $tag_name ); ?>>

<?php
$post = $original_post;
wp_reset_postdata();
