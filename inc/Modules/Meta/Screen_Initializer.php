<?php
/**
 * Initialize admin screens in background to capture default columns.
 *
 * @package ScreenOptions
 */

namespace ScreenOptions\Modules\Meta;

use ScreenOptions\Contracts\Interfaces\Registrable;

/**
 * Class Screen_Initializer
 *
 * This class initializes admin screens in the background to ensure all columns
 * are registered and available for capture, even when not on that specific screen.
 */
class Screen_Initializer implements Registrable {

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		add_action( 'admin_init', [ $this, 'initialize_screens_for_columns' ], 5 );
	}

	/**
	 * Initialize edit screens in the background to capture default columns.
	 *
	 * This method creates virtual admin screens for post types so that all
	 * column filters are triggered and columns can be recorded.
	 *
	 * @return void
	 */
	public function initialize_screens_for_columns(): void {
		global $typenow, $current_screen;

		// Store current values to restore later.
		$original_typenow = $typenow;
		$original_screen  = $current_screen;

		// Get all public post types.
		$post_types = \get_post_types(
			[
				'public'   => true,
				'_builtin' => false,
			],
			'objects'
		);

		// Add built-in post types.
		$builtin_post_types = [ 'post', 'page' ];
		foreach ( $builtin_post_types as $builtin_type ) {
			if ( ! post_type_exists( $builtin_type ) ) {
				continue;
			}

			$post_types[ $builtin_type ] = \get_post_type_object( $builtin_type );
		}

		// Initialize each screen and capture columns.
		foreach ( $post_types as $post_type ) {
			$this->initialize_edit_screen( $post_type->name );
		}

		// Restore original values.
		$typenow        = $original_typenow;
		$current_screen = $original_screen;
	}

	/**
	 * Initialize a specific edit screen to register columns.
	 *
	 * @param string $post_type The post type to initialize.
	 * @return void
	 */
	private function initialize_edit_screen( string $post_type ): void {
		global $typenow;

		// Set the global post type.
		$typenow = $post_type;

		// Create a screen object for this post type's edit screen.
		$screen = \WP_Screen::get( 'edit-' . $post_type );

		if ( ! $screen ) {
			return;
		}

		// Temporarily set as current screen to trigger column filters.
		$screen->set_current_screen();

		// Load the list table class to trigger column registration.
		$this->load_list_table( $post_type );

		// Capture and store the columns.
		$this->capture_columns( $screen );
	}

	/**
	 * Load the list table for a post type to register columns.
	 *
	 * @param string $post_type The post type.
	 * @return void
	 */
	private function load_list_table( string $post_type ): void {
		// Don't actually include the files, just trigger the column filters.
		if ( ! function_exists( '_get_list_table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
		}

		try {
			// Create list table instance to trigger column registration.
			// We use output buffering to suppress any output.
			ob_start();
			$list_table = \_get_list_table( 'WP_Posts_List_Table', [ 'screen' => 'edit-' . $post_type ] );

			if ( $list_table ) {
				// Trigger column registration.
				$list_table->get_columns();
			}
			ob_end_clean();
		} catch ( \Throwable $e ) {
			// Silently fail if there's an issue.
			if ( ob_get_level() > 0 ) {
				ob_end_clean();
			}
		}
	}

	/**
	 * Capture and store columns for a screen.
	 *
	 * @param \WP_Screen $screen The screen object.
	 * @return void
	 */
	private function capture_columns( \WP_Screen $screen ): void {
		// Get columns using WordPress core function.
		$columns = \get_column_headers( $screen );

		if ( empty( $columns ) ) {
			return;
		}

		// Get currently saved columns.
		$saved_columns = get_option( 'screen_options_available_columns', [] );

		if ( ! is_array( $saved_columns ) ) {
			$saved_columns = [];
		}

		// Store columns with both keys and labels.
		$column_data = [];
		foreach ( $columns as $column_key => $column_label ) {
			$column_data[ $column_key ] = $column_label;
		}

		// Check if we need to update.
		$needs_update = false;

		if ( ! array_key_exists( $screen->id, $saved_columns ) ) {
			$needs_update = true;
		} else {
			// Check if columns have changed.
			$current_keys = array_keys( $column_data );
			$saved_keys   = is_array( $saved_columns[ $screen->id ] )
				? array_keys( $saved_columns[ $screen->id ] )
				: [];

			$diff = array_diff( $current_keys, $saved_keys );
			if ( ! empty( $diff ) ) {
				$needs_update = true;
			}
		}

		// Update if needed.
		if ( ! $needs_update ) {
			return;
		}

		$saved_columns[ $screen->id ] = $column_data;
		update_option( 'screen_options_available_columns', $saved_columns, true );
	}
}
