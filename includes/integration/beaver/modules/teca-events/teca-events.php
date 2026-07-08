<?php

namespace GS_TECA;

use FLBuilderModule;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder module for rendering saved TECA shortcodes.
 */
class Teca_Beaver_Events_Module extends FLBuilderModule {

	/**
	 * Register the module with Beaver Builder.
	 */
	public function __construct() {
		parent::__construct(
			[
				'name'            => esc_html__( 'TECA Events', 'the-events-calendar-addon' ),
				'description'     => esc_html__( 'Display TECA events/layouts using saved TECA shortcodes.', 'the-events-calendar-addon' ),
				'group'           => esc_html__( 'GS Plugins', 'the-events-calendar-addon' ),
				'category'        => esc_html__( 'Basic', 'the-events-calendar-addon' ),
				'dir'             => GS_TECA_PLUGIN_DIR . 'includes/integration/beaver/modules/teca-events/',
				'url'             => GS_TECA_PLUGIN_URI . 'includes/integration/beaver/modules/teca-events/',
				'icon'            => 'events.svg',
				'editor_export'   => true,
				'enabled'         => true,
				'partial_refresh' => true,
			]
		);
	}

	/**
	 * Return an SVG icon for the module list.
	 *
	 * @param string $icon Icon filename.
	 * @return string
	 */
	public function get_icon( $icon = '' ) {
		$path = GS_TECA_PLUGIN_DIR . 'assets/img/events.svg';

		if ( '' === $icon || ! file_exists( $path ) ) {
			return '';
		}

		$icon_markup = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $icon_markup ) {
			return '';
		}

		$icon_markup = str_replace( 'width="150"', 'width="20"', $icon_markup );
		$icon_markup = str_replace( 'height="150"', 'height="20"', $icon_markup );

		return $icon_markup;
	}
}
