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

	if ( window.FLBuilder ) {
		FLBuilder.addHook( 'didRenderLayout', reprocessTecaLayouts );
		FLBuilder.addHook( 'didRefreshNode', reprocessTecaLayouts );
	}
}( jQuery ) );
