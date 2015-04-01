/*globals gp, _ */

/**
 * A filter dropdown for month/dates.
 *
 * @class
 * @augments gp.media.view.AttachmentFilters
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var l10n = gp.media.view.l10n,
	DateFilter;

DateFilter = gp.media.view.AttachmentFilters.extend({
	id: 'media-attachment-date-filters',

	createFilters: function() {
		var filters = {};
		_.each( gp.media.view.settings.months || {}, function( value, index ) {
			filters[ index ] = {
				text: value.text,
				props: {
					year: value.year,
					monthnum: value.month
				}
			};
		});
		filters.all = {
			text:  l10n.allDates,
			props: {
				monthnum: false,
				year:  false
			},
			priority: 10
		};
		this.filters = filters;
	}
});

module.exports = DateFilter;
