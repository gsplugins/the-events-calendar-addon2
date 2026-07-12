<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event = $event ?? array();

$teca_link_context = teca_build_card_link_context(
	array(
		'link_type'    => $gs_teca_link_type ?? 'none',
		'shortcode_id' => $id ?? '',
		'popup_style'  => $popup_style ?? 'default',
		'link_target'  => $link_target ?? '_blank',
	)
);

$event_id            = (int) ( $event['event_id'] ?? 0 );
$title               = $event['event_name'] ?? ( $event_id ? get_the_title( $event_id ) : '' );
$image_url           = $event_id ? get_the_post_thumbnail_url( $event_id, 'medium_large' ) : '';
$visibility_settings = $visibility_settings ?? null;

if ( teca_is_card_field_visible( 'event_thumbnail', $visibility_settings ) && $image_url ) :
	?>
	<div class="<?php teca_print_card_visible_classes( 'event_thumbnail', 'teca-timeline-thumb teca-timeline-1-thumb gs-teca-thumbnail-wrapper teca-event-thumb', $visibility_settings ); ?>">
		<?php
		echo wp_kses_post( teca_get_card_link_html(
			$event_id,
			'<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy" />',
			$teca_link_context,
			'teca-event-image-link'
		) );
		?>
	</div>
	<?php
endif;
?>
<div class="teca-timeline-content teca-timeline-1-content">
	<?php
	teca_render_card_elements(
		array(
			'layout'              => 'timeline-1',
			'event'               => $event,
			'visibility_settings' => $visibility_settings,
			'link_context'        => $teca_link_context,
			'skip_fields'         => array( 'event_date', 'event_thumbnail' ),
			'excerpt_words'       => 30,
			'show_button'         => true,
		)
	);
	?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
