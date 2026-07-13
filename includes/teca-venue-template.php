<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function teca_is_venue_template_view_type( $view_type ) {
	return Venue_Template_Renderer::is_venue_template_view_type( $view_type );
}

function teca_get_venue_template_layout_options() {
	return array(
		'layout-1',
		'layout-2',
		'layout-3',
	);
}

function teca_get_selected_venue_template_layout( array $settings ) {
	$default = 'layout-1';
	$value   = sanitize_key( (string) ( $settings['venue_template_layout'] ?? $default ) );
	$valid   = teca_get_venue_template_layout_options();

	if ( ! in_array( $value, $valid, true ) ) {
		return $default;
	}

	return $value;
}

function teca_sanitize_venue_template_settings( array $shortcode_settings ) {
	$shortcode_settings['venue_template_layout'] = teca_get_selected_venue_template_layout( $shortcode_settings );

	return $shortcode_settings;
}

function teca_get_venue_template_area_layout_class( array $settings ) {
	if ( 'venue_template' !== ( $settings['view_type'] ?? '' ) ) {
		return '';
	}

	$layout = teca_get_selected_venue_template_layout( $settings );

	if ( 'layout-1' === $layout ) {
		return 'teca-has-venue-template-layout-1';
	}

	if ( 'layout-2' === $layout ) {
		return 'teca-has-venue-template-layout-2';
	}

	if ( 'layout-3' === $layout ) {
		return 'teca-has-venue-template-layout-3';
	}

	return 'teca-has-venue-template-' . sanitize_html_class( str_replace( 'layout-', 'layout-', $layout ) );
}

function teca_render_venue_template_layout( array $settings, array $ajax_datas = array() ) {
	return Venue_Template_Renderer::render_layout( $settings, $ajax_datas );
}

/**
 * Build a comma-separated full address from venue parts.
 *
 * @param array $venue Venue data array.
 * @return string
 */
function teca_build_venue_full_address( array $venue ) {
	$parts = array();

	if ( ! empty( $venue['address'] ) ) {
		$parts[] = trim( (string) $venue['address'] );
	}

	$locality = '';

	if ( ! empty( $venue['city'] ) ) {
		$locality = trim( (string) $venue['city'] );
	}

	if ( ! empty( $venue['state'] ) ) {
		$locality .= ( $locality ? ', ' : '' ) . trim( (string) $venue['state'] );
	}

	if ( ! empty( $venue['zip'] ) ) {
		$locality .= ( $locality ? ' ' : '' ) . trim( (string) $venue['zip'] );
	}

	if ( $locality ) {
		$parts[] = $locality;
	}

	if ( ! empty( $venue['country'] ) ) {
		$parts[] = trim( (string) $venue['country'] );
	}

	return implode( ', ', $parts );
}

/**
 * Short location label for chips, e.g. "Sacramento, CA".
 *
 * @param array $venue Venue data array.
 * @return string
 */
function teca_get_venue_location_chip_label( array $venue ) {
	$city  = trim( (string) ( $venue['city'] ?? '' ) );
	$state = trim( (string) ( $venue['state'] ?? '' ) );

	if ( $city && $state ) {
		return $city . ', ' . $state;
	}

	if ( $city ) {
		return $city;
	}

	if ( $state ) {
		return $state;
	}

	if ( ! empty( $venue['country'] ) ) {
		return trim( (string) $venue['country'] );
	}

	return '';
}

/**
 * @param int $count Upcoming event count.
 * @return string
 */
function teca_get_venue_upcoming_count_chip_label( $count ) {
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
 * @param int $count Upcoming event count.
 * @return string
 */
function teca_get_venue_upcoming_count_label( $count ) {
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
 * @param array $settings Optional settings (reserved for future use).
 * @return array<int, array<string, mixed>>
 */
function teca_get_all_venues_data( array $settings = array() ) {
	return Query::get_all_venues_template_data( $settings );
}

/**
 * @param array $venue Venue data.
 * @return string Initial letter for fallback media.
 */
function teca_get_venue_fallback_initial( array $venue ) {
	$title = trim( (string) ( $venue['title'] ?? '' ) );

	if ( '' === $title ) {
		return 'V';
	}

	if ( function_exists( 'mb_substr' ) ) {
		return mb_strtoupper( mb_substr( $title, 0, 1 ) );
	}

	return strtoupper( substr( $title, 0, 1 ) );
}
