<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Venue_Template_Renderer {

	public static function is_venue_template_view_type( $view_type ) {
		return 'venue_template' === (string) $view_type;
	}

	/**
	 * @param array $settings   Shortcode settings.
	 * @param array $ajax_datas Optional AJAX/preview context.
	 */
	public static function render_layout( array $settings, array $ajax_datas = array() ) {
		$layout = teca_get_selected_venue_template_layout( $settings );
		$venues = ! empty( $ajax_datas['venues'] ) && is_array( $ajax_datas['venues'] )
			? $ajax_datas['venues']
			: teca_get_all_venues_data( $settings );

		ob_start();

		$template = Template_Loader::locate_template( 'venues/' . $layout . '.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}
}
