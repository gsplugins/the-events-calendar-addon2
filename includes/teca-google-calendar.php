<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build Google Calendar location string for an event.
 *
 * @param int $event_id Event ID.
 * @return string
 */
function teca_get_google_calendar_location( $event_id ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return '';
	}

	$map_data = teca_get_single_event_map_data( $event_id );

	if ( ! empty( $map_data['full_address'] ) ) {
		return trim( (string) $map_data['full_address'] );
	}

	if ( ! empty( $map_data['venue_name'] ) ) {
		return trim( (string) $map_data['venue_name'] );
	}

	return '';
}

/**
 * Build Google Calendar details text for an event.
 *
 * @param int $event_id Event ID.
 * @return string
 */
function teca_get_google_calendar_details( $event_id ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return '';
	}

	$excerpt   = teca_get_event_excerpt_text( $event_id, 40 );
	$permalink = get_permalink( $event_id );
	$parts     = array();

	if ( '' !== $excerpt ) {
		$parts[] = sprintf(
			/* translators: %s: event excerpt */
			__( 'Event details: %s', 'the-events-calendar-addon' ),
			$excerpt
		);
	}

	if ( $permalink ) {
		$parts[] = sprintf(
			/* translators: %s: event URL */
			__( 'View event: %s', 'the-events-calendar-addon' ),
			$permalink
		);
	}

	return trim( implode( "\n\n", $parts ) );
}

/**
 * Whether an event is all-day.
 *
 * @param int $event_id Event ID.
 * @return bool
 */
function teca_is_event_all_day( $event_id ) {
	$all_day = get_post_meta( $event_id, '_EventAllDay', true );

	if ( class_exists( '\Tribe__Date_Utils' ) && method_exists( '\Tribe__Date_Utils', 'is_all_day' ) ) {
		return (bool) \Tribe__Date_Utils::is_all_day( $all_day );
	}

	return (bool) $all_day;
}

/**
 * Get UTC timestamps for Google Calendar date formatting.
 *
 * @param int $event_id Event ID.
 * @return array{start:int,end:int}
 */
function teca_get_google_calendar_timestamps( $event_id ) {
	$event_id = (int) $event_id;
	$start_ts = 0;
	$end_ts   = 0;

	if ( class_exists( '\Tribe__Events__Timezones' ) ) {
		$start_ts = (int) \Tribe__Events__Timezones::event_start_timestamp( $event_id, 'UTC' );
		$end_ts   = (int) \Tribe__Events__Timezones::event_end_timestamp( $event_id, 'UTC' );
	} else {
		$start = (string) get_post_meta( $event_id, '_EventStartDate', true );
		$end   = (string) get_post_meta( $event_id, '_EventEndDate', true );

		if ( '' !== $start ) {
			$start_ts = (int) strtotime( $start . ' UTC' );
		}

		if ( '' !== $end ) {
			$end_ts = (int) strtotime( $end . ' UTC' );
		}
	}

	if ( $start_ts <= 0 ) {
		return array(
			'start' => 0,
			'end'   => 0,
		);
	}

	if ( $end_ts <= 0 || $end_ts < $start_ts ) {
		$end_ts = $start_ts + HOUR_IN_SECONDS;
	}

	return array(
		'start' => $start_ts,
		'end'   => $end_ts,
	);
}

/**
 * Format Google Calendar dates parameter.
 *
 * @param int  $event_id Event ID.
 * @param bool $all_day  Whether event is all-day.
 * @return string
 */
function teca_format_google_calendar_dates( $event_id, $all_day = false ) {
	$timestamps = teca_get_google_calendar_timestamps( $event_id );

	if ( $timestamps['start'] <= 0 ) {
		return '';
	}

	if ( $all_day ) {
		$start_day = gmdate( 'Ymd', $timestamps['start'] );
		$end_day   = gmdate( 'Ymd', $timestamps['end'] );

		if ( $end_day > $start_day ) {
			return $start_day . '/' . $end_day;
		}

		return $start_day . '/' . gmdate( 'Ymd', $timestamps['start'] + DAY_IN_SECONDS );
	}

	return gmdate( 'Ymd\THis\Z', $timestamps['start'] ) . '/' . gmdate( 'Ymd\THis\Z', $timestamps['end'] );
}

/**
 * Build Google Calendar add-event URL for an event.
 *
 * @param int $event_id Event ID.
 * @return string
 */
function teca_get_google_calendar_url( $event_id ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return '';
	}

	$post = get_post( $event_id );

	if ( ! $post || 'tribe_events' !== $post->post_type ) {
		return '';
	}

	$title = get_the_title( $event_id );

	if ( '' === trim( $title ) ) {
		return '';
	}

	$dates = teca_format_google_calendar_dates( $event_id, teca_is_event_all_day( $event_id ) );

	if ( '' === $dates ) {
		return '';
	}

	$params = array(
		'action' => 'TEMPLATE',
		'text'   => $title,
		'dates'  => $dates,
	);

	$details = teca_get_google_calendar_details( $event_id );

	if ( '' !== $details ) {
		$params['details'] = $details;
	}

	$location = teca_get_google_calendar_location( $event_id );

	if ( '' !== $location ) {
		$params['location'] = $location;
	}

	$url = add_query_arg( $params, 'https://calendar.google.com/calendar/render' );

	return (string) apply_filters( 'teca_google_calendar_url', $url, $event_id );
}

/**
 * Google Calendar button label.
 *
 * @param string $context Render context.
 * @return string
 */
function teca_get_google_calendar_button_label( $context = 'card' ) {
	return teca_get_add_to_calendar_button_text( $context );
}

/**
 * Context-specific Google Calendar button defaults.
 *
 * @param string $context Render context.
 * @return string
 */
function teca_get_google_calendar_button_label_default( $context = 'card' ) {
	$context = sanitize_key( (string) $context );

	if ( 'compact' === $context ) {
		return __( 'Google Cal', 'the-events-calendar-addon' );
	}

	if ( 'table' === $context ) {
		return __( 'Add to Calendar', 'the-events-calendar-addon' );
	}

	return __( 'Add to calendar', 'the-events-calendar-addon' );
}

/**
 * Render Google Calendar button markup.
 *
 * @param int                  $event_id Event ID.
 * @param string               $context  card|popup|single|compact.
 * @param array<string, mixed> $args     Optional args.
 */
function teca_render_google_calendar_button( $event_id, $context = 'card', array $args = array() ) {
	$event_id = teca_resolve_event_id( $event_id, $args['event'] ?? array() );

	if ( $event_id <= 0 ) {
		return;
	}

	if ( isset( $args['visible'] ) && ! $args['visible'] ) {
		return;
	}

	$url = ! empty( $args['google_calendar_url'] )
		? (string) $args['google_calendar_url']
		: teca_get_google_calendar_url( $event_id );

	if ( '' === $url ) {
		return;
	}

	$context = sanitize_key( (string) $context );
	$label   = isset( $args['label'] ) ? (string) $args['label'] : teca_get_add_to_calendar_button_text( $context );
	$classes = array(
		'teca-google-calendar-btn',
		'teca-event-button',
		'teca-google-calendar-btn--' . $context,
	);

	if ( 'popup' === $context ) {
		$classes[] = 'teca-popup-button';
	}

	if ( ! empty( $args['class'] ) ) {
		$classes[] = sanitize_html_class( (string) $args['class'] );
	}

	printf(
		'<a class="%1$s" href="%2$s" target="_blank" rel="noopener noreferrer"><span>%3$s</span></a>',
		esc_attr( implode( ' ', array_filter( $classes ) ) ),
		esc_url( $url ),
		esc_html( $label )
	);
}

/**
 * Get Google Calendar button anchor HTML.
 *
 * @param int                  $event_id Event ID.
 * @param string               $context  card|list|table|popup|single|compact.
 * @param array<string, mixed> $args     Optional args (visibility_settings, google_calendar_url, class, label).
 * @return string
 */
function teca_get_google_calendar_button_html( $event_id, $context = 'card', array $args = array() ) {
	$event_id = teca_resolve_event_id( $event_id, $args['event'] ?? array() );

	if ( $event_id <= 0 ) {
		return '';
	}

	$visibility_settings = $args['visibility_settings'] ?? null;

	if ( ! teca_is_google_calendar_button_visible( $visibility_settings ) ) {
		return '';
	}

	ob_start();
	teca_render_google_calendar_button( $event_id, $context, $args );
	return trim( (string) ob_get_clean() );
}

/**
 * Resolve event ID from explicit ID or event data array.
 *
 * @param int              $event_id Event ID.
 * @param array|string|int $event    Event row or ID fallback.
 * @return int
 */
function teca_resolve_event_id( $event_id, $event = array() ) {
	$event_id = (int) $event_id;

	if ( $event_id > 0 ) {
		return $event_id;
	}

	if ( is_array( $event ) && ! empty( $event['event_id'] ) ) {
		return (int) $event['event_id'];
	}

	if ( is_numeric( $event ) ) {
		return (int) $event;
	}

	return 0;
}

/**
 * Echo Google Calendar button inside the standard actions wrapper.
 *
 * @param int                  $event_id            Event ID.
 * @param string               $context             Button context.
 * @param array|null           $visibility_settings Visibility settings.
 * @param string               $outer_wrapper_class Optional outer wrapper class.
 * @param array<string, mixed> $args                Optional render args.
 */
function teca_echo_google_calendar_button_actions( $event_id, $context = 'card', $visibility_settings = null, $outer_wrapper_class = '', array $args = array() ) {
	$args['visibility_settings'] = $visibility_settings;
	$button_html                   = teca_get_google_calendar_button_html( $event_id, $context, $args );

	if ( '' === $button_html ) {
		return;
	}

	$visibility_field = teca_get_card_visibility_settings( $visibility_settings )['google_calendar_button'] ?? true;
	$context          = sanitize_key( (string) $context );
	$wrapper_class    = 'teca-google-calendar-actions teca-event-actions';

	if ( 'compact' !== $context ) {
		$wrapper_class .= ' teca-google-calendar-actions--' . $context;
	}

	$outer = trim( (string) $outer_wrapper_class );

	if ( '' !== $outer ) {
		$outer_classes = implode( ' ', Helpers::get_visible_classes( $visibility_field, $outer ) );
		$inner         = '<div class="' . esc_attr( $wrapper_class ) . '">' . $button_html . '</div>';
		echo '<div class="' . esc_attr( $outer_classes ) . '">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	$inner_classes = implode( ' ', Helpers::get_visible_classes( $visibility_field, $wrapper_class ) );
	echo '<div class="' . esc_attr( $inner_classes ) . '">' . $button_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Whether Google Calendar button should render for visibility settings.
 *
 * @param array|null $visibility_settings Visibility settings.
 * @return bool
 */
function teca_is_google_calendar_button_visible( $visibility_settings = null ) {
	return teca_is_card_field_visible( 'google_calendar_button', $visibility_settings );
}
