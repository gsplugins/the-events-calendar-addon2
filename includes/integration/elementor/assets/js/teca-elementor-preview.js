( function( $ ) {
	'use strict';

	function reprocessTecaWidget( $scope ) {
		var $widget = $scope.find( '.gs_teca_area' );

		if ( ! $widget.length ) {
			$widget = $scope.find( '.teca-elementor-widget-wrap' );
		}

		if ( ! $widget.length ) {
			return;
		}

		$( document ).trigger( 'gsteca:scripts:reprocess' );
	}

	$( window ).on( 'elementor/frontend/init', function() {
		if ( ! window.elementorFrontend || ! elementorFrontend.hooks ) {
			return;
		}

		elementorFrontend.hooks.addAction( 'frontend/element_ready/teca-events.default', reprocessTecaWidget );
	} );
}( jQuery ) );
