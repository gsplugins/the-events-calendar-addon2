( function( $ ) {
	'use strict';

	function updateEditLink( $panel ) {
		var $shortcodeField = $panel.find( '.elementor-control-shortcode_id .elementor-control-input-wrapper select' );
		var $editLink = $panel.find( '.elementor-control-shortcode_id .teca-elementor-edit-link' );

		if ( ! $shortcodeField.length || ! $editLink.length ) {
			return;
		}

		var baseHref = window.gs_teca_elementor && window.gs_teca_elementor.editShortcodeBase
			? window.gs_teca_elementor.editShortcodeBase
			: $editLink.attr( 'href' );

		if ( baseHref ) {
			baseHref = baseHref.split( '/shortcode/' )[0] + '/shortcode/';
		}

		function syncLink() {
			var shortcodeId = $shortcodeField.val();

			if ( baseHref && shortcodeId ) {
				$editLink.attr( 'href', baseHref + shortcodeId );
			}
		}

		syncLink();
		$shortcodeField.off( 'change.tecaElementor' ).on( 'change.tecaElementor', syncLink );
	}

	$( window ).on( 'load', function() {
		if ( ! window.elementor || ! elementor.hooks ) {
			return;
		}

		elementor.hooks.addAction( 'panel/open_editor/widget/teca-events', function( panel ) {
			updateEditLink( panel.$el );
		} );
	} );
}( jQuery ) );
