'use strict'

/*document.addEventListener( "DOMContentLoaded", () => {
	const host = document.querySelector( '#direktt-profile-wrapper' );
	const shadow = host.attachShadow( { mode: "open" } );
	const css = document.querySelector( '#direktt-profile-style-css' );
	
	// Clone css into shadow root
	if ( css ) {
		shadow.appendChild( css.cloneNode(true) );
	}
	
	// Move content into shadow root
	while ( host.firstChild ) {
		shadow.appendChild( host.firstChild );
	}

	console.log( 'Profile' );
});*/

jQuery( document ).ready( function( $ ) { "use strict";
	$( '#direktt-profile-tools-toggler' ).on( 'click', function() {
		$(this).parent().parent().parent().parent().toggleClass('direktt-profile-tools-open');
		// return: false;
	});
});