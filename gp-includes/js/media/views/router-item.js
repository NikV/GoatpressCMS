/**
 * gp.media.view.RouterItem
 *
 * @class
 * @augments gp.media.view.MenuItem
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var RouterItem = gp.media.view.MenuItem.extend({
	/**
	 * On click handler to activate the content region's corresponding mode.
	 */
	click: function() {
		var contentMode = this.options.contentMode;
		if ( contentMode ) {
			this.controller.content.mode( contentMode );
		}
	}
});

module.exports = RouterItem;
