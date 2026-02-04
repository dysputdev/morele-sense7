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
