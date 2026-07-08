<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event_id            = (int) ( $event_id ?? $event['event_id'] ?? 0 );
$teca_gcal_context   = $teca_gcal_context ?? 'card';
$visibility_settings = $visibility_settings ?? ( $teca_card_args['visibility_settings'] ?? null );
$gcal_args           = array(
	'google_calendar_url' => $event['google_calendar_url'] ?? '',
);

teca_echo_google_calendar_button_actions(
	$event_id,
	$teca_gcal_context,
	$visibility_settings,
	'',
	$gcal_args
);
