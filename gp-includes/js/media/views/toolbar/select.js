/*globals gp, _ */

/**
 * gp.media.view.Toolbar.Select
 *
 * @class
 * @augments gp.media.view.Toolbar
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Toolbar = gp.media.view.Toolbar,
	l10n = gp.media.view.l10n,
	Select;

Select = Toolbar.extend({
	initialize: function() {
		var options = this.options;

		_.bindAll( this, 'clickSelect' );

		_.defaults( options, {
			event: 'select',
			state: false,
			reset: true,
			close: true,
			text:  l10n.select,

			// Does the button rely on the selection?
			requires: {
				selection: true
			}
		});

		options.items = _.defaults( options.items || {}, {
			select: {
				style:    'primary',
				text:     options.text,
				priority: 80,
				click:    this.clickSelect,
				requires: options.requires
			}
		});
		// Call 'initialize' directly on the parent class.
		Toolbar.prototype.initialize.apply( this, arguments );
	},

	clickSelect: function() {
		var options = this.options,
			controller = this.controller;

		if ( options.close ) {
			controller.close();
		}

		if ( options.event ) {
			controller.state().trigger( options.event );
		}

		if ( options.state ) {
			controller.setState( options.state );
		}

		if ( options.reset ) {
			controller.reset();
		}
	}
});

module.exports = Select;
