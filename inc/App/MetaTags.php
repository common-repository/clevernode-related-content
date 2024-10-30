<?php
/**
 * Plugin Meta Tags.
 * 
 * Handles plugin meta tags added to page head and post content.
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
 * Meta Tag.
 * 
 * Insert meta tags to connect semantical reader.
 *
 * @since 1.0.0
 */
if ( !class_exists( 'MetaTags' ) ) :

	class MetaTags {

		private $metaOg, $metaDefaultImg, $metaBlankInTitle, $version;

		public function __construct($version) {
			$this->version = $version;

			/**
			 * Get options
			 */
			$options = get_option( 'clevernode_plugin_settings' );
			$this->metaOg          = isset( $options['has_og'] ) ? $options['has_og'] : false;
			$this->metaDefaultImg   = isset( $options['default_image'] ) ? $options['default_image'] : false;
			$this->metaBlankInTitle = isset( $options['meta_blankin_title'] ) ? $options['meta_blankin_title'] : false;

			/**
			 * Filter to add tag wrapping post content.
			 */
			add_filter(
				'the_content',
				array( $this, 'add_meta_edindex_content' ),
				98
			);

			/**
			 * Actions to add meta tags to site head.
			 */
			add_action( 'wp_head', array( $this, 'add_meta_edimage' ) );
			add_action( 'wp_head', array( $this, 'add_meta_edblank_title' ) );
			add_action( 'wp_head', array( $this, 'add_meta_edtitle' ) );
			add_action( 'wp_head', array( $this, 'add_meta_eddate' ) );
			add_action( 'wp_head', array( $this, 'add_meta_clversion' ) );
			add_action( 'wp_head', array( $this, 'add_settings_data' ) );
			
			// AMP Legacy support
			add_action( 'amp_post_template_head', array( $this, 'add_meta_edimage' ) );
			add_action( 'amp_post_template_head', array( $this, 'add_meta_edblank_title' ) );
			add_action( 'amp_post_template_head', array( $this, 'add_meta_edtitle' ) );
			add_action( 'amp_post_template_head', array( $this, 'add_meta_eddate' ) );
			add_action( 'amp_post_template_head', array( $this, 'add_meta_clversion' ) );
			add_action( 'amp_post_template_head', array( $this, 'add_settings_data' ) );

			/**
			 * Filter document title separator.
			 */
			add_filter(
				'document_title_separator',
				array( $this, 'chage_doc_title_separator' ),
				10,
				2
			);
		}

		/**
		 * Meta EdIndex.
		 * 
		 * Filter the_content to append commented tags.
		 * 
		 * @since 1.0.0
		 */
		public function add_meta_edindex_content( $content ){
			if( is_single() && is_main_query() ) {
				return '<!-- <EdIndex> -->' . $content . '<!-- </EdIndex> -->';
			}

			return $content;
		}

		/**
		 * Meta EdImage.
		 * 
		 * No display if OG enabled.
		 * Get featured post image or image fallback.
		 * 
		 * @since 1.0.0
		 */
		public function add_meta_edimage(){
			if ( !is_main_query() ) return;
			
			if ( !is_single() ) return;

			if( $this->metaOg === '1' ) return;

			global $post;
			$featured_img = get_the_post_thumbnail_url( $post );
			$default_img  = $this->metaDefaultImg === false ? $this->metaDefaultImg : wp_get_attachment_image_src($this->metaDefaultImg, 'full');

			if( $featured_img ) {
				$image_url = $featured_img;
			} else if( $default_img ) {
				$image_url = $default_img[0];
			}

			if( isset($image_url) ) {
				printf(
					'<Meta name="EdImage" content="%s"/>',
					esc_url( $image_url )
				);
			}
			return;
		}

		/**
		 * Meta EdBlankInTitle.
		 * 
		 * Filter title to omit site name.
		 * 
		 * @since 1.0.0
		 */
		public function add_meta_edblank_title(){
			if ( !is_main_query() ) return;
			
			if ( !is_single() ) return;

			if( $this->metaOg === '1' ) return;

			if( false !== $this->metaBlankInTitle && ! empty( $this->metaBlankInTitle ) ) {
				printf(
					'<Meta name="EdBlankInTitle" content="%s">',
					esc_attr( $this->metaBlankInTitle )
				);
			}
			return;
		}

		/**
		 * Meta EdTitle.
		 * 
		 * No display if EdBlankInTitle isset.
		 * Get post title or post "_yoast_wpseo_title" if isset.
		 * 
		 * @since 1.0.0
		 */
		public function add_meta_edtitle(){
			if ( !is_main_query() ) return;
			
			if ( !is_single() ) return;

			if( $this->metaOg === '1' ) return;

			global $post;

			if( false !== $this->metaBlankInTitle && ! empty( $this->metaBlankInTitle ) ) {
				$post_title = esc_html( str_replace( $this->metaBlankInTitle, '', $post->post_title ) );
			} else {
				$post_title = esc_html( $post->post_title );
			}

			if( ! empty( $post_title ) ) {
				printf(
					'<Meta name="EdTitle" content="%s">',
					esc_attr( $post_title )
				);
			}
			return;
		}

		/**
		 * Meta EdDateTime.
		 * 
		 * Get post published date.
		 * 
		 * @since 1.0.0
		 */
		public function add_meta_eddate(){
			if ( !is_main_query() ) return;
			
			if ( !is_single() ) return;

			if( $this->metaOg === '1' ) return;

			global $post;
			if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
				$post_date = get_the_modified_date( 'Y/m/d', $post );
			} else {
				$post_date = get_the_date( 'Y/m/d', $post );
			}

			if( isset($post_date) ) {
				printf(
					'<Meta name="EdDateTime" content="%s">',
					esc_attr( $post_date )
				);
			}
			return;
		}

		/**
		 * Meta Clevernode Version.
		 * 
		 * Get plugin version.
		 * 
		 * @since 1.1.2
		 */
		public function add_meta_clversion(){
			printf(
				'<Meta name="ClevernodeVers" content="%s">',
				esc_attr( $this->version )
			);
		}

		/**
		 * JSON Settings Data
		 */
		public function add_settings_data() {
			$settings = get_option( 'clevernode_plugin_settings' );

			if( empty( $settings ) ) {
				print( '<meta name="ClevernodeSettings" content="defaults">' );
				return;
			}

			// OG Tags
			printf(
				'<meta name="ClevernodeOGTags" content="%s">',
				isset( $settings["has_og"] ) && ! empty( $settings["has_og"] ) ? 'active' : 'inactive'
			);

			// Display Option
			printf(
				'<meta name="ClevernodeDisplay" content="%s">',
				isset( $settings["display_opt"] ) && ! empty( $settings["display_opt"] ) ? $settings["display_opt"] === 'on' ? 'filter' : 'shortcode' : 'filter'
			);
		}

		/**
		 * Change document title separator to help users 
		 * with EdBlankInTitle Meta.
		 * 
		 * @see document_title_separator
		 * 
		 * @since 1.0.0
		 * 
		 * @param string $sep Document title separator.
		 */
		public function chage_doc_title_separator( $sep ){
			return '−';
		}

	}

endif;