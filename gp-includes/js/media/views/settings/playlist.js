/*globals gp */

/**
 * gp.media.view.Settings.Playlist
 *
 * @class
 * @augments gp.media.view.Settings
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Playlist = gp.media.view.Settings.extend({
	className: 'collection-settings playlist-settings',
	template:  gp.template('playlist-settings')
});

module.exports = Playlist;
