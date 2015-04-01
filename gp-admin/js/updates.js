window.gp = window.gp || {};

(function( $, gp, pagenow ) {
	gp.updates = {};

	/**
	 * User nonce for ajax calls.
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	gp.updates.ajaxNonce = window._gpUpdatesSettings.ajax_nonce;

	/**
	 * Localized strings.
	 *
	 * @since 4.2.0
	 *
	 * @var object
	 */
	gp.updates.l10n = window._gpUpdatesSettings.l10n;

	/**
	 * Whether filesystem credentials need to be requested from the user.
	 *
	 * @since 4.2.0
	 *
	 * @var bool
	 */
	gp.updates.shouldRequestFilesystemCredentials = null;

	/**
	 * Filesystem credentials to be packaged along with the request.
	 *
	 * @since  4.2.0
	 *
	 * @var object
	 */
	gp.updates.filesystemCredentials = {
		ftp: {
			host: null,
			username: null,
			password: null,
			connectionType: null
		},
		ssh: {
			publicKey: null,
			privateKey: null
		}
	};

	/**
	 * Flag if we're waiting for an update to complete.
	 *
	 * @since 4.2.0
	 *
	 * @var bool
	 */
	gp.updates.updateLock = false;

	/**
	 * * Flag if we've done an update successfully.
	 *
	 * @since 4.2.0
	 *
	 * @var bool
	 */
	gp.updates.updateDoneSuccessfully = false;

	/**
	 * If the user tries to update a plugin while an update is
	 * already happening, it can be placed in this queue to perform later.
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	gp.updates.updateQueue = [];

	/**
	 * Store a jQuery reference to return focus to when exiting the request credentials modal.
	 *
	 * @since 4.2.0
	 *
	 * @var jQuery object
	 */
	gp.updates.$elToReturnFocusToFromCredentialsModal = null;

	/**
	 * Decrement update counts throughout the various menus.
	 *
	 * @since 3.9.0
	 *
	 * @param {string} updateType
	 */
	gp.updates.decrementCount = function( upgradeType ) {
		var count,
		    pluginCount,
		    $adminBarUpdateCount = $( '#gp-admin-bar-updates .ab-label' ),
		    $dashboardNavMenuUpdateCount = $( 'a[href="update-core.php"] .update-plugins' ),
		    $pluginsMenuItem = $( '#menu-plugins' );


		count = $adminBarUpdateCount.text();
		count = parseInt( count, 10 ) - 1;
		if ( count < 0 || isNaN( count ) ) {
			return;
		}
		$( '#gp-admin-bar-updates .ab-item' ).removeAttr( 'title' );
		$adminBarUpdateCount.text( count );


		$dashboardNavMenuUpdateCount.each( function( index, elem ) {
			elem.className = elem.className.replace( /count-\d+/, 'count-' + count );
		} );
		$dashboardNavMenuUpdateCount.removeAttr( 'title' );
		$dashboardNavMenuUpdateCount.find( '.update-count' ).text( count );

		if ( 'plugin' === upgradeType ) {
			pluginCount = $pluginsMenuItem.find( '.plugin-count' ).eq(0).text();
			pluginCount = parseInt( pluginCount, 10 ) - 1;
			if ( pluginCount < 0 || isNaN( pluginCount ) ) {
				return;
			}
			$pluginsMenuItem.find( '.plugin-count' ).text( pluginCount );
			$pluginsMenuItem.find( '.update-plugins' ).each( function( index, elem ) {
				elem.className = elem.className.replace( /count-\d+/, 'count-' + pluginCount );
			} );

			if (pluginCount > 0 ) {
				$( '.subsubsub .upgrade .count' ).text( '(' + pluginCount + ')' );
			} else {
				$( '.subsubsub .upgrade' ).remove();
			}
		}
	};

	/**
	 * Send an Ajax request to the server to update a plugin.
	 *
	 * @since 4.2.0
	 *
	 * @param {string} plugin
	 * @param {string} slug
	 */
	gp.updates.updatePlugin = function( plugin, slug ) {
		var $message;
		if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
			$message = $( '[data-slug="' + slug + '"]' ).next().find( '.update-message' );
		} else if ( 'plugin-install' === pagenow ) {
			$message = $( '.plugin-card-' + slug ).find( '.update-now' );
		}

		$message.addClass( 'updating-message' );
		if ( $message.html() !== gp.updates.l10n.updating ){
			$message.data( 'originaltext', $message.html() );
		}

		$message.text( gp.updates.l10n.updating );
		gp.a11y.speak( gp.updates.l10n.updatingMsg );

		if ( gp.updates.updateLock ) {
			gp.updates.updateQueue.push( {
				type: 'update-plugin',
				data: {
					plugin: plugin,
					slug: slug
				}
			} );
			return;
		}

		gp.updates.updateLock = true;

		var data = {
			_ajax_nonce:     gp.updates.ajaxNonce,
			plugin:          plugin,
			slug:            slug,
			username:        gp.updates.filesystemCredentials.ftp.username,
			password:        gp.updates.filesystemCredentials.ftp.password,
			hostname:        gp.updates.filesystemCredentials.ftp.hostname,
			connection_type: gp.updates.filesystemCredentials.ftp.connectionType,
			public_key:      gp.updates.filesystemCredentials.ssh.publicKey,
			private_key:     gp.updates.filesystemCredentials.ssh.privateKey
		};

		gp.ajax.post( 'update-plugin', data )
			.done( gp.updates.updateSuccess )
			.fail( gp.updates.updateError );
	};

	/**
	 * On a successful plugin update, update the UI with the result.
	 *
	 * @since 4.2.0
	 *
	 * @param {object} response
	 */
	gp.updates.updateSuccess = function( response ) {
		var $updateMessage;
		if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
			var $pluginRow = $( '[data-slug="' + response.slug + '"]' ).first();
			$updateMessage = $pluginRow.next().find( '.update-message' );
			$pluginRow.addClass( 'updated' ).removeClass( 'update' );

			// Update the version number in the row.
			var newText = $pluginRow.find('.plugin-version-author-uri').html().replace( response.oldVersion, response.newVersion );
			$pluginRow.find('.plugin-version-author-uri').html( newText );
		} else if ( 'plugin-install' === pagenow ) {
			$updateMessage = $( '.plugin-card-' + response.slug ).find( '.update-now' );
			$updateMessage.addClass( 'button-disabled' );
		}

		$updateMessage.removeClass( 'updating-message' ).addClass( 'updated-message' );
		$updateMessage.text( gp.updates.l10n.updated );
		gp.a11y.speak( gp.updates.l10n.updatedMsg );

		gp.updates.decrementCount( 'plugin' );

		gp.updates.updateDoneSuccessfully = true;

		/*
		 * The lock can be released since the update was successful,
		 * and any other updates can commence.
		 */
		gp.updates.updateLock = false;
		gp.updates.queueChecker();
	};

	/**
	 * On a plugin update error, update the UI appropriately.
	 *
	 * @since 4.2.0
	 *
	 * @param {object} response
	 */
	gp.updates.updateError = function( response ) {
		var $message;
		gp.updates.updateDoneSuccessfully = false;
		if ( response.errorCode && response.errorCode == 'unable_to_connect_to_filesystem' ) {
			gp.updates.credentialError( response, 'update-plugin' );
			return;
		}
		if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
			$message = $( '[data-slug="' + response.slug + '"]' ).next().find( '.update-message' );
		} else if ( 'plugin-install' === pagenow ) {
			$message = $( '.plugin-card-' + response.slug ).find( '.update-now' );
		}
		$message.removeClass( 'updating-message' );
		$message.text( gp.updates.l10n.updateFailed );
		gp.a11y.speak( gp.updates.l10n.updateFailed );

	};

	/**
	 * Show an error message in the request for credentials form.
	 *
	 * @param {string} message
	 * @since 4.2.0
	 */
	gp.updates.showErrorInCredentialsForm = function( message ) {
		var $modal = $( '.notification-dialog' );

		// Remove any existing error.
		$modal.find( '.error' ).remove();

		$modal.find( 'h3' ).after( '<div class="error">' + message + '</div>' );
	};

	/**
	 * Events that need to happen when there is a credential error
	 *
	 * @since 4.2.0
	 */
	gp.updates.credentialError = function( response, type ) {
		gp.updates.updateQueue.push( {
			'type': type,
			'data': {
				// Not cool that we're depending on response for this data.
				// This would feel more whole in a view all tied together.
				plugin: response.plugin,
				slug: response.slug
			}
		} );
		gp.updates.showErrorInCredentialsForm( response.error );
		gp.updates.requestFilesystemCredentials();
	};

	/**
	 * If an update job has been placed in the queue, queueChecker pulls it out and runs it.
	 *
	 * @since 4.2.0
	 */
	gp.updates.queueChecker = function() {
		if ( gp.updates.updateLock || gp.updates.updateQueue.length <= 0 ) {
			return;
		}

		var job = gp.updates.updateQueue.shift();

		gp.updates.updatePlugin( job.data.plugin, job.data.slug );
	};


	/**
	 * Request the users filesystem credentials if we don't have them already.
	 *
	 * @since 4.2.0
	 */
	gp.updates.requestFilesystemCredentials = function( event ) {
		if ( gp.updates.updateDoneSuccessfully === false ) {
			/*
			 * For the plugin install screen, return the focus to the install button
			 * after exiting the credentials request modal.
			 */
			if ( 'plugin-install' === pagenow && event ) {
				gp.updates.$elToReturnFocusToFromCredentialsModal = $( event.target );
			}

			gp.updates.updateLock = true;

			gp.updates.requestForCredentialsModalOpen();
		}
	};

	/**
	 * Keydown handler for the request for credentials modal.
	 *
	 * Close the modal when the escape key is pressed.
	 * Constrain keyboard navigation to inside the modal.
	 *
	 * @since 4.2.0
	 */
	gp.updates.keydown = function( event ) {
		if ( 27 === event.keyCode ) {
			gp.updates.requestForCredentialsModalCancel();
		} else if ( 9 === event.keyCode ) {
			// #upgrade button must always be the last focusable element in the dialog.
			if ( event.target.id === 'upgrade' && ! event.shiftKey ) {
				$( '#hostname' ).focus();
				event.preventDefault();
			} else if ( event.target.id === 'hostname' && event.shiftKey ) {
				$( '#upgrade' ).focus();
				event.preventDefault();
			}
		}
	};

	/**
	 * Open the request for credentials modal.
	 *
	 * @since 4.2.0
	 */
	gp.updates.requestForCredentialsModalOpen = function() {
		var $modal = $( '#request-filesystem-credentials-dialog' );
		$( 'body' ).addClass( 'modal-open' );
		$modal.show();

		$modal.find( '#hostname' ).focus();
		$modal.keydown( gp.updates.keydown );
	};

	/**
	 * Close the request for credentials modal.
	 *
	 * @since 4.2.0
	 */
	gp.updates.requestForCredentialsModalClose = function() {
		$( '#request-filesystem-credentials-dialog' ).hide();
		$( 'body' ).removeClass( 'modal-open' );
		gp.updates.$elToReturnFocusToFromCredentialsModal.focus();
	};

	/**
	 * The steps that need to happen when the modal is canceled out
	 *
	 * @since 4.2.0
	 */
	gp.updates.requestForCredentialsModalCancel = function() {
		// no updateLock and no updateQueue means we already have cleared things up
		var slug, $message;

		if( gp.updates.updateLock === false && gp.updates.updateQueue.length === 0 ){
			return;
		}

		slug = gp.updates.updateQueue[0].data.slug,

		// remove the lock, and clear the queue
		gp.updates.updateLock = false;
		gp.updates.updateQueue = [];

		gp.updates.requestForCredentialsModalClose();
		if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
			$message = $( '[data-slug="' + slug + '"]' ).next().find( '.update-message' );
		} else if ( 'plugin-install' === pagenow ) {
			$message = $( '.plugin-card-' + slug ).find( '.update-now' );
		}

		$message.removeClass( 'updating-message' );
		$message.html( $message.data( 'originaltext' ) );
		gp.a11y.speak( gp.updates.l10n.updateCancel );
	};

	$( document ).ready( function() {
		/*
		 * Check whether a user needs to submit filesystem credentials based on whether
		 * the form was output on the page server-side.
		 *
		 * @see {gp_print_request_filesystem_credentials_modal() in PHP}
		 */
		gp.updates.shouldRequestFilesystemCredentials = ( $( '#request-filesystem-credentials-dialog' ).length <= 0 ) ? false : true;

		// File system credentials form submit noop-er / handler.
		$( '#request-filesystem-credentials-dialog form' ).on( 'submit', function() {
			// Persist the credentials input by the user for the duration of the page load.
			gp.updates.filesystemCredentials.ftp.hostname = $('#hostname').val();
			gp.updates.filesystemCredentials.ftp.username = $('#username').val();
			gp.updates.filesystemCredentials.ftp.password = $('#password').val();
			gp.updates.filesystemCredentials.ftp.connectionType = $('input[name="connection_type"]:checked').val();
			gp.updates.filesystemCredentials.ssh.publicKey = $('#public_key').val();
			gp.updates.filesystemCredentials.ssh.privateKey = $('#private_key').val();

			gp.updates.requestForCredentialsModalClose();

			// Unlock and invoke the queue.
			gp.updates.updateLock = false;
			gp.updates.queueChecker();

			return false;
		});

		// Close the request credentials modal when
		$( '#request-filesystem-credentials-dialog [data-js-action="close"], .notification-dialog-background' ).on( 'click', function() {
			gp.updates.requestForCredentialsModalCancel();
		});

		// Click handler for plugin updates in List Table view.
		$( '.plugin-update-tr' ).on( 'click', '.update-link', function( e ) {
			e.preventDefault();
			if ( gp.updates.shouldRequestFilesystemCredentials && ! gp.updates.updateLock ) {
				gp.updates.requestFilesystemCredentials( e );
			}
			var updateRow = $( e.target ).parents( '.plugin-update-tr' );
			// Return the user to the input box of the plugin's table row after closing the modal.
			gp.updates.$elToReturnFocusToFromCredentialsModal = $( '#' + updateRow.data( 'slug' ) ).find( '.check-column input' );
			gp.updates.updatePlugin( updateRow.data( 'plugin' ), updateRow.data( 'slug' ) );
		} );

		$( '#bulk-action-form' ).on( 'submit', function( e ) {
			var $checkbox, plugin, slug;

			if ( $( '#bulk-action-selector-top' ).val() == 'update-selected' ) {
				e.preventDefault();

				$( 'input[name="checked[]"]:checked' ).each( function( index, elem ) {
					$checkbox = $( elem );
					plugin = $checkbox.val();
					slug = $checkbox.parents( 'tr' ).prop( 'id' );

					gp.updates.updatePlugin( plugin, slug );

					$checkbox.attr( 'checked', false );
				} );
			}
		} );

		$( '.plugin-card' ).on( 'click', '.update-now', function( e ) {
			e.preventDefault();
			var $button = $( e.target );

			if ( gp.updates.shouldRequestFilesystemCredentials && ! gp.updates.updateLock ) {
				gp.updates.requestFilesystemCredentials( e );
			}

			gp.updates.updatePlugin( $button.data( 'plugin' ), $button.data( 'slug' ) );
		} );

	} );

	$( window ).on( 'message', function( e ) {
		var event = e.originalEvent,
			message,
			loc = document.location,
			expectedOrigin = loc.protocol + '//' + loc.hostname;

		if ( event.origin !== expectedOrigin ) {
			return;
		}

		message = $.parseJSON( event.data );

		if ( typeof message.action === 'undefined' || message.action !== 'decrementUpdateCount' ) {
			return;
		}

		gp.updates.decrementCount( message.upgradeType );

	} );

})( jQuery, window.gp, window.pagenow, window.ajaxurl );
