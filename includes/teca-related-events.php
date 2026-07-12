<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single-page related events setting keys.
 *
 * @return string[]
 */
function teca_get_single_related_events_setting_keys() {
	return array(
		'show_related_events',
		'related_events_title',
		'related_events_limit',
		'related_events_sources',
	);
}

/**
 * Popup related events setting keys.
 *
 * @return string[]
 */
function teca_get_popup_related_events_setting_keys() {
	return array(
		'popup_show_related_events',
		'popup_related_events_title',
		'popup_related_events_limit',
		'popup_related_events_sources',
	);
}

/**
 * Default related events values for a context.
 *
 * @param string $context single|popup.
 * @return array<string, mixed>
 */
function teca_get_related_events_default_settings( $context = 'single' ) {
	$defaults = array(
		'show'    => 'on',
		'title'   => __( 'Related Events', 'the-events-calendar-addon2' ),
		'limit'   => 3,
		'sources' => array( 'category', 'tag', 'venue', 'organizer', 'upcoming' ),
	);

	if ( 'popup' === $context ) {
		return array(
			'popup_show_related_events'    => $defaults['show'],
			'popup_related_events_title'   => $defaults['title'],
			'popup_related_events_limit'   => $defaults['limit'],
			'popup_related_events_sources' => $defaults['sources'],
		);
	}

	return array(
		'show_related_events'    => $defaults['show'],
		'related_events_title'   => $defaults['title'],
		'related_events_limit'   => $defaults['limit'],
		'related_events_sources' => $defaults['sources'],
	);
}

/**
 * Allowed related event source keys.
 *
 * @return string[]
 */
function teca_get_related_events_allowed_sources() {
	return array( 'category', 'tag', 'venue', 'organizer', 'upcoming' );
}

/**
 * Determine whether a related-events show flag is enabled.
 *
 * @param mixed $value Raw stored value.
 * @return bool
 */
function teca_is_related_events_enabled( $value ) {
	if ( is_bool( $value ) ) {
		return $value;
	}

	if ( is_numeric( $value ) ) {
		return (int) $value === 1;
	}

	$value = strtolower( trim( (string) $value ) );

	if ( in_array( $value, array( 'off', 'false', '0', 'no' ), true ) ) {
		return false;
	}

	return true;
}

/**
 * Normalize one related-events settings group.
 *
 * @param array<string, mixed> $values Raw values.
 * @return array<string, mixed>
 */
function teca_normalize_related_events_values( array $values ) {
	$defaults = array(
		'title'   => __( 'Related Events', 'the-events-calendar-addon2' ),
		'limit'   => 3,
		'sources' => teca_get_related_events_allowed_sources(),
	);

	$show = array_key_exists( 'show', $values ) ? $values['show'] : 'on';
	$show = teca_is_related_events_enabled( $show ) ? 'on' : 'off';

	$title = array_key_exists( 'title', $values )
		? sanitize_text_field( (string) $values['title'] )
		: $defaults['title'];

	$title = teca_get_related_events_title_text( $title );

	$limit = array_key_exists( 'limit', $values ) ? absint( $values['limit'] ) : (int) $defaults['limit'];
	if ( $limit < 1 ) {
		$limit = 1;
	}
	if ( $limit > 12 ) {
		$limit = 12;
	}

	$sources = array_key_exists( 'sources', $values ) && is_array( $values['sources'] )
		? $values['sources']
		: $defaults['sources'];

	$sources = array_values(
		array_intersect(
			array_map( 'sanitize_key', $sources ),
			teca_get_related_events_allowed_sources()
		)
	);

	if ( empty( $sources ) ) {
		$sources = $defaults['sources'];
	}

	return array(
		'show'    => $show,
		'title'   => $title,
		'limit'   => $limit,
		'sources' => $sources,
	);
}

/**
 * Extract related settings for a context from a mixed settings array.
 *
 * @param array<string, mixed> $settings Settings array.
 * @param string               $context  single|popup.
 * @return array<string, mixed>
 */
function teca_extract_related_events_values( array $settings, $context = 'single' ) {
	if ( 'popup' === $context ) {
		$values = array(
			'show'    => array_key_exists( 'popup_show_related_events', $settings )
				? $settings['popup_show_related_events']
				: ( $settings['show_related_events'] ?? 'on' ),
			'title'   => array_key_exists( 'popup_related_events_title', $settings )
				? $settings['popup_related_events_title']
				: ( $settings['related_events_title'] ?? __( 'Related Events', 'the-events-calendar-addon2' ) ),
			'limit'   => array_key_exists( 'popup_related_events_limit', $settings )
				? $settings['popup_related_events_limit']
				: ( $settings['related_events_limit'] ?? 3 ),
			'sources' => array_key_exists( 'popup_related_events_sources', $settings )
				? $settings['popup_related_events_sources']
				: ( $settings['related_events_sources'] ?? array() ),
		);
	} else {
		$values = array(
			'show'    => array_key_exists( 'show_related_events', $settings ) ? $settings['show_related_events'] : 'on',
			'title'   => array_key_exists( 'related_events_title', $settings ) ? $settings['related_events_title'] : __( 'Related Events', 'the-events-calendar-addon2' ),
			'limit'   => array_key_exists( 'related_events_limit', $settings ) ? $settings['related_events_limit'] : 3,
			'sources' => array_key_exists( 'related_events_sources', $settings ) ? $settings['related_events_sources'] : array(),
		);
	}

	return teca_normalize_related_events_values( $values );
}

/**
 * Sanitize single-page related events keys inside layout settings.
 *
 * @param array<string, mixed> $settings Settings array.
 * @return array<string, mixed>
 */
function teca_sanitize_single_related_events_settings( array $settings ) {
	$normalized = teca_extract_related_events_values( $settings, 'single' );
	$defaults   = teca_get_related_events_default_settings( 'single' );

	$settings['show_related_events']    = $normalized['show'];
	$settings['related_events_title']   = $normalized['title'];
	$settings['related_events_limit']   = $normalized['limit'];
	$settings['related_events_sources'] = $normalized['sources'];

	foreach ( teca_get_popup_related_events_setting_keys() as $popup_key ) {
		if ( isset( $settings[ $popup_key ] ) ) {
			unset( $settings[ $popup_key ] );
		}
	}

	return teca_merge_related_events_settings( $settings, $defaults );
}

/**
 * Merge sanitized related settings without overwriting explicit off values.
 *
 * @param array<string, mixed> $settings Settings array.
 * @param array<string, mixed> $defaults Default keys.
 * @return array<string, mixed>
 */
function teca_merge_related_events_settings( array $settings, array $defaults ) {
	foreach ( $defaults as $key => $default_value ) {
		if ( ! array_key_exists( $key, $settings ) ) {
			$settings[ $key ] = $default_value;
		}
	}

	return $settings;
}

/**
 * Sanitize popup related events keys inside shortcode settings.
 *
 * @param array<string, mixed> $settings Settings array.
 * @return array<string, mixed>
 */
function teca_sanitize_popup_related_events_settings( array $settings ) {
	$normalized = teca_extract_related_events_values( $settings, 'popup' );
	$defaults   = teca_get_related_events_default_settings( 'popup' );

	$settings['popup_show_related_events']    = $normalized['show'];
	$settings['popup_related_events_title']   = $normalized['title'];
	$settings['popup_related_events_limit']   = $normalized['limit'];
	$settings['popup_related_events_sources'] = $normalized['sources'];

	unset( $settings['show_related_events'], $settings['related_events_title'], $settings['related_events_limit'], $settings['related_events_sources'] );

	return teca_merge_related_events_settings( $settings, $defaults );
}

/**
 * Backward-compatible sanitizer used by legacy call sites.
 *
 * @param array<string, mixed> $settings Settings array.
 * @return array<string, mixed>
 */
function teca_sanitize_related_events_settings( array $settings ) {
	if ( isset( $settings['popup_show_related_events'] ) || isset( $settings['popup_related_events_title'] ) ) {
		return teca_sanitize_popup_related_events_settings( $settings );
	}

	return teca_sanitize_single_related_events_settings( $settings );
}

/**
 * Normalize queried values to unique published tribe_events IDs.
 *
 * @param mixed $ids Post IDs or legacy event arrays.
 * @return int[]
 */
function teca_normalize_related_event_id_list( $ids ) {
	$normalized = array();

	foreach ( (array) $ids as $id ) {
		if ( is_array( $id ) ) {
			$id = isset( $id['event_id'] ) ? absint( $id['event_id'] ) : 0;
		} else {
			$id = absint( $id );
		}

		if ( ! $id || in_array( $id, $normalized, true ) ) {
			continue;
		}

		$post = get_post( $id );

		if ( ! $post || Query::CPT_EVENT !== $post->post_type || 'publish' !== $post->post_status ) {
			continue;
		}

		$normalized[] = $id;
	}

	return $normalized;
}

/**
 * Base upcoming-events query args.
 *
 * @param int   $limit   Max posts.
 * @param int[] $exclude Excluded post IDs.
 * @return array<string, mixed>
 */
function teca_get_related_events_upcoming_query_args( $limit, array $exclude ) {
	$current_datetime = current_time( 'mysql' );

	return array(
		'post_type'                    => Query::CPT_EVENT,
		'post_status'                  => 'publish',
		'posts_per_page'               => max( 1, absint( $limit ) ),
		// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required to exclude the current event from related events.
		'post__not_in'                 => array_values( array_filter( array_map( 'absint', $exclude ) ) ),
		'fields'                       => 'ids',
		'no_found_rows'                => true,
		'ignore_sticky_posts'          => true,
		'eventDisplay'                 => 'custom',
		'tribe_suppress_query_filters' => true,
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required for related event date ordering.
		'meta_key'                     => '_EventStartDate',
		'orderby'                      => 'meta_value',
		'order'                        => 'ASC',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for related event date filtering.
		'meta_query'                   => array(
			'relation' => 'AND',
			array(
				'key'     => '_EventStartDate',
				'value'   => $current_datetime,
				'compare' => '>',
				'type'    => 'DATETIME',
			),
		),
	);
}

/**
 * Query upcoming related event IDs for a source.
 *
 * @param int    $event_id Current event ID.
 * @param string $source   Source key.
 * @param int    $limit    Max IDs to return.
 * @param int[]  $exclude  Excluded IDs.
 * @return int[]
 */
function teca_query_related_events_by_source( $event_id, $source, $limit, array $exclude ) {
	$event_id = absint( $event_id );
	$limit    = max( 1, absint( $limit ) );
	$source   = sanitize_key( (string) $source );

	if ( ! $event_id || ! post_type_exists( Query::CPT_EVENT ) ) {
		return array();
	}

	$query_args = teca_get_related_events_upcoming_query_args( $limit, $exclude );

	switch ( $source ) {
		case 'category':
			$term_ids = wp_get_post_terms( $event_id, 'tribe_events_cat', array( 'fields' => 'ids' ) );
			if ( empty( $term_ids ) || is_wp_error( $term_ids ) ) {
				return array();
			}
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required to find related events by shared taxonomy terms.
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'tribe_events_cat',
					'field'    => 'term_id',
					'terms'    => array_map( 'absint', $term_ids ),
				),
			);
			break;

		case 'tag':
			$term_ids = wp_get_post_terms( $event_id, 'post_tag', array( 'fields' => 'ids' ) );
			if ( empty( $term_ids ) || is_wp_error( $term_ids ) ) {
				return array();
			}
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required to find related events by shared taxonomy terms.
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'term_id',
					'terms'    => array_map( 'absint', $term_ids ),
				),
			);
			break;

		case 'venue':
			$venue_id = Query::get_event_venue_id( $event_id );
			if ( ! $venue_id ) {
				return array();
			}
			$query_args['meta_query'][] = array(
				'key'     => '_EventVenueID',
				'value'   => $venue_id,
				'compare' => '=',
			);
			break;

		case 'organizer':
			$organizer_ids = Query::get_event_organizer_ids( $event_id );
			if ( empty( $organizer_ids ) ) {
				$organizer_ids = array_map( 'absint', (array) get_post_meta( $event_id, '_EventOrganizerID', false ) );
			}
			$organizer_ids = array_values( array_filter( array_map( 'absint', $organizer_ids ) ) );
			if ( empty( $organizer_ids ) ) {
				return array();
			}
			$organizer_clauses = array( 'relation' => 'OR' );
			foreach ( $organizer_ids as $organizer_id ) {
				$organizer_clauses[] = array(
					'key'     => '_EventOrganizerID',
					'value'   => $organizer_id,
					'compare' => '=',
				);
			}
			$query_args['meta_query'][] = $organizer_clauses;
			break;

		case 'upcoming':
			break;

		default:
			return array();
	}

	return teca_normalize_related_event_id_list( get_posts( $query_args ) );
}

/**
 * Get related upcoming event IDs for an event.
 *
 * @param int                  $event_id Event ID.
 * @param array<string, mixed> $args     Optional args: limit, sources, exclude_ids.
 * @return int[]
 */
function teca_get_related_events( $event_id, $args = array() ) {
	$event_id = absint( $event_id );

	if ( ! $event_id ) {
		return array();
	}

	$current_post = get_post( $event_id );
	if ( ! $current_post || Query::CPT_EVENT !== $current_post->post_type ) {
		return array();
	}

	$defaults = array(
		'limit'       => 3,
		'sources'     => teca_get_related_events_allowed_sources(),
		'exclude_ids' => array(),
	);

	$args = wp_parse_args( $args, $defaults );

	$limit = max( 1, min( 12, absint( $args['limit'] ) ) );

	$sources = is_array( $args['sources'] ) ? $args['sources'] : array();
	$sources = array_values(
		array_intersect(
			array_map( 'sanitize_key', $sources ),
			teca_get_related_events_allowed_sources()
		)
	);

	if ( empty( $sources ) ) {
		$sources = teca_get_related_events_default_settings( 'single' )['related_events_sources'];
	}

	$exclude = array_unique(
		array_merge(
			array( $event_id ),
			array_map( 'absint', (array) $args['exclude_ids'] )
		)
	);

	$related = array();

	foreach ( $sources as $source ) {
		if ( count( $related ) >= $limit ) {
			break;
		}

		$needed = $limit - count( $related );
		$ids    = teca_query_related_events_by_source( $event_id, $source, $needed, $exclude );

		foreach ( $ids as $id ) {
			$id = absint( $id );

			if ( ! $id || in_array( $id, $related, true ) || in_array( $id, $exclude, true ) ) {
				continue;
			}

			$related[] = $id;
			$exclude[] = $id;
		}
	}

	return array_slice( $related, 0, $limit );
}

/**
 * Format a related event card date string.
 *
 * @param int $event_id Event ID.
 * @return string
 */
function teca_get_related_event_date_label( $event_id ) {
	$event_id = absint( $event_id );

	if ( ! $event_id ) {
		return '';
	}

	if ( function_exists( 'tribe_get_start_date' ) ) {
		return (string) tribe_get_start_date( $event_id, false );
	}

	$start = get_post_meta( $event_id, '_EventStartDate', true );

	if ( ! $start ) {
		return '';
	}

	return mysql2date( get_option( 'date_format' ), $start );
}

/**
 * Render a single related event card.
 *
 * @param int                  $event_id Related event ID.
 * @param array<string, mixed> $settings Settings.
 * @return void
 */
function teca_render_related_event_card( $event_id, array $settings = array() ) {
	$event_id = absint( $event_id );

	if ( ! $event_id ) {
		return;
	}

	Template_Loader::load_template(
		'partials/teca-related-event-card.php',
		array(
			'event_id' => $event_id,
			'settings' => $settings,
		)
	);
}

/**
 * Render the related events section.
 *
 * @param int                  $event_id Event ID.
 * @param array<string, mixed> $settings Settings array.
 * @param array<string, mixed> $args     Optional args: context, sorted_fields.
 * @return void
 */
function teca_render_related_events_section( $event_id, $settings = array(), $args = array() ) {
	$event_id = absint( $event_id );

	if ( ! $event_id ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		array(
			'context'       => 'single',
			'sorted_fields' => array(),
		)
	);

	$context  = sanitize_key( (string) $args['context'] );
	$settings = is_array( $settings ) ? $settings : array();
	$config   = teca_extract_related_events_values( $settings, $context );

	if ( ! teca_is_related_events_enabled( $config['show'] ) ) {
		return;
	}

	$related_ids = teca_get_related_events(
		$event_id,
		array(
			'limit'   => $config['limit'],
			'sources' => $config['sources'],
		)
	);

	if ( empty( $related_ids ) ) {
		return;
	}

	Template_Loader::load_template(
		'partials/teca-related-events.php',
		array(
			'event_ids' => $related_ids,
			'title'     => $config['title'],
			'settings'  => $settings,
			'context'   => $context,
		)
	);
}

/**
 * Related events source options for the builder.
 *
 * @return array<int, array<string, string>>
 */
function teca_get_related_events_source_options() {
	return array(
		array(
			'label' => __( 'Category', 'the-events-calendar-addon2' ),
			'value' => 'category',
		),
		array(
			'label' => __( 'Tag', 'the-events-calendar-addon2' ),
			'value' => 'tag',
		),
		array(
			'label' => __( 'Venue', 'the-events-calendar-addon2' ),
			'value' => 'venue',
		),
		array(
			'label' => __( 'Organizer', 'the-events-calendar-addon2' ),
			'value' => 'organizer',
		),
		array(
			'label' => __( 'Upcoming fallback', 'the-events-calendar-addon2' ),
			'value' => 'upcoming',
		),
	);
}
