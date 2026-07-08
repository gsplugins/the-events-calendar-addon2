<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

teca_render_event_categories(
	array(
		'event'         => $event ?? array(),
		'wrapper_class' => $wrapper_class ?? 'teca-event-categories',
		'item_class'    => $item_class ?? 'teca-event-category',
		'transform'     => $transform ?? '',
	)
);
