/*globals gp */

/**
 * gp.media.view.Attachments.EditSelection
 *
 * @class
 * @augments gp.media.view.Attachment.Selection
 * @augments gp.media.view.Attachment
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var EditSelection = gp.media.view.Attachment.Selection.extend({
	buttons: {
		close: true
	}
});

module.exports = EditSelection;
