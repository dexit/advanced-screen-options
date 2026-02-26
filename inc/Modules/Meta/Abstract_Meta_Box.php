<?php
/**
 * Abstract class to register post meta box.
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions\Modules\Meta;

use AdvancedScreenOptions\Contracts\Interfaces\Registrable;

/**
 * Base class to register post meta boxes.
 */
abstract class Abstract_Meta_Box implements Registrable {

	/**
	 * Get meta box ID.
	 *
	 * @return lowercase-string&non-empty-string
	 */
	abstract public static function get_id(): string;

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		add_action( 'add_meta_boxes', [ $this, 'register_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_meta_box_data' ] );
		add_filter( 'manage_edit-adv-screen-options_columns', [ $this, 'add_custom_post_columns' ] );
		add_action( 'manage_adv-screen-options_posts_custom_column', [ $this, 'custom_post_column_content' ], 10, 2 );
	}

	/**
	 * To register meta box.
	 */
	abstract public function register_meta_box(): void;

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box_data( int $post_id ): void {
		// Verify if this is an auto save routine.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	}

	/**
	 * Add custom columns to post list table.
	 *
	 * @param array<string, string> $columns Existing columns.
	 *
	 * @return array<string, string> Modified columns. Must always return an array.
	 */
	public function add_custom_post_columns( array $columns ): array {
		return $columns;
	}

	/**
	 * Output content for custom columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 *
	 * @return void
	 */
	abstract public function custom_post_column_content( string $column, int $post_id ): void;
}
