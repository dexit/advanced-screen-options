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
		const $notice = $( '<div class="notice notice-warning inline screen-options-locked-notice"><p></p></div>' );
		// Set the lock message as text to avoid injecting HTML.
		$notice.find( 'p' ).html( ScreenOptionsSettings.lockMessage );

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

		const isLocked = !! ScreenOptionsSettings.is_locked;

		if ( isLocked ) {
			disableScreenOptionCheckboxes();
			displayLockedNotice();
		}
	};

	// Simulating lock state per post type (In real app, fetch from DB)
	const lockStates = {
		post: false,
		page: false,
		product: false,
	};

	// --- 1. Global Lock Logic ---
	$( '#global-lock-check' ).change( function() {
		const isLocked = $( this ).is( ':checked' );
		const currentType = $( '#post-type-select' ).val();

		// Update simulated state
		lockStates[ currentType ] = isLocked;

		updateVisualLockState( currentType, isLocked );
	} );

	function updateVisualLockState( type, locked ) {
		const panel = $( '#postbox-' + type );

		if ( locked ) {
			panel.addClass( 'global-locked' );
			panel.find( '.locked-badge' ).fadeIn();
		} else {
			panel.removeClass( 'global-locked' );
			panel.find( '.locked-badge' ).fadeOut();
		}
	}

	// --- 2. Post Type Switcher ---
	$( '#post-type-select' ).change( function() {
		const selectedType = $( this ).val();

		// Switch Panels
		$( '.settings-panel' ).removeClass( 'active-panel' );
		$( '#panel-' + selectedType ).addClass( 'active-panel' );

		// Sync Lock Switch with simulated state
		const isLocked = lockStates[ selectedType ];
		$( '#global-lock-check' ).prop( 'checked', isLocked );

		// Ensure visual state matches
		// Reset all first
		$( '.postbox' ).removeClass( 'global-locked' );
		$( '.locked-badge' ).hide();
		// Apply to current
		updateVisualLockState( selectedType, isLocked );
	} );

	// --- 3. Role Selection Logic ---
	function validate() {
		if ( $( '.role-check:checked' ).length > 0 ) {
			$( '#settings-area' ).removeClass( 'disabled' );
			$( '#role-error' ).hide();
			$( '#save-btn' ).prop( 'disabled', false );
		} else {
			$( '#settings-area' ).addClass( 'disabled' );
			$( '#role-error' ).fadeIn();
			$( '#save-btn' ).prop( 'disabled', true );
		}
	}

	/**
	 * Update post type dropdown based on selected roles
	 */
	function updatePostTypeDropdown() {
		// Get all selected role values
		const selectedRoles = [];
		$( '.role-check:checked' ).each( function() {
			const roleValue = $( this ).val();
			if ( roleValue ) {
				selectedRoles.push( roleValue );
			}
		} );

		// Check if "All Users" is selected
		const allUsersSelected = $( '#role-all' ).is( ':checked' );
		if ( allUsersSelected ) {
			selectedRoles.push( 'all_users' );
		}

		// Get the currently selected post type to restore if possible
		const currentSelection = $( '#post-type-select' ).val();

		// Update each post type option
		$( '#post-type-select option' ).each( function() {
			const $option = $( this );
			const optionValue = $option.val();

			// Skip the placeholder option
			if ( ! optionValue ) {
				return;
			}

			// Get configured roles for this post type
			const configuredRoles = $option.data( 'configured-roles' );
			const configuredRolesArray = configuredRoles ? configuredRoles.toString().split( ',' ) : [];

			// Check if any selected role has already configured this post type
			let shouldDisable = false;
			for ( let i = 0; i < selectedRoles.length; i++ ) {
				if ( configuredRolesArray.includes( selectedRoles[ i ] ) ) {
					shouldDisable = true;
					break;
				}
			}

			// Enable/disable the option
			$option.prop( 'disabled', shouldDisable );

			// If currently selected option gets disabled, clear the selection
			if ( shouldDisable && currentSelection === optionValue ) {
				$( '#post-type-select' ).val( '' ).trigger( 'change' );
			}
		} );
	}

	$( '.role-row label' ).click( function( e ) {
		if ( e.target.type !== 'checkbox' ) {
			const chk = $( this ).find( 'input[type="checkbox"]' );
			// Don't toggle if checkbox is disabled
			if ( ! chk.prop( 'disabled' ) ) {
				chk.prop( 'checked', ! chk.prop( 'checked' ) ).change();
			}
		}
	} );

	$( '.role-check' ).change( function() {
		const row = $( this ).closest( '.role-row' );
		if ( $( this ).is( ':checked' ) ) {
			row.addClass( 'selected' );
		} else {
			row.removeClass( 'selected' );
		}
		validate();
		updatePostTypeDropdown();
	} );

	$( document ).ready( () => {
		checkScreenOptionsLock();
		validate();
		updatePostTypeDropdown();
	} );
}( jQuery ) );
