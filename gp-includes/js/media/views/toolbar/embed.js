/*globals gp, _ */

/**
 * gp.media.view.Toolbar.Embed
 *
 * @class
 * @augments gp.media.view.Toolbar.Select
 * @augments gp.media.view.Toolbar
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Select = gp.media.view.Toolbar.Select,
	l10n = gp.media.view.l10n,
	Embed;

Embed = Select.extend({
	initialize: function() {
		_.defaults( this.options, {
			text: l10n.insertIntoPost,
			requires: false
		});
		// Call 'initialize' directly on the parent class.
		Select.prototype.initialize.apply( this, arguments );
	},

	refresh: function() {
		var url = this.controller.state().props.get('url');
		this.get('select').model.set( 'disabled', ! url || url === 'http://' );
		/**
		 * call 'refresh' directly on the parent class
		 */
		Select.prototype.refresh.apply( this, arguments );
	}
});

module.exports = Embed;
