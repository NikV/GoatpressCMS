/*globals gp, _ */

/**
 * gp.media.controller.MediaLibrary
 *
 * @class
 * @augments gp.media.controller.Library
 * @augments gp.media.controller.State
 * @augments Backbone.Model
 */
var Library = gp.media.controller.Library,
	MediaLibrary;

MediaLibrary = Library.extend({
	defaults: _.defaults({
		// Attachments browser defaults. @see media.view.AttachmentsBrowser
		filterable:      'uploaded',

		displaySettings: false,
		priority:        80,
		syncSelection:   false
	}, Library.prototype.defaults ),

	/**
	 * @since 3.9.0
	 *
	 * @param options
	 */
	initialize: function( options ) {
		this.media = options.media;
		this.type = options.type;
		this.set( 'library', gp.media.query({ type: this.type }) );

		Library.prototype.initialize.apply( this, arguments );
	},

	/**
	 * @since 3.9.0
	 */
	activate: function() {
		// @todo this should use this.frame.
		if ( gp.media.frame.lastMime ) {
			this.set( 'library', gp.media.query({ type: gp.media.frame.lastMime }) );
			delete gp.media.frame.lastMime;
		}
		Library.prototype.activate.apply( this, arguments );
	}
});

module.exports = MediaLibrary;
