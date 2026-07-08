<?php
namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Demo venue definitions.
 *
 * @return array<int, array<string, string>>
 */
function teca_get_demo_venue_definitions() {
	return array(
		array(
			'Venue'   => __( 'GS Conference Hall', 'the-events-calendar-addon' ),
			'Address' => '123 Main Street',
			'City'    => 'New York',
			'State'   => 'NY',
			'Zip'     => '10001',
			'Country' => 'US',
			'Phone'   => '+1 (555) 010-2000',
		),
		array(
			'Venue'   => __( 'Creative Studio Space', 'the-events-calendar-addon' ),
			'Address' => '456 Market Street',
			'City'    => 'San Francisco',
			'State'   => 'CA',
			'Zip'     => '94105',
			'Country' => 'US',
			'Phone'   => '+1 (555) 010-3000',
		),
		array(
			'Venue'   => __( 'Riverside Event Center', 'the-events-calendar-addon' ),
			'Address' => '789 River Road',
			'City'    => 'Austin',
			'State'   => 'TX',
			'Zip'     => '73301',
			'Country' => 'US',
			'Phone'   => '+1 (555) 010-4000',
		),
	);
}

/**
 * Demo organizer definitions.
 *
 * @return array<int, array<string, string>>
 */
function teca_get_demo_organizer_definitions() {
	return array(
		array(
			'Organizer' => __( 'GS Events Team', 'the-events-calendar-addon' ),
			'Email'     => 'events@gsplugins.com',
			'Website'   => 'https://www.gsplugins.com/',
			'Phone'     => '+1 (555) 010-1001',
		),
		array(
			'Organizer' => __( 'Creative Collective', 'the-events-calendar-addon' ),
			'Email'     => 'hello@creativecollective.com',
			'Website'   => 'https://example.com/creative-collective',
			'Phone'     => '+1 (555) 010-1002',
		),
		array(
			'Organizer' => __( 'Summit Organizers', 'the-events-calendar-addon' ),
			'Email'     => 'info@summitorganizers.com',
			'Website'   => 'https://example.com/summit-organizers',
			'Phone'     => '+1 (555) 010-1003',
		),
	);
}

/**
 * Whether an event already has venue and organizer assigned.
 *
 * @param int $event_id Event post ID.
 * @return bool
 */
function teca_event_has_linked_posts( $event_id ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return false;
	}

	$venue_id = class_exists( __NAMESPACE__ . '\\Query' )
		? Query::get_event_venue_id( $event_id )
		: (int) get_post_meta( $event_id, '_EventVenueID', true );

	$organizer_ids = class_exists( __NAMESPACE__ . '\\Query' )
		? Query::get_event_organizer_ids( $event_id )
		: array_filter( array( (int) get_post_meta( $event_id, '_EventOrganizerID', true ) ) );

	return $venue_id > 0 && ! empty( $organizer_ids );
}

/**
 * Create a single demo venue through TEC APIs.
 *
 * @param array<string, string> $definition Venue data.
 * @return int Venue post ID or 0.
 */
function teca_create_demo_venue( array $definition ) {
	if ( function_exists( 'tribe_create_venue' ) ) {
		$venue_id = tribe_create_venue( $definition );
	} else {
		$venue_id = wp_insert_post(
			array(
				'post_type'   => 'tribe_venue',
				'post_title'  => $definition['Venue'] ?? '',
				'post_status' => 'publish',
			)
		);

		if ( $venue_id && ! is_wp_error( $venue_id ) ) {
			foreach ( $definition as $key => $value ) {
				if ( 'Venue' === $key ) {
					continue;
				}

				update_post_meta( (int) $venue_id, '_Venue' . $key, $value );
			}
		}
	}

	if ( ! $venue_id || is_wp_error( $venue_id ) ) {
		return 0;
	}

	add_post_meta( (int) $venue_id, 'gsteca-demo_data', 1, true );

	return (int) $venue_id;
}

/**
 * Create a single demo organizer through TEC APIs.
 *
 * @param array<string, string> $definition Organizer data.
 * @return int Organizer post ID or 0.
 */
function teca_create_demo_organizer( array $definition ) {
	if ( function_exists( 'tribe_create_organizer' ) ) {
		$organizer_id = tribe_create_organizer( $definition );
	} else {
		$organizer_id = wp_insert_post(
			array(
				'post_type'   => 'tribe_organizer',
				'post_title'  => $definition['Organizer'] ?? '',
				'post_status' => 'publish',
			)
		);

		if ( $organizer_id && ! is_wp_error( $organizer_id ) ) {
			foreach ( $definition as $key => $value ) {
				if ( 'Organizer' === $key ) {
					continue;
				}

				update_post_meta( (int) $organizer_id, '_Organizer' . $key, $value );
			}
		}
	}

	if ( ! $organizer_id || is_wp_error( $organizer_id ) ) {
		return 0;
	}

	add_post_meta( (int) $organizer_id, 'gsteca-demo_data', 1, true );

	return (int) $organizer_id;
}

/**
 * Get existing demo linked posts marked with gsteca-demo_data.
 *
 * @return array{venues: int[], organizers: int[]}
 */
function teca_get_existing_demo_linked_post_ids() {
	$venues = get_posts(
		array(
			'post_type'      => 'tribe_venue',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => 'gsteca-demo_data',
			'meta_value'     => '1',
		)
	);

	$organizers = get_posts(
		array(
			'post_type'      => 'tribe_organizer',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => 'gsteca-demo_data',
			'meta_value'     => '1',
		)
	);

	return array(
		'venues'     => array_values( array_map( 'intval', (array) $venues ) ),
		'organizers' => array_values( array_map( 'intval', (array) $organizers ) ),
	);
}

/**
 * Create demo venues and organizers, or reuse existing demo records.
 *
 * @return array{venues: int[], organizers: int[]}
 */
function teca_get_or_create_demo_linked_posts() {
	$existing = teca_get_existing_demo_linked_post_ids();

	if ( ! empty( $existing['venues'] ) && ! empty( $existing['organizers'] ) ) {
		return $existing;
	}

	$venues     = $existing['venues'];
	$organizers = $existing['organizers'];

	foreach ( teca_get_demo_venue_definitions() as $definition ) {
		$venue_id = teca_create_demo_venue( $definition );

		if ( $venue_id > 0 ) {
			$venues[] = $venue_id;
		}
	}

	foreach ( teca_get_demo_organizer_definitions() as $definition ) {
		$organizer_id = teca_create_demo_organizer( $definition );

		if ( $organizer_id > 0 ) {
			$organizers[] = $organizer_id;
		}
	}

	$linked = array(
		'venues'     => array_values( array_unique( array_map( 'intval', $venues ) ) ),
		'organizers' => array_values( array_unique( array_map( 'intval', $organizers ) ) ),
	);

	update_option( 'gsteca_demo_linked_post_ids', $linked, false );

	return $linked;
}

/**
 * Resolve venue/organizer IDs for a demo event index.
 *
 * @param int   $index  Demo event index.
 * @param array $linked Linked post IDs.
 * @return array{venue_id:int,organizer_id:int}
 */
function teca_get_demo_linked_post_assignment( $index, array $linked ) {
	$venues     = $linked['venues'] ?? array();
	$organizers = $linked['organizers'] ?? array();
	$index      = max( 0, (int) $index );

	$venue_id     = 0;
	$organizer_id = 0;

	if ( ! empty( $venues ) ) {
		$venue_id = (int) $venues[ $index % count( $venues ) ];
	}

	if ( ! empty( $organizers ) ) {
		$organizer_id = (int) $organizers[ $index % count( $organizers ) ];
	}

	return array(
		'venue_id'     => $venue_id,
		'organizer_id' => $organizer_id,
	);
}

/**
 * Assign venue/organizer to an event using TEC's native save pipeline.
 *
 * @param int  $event_id     Event post ID.
 * @param int  $venue_id     Venue post ID.
 * @param int  $organizer_id Organizer post ID.
 * @param bool $force_demo   Force assignment for demo events.
 * @return bool
 */
function teca_sync_event_linked_posts( $event_id, $venue_id, $organizer_id, $force_demo = false ) {
	$event_id     = (int) $event_id;
	$venue_id     = (int) $venue_id;
	$organizer_id = (int) $organizer_id;

	if ( $event_id <= 0 || ( $venue_id <= 0 && $organizer_id <= 0 ) ) {
		return false;
	}

	$is_demo = $force_demo || ! empty( get_post_meta( $event_id, 'gsteca-demo_data', true ) );

	if ( ! $is_demo && teca_event_has_linked_posts( $event_id ) ) {
		return false;
	}

	if ( function_exists( 'tribe_update_event' ) ) {
		$args = array();

		if ( $venue_id > 0 ) {
			$args['Venue'] = array(
				'VenueID' => $venue_id,
			);
		}

		if ( $organizer_id > 0 ) {
			$args['Organizer'] = array(
				'OrganizerID' => $organizer_id,
			);
		}

		return (bool) tribe_update_event( $event_id, $args );
	}

	if ( $venue_id > 0 ) {
		update_post_meta( $event_id, '_EventVenueID', $venue_id );
	}

	if ( $organizer_id > 0 ) {
		update_post_meta( $event_id, '_EventOrganizerID', $organizer_id );
	}

	return true;
}

/**
 * Delete demo venues and organizers created by the importer.
 *
 * @return void
 */
function teca_delete_demo_linked_posts() {
	foreach ( array( 'tribe_venue', 'tribe_organizer' ) as $post_type ) {
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => 'gsteca-demo_data',
				'meta_value'     => '1',
			)
		);

		foreach ( (array) $posts as $post_id ) {
			wp_delete_post( (int) $post_id, true );
		}
	}

	delete_option( 'gsteca_demo_linked_post_ids' );
}
