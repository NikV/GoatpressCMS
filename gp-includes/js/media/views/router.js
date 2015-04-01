/*globals gp */

/**
 * gp.media.view.Router
 *
 * @class
 * @augments gp.media.view.Menu
 * @augments gp.media.view.PriorityList
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Menu = gp.media.view.Menu,
	Router;

Router = Menu.extend({
	tagName:   'div',
	className: 'media-router',
	property:  'contentMode',
	ItemView:  gp.media.view.RouterItem,
	region:    'router',

	initialize: function() {
		this.controller.on( 'content:render', this.update, this );
		// Call 'initialize' directly on the parent class.
		Menu.prototype.initialize.apply( this, arguments );
	},

	update: function() {
		var mode = this.controller.content.mode();
		if ( mode ) {
			this.select( mode );
		}
	}
});

module.exports = Router;
