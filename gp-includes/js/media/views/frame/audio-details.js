/*globals gp */

/**
 * gp.media.view.MediaFrame.AudioDetails
 *
 * @class
 * @augments gp.media.view.MediaFrame.MediaDetails
 * @augments gp.media.view.MediaFrame.Select
 * @augments gp.media.view.MediaFrame
 * @augments gp.media.view.Frame
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 * @mixes gp.media.controller.StateMachine
 */
var MediaDetails = gp.media.view.MediaFrame.MediaDetails,
	MediaLibrary = gp.media.controller.MediaLibrary,

	l10n = gp.media.view.l10n,
	AudioDetails;

AudioDetails = MediaDetails.extend({
	defaults: {
		id:      'audio',
		url:     '',
		menu:    'audio-details',
		content: 'audio-details',
		toolbar: 'audio-details',
		type:    'link',
		title:    l10n.audioDetailsTitle,
		priority: 120
	},

	initialize: function( options ) {
		options.DetailsView = gp.media.view.AudioDetails;
		options.cancelText = l10n.audioDetailsCancel;
		options.addText = l10n.audioAddSourceTitle;

		MediaDetails.prototype.initialize.call( this, options );
	},

	bindHandlers: function() {
		MediaDetails.prototype.bindHandlers.apply( this, arguments );

		this.on( 'toolbar:render:replace-audio', this.renderReplaceToolbar, this );
		this.on( 'toolbar:render:add-audio-source', this.renderAddSourceToolbar, this );
	},

	createStates: function() {
		this.states.add([
			new gp.media.controller.AudioDetails( {
				media: this.media
			} ),

			new MediaLibrary( {
				type: 'audio',
				id: 'replace-audio',
				title: l10n.audioReplaceTitle,
				toolbar: 'replace-audio',
				media: this.media,
				menu: 'audio-details'
			} ),

			new MediaLibrary( {
				type: 'audio',
				id: 'add-audio-source',
				title: l10n.audioAddSourceTitle,
				toolbar: 'add-audio-source',
				media: this.media,
				menu: false
			} )
		]);
	}
});

module.exports = AudioDetails;
