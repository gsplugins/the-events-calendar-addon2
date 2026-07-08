( function( $ ) {
	'use strict';

	function reprocessTecaLayouts( $scope ) {
		$( document ).trigger( 'gsteca:scripts:reprocess' );

		if ( $scope && $scope.length ) {
			$scope.find( '.teca-ux-builder-widget-wrap' ).each( function() {
				$( this ).trigger( 'gsteca:scripts:reprocess' );
			} );
		}
	}

	function bindReprocess() {
		var $wraps = $( '.teca-ux-builder-widget-wrap' );

		if ( $wraps.length ) {
			reprocessTecaLayouts( $wraps.closest( 'body' ) );
		}
	}

	var count = 0;
	var interval = setInterval( function() {
		bindReprocess();

		if ( count > 40 ) {
			clearInterval( interval );
		}

		count++;
	}, 300 );

	$( window ).on( 'load', bindReprocess );

	if ( window.MutationObserver ) {
		var observer = new MutationObserver( function() {
			bindReprocess();
		} );

		$( function() {
			var target = document.body;

			if ( target ) {
				observer.observe( target, {
					childList: true,
					subtree: true,
				} );
			}
		} );
	}
}( jQuery ) );
