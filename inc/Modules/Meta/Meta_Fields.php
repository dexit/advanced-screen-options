<?php
/**
 * Wrapper functions for post meta and options operations.
 *
 * @package AdvancedScreenOptions\Modules\Meta
 */

namespace AdvancedScreenOptions\Modules\Meta;

/**
 * Class Meta_Fields
 *
 * Provides static wrapper methods for get_post_meta, update_post_meta,
 * get_option, and update_option operations.
 */
class Meta_Fields {

	/**
	 * Meta key for screen options roles.
	 */
	public const ROLES_POSTMETA_KEY = 'screen_options_roles';

	/**
	 * Meta key for screen options post type.
	 */
	public const POST_TYPE_POSTMETA_KEY = 'screen_options_post_type';

	/**
	 * Meta key for screen options columns.
	 */
	public const COLUMNS_POSTMETA_KEY = 'screen_options_columns';

	/**
	 * Meta key for screen options lock.
	 */
	public const LOCK_POSTMETA_KEY = 'screen_options_lock';

	/**
	 * Option key for available columns.
	 */
	public const AVAILABLE_COLUMNS_OPTION_KEY = 'screen_options_available_columns';

	/**
	 * Get screen options roles from post meta.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array The array of assigned roles.
	 */
	public static function get_roles( int $post_id ): array {
		if ( ! $post_id ) {
			return [];
		}

		$roles = get_post_meta( $post_id, self::ROLES_POSTMETA_KEY, true );

		return is_array( $roles ) ? $roles : [];
	}

	/**
	 * Update screen options roles post meta.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $roles   The array of roles to set.
	 *
	 * @return bool True if the meta value was added or changed; false if the value was unchanged or if the update failed.
	 */
	public static function update_roles( int $post_id, array $roles ): bool {
		if ( ! $post_id ) {
			return false;
		}

		// update_post_meta() returns false both on failure and when the value is unchanged; we expose this as false.
		return (bool) update_post_meta( $post_id, self::ROLES_POSTMETA_KEY, $roles );
	}

	/**
	 * Get screen options post type from post meta.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string The post type.
	 */
	public static function get_post_type( int $post_id ): string {
		if ( ! $post_id ) {
			return '';
		}

		$post_type = get_post_meta( $post_id, self::POST_TYPE_POSTMETA_KEY, true );

		return is_string( $post_type ) ? $post_type : '';
	}

	/**
	 * Update screen options post type post meta.
	 *
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type to set.
	 *
	 * @return bool True if the update was successful, false otherwise.
	 */
	public static function update_post_type( int $post_id, string $post_type ): bool {
		if ( ! $post_id || empty( $post_type ) ) {
			return false;
		}

		return (bool) update_post_meta( $post_id, self::POST_TYPE_POSTMETA_KEY, $post_type );
	}

	/**
	 * Get screen options columns from post meta.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array The array of columns.
	 */
	public static function get_columns( int $post_id ): array {
		if ( ! $post_id ) {
			return [];
		}

		$columns = get_post_meta( $post_id, self::COLUMNS_POSTMETA_KEY, true );

		return is_array( $columns ) ? $columns : [];
	}

	/**
	 * Update screen options columns post meta.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $columns The array of columns to set.
	 *
	 * @return bool True if the update was successful, false otherwise.
	 */
	public static function update_columns( int $post_id, array $columns ): bool {
		if ( ! $post_id ) {
			return false;
		}

		return (bool) update_post_meta( $post_id, self::COLUMNS_POSTMETA_KEY, $columns );
	}

	/**
	 * Get screen options lock status from post meta.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if locked, false otherwise.
	 */
	public static function is_locked( int $post_id ): bool {
		if ( ! $post_id ) {
			return false;
		}

		return (bool) get_post_meta( $post_id, self::LOCK_POSTMETA_KEY, true );
	}

	/**
	 * Get screen options lock settings from post meta.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array The lock settings.
	 */
	public static function get_lock_settings( int $post_id ): array {
		if ( ! $post_id ) {
			return [];
		}

		$lock_settings = get_post_meta( $post_id, self::LOCK_POSTMETA_KEY, true );

		return is_array( $lock_settings ) ? $lock_settings : [];
	}

	/**
	 * Update screen options lock settings post meta.
	 *
	 * @param int              $post_id       The post ID.
	 * @param array|bool|mixed $lock_settings The lock settings to set (can be bool or array).
	 *
	 * @return bool True if the update was successful, false otherwise.
	 */
	public static function update_lock_settings( int $post_id, $lock_settings ): bool {
		if ( ! $post_id ) {
			return false;
		}

		return (bool) update_post_meta( $post_id, self::LOCK_POSTMETA_KEY, $lock_settings );
	}

	/**
	 * Get available columns from options.
	 *
	 * @return array The array of available columns.
	 */
	public static function get_available_columns(): array {
		$columns = get_option( self::AVAILABLE_COLUMNS_OPTION_KEY, [] );

		return is_array( $columns ) ? $columns : [];
	}

	/**
	 * Update available columns option.
	 *
	 * @param array $columns The array of available columns to set.
	 *
	 * @return bool True if the update was successful, false otherwise.
	 */
	public static function update_available_columns( array $columns ): bool {
		return update_option( self::AVAILABLE_COLUMNS_OPTION_KEY, $columns );
	}

	/**
	 * Delete available columns option.
	 *
	 * @return bool True if the deletion was successful, false otherwise.
	 */
	public static function delete_available_columns(): bool {
		return delete_option( self::AVAILABLE_COLUMNS_OPTION_KEY );
	}

	/**
	 * Delete all post meta for a specific post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if all deletions were successful, false otherwise.
	 */
	public static function delete_all_post_meta( int $post_id ): bool {
		if ( ! $post_id ) {
			return false;
		}

		$results = [
			delete_post_meta( $post_id, self::ROLES_POSTMETA_KEY ),
			delete_post_meta( $post_id, self::POST_TYPE_POSTMETA_KEY ),
			delete_post_meta( $post_id, self::COLUMNS_POSTMETA_KEY ),
			delete_post_meta( $post_id, self::LOCK_POSTMETA_KEY ),
		];

		return ! in_array( false, $results, true );
	}

	/**
	 * Get all post meta for a specific post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array Associative array of all meta values.
	 */
	public static function get_all_post_meta( int $post_id ): array {
		if ( ! $post_id ) {
			return [];
		}

		return [
			'roles'         => self::get_roles( $post_id ),
			'post_type'     => self::get_post_type( $post_id ),
			'columns'       => self::get_columns( $post_id ),
			'lock_settings' => self::get_lock_settings( $post_id ),
			'is_locked'     => self::is_locked( $post_id ),
		];
	}

	/**
	 * Bulk update post meta values.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $meta    Associative array of meta keys and values.
	 *
	 * @return bool True if all updates were successful, false otherwise.
	 */
	public static function bulk_update_post_meta( int $post_id, array $meta ): bool {
		if ( ! $post_id || empty( $meta ) ) {
			return false;
		}

		$results = [];

		if ( isset( $meta['roles'] ) && is_array( $meta['roles'] ) ) {
			$results[] = self::update_roles( $post_id, $meta['roles'] );
		}

		if ( isset( $meta['post_type'] ) && is_string( $meta['post_type'] ) ) {
			$results[] = self::update_post_type( $post_id, $meta['post_type'] );
		}

		if ( isset( $meta['columns'] ) && is_array( $meta['columns'] ) ) {
			$results[] = self::update_columns( $post_id, $meta['columns'] );
		}

		if ( isset( $meta['lock_settings'] ) ) {
			$results[] = self::update_lock_settings( $post_id, $meta['lock_settings'] );
		}

		return ! in_array( false, $results, true );
	}
}
