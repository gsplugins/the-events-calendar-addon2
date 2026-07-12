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
?>
<div class="teca-accordion-panel-content">
	<?php
	teca_render_card_elements(
		array(
			'layout'              => 'accordion-panel',
			'event'               => $event,
			'visibility_settings' => $visibility_settings ?? null,
			'link_context'        => $teca_link_context,
			'hide_panel_image'    => ! empty( $accordion_hide_panel_image ),
			'hide_panel_tags'     => ! empty( $accordion_hide_panel_tags ),
			'hide_panel_cat'      => ! empty( $accordion_hide_panel_cat ),
			'excerpt_words'       => 30,
			'show_button'         => false,
		)
	);
	?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
