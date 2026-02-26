<?php
/**
 * Settings class.
 * This class handles the settings page for the AdvancedScreenOptions plugin,
 *
 * @package AdvancedScreenOptions
 */

namespace AdvancedScreenOptions\Modules\Settings;

use AdvancedScreenOptions\Contracts\Interfaces\Registrable;

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
	public const MENU_SLUG = 'adv-screen-options';

	/**
	 * The screen ID for the settings page.
	 */
	public const SCREEN_ID = 'edit-' . self::MENU_SLUG;

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		add_filter( 'plugin_action_links_' . ADVANCED_SCREEN_OPTIONS_PLUGIN_BASENAME, [ $this, 'add_action_links' ], 2 );
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
			admin_url( 'edit.php?post_type=' . self::MENU_SLUG ),
			__( 'Settings', 'screen-options' )
		);

		return $links;
	}
}
