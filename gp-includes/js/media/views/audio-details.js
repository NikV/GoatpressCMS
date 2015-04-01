/*globals gp */

/**
 * gp.media.view.AudioDetails
 *
 * @class
 * @augments gp.media.view.MediaDetails
 * @augments gp.media.view.Settings.AttachmentDisplay
 * @augments gp.media.view.Settings
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var MediaDetails = gp.media.view.MediaDetails,
	AudioDetails;

AudioDetails = MediaDetails.extend({
	className: 'audio-details',
	template:  gp.template('audio-details'),

	setMedia: function() {
		var audio = this.$('.gp-audio-shortcode');

		if ( audio.find( 'source' ).length ) {
			if ( audio.is(':hidden') ) {
				audio.show();
			}
			this.media = MediaDetails.prepareSrc( audio.get(0) );
		} else {
			audio.hide();
			this.media = false;
		}

		return this;
	}
});

module.exports = AudioDetails;
