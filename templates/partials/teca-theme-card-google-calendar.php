<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Card/theme Google Calendar action output.
 *
 * Optional vars:
 * - $teca_theme_gcal_wrapper / $teca_gcal_wrapper  Outer wrapper class.
 * - $teca_gcal_context                             Button context (default: card).
 */
$event_id              = (int) ( $event_id ?? $event['event_id'] ?? 0 );
$teca_theme_gcal_wrapper = isset( $teca_theme_gcal_wrapper ) ? trim( (string) $teca_theme_gcal_wrapper ) : '';
$teca_gcal_wrapper     = isset( $teca_gcal_wrapper ) ? trim( (string) $teca_gcal_wrapper ) : $teca_theme_gcal_wrapper;
$teca_gcal_context     = $teca_gcal_context ?? 'card';
$visibility_settings   = $visibility_settings ?? null;
$gcal_args             = array(
	'google_calendar_url' => $event['google_calendar_url'] ?? '',
);

teca_echo_google_calendar_button_actions(
	$event_id,
	$teca_gcal_context,
	$visibility_settings,
	$teca_gcal_wrapper,
	$gcal_args
);
