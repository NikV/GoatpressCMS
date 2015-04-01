/**
 * gp.media.view.Embed
 *
 * @class
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Embed = gp.media.View.extend({
	className: 'media-embed',

	initialize: function() {
		/**
		 * @member {gp.media.view.EmbedUrl}
		 */
		this.url = new gp.media.view.EmbedUrl({
			controller: this.controller,
			model:      this.model.props
		}).render();

		this.views.set([ this.url ]);
		this.refresh();
		this.listenTo( this.model, 'change:type', this.refresh );
		this.listenTo( this.model, 'change:loading', this.loading );
	},

	/**
	 * @param {Object} view
	 */
	settings: function( view ) {
		if ( this._settings ) {
			this._settings.remove();
		}
		this._settings = view;
		this.views.add( view );
	},

	refresh: function() {
		var type = this.model.get('type'),
			constructor;

		if ( 'image' === type ) {
			constructor = gp.media.view.EmbedImage;
		} else if ( 'link' === type ) {
			constructor = gp.media.view.EmbedLink;
		} else {
			return;
		}

		this.settings( new constructor({
			controller: this.controller,
			model:      this.model.props,
			priority:   40
		}) );
	},

	loading: function() {
		this.$el.toggleClass( 'embed-loading', this.model.get('loading') );
	}
});

module.exports = Embed;
