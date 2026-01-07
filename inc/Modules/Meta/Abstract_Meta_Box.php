<?php
/**
 * Abstract class to register post meta box.
 *
 * @package ScreenOptions
 */

namespace ScreenOptions\Modules\Meta;

use ScreenOptions\Contracts\Interfaces\Registrable;

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
		add_action( 'content_save_pre' , [ $this, 'check_metadata_before_save' ] );
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
	 * Check role meta before saving to ensure at least one role is selected.
	 *
	 * @param string $content The post content.
	 * @return string The post content.
	 */
	public function check_metadata_before_save( string $content ): string {
		return $content;
	}
}
