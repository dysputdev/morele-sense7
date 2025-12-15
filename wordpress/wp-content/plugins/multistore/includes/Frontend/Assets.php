<?php
/**
 * Frontend Assets
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Frontend;

/**
 * Class Assets
 *
 * Handles frontend assets (CSS, JS)
 *
 * @since 1.0.0
 */
class Assets {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets(): void {
		// Enqueue CSS.
		wp_enqueue_style(
			'multistore-plugin-styles',
			MULTISTORE_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			MULTISTORE_PLUGIN_VERSION
		);

		// Enqueue JS.
		wp_enqueue_script(
			'multistore-plugin-scripts',
			MULTISTORE_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			MULTISTORE_PLUGIN_VERSION,
			true
		);

		// Localize script with data.
		wp_localize_script(
			'multistore-plugin-scripts',
			'multistoreData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'multistore_nonce' ),
			)
		);
	}
}
