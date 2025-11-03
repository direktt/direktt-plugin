'use strict'

jQuery( document ).ready( function( $ ) { "use strict";
	$( '#direktt-profile-tools-toggler' ).on( 'click', function() {
		$(this).parent().parent().parent().parent().toggleClass('direktt-profile-tools-open');
		// return: false;
	});
});