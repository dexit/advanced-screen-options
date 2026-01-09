/**
 * This will have admin related code only.
 *
 * @package
 * @since 1.0.0
 * @license GPL-2.0-or-later
 */

/* global jQuery */

( function( $ ) {
	'use strict';

	/**
	 * Display a notice banner about locked screen options
	 */
	const displayLockedNotice = () => {
		const $notice = $( '<div class="notice notice-warning inline screen-options-locked-notice" style="margin: 10px 0;"><p></p></div>' );
		// Set the lock message as text to avoid injecting HTML.
		$notice.find( 'p' ).text( ScreenOptionsSettings.lockMessage );

		// Insert the notice at the top of the metabox-prefs fieldset
		const $metaboxPrefs = $( 'fieldset.metabox-prefs:not(.view-mode)' );
		if ( $metaboxPrefs.length ) {
			$metaboxPrefs.prepend( $notice );
		}
	};

	/**
	 * Disable all screen option checkboxes
	 */
	const disableScreenOptionCheckboxes = () => {
		$( '.hide-column-tog' ).each( function() {
			$( this ).prop( 'disabled', true )
				.closest( 'label' )
				.css( 'opacity', '0.6' );
		} );
	};

	/**
	 * Check if screen options are locked and apply restrictions
	 */
	const checkScreenOptionsLock = () => {
		if ( typeof ScreenOptionsSettings === 'undefined' ) {
			return;
		}

		const isLocked = ScreenOptionsSettings.is_locked === 1 ||
			ScreenOptionsSettings.is_locked === true ||
			ScreenOptionsSettings.is_locked === '1';

		if ( isLocked ) {
			disableScreenOptionCheckboxes();
			displayLockedNotice();
		}
	};

	$( document ).ready( () => {
		checkScreenOptionsLock();
	} );
}( jQuery ) );
