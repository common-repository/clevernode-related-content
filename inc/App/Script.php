<?php
/**
 * Plugin Script Tag.
 * 
 * Handles plugin script integration to display related posts.
 *
 * @package clevernode-related-content
 * @since   1.0.0
 */
namespace CleverNodeRCFree\App;

/**
 * Don't load directly.
 */
if( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Use required classes.
 */
use CleverNodeRCFree\Classes\Settings_API;
use CleverNodeRCFree\Classes\Settings_API_Fields;
use CleverNodeRCFree\Utils\Utility;

/**
 * Script Tag.
 * 
 * Insert script tag to connect semantical reader.
 *
 * @since 1.0.0
 */
if ( !class_exists( 'Script' ) ) :

	class Script {

		private $settings_api, $settings_fields, $account, $channel, $utils, $display;

		public function __construct() {
			$this->settings_api = new Settings_API;
			$this->settings_fields = new Settings_API_Fields;
			$this->utils = new Utility;

			$this->display = $this->settings_fields->get_option( 'display_opt', 'clevernode_plugin_settings' );
			$this->account = $this->settings_fields->get_option( 'account', 'clevernode_account_data' );
			$this->channel = 1;

			/**
			 * Script Tag after post content
			 */
			add_filter(
				'the_content', 
				array( $this, 'add_related_script' ),
				99
			);

			/**
			 * Script Tag shortcode
			 */
			add_action( 'init', array($this, 'register_shortcode') );
		}

		/**
		 * Register clevernode shortcode
		 */
		public function register_shortcode() {
			add_shortcode(
				'clevernode-related',
				array( $this, 'do_related_shotcode' )
			);
		}

		/**
		 * Filter the_content to add script
		 */
		public function add_related_script( $content ){
			if( $this->utils->check_filter($this->display) === false ) {
				return $content . '<!-- CleverNode Shortcode Active -->';
			}
			
			if ( in_the_loop() && is_single() && is_main_query() ) {
				
				if( $this->utils->is_amp_page() ) {
                    return $content . $this->amp_component($this->account, $this->channel);
				} else {
					return $content . $this->print_script($this->account, $this->channel);
				}

			}

			return $content;
		}

		/**
		 * Shortcode
		 */
		public function do_related_shotcode( $atts ) {
			if( $this->utils->check_code($this->display) === false ) {
				return;
			}

			$attr = shortcode_atts( array(
				'account' => $this->account,
				'channel' => $this->channel,
			), $atts );
			
			if( $this->utils->is_amp_page() === true ) {
				return $this->amp_component( $attr['account'], $attr['channel'] );
			} else {
				return $this->print_script( $attr["account"], $attr["channel"] );
			}
		}

		/**
		 * Print Script
		 */
		private function print_script($account, $channel){
			$id = $this->utils->build_script_id($account, $channel);
			$script = sprintf(
				'<script id="%1$s" async="async" src="//epeex.com/related/service/widget/clevernode/?ac=%2$s&ch=%3$s&is=%1$s&sc=cl%4$s" language="javascript" data-jetpack-boost="ignore" data-cfasync="false" data-no-optimize="1" data-wpmeteor-nooptimize="true"></script>', 
				$id,
				esc_attr( $account ),
				esc_attr( $channel ),
				time() + wp_rand( 113241, 999999 )
			);
			return $script;
		}

		/**
		 * AMP Embed Component
		 */
		public function amp_component($account, $channel) {
			return '<amp-embed
				type="epeex"
				data-account="'.$account.'"
				data-channel="'.$channel.'"
				width="100"
				height="100"
				heights="(max-width:320px) 650%, (max-width:480px) 550%, (max-width:640px) 100%, 100%">
			</amp-embed>';
		}

	}

endif;