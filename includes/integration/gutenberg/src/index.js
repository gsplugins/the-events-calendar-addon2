import './editor.scss';

( function( wp ) {
	const { blocks, serverSideRender, i18n, element } = wp;
	const { registerBlockType } = blocks;
	const ServerSideRender = serverSideRender;
	const { __ } = i18n;
	const { createElement: el, Fragment } = element;

	let interval;
	let intervalCount = 0;

	function blockServerRenderScript() {
		if ( interval ) {
			clearInterval( interval );
		}

		intervalCount = 0;

		interval = setInterval( function() {
			if ( window.jQuery ) {
				window.jQuery( document ).trigger( 'gsteca:scripts:reprocess' );
			}

			if ( interval && intervalCount > 100 ) {
				clearInterval( interval );
			}

			intervalCount++;
		}, 200 );
	}

	function getDefaultShortcodeId() {
		if ( ! window.gs_teca_block || ! Array.isArray( window.gs_teca_block.shortcodes ) ) {
			return '';
		}

		return window.gs_teca_block.shortcodes[0] ? String( window.gs_teca_block.shortcodes[0].id ) : '';
	}

	function shortcodeExists( shortcodeId ) {
		if ( ! window.gs_teca_block || ! Array.isArray( window.gs_teca_block.shortcodes ) ) {
			return false;
		}

		return window.gs_teca_block.shortcodes.some( function( item ) {
			return String( item.id ) === String( shortcodeId );
		} );
	}

	function BlockIcon() {
		return el(
			'svg',
			{
				width: 24,
				height: 24,
				viewBox: '0 0 24 24',
				xmlns: 'http://www.w3.org/2000/svg',
				'aria-hidden': true,
				focusable: false,
			},
			el( 'path', {
				d: 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z',
			} )
		);
	}

	function BlockDisplay( props ) {
		const { attributes, setAttributes, className } = props;
		const shortcodeId = attributes.shortcode || '';
		const shortcodes = ( window.gs_teca_block && window.gs_teca_block.shortcodes ) || [];

		blockServerRenderScript();

		if ( ! shortcodes.length ) {
			return el(
				'div',
				{ className: 'teca--block-placeholder' },
				window.gs_teca_block.no_shortcodes_available
			);
		}

		function updateShortcodeId( event ) {
			setAttributes( {
				shortcode: event.target.value,
			} );
		}

		const options = shortcodes.map( function( item ) {
			return el(
				'option',
				{
					value: String( item.id ),
					key: String( item.id ),
				},
				item.shortcode_name
			);
		} );

		const selectedId = shortcodeId || getDefaultShortcodeId();
		const hasValidSelection = selectedId && shortcodeExists( selectedId );

		return el(
			'div',
			{ className: 'teca--block' },
			el(
				'div',
				{ className: 'teca--toolbar' },
				el(
					'label',
					null,
					window.gs_teca_block.select_shortcode
				),
				el(
					'select',
					{
						onChange: updateShortcodeId,
						value: selectedId,
					},
					options
				),
				el(
					'p',
					{ className: 'gs-teca-block--des' },
					el(
						'span',
						null,
						window.gs_teca_block.edit_description_text + ' ',
						el(
							'a',
							{
								href: window.gs_teca_block.edit_link + selectedId,
								target: '_blank',
								rel: 'noopener noreferrer',
							},
							window.gs_teca_block.edit_link_text
						)
					),
					el(
						'span',
						null,
						window.gs_teca_block.create_description_text + ' ',
						el(
							'a',
							{
								href: window.gs_teca_block.create_link,
								target: '_blank',
								rel: 'noopener noreferrer',
							},
							window.gs_teca_block.create_link_text
						)
					)
				)
			),
			hasValidSelection
				? el( ServerSideRender, {
					className: className,
					block: 'teca/events',
					attributes: {
						shortcode: selectedId,
						align: attributes.align,
					},
				} )
				: el(
					'div',
					{ className: 'teca--block-placeholder' },
					shortcodeId
						? window.gs_teca_block.shortcode_missing
						: window.gs_teca_block.no_shortcode_selected
				)
		);
	}

	registerBlockType( 'teca/events', {
		title: __( 'TECA Events', 'the-events-calendar-addon' ),
		description: __( 'Insert and display TECA event layouts.', 'the-events-calendar-addon' ),
		icon: BlockIcon,
		category: 'widgets',
		keywords: [ __( 'events', 'the-events-calendar-addon' ), __( 'calendar', 'the-events-calendar-addon' ), 'teca' ],
		supports: {
			align: [ 'wide', 'full' ],
		},
		attributes: {
			shortcode: {
				type: 'string',
				default: getDefaultShortcodeId(),
			},
			align: {
				type: 'string',
				default: 'wide',
			},
		},
		edit: BlockDisplay,
		save: function() {
			return null;
		},
	} );
}( window.wp ) );
