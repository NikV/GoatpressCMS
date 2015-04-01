/*globals gp */

/**
 * gp.media.controller.VideoDetails
 *
 * The controller for the Video Details state
 *
 * @class
 * @augments gp.media.controller.State
 * @augments Backbone.Model
 */
var State = gp.media.controller.State,
	l10n = gp.media.view.l10n,
	VideoDetails;

VideoDetails = State.extend({
	defaults: {
		id: 'video-details',
		toolbar: 'video-details',
		title: l10n.videoDetailsTitle,
		content: 'video-details',
		menu: 'video-details',
		router: false,
		priority: 60
	},

	initialize: function( options ) {
		this.media = options.media;
		State.prototype.initialize.apply( this, arguments );
	}
});

module.exports = VideoDetails;
