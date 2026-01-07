<?php
/**
 * Registers the plugin's settings and options
 *
 * @package ScreenOptions\Modules\Settings
 */

declare(strict_types = 1);

namespace ScreenOptions\Modules\Settings;

use ScreenOptions\Contracts\Interfaces\Registrable;

/**
 * Class - Settings
 */
final class Settings implements Registrable {
	/**
	 * The setting prefix.
	 *
	 * @todo need to replace globally with single source of truth.
	 *
	 * @var string
	 */
	private const SETTING_PREFIX = 'screen-options_';

	/**
	 * The setting group.
	 */
	public const SETTING_GROUP = self::SETTING_PREFIX . 'settings';

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings(): void {}
}
