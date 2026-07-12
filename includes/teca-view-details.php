<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the View Details button should render for card visibility settings.
 *
 * @param array|null $visibility_settings Visibility settings.
 * @return bool
 */
function teca_is_view_details_button_visible( $visibility_settings = null ) {
	return teca_is_card_field_visible( 'view_details_button', $visibility_settings );
}

/**
 * Whether View Details should show for a card/footer action row.
 *
 * @param array|null $visibility_settings Visibility settings.
 * @param string     $link_type         Shortcode link type.
 * @return bool
 */
function teca_should_show_view_details_button( $visibility_settings, $link_type ) {
	$link_type = sanitize_key( (string) $link_type );

	if ( ! in_array( $link_type, array( 'popup', 'single_page' ), true ) ) {
		return false;
	}

	return teca_is_view_details_button_visible( $visibility_settings );
}

/**
 * Resolve view-details visibility for single-page scoped field settings.
 *
 * @param array  $sorted_fields Scoped visibility settings.
 * @return bool
 */
function teca_is_single_page_view_details_visible( array $sorted_fields ) {
	if ( isset( $sorted_fields['view_details_button'] ) ) {
		return teca_is_single_page_element_visible( 'view_details_button', $sorted_fields );
	}

	if ( isset( $sorted_fields['event_button'] ) ) {
		return teca_is_single_page_element_visible( 'event_button', $sorted_fields );
	}

	return true;
}

/**
 * Build link context for style 1-10 theme templates from shortcode settings.
 *
 * @param string $link_type     Popup or single_page.
 * @param mixed  $shortcode_id  Shortcode ID.
 * @param string $popup_style   Popup style slug.
 * @param string $link_target   Link target for single-page links.
 * @return array
 */
function teca_build_theme_link_context( $link_type, $shortcode_id, $popup_style = 'default', $link_target = '_blank' ) {
	return teca_build_card_link_context(
		array(
			'link_type'    => $link_type,
			'shortcode_id' => $shortcode_id,
			'popup_style'  => $popup_style,
			'link_target'  => $link_target,
		)
	);
}

/**
 * Render a style 1-10 View Details button using the same link logic as titles.
 *
 * @param int   $event_id Event ID.
 * @param array $args {
 *     @type array  $link_context Link context from teca_build_theme_link_context().
 *     @type string $button_class Style-specific classes (without gs-teca-btn-popup/link).
 *     @type string $inner_html   Inner button markup.
 * }
 * @return string
 */
function teca_get_view_details_button_html( $event_id, array $args = array() ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'link_context' => array(),
			'button_class' => 'teca-event-button',
			'inner_html'   => '',
		)
	);

	$link_context = teca_build_card_link_context( (array) $args['link_context'] );
	$link_type    = $link_context['link_type'] ?? 'none';

	if ( ! in_array( $link_type, array( 'popup', 'single_page' ), true ) ) {
		return '';
	}

	$inner_html = (string) $args['inner_html'];

	if ( '' === $inner_html ) {
		$inner_html = esc_html( teca_get_view_details_text() );
	}

	$style_class = trim( (string) $args['button_class'] );
	$link_class  = 'popup' === $link_type
		? trim( 'gs-teca-btn-popup ' . $style_class )
		: trim( 'gs-teca-btn-link ' . $style_class );

	return teca_get_card_link_html( $event_id, $inner_html, $link_context, $link_class );
}
