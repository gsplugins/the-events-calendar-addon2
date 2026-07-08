<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Card visibility / ordering / link helpers for Accordion & Timeline styles.
 */
function teca_get_card_visibility_settings( $visibility_settings = null ) {
	$defaults = plugin()->builder->get_visibility_defaults();

	if ( ! is_array( $visibility_settings ) || empty( $visibility_settings ) ) {
		return $defaults;
	}

	return array_merge( $defaults, $visibility_settings );
}

function teca_get_card_field_order( $visibility_settings = null ) {
	return array_keys( teca_get_card_visibility_settings( $visibility_settings ) );
}

/**
 * Timeline 3: category and tag row always render first in card content.
 *
 * @param string[] $field_order Card field order.
 * @return string[]
 */
function teca_reorder_timeline_3_field_order( array $field_order ) {
	$field_order = array_values( array_unique( $field_order ) );

	$tax_fields = array();

	foreach ( array( 'event_cat', 'event_tags' ) as $tax_field ) {
		if ( in_array( $tax_field, $field_order, true ) ) {
			$tax_fields[] = $tax_field;
		}
	}

	if ( empty( $tax_fields ) ) {
		return $field_order;
	}

	$remaining = array_values( array_diff( $field_order, $tax_fields ) );

	return array_merge( $tax_fields, $remaining );
}

/**
 * Timeline and Accordion card layouts omit standalone Event Cost output.
 * Cost may still appear in event details / popup / single page elsewhere.
 *
 * @param string $layout Card layout key from teca_render_card_elements().
 * @return bool
 */
function teca_layout_suppresses_card_event_cost( $layout ) {
	return in_array(
		(string) $layout,
		array(
			'timeline-1',
			'timeline-2',
			'timeline-3',
			'accordion-panel',
		),
		true
	);
}

function teca_is_card_field_visible( $field_key, $visibility_settings = null ) {
	$settings = teca_get_card_visibility_settings( $visibility_settings );
	$field    = $settings[ $field_key ] ?? true;

	return Helpers::is_visible( $field );
}

function teca_print_card_visible_classes( $field_key, $additional_class = '', $visibility_settings = null ) {
	$settings = teca_get_card_visibility_settings( $visibility_settings );
	$field    = $settings[ $field_key ] ?? true;

	Helpers::print_visible_classes( $field, $additional_class );
}

function teca_build_card_link_context( array $context = array() ) {
	return wp_parse_args(
		$context,
		array(
			'link_type'    => 'none',
			'shortcode_id' => '',
			'popup_style'  => 'default',
			'link_target'  => '_blank',
		)
	);
}

function teca_get_card_link_html( $event_id, $content, array $link_context, $class = '' ) {
	$event_id     = (int) $event_id;
	$link_type    = $link_context['link_type'] ?? 'none';
	$shortcode_id = $link_context['shortcode_id'] ?? '';
	$popup_style  = $link_context['popup_style'] ?? 'default';
	$link_target  = $link_context['link_target'] ?? '_blank';
	$class        = trim( (string) $class );

	if ( 'popup' === $link_type && $event_id && $shortcode_id ) {
		$data_src   = '#gs_teca_popup_' . $event_id . '_' . $shortcode_id;
		$data_theme = 'gs-teca-popup-' . sanitize_key( $popup_style );

		return sprintf(
			'<a href="#" class="%s gs_teca_pop open-popup-link" data-mfp-src="%s" data-theme="%s">%s</a>',
			esc_attr( $class ),
			esc_attr( $data_src ),
			esc_attr( $data_theme ),
			$content
		);
	}

	if ( 'single_page' === $link_type && $event_id ) {
		return sprintf(
			'<a href="%s" target="%s" class="%s">%s</a>',
			esc_url( get_the_permalink( $event_id ) ),
			esc_attr( $link_target ),
			esc_attr( $class ),
			$content
		);
	}

	return $content;
}

function teca_get_popup_field_order( $popup_visibility_settings = null, $popup_visibility_order = null ) {
	$visibility = is_array( $popup_visibility_settings ) && ! empty( $popup_visibility_settings )
		? $popup_visibility_settings
		: plugin()->builder->get_popup_visibility_defaults();

	$default_order = array_keys( $visibility );

	if ( is_array( $popup_visibility_order ) && ! empty( $popup_visibility_order ) ) {
		$ordered   = array_values( array_intersect( $popup_visibility_order, $default_order ) );
		$remaining = array_values( array_diff( $default_order, $ordered ) );

		return array_merge( $ordered, $remaining );
	}

	return $default_order;
}

function teca_render_card_elements( array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'layout'              => 'accordion-panel',
			'event'               => array(),
			'visibility_settings' => null,
			'link_context'        => array(),
			'field_order'         => null,
			'skip_fields'         => array(),
			'hide_panel_image'    => false,
			'hide_panel_tags'     => false,
			'excerpt_words'       => 30,
			'show_button'         => true,
			'button_text'         => '',
		)
	);

	$partial = Template_Loader::locate_template( 'partials/teca-card-elements.php' );

	if ( is_wp_error( $partial ) ) {
		return;
	}

	$teca_card_args = $args;
	include $partial;
}
