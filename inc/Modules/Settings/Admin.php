<?php
/**
 * Settings class.
 * This class handles the settings page for the ScreenOptions plugin,
 *
 * @package ScreenOptions
 */

namespace ScreenOptions\Modules\Settings;

use ScreenOptions\Contracts\Interfaces\Registrable;
use ScreenOptions\Modules\Core\Assets;

/**
 * Class Settings
 */
class Admin implements Registrable {
	/**
	 * The menu slug for the admin menu.
	 *
	 * @todo need to replace globally with single source of truth.
	 *
	 * @var string
	 */
	public const MENU_SLUG = 'screen-options';

	/**
	 * The screen ID for the settings page.
	 */
	public const SCREEN_ID = self::MENU_SLUG . '-settings';

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		add_filter( 'plugin_action_links_' . SCREENOPTIONS_PLUGIN_BASENAME, [ $this, 'add_action_links' ], 2 );
		add_filter( 'admin_body_class', [ $this, 'add_body_classes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 25 );
	}

	/**
	 * Add action links to the settings on the plugins page.
	 *
	 * @param string[] $links Existing links.
	 *
	 * @return string[]
	 */
	public function add_action_links( $links ): array {
		// Defense against other plugins.
		if ( ! is_array( $links ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Expected an array.', 'screen-options' ), '1.0.0' );

			$links = [];
		}

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( sprintf( 'admin.php?page=%s', self::SCREEN_ID ) ) ),
			__( 'Settings', 'screen-options' )
		);

		return $links;
	}

	/**
	 * Add body classes for the admin area.
	 *
	 * @param string $classes Existing body classes.
	 */

	/**
	 * Add body classes for the admin area.
	 *
	 * @param string $classes Existing body classes.
	 */
	public function add_body_classes( $classes ): string {
		$current_screen = get_current_screen();

		if ( ! $current_screen ) {
			return $classes;
		}

		if ( strpos( $current_screen->id, self::SCREEN_ID ) !== false ) {
			$classes .= ' screen-options-admin-page';
		}

		return $classes;
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_scripts( string $hook ): void {

		if ( strpos( $hook, self::SCREEN_ID ) !== false ) {
			wp_localize_script(
				Assets::SETTINGS_SCRIPT_HANDLE,
				'ScreenOptionsSettings',
				Assets::get_localized_data()
			);
			wp_enqueue_script( Assets::SETTINGS_SCRIPT_HANDLE );
		}
	}
}
