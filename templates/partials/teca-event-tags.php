<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

teca_render_event_tags(
	array(
		'event'         => $event ?? array(),
		'wrapper_class' => $wrapper_class ?? 'teca-event-tags',
		'item_class'    => $item_class ?? 'teca-event-tag',
		'transform'     => $transform ?? '',
	)
);
