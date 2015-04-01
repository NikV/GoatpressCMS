/*globals gp */

/**
 * gp.media.view.Settings.Gallery
 *
 * @class
 * @augments gp.media.view.Settings
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Gallery = gp.media.view.Settings.extend({
	className: 'collection-settings gallery-settings',
	template:  gp.template('gallery-settings')
});

module.exports = Gallery;
