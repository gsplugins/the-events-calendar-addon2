<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Organizer_Template_Renderer {

	public static function is_organizer_template_view_type( $view_type ) {
		return 'organizer_template' === (string) $view_type;
	}

	/**
	 * @param array $settings   Shortcode settings.
	 * @param array $ajax_datas Optional AJAX/preview context.
	 */
	public static function render_layout( array $settings, array $ajax_datas = array() ) {
		$layout     = teca_get_selected_organizer_template_layout( $settings );
		$organizers = ! empty( $ajax_datas['organizers'] ) && is_array( $ajax_datas['organizers'] )
			? $ajax_datas['organizers']
			: teca_get_all_organizers_data( $settings );

		ob_start();

		$template = Template_Loader::locate_template( 'organizers/' . $layout . '.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}
}
