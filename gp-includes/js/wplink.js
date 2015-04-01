/* global ajaxurl, tinymce, gpLinkL10n, setUserSetting, gpActiveEditor */
var gpLink;

( function( $ ) {
	var editor, searchTimer, River, Query, correctedURL,
		inputs = {},
		rivers = {},
		isTouch = ( 'ontouchend' in document );

	function getLink() {
		return editor.dom.getParent( editor.selection.getNode(), 'a' );
	}

	gpLink = {
		timeToTriggerRiver: 150,
		minRiverAJAXDuration: 200,
		riverBottomThreshold: 5,
		keySensitivity: 100,
		lastSearch: '',
		textarea: '',

		init: function() {
			inputs.wrap = $('#gp-link-wrap');
			inputs.dialog = $( '#gp-link' );
			inputs.backdrop = $( '#gp-link-backdrop' );
			inputs.submit = $( '#gp-link-submit' );
			inputs.close = $( '#gp-link-close' );

			// Input
			inputs.text = $( '#gp-link-text' );
			inputs.url = $( '#gp-link-url' );
			inputs.nonce = $( '#_ajax_linking_nonce' );
			inputs.openInNewTab = $( '#gp-link-target' );
			inputs.search = $( '#gp-link-search' );

			// Build Rivers
			rivers.search = new River( $( '#search-results' ) );
			rivers.recent = new River( $( '#most-recent-results' ) );
			rivers.elements = inputs.dialog.find( '.query-results' );

			// Get search notice text
			inputs.queryNotice = $( '#query-notice-message' );
			inputs.queryNoticeTextDefault = inputs.queryNotice.find( '.query-notice-default' );
			inputs.queryNoticeTextHint = inputs.queryNotice.find( '.query-notice-hint' );

			// Bind event handlers
			inputs.dialog.keydown( gpLink.keydown );
			inputs.dialog.keyup( gpLink.keyup );
			inputs.submit.click( function( event ) {
				event.preventDefault();
				gpLink.update();
			});
			inputs.close.add( inputs.backdrop ).add( '#gp-link-cancel a' ).click( function( event ) {
				event.preventDefault();
				gpLink.close();
			});

			$( '#gp-link-search-toggle' ).on( 'click', gpLink.toggleInternalLinking );

			rivers.elements.on( 'river-select', gpLink.updateFields );

			// Display 'hint' message when search field or 'query-results' box are focused
			inputs.search.on( 'focus.gplink', function() {
				inputs.queryNoticeTextDefault.hide();
				inputs.queryNoticeTextHint.removeClass( 'screen-reader-text' ).show();
			} ).on( 'blur.gplink', function() {
				inputs.queryNoticeTextDefault.show();
				inputs.queryNoticeTextHint.addClass( 'screen-reader-text' ).hide();
			} );

			inputs.search.keyup( function() {
				var self = this;

				window.clearTimeout( searchTimer );
				searchTimer = window.setTimeout( function() {
					gpLink.searchInternalLinks.call( self );
				}, 500 );
			});

			function correctURL() {
				var url = $.trim( inputs.url.val() );

				if ( url && correctedURL !== url && ! /^(?:[a-z]+:|#|\?|\.|\/)/.test( url ) ) {
					inputs.url.val( 'http://' + url );
					correctedURL = url;
				}
			}

			inputs.url.on( 'paste', function() {
				setTimeout( correctURL, 0 );
			} );

			inputs.url.on( 'blur', correctURL );
		},

		open: function( editorId ) {
			var ed;

			$( document.body ).addClass( 'modal-open' );

			gpLink.range = null;

			if ( editorId ) {
				window.gpActiveEditor = editorId;
			}

			if ( ! window.gpActiveEditor ) {
				return;
			}

			this.textarea = $( '#' + window.gpActiveEditor ).get( 0 );

			if ( typeof tinymce !== 'undefined' ) {
				ed = tinymce.get( gpActiveEditor );

				if ( ed && ! ed.isHidden() ) {
					editor = ed;
				} else {
					editor = null;
				}

				if ( editor && tinymce.isIE ) {
					editor.windowManager.bookmark = editor.selection.getBookmark();
				}
			}

			if ( ! gpLink.isMCE() && document.selection ) {
				this.textarea.focus();
				this.range = document.selection.createRange();
			}

			inputs.wrap.show();
			inputs.backdrop.show();

			gpLink.refresh();

			$( document ).trigger( 'gplink-open', inputs.wrap );
		},

		isMCE: function() {
			return editor && ! editor.isHidden();
		},

		refresh: function() {
			// Refresh rivers (clear links, check visibility)
			rivers.search.refresh();
			rivers.recent.refresh();

			if ( gpLink.isMCE() ) {
				gpLink.mceRefresh();
			} else {
				inputs.wrap.removeClass( 'has-text-field' );
				inputs.text.val( '' );
				gpLink.setDefaultValues();
			}

			if ( isTouch ) {
				// Close the onscreen keyboard
				inputs.url.focus().blur();
			} else {
				// Focus the URL field and highlight its contents.
				// If this is moved above the selection changes,
				// IE will show a flashing cursor over the dialog.
				inputs.url.focus()[0].select();
			}

			// Load the most recent results if this is the first time opening the panel.
			if ( ! rivers.recent.ul.children().length ) {
				rivers.recent.ajax();
			}

			correctedURL = inputs.url.val().replace( /^http:\/\//, '' );
		},

		hasSelectedText: function( linkNode ) {
			var html = editor.selection.getContent();

			// Partial html and not a fully selected anchor element
			if ( /</.test( html ) && ( ! /^<a [^>]+>[^<]+<\/a>$/.test( html ) || html.indexOf('href=') === -1 ) ) {
				return false;
			}

			if ( linkNode ) {
				var nodes = linkNode.childNodes, i;

				if ( nodes.length === 0 ) {
					return false;
				}

				for ( i = nodes.length - 1; i >= 0; i-- ) {
					if ( nodes[i].nodeType != 3 ) {
						return false;
					}
				}
			}

			return true;
		},

		mceRefresh: function() {
			var text,
				selectedNode = editor.selection.getNode(),
				linkNode = editor.dom.getParent( selectedNode, 'a[href]' ),
				onlyText = this.hasSelectedText( linkNode );

			if ( linkNode ) {
				text = linkNode.innerText || linkNode.textContent;
				inputs.url.val( editor.dom.getAttrib( linkNode, 'href' ) );
				inputs.openInNewTab.prop( 'checked', '_blank' === editor.dom.getAttrib( linkNode, 'target' ) );
				inputs.submit.val( gpLinkL10n.update );
			} else {
				text = editor.selection.getContent({ format: 'text' });
				this.setDefaultValues();
			}

			if ( onlyText ) {
				inputs.text.val( text || '' );
				inputs.wrap.addClass( 'has-text-field' );
			} else {
				inputs.text.val( '' );
				inputs.wrap.removeClass( 'has-text-field' );
			}
		},

		close: function() {
			$( document.body ).removeClass( 'modal-open' );

			if ( ! gpLink.isMCE() ) {
				gpLink.textarea.focus();

				if ( gpLink.range ) {
					gpLink.range.moveToBookmark( gpLink.range.getBookmark() );
					gpLink.range.select();
				}
			} else {
				editor.focus();
			}

			inputs.backdrop.hide();
			inputs.wrap.hide();

			correctedURL = false;

			$( document ).trigger( 'gplink-close', inputs.wrap );
		},

		getAttrs: function() {
			return {
				href: $.trim( inputs.url.val() ),
				target: inputs.openInNewTab.prop( 'checked' ) ? '_blank' : ''
			};
		},

		update: function() {
			if ( gpLink.isMCE() ) {
				gpLink.mceUpdate();
			} else {
				gpLink.htmlUpdate();
			}
		},

		htmlUpdate: function() {
			var attrs, text, html, begin, end, cursor, selection,
				textarea = gpLink.textarea;

			if ( ! textarea ) {
				return;
			}

			attrs = gpLink.getAttrs();
			text = inputs.text.val();

			// If there's no href, return.
			if ( ! attrs.href ) {
				return;
			}

			// Build HTML
			html = '<a href="' + attrs.href + '"';

			if ( attrs.target ) {
				html += ' target="' + attrs.target + '"';
			}

			html += '>';

			// Insert HTML
			if ( document.selection && gpLink.range ) {
				// IE
				// Note: If no text is selected, IE will not place the cursor
				//       inside the closing tag.
				textarea.focus();
				gpLink.range.text = html + ( text || gpLink.range.text ) + '</a>';
				gpLink.range.moveToBookmark( gpLink.range.getBookmark() );
				gpLink.range.select();

				gpLink.range = null;
			} else if ( typeof textarea.selectionStart !== 'undefined' ) {
				// W3C
				begin = textarea.selectionStart;
				end = textarea.selectionEnd;
				selection = text || textarea.value.substring( begin, end );
				html = html + selection + '</a>';
				cursor = begin + html.length;

				// If no text is selected, place the cursor inside the closing tag.
				if ( begin === end && ! selection ) {
					cursor -= 4;
				}

				textarea.value = (
					textarea.value.substring( 0, begin ) +
					html +
					textarea.value.substring( end, textarea.value.length )
				);

				// Update cursor position
				textarea.selectionStart = textarea.selectionEnd = cursor;
			}

			gpLink.close();
			textarea.focus();
		},

		mceUpdate: function() {
			var attrs = gpLink.getAttrs(),
				link, text;

			gpLink.close();
			editor.focus();

			if ( tinymce.isIE ) {
				editor.selection.moveToBookmark( editor.windowManager.bookmark );
			}

			if ( ! attrs.href ) {
				editor.execCommand( 'unlink' );
				return;
			}

			link = getLink();
			text = inputs.text.val();

			if ( link ) {
				if ( text ) {
					if ( 'innerText' in link ) {
						link.innerText = text;
					} else {
						link.textContent = text;
					}
				}

				editor.dom.setAttribs( link, attrs );
			} else {
				if ( text ) {
					editor.selection.setNode( editor.dom.create( 'a', attrs, text ) );
				} else {
					editor.execCommand( 'mceInsertLink', false, attrs );
				}
			}
		},

		updateFields: function( e, li ) {
			inputs.url.val( li.children( '.item-permalink' ).val() );
		},

		setDefaultValues: function() {
			var selection,
				emailRegexp = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i,
				urlRegexp = /^(https?|ftp):\/\/[A-Z0-9.-]+\.[A-Z]{2,4}[^ "]*$/i;

			if ( this.isMCE() ) {
				selection = editor.selection.getContent();
			} else if ( document.selection && gpLink.range ) {
				selection = gpLink.range.text;
			} else if ( typeof this.textarea.selectionStart !== 'undefined' ) {
				selection = this.textarea.value.substring( this.textarea.selectionStart, this.textarea.selectionEnd );
			}

			if ( selection && emailRegexp.test( selection ) ) {
				// Selection is email address
				inputs.url.val( 'mailto:' + selection );
			} else if ( selection && urlRegexp.test( selection ) ) {
				// Selection is URL
				inputs.url.val( selection.replace( /&amp;|&#0?38;/gi, '&' ) );
			} else {
				// Set URL to default.
				inputs.url.val( '' );
			}

			// Update save prompt.
			inputs.submit.val( gpLinkL10n.save );
		},

		searchInternalLinks: function() {
			var t = $( this ), waiting,
				search = t.val();

			if ( search.length > 2 ) {
				rivers.recent.hide();
				rivers.search.show();

				// Don't search if the keypress didn't change the title.
				if ( gpLink.lastSearch == search )
					return;

				gpLink.lastSearch = search;
				waiting = t.parent().find('.spinner').show();

				rivers.search.change( search );
				rivers.search.ajax( function() {
					waiting.hide();
				});
			} else {
				rivers.search.hide();
				rivers.recent.show();
			}
		},

		next: function() {
			rivers.search.next();
			rivers.recent.next();
		},

		prev: function() {
			rivers.search.prev();
			rivers.recent.prev();
		},

		keydown: function( event ) {
			var fn, id,
				key = $.ui.keyCode;

			if ( key.ESCAPE === event.keyCode ) {
				gpLink.close();
				event.stopImmediatePropagation();
			} else if ( key.TAB === event.keyCode ) {
				id = event.target.id;

				// gp-link-submit must always be the last focusable element in the dialog.
				// following focusable elements will be skipped on keyboard navigation.
				if ( id === 'gp-link-submit' && ! event.shiftKey ) {
					inputs.close.focus();
					event.preventDefault();
				} else if ( id === 'gp-link-close' && event.shiftKey ) {
					inputs.submit.focus();
					event.preventDefault();
				}
			}

			if ( event.keyCode !== key.UP && event.keyCode !== key.DOWN ) {
				return;
			}

			if ( document.activeElement &&
				( document.activeElement.id === 'link-title-field' || document.activeElement.id === 'url-field' ) ) {
				return;
			}

			fn = event.keyCode === key.UP ? 'prev' : 'next';
			clearInterval( gpLink.keyInterval );
			gpLink[ fn ]();
			gpLink.keyInterval = setInterval( gpLink[ fn ], gpLink.keySensitivity );
			event.preventDefault();
		},

		keyup: function( event ) {
			var key = $.ui.keyCode;

			if ( event.which === key.UP || event.which === key.DOWN ) {
				clearInterval( gpLink.keyInterval );
				event.preventDefault();
			}
		},

		delayedCallback: function( func, delay ) {
			var timeoutTriggered, funcTriggered, funcArgs, funcContext;

			if ( ! delay )
				return func;

			setTimeout( function() {
				if ( funcTriggered )
					return func.apply( funcContext, funcArgs );
				// Otherwise, wait.
				timeoutTriggered = true;
			}, delay );

			return function() {
				if ( timeoutTriggered )
					return func.apply( this, arguments );
				// Otherwise, wait.
				funcArgs = arguments;
				funcContext = this;
				funcTriggered = true;
			};
		},

		toggleInternalLinking: function( event ) {
			var visible = inputs.wrap.hasClass( 'search-panel-visible' );

			inputs.wrap.toggleClass( 'search-panel-visible', ! visible );
			setUserSetting( 'gplink', visible ? '0' : '1' );
			inputs[ ! visible ? 'search' : 'url' ].focus();
			event.preventDefault();
		}
	};

	River = function( element, search ) {
		var self = this;
		this.element = element;
		this.ul = element.children( 'ul' );
		this.contentHeight = element.children( '#link-selector-height' );
		this.waiting = element.find('.river-waiting');

		this.change( search );
		this.refresh();

		$( '#gp-link .query-results, #gp-link #link-selector' ).scroll( function() {
			self.maybeLoad();
		});
		element.on( 'click', 'li', function( event ) {
			self.select( $( this ), event );
		});
	};

	$.extend( River.prototype, {
		refresh: function() {
			this.deselect();
			this.visible = this.element.is( ':visible' );
		},
		show: function() {
			if ( ! this.visible ) {
				this.deselect();
				this.element.show();
				this.visible = true;
			}
		},
		hide: function() {
			this.element.hide();
			this.visible = false;
		},
		// Selects a list item and triggers the river-select event.
		select: function( li, event ) {
			var liHeight, elHeight, liTop, elTop;

			if ( li.hasClass( 'unselectable' ) || li == this.selected )
				return;

			this.deselect();
			this.selected = li.addClass( 'selected' );
			// Make sure the element is visible
			liHeight = li.outerHeight();
			elHeight = this.element.height();
			liTop = li.position().top;
			elTop = this.element.scrollTop();

			if ( liTop < 0 ) // Make first visible element
				this.element.scrollTop( elTop + liTop );
			else if ( liTop + liHeight > elHeight ) // Make last visible element
				this.element.scrollTop( elTop + liTop - elHeight + liHeight );

			// Trigger the river-select event
			this.element.trigger( 'river-select', [ li, event, this ] );
		},
		deselect: function() {
			if ( this.selected )
				this.selected.removeClass( 'selected' );
			this.selected = false;
		},
		prev: function() {
			if ( ! this.visible )
				return;

			var to;
			if ( this.selected ) {
				to = this.selected.prev( 'li' );
				if ( to.length )
					this.select( to );
			}
		},
		next: function() {
			if ( ! this.visible )
				return;

			var to = this.selected ? this.selected.next( 'li' ) : $( 'li:not(.unselectable):first', this.element );
			if ( to.length )
				this.select( to );
		},
		ajax: function( callback ) {
			var self = this,
				delay = this.query.page == 1 ? 0 : gpLink.minRiverAJAXDuration,
				response = gpLink.delayedCallback( function( results, params ) {
					self.process( results, params );
					if ( callback )
						callback( results, params );
				}, delay );

			this.query.ajax( response );
		},
		change: function( search ) {
			if ( this.query && this._search == search )
				return;

			this._search = search;
			this.query = new Query( search );
			this.element.scrollTop( 0 );
		},
		process: function( results, params ) {
			var list = '', alt = true, classes = '',
				firstPage = params.page == 1;

			if ( ! results ) {
				if ( firstPage ) {
					list += '<li class="unselectable no-matches-found"><span class="item-title"><em>' +
						gpLinkL10n.noMatchesFound + '</em></span></li>';
				}
			} else {
				$.each( results, function() {
					classes = alt ? 'alternate' : '';
					classes += this.title ? '' : ' no-title';
					list += classes ? '<li class="' + classes + '">' : '<li>';
					list += '<input type="hidden" class="item-permalink" value="' + this.permalink + '" />';
					list += '<span class="item-title">';
					list += this.title ? this.title : gpLinkL10n.noTitle;
					list += '</span><span class="item-info">' + this.info + '</span></li>';
					alt = ! alt;
				});
			}

			this.ul[ firstPage ? 'html' : 'append' ]( list );
		},
		maybeLoad: function() {
			var self = this,
				el = this.element,
				bottom = el.scrollTop() + el.height();

			if ( ! this.query.ready() || bottom < this.contentHeight.height() - gpLink.riverBottomThreshold )
				return;

			setTimeout(function() {
				var newTop = el.scrollTop(),
					newBottom = newTop + el.height();

				if ( ! self.query.ready() || newBottom < self.contentHeight.height() - gpLink.riverBottomThreshold )
					return;

				self.waiting.show();
				el.scrollTop( newTop + self.waiting.outerHeight() );

				self.ajax( function() {
					self.waiting.hide();
				});
			}, gpLink.timeToTriggerRiver );
		}
	});

	Query = function( search ) {
		this.page = 1;
		this.allLoaded = false;
		this.querying = false;
		this.search = search;
	};

	$.extend( Query.prototype, {
		ready: function() {
			return ! ( this.querying || this.allLoaded );
		},
		ajax: function( callback ) {
			var self = this,
				query = {
					action : 'gp-link-ajax',
					page : this.page,
					'_ajax_linking_nonce' : inputs.nonce.val()
				};

			if ( this.search )
				query.search = this.search;

			this.querying = true;

			$.post( ajaxurl, query, function( r ) {
				self.page++;
				self.querying = false;
				self.allLoaded = ! r;
				callback( r, query );
			}, 'json' );
		}
	});

	$( document ).ready( gpLink.init );
})( jQuery );
