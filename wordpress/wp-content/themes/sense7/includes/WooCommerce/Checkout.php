<?php

namespace Sense7\Theme\WooCommerce;

class Checkout {
	public function __construct() {

		// Enqueue assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue theme assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		// Enqueue checkout script on checkout page.
		if ( is_checkout() && file_exists( SENSE7_THEME_DIR . '/assets/js/checkout.js' ) ) {
			wp_enqueue_script(
				'sense7-checkout',
				SENSE7_THEME_URL . '/assets/js/checkout.js',
				array( 'jquery' ),
				SENSE7_THEME_VERSION,
				true
			);
		}
	}
}
