/**
 * gp.media.view.Iframe
 *
 * @class
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Iframe = gp.media.View.extend({
	className: 'media-iframe',
	/**
	 * @returns {gp.media.view.Iframe} Returns itself to allow chaining
	 */
	render: function() {
		this.views.detach();
		this.$el.html( '<iframe src="' + this.controller.state().get('src') + '" />' );
		this.views.render();
		return this;
	}
});

module.exports = Iframe;
