/*globals gp */

/**
 * gp.media.view.UploaderStatusError
 *
 * @class
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 */
var UploaderStatusError = gp.media.View.extend({
	className: 'upload-error',
	template:  gp.template('uploader-status-error')
});

module.exports = UploaderStatusError;
