/* global _gpUtilSettings */
window.gp = window.gp || {};

(function ($) {
	// Check for the utility settings.
	var settings = typeof _gpUtilSettings === 'undefined' ? {} : _gpUtilSettings;

	/**
	 * gp.template( id )
	 *
	 * Fetch a JavaScript template for an id, and return a templating function for it.
	 *
	 * @param  {string} id   A string that corresponds to a DOM element with an id prefixed with "tmpl-".
	 *                       For example, "attachment" maps to "tmpl-attachment".
	 * @return {function}    A function that lazily-compiles the template requested.
	 */
	gp.template = _.memoize(function ( id ) {
		var compiled,
			/*
			 * Underscore's default ERB-style templates are incompatible with PHP
			 * when asp_tags is enabled, so Goatpress uses Mustache-inspired templating syntax.
			 *
			 * @see trac ticket #22344.
			 */
			options = {
				evaluate:    /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
				variable:    'data'
			};

		return function ( data ) {
			compiled = compiled || _.template( $( '#tmpl-' + id ).html(), null, options );
			return compiled( data );
		};
	});

	// gp.ajax
	// ------
	//
	// Tools for sending ajax requests with JSON responses and built in error handling.
	// Mirrors and wraps jQuery's ajax APIs.
	gp.ajax = {
		settings: settings.ajax || {},

		/**
		 * gp.ajax.post( [action], [data] )
		 *
		 * Sends a POST request to Goatpress.
		 *
		 * @param  {string} action The slug of the action to fire in Goatpress.
		 * @param  {object} data   The data to populate $_POST with.
		 * @return {$.promise}     A jQuery promise that represents the request.
		 */
		post: function( action, data ) {
			return gp.ajax.send({
				data: _.isObject( action ) ? action : _.extend( data || {}, { action: action })
			});
		},

		/**
		 * gp.ajax.send( [action], [options] )
		 *
		 * Sends a POST request to Goatpress.
		 *
		 * @param  {string} action  The slug of the action to fire in Goatpress.
		 * @param  {object} options The options passed to jQuery.ajax.
		 * @return {$.promise}      A jQuery promise that represents the request.
		 */
		send: function( action, options ) {
			if ( _.isObject( action ) ) {
				options = action;
			} else {
				options = options || {};
				options.data = _.extend( options.data || {}, { action: action });
			}

			options = _.defaults( options || {}, {
				type:    'POST',
				url:     gp.ajax.settings.url,
				context: this
			});

			return $.Deferred( function( deferred ) {
				// Transfer success/error callbacks.
				if ( options.success )
					deferred.done( options.success );
				if ( options.error )
					deferred.fail( options.error );

				delete options.success;
				delete options.error;

				// Use with PHP's gp_send_json_success() and gp_send_json_error()
				$.ajax( options ).done( function( response ) {
					// Treat a response of `1` as successful for backwards
					// compatibility with existing handlers.
					if ( response === '1' || response === 1 )
						response = { success: true };

					if ( _.isObject( response ) && ! _.isUndefined( response.success ) )
						deferred[ response.success ? 'resolveWith' : 'rejectWith' ]( this, [response.data] );
					else
						deferred.rejectWith( this, [response] );
				}).fail( function() {
					deferred.rejectWith( this, arguments );
				});
			}).promise();
		}
	};

}(jQuery));
