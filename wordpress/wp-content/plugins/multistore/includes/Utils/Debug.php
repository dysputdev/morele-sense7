<?php

namespace MultiStore\Plugin\Utils;

class Debug {

	public static function dump( ...$vars ) {
		$is_cli = defined( 'WP_CLI' ) && \WP_CLI;
		if ( $is_cli ) {
			foreach ( $vars as $var ) {
				\WP_CLI::log( $var );
			}
			return;
		}

		if ( ! $is_cli ) {
			echo '<pre style="background:#111;color:#0f0;padding:10px;border-radius:5px;font-size:13px;">';
		}
		foreach ( $vars as $var ) {
			var_export( $var );
		}

		if ( ! $is_cli ) {
			echo '</pre>';
		}
	}

	public static function dd( ...$vars ) {
		self::dump( ...$vars );
		die;
	}

	public static function log( ...$vars ) {
		foreach ( $vars as $var_key => $var ) {
			error_log( self::stringify( $var ) );
		}
	}

	public static function stringify( $var ) {
		if ( is_scalar( $var ) || is_null( $var ) ) {
			return (string) $var;
		}
		return print_r( $var, true );
	}
}
