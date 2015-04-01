( function( $ ){
	gp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '#site-title a' ).text( to );
		} );
	} );
	gp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '#site-description' ).text( to );
		} );
	} );
} )( jQuery );