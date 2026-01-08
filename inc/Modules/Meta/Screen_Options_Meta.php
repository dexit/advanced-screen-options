<?php
/**
 * Register Screen Options meta box.
 *
 * @package ScreenOptions
 */

namespace ScreenOptions\Modules\Meta;

use ScreenOptions\Modules\Post_Types\Default_Screen_Options;

/**
 * Class Screen_Options_Meta
 */
class Screen_Options_Meta extends Abstract_Meta_Box {
	/**
	 * {@inheritDoc}
	 */
	public static function get_id(): string {
		return 'screen_options_meta_box';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_meta_box(): void {
		add_meta_box(
			self::get_id(),
			__( 'Screen Options Settings', 'screen-options' ),
			[ $this, 'render_meta_box' ],
			Default_Screen_Options::get_slug(),
			'normal',
			'default'
		);

		// Add meta box for role selection.
		add_meta_box(
			'screen_options_role_meta_box',
			__( 'Assign Roles', 'screen-options' ),
			[ $this, 'render_role_meta_box' ],
			Default_Screen_Options::get_slug(),
			'normal',
			'default'
		);

		// Add Lock Settings meta box.
		add_meta_box(
			'screen_options_lock_meta_box',
			__( 'Lock Screen Options', 'screen-options' ),
			[ $this, 'render_lock_meta_box' ],
			Default_Screen_Options::get_slug(),
			'side',
			'default'
		);

		// Add admin notice for validation errors.
		add_action( 'admin_notices', [ $this, 'display_validation_notices' ] );
	}

	/**
	 * Render meta box content.
	 */
	public function render_meta_box(): void {
		global $post;

		// Get all available post types except the screen options post type.
		$post_types = \get_post_types(
			[
				'public'   => true,
				'_builtin' => false,
			],
			'objects',
			'or'
		);

		$columns = \get_option( 'screen_options_available_columns', [] );

		// Get saved columns for this post.
		$saved_columns = [];
		if ( $post && $post->ID ) {
			$post_meta = \get_post_meta( $post->ID, 'screen_options_columns', true );
			if ( ! empty( $post_meta ) && is_array( $post_meta ) ) {
				$saved_columns = $post_meta;
			}
		}

		// Remove screen options post type from the list.
		unset( $post_types[ Default_Screen_Options::get_slug() ] );

		foreach ( $post_types as $post_type ) {
			// Get all screen options columns for a post type and add checkboxes to enable/disable them also add a checkbox to lock/unlock them.
			if ( isset( $columns[ 'edit-' . $post_type->name ] ) && is_array( $columns[ 'edit-' . $post_type->name ] ) ) {

				echo '<h3>' . esc_html__( 'Screen Options: ', 'screen-options' ) . esc_html( \ucfirst( $post_type->name ) ) . '</h3>';
				echo '<table class="form-table"><tbody><tr>';
				foreach ( $columns[ 'edit-' . $post_type->name ] as $column_id => $column_name ) {
					if ( 'cb' === $column_id ) {
						continue;
					}

					// Check if this column is saved for this post type.
					$is_checked = isset( $saved_columns[ 'edit-' . $post_type->name ] ) && in_array( $column_id, $saved_columns[ 'edit-' . $post_type->name ], true );
					// Default to checked if no saved data.
					if ( empty( $saved_columns ) ) {
						$is_checked = true;
					}

					echo '<td>';
					printf(
						'<label><input type="checkbox" name="screen_options_columns[%s][]" value="%s" %s> %s</label>',
						esc_attr( 'edit-' . $post_type->name ),
						esc_attr( $column_id ),
						$is_checked ? 'checked' : '',
						esc_html( \wp_strip_all_tags( $column_name ) )
					);
					echo '</td>';
				}
				echo '</tr></tbody></table>';
			}
		}
	}

	/**
	 * Render role meta box content.
	 */
	public function render_role_meta_box(): void {
		global $post;

		\wp_nonce_field( 'screen_options_save_meta', 'screen_options_meta_nonce' );

		// Get saved roles for this post.
		$saved_roles = [];
		if ( $post && $post->ID ) {
			$post_meta = \get_post_meta( $post->ID, 'screen_options_roles', true );
			if ( ! empty( $post_meta ) && is_array( $post_meta ) ) {
				$saved_roles = $post_meta;
			}
		}

		$is_all_users_checked = in_array( 'all_users', $saved_roles, true );

		printf(
			'<label><input type="checkbox" name="screen_options_assigned_roles[]" value="all_users" %s> <strong>%s</strong></label><br><br>',
			$is_all_users_checked ? 'checked' : '',
			esc_html__( 'All Users', 'screen-options' )
		);

		$roles = \wp_roles()->roles;
		foreach ( $roles as $role_key => $role ) {
			$is_checked = in_array( $role_key, $saved_roles, true );

			printf(
				'<label><input type="checkbox" name="screen_options_assigned_roles[]" value="%s" %s> %s</label><br>',
				esc_attr( $role_key ),
				$is_checked ? 'checked' : '',
				esc_html( $role['name'] )
			);
		}
		echo '<p class="description" style="color: #d63638; margin-top: 10px;">' . esc_html__( '* At least one role must be selected to publish', 'screen-options' ) . '</p>';
	}

	/**
	 * Render lock meta box content.
	 */
	public function render_lock_meta_box(): void {
		global $post;

		// Get saved lock setting for this post.
		$is_locked = false;
		if ( $post && $post->ID ) {
			$is_locked = (bool) \get_post_meta( $post->ID, 'screen_options_lock', true );
		}

		printf(
			'<label><input type="checkbox" name="screen_options_lock_settings" value="1" %s> %s</label><br>',
			$is_locked ? 'checked' : '',
			esc_html__( 'Lock Screen Options settings for assigned roles', 'screen-options' )
		);
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_meta_box_data( int $post_id ): void {

		// Verify if this is an auto save routine.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['screen_options_meta_nonce'] ) ) {
			return;
		}

		$nonce = \sanitize_text_field( \wp_unslash( $_POST['screen_options_meta_nonce'] ) );

		// Verify nonce.
		if ( ! isset( $_POST['screen_options_meta_nonce'] ) || ! \wp_verify_nonce( $nonce, 'screen_options_save_meta' ) ) {
			return;
		}

		// Check post type.
		if ( get_post_type( $post_id ) !== Default_Screen_Options::get_slug() ) {
			return;
		}

		// Check user permissions.
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$columns       = [];
		$roles         = [];
		$lock_settings = false;

		// Save assigned roles (only selected ones).
		if ( isset( $_POST['screen_options_assigned_roles'] ) && is_array( $_POST['screen_options_assigned_roles'] ) ) {
			$roles = array_map( 'sanitize_text_field', wp_unslash( $_POST['screen_options_assigned_roles'] ) );
		}

		// Validate that at least one role is selected.
		if ( empty( $roles ) ) {
			// Remove the hook to prevent infinite loop.
			remove_action( 'save_post', [ $this, 'save_meta_box_data' ] );

			// Prevent publishing by transitioning post to draft.
			\wp_update_post(
				[
					'ID'          => $post_id,
					'post_status' => 'draft',
				],
				true
			);

			// Re-add the hook.
			add_action( 'save_post', [ $this, 'save_meta_box_data' ] );

			// Set admin notice.
			\set_transient(
				'screen_options_role_error_' . $post_id,
				__( 'Screen option cannot be saved! At least one role must be selected before saving it. Settings saved as draft.', 'screen-options' ),
				45
			);
			return;
		}

		// Save default columns (only selected ones).
		if ( isset( $_POST['screen_options_columns'] ) && is_array( $_POST['screen_options_columns'] ) ) {
			$columns = array_map(
				function( $column_array ) {
					return array_map( 'sanitize_text_field', $column_array );
				},
				wp_unslash( $_POST['screen_options_columns'] )
			);
		}

		// Save lock settings.
		if ( isset( $_POST['screen_options_lock_settings'] ) ) {
			$lock_settings_value = sanitize_text_field( wp_unslash( $_POST['screen_options_lock_settings'] ) );
			if ( '1' === $lock_settings_value ) {
				$lock_settings = true;
			}
		}

		// Save data for each selected role.
		if ( ! empty( $roles ) ) {
			// Save as post meta for this specific post.
			\update_post_meta( $post_id, 'screen_options_roles', $roles );
			\update_post_meta( $post_id, 'screen_options_columns', $columns );
			\update_post_meta( $post_id, 'screen_options_lock', $lock_settings );
		}
	}

	/**
	 * Display validation error notices.
	 */
	public function display_validation_notices(): void {
		global $post;

		if ( ! $post || get_post_type( $post ) !== Default_Screen_Options::get_slug() ) {
			return;
		}

		$error_message = \get_transient( 'screen_options_role_error_' . $post->ID );
		if ( $error_message ) {
			printf(
				'<div class="notice notice-error is-dismissible"><p><strong>%s</strong></p></div>',
				esc_html( $error_message )
			);
			\delete_transient( 'screen_options_role_error_' . $post->ID );
		}
	}
}
