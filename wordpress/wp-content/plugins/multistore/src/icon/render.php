<?php
/**
 * Icon Block template.
 *
 * @param array $attributes The block attributes.
 * @param WPBlock $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 *
 * @package multistore
 */

namespace MultiStore\Plugin\Block\Icon;

// migrate acf data to block attributes.
if ( isset( $attributes['data'] ) && ! empty( $attributes['data'] ) ) {
	$attributes['file'] = $attributes['data']['icon'] ? $attributes['data']['icon'] . '.svg' : $attributes['file'];
	$attributes['size'] = $attributes['data']['size'] ?? $attributes['size'];
}

$icon = isset( $attributes['file'] ) ? $attributes['file'] : '';
if ( empty( $icon ) ) {
	return '';
}

$icons_dir = MULTISTORE_PLUGIN_DIR . 'assets/icons/';
$icons_url = MULTISTORE_PLUGIN_URL . 'assets/icons/';

$icon_pack = $attributes['pack'] ?? 'v1';
$icon_path = $icons_dir . $icon_pack . '/' . str_replace( '.svg', '', $icon ) . '.svg';
if ( ! file_exists( $icon_path ) ) {
	return '';
}

$svg_content = file_get_contents( $icon_path );

$wrapper_classes = array( 'multistore-block-icon' );
if ( isset( $attributes['className'] ) ) {
	$wrapper_classes[] = $attributes['className'];
}

if ( isset( $attributes['iconPosition'] ) ) {
	$wrapper_classes[] = 'has-icon-' . $attributes['iconPosition'];
}

$block_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
	)
);

$default_icon_size = 48;
if ( isset( $attributes['size'] ) && 'custom' === $attributes['size'] ) {
	$icon_size = $attributes['customSize'];
} elseif ( isset( $attributes['size'] ) ) {
	$size  = $attributes['size'];
	$sizes = array(
		'x-small'    => '16',
		'small'      => '24',
		'medium'     => '32',
		'default'    => '48',
		'large'      => '56',
		'x-large'    => '64',
		'xx-large'   => '72',
		'xxx-large'  => '92',
		'xxxx-large' => '128',
	);

	$icon_size = $sizes[ $size ] ?? $icon_size;
}

if ( ! is_numeric( $icon_size ) ) {
	$icon_size = $default_icon_size;
}

?>

<figure <?php echo $block_attributes ?>>
	<?php if ( 'svg' === pathinfo( $icon_path, PATHINFO_EXTENSION ) ): ?>
		<?php echo get_clear_svg_content( $svg_content, $icon_size ); ?>
	<?php endif; ?>
</figure>
