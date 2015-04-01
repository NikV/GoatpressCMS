/*globals gp, _ */

/**
 * gp.media.view.Attachments.Selection
 *
 * @class
 * @augments gp.media.view.Attachments
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Attachments = gp.media.view.Attachments,
	Selection;

Selection = Attachments.extend({
	events: {},
	initialize: function() {
		_.defaults( this.options, {
			sortable:   false,
			resize:     false,

			// The single `Attachment` view to be used in the `Attachments` view.
			AttachmentView: gp.media.view.Attachment.Selection
		});
		// Call 'initialize' directly on the parent class.
		return Attachments.prototype.initialize.apply( this, arguments );
	}
});

module.exports = Selection;
