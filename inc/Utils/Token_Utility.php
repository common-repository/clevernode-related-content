<?php
/**
 * Token Utility.
 * 
 * Handles methods to set and check token, using ReallySimpleJWT.
 *
 * @package clevernode-related-content
 * @since   1.0.0
 */
namespace CleverNodeRCFree\Utils;

/**
 * Don't load directly.
 */
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Use required classes.
 */
use \ReallySimpleJWT\Token;

/**
 * Token Utility.
 * 
 * Handles token utility methods.
 *
 * @since 1.0.0
 */
if ( !class_exists( 'Token_Utility' ) ) :

	class Token_Utility {

		/**
		 * Create Token.
		 * 
		 * Handles token creation with a custom payload.
		 *
		 * @since 1.0.0
		 */
		public function create_token() {
			$payload = [
				'iat' => time(),
				'uid' => get_current_user_id(),
				'exp' => time() + 120,
				'iss' => get_site_url()
			];
			return Token::customPayload($payload, $this->set_salt());
		}

		/**
		 * Validate Token.
		 * 
		 * Handles token validation, check string and expiration.
		 *
		 * @since 1.0.0
		 * 
		 * @param string $token Token to validate.
		 */
		public function validate_token( $token ) {
			if( $this->validate_token_str( $token ) === true && 
				$this->validate_token_exp( $token ) === true ) {
				return true;
			}

			return false;
		}

		/**
		 * Validate Token Structure and Secret.
		 * 
		 * Handles token structure and secret validation.
		 *
		 * @since 1.0.0
		 * 
		 * @param string $token Token to validate.
		 */
		public function validate_token_str($token) {
			try {
				$check = Token::validate( $token, $this->set_salt() );
			} catch(\Exception $e) {
				return false;
			}

			return $check;
		}

		/**
		 * Validate Token Expiration.
		 * 
		 * Handles token expiration validation.
		 *
		 * @since 1.0.0
		 * 
		 * @param string $token Token to validate.
		 */
		public function validate_token_exp($token) {
			try {
				$check = Token::validateExpiration( $token, $this->set_salt() );
			} catch(\Exception $e) {
				return false;
			}

			return $check;
		}

		/**
		 * Validate User Subscription Key.
		 * 
		 * Handles user key validation.
		 *
		 * @since 1.0.0
		 * 
		 * @param string $key Key to validate.
		 */
		public function validate_subscription_key( $key ) {
			$check_key = $this->set_user_key();

			if ( $key === $check_key ) {
				return true;
			}

			return false;
		}

		/**
		 * Validate User Verification Key.
		 * 
		 * Handles user verification key validation.
		 *
		 * @since 1.0.0
		 * 
		 * @param string $key Key to validate.
		 */
		public function validate_verification_key( $key ) {
			$check_key = sha1( $this->set_user_key() );

			if ( $key === $check_key ) {
				return true;
			}

			return false;
		}


		/**
		 * Set User Data Key.
		 * 
		 * Set user key for validation.
		 *
		 * @since 1.0.0
		 */
		public function set_user_key() {
			return sha1( wp_salt('secure_auth') . get_current_user_id() );
		}


		/**
		 * Set Unique Key.
		 * 
		 * Set a sal key with WP SALT.
		 * 
		 * @since 1.0.0
		 */
		protected function set_salt(){
			if ( function_exists( 'wp_salt' ) ) {
				return wp_salt( 'secure_auth' );
			}
		}

	}

endif;