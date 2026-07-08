( function( $ ) {
	'use strict';

	class GsTecaEvents extends React.Component {
		static slug = 'gs_teca_events';

		componentDidMount() {
			this.triggerScriptProcess();
		}

		componentDidUpdate() {
			this.triggerScriptProcess();
		}

		triggerScriptProcess() {
			let count = 0;
			const interval = setInterval( function() {
				$( document ).trigger( 'gsteca:scripts:reprocess' );

				if ( count > 20 ) {
					clearInterval( interval );
				}

				count++;
			}, 100 );
		}

		render() {
			return React.createElement( 'div', {
				className: 'gs-teca-divi',
				dangerouslySetInnerHTML: { __html: this.props.__shortcode || '' },
			} );
		}
	}

	$( window ).on( 'et_builder_api_ready', function( event, API ) {
		API.registerModules( [ GsTecaEvents ] );
	} );
}( jQuery ) );
