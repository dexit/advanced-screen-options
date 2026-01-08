<?php
/**
 * Role based screen options assignments.
 *
 * @package ScreenOptions
 */

namespace ScreenOptions\Modules\Column_Assignment;

use ScreenOptions\Contracts\Interfaces\Registrable;

/**
 * Class for Role based Screen option assignment.
 */
class Role_Based_Screen_Options implements Registrable {

	/**
	 * Screen Options Posts ID constant.
	 *
	 * @var const
	 */
	private int $screen_options_posts_id = 0;

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		// we can use default_hidden_meta_boxes filter to set default hidden meta boxes based on role.
		add_filter( 'hidden_columns', [ $this, 'filter_hidden_columns' ], 10, 3 );
	}

	/**
	 * Filter hidden columns based on user role.
	 *
	 * @param array     $hidden_columns Array of hidden column IDs.
	 * @param WP_Screen $screen         Current screen object.
	 * @return array Modified array of hidden column IDs.
	 */
	public function filter_hidden_columns( $hidden_columns, $screen, $use_defaults ): array {

		if ( ! $use_defaults ) {
			return $hidden_columns;
		}

		$current_user = wp_get_current_user();
		$user_roles   = (array) $current_user->roles;

		// Fetch screen options posts based on user role.
		$screen_options_posts = \get_posts(
			[
				'post_type'      => 'default-screens',
				'posts_per_page' => -1,
				'meta_query'     => [
					[
						'key'     => 'screen_options_roles',
						'value'   => $user_roles[0],
						'compare' => 'LIKE',
					],
				],
			]
		);

		// If no screen options posts found, return original hidden columns.
		if ( empty( $screen_options_posts ) ) {
			return $hidden_columns;
		}

		$this->screen_options_posts_id = $screen_options_posts[0]->ID;

		$selected_columns = \get_post_meta(
			$this->screen_options_posts_id,
			'screen_options_columns',
			true
		);

		// If no selected columns found, return original hidden columns.
		if ( empty( $selected_columns) || ! is_array( $selected_columns ) ) {
			return $hidden_columns;
		}

		// Determine screen ID key for selected columns.
		$screen_id_key = $screen->id;
		if ( isset( $selected_columns[ $screen_id_key ] ) ) {
			$columns_for_screen = $selected_columns[ $screen_id_key ];
		}

		if ( empty( $columns_for_screen ) ) {
			return $hidden_columns;
		}

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

			// Check lock
			$is_locked = \get_post_meta(
				$this->screen_options_posts_id,
				'screen_options_lock',
				true
			);
			if ( ! empty( $is_locked ) ) {
				\add_filter( 'screen_options_show_submit', '__return_false', 50 );
			}

			return $columns_to_hide;
		}

		return $hidden_columns;
	}

	/**
	 * Check if screen options are locked and hide submit button if so.
	 *
	 * @return bool False if locked, true otherwise.
	 */
	public function check_lock_hide_submit( $show_hide_submit ): bool {
		if ( 0 === $this->screen_options_posts_id ) {
			return $show_hide_submit;
		}
		$is_locked = \get_post_meta(
			$this->screen_options_posts_id,
			'screen_options_lock',
			true
		);

		if ( ! empty( $is_locked ) ) {
			return false;
		}
		return $show_hide_submit;
	}
}
