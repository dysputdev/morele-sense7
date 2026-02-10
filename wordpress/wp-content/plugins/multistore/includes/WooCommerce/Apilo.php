<?php

namespace MultiStore\Plugin\WooCommerce;

class Apilo {

	private $logger;

	public function __construct() {
		$this->logger = wc_get_logger();

		add_filter( 'rest_pre_dispatch', array( $this, 'apilo_log_request' ), 10, 3 );
		add_filter( 'rest_post_dispatch', array( $this, 'apilo_log_response' ), 10, 3 );
	}

	/**
	 * Filters the pre-calculated result of a REST API dispatch request.
	 *
	 * Allow hijacking the request before dispatching by returning a non-empty. The returned value
	 * will be used to serve the request instead.
	 *
	 * @since 4.4.0
	 *
	 * @param mixed           $result  Response to replace the requested version with. Can be anything
	 *                                 a normal endpoint can return, or null to not hijack the request.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 */
	public function apilo_log_request( $result, $server, $request ) {

		$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
		if ( strpos( $ua, 'Apilo' ) === false ) {
			return $result;
		}

		$this->log(
			array(
				'result' => $result,
				'server' => $server,
				'method' => $request->get_method(),
				'path'   => $request->get_route(),
				'params' => $request->get_params(),
			)
		);

		return $result;
	}

	/**
	 * Filters the REST API response.
	 *
	 * Allows modification of the response before returning.
	 *
	 * @since 4.4.0
	 * @since 4.5.0 Applied to embedded responses.
	 *
	 * @param WP_HTTP_Response $result  Result to send to the client. Usually a `WP_REST_Response`.
	 * @param WP_REST_Server   $server  Server instance.
	 * @param WP_REST_Request  $request Request used to generate the response.
	 */
	public function apilo_log_response( $result, $server, $request ) {

		$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
		if ( strpos( $ua, 'Apilo' ) === false ) {
			return $result;
		}

		$this->log(
			array(
				'result' => $result,
				'server' => $server,
				'method' => $request->get_method(),
				'path'   => $request->get_route(),
				'params' => $request->get_params(),
			)
		);

		return $result;
	}

	private function log( $data ) {
		try {
			$this->logger->info(
				wp_json_encode( $data, JSON_UNESCAPED_UNICODE ),
				array( 'source' => 'apilo-rest' )
			);
		} catch ( \Exception $e ) {
			$this->logger->info( $e->getMessage() );
		}
	}
}
