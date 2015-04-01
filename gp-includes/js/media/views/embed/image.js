/*globals gp */

/**
 * gp.media.view.EmbedImage
 *
 * @class
 * @augments gp.media.view.Settings.AttachmentDisplay
 * @augments gp.media.view.Settings
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var AttachmentDisplay = gp.media.view.Settings.AttachmentDisplay,
	EmbedImage;

EmbedImage = AttachmentDisplay.extend({
	className: 'embed-media-settings',
	template:  gp.template('embed-image-settings'),

	initialize: function() {
		/**
		 * Call `initialize` directly on parent class with passed arguments
		 */
		AttachmentDisplay.prototype.initialize.apply( this, arguments );
		this.listenTo( this.model, 'change:url', this.updateImage );
	},

	updateImage: function() {
		this.$('img').attr( 'src', this.model.get('url') );
	}
});

module.exports = EmbedImage;
