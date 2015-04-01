/*globals gp */

/**
 * gp.media.controller.AudioDetails
 *
 * The controller for the Audio Details state
 *
 * @class
 * @augments gp.media.controller.State
 * @augments Backbone.Model
 */
var State = gp.media.controller.State,
	l10n = gp.media.view.l10n,
	AudioDetails;

AudioDetails = State.extend({
	defaults: {
		id: 'audio-details',
		toolbar: 'audio-details',
		title: l10n.audioDetailsTitle,
		content: 'audio-details',
		menu: 'audio-details',
		router: false,
		priority: 60
	},

	initialize: function( options ) {
		this.media = options.media;
		State.prototype.initialize.apply( this, arguments );
	}
});

module.exports = AudioDetails;
