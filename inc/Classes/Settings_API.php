<?php
/**
 * Plugin Settings API.
 * 
 * Based on Tareq1988 Wordpress Settings API Class.
 * @see https://github.com/tareq1988/wordpress-settings-api-class
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
use CleverNodeRCFree\Classes\Settings_API_Fields;

/**
 * Setting API.
 * 
 * Handles registration for plugin settings, sections and fields.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Settings_API' ) ) :

	class Settings_API {

		/**
		 * Settings options array.
		 *
		 * @since 1.0.0
		 * 
		 * @var array
		 */
		protected $settings_options = array();

		/**
		 * Settings sections array.
		 * 
		 * @since 1.0.0
		 *
		 * @var array
		 */
		protected $settings_sections = array();

		/**
		 * Settings fields array.
		 * 
		 * @since 1.0.0
		 *
		 * @var array
		 */
		protected $settings_fields = array();

		/**
		 * Settings elements array.
		 * 
		 * @since 1.0.0
		 *
		 * @var array
		 */
		protected $settings_elements = array();

		/**
		 * Settings fields callback.
		 * 
		 * @since 1.0.0
		 *
		 * @var array
		 */
		protected $field_callback = null;

		public function __construct() {
			/**
			 * Add scripts for fields to admin.
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			/**
			 * Init fields callback class.
			 */
			$this->field_callback = new Settings_API_Fields();
		}

		/**
		 * Enqueue scripts.
		 * 
		 * Enqueue scripts and styles for settings fields.
		 * 
		 * @since 1.0.0
		 */
		function admin_enqueue_scripts() {
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_media();
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery' );
		}

		/**
		 * Set settings options.
		 * 
		 * @since 1.0.0
		 *
		 * @param array   $setting array
		 */
		function set_settings( $options ) {
			$this->settings_options = $options;

			return $this;
		}

		/**
		 * Set settings sections.
		 * 
		 * @since 1.0.0
		 *
		 * @param array   $sections setting sections array
		 */
		function set_sections( $sections ) {
			$this->settings_sections = $sections;

			return $this;
		}

		/**
		 * Add a single section.
		 * 
		 * @since 1.0.0
		 *
		 * @param array   $section
		 */
		function add_section( $section ) {
			$this->settings_sections[] = $section;

			return $this;
		}

		/**
		 * Set settings fields.
		 * 
		 * @since 1.0.0
		 *
		 * @param array   $fields settings fields array
		 */
		function set_fields( $fields ) {
			$this->settings_fields = $fields;

			return $this;
		}

		/**
		 * Set settings element.
		 * 
		 * @since 1.0.0
		 *
		 * @param array   $fields settings fields array
		 */
		public function set_elements( $element ) {
			$this->settings_elements = $element;

			return $this;
		}

		/**
		 * Init Settings.
		 * 
		 * Registers settings sections, fields and plugin settings.
		 * 
		 * @since 1.0.0
		 */
		public function admin_init() {
			// add setting sections
			$this->register_setting_sections();
			// add setting fields
			$this->register_setting_fields();
			// register setting
			$this->register_plugin_setting();
		}

		/**
		 * Register setting sections.
		 * 
		 * @since 1.0.0
		 */
		public function register_setting_sections() {
			foreach ( $this->settings_sections as $sec ) {
				add_settings_section( $sec["id"], $sec["title"], $sec["desc"], $sec["menu_slug"] );
			}
		}

		/**
		 * Register settings fields.
		 * 
		 * @since 1.0.0
		 */
		public function register_setting_fields() {
			foreach ( $this->settings_fields as $id => $field ) {
				$args = [
					'menu_slug'         => $field["menu_slug"],
					'id'                => $id,
					'class'             => isset( $field['class'] ) ? $field['class'] : $field["name"],
					'label_for'         => $id,
					'desc'              => isset( $field['desc'] ) ? $field['desc'] : '',
					'name'              => $field["name"],
					'label'             => $field["label"],
					'section'           => $field["section"],
					'option'            => $field["option"],
					'required'          => isset( $field['required'] ) ? $field['required'] : false,
					'info'              => isset( $field['info'] ) ? $field['info'] : null,
					'size'              => isset( $field['size'] ) ? $field['size'] : null,
					'options'           => isset( $field['options'] ) ? $field['options'] : '',
					'std'               => isset( $field['default'] ) ? $field['default'] : '',
					'type'              => $field["type"],
					'placeholder'       => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
					'min'               => isset( $field['min'] ) ? $field['min'] : '',
					'max'               => isset( $field['max'] ) ? $field['max'] : '',
					'step'              => isset( $field['step'] ) ? $field['step'] : '',
					'callback'          => array( $this->field_callback, 'callback_' . $field["type"] )
				];

				add_settings_field( $args["name"], $args["label"], $args["callback"], $args["menu_slug"], $args["section"], $args );
			}
		}

		/**
		 * Register settings options.
		 * 
		 * @since 1.0.0
		 */
		public function register_plugin_setting() {
			foreach ( $this->settings_options as $opt ) {
				$opt["args"]["sanitize_callback"] = $this->set_sanitize_callback();
				register_setting( $opt["group"], $opt["option"], $opt["args"] );
				add_option( $opt["option"] );
			}
		}

		/**
		 * Set sanitize callback.
		 * 
		 * @since 1.0.0
		 */
		public function set_sanitize_callback() {
			return array( $this, 'get_sanitize_callback' );
		}

		/**
		 * Get sanitize callback.
		 * 
		 * @since 1.0.0
		 */
		public function get_sanitize_callback( $input ) {
			$new = array();
			$fields = $this->settings_fields;

			if( $input ) {
				foreach ($input as $name => $value) {
					$type = $fields[$name]["type"];

					switch ($type) {
						case 'checkbox':
						$new[$name] = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
						break;

						case 'radio':
						$new[$name] = $value === 'on' ? 'on' : 'off';
						break;

						default:
						$new[$name] = sanitize_text_field($value);
						break;
					}
				}
			}

			return $new;
		}

		/**
		 * Get settings forms.
		 * 
		 * Display settings forms for each settings options.
		 * 
		 * @since 1.0.0
		 */
		public function get_forms() {
			$sections = $this->settings_options;
		?>
			<div class="metabox-holder">
		<?php
				foreach ( $sections as $section ) {

					$this->do_settings_sections( $section['menu_slug'], $section['group'] );

				}
		?>
			</div>
		<?php
		}

		/**
		 * Show navigations as tab.
		 *
		 * Shows all the settings section labels as tab.
		 * 
		 * @since 1.0.0
		 */
		public function get_navigation() {
			$html = sprintf( '<h2 class="nav-tab-wrapper">' );

			$count = count( $this->settings_sections );

			// don't show the navigation if only one section exists
			if ( $count === 1 ) {
				return;
			}

			foreach ( $this->settings_sections as $tab ) {
				$html .= sprintf(
					'<a href="#%1$s" class="nav-tab%3$s" id="%1$s-tab">%2$s</a>',
					esc_attr( $tab['id'] ),
					esc_attr( $tab['title'] ),
					esc_attr( $tab['class'] )
				);
			}

			$html .= sprintf( '</h2>' );

			print( $html );
		}

		/**
		 * Do setting sections.
		 * 
		 * Display sections groups form and elements.
		 * 
		 * @since 1.0.0
		 * 
		 * @see https://developer.wordpress.org/reference/functions/do_settings_sections/
		 */
		public function do_settings_sections( $page, $group ) {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections[ $page ] ) ) {
				return;
			}

			$this->do_settings_elements( 'top' );

			print( '<form action="options.php" method="POST">' );

			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
				printf( '<div id="%s" class="group">', $section["id"] );
				if ( isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
					if ( $section['title'] ) {
						print( "<h2>{$section['title']}</h2>\n" );
					}
				}

				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}

				if ( isset( $wp_settings_fields ) && isset( $wp_settings_fields[ $page ] ) && isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {

					print( '<table class="form-table" role="presentation">' );
						settings_fields($group);
						do_settings_fields( $page, $section['id'] );
					print( '</table>' );

					printf( '<div class="input-holder" %1$s>%2$s</div>', isset( $section['id'] ) ? ' id="submit_' . esc_attr( $section['id'] ) . '"' : '', get_submit_button() );

				}
				printf( '</div><!-- / end %s section -->', $section["id"] );
			}

			print( '</form>' );

			$this->do_settings_elements( 'bottom' );

		}

		/**
		 * Do settings elements.
		 * 
		 * @since 1.0.0
		 */
		private function do_settings_elements( $position ) {
			foreach ( $this->settings_sections as $section ) {
				printf(
					'<div id="%1$s-part-%2$s" class="group">',
					esc_attr( $section["id"] ),
					esc_attr( $position )
				);

				foreach ( $this->settings_elements as $element ) {

					if( $element["section"] == $section["id"] ) {


						if( $element["position"] == $position ) {

							$method = 'callback_' . $element["type"];
							$this->field_callback->$method($element);

						}
					}

				}

				printf(
					'</div><!-- / end %s section -->',
					esc_attr( $section["id"] )
				);
			}
		}

	}

endif;