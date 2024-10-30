<?php
/**
 * Plugin HTTP Client.
 * 
 * Handles POST and GET requests with GuzzleHttp.
 * 
 * @package clevernode-related-content
 * @since   1.0.0
 */
namespace CleverNodeRCFree\Classes;

/**
 * Don't load directly.
 */
if( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Use required classes.
 */
use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use CleverNodeRCFree\Utils\Utility;

/**
 * HTTP client.
 * 
 * Handles HTTP requests.
 *
 * @since 1.0.0
 */
if ( !class_exists( 'Http_Client' ) ) :

	class Http_Client {

		protected $client, $utility;

		public function __construct() {
			$this->client  = new \GuzzleHttp\Client();
			$this->utility = new Utility();
		}

		/**
		 * Send POST Request.
		 * 
		 * Send a POST request to an endpoint with json data.
		 * 
		 * @since 1.0.0
		 * 
		 * @param string $endpoint Endpoint for POST request.
		 * @param string $json     JSON object data for POST request.
		 */
		public function send_post_request( $endpoint, $json ) {
			$http = $this->client;

			// Activate WP_DEBUG
			$this->utility->do_log("Request Body");
			$this->utility->do_log($json);
			$this->utility->do_log("Endpoint");
			$this->utility->do_log($endpoint);

			// Error fallback
			$error = array(
				'status' => 503,
				'body' => array(
					'message' => __( 'Service unavailable. Maybe the hosting service is blocking HTTP requests.', 'clevernode-related-content' )
				)
			);

			try {

				$res = $http->request( 'POST', $endpoint, [
					'verify'  => true,
					'headers' => [
						'accept' => 'application/json'
					],
					'timeout' => 30,
					'json'    => $json
				] );

				if( ! isset( $res ) || empty( $res ) || ! is_object( $res ) ) {
					return $error;
				}

				if( ! method_exists( $res, 'getStatusCode' ) || ! method_exists( $res, 'getBody' ) ) {
					return $error;
				}

				$status = $res->getStatusCode();
				$body = $res->getBody()->getContents();
				$body = json_decode( $body, true );

				if( ! isset( $body ) || empty( $body ) || json_last_error() !== JSON_ERROR_NONE ) {
					$status = 403;
					$body = array( 'message' => __( 'Forbidden. There is an error decoding JSON response.', 'clevernode-related-content' ) );
				}

				$response = [
					'status' => $status,
					'body'   => $body
				];

				// Activate WP_DEBUG
				$this->utility->do_log("Response");
				$this->utility->do_log($response);

				return $response;
				
			} catch ( RequestException $e ) {

				$error = [];
				
				if( is_object( $e ) && method_exists( $e, 'hasResponse' ) && $e->hasResponse() ) {
					$error = [
						'status' => method_exists( $e, 'getResponse' ) ? $e->getResponse()->getStatusCode() : 503,
						'body'   => null,
					];

					try {
						$responseBody = method_exists( $e, 'getResponse' ) ? $e->getResponse()->getBody(true)->getContents() : null;
						if( isset( $responseBody ) ) {
							$error['body'] = json_decode( $responseBody, true );
						}
					} catch ( \Exception $jsonError ) {
						$error['body'] = $jsonError->getMessage();
					}
				}

				// Remove logs
				$this->utility->do_log("Request Error");
				$this->utility->do_log($error);

				return $error;

			} catch ( \Exception $e ) {

				$error = [
					'status' => is_object( $e ) && method_exists( $e, 'getCode' ) ? $e->getCode() : 503,
					'body'   => array(
						'message' => is_object( $e ) && method_exists( $e, 'getMessage' ) ? $e->getMessage() : null
					)
				];

				$this->utility->do_log("Exception");
				$this->utility->do_log($error);

				return $error;
				
			}
		}

		/**
		 * Send GET Request.
		 * 
		 * Send a GET request to an endpoint.
		 * 
		 * @since 1.0.0
		 * 
		 * @param string $endpoint Endpoint for GET request.
		 */
		public function send_get_request( $endpoint ) {
			$http = $this->client;

			$this->utility->do_log("GET Endpoint");
			$this->utility->do_log($endpoint);

			try {

				$res = $http->request( 'GET', $endpoint, [
					'timeout' => 30,
				] );

				if( ! isset( $res ) || empty( $res ) || ! is_object( $res ) ) {
					return false;
				}

				if( ! method_exists( $res, 'getStatusCode' ) || ! method_exists( $res, 'getBody' ) ) {
					return false;
				}

				$response = [
					'status' => $res->getStatusCode(),
					'body'   => $res->getBody()->__toString()
				];

				$this->utility->do_log("GET Response");
				$this->utility->do_log($response);

				return $response;

			} catch ( \Exception $e ) {

				$error = [
					'status' => is_object( $e ) && method_exists( $e, 'getCode' ) ? $e->getCode() : 503,
					'body'   => array(
						'message' => is_object( $e ) && method_exists( $e, 'getMessage' ) ? $e->getMessage() : __( 'There is an error, please report error code to support team.', 'clevernode-related-content' )
					)
				];

				$this->utility->do_log("Exception");
				$this->utility->do_log($error);

				return $error;
				
			}
		}
	}

endif;