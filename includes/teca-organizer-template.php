<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function teca_is_organizer_template_view_type( $view_type ) {
	return Organizer_Template_Renderer::is_organizer_template_view_type( $view_type );
}

function teca_get_organizer_template_layout_options() {
	return array(
		'layout-1',
		'layout-2',
		'layout-3',
	);
}

function teca_get_selected_organizer_template_layout( array $settings ) {
	$default = 'layout-1';
	$value   = sanitize_key( (string) ( $settings['organizer_template_layout'] ?? $default ) );
	$valid   = teca_get_organizer_template_layout_options();

	if ( ! in_array( $value, $valid, true ) ) {
		return $default;
	}

	return $value;
}

function teca_sanitize_organizer_template_settings( array $shortcode_settings ) {
	$shortcode_settings['organizer_template_layout'] = teca_get_selected_organizer_template_layout( $shortcode_settings );

	return $shortcode_settings;
}

function teca_get_organizer_template_area_layout_class( array $settings ) {
	if ( 'organizer_template' !== ( $settings['view_type'] ?? '' ) ) {
		return '';
	}

	$layout = teca_get_selected_organizer_template_layout( $settings );

	if ( 'layout-1' === $layout ) {
		return 'teca-has-organizer-template-layout-1';
	}

	if ( 'layout-2' === $layout ) {
		return 'teca-has-organizer-template-layout-2';
	}

	if ( 'layout-3' === $layout ) {
		return 'teca-has-organizer-template-layout-3';
	}

	return 'teca-has-organizer-template-' . sanitize_html_class( str_replace( 'layout-', 'layout-', $layout ) );
}

function teca_render_organizer_template_layout( array $settings, array $ajax_datas = array() ) {
	return Organizer_Template_Renderer::render_layout( $settings, $ajax_datas );
}

/**
 * @param array $settings Optional settings (reserved for future use).
 * @return array<int, array<string, mixed>>
 */
function teca_get_all_organizers_data( array $settings = array() ) {
	return Query::get_all_organizers_template_data( $settings );
}

/**
 * @param int $count Upcoming event count.
 * @return string
 */
function teca_get_organizer_upcoming_count_label( $count ) {
	$count = absint( $count );

	if ( 0 === $count ) {
		return __( '0 Upcoming Events', 'the-events-calendar-addon' );
	}

	if ( 1 === $count ) {
		return __( '1 Upcoming Event', 'the-events-calendar-addon' );
	}

	return sprintf(
		/* translators: %d: number of upcoming events */
		__( '%d Upcoming Events', 'the-events-calendar-addon' ),
		$count
	);
}

/**
 * @param int $count Upcoming event count.
 * @return string
 */
function teca_get_organizer_upcoming_count_chip_label( $count ) {
	$count = absint( $count );

	if ( 1 === $count ) {
		return __( '1 Event', 'the-events-calendar-addon' );
	}

	return sprintf(
		/* translators: %d: number of upcoming events */
		__( '%d Events', 'the-events-calendar-addon' ),
		$count
	);
}

/**
 * @param array $organizer Organizer data.
 * @return string
 */
function teca_get_organizer_fallback_initial( array $organizer ) {
	$title = trim( (string) ( $organizer['title'] ?? '' ) );

	if ( '' === $title ) {
		return 'O';
	}

	if ( function_exists( 'mb_substr' ) ) {
		return mb_strtoupper( mb_substr( $title, 0, 1 ) );
	}

	return strtoupper( substr( $title, 0, 1 ) );
}

/**
 * @param array $organizer Organizer data.
 * @return string
 */
function teca_get_organizer_excerpt_display( array $organizer ) {
	$excerpt = trim( (string) ( $organizer['excerpt'] ?? '' ) );

	if ( '' !== $excerpt ) {
		return wp_strip_all_tags( $excerpt );
	}

	$description = trim( (string) ( $organizer['description'] ?? '' ) );

	if ( '' === $description ) {
		return '';
	}

	return wp_trim_words( wp_strip_all_tags( $description ), 22, '…' );
}
