( function( $ ) {
	'use strict';

	function reprocessTecaLayouts() {
		$( document ).trigger( 'gsteca:scripts:reprocess' );
	}

	var count = 0;
	var interval = setInterval( function() {
		reprocessTecaLayouts();

		if ( count > 40 ) {
			clearInterval( interval );
		}

		count++;
	}, 300 );

	$( window ).on( 'load', reprocessTecaLayouts );

	if ( window.parent && window.parent.jQuery ) {
		window.parent.jQuery( window.parent.document ).on( 'oxygen-rebuild-element', reprocessTecaLayouts );
	}
}( jQuery ) );
