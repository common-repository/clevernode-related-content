<?php
/**
 * Setup Plugin.
 * 
 * Registers plugin scripts and instantiate plugin classes.
 * 
 * @package clevernode-related-content
 * @since   1.0.0
 */
namespace CleverNodeRCFree;

/**
 * Don't load directly.
 */
if( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Use required classes.
 */
use CleverNodeRCFree\Utils\Utility;
use CleverNodeRCFree\Classes\Admin_Notices;
use CleverNodeRCFree\Classes\Http_Client;
use CleverNodeRCFree\Modules\Settings;
use CleverNodeRCFree\Modules\Connection;
use CleverNodeRCFree\App\MetaTags;
use CleverNodeRCFree\App\Script;

/**
 * Plugin Setup Class.
 * 
 * Init plugin settings, instatiate primary plugin classes,
 * enqueue scripts and add notice in admin.
 *
 * @since 1.0.0
 * 
 * @var string $path    Plugin dir path.
 * @var string $root    Plugin dir URL.
 * @var string $version Plugin version.
 */
if ( ! class_exists( 'Plugin_Setup' ) ) :

	class Plugin_Setup {

		private $path, $root, $version, $basefile, $basedir;
		protected $utility, $verified, $active, $http;

		public function __construct( $path, $root, $version, $basefile, $basedir ){
			$this->path     = $path;
			$this->root     = $root;
			$this->version  = $version;
			$this->basefile = $basefile;
			$this->basedir  = $basedir;

			/**
			 * Utility to get account activation/verification
			 */
			$this->utility  = new Utility;
			$this->http     = new Http_Client;
			$this->active   = $this->utility->get_account_activated();
			$this->verified = $this->utility->get_account_verified();

			/**
			 * Instantiate plugin settings and modules.
			 */
			$admin   = new Settings($this->root);
			$connect = new Connection();
			$notices = new Admin_Notices();

			/**
			 * Load plugin structure.
			 */
			if ( ! wp_installing() ) {
				add_action( 'plugins_loaded', array( $this, 'load_plugin_structure' ) );
			}

			/**
			 * Check plugin activation dependencies.
			 */
			add_action( 'activate_plugin', array( $this, 'check_deps' ), 10, 2 );

			/**
			 * Add plugin row meta.
			 */
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 4 );

			/**
			 * Register plugin scripts and styles for admin.
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

			/**
			 * Add plugin activation notice.
			 */
			add_action( 'admin_notices', array( $this, 'activation_notice' ) );
			add_action( 'admin_notices', array( $this, 'review_notice' ) );

			/**
			 * Register notice transients.
			 */
			add_action( 'admin_init', array( $this, 'set_review_transient' ) );

			/**
			 * Dismiss plugin connection notice.
			 */
			add_action( 'admin_init', array( $this, 'dismiss_connection_notice' ) );

			/**
			 * Dismiss plugin review notice.
			 */
			add_action( 'admin_init', array( $this, 'dismiss_review_notice' ) );

			/**
			 * Register plugin review ajax action.
			 */
			add_action( 'wp_ajax_clevernode_review_later', array($this, 'review_later') );
		}

		/**
		 * Activation Hook.
		 *
		 * Set a transient for plugin activation admin notice.
		 *
		 * @since 1.0.0
		 * 
		 * @see clevernode-related-content.php
		 */
		public function activation_hook() {

			// Set activation + review notice transient
			set_transient( 'clevernode-admin-notice-activation', 'active', 60 * 60 * 24 * 30 );
			set_transient( 'clevernode-review-notice', 'active' );

		}

		/**
		 * Deactivation Hook.
		 *
		 * Delete plugin activation admin notice transient.
		 *
		 * @since 1.0.0
		 * 
		 * @see clevernode-related-content.php
		 */
		public function deactivation_hook() {
			// Deactivate account
			$this->deactivate_account();
		}

		/**
		 * Deactivate account.
		 * 
		 * Deactivate remote account a reset settings.
		 * 
		 * @since 1.0.0
		 * 
		 * @see deactivation_hook
		 */
		public function deactivate_account() {
			$account_data = get_option( 'clevernode_account_data' );
			$endpoint = $this->utility->base_endpoint . '/api/v1/account/disconnect';

			$data = array(
				'account' => isset( $account_data['account'] ) ? $account_data['account'] : null,
				'token'   => isset( $account_data['wp_token'] ) ? $account_data['wp_token'] : null,
			);

			if( null !== $data['account'] && null !== $data['token'] ) {

				// Call for account deactivation
				$response = $this->http->send_post_request( $endpoint, $data );
	
				// Remove account data
				delete_option( 'clevernode_account_data' );
				delete_transient( 'clevernode-review-notice' );
				delete_transient( 'clevernode-review-notice-check' );
				delete_transient( 'clevernode-review-notice-flag' );
			}
		}

		/**
		 * Check plugin activation.
		 * 
		 * Redirect to plugins and prevent activation 
		 * if premium version is active.
		 * 
		 * @param string $plugin  Path to the plugin file
		 * @param bool   $network Network wide on activation
		 * 
		 * @since 1.0.2
		 */
		public function check_deps( $plugin, $network ) {
			if( $plugin !== $this->basefile ) {
				return;
			}

			// Deactivate clevernode free if premium active
			if( is_plugin_active('clevernode-premium/clevernode-premium.php') ) {
				$redirect = self_admin_url( 'plugins.php?clevernode_free_notice=1' );
				wp_redirect( $redirect );
				exit;
			}
		}

		/**
		 * Filters the array of row meta for 
		 * each/specific plugin in the Plugins list table.
		 * Appends additional links below each/specific
		 * plugin on the plugins page.
		 * 
		 * @param   array  $links_array An array of the plugin's metadata
		 * @param   string $plugin_file_name Path to the plugin file
		 * @param   array  $plugin_data      An array of plugin data
		 * @param   string $status           Status of the plugin
		 * @return  array  $links_array      Array of links
		 * 
		 * @since 1.0.2
		 */
		public function add_plugin_row_meta( $links_array, $plugin_file_name, $plugin_data, $status ) {
			if ( strpos( $plugin_file_name, $this->basefile ) !== false ) {
				$links_array[] = __( 'AMP Compatible', 'clevernode-related-content' );
			}
		 
			return $links_array;
		}

		/**
		 * Register plugin scripts and styles for admin.
		 *
		 * Registers assets only for admin settings page.
		 *
		 * @since 1.0.0
		 * 
		 * @see admin_enqueue_scripts
		 */
		public function admin_assets( $hook ) {
			if( $hook === 'toplevel_page_clevernode-settings' ) {

				wp_enqueue_style( 'clevernode-related-content-style', $this->root . 'public/css/style.min.css', array(), filemtime( $this->path . 'public/css/style.min.css' ) );
				wp_enqueue_script( 'jquery-cookie', $this->root . 'public/js/jquery.cookie.js', array('jquery'), null, true );
				wp_enqueue_script( 'clevernode-related-content-script', $this->root . 'public/js/scripts.min.js', array('jquery', 'jquery-cookie'), filemtime( $this->path . 'public/js/scripts.min.js' ), true );

			}

			// Add ajax data
			wp_localize_script( 'jquery-core', 'cleverNodeData', array(
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'clevernode-ajax-nonce' )
			) );
		}

		/**
		 * Plugin activation admin notice.
		 *
		 * Shows a success admin notice on plugin activation, with a link to
		 * plugin's settings page.
		 *
		 * @since 1.0.0
		 *
		 * @see admin_notices
		 * @return string HTML for admin notice.
		 */
		public function activation_notice(){
			if( false !== get_transient( 'clevernode-admin-notice-activation' ) ){
				$html  = '<div class="notice-warning notice clevernode-notice">';
				$html .= '<p>';
				$html .= sprintf( '%1$s activated. You have to go to <strong><a href="%2$s">plugin settings page</a></strong> to setup connection with CleverNode service. <a class="button" href="%3$s">Dismiss</a>', '<strong>' . __( 'CleverNode Related Content', 'clevernode-related-content' ) . '</strong>', esc_url( admin_url( 'admin.php?page=clevernode-settings#clevernode_connect' ) ), wp_nonce_url( add_query_arg( 'clevernode-ignore-connection-notice', 'clv_notice' ), 'clv_notice', '_clv_notice_nonce' ) );
				$html .= '</p>';
				$html .= '</div>';
				print( $html );
			}
		}

		/**
		 * Review admin notice.
		 * 
		 * @uses admin_init
		 * 
		 * @since 1.1.5
		 */
		public function review_notice() {

			$opts = get_option( 'clevernode_account_data' );

			if( false !== get_transient( 'clevernode-review-notice' ) && isset( $opts['account_created'] ) && ! empty( $opts['account_created'] ) && time() > strtotime($opts['account_created']) + (10 * DAY_IN_SECONDS) || false !== get_transient( 'clevernode-review-notice-flag' ) && isset( $opts['ask_review_flag'] ) && ! empty( $opts['ask_review_flag'] ) && time() > $opts['ask_review_flag'] + (10 * DAY_IN_SECONDS) ) {

				$html = "<script>!(function($) {
					$(function() {
						if( typeof cleverNodeData === 'undefined' ) return;

						$('.clevernode-dismiss-review-notice').on('click', function(i) {
							i.preventDefault(), $('.clevernode-review-notice').find('.notice-dismiss').trigger('click');

							var cl_review_data = {
								action: 'clevernode_review_later',
								security: cleverNodeData.security
							};
							$.post(cleverNodeData.ajaxurl, cl_review_data, function(response) {
								console.log(response);
							});
						});
					});
				})(jQuery);</script>";

				$html  .= sprintf( '<div class="notice clevernode-notice clevernode-review-notice is-dismissible" style="border-left-color: #1260aa; background: #fff url(%1$s) no-repeat top left; background-size: 50px; padding-left: 60px;">',
				$this->root . 'public/img/icon-clevernode.jpg' );
				$html .= '<p>';
				/* translators: placeholder is the plugin name. */
				$html .= sprintf( esc_html__( 'Hey, we noticed that you have been using %s for 10 days, thank you!', 'clevernode-related-content' ), '<strong>' . _x( 'CleverNode Related Content', 'Plugin name in review notice', 'clevernode-related-content' ) . '</strong>' );
				$html .= '<br />';
				$html .= esc_html__( 'Would you please leave your review so our plugin is well-known?', 'clevernode-related-content' );
				$html .= '<br />';
				$html .= sprintf( '<span style="display: block; margin-top: 5px; font-weight: bold;"><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', 'https://wordpress.org/support/plugin/clevernode-related-content/reviews/?rate=5#new-post', esc_html__( 'Yes, you deserve it', 'clevernode-related-content' ) );
				$html .= sprintf( '<span style="display: block; font-weight: bold;"><a href="#" class="clevernode-dismiss-review-notice">%s</a></span>', esc_html__( 'No, maybe later', 'clevernode-related-content' ) );
				$html .= sprintf( '<span style="display: block; font-weight: bold;"><a href="%1$s">%2$s</a></span>', wp_nonce_url( add_query_arg( 'clevernode-ignore-review-notice', 'clv_notice' ), 'clv_notice', '_clv_notice_nonce' ), esc_html__( 'I\'ve already done', 'clevernode-related-content' ) );
				$html .= '</p>';
				$html .= '</div>';
				print( $html );

			}

		}

		/**
		 * Dismiss connection notice.
		 * 
		 * @uses admin_init
		 * 
		 * @since 1.0.9
		 */
		public function dismiss_connection_notice() {
			if( isset( $_GET['clevernode-ignore-connection-notice'], $_GET['_clv_notice_nonce'] ) ) {
				$notice = sanitize_key( $_GET['clevernode-ignore-connection-notice'] );
				if( check_admin_referer( $notice, '_clv_notice_nonce' ) ) {
					delete_transient( 'clevernode-admin-notice-activation' );
					wp_safe_redirect( remove_query_arg( array( 'clevernode-ignore-conection-notice' ), wp_get_referer() ) );
					exit;
				}
			}
		}

		/**
		 * Dismiss review notice.
		 * 
		 * @uses admin_init
		 * 
		 * @since 1.0.9
		 */
		public function dismiss_review_notice() {
			if( isset( $_GET['clevernode-ignore-review-notice'], $_GET['_clv_notice_nonce'] ) ) {
				$notice = sanitize_key( $_GET['clevernode-ignore-review-notice'] );
				if( check_admin_referer( $notice, '_clv_notice_nonce' ) ) {
					delete_transient( 'clevernode-review-notice' );
					delete_transient( 'clevernode-review-notice-flag' );

					$opts = get_option( 'clevernode_account_data' );
					unset( $opts['ask_review_flag'] );
					update_option( 'clevernode_account_data', $opts );

					wp_safe_redirect( remove_query_arg( array( 'clevernode-ignore-review-notice' ), wp_get_referer() ) );
					exit;
				}
			}
		}

		/**
		 * Review Later ajax action.
		 * 
		 * @see review_notice
		 * 
		 * @since 1.1.5
		 */
		public function review_later() {
			check_ajax_referer( 'clevernode-ajax-nonce', 'security' );
			$opts = get_option( 'clevernode_account_data' );

			$opts['ask_review_flag'] = time();
			set_transient( 'clevernode-review-notice-flag', 'active' );
			set_transient( 'clevernode-review-notice-check', 'active' );
			delete_transient( 'clevernode-review-notice' );

			update_option( 'clevernode_account_data', $opts );
			$up_opts = get_option( 'clevernode_account_data' );

			wp_send_json_success( array(
				'flag' => isset( $up_opts['ask_review_flag'] ) ? $up_opts['ask_review_flag'] : false,
				'transient_flag' => get_transient( 'clevernode-review-notice-flag' ),
				'transient_notice' => get_transient( 'clevernode-review-notice' )
			), 200 );
		}

		/**
		 * Enable plugin functionality.
		 * 
		 * Enable plugin meta tags and script on front-end.
		 * 
		 * @see App/MetaTags.php
		 * @see App/Script.php
		 * 
		 * @since 1.0.0
		 */
		public function load_plugin_structure() {
			if( $this->active && $this->verified ) {
				$meta_tags = new MetaTags($this->version);
				$cn_script = new Script();
			}
		}


		/**
		 * Register review notice transient.
		 * 
		 * @uses admin_init
		 * 
		 * @since 1.1.5
		 */
		public function set_review_transient() {
			if( false === get_transient( 'clevernode-review-notice' ) && false === get_transient( 'clevernode-review-notice-check' ) ) {
				set_transient( 'clevernode-review-notice', 'active' );
				set_transient( 'clevernode-review-notice-check', 'active' );
			}
		}

	}

endif;