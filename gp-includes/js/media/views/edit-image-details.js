/*globals gp, _ */

/**
 * gp.media.view.EditImage.Details
 *
 * @class
 * @augments gp.media.view.EditImage
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var View = gp.media.View,
	EditImage = gp.media.view.EditImage,
	Details;

Details = EditImage.extend({
	initialize: function( options ) {
		this.editor = window.imageEdit;
		this.frame = options.frame;
		this.controller = options.controller;
		View.prototype.initialize.apply( this, arguments );
	},

	back: function() {
		this.frame.content.mode( 'edit-metadata' );
	},

	save: function() {
		this.model.fetch().done( _.bind( function() {
			this.frame.content.mode( 'edit-metadata' );
		}, this ) );
	}
});

module.exports = Details;
