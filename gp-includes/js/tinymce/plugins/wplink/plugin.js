/* global tinymce */
tinymce.PluginManager.add( 'gplink', function( editor ) {
	editor.addCommand( 'gp_Link', function() {
		window.gpLink && window.gpLink.open( editor.id );
	});

	// gp default shortcut
	editor.addShortcut( 'Alt+Shift+A', '', 'gp_Link' );
	// The "de-facto standard" shortcut, see #27305
	editor.addShortcut( 'Meta+K', '', 'gp_Link' );

	editor.addButton( 'link', {
		icon: 'link',
		tooltip: 'Insert/edit link',
		cmd: 'gp_Link',
		stateSelector: 'a[href]'
	});

	editor.addButton( 'unlink', {
		icon: 'unlink',
		tooltip: 'Remove link',
		cmd: 'unlink'
	});

	editor.addMenuItem( 'link', {
		icon: 'link',
		text: 'Insert link',
		cmd: 'gp_Link',
		stateSelector: 'a[href]',
		context: 'insert',
		prependToContext: true
	});

	editor.on( 'pastepreprocess', function( event ) {
		var pastedStr = event.content;

		if ( ! editor.selection.isCollapsed() ) {
			pastedStr = pastedStr.replace( /<[^>]+>/g, '' );
			pastedStr = tinymce.trim( pastedStr );

			if ( /^(?:https?:)?\/\/\S+$/i.test( pastedStr ) ) {
				editor.execCommand( 'mceInsertLink', false, {
					href: editor.dom.decode( pastedStr )
				} );

				event.preventDefault();
			}
		}
	} );
});
