<?php
/**
 * Plugin Name: Screen Options
 * Description: Screen Options - Manage your admin screen options and column visibility with ease.
 * Author: rtCamp
 * Author URI: https://rtcamp.com
 * Plugin URI: https://github.com/rtCamp/screen-options/
 * Update URI: https://github.com/rtCamp/screen-options/
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: screen-options
 * Domain Path: /languages
 * Version: 1.0.0
 * Requires PHP: 8.1
 * Requires at least: 6.8
 * Tested up to: 6.9
 *
 * @package ScreenOptions
 */

namespace ScreenOptions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit();

/**
 * Define the plugin constants.
 */
function constants(): void {
	/**
	 * Version of the plugin.
	 */
	define( 'SCREENOPTIONS_VERSION', '1.0.0' );

	/**
	 * Root path to the plugin directory.
	 */
	define( 'SCREENOPTIONS_DIR', plugin_dir_path( __FILE__ ) );

	/**
	 * Root URL to the plugin directory.
	 */
	define( 'SCREENOPTIONS_URL', plugin_dir_url( __FILE__ ) );

	/**
	 * Plugin basename.
	 */
	define( 'SCREENOPTIONS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

constants();

// If autoloader failed, we cannot proceed.
require_once __DIR__ . '/inc/Autoloader.php';
if ( ! \ScreenOptions\Autoloader::autoload() ) {
	return;
}

/**
 * Load plugin.
 */
if ( class_exists( 'ScreenOptions\Main' ) ) {
	add_action(
		'plugins_loaded',
		'\ScreenOptions\load_plugin'
	);
}

/**
 * Load ScreenOptions plugin functionality.
 *
 * @return void
 */
function load_plugin(): void {
	\ScreenOptions\Main::instance();

	//phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- @todo remove before submitting to .org.
	load_plugin_textdomain( 'screen-options', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
