window.gp = window.gp || {};

( function ( gp, $ ) {
	'use strict';

	var $container;

	/**
	 * Update the ARIA live notification area text node.
	 *
	 * @since 4.2.0
	 *
	 * @param {String} message
	 */
	function speak( message ) {
		if ( $container ) {
			$container.text( message );
		}
	}

	/**
	 * Initialize gp.a11y and define ARIA live notification area.
	 *
	 * @since 4.2.0
	 */
	$( document ).ready( function() {
		$container = $( '#gp-a11y-speak' );

		if ( ! $container.length ) {
			$container = $( '<div>', {
				id: 'gp-a11y-speak',
				role: 'status',
				'aria-live': 'polite',
				'aria-relevant': 'all',
				'aria-atomic': 'true',
				'class': 'screen-reader-text'
			} );

			$( document.body ).append( $container );
		}
	} );

	gp.a11y = gp.a11y || {};
	gp.a11y.speak = speak;

} )( window.gp, window.jQuery );
