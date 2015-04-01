/* global gpColorPickerL10n */
( function( $, undef ){

	var ColorPicker,
		// html stuff
		_before = '<a tabindex="0" class="gp-color-result" />',
		_after = '<div class="gp-picker-holder" />',
		_wrap = '<div class="gp-picker-container" />',
		_button = '<input type="button" class="button button-small hidden" />';

	// jQuery UI Widget constructor
	ColorPicker = {
		options: {
			defaultColor: false,
			change: false,
			clear: false,
			hide: true,
			palettes: true,
			width: 255,
			mode: 'hsv'
		},
		_create: function() {
			// bail early for unsupported Iris.
			if ( ! $.support.iris ) {
				return;
			}

			var self = this,
				el = self.element;

			$.extend( self.options, el.data() );

			// keep close bound so it can be attached to a body listener
			self.close = $.proxy( self.close, self );

			self.initialValue = el.val();

			// Set up HTML structure, hide things
			el.addClass( 'gp-color-picker' ).hide().wrap( _wrap );
			self.wrap = el.parent();
			self.toggler = $( _before ).insertBefore( el ).css( { backgroundColor: self.initialValue } ).attr( 'title', gpColorPickerL10n.pick ).attr( 'data-current', gpColorPickerL10n.current );
			self.pickerContainer = $( _after ).insertAfter( el );
			self.button = $( _button );

			if ( self.options.defaultColor ) {
				self.button.addClass( 'gp-picker-default' ).val( gpColorPickerL10n.defaultString );
			} else {
				self.button.addClass( 'gp-picker-clear' ).val( gpColorPickerL10n.clear );
			}

			el.wrap( '<span class="gp-picker-input-wrap" />' ).after(self.button);

			el.iris( {
				target: self.pickerContainer,
				hide: self.options.hide,
				width: self.options.width,
				mode: self.options.mode,
				palettes: self.options.palettes,
				change: function( event, ui ) {
					self.toggler.css( { backgroundColor: ui.color.toString() } );
					// check for a custom cb
					if ( $.isFunction( self.options.change ) ) {
						self.options.change.call( this, event, ui );
					}
				}
			} );

			el.val( self.initialValue );
			self._addListeners();
			if ( ! self.options.hide ) {
				self.toggler.click();
			}
		},
		_addListeners: function() {
			var self = this;

			// prevent any clicks inside this widget from leaking to the top and closing it
			self.wrap.on( 'click.gpcolorpicker', function( event ) {
				event.stopPropagation();
			});

			self.toggler.click( function(){
				if ( self.toggler.hasClass( 'gp-picker-open' ) ) {
					self.close();
				} else {
					self.open();
				}
			});

			self.element.change( function( event ) {
				var me = $( this ),
					val = me.val();
				// Empty = clear
				if ( val === '' || val === '#' ) {
					self.toggler.css( 'backgroundColor', '' );
					// fire clear callback if we have one
					if ( $.isFunction( self.options.clear ) ) {
						self.options.clear.call( this, event );
					}
				}
			});

			// open a keyboard-focused closed picker with space or enter
			self.toggler.on( 'keyup', function( event ) {
				if ( event.keyCode === 13 || event.keyCode === 32 ) {
					event.preventDefault();
					self.toggler.trigger( 'click' ).next().focus();
				}
			});

			self.button.click( function( event ) {
				var me = $( this );
				if ( me.hasClass( 'gp-picker-clear' ) ) {
					self.element.val( '' );
					self.toggler.css( 'backgroundColor', '' );
					if ( $.isFunction( self.options.clear ) ) {
						self.options.clear.call( this, event );
					}
				} else if ( me.hasClass( 'gp-picker-default' ) ) {
					self.element.val( self.options.defaultColor ).change();
				}
			});
		},
		open: function() {
			this.element.show().iris( 'toggle' ).focus();
			this.button.removeClass( 'hidden' );
			this.toggler.addClass( 'gp-picker-open' );
			$( 'body' ).trigger( 'click.gpcolorpicker' ).on( 'click.gpcolorpicker', this.close );
		},
		close: function() {
			this.element.hide().iris( 'toggle' );
			this.button.addClass( 'hidden' );
			this.toggler.removeClass( 'gp-picker-open' );
			$( 'body' ).off( 'click.gpcolorpicker', this.close );
		},
		// $("#input").gpColorPicker('color') returns the current color
		// $("#input").gpColorPicker('color', '#bada55') to set
		color: function( newColor ) {
			if ( newColor === undef ) {
				return this.element.iris( 'option', 'color' );
			}

			this.element.iris( 'option', 'color', newColor );
		},
		//$("#input").gpColorPicker('defaultColor') returns the current default color
		//$("#input").gpColorPicker('defaultColor', newDefaultColor) to set
		defaultColor: function( newDefaultColor ) {
			if ( newDefaultColor === undef ) {
				return this.options.defaultColor;
			}

			this.options.defaultColor = newDefaultColor;
		}
	};

	$.widget( 'gp.gpColorPicker', ColorPicker );
}( jQuery ) );
