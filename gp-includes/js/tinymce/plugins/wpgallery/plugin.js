/* global tinymce */
tinymce.PluginManager.add('gpgallery', function( editor ) {

	function replaceGalleryShortcodes( content ) {
		return content.replace( /\[gallery([^\]]*)\]/g, function( match ) {
			return html( 'gp-gallery', match );
		});
	}

	function html( cls, data ) {
		data = window.encodeURIComponent( data );
		return '<img src="' + tinymce.Env.transparentSrc + '" class="gp-media mceItem ' + cls + '" ' +
			'data-gp-media="' + data + '" data-mce-resize="false" data-mce-placeholder="1" />';
	}

	function restoreMediaShortcodes( content ) {
		function getAttr( str, name ) {
			name = new RegExp( name + '=\"([^\"]+)\"' ).exec( str );
			return name ? window.decodeURIComponent( name[1] ) : '';
		}

		return content.replace( /(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function( match, image ) {
			var data = getAttr( image, 'data-gp-media' );

			if ( data ) {
				return '<p>' + data + '</p>';
			}

			return match;
		});
	}

	function editMedia( node ) {
		var gallery, frame, data;

		if ( node.nodeName !== 'IMG' ) {
			return;
		}

		// Check if the `gp.media` API exists.
		if ( typeof gp === 'undefined' || ! gp.media ) {
			return;
		}

		data = window.decodeURIComponent( editor.dom.getAttrib( node, 'data-gp-media' ) );

		// Make sure we've selected a gallery node.
		if ( editor.dom.hasClass( node, 'gp-gallery' ) && gp.media.gallery ) {
			gallery = gp.media.gallery;
			frame = gallery.edit( data );

			frame.state('gallery-edit').on( 'update', function( selection ) {
				var shortcode = gallery.shortcode( selection ).string();
				editor.dom.setAttrib( node, 'data-gp-media', window.encodeURIComponent( shortcode ) );
				frame.detach();
			});
		}
	}

	// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
	editor.addCommand( 'gp_Gallery', function() {
		editMedia( editor.selection.getNode() );
	});

	editor.on( 'mouseup', function( event ) {
		var dom = editor.dom,
			node = event.target;

		function unselect() {
			dom.removeClass( dom.select( 'img.gp-media-selected' ), 'gp-media-selected' );
		}

		if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-gp-media' ) ) {
			// Don't trigger on right-click
			if ( event.button !== 2 ) {
				if ( dom.hasClass( node, 'gp-media-selected' ) ) {
					editMedia( node );
				} else {
					unselect();
					dom.addClass( node, 'gp-media-selected' );
				}
			}
		} else {
			unselect();
		}
	});

	// Display gallery, audio or video instead of img in the element path
	editor.on( 'ResolveName', function( event ) {
		var dom = editor.dom,
			node = event.target;

		if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-gp-media' ) ) {
			if ( dom.hasClass( node, 'gp-gallery' ) ) {
				event.name = 'gallery';
			}
		}
	});

	editor.on( 'BeforeSetContent', function( event ) {
		// 'gpview' handles the gallery shortcode when present
		if ( ! editor.plugins.gpview || typeof gp === 'undefined' || ! gp.mce ) {
			event.content = replaceGalleryShortcodes( event.content );
		}
	});

	editor.on( 'PostProcess', function( event ) {
		if ( event.get ) {
			event.content = restoreMediaShortcodes( event.content );
		}
	});
});
