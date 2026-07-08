<?php
namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Default start time used when an event has a date but no time value.
 *
 * @return string
 */
function teca_get_default_event_start_time() {
	return '09:00:00';
}

/**
 * Whether a stored datetime string contains only a date (no time component).
 *
 * @param string $datetime Datetime string.
 * @return bool
 */
function teca_event_datetime_is_date_only( $datetime ) {
	$datetime = trim( (string) $datetime );

	return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $datetime );
}

/**
 * Whether an event datetime value is missing or incomplete and needs a fallback time.
 *
 * @param string $datetime Datetime string.
 * @return bool
 */
function teca_event_datetime_needs_time_fallback( $datetime ) {
	$datetime = trim( (string) $datetime );

	if ( '' === $datetime ) {
		return true;
	}

	return teca_event_datetime_is_date_only( $datetime );
}

/**
 * Normalize start/end datetimes in memory without persisting.
 *
 * @param string $start         Event start datetime.
 * @param string $end           Event end datetime.
 * @param string $fallback_date Optional fallback source (e.g. post_date).
 * @return array{start:string,end:string}
 */
function teca_normalize_event_datetime_pair( $start, $end, $fallback_date = '' ) {
	$start        = trim( (string) $start );
	$end          = trim( (string) $end );
	$default_time = teca_get_default_event_start_time();

	if ( '' === $start && '' !== trim( (string) $fallback_date ) ) {
		$fallback_ts = strtotime( (string) $fallback_date );

		if ( $fallback_ts ) {
			$start = wp_date( 'Y-m-d', $fallback_ts ) . ' ' . $default_time;
		}
	}

	if ( teca_event_datetime_is_date_only( $start ) ) {
		$start = $start . ' ' . $default_time;
	}

	if ( '' === $start ) {
		return array(
			'start' => '',
			'end'   => $end,
		);
	}

	$start_ts = strtotime( $start );

	if ( ! $start_ts ) {
		return array(
			'start' => $start,
			'end'   => $end,
		);
	}

	if ( '' === $end || teca_event_datetime_needs_time_fallback( $end ) ) {
		if ( teca_event_datetime_is_date_only( $end ) ) {
			$end = $end . ' ' . wp_date( 'H:i:s', $start_ts + HOUR_IN_SECONDS );
		} else {
			$end = wp_date( 'Y-m-d H:i:s', $start_ts + HOUR_IN_SECONDS );
		}
	}

	return array(
		'start' => $start,
		'end'   => $end,
	);
}

/**
 * Whether an event already has complete start/end datetimes and should not be changed.
 *
 * @param string $start Event start datetime.
 * @param string $end   Event end datetime.
 * @return bool
 */
function teca_event_datetime_is_complete( $start, $end ) {
	$start = trim( (string) $start );
	$end   = trim( (string) $end );

	if ( '' === $start || '' === $end ) {
		return false;
	}

	if ( teca_event_datetime_needs_time_fallback( $start ) || teca_event_datetime_needs_time_fallback( $end ) ) {
		return false;
	}

	return (bool) strtotime( $start ) && (bool) strtotime( $end );
}

/**
 * Persist missing/incomplete event datetime meta for demo imports or empty time values.
 *
 * @param int    $event_id      Event post ID.
 * @param string $fallback_date Optional fallback source (e.g. post_date).
 * @param bool   $force_demo    Force repair for demo events even when partially set.
 * @return bool True when meta was updated.
 */
function teca_ensure_event_datetime_meta( $event_id, $fallback_date = '', $force_demo = false ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return false;
	}

	$is_demo = $force_demo || ! empty( get_post_meta( $event_id, 'gsteca-demo_data', true ) );
	$start   = (string) get_post_meta( $event_id, '_EventStartDate', true );
	$end     = (string) get_post_meta( $event_id, '_EventEndDate', true );

	if ( ! $is_demo && teca_event_datetime_is_complete( $start, $end ) ) {
		return false;
	}

	if ( ! $is_demo && ! teca_event_datetime_needs_time_fallback( $start ) && '' !== $start && '' !== $end ) {
		return false;
	}

	$normalized = teca_normalize_event_datetime_pair( $start, $end, $fallback_date );

	if ( '' === $normalized['start'] || '' === $normalized['end'] ) {
		return false;
	}

	if ( $start === $normalized['start'] && $end === $normalized['end'] ) {
		return false;
	}

	if ( function_exists( 'tribe_update_event' ) ) {
		$updated = tribe_update_event(
			$event_id,
			array(
				'EventStartDate' => $normalized['start'],
				'EventEndDate'   => $normalized['end'],
				'EventAllDay'    => false,
			)
		);

		return (bool) $updated;
	}

	update_post_meta( $event_id, '_EventStartDate', $normalized['start'] );
	update_post_meta( $event_id, '_EventEndDate', $normalized['end'] );
	update_post_meta( $event_id, '_EventAllDay', '0' );
	update_post_meta( $event_id, '_EventDuration', max( 0, strtotime( $normalized['end'] ) - strtotime( $normalized['start'] ) ) );

	return true;
}

/**
 * Build demo event start/end datetimes spread across upcoming days.
 *
 * @param int $index Zero-based demo event index.
 * @return array{start:string,end:string}
 */
function teca_get_demo_event_datetime_pair( $index ) {
	$index    = max( 0, (int) $index );
	$day      = 1 + ( $index * 4 );
	$hour     = 9 + ( $index % 4 );
	$start_ts = strtotime( sprintf( '+%d days %02d:00:00', $day, $hour ) );

	if ( ! $start_ts ) {
		$start_ts = time() + DAY_IN_SECONDS;
	}

	return array(
		'start' => wp_date( 'Y-m-d H:i:s', $start_ts ),
		'end'   => wp_date( 'Y-m-d H:i:s', $start_ts + HOUR_IN_SECONDS ),
	);
}
