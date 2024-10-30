<?php
/**
 * Plugin Utility.
 * 
 * Handles methods to set and check plugin options and keys.
 *
 * @package clevernode-related-content
 * @since   1.0.0
 */
namespace CleverNodeRCFree\Utils;

/**
 * Don't load directly.
 */
if( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Plugin Utility.
 * 
 * Handles plugin utility methods.
 *
 * @since 1.0.0
 */
if ( !class_exists( 'Utility' ) ) :

	class Utility {

		public $base_endpoint;

		/**
		 * Init Class.
		 */
		public function __construct() {
			$this->base_endpoint = 'https://clevernode.it';
		}

		/**
		 * Build Script ID.
		 *
		 * Create script tag ID with account an channel data.
		 *
		 * @since 1.0.0
		 * 
		 * @param string $account Required Account name.
		 * @param string $channel Channel ID.
		 */
		public function build_script_id( $account, $channel = '1' ) {
			$data = array(
				$account,
				$channel,
				time() + rand()
			);

			$id = md5( implode( '', $data ) );

			return $id;
		}

		/**
		 * Check Filter Option Enabled.
		 * 
		 * Check if content filter is enabled by plugin settings.
		 *
		 * @since 1.0.0
		 * 
		 * @param string $opt Option name.
		 */
		public function check_filter( $opt = '' ) {
			if( empty( $opt ) || $opt === 'on' ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check Shortcode Option Enabled.
		 * 
		 * Check if shortcode is enabled by plugin settings.
		 * 
		 * @since 1.0.0
		 * 
		 * @param string $opt Option name.
		 */
		public function check_code( $opt = '' ) {
			if( ! empty( $opt ) && $opt === 'off' ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check AMP.
		 * 
		 * Check if current page is AMP with plugins methods.
		 * 
		 * Plugins support:
		 * AMP for WP - https://wordpress.org/plugins/accelerated-mobile-pages/
		 * AMP WP - https://wordpress.org/plugins/amp/
		 * 
		 * @since 1.0.2
		 * 
		 * @return bool AMP page or not.
		 */
		public function is_amp_page() {
			if ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() ) {
				return true;
			} elseif( function_exists('amp_is_request') && amp_is_request() ) {
				return true;
			}

			return false;
		}

		/**
		 * Render Page Template.
		 * 
		 * Get php page template and return HTML.
		 * 
		 * @since 1.0.0
		 * 
		 * @param string $template_name Template name.
		 * @param array  $attr          Data for template.
		 */
		public function get_template_html( $template_name, $attr = null ) {
			if ( ! $attr ) $attr = array();

			ob_start();

			do_action( 'clevernode_before_' . $template_name );
			require( plugin_dir_path( __DIR__ ) . 'templates/' . $template_name . '.php' );
			do_action( 'clevernode_after_' . $template_name );

			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		}

		/**
		 * Check Account Active.
		 * 
		 * Get if Account is activated.
		 * 
		 * @since 1.0.0
		 */
		public function get_account_activated() {
			$data = get_option( 'clevernode_account_data' );

			if( isset( $data["account_created"] ) && ! empty( $data["account_created"] ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check Account Verified.
		 * 
		 * Get if Account if verified.
		 * 
		 * @since 1.0.0
		 */
		public function get_account_verified() {
			$data = get_option( 'clevernode_account_data' );

			if( isset( $data["account_verified"] ) && ! empty( $data["account_verified"] ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Set option.
		 * 
		 * Set the value of a settings field.
		 * 
		 * @since 1.0.0
		 *
		 * @param string  $option  Settings field name
		 * @param string  $section Section name this field belongs to
		 * @param string  $value   Field value
		 */
		public function set_option( $option, $section, $value = '', $backup = '' ) {
			$options = get_option( $section );

			if( !is_array( $options ) || empty( $options ) ) {
				$options = array();
			}

			if( isset( $options[$option] ) ) {
				$backup = $options[$option];
			}

			if ( isset( $value ) && $backup !== $value ) {
				$options[$option] = $value;
				update_option( $section, $options );
			}

			return;
		}

		/**
		 * Get Cutom Post Types.
		 * 
		 * Get registered post types, exclude builtin.
		 * 
		 * @since 1.0.2
		 * 
		 * @return array $types Slug and name of post type.
		 */
		public function get_available_post_types() {
			$types = array(
				'post' => 'Posts'
			);

			$args = array(
				'public'   => true,
				'_builtin' => false
			);

			$post_types = get_post_types( $args, 'objects' );

			foreach( $post_types as $obj ) {
				$types[$obj->name] = $obj->label;
			}

			return $types;
		}

		/**
		 * Do log.
		 * 
		 * Prints a message to the debug file that can easily be called by any subclass.
		 *
		 * @param mixed $message   an object, array, string, number, or other data to write to the debug log
		 * @param bool  $shouldDie whether or not the The function should exit after writing to the log
		 * 
		 * @see https://tommcfarlin.com/the-wordpress-debug-log/
		 */
		public function do_log( $message, $shouldDie = false ) {
			if ( WP_DEBUG === true && WP_DEBUG_LOG === true ) {
				error_log( print_r( $message, true ) );

				if ( $shouldDie ){
					exit;
				}
			}
		}

	}

endif;
