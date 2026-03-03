<?php
/**
 * Register Screen Options post type.
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions\Modules\Post_Types;

/**
 * Class Advanced_Screen_Options
 */
class Advanced_Screen_Options extends Abstract_Post_Type {

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'adv-screen-options';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_post_type(): void {

		$labels = [
			'name'               => _x( 'Advanced Screen Options', 'post type general name', 'screen-options' ),
			'singular_name'      => _x( 'Advanced Screen Options', 'post type singular name', 'screen-options' ),
			'menu_name'          => _x( 'Advanced Screen Options', 'admin menu', 'screen-options' ),
			'name_admin_bar'     => _x( 'Advanced Screen Options', 'add new on admin bar', 'screen-options' ),
			'add_new'            => _x( 'Add New', 'Advanced Screen Options', 'screen-options' ),
			'add_new_item'       => __( 'Add New Advanced Screen Options', 'screen-options' ),
			'new_item'           => __( 'New Advanced Screen Options', 'screen-options' ),
			'edit_item'          => __( 'Edit Advanced Screen Options', 'screen-options' ),
			'view_item'          => __( 'View Advanced Screen Options', 'screen-options' ),
			'all_items'          => __( 'Advanced Screen Options', 'screen-options' ),
			'search_items'       => __( 'Search Advanced Screen Options', 'screen-options' ),
			'parent_item_colon'  => __( 'Parent Advanced Screen Options:', 'screen-options' ),
			'not_found'          => __( 'No Advanced Screen Options found.', 'screen-options' ),
			'not_found_in_trash' => __( 'No Advanced Screen Options found in trash.', 'screen-options' ),
		];

		$args = wp_parse_args(
			[
				'public'       => false,
				'show_ui'      => true,
				'has_archive'  => false,
				'show_in_rest' => false,
				'supports'     => [ 'title' ],
				'show_in_menu' => 'options-general.php',
				'labels'       => $labels,
			],
			$this->default_args(),
		);

		// phpcs:ignore WordPress.NamingConventions.ValidPostTypeSlug.NotStringLiteral -- Slug is defined in get_slug method.
		register_post_type(
			self::get_slug(),
			$args
		);
	}
}
