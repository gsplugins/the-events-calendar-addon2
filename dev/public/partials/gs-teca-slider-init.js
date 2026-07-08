/* TECA carousel/ticker init helpers — demo-site resilient (width, loop, columns, dedupe). */
(function( $ ) {
	'use strict';

	/**
	 * Match PHP gs_cols_to_number(): grid span -> visible slide count.
	 * Admin stores bootstrap spans (12=1 col, 6=2, 4=3, 3=4, 2_4=5, 2=6).
	 */
	window.gs_teca_grid_column_to_slides_per_view = function( gridColumn, fallback ) {
		var raw = gridColumn;

		if ( raw === undefined || raw === null || raw === '' ) {
			raw = fallback;
		}

		if ( raw === undefined || raw === null || raw === '' ) {
			return 1;
		}

		var span = parseFloat( String( raw ).replace( '_', '.' ) );

		if ( ! Number.isFinite( span ) || span <= 0 ) {
			var fallbackSpan = parseFloat( String( fallback || '12' ).replace( '_', '.' ) );

			if ( ! Number.isFinite( fallbackSpan ) || fallbackSpan <= 0 ) {
				return 1;
			}

			span = fallbackSpan;
		}

		return 12 / span;
	};

	window.gs_teca_get_layout_width = function( $el ) {
		var node = $el && $el[0];

		if ( ! node ) {
			return 0;
		}

		var rect = node.getBoundingClientRect();

		return Math.max( 0, rect.width || node.offsetWidth || 0 );
	};

	window.gs_teca_when_carousel_ready = function( $el, callback ) {
		var attempts = 0;
		var maxAttempts = 80;
		var done = false;

		function finish() {
			if ( done ) {
				return;
			}

			done = true;
			callback( window.gs_teca_get_layout_width( $el ) );
		}

		function tick() {
			attempts++;

			var width = window.gs_teca_get_layout_width( $el );
			var visible = $el.is( ':visible' ) && width > 0;

			if ( visible || attempts >= maxAttempts ) {
				finish();
				return;
			}

			requestAnimationFrame( tick );
		}

		tick();

		if ( 'IntersectionObserver' in window && $el[0] ) {
			var observer = new IntersectionObserver( function( entries ) {
				entries.forEach( function( entry ) {
					if ( entry.isIntersecting && ! done ) {
						observer.disconnect();
						tick();
					}
				} );
			}, { threshold: 0.01 } );

			observer.observe( $el[0] );
		}

		$( window ).one( 'load.gsTecaCarousel', function() {
			if ( ! done ) {
				tick();
			}
		} );
	};

})( jQuery );
