( function( $ ) {
	'use strict';

	function reprocessTecaLayouts() {
		$( document ).trigger( 'gsteca:scripts:reprocess' );
	}

	function updateEditLink( $panel ) {
		if ( ! window.gs_teca_wpbakery ) {
			return;
		}

		var base = gs_teca_wpbakery.elementBase || 'teca_wpbakery_events';
		var $shortcodeField = $panel.find(
			'.vc_ui-panel-window[data-vc-shortcode="' + base + '"] .vc_shortcode-param[data-vc-shortcode-param-name="shortcode_id"] .wpb-select'
		);
		var $editLink = $panel.find(
			'.vc_ui-panel-window[data-vc-shortcode="' + base + '"] .vc_shortcode-param[data-vc-shortcode-param-name="shortcode_id"] .teca-wpbakery-edit-link'
		);

		if ( ! $shortcodeField.length || ! $editLink.length ) {
			return;
		}

		var href = gs_teca_wpbakery.editShortcodeBase || $editLink.attr( 'href' );

		if ( href ) {
			href = href.split( '/shortcode/' )[0] + '/shortcode/';
		}

		function syncLink() {
			var shortcodeId = $shortcodeField.val();

			if ( href && shortcodeId ) {
				$editLink.attr( 'href', href + shortcodeId );
			}
		}

		syncLink();
		$shortcodeField.off( 'change.tecaWpbakery' ).on( 'change.tecaWpbakery', syncLink );
	}

	function bindEditLinkFix() {
		var attempts = 0;
		var interval = setInterval( function() {
			attempts++;
			updateEditLink( $( 'body' ) );

			if ( attempts > 100 ) {
				clearInterval( interval );
			}
		}, 50 );
	}

	$( window ).on( 'load', function() {
		if ( ! window.vc ) {
			return;
		}

		$( 'body' ).on( 'click', '.wpb_teca_wpbakery_events .vc_control-btn-edit', bindEditLinkFix );
		$( 'body' ).on( 'click', '.wpb-elements-list li.wpb-layout-element-button a#teca_wpbakery_events', bindEditLinkFix );

		var $iframe = $( '#vc_inline-frame' );

		if ( $iframe.length ) {
			$iframe.contents().find( 'body' ).on(
				'mouseleave',
				'.vc_teca_wpbakery_events .vc_controls .vc_control-btn-edit',
				bindEditLinkFix
			);
		}

		if ( window.vc.events ) {
			window.vc.events.on( 'shortcodeView:ready', function( model ) {
				if ( model && model.get && model.get( 'shortcode' ) === ( gs_teca_wpbakery.elementBase || 'teca_wpbakery_events' ) ) {
					reprocessTecaLayouts();
				}
			} );

			window.vc.events.on( 'shortcodeView:updated', function( model ) {
				if ( model && model.get && model.get( 'shortcode' ) === ( gs_teca_wpbakery.elementBase || 'teca_wpbakery_events' ) ) {
					reprocessTecaLayouts();
				}
			} );
		}

		$( document ).on( 'vc-render', reprocessTecaLayouts );
	} );
}( jQuery ) );
