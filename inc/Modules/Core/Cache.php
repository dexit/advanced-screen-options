<?php
/**
 * Cache wrapper class for transients and object cache.
 *
 * @package ScreenOptions\Modules\Core
 */

namespace ScreenOptions\Modules\Core;

/**
 * Class Cache
 *
 * Provides static wrapper methods for WordPress transient API and object cache.
 */
class Cache {

	/**
	 * Transient prefix for the plugin.
	 */
	public const TRANSIENT_PREFIX = 'screen_options_';

	/**
	 * Default transient expiration time (1 hour).
	 */
	public const DEFAULT_EXPIRATION = HOUR_IN_SECONDS;

	/**
	 * Get a transient value.
	 *
	 * @param string $key The transient key (without prefix).
	 *
	 * @return mixed The transient value, or false if not found.
	 */
	public static function get_transient( string $key ) {
		$transient_key = self::get_transient_key( $key );
		return get_transient( $transient_key );
	}

	/**
	 * Set a transient value.
	 *
	 * @param string $key        The transient key (without prefix).
	 * @param mixed  $value      The value to store.
	 * @param int    $expiration Optional. Time until expiration in seconds. Default is 1 hour.
	 *
	 * @return bool True if the value was set, false otherwise.
	 */
	public static function set_transient( string $key, $value, int $expiration = self::DEFAULT_EXPIRATION ): bool {
		$transient_key = self::get_transient_key( $key );
		return set_transient( $transient_key, $value, $expiration );
	}

	/**
	 * Delete a transient.
	 *
	 * @param string $key The transient key (without prefix).
	 *
	 * @return bool True if the transient was deleted, false otherwise.
	 */
	public static function delete_transient( string $key ): bool {
		$transient_key = self::get_transient_key( $key );
		return delete_transient( $transient_key );
	}

	/**
	 * Get a site transient value (multisite compatible).
	 *
	 * @param string $key The site transient key (without prefix).
	 *
	 * @return mixed The site transient value, or false if not found.
	 */
	public static function get_site_transient( string $key ) {
		$transient_key = self::get_transient_key( $key );
		return get_site_transient( $transient_key );
	}

	/**
	 * Set a site transient value (multisite compatible).
	 *
	 * @param string $key        The site transient key (without prefix).
	 * @param mixed  $value      The value to store.
	 * @param int    $expiration Optional. Time until expiration in seconds. Default is 1 hour.
	 *
	 * @return bool True if the value was set, false otherwise.
	 */
	public static function set_site_transient( string $key, $value, int $expiration = self::DEFAULT_EXPIRATION ): bool {
		$transient_key = self::get_transient_key( $key );
		return set_site_transient( $transient_key, $value, $expiration );
	}

	/**
	 * Delete a site transient.
	 *
	 * @param string $key The site transient key (without prefix).
	 *
	 * @return bool True if the site transient was deleted, false otherwise.
	 */
	public static function delete_site_transient( string $key ): bool {
		$transient_key = self::get_transient_key( $key );
		return delete_site_transient( $transient_key );
	}

	/**
	 * Remember a transient value (get from transient or set if not exists).
	 *
	 * This is a helper method that gets a value from transient, and if it doesn't exist,
	 * calls the callback to generate the value and stores it as a transient.
	 *
	 * @param string   $key        The transient key (without prefix).
	 * @param callable $callback   Callback to generate the value if not cached.
	 * @param int      $expiration Optional. Time until expiration in seconds. Default is 1 hour.
	 *
	 * @return mixed The transient or generated value.
	 */
	public static function remember_transient( string $key, callable $callback, int $expiration = self::DEFAULT_EXPIRATION ) {
		$value = self::get_transient( $key );

		if ( false === $value ) {
			$value = $callback();
			self::set_transient( $key, $value, $expiration );
		}

		return $value;
	}

	/**
	 * Clear all plugin transients.
	 *
	 * This method deletes all transients that start with the plugin prefix.
	 *
	 * @return int Number of transients deleted.
	 */
	public static function clear_all_transients(): int {
		global $wpdb;

		$count = 0;

		// Delete regular transients.
		$transients = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . self::TRANSIENT_PREFIX ) . '%'
			)
		);

		foreach ( $transients as $transient ) {
			$key = str_replace( '_transient_', '', $transient->option_name );
			if ( ! delete_transient( $key ) ) {
				continue;
			}

			++$count;
		}

		return $count;
	}

	/**
	 * Get the full transient key with prefix.
	 *
	 * @param string $key The transient key without prefix.
	 *
	 * @return string The full transient key with prefix.
	 */
	private static function get_transient_key( string $key ): string {
		// Don't double-prefix if already prefixed.
		if ( strpos( $key, self::TRANSIENT_PREFIX ) === 0 ) {
			return $key;
		}

		return self::TRANSIENT_PREFIX . $key;
	}
}
