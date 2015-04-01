/**
 * gp.media.view.Label
 *
 * @class
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Label = gp.media.View.extend({
	tagName: 'label',
	className: 'screen-reader-text',

	initialize: function() {
		this.value = this.options.value;
	},

	render: function() {
		this.$el.html( this.value );

		return this;
	}
});

module.exports = Label;
