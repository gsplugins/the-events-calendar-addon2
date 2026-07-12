<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve postponed/canceled badge data for a single event.
 *
 * @param int $event_id Event post ID.
 * @return array<string, string>|null
 */
function teca_get_single_event_status_badge_data( $event_id ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return null;
	}

	$status_slug  = '';
	$status_label = '';
	$status_reason = '';
	$event_object  = null;

	if ( function_exists( 'tribe_get_event' ) ) {
		$event_object = tribe_get_event( $event_id );

		if ( $event_object && ! empty( $event_object->event_status ) ) {
			$status_slug = sanitize_key( (string) $event_object->event_status );
		}

		if ( $event_object && ! empty( $event_object->event_status_reason ) ) {
			$status_reason = (string) $event_object->event_status_reason;
		}
	}

	if ( '' === $status_slug && function_exists( 'tribe_is_event_cancelled' ) && tribe_is_event_cancelled( $event_id ) ) {
		$status_slug = 'canceled';
	}

	if ( '' === $status_slug && function_exists( 'tribe_is_event_postponed' ) && tribe_is_event_postponed( $event_id ) ) {
		$status_slug = 'postponed';
	}

	if ( '' === $status_slug ) {
		$meta_status = sanitize_key( (string) get_post_meta( $event_id, '_tribe_events_status', true ) );
		if ( '' !== $meta_status && 'scheduled' !== $meta_status ) {
			$status_slug = $meta_status;
		}
	}

	if ( '' === $status_reason ) {
		$status_reason = (string) get_post_meta( $event_id, '_tribe_events_status_reason', true );
	}

	if ( 'cancelled' === $status_slug ) {
		$status_slug = 'canceled';
	}

	if ( ! in_array( $status_slug, array( 'postponed', 'canceled' ), true ) ) {
		return null;
	}

	if ( class_exists( '\Tribe\Events\Event_Status\Status_Labels' ) ) {
		$labels = new \Tribe\Events\Event_Status\Status_Labels();

		if ( 'postponed' === $status_slug ) {
			$status_label = $labels->get_postponed_label();
		} else {
			$status_label = $labels->get_canceled_label();
		}
	} elseif ( 'postponed' === $status_slug ) {
		$status_label = __( 'Postponed', 'the-events-calendar-addon2' );
	} else {
		$status_label = __( 'Cancelled', 'the-events-calendar-addon2' );
	}

	$status_label = trim( (string) $status_label );

	if ( '' === $status_label ) {
		return null;
	}

	return array(
		'slug'   => $status_slug,
		'label'  => $status_label,
		'reason' => $status_reason,
	);
}

/**
 * Build single-page event status badge markup.
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function teca_get_single_event_status_badge_html( $event_id ) {
	$data = teca_get_single_event_status_badge_data( $event_id );

	if ( ! $data ) {
		return '';
	}

	$class_slug = 'postponed' === $data['slug'] ? 'postponed' : 'cancelled';
	$classes    = array(
		'teca-single-event-status',
		'teca-single-event-status--' . $class_slug,
	);

	if ( 'cancelled' === $class_slug ) {
		$classes[] = 'teca-single-event-status--canceled';
	}

	$html  = '<div class="teca-single-event-status-wrap">';
	$html .= '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">';
	$html .= esc_html( $data['label'] );
	$html .= '</span>';

	if ( '' !== trim( (string) $data['reason'] ) ) {
		$html .= '<div class="teca-single-event-status-reason">' . wp_kses_post( $data['reason'] ) . '</div>';
	}

	$html .= '</div>';

	return $html;
}

/**
 * Echo single-page event status badge markup.
 *
 * @param int $event_id Event post ID.
 * @return void
 */
function teca_render_single_event_status_badge( $event_id ) {
	$markup = teca_get_single_event_status_badge_html( $event_id );

	if ( '' === $markup ) {
		return;
	}

	echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in builder.
}

/**
 * Whether a single-page status badge will render for the current layout.
 *
 * @param int        $event_id      Event post ID.
 * @param array|null $sorted_fields Visibility settings.
 * @return bool
 */
function teca_single_page_status_badge_will_display( $event_id, $sorted_fields = null ) {
	return (bool) teca_get_single_event_status_badge_data( $event_id );
}

/**
 * Render status badge at the top of the layout when the title field is hidden.
 *
 * @param array $sorted_fields Visibility settings.
 * @param int   $event_id      Event post ID.
 * @return void
 */
function teca_maybe_render_single_page_status_badge_fallback( array $sorted_fields, $event_id ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 || teca_is_single_page_element_visible( 'event_title', $sorted_fields ) ) {
		return;
	}

	$markup = teca_get_single_event_status_badge_html( $event_id );

	if ( '' === $markup ) {
		return;
	}

	echo '<div class="teca-single-event-status-fallback">' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge builder.
}

/**
 * Remove TEC single status notice markup when TECA renders its own badge.
 *
 * @param string              $notices_html Notice HTML.
 * @param array<string,mixed> $notices      Notice data.
 * @return string
 */
function teca_filter_single_page_tribe_notices( $notices_html, $notices ) {
	unset( $notices );

	if ( ! is_singular( 'tribe_events' ) ) {
		return $notices_html;
	}

	if ( ! teca_single_page_status_badge_will_display( (int) get_the_ID() ) ) {
		return $notices_html;
	}

	return teca_strip_tec_single_event_status_notice( (string) $notices_html );
}

/**
 * Strip the TEC single event status container from notice HTML.
 *
 * @param string $html Notice HTML.
 * @return string
 */
function teca_strip_tec_single_event_status_notice( $html ) {
	$needle = 'tribe-events-status-single';

	if ( false === strpos( $html, $needle ) ) {
		return $html;
	}

	$start = strrpos( substr( $html, 0, (int) strpos( $html, $needle ) ), '<div' );

	if ( false === $start ) {
		return $html;
	}

	$depth  = 0;
	$length = strlen( $html );

	for ( $index = $start; $index < $length; $index++ ) {
		if ( '<' !== $html[ $index ] ) {
			continue;
		}

		if ( 0 === substr_compare( $html, '</div>', $index, 6 ) ) {
			$depth--;

			if ( 0 === $depth ) {
				return substr( $html, 0, $start ) . ltrim( substr( $html, $index + 6 ) );
			}

			$index += 5;
			continue;
		}

		if ( 0 === substr_compare( $html, '<div', $index, 4 ) ) {
			$depth++;
		}
	}

	return $html;
}

add_filter( 'tribe_the_notices', __NAMESPACE__ . '\\teca_filter_single_page_tribe_notices', 20, 2 );
