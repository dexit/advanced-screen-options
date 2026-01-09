<?php
/**
 * Role based screen options assignments.
 *
 * @package ScreenOptions
 */

namespace ScreenOptions\Modules\Column_Assignment;

use ScreenOptions\Contracts\Interfaces\Registrable;
use ScreenOptions\Modules\Core\Assets;
use ScreenOptions\Modules\Post_Types\Default_Screen_Options;
use WP_Query;

/**
 * Class for Role based Screen option assignment.
 */
class Role_Based_Screen_Options implements Registrable {

	/**
	 * Screen Options Posts ID.
	 *
	 * @var int
	 */
	private int $screen_options_posts_id = 0;

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		// Use the 'hidden_columns' filter to set default hidden columns based on user role.
		add_filter( 'hidden_columns', [ $this, 'filter_hidden_columns' ], 10, 3 );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 25 );
	}

	/**
	 * Filter hidden columns based on user role.
	 *
	 * @param array      $hidden_columns Array of hidden column IDs.
	 * @param \WP_Screen $screen         Current screen object.
	 * @param bool       $use_defaults   Whether to use default hidden columns.
	 *
	 * @return array Modified array of hidden column IDs.
	 */
	public function filter_hidden_columns( $hidden_columns, $screen, $use_defaults ): array {

		// If use_defaults is false, return original hidden columns.
		if ( ! $use_defaults ) {
			return $hidden_columns;
		}

		$this->screen_options_posts_id = $this->get_screen_options_post_id();

		if ( 0 === $this->screen_options_posts_id ) {
			return $hidden_columns;
		}

		$selected_columns = \get_post_meta(
			$this->screen_options_posts_id,
			'screen_options_columns',
			true
		);

		// If no selected columns found, return original hidden columns.
		if ( empty( $selected_columns ) || ! is_array( $selected_columns ) ) {
			return $hidden_columns;
		}

		$columns_for_screen = [];

		// Determine screen ID key for selected columns.
		$screen_id_key = $screen->id;
		if ( isset( $selected_columns[ $screen_id_key ] ) ) {
			$columns_for_screen = $selected_columns[ $screen_id_key ];
		}

		if ( empty( $columns_for_screen ) ) {
			return $hidden_columns;
		}

		$columns_for_screen[] = 'title';

		// Get current columns for the screen.
		$current_columns = array_keys( get_column_headers( $screen_id_key ) );
		$cb_column_key   = array_search( 'cb', $current_columns, true );
		if ( false !== $cb_column_key ) {
			unset( $current_columns[ $cb_column_key ] ); // Remove checkbox column if present.
		}

		// Determine columns to hide.
		$columns_to_hide = array_diff( $current_columns, $columns_for_screen );

		// If there are columns to hide, return them.
		if ( ! empty( $columns_to_hide ) ) {
			return $columns_to_hide;
		}

		return $hidden_columns;
	}

	/**
	 * Function to retrieve current screen options id based on user role.
	 *
	 * @return int Screen Options Post ID.
	 */
	public function get_screen_options_post_id(): int {

		if ( $this->screen_options_posts_id > 0 ) {
			return $this->screen_options_posts_id;
		}

		$current_user = wp_get_current_user();
		$user_roles   = (array) $current_user->roles;

		if ( empty( $user_roles ) ) {
			return 0;
		}

		// Build meta query to fetch screen options posts based on all user roles.
		$meta_query = []; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

		foreach ( $user_roles as $role ) {
			$meta_query[] = [
				'key'     => 'screen_options_roles',
				'value'   => $role,
				'compare' => 'LIKE',
			];
		}

		if ( count( $meta_query ) > 1 ) {
			$meta_query['relation'] = 'OR';
		}

		// Fetch screen options posts based on user role.
		$screen_options_posts = new WP_Query(
			[
				'post_type'      => Default_Screen_Options::get_slug(),
				'posts_per_page' => -1,
				'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'fields'         => 'ids',
				'post_status'    => 'publish',
			]
		);

		if ( empty( $screen_options_posts->posts ) || ! is_array( $screen_options_posts->posts ) ) {
			// If no posts found for the role, check for 'all_users'.
			$screen_options_posts = new WP_Query(
				[
					'post_type'      => Default_Screen_Options::get_slug(),
					'posts_per_page' => -1,
					'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						[
							'key'     => 'screen_options_roles',
							'value'   => 'all_users',
							'compare' => 'LIKE',
						],
					],
					'fields'         => 'ids',
					'post_status'    => 'publish',
				]
			);
		}

		// If still no posts found, return 0.
		if ( empty( $screen_options_posts->posts ) || ! is_array( $screen_options_posts->posts ) ) {
			return 0;
		}

		if ( isset( $screen_options_posts->posts[0] ) && is_int( $screen_options_posts->posts[0] ) ) {
			$this->screen_options_posts_id = $screen_options_posts->posts[0];
		}

		return $this->screen_options_posts_id;
	}

	/**
	 * Get lock settings for the current role.
	 *
	 * @return bool
	 */
	public function get_lock_settings_for_current_role(): bool {

		$screen = get_current_screen();

		if ( empty( $screen ) || empty( $screen->id ) ) {
			return false;
		}

		$hidden = get_user_option( 'manage' . $screen->id . 'columnshidden' );

		$use_defaults = ! is_array( $hidden );

		if ( ! $use_defaults ) {
			return false;
		}

		$screen_options_post_id = $this->get_screen_options_post_id();

		if ( 0 === $screen_options_post_id ) {
			return false;
		}

		$is_locked = \get_post_meta(
			$screen_options_post_id,
			'screen_options_lock',
			true
		);

		return ! empty( $is_locked );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( string $hook ) {

		// Add only on post listing pages.
		if ( strpos( $hook, 'edit.php' ) === false ) {
			return;
		}

		wp_enqueue_script( Assets::ADMIN_SCRIPTS_HANDLE );

		// Add lock settings to localized script data.
		Assets::set_localized_data(
			[
				'is_locked'   => $this->get_lock_settings_for_current_role(),
				'lockMessage' => \sprintf(
					'<strong>%1$s</strong> %2$s %3$s',
					/* translators: 1: Screen Options */
					__( 'Locked:', 'screen-options' ),
					__( 'columns are locked by the administrator. To modify your screen options, ', 'screen-options' ),
					\current_user_can( 'manage_options' ) ?
						__( 'go to settings -> default screen options.', 'screen-options' ) :
						__( 'please contact your site administrator.', 'screen-options' )
				),
			]
		);

		wp_localize_script(
			Assets::ADMIN_SCRIPTS_HANDLE,
			'ScreenOptionsSettings',
			Assets::get_localized_data()
		);
	}
}
