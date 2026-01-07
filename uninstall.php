<?php
/**
 * This will be executed when the plugin is uninstalled.
 *
 * @package ScreenOptions
 */

declare( strict_types=1 );

namespace ScreenOptions;

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * The (site-specific) uninstall function.
 */
function uninstall(): void {
}
