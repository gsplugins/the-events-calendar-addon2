<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether global multilingual preference mode is enabled.
 *
 * @return bool
 */
function teca_is_multilingual_enabled() {
	if ( ! function_exists( __NAMESPACE__ . '\\plugin' ) || ! plugin()->builder ) {
		return true;
	}

	$prefs = plugin()->builder->_get_shortcode_pref( false );

	return ( $prefs['gs_teca_enable_multilingual'] ?? 'on' ) === 'on';
}

/**
 * Resolve a preference text value with default fallback.
 *
 * @param string $key Preference translation key.
 * @return string
 */
function teca_get_preference_text( $key ) {
	$key = sanitize_key( (string) $key );

	if ( '' === $key || ! function_exists( __NAMESPACE__ . '\\get_translation' ) ) {
		return '';
	}

	$text = trim( (string) get_translation( $key ) );

	if ( '' !== $text ) {
		return $text;
	}

	if ( ! function_exists( __NAMESPACE__ . '\\plugin' ) || ! plugin()->builder ) {
		return '';
	}

	$defaults = plugin()->builder->get_shortcode_default_translations();

	return isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '';
}

/**
 * View Details button label.
 *
 * @return string
 */
function teca_get_view_details_text() {
	return teca_get_preference_text( 'gs_teca_view_details_text' );
}

/**
 * Related Events section title.
 *
 * @param string $shortcode_title Optional shortcode-specific title.
 * @return string
 */
function teca_get_related_events_title_text( $shortcode_title = '' ) {
	if ( ! teca_is_multilingual_enabled() ) {
		return teca_get_preference_text( 'gs_teca_related_events_title' );
	}

	$shortcode_title = trim( (string) $shortcode_title );

	if ( '' !== $shortcode_title ) {
		return $shortcode_title;
	}

	return __( 'Related Events', 'the-events-calendar-addon2' );
}

/**
 * Event Website button label.
 *
 * @return string
 */
function teca_get_event_website_text() {
	return teca_get_preference_text( 'gs_teca_event_website_text' );
}

/**
 * Add to Calendar button label.
 *
 * Uses one global preference value when multilingual is off.
 * Falls back to existing context-specific defaults when unset.
 *
 * @param string $context card|popup|single|compact|table|list.
 * @return string
 */
function teca_get_add_to_calendar_button_text( $context = 'card' ) {
	if ( ! teca_is_multilingual_enabled() ) {
		$custom = trim( (string) get_translation( 'gs_teca_add_to_calendar_text' ) );

		if ( '' !== $custom ) {
			return $custom;
		}
	}

	return teca_get_google_calendar_button_label_default( $context );
}
