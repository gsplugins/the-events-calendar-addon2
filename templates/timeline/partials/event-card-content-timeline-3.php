<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event = $event ?? array();

$teca_link_context = teca_build_card_link_context(
	array(
		'link_type'    => $gs_teca_link_type ?? 'none',
		'shortcode_id' => $id ?? '',
		'popup_style'  => $popup_style ?? 'default',
		'link_target'  => $link_target ?? '_blank',
	)
);
$teca_timeline_3_link_type = $teca_link_context['link_type'] ?? 'none';
$teca_timeline_3_show_button = in_array( $teca_timeline_3_link_type, array( 'popup', 'single_page' ), true );
?>
<div class="teca-timeline-3-card-inner">
	<?php
	teca_render_card_elements(
		array(
			'layout'              => 'timeline-3',
			'event'               => $event,
			'visibility_settings' => $visibility_settings ?? null,
			'link_context'        => $teca_link_context,
			'excerpt_words'       => 30,
			'show_button'         => $teca_timeline_3_show_button,
			'button_text'         => __( 'View Event', 'the-events-calendar-addon' ),
		)
	);
	?>
</div>
