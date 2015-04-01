/*globals gp */

/**
 * gp.media.view.Attachment.Selection
 *
 * @class
 * @augments gp.media.view.Attachment
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Selection = gp.media.view.Attachment.extend({
	className: 'attachment selection',

	// On click, just select the model, instead of removing the model from
	// the selection.
	toggleSelection: function() {
		this.options.selection.single( this.model );
	}
});

module.exports = Selection;
