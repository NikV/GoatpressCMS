/*globals gp */

/**
 * gp.media.view.Attachment.Details.TwoColumn
 *
 * A similar view to media.view.Attachment.Details
 * for use in the Edit Attachment modal.
 *
 * @class
 * @augments gp.media.view.Attachment.Details
 * @augments gp.media.view.Attachment
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Details = gp.media.view.Attachment.Details,
	TwoColumn;

TwoColumn = Details.extend({
	template: gp.template( 'attachment-details-two-column' ),

	editAttachment: function( event ) {
		event.preventDefault();
		this.controller.content.mode( 'edit-image' );
	},

	/**
	 * Noop this from parent class, doesn't apply here.
	 */
	toggleSelectionHandler: function() {},

	render: function() {
		Details.prototype.render.apply( this, arguments );

		gp.media.mixin.removeAllPlayers();
		this.$( 'audio, video' ).each( function (i, elem) {
			var el = gp.media.view.MediaDetails.prepareSrc( elem );
			new window.MediaElementPlayer( el, gp.media.mixin.mejsSettings );
		} );
	}
});

module.exports = TwoColumn;
