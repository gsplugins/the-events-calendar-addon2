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
 * Whether TEC has already generated the derived event meta from a normal save/update.
 *
 * @param int $event_id Event post ID.
 * @return bool
 */
function teca_event_has_complete_tec_meta( $event_id ) {
	$event_id  = (int) $event_id;
	$start     = trim( (string) get_post_meta( $event_id, '_EventStartDate', true ) );
	$end       = trim( (string) get_post_meta( $event_id, '_EventEndDate', true ) );
	$start_utc = trim( (string) get_post_meta( $event_id, '_EventStartDateUTC', true ) );
	$end_utc   = trim( (string) get_post_meta( $event_id, '_EventEndDateUTC', true ) );

	if ( '' === $start || '' === $end ) {
		return false;
	}

	if ( teca_event_datetime_needs_time_fallback( $start ) || teca_event_datetime_needs_time_fallback( $end ) ) {
		return false;
	}

	return '' !== $start_utc && '' !== $end_utc;
}

/**
 * Build TEC API date/time args the same way the event editor does.
 *
 * TEC only generates UTC/duration meta when separate date + hour/minute fields are provided.
 *
 * @param string $start_datetime Start datetime (Y-m-d H:i:s).
 * @param string $end_datetime   End datetime (Y-m-d H:i:s).
 * @return array<string, mixed>
 */
function teca_build_tec_event_datetime_args( $start_datetime, $end_datetime ) {
	$pair      = teca_normalize_event_datetime_pair( $start_datetime, $end_datetime );
	$start_ts  = strtotime( $pair['start'] );
	$end_ts    = strtotime( $pair['end'] );

	if ( ! $start_ts ) {
		return array();
	}

	if ( ! $end_ts ) {
		$end_ts = $start_ts + HOUR_IN_SECONDS;
	}

	return array(
		'EventStartDate'   => wp_date( 'Y-m-d', $start_ts ),
		'EventEndDate'     => wp_date( 'Y-m-d', $end_ts ),
		'EventStartHour'   => wp_date( 'H', $start_ts ),
		'EventStartMinute' => wp_date( 'i', $start_ts ),
		'EventEndHour'     => wp_date( 'H', $end_ts ),
		'EventEndMinute'   => wp_date( 'i', $end_ts ),
		'EventAllDay'      => false,
	);
}

/**
 * Force a demo event post to remain published (never scheduled/future).
 *
 * WordPress converts publish + future post_date into the `future` status.
 * Demo events must stay published while event dates live in TEC meta.
 *
 * @param int $event_id Event post ID.
 * @return void
 */
function teca_force_demo_event_published( $event_id ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return;
	}

	$post_now     = current_time( 'mysql' );
	$post_now_gmt = current_time( 'mysql', true );

	wp_update_post(
		array(
			'ID'            => $event_id,
			'post_status'   => 'publish',
			'post_date'     => $post_now,
			'post_date_gmt' => $post_now_gmt,
			'edit_date'     => true,
		),
		true
	);
}

/**
 * Sync an event through TEC's save pipeline so all derived meta is generated.
 *
 * @param int    $event_id      Event post ID.
 * @param string $fallback_date Optional fallback source (e.g. post_date).
 * @param bool   $force_demo    Force sync for demo events.
 * @return bool True when sync ran.
 */
function teca_sync_event_via_tec( $event_id, $fallback_date = '', $force_demo = false ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return false;
	}

	$is_demo = $force_demo || ! empty( get_post_meta( $event_id, 'gsteca-demo_data', true ) );
	$start   = (string) get_post_meta( $event_id, '_EventStartDate', true );
	$end     = (string) get_post_meta( $event_id, '_EventEndDate', true );

	if ( ! $is_demo && teca_event_has_complete_tec_meta( $event_id ) ) {
		return false;
	}

	$normalized = teca_normalize_event_datetime_pair( $start, $end, $fallback_date );

	if ( '' === $normalized['start'] || '' === $normalized['end'] ) {
		return false;
	}

	$datetime_args = teca_build_tec_event_datetime_args( $normalized['start'], $normalized['end'] );

	if ( empty( $datetime_args ) ) {
		return false;
	}

	if ( ! $is_demo && teca_event_has_complete_tec_meta( $event_id ) ) {
		return false;
	}

	if ( function_exists( 'tribe_update_event' ) ) {
		$update_args = $datetime_args;

		if ( $is_demo ) {
			$update_args['post_status'] = 'publish';
		}

		$updated = tribe_update_event( $event_id, $update_args );

		if ( $updated && $is_demo ) {
			teca_force_demo_event_published( $event_id );
		}

		if ( $updated ) {
			teca_update_event_custom_tables( $event_id );
		}

		return (bool) $updated;
	}

	if ( class_exists( 'Tribe__Events__API' ) ) {
		\Tribe__Events__API::saveEventMeta( $event_id, $datetime_args, get_post( $event_id ) );

		if ( $is_demo ) {
			teca_force_demo_event_published( $event_id );
		}

		return true;
	}

	update_post_meta( $event_id, '_EventStartDate', $normalized['start'] );
	update_post_meta( $event_id, '_EventEndDate', $normalized['end'] );
	update_post_meta( $event_id, '_EventAllDay', '0' );
	update_post_meta( $event_id, '_EventDuration', max( 0, strtotime( $normalized['end'] ) - strtotime( $normalized['start'] ) ) );

	if ( $is_demo ) {
		teca_force_demo_event_published( $event_id );
	}

	return true;
}

/**
 * Backward-compatible wrapper used by demo repair helpers.
 *
 * @param int    $event_id      Event post ID.
 * @param string $fallback_date Optional fallback source (e.g. post_date).
 * @param bool   $force_demo    Force sync for demo events.
 * @return bool
 */
function teca_ensure_event_datetime_meta( $event_id, $fallback_date = '', $force_demo = false ) {
	return teca_sync_event_via_tec( $event_id, $fallback_date, $force_demo );
}

/**
 * Upsert TEC custom-table rows for a single event when custom tables are enabled.
 *
 * @param int $event_id Event post ID.
 * @return void
 */
function teca_update_event_custom_tables( $event_id ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 || ! class_exists( '\TEC\Events\Custom_Tables\V1\Updates\Events' ) ) {
		return;
	}

	( new \TEC\Events\Custom_Tables\V1\Updates\Events() )->update( $event_id );
}

/**
 * Clear caches/transients after demo import so events are queryable immediately.
 *
 * @return void
 */
function teca_flush_event_caches_after_demo_import() {
	delete_transient( 'gsteca_dummy_events' );

	if ( class_exists( 'Tribe__Events__Dates__Known_Range' ) ) {
		\Tribe__Events__Dates__Known_Range::instance()->rebuild_known_range();
	}

	if ( function_exists( 'tribe' ) && tribe()->bound( 'tec.events.custom-tables-v1.updates' ) ) {
		tribe( 'tec.events.custom-tables-v1.updates' )->commit_updates();
	}

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
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

/**
 * Create a demo event using TEC's native save pipeline when available.
 *
 * @param array $event Event definition.
 * @param int   $index Demo event index.
 * @return int Event post ID or 0 on failure.
 */
function teca_insert_demo_event( array $event, $index ) {
	$schedule         = teca_get_demo_event_datetime_pair( (int) $index );
	$datetime_args    = teca_build_tec_event_datetime_args( $schedule['start'], $schedule['end'] );
	$thumbnail_id     = 0;
	$thumbnail_source = $event['meta_input']['_thumbnail_id'] ?? '';

	if ( is_numeric( $thumbnail_source ) ) {
		$thumbnail_id = (int) $thumbnail_source;
	}

	if ( empty( $datetime_args ) ) {
		return 0;
	}

	$post_now     = current_time( 'mysql' );
	$post_now_gmt = current_time( 'mysql', true );

	$args = array_merge(
		array(
			'post_title'    => $event['post_title'] ?? '',
			'post_content'  => $event['post_content'] ?? '',
			'post_status'   => 'publish',
			'post_date'     => $post_now,
			'post_date_gmt' => $post_now_gmt,
			'edit_date'     => true,
			'tax_input'     => $event['tax_input'] ?? array(),
		),
		$datetime_args
	);

	if ( $thumbnail_id > 0 ) {
		$args['FeaturedImage'] = $thumbnail_id;
	}

	if ( function_exists( 'tribe_create_event' ) ) {
		$post_id = tribe_create_event( $args );
	} else {
		$insert_args = array_merge(
			$event,
			array(
				'post_status'   => 'publish',
				'post_date'     => $post_now,
				'post_date_gmt' => $post_now_gmt,
				'edit_date'     => true,
				'meta_input'    => array(
					'_thumbnail_id'   => $thumbnail_id,
					'_EventStartDate' => $schedule['start'],
					'_EventEndDate'   => $schedule['end'],
					'_EventAllDay'    => '0',
					'_EventDuration'  => HOUR_IN_SECONDS,
				),
			)
		);

		$post_id = wp_insert_post( $insert_args );
	}

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	add_post_meta( (int) $post_id, 'gsteca-demo_data', 1, true );
	teca_force_demo_event_published( (int) $post_id );
	teca_sync_event_via_tec( (int) $post_id, $schedule['start'], true );

	return (int) $post_id;
}
