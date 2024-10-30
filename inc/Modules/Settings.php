<?php
/**
 * Plugin admin settings.
 * 
 * Registers plugin settings page and settings sections and fields.
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
use CleverNodeRCFree\Utils\Utility;
use CleverNodeRCFree\Classes\Settings_API;
use CleverNodeRCFree\Classes\Settings_API_Fields;
use CleverNodeRCFree\Classes\Http_Client;

/**
 * Plugin Settings Class.
 * 
 * Registers plugin settings.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Settings' ) ) :

	class Settings {

		private $settings_api, $settings_fields, $plugin_url;
		protected $utility, $http, $verified, $active, $has_og;

		public function __construct($root) {
			$this->settings_api    = new Settings_API;
			$this->settings_fields = new Settings_API_Fields;
			$this->utility         = new Utility;
			$this->http            = new Http_Client;

			$this->plugin_url = $root;
			$this->active     = $this->utility->get_account_activated();
			$this->verified   = $this->utility->get_account_verified();
			$this->has_og     = $this->settings_fields->get_option( 'has_og', 'clevernode_plugin_settings' );

			/**
			 * Add plugin settings page to menu.
			 */
			add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
			
			/**
			 * Init plugin settings.
			 */
			add_action( 'admin_init', array( $this, 'init_settings' ) );

			/**
			 * Save site IP.
			 */
			add_action( 'admin_init', array( $this, 'get_site_ip' ), 1 );

			/**
			 * Add plugin settings link.
			 */
			add_filter( 'plugin_action_links_clevernode-related-content/clevernode-related-content.php', array($this, 'plugin_settings_link') );

			/**
			 * Add Feedback Modal Window.
			 */
			add_action( 'admin_footer', array($this, 'plugin_info_modal') );

			/**
			 * Set transient to show modal on settings save.
			 */
			add_filter( 'pre_update_option_clevernode_plugin_settings', array($this, 'plugin_settings_update_notice'), 10, 3 );
		}

		/**
		 * Add Admin CSS.
		 *
		 * Description.
		 *
		 * @since 1.0.0
		 *
		 * @return string Style to hide submit button.
		 * @todo   check and remove function
		 */
		public function add_admin_css() {
			if( ! $this->active && ! $this->verified ) {
				print( '<style type="text/css">#submit_clevernode_connect { display: none; }</style>' );
			}
		}

		/**
		 * Add Plugin Settings Link.
		 * 
		 * Add settings link in plugin page list.
		 * 
		 * @see plugin_action_links_{plugin_file}
		 * 
		 * @since 1.0.0
		 */
		public function plugin_settings_link( $links ) {
			// Build and escape the URL.
			$url = esc_url( add_query_arg(
				'page',
				'clevernode-settings',
				get_admin_url() . 'admin.php'
			) );
			
			// Create the link.
			$settings_link = "<a href='$url'>" . __( 'Settings', 'clevernode-related-content' ) . '</a>';
			
			// Adds the link to the end of the array.
			array_unshift(
				$links,
				$settings_link
			);

			return $links;
		}

		/**
		 * Register plugin page.
		 *
		 * Registers plugin settings page in admin menu.
		 *
		 * @since 1.0.0
		 *
		 * @see admin_menu
		 */
		public function register_menu_page() {
			add_menu_page(
				__( 'CleverNode Settings', 'clevernode-related-content' ),
				__( 'CleverNode', 'clevernode-related-content' ),
				'manage_options',
				'clevernode-settings',
				array( $this, 'plugin_page' ),
				'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNCIgaGVpZ2h0PSIxNCIgdmlld0JveD0iMCAwIDMuNzA0IDMuNzA0Ij48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIC0yOTMuMjk2KSI+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMy43MDQgMjk0LjAwMnYyLjI5MmEuMTc2LjE3NiAwIDAgMS0uMTc2LjE3NUgyLjM1NmEuMjY0LjI2NCAwIDAgMS0uMjY0LS4yNjQuMjY1LjI2NSAwIDAgMSAuMDc4LS4xODZsLjc0Ni0uNzQ3YS4xNzYuMTc2IDAgMCAwIDAtLjI1bC0uNzQ3LS43NDZhLjI2NC4yNjQgMCAwIDEtLjA3OC0uMTg2LjI2NC4yNjQgMCAwIDEgLjI2NS0uMjY0aDEuMTcyYS4xNzYuMTc2IDAgMCAxIC4xNzYuMTc2ek0xLjUzNSAyOTQuMjc2bC0uNzQzLjc0M2EuMTgzLjE4MyAwIDAgMCAwIC4yNThsLjc0My43NDNhLjI2NC4yNjQgMCAwIDEgLjA3OC4xODcuMjY0LjI2NCAwIDAgMS0uMjY1LjI2M0guMTgzYS4xODMuMTgzIDAgMCAxLS4xODMtLjE4M3YtMi4yNzhhLjE4My4xODMgMCAwIDEgLjE4My0uMTgzaDEuMTY1YS4yNjQuMjY0IDAgMCAxIC4yNjQuMjY0LjI2NC4yNjQgMCAwIDEtLjA3Ny4xODZ6IiBzdHlsZT0ic3Ryb2tlLXdpZHRoOi4wMTk1Mzk4NDtmaWxsOiNmZmYiLz48cmVjdCBjbGFzcz0iY2xzLTIiIHg9IjIwOS41MSIgeT0iMjA2Ljg5IiB3aWR0aD0iMS4wMDIiIGhlaWdodD0iMS4wMDIiIHJ4PSIuMTk1IiB0cmFuc2Zvcm09InJvdGF0ZSg0NSkiIHN0eWxlPSJzdHJva2Utd2lkdGg6LjAxOTUzOTg0O2ZpbGw6I2ZmZiIvPjwvZz48L3N2Zz4=',
				100,
			);
		}

		/**
		 * Init plugin settings.
		 *
		 * Registers plugin settings, sections and fields.
		 *
		 * @since 1.0.0
		 *
		 * @see admin_init
		 * @see Classes/Settings_API.php
		 */
		public function init_settings() {
			// set the settings
			$this->settings_api->set_settings( $this->set_plugin_setting() );
			$this->settings_api->set_sections( $this->set_setting_sections() );
			$this->settings_api->set_fields( $this->set_setting_fields() );
			$this->settings_api->set_elements( $this->get_settings_elements() );

			// initialize settings
			$this->settings_api->admin_init();
		}

		/**
		 * Get site IP.
		 *
		 * Get site IP to display in support tab.
		 *
		 * @since 1.0.1
		 *
		 * @see admin_init
		 */
		public function get_site_ip() {
			$user_data = get_option( 'clevernode_account_data' );

			if( ! is_array( $user_data ) || empty( $user_data ) ) {
				$user_data = array();
			}

			if( ! isset( $user_data['account_ip'] ) || empty( $user_data['account_ip'] ) ) {
				$ip = $this->http->send_get_request( 'https://ifconfig.me/ip' );

				if( isset( $ip["status"] ) && $ip['status'] === 200 ) {
					$user_data['account_ip'] = $ip['body'];
					update_option( 'clevernode_account_data', $user_data );
				}

			}
		}

		/**
		 * Registers settings options.
		 *
		 * @since 1.0.0
		 *
		 * @return array Plugin settings options.
		 */
		public function set_plugin_setting() {
			$settings = array(
				array(
					'group'     => 'clevernodedata',
					'menu_slug' => 'clevernode-settings',
					'option'    => 'clevernode_plugin_settings',
					'args'      => array( 'default' => '0' ),
				),
			);

			return $settings;
		}

		/**
		 * Set settings fields.
		 * 
		 * Set settings fields for each settings sections.
		 *
		 * @since 1.0.0
		 *
		 * @return array Settings fields.
		 */
		public function set_setting_fields( $fields = array() ) {
			if ( $this->active && $this->verified ) {
				// Checkbox: Open Graph Enabled
				$fields["has_og"] = array(
					'name'      => 'clevernode_plugin_settings[has_og]',
					'label'     => __( 'Open Graph enabled?', 'clevernode-related-content' ),
					'class'     => 'checkbox-switch',
					'type'      => 'checkbox',
					/* translators: link to facebook developer console to check open graph */
					'info'      => sprintf( __( '<a href="https://developers.facebook.com/tools/debug/?q=%s" target="_blank">Find out</a> if your site uses Open Graph and enable this setting if so', 'clevernode-related-content' ), urlencode( get_site_url() ) ),
					'section'   => 'clevernode_metatags',
					'option'    => 'clevernode_plugin_settings',
					'menu_slug' => 'clevernode-settings'
				);
				// Text: Omit from title
				$fields["meta_blankin_title"] = array(
					'name'              => 'clevernode_plugin_settings[meta_blankin_title]',
					'label'             => __( 'Title filter', 'clevernode-related-content' ),
					'desc'              => __( 'Insert HTML to omit from the Title tag', 'clevernode-related-content' ),
					'type'              => 'text',
					'class'             => ( $this->has_og == "1" ) ? 'switch-no' : 'switch-no switch-default',
					'placeholder'       => __( 'ex: Site Name - ', 'clevernode-related-content' ),
					'section'           => 'clevernode_metatags',
					'option'            => 'clevernode_plugin_settings',
					'menu_slug'         => 'clevernode-settings',
					'sanitize_callback' => 'sanitize_text_field',
				);
				// Image: Default image if no featured
				$fields["default_image"] = array(
					'name'              => 'clevernode_plugin_settings[default_image]',
					'label'             => __( 'Default image', 'clevernode-related-content' ),
					'desc'              => __( 'If the featured image is not set for the post, you can upload an image to display', 'clevernode-related-content' ),
					'type'              => 'image',
					'class'             => ( $this->has_og == "1" ) ? 'switch-no' : 'switch-no switch-default',
					'section'           => 'clevernode_metatags',
					'option'            => 'clevernode_plugin_settings',
					'menu_slug'         => 'clevernode-settings',
					'sanitize_callback' => 'sanitize_text_field'
				);
				// Radio: Display below post or with shortcode
				$fields["display_opt"] = array(
					'name'    => 'clevernode_plugin_settings[display_opt]',
					'label'   => __( 'Enable filter or shortcode', 'clevernode-related-content' ),
					'type'    => 'radio',
					'options' => [
						'on'  => __( 'Enable <em>the_content</em> filter', 'clevernode-related-content' ),
						'off' => __( 'Enable and insert shortcode <code>[clevernode-related]</code> in post content', 'clevernode-related-content' ),
					],
					'default'           => 'on',
					'section'           => 'clevernode_display',
					'option'            => 'clevernode_plugin_settings',
					'menu_slug'         => 'clevernode-settings',
					'sanitize_callback' => 'sanitize_text_field',
				);
			}

			return $fields;
		}

		/**
		 * Registers settings sections.
		 * 
		 * Check if account is active and verified to show sections.
		 *
		 * @since 1.0.0
		 *
		 * @return array Settings sections.
		 */
		public function set_setting_sections() {
			$sections = array(
				array(
					'id'           => 'clevernode_connect',
					'title'        => __( 'Setup Connection', 'clevernode-related-content' ),
					'desc'         => '',
					'class'        => '',
					'menu_slug'    => 'clevernode-settings',
					'group'        => 'clevernodedata',
				)
			);

			if( $this->active && $this->verified ) {
				// Meta Tags section
				$sections[] = array(
					'id'        => 'clevernode_metatags',
					'title'     => __( 'Meta Tags', 'clevernode-related-content' ),
					'desc'      => function() {
						_e( '<p>This plugin inserts some meta tags in single posts, which are useful for gathering the necessary information.</p><p>If your site uses <strong><abbr title="Open Graph is a technology first introduced by Facebook in 2010 that allows integration between Facebook and its user data and a website. By integrating Open Graph meta tags into your page\'s content, you can identify which elements of your page you want to show when someone share\'s your page.">OG Tags</abbr></strong> you can enable the setting, otherwise you can choose which image to show if the post does not have a featured one and if necessary insert the <em>prefix</em> or <em>suffix</em> of your post in the title tag.', 'clevernode-related-content' );
					},
					'class'     => '',
					'menu_slug' => 'clevernode-settings',
					'group'     => 'clevernodedata',
				);
				// Display section
				$sections[] = array(
					'id'        => 'clevernode_display',
					'title'     => __( 'Display', 'clevernode-related-content' ),
					'desc'      => function() {
						_e( '<p>This plugin auto inserts a <strong>script tag</strong> after the content of single post to display related articles.<br>You can choose to insert the <strong>shortcode</strong> manually instead, if you want to place related posts elsewhere on the page.</p>', 'clevernode-related-content' );
					},
					'class'     => '',
					'menu_slug' => 'clevernode-settings',
					'group'     => 'clevernodedata',
				);
			}
			// Support section
			$sections[] = array(
				'id'        => 'clevernode_help',
				'title'     => __( 'Get Support', 'clevernode-related-content' ),
				'desc'      => '',
				'class'     => '',
				'menu_slug' => 'clevernode-settings',
				'group'     => 'clevernodedata',
			);

			return $sections;
		}

		/**
		 * Registers settings elements.
		 * 
		 * Register settings custom forms as settings page element.
		 *
		 * @since 1.0.0
		 *
		 * @return array Settings sections.
		 */
		public function get_settings_elements() {
			// Get user data for support tpl
			$user_data = get_option( 'clevernode_account_data' );

			$elements = array(
				// Help tab content
				array(
					'position' => 'top',
					'name'     => 'clevernode_help_content',
					'id'       => 'element_clevernode_help',
					'section'  => 'clevernode_help',
					'class'    => '',
					'type'     => 'custom_content',
					'tpl'      => 'help-tab',
					'tpl_args' => array(
						'title'         => 'Get Support',
						'account_name'  => isset( $user_data['account'] ) ? $user_data['account'] : '',
						'account_ip'    => isset( $user_data['account_ip'] ) ? $user_data['account_ip'] : '',
					),
				),
				// Connection form
				array(
					'position' => 'top',
					'name'     => 'clevernode_connection',
					'id'       => 'element_clevernode_connect',
					'section'  => 'clevernode_connect',
					'class'    => '',
					'type'     => 'connection_form',
					'connect'  => ( $this->active && $this->verified ) ? true : false
				),
			);

			return $elements;
		}

		/**
		 * Page for plugin settings.
		 *
		 * Outputs plugin settings page with sections and fields.
		 *
		 * @since 1.0.0
		 *
		 * @see admin_init
		 * @see Classes/Settings_API.php
		 */
		public function plugin_page() {
			print( '<div class="clevernode-settings wrap">' );
			
			printf( '<h1>%s</h1>', __( 'CleverNode Plugin Settings', 'clevernode-related-content' ) );
			
			settings_errors();
			
			$this->settings_api->get_navigation();
			$this->settings_api->get_forms();

			print( '</div>' );
			

		}

		/**
		 * Plugin Settings Update Notice.
		 * 
		 * @see pre_update_option_clevernode_plugin_settings
		 * @since 1.0.8
		 */
		public function plugin_settings_update_notice( $value, $old_value, $option ) {
			set_transient( 'clevernode-admin-notice-connection', 'connected', 5 );

			return $value;
		}
		
		/**
		 * Connection Feedback Modal.
		 * 
		 * @see admin_footer
		 * @since 1.0.8
		 */
		public function plugin_info_modal() {
			if( get_current_screen()->base !== 'toplevel_page_clevernode-settings' ) return;

			if( ! get_transient( 'clevernode-admin-notice-connection' ) ) return;

			$attr = array(
				'logo_url'    => $this->plugin_url . 'public/img/icon-clevernode.jpg',
				'icon_hand_r' => $this->plugin_url . 'public/img/icon-hand-r.svg'
			);
			
			echo $this->utility->get_template_html('banner-connect', $attr);

			delete_transient( 'clevernode-admin-notice-connection' );
		}

		/**
		 * Display Premium Banner.
		 * 
		 * @see plugin_page
		 * @since 1.0.7
		 */
		public function plugin_banner() {
			$attr = array(
				'logo_url' => $this->plugin_url . 'public/img/icon-clevernode.jpg'
			);
			
			echo $this->utility->get_template_html('banner-premium', $attr);
		}

	}

endif;