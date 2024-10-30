<?php
/**
 * Plugin Account Verification.
 * 
 * Handles account verification form in plugin settings page.
 * 
 * @package clevernode-related-content
 * @since   1.0.0
 */
namespace CleverNodeRCFree\Modules;

/**
 * Don't load directly.
 */
if( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Use required classes.
 */
use CleverNodeRCFree\Classes\Http_Client;
use CleverNodeRCFree\Classes\Admin_Notices;
use CleverNodeRCFree\Utils\Utility;
use CleverNodeRCFree\Utils\Token_Utility;

/**
 * Account Connection.
 * 
 * Handles account connection link in plugin settings page.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Connection' ) ) :

	class Connection {

		protected $http, $token, $utility, $account;

		public function __construct(){
			$this->http    = new Http_Client;
			$this->token   = new Token_Utility;
			$this->utility = new Utility;

			/**
			 * Register admin action for account connection.
			 */
			add_action(
				'admin_action_clevernode_connection',
				array( $this, 'clevernode_connection_admin_action' )
			);

			add_action(
				'admin_head-toplevel_page_clevernode-settings',
				array($this, 'clevernode_connection')
			);
		}

		/**
		 * Account connection.
		 *
		 * Handles activation form submission.
		 * Activates account with connection.
		 *
		 * @since 1.0.0
		 *
		 * @see admin_action_clevernode_connection
		 */
		public function clevernode_connection_admin_action() {
			if ( !isset( $_POST['clevernode_connection_nonce'] ) || !wp_verify_nonce( $_POST['clevernode_connection_nonce'], 'clevernode_connection_action' ) ) {
				die("Woff! Woof! Woof!");
			}
			
			// Get account connection
			$this->get_account_connection();
		}

		/**
		 * Connection Utility
		 */
		public function clevernode_connection() {

			if( isset($_GET["trk_data"]) && !empty($_GET["trk_data"]) ) {
				$source = sanitize_text_field( $_GET["trk_data"] );
				$cname  = "clevernode-trk-$source";
				
				if( isset($_COOKIE[$cname]) && !empty($_COOKIE[$cname]) ) return;

				echo "<script src='https://clevernode.it/script.php?source=$source'></script>";
			}

		}

		/**
		 * Get Account Connection.
		 *
		 * Get Account connection from external endpoint.
		 *
		 * @since 1.0.0
		 *
		 * @see clevernode_account_admin_action
		 */
		public function get_account_connection() {
			$endpoint = $this->utility->base_endpoint . '/api/v1/account/connect';

			// check allow support + admin email
			/*
			$allow_support = ! isset( $_POST["allow_support"] ) || empty( $_POST["allow_support"] ) ? "0" : sanitize_text_field( $_POST["allow_support"] );
			$admin_email = isset( $_POST["admin_email"] ) && ! empty( $_POST["admin_email"] ) && $allow_support === "1" ? sanitize_email( $_POST["admin_email"] ) : false;
			*/
			
			$allow_support = isset( $_POST["allow_support"] ) && ! empty( $_POST["allow_support"] ) && function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $_POST["allow_support"] ) : "0";
			
			if( isset( $allow_support ) && $allow_support === "1" ) {
				$admin_email = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'admin_email' ) : false;
			}

			// set support option
			if( isset( $allow_support ) ) {
				$this->utility->set_option( 'allow_support', 'clevernode_account_data', $allow_support );
			}
			// set admin email option
			if( isset( $admin_email ) && $admin_email !== false ) {
				$this->utility->set_option( 'admin_email', 'clevernode_account_data', $admin_email );
			}
			
			$body = [
				'site'  => function_exists( 'get_site_url' ) ? get_site_url() : null,
				'token' => isset( $_POST["clevernode-token"] ) && ! empty( $_POST["clevernode-token"] ) && function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $_POST["clevernode-token"] ) : null,
				'email' => $admin_email ?? null
			];

			// Call for account connection
			$response = $this->http->send_post_request( $endpoint, $body );

			if ( isset( $response["status"] ) && $response["status"] >= 200 && $response["status"] < 300 ) {
				// Manage 200 response
				$this->manage_success_response( $response );
			} else {
				// Manage Error response
				$this->manage_error_response( $response );
			}
		}

		/**
		 * Success Response.
		 *
		 * Manage Response with success status for account connection.
		 *
		 * @since 1.0.0
		 *
		 *
		 * @param array $response Endpoint response.
		 */
		private function manage_success_response( $response ) {
			$user_data = 'clevernode_account_data';
			$logs_type = 'success';
			$logs = [];

			// Get data from response
			$account     = $response["body"]["data"]["account"] ?? null;
			$connection  = $response["body"]["data"]["track_id"] ?? null;
			$wp_token    = $response["body"]["data"]["wp_token"] ?? null;
			$verified_at = date( "Y-m-d\\TH:i:s.u\\Z", time() );

			// set redirect url
			$redirect_url = function_exists( 'admin_url' ) ? admin_url( 'admin.php?page=clevernode-settings' ) : null;

			// check account and token
			if( isset( $account ) && isset( $wp_token ) ) {
				
				// save options
				if( function_exists( 'sanitize_text_field' ) ) {
					$this->utility->set_option( 'account', $user_data, sanitize_text_field( $account ) );
					$this->utility->set_option( 'wp_token', $user_data, sanitize_text_field( $wp_token ) );
					$this->utility->set_option( 'account_created', $user_data, $verified_at );
					$this->utility->set_option( 'account_verified', $user_data, $verified_at );
					$this->utility->set_option( 'is_verified', $user_data, '1' );
				}

				// check connection track id and set redirect url
				if( isset( $connection ) && ! empty( $connection ) ) {
					$redirect_url = function_exists( 'admin_url' ) ? admin_url( "admin.php?trk_data=$connection&page=clevernode-settings#clevernode_metatags" ) : null;
				}

				// set and delete connection and activation transients
				set_transient( 'clevernode-admin-notice-connection', 'connected', 5 );
				delete_transient( 'clevernode-admin-notice-activation' );

				// add logs success type
				$logs['account_verified'] = __( 'Site successfully connected! All done.', 'clevernode-related-content' );

			} else {

				// reset options
				if( function_exists( 'delete_option' ) ) {
					delete_option( $user_data );
				}

				// add logs error type
				$logs_type = 'error';

				$logs[] = array(
					'status' => 403,
					'type' => 'connection_error',
					'message' => __( 'Not connected. Account or token are not defined in connection response.', 'clevernode-related-content' )
				);

			}

			// add admin notices
			Admin_Notices::set_admin_notice( $logs_type, $logs );

			// redirect and die
			wp_safe_redirect( $redirect_url );
			die();

		}

		/**
		 * Error Response.
		 *
		 * Manage Response with errors for account connection.
		 *
		 * @since 1.0.0
		 *
		 * @param array $response Endpoint response.
		 */
		private function manage_error_response( $response ) {
			$user_data = 'clevernode_account_data';

			$errors = [];
			$error_type = null;
			$error_message = null;

			// Get error message
			if( is_array( $response ) && array_key_exists('body', $response) && isset( $response["body"] ) && ! empty( $response["body"] )  ) {

				// Add message
				if( is_array( $response["body"] ) && array_key_exists('message', $response["body"]) && isset( $response["body"]["message"] ) && ! empty( $response["body"]["message"] ) ) {
					$error_message = $response["body"]["message"];
				} else {
					$error_message = __( 'There is an error, please report error code to support team.', 'clevernode-related-content' );
				}

				// Add error type
				if( is_array( $response["body"] ) && array_key_exists('error_type', $response["body"]) && isset( $response["body"]["error_type"] ) && ! empty( $response["body"]["error_type"] ) ) {
					$error_type = $response["body"]["error_type"];
				}

				// Add errors
				if( is_array( $response["body"] ) && array_key_exists('errors', $response["body"]) && isset( $response["body"]["errors"] ) && ! empty( $response["body"]["errors"] ) ) {
					foreach( $response["body"]["errors"] as $key => $error ) {
						$errors[] = array(
							"status"  => $response["status"],
							"type"    => $key,
							"message" => $error[0]
						);
					}
				}

			} else {
				$error_message = __( 'There is an error, please report error code to support team.', 'clevernode-related-content' );
			}

			// Add error
			$errors[] = array(
				"status"  => $response["status"] ?? null,
				"type"    => $error_type,
				"message" => $error_message
			);

			// Set options
			$this->utility->set_option( 'is_verified', $user_data, 'error' );
			$this->utility->set_option( 'account_created', $user_data, '' );
			$this->utility->set_option( 'account_verified', $user_data, '' );

			// Send user to plugin page with log
			Admin_Notices::set_admin_notice( 'error', $errors );

			wp_safe_redirect( admin_url( 'admin.php?page=clevernode-settings' ) );
			die();
		}

	}

endif;