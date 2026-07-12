<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event               = $event ?? array();
$visibility_settings = $visibility_settings ?? ( $teca_card_args['visibility_settings'] ?? null );
$event_id            = (int) ( $event_id ?? $event['event_id'] ?? 0 );
$gcal_args           = array(
	'google_calendar_url' => $event['google_calendar_url'] ?? '',
	'class'               => 'teca-google-calendar-btn--table',
);

teca_echo_google_calendar_button_actions(
	$event_id,
	'table',
	$visibility_settings,
	'',
	$gcal_args
);

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
