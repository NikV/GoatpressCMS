/*globals gp */

/**
 * gp.media.controller.EditAttachmentMetadata
 *
 * A state for editing an attachment's metadata.
 *
 * @class
 * @augments gp.media.controller.State
 * @augments Backbone.Model
 */
var l10n = gp.media.view.l10n,
	EditAttachmentMetadata;

EditAttachmentMetadata = gp.media.controller.State.extend({
	defaults: {
		id:      'edit-attachment',
		// Title string passed to the frame's title region view.
		title:   l10n.attachmentDetails,
		// Region mode defaults.
		content: 'edit-metadata',
		menu:    false,
		toolbar: false,
		router:  false
	}
});

module.exports = EditAttachmentMetadata;
