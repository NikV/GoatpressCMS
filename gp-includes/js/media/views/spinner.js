/*globals _ */

/**
 * gp.media.view.Spinner
 *
 * @class
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var Spinner = gp.media.View.extend({
	tagName:   'span',
	className: 'spinner',
	spinnerTimeout: false,
	delay: 400,

	show: function() {
		if ( ! this.spinnerTimeout ) {
			this.spinnerTimeout = _.delay(function( $el ) {
				$el.show();
			}, this.delay, this.$el );
		}

		return this;
	},

	hide: function() {
		this.$el.hide();
		this.spinnerTimeout = clearTimeout( this.spinnerTimeout );

		return this;
	}
});

module.exports = Spinner;
