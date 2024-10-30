<?php
/**
 * Plugin Name:       CleverNode Related Content
 * Plugin URI:        https://clevernode.it/
 * Description:       CleverNode Related Content is a semantic correlation service that allows you to place a collection of related articles on your WordPress site.
 * Version:           1.1.5
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Metup s.r.l.
 * Author URI:        https://metup.it/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://wordpress.org/plugins/clevernode-related-content/
 * Text Domain:       clevernode-related-content
 * Domain Path:       /languages
 */
/**
 * Don't load directly.
 */
if( ! defined('ABSPATH') ) {
	die( 'You can\'t access this file' );
}

/**
 * Check PHP Version.
 */
if ( version_compare( PHP_VERSION, '7.4', '<=' ) ) {
	return;
}

/**
 * Require Composer autoload.
 */
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * Define Plugin Data.
 */
$cnrp_path = plugin_dir_path(__FILE__);
$cnrp_root = plugin_dir_url(__FILE__);
$cnrp_data = get_file_data(__FILE__, array('Version'), 'plugin');
$cnrp_basefile = plugin_basename( __FILE__ );
$cnrp_basedir  = plugin_basename( __DIR__ );

/**
 * Setup Plugin.
 */
use CleverNodeRCFree\Plugin_Setup;
$clevernoderp = new Plugin_Setup( $cnrp_path, $cnrp_root, $cnrp_data[0], $cnrp_basefile, $cnrp_basedir );

/**
 * Activation Hook.
 */
register_activation_hook(
	__FILE__,
	array( $clevernoderp, 'activation_hook' )
);

/**
 * Deactivation Hook.
 */
register_deactivation_hook(
	__FILE__,
	array( $clevernoderp, 'deactivation_hook' )
);