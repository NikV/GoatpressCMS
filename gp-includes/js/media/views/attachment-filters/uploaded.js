/*globals gp */

/**
 * gp.media.view.AttachmentFilters.Uploaded
 *
 * @class
 * @augments gp.media.view.AttachmentFilters
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var l10n = gp.media.view.l10n,
	Uploaded;

Uploaded = gp.media.view.AttachmentFilters.extend({
	createFilters: function() {
		var type = this.model.get('type'),
			types = gp.media.view.settings.mimeTypes,
			text;

		if ( types && type ) {
			text = types[ type ];
		}

		this.filters = {
			all: {
				text:  text || l10n.allMediaItems,
				props: {
					uploadedTo: null,
					orderby: 'date',
					order:   'DESC'
				},
				priority: 10
			},

			uploaded: {
				text:  l10n.uploadedToThisPost,
				props: {
					uploadedTo: gp.media.view.settings.post.id,
					orderby: 'menuOrder',
					order:   'ASC'
				},
				priority: 20
			},

			unattached: {
				text:  l10n.unattached,
				props: {
					uploadedTo: 0,
					orderby: 'menuOrder',
					order:   'ASC'
				},
				priority: 50
			}
		};
	}
});

module.exports = Uploaded;
