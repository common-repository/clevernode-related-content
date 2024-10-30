<?php
/**
 * Uninstall Plugin.
 * 
 * @package clevernode-related-content
 */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Delete plugin options.
 *
 * Delete all plugin options at plugin unistall.
 *
 * @since 1.0.0
 */
foreach ( wp_load_alloptions() as $option => $value ) {
    if ( strpos( $option, 'clevernode_' ) === 0 ) {
        delete_option( $option );
    }
}