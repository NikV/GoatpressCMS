/* global tinymce */
/**
 * Included for back-compat.
 * The default WindowManager in TinyMCE 4.0 supports three types of dialogs:
 *	- With HTML created from JS.
 *	- With inline HTML (like gpWindowManager).
 *	- Old type iframe based dialogs.
 * For examples see the default plugins: https://github.com/tinymce/tinymce/tree/master/js/tinymce/plugins
 */
tinymce.gpWindowManager = tinymce.InlineWindowManager = function( editor ) {
	if ( this.gp ) {
		return this;
	}

	this.gp = {};
	this.parent = editor.windowManager;
	this.editor = editor;

	tinymce.extend( this, this.parent );

	this.open = function( args, params ) {
		var $element,
			self = this,
			gp = this.gp;

		if ( ! args.gpDialog ) {
			return this.parent.open.apply( this, arguments );
		} else if ( ! args.id ) {
			return;
		}

		if ( typeof jQuery === 'undefined' || ! jQuery.gp || ! jQuery.gp.gpdialog ) {
			// gpdialog.js is not loaded
			if ( window.console && window.console.error ) {
				window.console.error('gpdialog.js is not loaded. Please set "gpdialogs" as dependency for your script when calling gp_enqueue_script(). You may also want to enqueue the "gp-jquery-ui-dialog" stylesheet.');
			}

			return;
		}

		gp.$element = $element = jQuery( '#' + args.id );

		if ( ! $element.length ) {
			return;
		}

		if ( window.console && window.console.log ) {
			window.console.log('tinymce.gpWindowManager is deprecated. Use the default editor.windowManager to open dialogs with inline HTML.');
		}

		gp.features = args;
		gp.params = params;

		// Store selection. Takes a snapshot in the FocusManager of the selection before focus is moved to the dialog.
		editor.nodeChanged();

		// Create the dialog if necessary
		if ( ! $element.data('gpdialog') ) {
			$element.gpdialog({
				title: args.title,
				width: args.width,
				height: args.height,
				modal: true,
				dialogClass: 'gp-dialog',
				zIndex: 300000
			});
		}

		$element.gpdialog('open');

		$element.on( 'gpdialogclose', function() {
			if ( self.gp.$element ) {
				self.gp = {};
			}
		});
	};

	this.close = function() {
		if ( ! this.gp.features || ! this.gp.features.gpDialog ) {
			return this.parent.close.apply( this, arguments );
		}

		this.gp.$element.gpdialog('close');
	};
};

tinymce.PluginManager.add( 'gpdialogs', function( editor ) {
	// Replace window manager
	editor.on( 'init', function() {
		editor.windowManager = new tinymce.gpWindowManager( editor );
	});
});
