/*globals gp, _, jQuery */

/**
 * gp.media.view.Cropper
 *
 * Uses the imgAreaSelect plugin to allow a user to crop an image.
 *
 * Takes imgAreaSelect options from
 * gp.customize.HeaderControl.calculateImageSelectOptions via
 * gp.customize.HeaderControl.openMM.
 *
 * @class
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var View = gp.media.View,
	UploaderStatus = gp.media.view.UploaderStatus,
	l10n = gp.media.view.l10n,
	$ = jQuery,
	Cropper;

Cropper = View.extend({
	className: 'crop-content',
	template: gp.template('crop-content'),
	initialize: function() {
		_.bindAll(this, 'onImageLoad');
	},
	ready: function() {
		this.controller.frame.on('content:error:crop', this.onError, this);
		this.$image = this.$el.find('.crop-image');
		this.$image.on('load', this.onImageLoad);
		$(window).on('resize.cropper', _.debounce(this.onImageLoad, 250));
	},
	remove: function() {
		$(window).off('resize.cropper');
		this.$el.remove();
		this.$el.off();
		View.prototype.remove.apply(this, arguments);
	},
	prepare: function() {
		return {
			title: l10n.cropYourImage,
			url: this.options.attachment.get('url')
		};
	},
	onImageLoad: function() {
		var imgOptions = this.controller.get('imgSelectOptions');
		if (typeof imgOptions === 'function') {
			imgOptions = imgOptions(this.options.attachment, this.controller);
		}

		imgOptions = _.extend(imgOptions, {parent: this.$el});
		this.trigger('image-loaded');
		this.controller.imgSelect = this.$image.imgAreaSelect(imgOptions);
	},
	onError: function() {
		var filename = this.options.attachment.get('filename');

		this.views.add( '.upload-errors', new gp.media.view.UploaderStatusError({
			filename: UploaderStatus.prototype.filename(filename),
			message: window._gpMediaViewsL10n.cropError
		}), { at: 0 });
	}
});

module.exports = Cropper;
