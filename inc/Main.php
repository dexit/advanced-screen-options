<?php
/**
 * The main plugin file.
 *
 * @package ScreenOptions
 */

declare( strict_types = 1 );

namespace ScreenOptions;

use ScreenOptions\Contracts\Traits\Singleton;

/**
 * Class - Main
 */
final class Main {
	use Singleton;

	/**
	 * Registrable classes are entrypoints that "hook" into WordPress.
	 * They should implement the Registrable interface.
	 *
	 * @var class-string<\ScreenOptions\Contracts\Interfaces\Registrable>[]
	 */
	private const REGISTRABLE_CLASSES = [

		// Column Assignment.
		Modules\Column_Assignment\Role_Based_Screen_Options::class,

		// Core.
		Modules\Core\Assets::class,

		// Meta Boxes.
		Modules\Meta\Screen_Initializer::class,
		Modules\Meta\Screen_Options_Meta::class,

		// Post Types.
		Modules\Post_Types\Default_Screen_Options::class,

		// Settings.
		Modules\Settings\Admin::class,

	];

	/**
	 * {@inheritDoc}
	 */
	public static function instance(): self {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup the plugin.
	 */
	private function setup(): void {
		// Load the plugin classes.
		$this->load();
	}

	/**
	 * Load the plugin classes.
	 */
	private function load(): void {
		foreach ( self::REGISTRABLE_CLASSES as $class_name ) {
			$instance = new $class_name();
			$instance->register_hooks();
		}

		// Do other generalizable stuff here.
	}
}
