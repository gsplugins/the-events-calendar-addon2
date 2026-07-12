<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

teca_render_event_tags(
	array(
		'event'         => $event ?? array(),
		'wrapper_class' => $wrapper_class ?? 'teca-event-tags',
		'item_class'    => $item_class ?? 'teca-event-tag',
		'transform'     => $transform ?? '',
	)
);

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
