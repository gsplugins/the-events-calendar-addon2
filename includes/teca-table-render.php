<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build balanced CSS grid column definition for table styles 1–5.
 *
 * @param string $style_num Table style number (1-5).
 * @param bool   $show_image Whether image column is visible.
 * @param bool   $show_title Whether title column is visible.
 * @param bool   $show_organizer Whether organizer column is visible.
 * @param bool   $show_venue Whether venue column is visible.
 * @param bool   $show_links Whether links column is visible.
 * @return string Inline style value for --teca-t{N}-cols.
 */
function teca_build_table_grid_style( $style_num, $show_image, $show_title, $show_organizer, $show_venue, $show_links ) {
	$style_num = (string) $style_num;
	$columns   = array();
	$image_var = '--teca-t' . $style_num . '-image-size';

	if ( $show_image ) {
		$columns[] = 'minmax(var(' . $image_var . '), var(' . $image_var . '))';
	}

	if ( $show_title ) {
		$columns[] = 'minmax(150px, 1.45fr)';
	}

	if ( $show_organizer ) {
		$columns[] = 'minmax(120px, 1.05fr)';
	}

	if ( $show_venue ) {
		$columns[] = 'minmax(110px, 0.7fr)';
	}

	if ( $show_links ) {
		$columns[] = 'minmax(130px, 140px)';
	}

	if ( empty( $columns ) ) {
		return '';
	}

	return sprintf( '--teca-t%s-cols: %s;', $style_num, implode( ' ', $columns ) );
}

/**
 * Render venue cell content for table styles 1–5.
 *
 * @param array<string, mixed> $event Event data from Query::get_event_linked_data().
 */
function teca_render_table_venue( array $event ) {
	$venue_name = trim( (string) ( $event['venue_name'] ?? ( $event['venue']['title'] ?? '' ) ) );
	$venue_city = trim( (string) ( $event['venue_city'] ?? ( $event['venue']['city'] ?? '' ) ) );
	$venue_state = trim( (string) ( $event['venue_state'] ?? ( $event['venue']['state'] ?? '' ) ) );

	$meta_parts = array();

	if ( '' !== $venue_city ) {
		$meta_parts[] = $venue_city;
	}

	if ( '' !== $venue_state ) {
		$meta_parts[] = $venue_state;
	}

	$venue_meta = implode( ', ', $meta_parts );

	if ( '' === $venue_name && '' === $venue_meta ) {
		echo '<span class="teca-table-venue-empty" aria-hidden="true">&mdash;</span>';
		return;
	}

	echo '<div class="teca-table-venue">';

	if ( '' !== $venue_name ) {
		printf(
			'<span class="teca-table-venue-name">%s</span>',
			esc_html( $venue_name )
		);
	}

	if ( '' !== $venue_meta ) {
		printf(
			'<span class="teca-table-venue-meta">%s</span>',
			esc_html( $venue_meta )
		);
	}

	echo '</div>';
}

/**
 * Render Google Calendar button for table Links column.
 *
 * @param int                  $event_id Event ID.
 * @param array<string, mixed> $event    Event data.
 * @param array|null           $visibility_settings Visibility settings.
 */
function teca_render_table_google_calendar_button( $event_id, array $event, $visibility_settings = null ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 || ! teca_is_google_calendar_button_visible( $visibility_settings ) ) {
		return;
	}

	teca_render_google_calendar_button(
		$event_id,
		'table',
		array(
			'google_calendar_url' => $event['google_calendar_url'] ?? '',
		)
	);
}
