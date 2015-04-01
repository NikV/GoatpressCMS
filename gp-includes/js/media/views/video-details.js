/*globals gp */

/**
 * gp.media.view.VideoDetails
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
	VideoDetails;

VideoDetails = MediaDetails.extend({
	className: 'video-details',
	template:  gp.template('video-details'),

	setMedia: function() {
		var video = this.$('.gp-video-shortcode');

		if ( video.find( 'source' ).length ) {
			if ( video.is(':hidden') ) {
				video.show();
			}

			if ( ! video.hasClass( 'youtube-video' ) && ! video.hasClass( 'vimeo-video' ) ) {
				this.media = MediaDetails.prepareSrc( video.get(0) );
			} else {
				this.media = video.get(0);
			}
		} else {
			video.hide();
			this.media = false;
		}

		return this;
	}
});

module.exports = VideoDetails;
