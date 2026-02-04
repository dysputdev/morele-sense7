<?php
/**
 * Global functions without namespace.
 *
 * Description.
 *
 * @since Version 3 digits
 */

if ( ! function_exists( 'dd' ) ) {
	function dd( ...$vars ) {
		MultiStore\Plugin\Utils\Debug::dd( ...$vars );
	}
}

if ( ! function_exists( 'dump' ) ) {

	function dump( ...$vars ) {
		MultiStore\Plugin\Utils\Debug::dump( ...$vars );
	}
}

if ( ! function_exists( 'log' ) ) {
	function log( ...$vars ) {
		MultiStore\Plugin\Utils\Debug::log( ...$vars );
	}
}

function multistore_template_part( $slug, $name = null, $args = array() ) {
	$template_file = $slug;
	if ( $name ) {
		$template_file = "{$slug}-{$name}";
	}
	$template = locate_template( "multistore/template-parts/{$template_file}.php" );
	if ( ! $template ) {
		$template = MULTISTORE_PLUGIN_DIR . "/template-parts/{$template_file}.php";
	}
	if ( file_exists( $template ) ) {
		load_template( $template, false, $args );
	}
}
