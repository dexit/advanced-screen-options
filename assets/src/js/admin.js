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

	/**
	 * Handle role selection changes and filter post types in Post Assignment metabox
	 */
	const handleRoleSelectionChange = () => {
		// Check if we're on the screen options post type edit screen.
		if ( typeof ScreenOptionsSettings === 'undefined' || ! ScreenOptionsSettings.ajax_url || ! ScreenOptionsSettings.post_type ) {
			return;
		}

		const $roleCheckboxes = $( 'input[name="screen_options_assigned_roles[]"]' );
		const $postAssignmentMetabox = $( '#screen_options_meta_box' );

		if ( $roleCheckboxes.length === 0 || $postAssignmentMetabox.length === 0 ) {
			return;
		}

		/**
		 * Get selected roles
		 */
		const getSelectedRoles = () => {
			const selectedRoles = [];
			$roleCheckboxes.each( function() {
				if ( $( this ).is( ':checked' ) && ! $( this ).is( ':disabled' ) ) {
					selectedRoles.push( $( this ).val() );
				}
			} );
			return selectedRoles;
		};

		/**
		 * Update post type visibility based on accessible post types
		 *
		 * @param {Array}  accessiblePostTypes List of accessible post types
		 * @param {string} message             Optional message to display
		 *
		 * @return {void}
		 */
		const updatePostTypeVisibility = ( accessiblePostTypes, message = '' ) => {
			const $details = $postAssignmentMetabox.find( 'details' );

			if ( accessiblePostTypes.length === 0 ) {
				// Show message if provided.
				if ( message ) {
					$postAssignmentMetabox.find( '.no-roles-error' ).text( message );
				}

				// Hide all post types if no roles selected or no accessible post types.
				$details.hide();
				return;
			}

			// Clear any previous messages.
			$postAssignmentMetabox.find( '.no-roles-error' ).text( '' );

			// Show/hide each post type based on accessibility.
			$details.each( function() {
				const $detail = $( this );
				const $summary = $detail.find( 'summary' );
				const postTypeName = $summary.text().trim().toLowerCase();

				// Check if this post type is in the accessible list.
				const isAccessible = accessiblePostTypes.some( function( accessibleType ) {
					return accessibleType.toLowerCase() === postTypeName;
				} );

				if ( isAccessible ) {
					$detail.show();
				} else {
					$detail.hide();
					// Uncheck all checkboxes in hidden post types.
					$detail.find( 'input[type="checkbox"]' ).prop( 'checked', false );
				}
			} );
		};

		/**
		 * Fetch accessible post types via AJAX
		 */
		const fetchAccessiblePostTypes = () => {
			const selectedRoles = getSelectedRoles();

			if ( selectedRoles.length === 0 ) {
				// No roles selected, hide all post types.
				updatePostTypeVisibility( [] );
				return;
			}

			// Show loading indicator (optional).
			$postAssignmentMetabox.css( 'opacity', '0.5' );

			$.ajax( {
				url: ScreenOptionsSettings.ajax_url,
				type: 'POST',
				data: {
					action: 'screen_options_get_accessible_post_types',
					roles: selectedRoles,
					nonce: ScreenOptionsSettings.role_nonce,
				},
				success( response ) {
					if ( response.success && response.data.accessible_post_types ) {
						updatePostTypeVisibility( response.data.accessible_post_types, response.data.message || '' );
					} else {
						updatePostTypeVisibility( [], response.data.message || '' );
					}
				},
				error() {
					// On error, show all post types (fail-safe).
					updatePostTypeVisibility( [], '' );
				},
				complete() {
					// Remove loading indicator.
					$postAssignmentMetabox.css( 'opacity', '1' );
				},
			} );
		};

		// Bind change event to role checkboxes.
		$roleCheckboxes.on( 'change', function() {
			fetchAccessiblePostTypes();
		} );

		// Initialize on page load with currently selected roles.
		fetchAccessiblePostTypes();
	};

	$( document ).ready( () => {
		checkScreenOptionsLock();
		handleRoleSelectionChange();
	} );
}( jQuery ) );
