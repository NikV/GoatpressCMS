/*globals gp */

/**
 * gp.media.view.Search
 *
 * @class
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var l10n = gp.media.view.l10n,
	Search;

Search = gp.media.View.extend({
	tagName:   'input',
	className: 'search',
	id:        'media-search-input',

	attributes: {
		type:        'search',
		placeholder: l10n.search
	},

	events: {
		'input':  'search',
		'keyup':  'search',
		'change': 'search',
		'search': 'search'
	},

	/**
	 * @returns {gp.media.view.Search} Returns itself to allow chaining
	 */
	render: function() {
		this.el.value = this.model.escape('search');
		return this;
	},

	search: function( event ) {
		if ( event.target.value ) {
			this.model.set( 'search', event.target.value );
		} else {
			this.model.unset('search');
		}
	}
});

module.exports = Search;
