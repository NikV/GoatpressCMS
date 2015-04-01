/* global deleteUserSetting, setUserSetting, switchEditors, tinymce, tinyMCEPreInit */
/**
 * Distraction-Free Writing
 * (gp-fullscreen)
 *
 * Access the API globally using the window.gp.editor.fullscreen variable.
 */
( function( $, window ) {
	var api, ps, s, toggleUI, uiTimer, PubSub,
		uiScrollTop = 0,
		transitionend = 'transitionend webkitTransitionEnd',
		$body = $( document.body ),
		$document = $( document );

	/**
	 * PubSub
	 *
	 * A lightweight publish/subscribe implementation.
	 *
	 * @access private
	 */
	PubSub = function() {
		this.topics = {};

		this.subscribe = function( topic, callback ) {
			if ( ! this.topics[ topic ] )
				this.topics[ topic ] = [];

			this.topics[ topic ].push( callback );
			return callback;
		};

		this.unsubscribe = function( topic, callback ) {
			var i, l,
				topics = this.topics[ topic ];

			if ( ! topics )
				return callback || [];

			// Clear matching callbacks
			if ( callback ) {
				for ( i = 0, l = topics.length; i < l; i++ ) {
					if ( callback == topics[i] )
						topics.splice( i, 1 );
				}
				return callback;

			// Clear all callbacks
			} else {
				this.topics[ topic ] = [];
				return topics;
			}
		};

		this.publish = function( topic, args ) {
			var i, l, broken,
				topics = this.topics[ topic ];

			if ( ! topics )
				return;

			args = args || [];

			for ( i = 0, l = topics.length; i < l; i++ ) {
				broken = ( topics[i].apply( null, args ) === false || broken );
			}
			return ! broken;
		};
	};

	// Initialize the fullscreen/api object
	api = {};

	// Create the PubSub (publish/subscribe) interface.
	ps = api.pubsub = new PubSub();

	s = api.settings = { // Settings
		visible: false,
		mode: 'tinymce',
		id: '',
		title_id: '',
		timer: 0,
		toolbar_shown: false
	};

	function _hideUI() {
		$body.removeClass('gp-dfw-show-ui');
	}

	/**
	 * toggleUI
	 *
	 * Toggle the CSS class to show/hide the toolbar, borders and statusbar.
	 */
	toggleUI = api.toggleUI = function( show ) {
		clearTimeout( uiTimer );

		if ( ! $body.hasClass('gp-dfw-show-ui') || show === 'show' ) {
			$body.addClass('gp-dfw-show-ui');
		} else if ( show !== 'autohide' ) {
			$body.removeClass('gp-dfw-show-ui');
		}

		if ( show === 'autohide' ) {
			uiTimer = setTimeout( _hideUI, 2000 );
		}
	};

	function resetCssPosition( add ) {
		s.$dfwWrap.parents().each( function( i, parent ) {
			var cssPosition, $parent = $(parent);

			if ( add ) {
				if ( parent.style.position ) {
					$parent.data( 'gp-dfw-css-position', parent.style.position );
				}

				$parent.css( 'position', 'static' );
			} else {
				cssPosition = $parent.data( 'gp-dfw-css-position' );
				cssPosition = cssPosition || '';
				$parent.css( 'position', cssPosition );
			}

			if ( parent.nodeName === 'BODY' ) {
				return false;
			}
		});
	}

	/**
	 * on()
	 *
	 * Turns fullscreen on.
	 *
	 * @param string mode Optional. Switch to the given mode before opening.
	 */
	api.on = function() {
		var id, $dfwWrap, titleId;

		if ( s.visible ) {
			return;
		}

		if ( ! s.$fullscreenFader ) {
			api.ui.init();
		}

		// Settings can be added or changed by defining "gp_fullscreen_settings" JS object.
		if ( typeof window.gp_fullscreen_settings === 'object' )
			$.extend( s, window.gp_fullscreen_settings );

		id = s.id || window.gpActiveEditor;

		if ( ! id ) {
			if ( s.hasTinymce ) {
				id = tinymce.activeEditor.id;
			} else {
				return;
			}
		}

		s.id = id;
		$dfwWrap = s.$dfwWrap = $( '#gp-' + id + '-wrap' );

		if ( ! $dfwWrap.length ) {
			return;
		}

		s.$dfwTextarea = $( '#' + id );
		s.$editorContainer = $dfwWrap.find( '.gp-editor-container' );
		uiScrollTop = $document.scrollTop();

		if ( s.hasTinymce ) {
			s.editor = tinymce.get( id );
		}

		if ( s.editor && ! s.editor.isHidden() ) {
			s.origHeight = $( '#' + id + '_ifr' ).height();
			s.mode = 'tinymce';
		} else {
			s.origHeight = s.$dfwTextarea.height();
			s.mode = 'html';
		}

		// Try to find title field
		if ( typeof window.adminpage !== 'undefined' &&
			( window.adminpage === 'post-php' || window.adminpage === 'post-new-php' ) ) {

			titleId = 'title';
		} else {
			titleId = id + '-title';
		}

		s.$dfwTitle = $( '#' + titleId );

		if ( ! s.$dfwTitle.length ) {
			s.$dfwTitle = null;
		}

		api.ui.fade( 'show', 'showing', 'shown' );
	};

	/**
	 * off()
	 *
	 * Turns fullscreen off.
	 */
	api.off = function() {
		if ( ! s.visible )
			return;

		api.ui.fade( 'hide', 'hiding', 'hidden' );
	};

	/**
	 * switchmode()
	 *
	 * @return string - The current mode.
	 *
	 * @param string to - The fullscreen mode to switch to.
	 * @event switchMode
	 * @eventparam string to   - The new mode.
	 * @eventparam string from - The old mode.
	 */
	api.switchmode = function( to ) {
		var from = s.mode;

		if ( ! to || ! s.visible || ! s.hasTinymce || typeof switchEditors === 'undefined' ) {
			return from;
		}

		// Don't switch if the mode is the same.
		if ( from == to )
			return from;

		if ( to === 'tinymce' && ! s.editor ) {
			s.editor = tinymce.get( s.id );

			if ( ! s.editor &&  typeof tinyMCEPreInit !== 'undefined' &&
				tinyMCEPreInit.mceInit && tinyMCEPreInit.mceInit[ s.id ] ) {

				// If the TinyMCE instance hasn't been created, set the "gp_fulscreen" flag on creating it
				tinyMCEPreInit.mceInit[ s.id ].gp_fullscreen = true;
			}
		}

		s.mode = to;
		switchEditors.go( s.id, to );
		api.refreshButtons( true );

		if ( to === 'html' ) {
			setTimeout( api.resizeTextarea, 200 );
		}

		return to;
	};

	/**
	 * General
	 */

	api.save = function() {
		var $hidden = $('#hiddenaction'),
			oldVal = $hidden.val(),
			$spinner = $('#gp-fullscreen-save .spinner'),
			$saveMessage = $('#gp-fullscreen-save .gp-fullscreen-saved-message'),
			$errorMessage = $('#gp-fullscreen-save .gp-fullscreen-error-message');

		$spinner.show();
		$errorMessage.hide();
		$saveMessage.hide();
		$hidden.val('gp-fullscreen-save-post');

		if ( s.editor && ! s.editor.isHidden() ) {
			s.editor.save();
		}

		$.ajax({
			url: window.ajaxurl,
			type: 'post',
			data: $('form#post').serialize(),
			dataType: 'json'
		}).done( function( response ) {
			$spinner.hide();

			if ( response && response.success ) {
				$saveMessage.show();

				setTimeout( function() {
					$saveMessage.fadeOut(300);
				}, 3000 );

				if ( response.data && response.data.last_edited ) {
					$('#gp-fullscreen-save input').attr( 'title',  response.data.last_edited );
				}
			} else {
				$errorMessage.show();
			}
		}).fail( function() {
			$spinner.hide();
			$errorMessage.show();
		});

		$hidden.val( oldVal );
	};

	api.dfwWidth = function( pixels, total ) {
		var width;

		if ( pixels && pixels.toString().indexOf('%') !== -1 ) {
			s.$editorContainer.css( 'width', pixels );
			s.$statusbar.css( 'width', pixels );

			if ( s.$dfwTitle ) {
				s.$dfwTitle.css( 'width', pixels );
			}
			return;
		}

		if ( ! pixels ) {
			// Reset to theme width
			width = $('#gp-fullscreen-body').data('theme-width') || 800;
			s.$editorContainer.width( width );
			s.$statusbar.width( width );

			if ( s.$dfwTitle ) {
				s.$dfwTitle.width( width - 16 );
			}

			deleteUserSetting('dfw_width');
			return;
		}

		if ( total ) {
			width = pixels;
		} else {
			width = s.$editorContainer.width();
			width += pixels;
		}

		if ( width < 200 || width > 1200 ) {
			// sanity check
			return;
		}

		s.$editorContainer.width( width );
		s.$statusbar.width( width );

		if ( s.$dfwTitle ) {
			s.$dfwTitle.width( width - 16 );
		}

		setUserSetting( 'dfw_width', width );
	};

	// This event occurs before the overlay blocks the UI.
	ps.subscribe( 'show', function() {
		var title = $('#last-edit').text();

		if ( title ) {
			$('#gp-fullscreen-save input').attr( 'title', title );
		}
	});

	// This event occurs while the overlay blocks the UI.
	ps.subscribe( 'showing', function() {
		$body.addClass( 'gp-fullscreen-active' );
		s.$dfwWrap.addClass( 'gp-fullscreen-wrap' );

		if ( s.$dfwTitle ) {
			s.$dfwTitle.after( '<span id="gp-fullscreen-title-placeholder">' );
			s.$dfwWrap.prepend( s.$dfwTitle.addClass('gp-fullscreen-title') );
		}

		api.refreshButtons();
		resetCssPosition( true );
		$('#gpadminbar').hide();

		// Show the UI for 2 sec. when opening
		toggleUI('autohide');

		api.bind_resize();

		if ( s.editor ) {
			s.editor.execCommand( 'gpFullScreenOn' );
		}

		if ( 'ontouchstart' in window ) {
			api.dfwWidth( '90%' );
		} else {
			api.dfwWidth( $( '#gp-fullscreen-body' ).data('dfw-width') || 800, true );
		}

		// scroll to top so the user is not disoriented
		scrollTo(0, 0);
	});

	// This event occurs after the overlay unblocks the UI
	ps.subscribe( 'shown', function() {
		s.visible = true;

		if ( s.editor && ! s.editor.isHidden() ) {
			s.editor.execCommand( 'gpAutoResize' );
		} else {
			api.resizeTextarea( 'force' );
		}
	});

	ps.subscribe( 'hide', function() { // This event occurs before the overlay blocks DFW.
		$document.unbind( '.fullscreen' );
		s.$dfwTextarea.unbind('.gp-dfw-resize');
	});

	ps.subscribe( 'hiding', function() { // This event occurs while the overlay blocks the DFW UI.
		$body.removeClass( 'gp-fullscreen-active' );

		if ( s.$dfwTitle ) {
			$( '#gp-fullscreen-title-placeholder' ).before( s.$dfwTitle.removeClass('gp-fullscreen-title').css( 'width', '' ) ).remove();
		}

		s.$dfwWrap.removeClass( 'gp-fullscreen-wrap' );
		s.$editorContainer.css( 'width', '' );
		s.$dfwTextarea.add( '#' + s.id + '_ifr' ).height( s.origHeight );

		if ( s.editor ) {
			s.editor.execCommand( 'gpFullScreenOff' );
		}

		resetCssPosition( false );

		window.scrollTo( 0, uiScrollTop );
		$('#gpadminbar').show();
	});

	// This event occurs after DFW is removed.
	ps.subscribe( 'hidden', function() {
		s.visible = false;
	});

	api.refreshButtons = function( fade ) {
		if ( s.mode === 'html' ) {
			$('#gp-fullscreen-mode-bar').removeClass('gp-tmce-mode').addClass('gp-html-mode')
				.find('a').removeClass( 'active' ).filter('.gp-fullscreen-mode-html').addClass( 'active' );

			if ( fade ) {
				$('#gp-fullscreen-button-bar').fadeOut( 150, function(){
					$(this).addClass('gp-html-mode').fadeIn( 150 );
				});
			} else {
				$('#gp-fullscreen-button-bar').addClass('gp-html-mode');
			}
		} else if ( s.mode === 'tinymce' ) {
			$('#gp-fullscreen-mode-bar').removeClass('gp-html-mode').addClass('gp-tmce-mode')
				.find('a').removeClass( 'active' ).filter('.gp-fullscreen-mode-tinymce').addClass( 'active' );

			if ( fade ) {
				$('#gp-fullscreen-button-bar').fadeOut( 150, function(){
					$(this).removeClass('gp-html-mode').fadeIn( 150 );
				});
			} else {
				$('#gp-fullscreen-button-bar').removeClass('gp-html-mode');
			}
		}
	};

	/**
	 * UI Elements
	 *
	 * Used for transitioning between states.
	 */
	api.ui = {
		init: function() {
			var toolbar;

			s.toolbar = toolbar = $('#fullscreen-topbar');
			s.$fullscreenFader = $('#fullscreen-fader');
			s.$statusbar = $('#gp-fullscreen-status');
			s.hasTinymce = typeof tinymce !== 'undefined';

			if ( ! s.hasTinymce )
				$('#gp-fullscreen-mode-bar').hide();

			$document.keyup( function(e) {
				var c = e.keyCode || e.charCode, modKey;

				if ( ! s.visible ) {
					return;
				}

				if ( navigator.platform && navigator.platform.indexOf('Mac') !== -1 ) {
					modKey = e.ctrlKey; // Ctrl key for Mac
				} else {
					modKey = e.altKey; // Alt key for Win & Linux
				}

				if ( modKey && ( 61 === c || 107 === c || 187 === c ) ) { // +
					api.dfwWidth( 25 );
					e.preventDefault();
				}

				if ( modKey && ( 45 === c || 109 === c || 189 === c ) ) { // -
					api.dfwWidth( -25 );
					e.preventDefault();
				}

				if ( modKey && 48 === c ) { // 0
					api.dfwWidth( 0 );
					e.preventDefault();
				}
			});

			$( window ).on( 'keydown.gp-fullscreen', function( event ) {
				// Turn fullscreen off when Esc is pressed.
				if ( 27 === event.keyCode && s.visible ) {
					api.off();
					event.stopImmediatePropagation();
				}
			});

			if ( 'ontouchstart' in window ) {
				$body.addClass('gp-dfw-touch');
			}

			toolbar.on( 'mouseenter', function() {
				toggleUI('show');
			}).on( 'mouseleave', function() {
				toggleUI('autohide');
			});

			// Bind buttons
			$('#gp-fullscreen-buttons').on( 'click.gp-fullscreen', 'button', function( event ) {
				var command = event.currentTarget.id ? event.currentTarget.id.substr(6) : null;

				if ( s.editor && 'tinymce' === s.mode ) {
					switch( command ) {
						case 'bold':
							s.editor.execCommand('Bold');
							break;
						case 'italic':
							s.editor.execCommand('Italic');
							break;
						case 'bullist':
							s.editor.execCommand('InsertUnorderedList');
							break;
						case 'numlist':
							s.editor.execCommand('InsertOrderedList');
							break;
						case 'link':
							s.editor.execCommand('gp_Link');
							break;
						case 'unlink':
							s.editor.execCommand('unlink');
							break;
						case 'help':
							s.editor.execCommand('gp_Help');
							break;
						case 'blockquote':
							s.editor.execCommand('mceBlockQuote');
							break;
					}
				} else if ( command === 'link' && window.gpLink ) {
					window.gpLink.open();
				}

				if ( command === 'gp-media-library' && typeof gp !== 'undefined' && gp.media && gp.media.editor ) {
					gp.media.editor.open( s.id );
				}
			});
		},

		fade: function( before, during, after ) {
			if ( ! s.$fullscreenFader ) {
				api.ui.init();
			}

			// If any callback bound to before returns false, bail.
			if ( before && ! ps.publish( before ) ) {
				return;
			}

			api.fade.In( s.$fullscreenFader, 200, function() {
				if ( during ) {
					ps.publish( during );
				}

				api.fade.Out( s.$fullscreenFader, 200, function() {
					if ( after ) {
						ps.publish( after );
					}
				});
			});
		}
	};

	api.fade = {
		// Sensitivity to allow browsers to render the blank element before animating.
		sensitivity: 100,

		In: function( element, speed, callback, stop ) {

			callback = callback || $.noop;
			speed = speed || 400;
			stop = stop || false;

			if ( api.fade.transitions ) {
				if ( element.is(':visible') ) {
					element.addClass( 'fade-trigger' );
					return element;
				}

				element.show();
				element.first().one( transitionend, function() {
					callback();
				});

				setTimeout( function() { element.addClass( 'fade-trigger' ); }, this.sensitivity );
			} else {
				if ( stop ) {
					element.stop();
				}

				element.css( 'opacity', 1 );
				element.first().fadeIn( speed, callback );

				if ( element.length > 1 ) {
					element.not(':first').fadeIn( speed );
				}
			}

			return element;
		},

		Out: function( element, speed, callback, stop ) {

			callback = callback || $.noop;
			speed = speed || 400;
			stop = stop || false;

			if ( ! element.is(':visible') ) {
				return element;
			}

			if ( api.fade.transitions ) {
				element.first().one( transitionend, function() {
					if ( element.hasClass('fade-trigger') ) {
						return;
					}

					element.hide();
					callback();
				});
				setTimeout( function() { element.removeClass( 'fade-trigger' ); }, this.sensitivity );
			} else {
				if ( stop ) {
					element.stop();
				}

				element.first().fadeOut( speed, callback );

				if ( element.length > 1 ) {
					element.not(':first').fadeOut( speed );
				}
			}

			return element;
		},

		// Check if the browser supports CSS 3.0 transitions
		transitions: ( function() {
			var style = document.documentElement.style;

			return ( typeof style.WebkitTransition === 'string' ||
				typeof style.MozTransition === 'string' ||
				typeof style.OTransition === 'string' ||
				typeof style.transition === 'string' );
		})()
	};

	/**
	 * Resize API
	 *
	 * Automatically updates textarea height.
	 */
	api.bind_resize = function() {
		s.$dfwTextarea.on( 'keydown.gp-dfw-resize click.gp-dfw-resize paste.gp-dfw-resize', function() {
			api.resizeTextarea();
		});
	};

	api.resizeTextarea = function() {
		var node = s.$dfwTextarea[0];

		if ( node.scrollHeight > node.clientHeight ) {
			node.style.height = node.scrollHeight + 50 + 'px';
		}
	};

	// Export
	window.gp = window.gp || {};
	window.gp.editor = window.gp.editor || {};
	window.gp.editor.fullscreen = api;

})( jQuery, window );
