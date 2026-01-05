<?php
/**
 * Handles REST API behavior.
 *
 * @package ScreenOptions\Modules\Rest
 */

declare( strict_types = 1 );

namespace ScreenOptions\Modules\Core;

use ScreenOptions\Contracts\Interfaces\Registrable;

/**
 * Class REST
 */
final class Rest implements Registrable {

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks(): void {
		add_filter( 'rest_allowed_cors_headers', [ $this, 'allowed_cors_headers' ] );
	}

	/**
	 * Adds plugin CORS headers to those allowed in REST responses.
	 *
	 * @param array<int, string> $headers Existing headers.
	 *
	 * @return array<int, string> Modified headers.
	 */
	public function allowed_cors_headers( $headers ): array {
		// Skip if the headers are already present.
		if ( in_array( 'X-ScreenOptions-Token', $headers, true ) ) {
			return $headers;
		}

		return array_merge(
			$headers,
			[
				'X-ScreenOptions-Token',
			]
		);
	}
}
