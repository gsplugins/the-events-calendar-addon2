<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Events_Section_Renderer {

	public static function is_events_section_view_type( $view_type ) {
		return 'events-section' === (string) $view_type;
	}

	/**
	 * @param array $settings   Shortcode settings.
	 * @param array $ajax_datas Optional AJAX/preview context.
	 */
	public static function render_layout( array $settings, array $ajax_datas = array() ) {
		$layout              = teca_get_selected_event_layout( $settings );
		$events_section_data = ! empty( $ajax_datas['events_section_data'] ) && is_array( $ajax_datas['events_section_data'] )
			? $ajax_datas['events_section_data']
			: Query::get_events_section_data( $settings, $ajax_datas );

		$featured_events = $events_section_data['featured_events'] ?? array();
		$past_events     = $events_section_data['past_events'] ?? array();
		$upcoming_events = $events_section_data['upcoming_events'] ?? array();

		ob_start();

		$template = Template_Loader::locate_template( 'events/' . $layout . '.php' );

		if ( ! is_wp_error( $template ) ) {
			$event_layout = $layout;
			include $template;
		}

		return (string) ob_get_clean();
	}
}
