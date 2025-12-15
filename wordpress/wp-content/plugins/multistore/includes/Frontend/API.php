<?php
/**
 * Frontend API
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Frontend;

/**
 * Class API
 *
 * Handles API requests
 *
 * @since 1.0.0
 */
class API {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register API routes
	 *
	 * @return void
	 */
	public function register_routes() {
		// Register API routes here.
	}

	public function handle_sync_price() {
		// Handle sync price request here.
	}
}
