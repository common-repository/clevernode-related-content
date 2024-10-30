<?php
/**
 * Plugin Admin Notices.
 * 
 * Handles admin notices via transients: sets, shows and delete.
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
 * Admin Notices.
 * 
 * Handles admin notices.
 *
 * @since 1.0.0
 */
if ( !class_exists( 'Admin_Notices' ) ) :

	class Admin_Notices {

		public function __construct() {
			add_action(
				'admin_notices',
				array( $this, 'admin_notices_action' )
			);
		}

		/**
		 * Display notice.
		 * 
		 * Displays admin messages in dismissible notice.
		 * 
		 * @since 1.0.0
		 * 
		 * @see admin_notices
		 */
		public function admin_notices_action() {
			$slug  = 'clevenode-admin-notice-';
			$types = [ 'warning', 'error', 'success' ];

			foreach ($types as $type) {
				$logs = get_transient( $slug . $type );

				if( is_array( $logs ) && ! empty( $logs ) ) {
					foreach ($logs as $log) {
						if( $type == "error" ) {
							if( is_array( $log ) && isset( $log["type"] ) && ! empty( $log["type"] ) && isset( $log["status"] ) && ! empty( $log["status"] ) ) {
								$st = esc_attr( $log["type"] ) . ' (' . esc_attr( $log["status"] ) . ')';
							} elseif ( isset( $log["status"] ) && ! empty( $log["status"] ) ) {
								$st = esc_attr( $log["status"] );
							}

							$html = sprintf( 
								/* translators: error notice content */
								__('<div class="%1$s notice is-dismissible clevernode-notice"><p><strong>Error %2$s</strong>: %3$s</p></div>', 'clevernode-related-content'),
								esc_attr( $type ),
								$st ?? '',
								$log["message"] ?? ''
							);
						} else {
							$html = sprintf( 
								'<div class="notice-%1$s notice is-dismissible clevernode-notice"><p>%2$s</p></div>',
								esc_attr( $type ),
								esc_html( $log )
							);
						}

						print( $html );
					}
				}

				// Delete transient after display
				delete_transient( $slug . $type );
			}
		}

		/**
		 * Set admin notice.
		 * 
		 * Set admin notice transient by type. Duration 2 min.
		 * 
		 * @since 1.0.0
		 * 
		 * @param string $type Notice type.
		 * @param array  $logs Notice info and text.
		 */
		public static function set_admin_notice( $type, $logs ) {
			$old_logs = get_transient( 'clevenode-admin-notice-' . $type );

			if( ! $old_logs ) {
				$old_logs = array();
			}

			set_transient( 'clevenode-admin-notice-' . $type, array_merge( $old_logs, $logs ), 180 );
		}

	}

endif;