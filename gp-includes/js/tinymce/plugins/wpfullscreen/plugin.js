/* global tinymce */
/**
 * gp Fullscreen (Distraction-Free Writing) TinyMCE plugin
 */
tinymce.PluginManager.add( 'gpfullscreen', function( editor ) {
	var settings = editor.settings;

	function fullscreenOn() {
		settings.gp_fullscreen = true;
		editor.dom.addClass( editor.getDoc().documentElement, 'gp-fullscreen' );
		// Start auto-resizing
		editor.execCommand( 'gpAutoResizeOn' );
	}

	function fullscreenOff() {
		settings.gp_fullscreen = false;
		editor.dom.removeClass( editor.getDoc().documentElement, 'gp-fullscreen' );
		// Stop auto-resizing
		editor.execCommand( 'gpAutoResizeOff' );
	}

	// For use from outside the editor.
	editor.addCommand( 'gpFullScreenOn', fullscreenOn );
	editor.addCommand( 'gpFullScreenOff', fullscreenOff );

	function getExtAPI() {
		return ( typeof gp !== 'undefined' && gp.editor && gp.editor.fullscreen );
	}

	// Toggle DFW mode. For use from inside the editor.
	function toggleFullscreen() {
		var fullscreen = getExtAPI();

		if ( fullscreen ) {
			if ( editor.getParam('gp_fullscreen') ) {
				fullscreen.off();
			} else {
				fullscreen.on();
			}
		}
	}

	editor.addCommand( 'gpFullScreen', toggleFullscreen );

	editor.on( 'keydown', function( event ) {
		var fullscreen;

		// Turn fullscreen off when Esc is pressed.
		if ( event.keyCode === 27 && ( fullscreen = getExtAPI() ) && fullscreen.settings.visible ) {
			fullscreen.off();
		}
	});

	editor.on( 'init', function() {
		// Set the editor when initializing from whitin DFW
		if ( editor.getParam('gp_fullscreen') ) {
			fullscreenOn();
		}
	});

	// Register buttons
	editor.addButton( 'gp_fullscreen', {
		tooltip: 'Distraction-free writing mode',
		shortcut: 'Alt+Shift+W',
		onclick: toggleFullscreen,
		classes: 'gp-fullscreen btn widget' // This overwrites all classes on the container!
	});

	editor.addMenuItem( 'gp_fullscreen', {
		text: 'Distraction-free writing mode',
		icon: 'gp_fullscreen',
		shortcut: 'Alt+Shift+W',
		context: 'view',
		onclick: toggleFullscreen
	});
});
