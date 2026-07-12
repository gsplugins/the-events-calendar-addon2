<?php
/**
 * Typography custom-flag helpers.
 *
 * Layout CSS is the default. Saved typography applies only when *_typography_custom is true.
 *
 * @package GS_TECA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map typography control group slug to settings key.
 *
 * @return array<string, string>
 */
function teca_get_typography_group_map() {
	return array(
		'title'          => 'title_typography',
		'cat'            => 'cat_typography',
		'tag'            => 'tag_typography',
		'org'            => 'org_typography',
		'date'           => 'date_typography',
		'details'        => 'details_typography',
		'venue'          => 'venue_typography',
		'view_details'   => 'view_details_button_typography',
		'google_calendar' => 'google_calendar_button_typography',
	);
}

/**
 * Whether typography and color typography Pro controls are available.
 *
 * @return bool
 */
function teca_is_typography_color_controls_pro_feature_available() {
	return \GS_TECA\is_pro_active_and_valid();
}

/**
 * Typography group slugs available without Pro.
 *
 * @return string[]
 */
function teca_get_free_typography_group_slugs() {
	return apply_filters(
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
		'gs_teca_free_typography_group_slugs',
		array(
			'title',
		)
	);
}

/**
 * Whether a typography group slug is free.
 *
 * @param string $group Typography group slug.
 * @return bool
 */
function teca_is_free_typography_group( $group ) {
	return in_array( sanitize_key( (string) $group ), teca_get_free_typography_group_slugs(), true );
}

/**
 * Reset a typography group to inactive defaults.
 *
 * @param array  $settings       Shortcode settings.
 * @param string $group          Typography group slug.
 * @param string $typography_key Typography setting key.
 * @return array
 */
function teca_reset_typography_group_settings( array $settings, $group, $typography_key ) {
	$settings[ $typography_key ] = (object) array();
	$settings[ teca_get_typography_custom_flag_key( $typography_key ) ] = (object) teca_get_typography_field_custom_flag_defaults();

	$typography_custom = teca_settings_to_array( $settings['typography_custom'] ?? array() );
	$typography_custom[ $group ] = teca_get_typography_field_custom_flag_defaults();
	$settings['typography_custom'] = $typography_custom;

	return $settings;
}

/**
 * Strip Pro-only typography overrides when Pro is inactive.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_typography_pro_settings( array $settings ) {
	if ( teca_is_typography_color_controls_pro_feature_available() ) {
		return $settings;
	}

	foreach ( teca_get_typography_group_map() as $group => $typography_key ) {
		if ( teca_is_free_typography_group( $group ) ) {
			continue;
		}

		$settings = teca_reset_typography_group_settings( $settings, $group, $typography_key );
	}

	return $settings;
}

/**
 * Typography setting keys stored in shortcode settings.
 *
 * @return string[]
 */
function teca_get_typography_setting_keys() {
	return array_values( teca_get_typography_group_map() );
}

/**
 * Custom flag key for a typography setting key.
 *
 * @param string $typography_key e.g. title_typography.
 * @return string
 */
function teca_get_typography_custom_flag_key( $typography_key ) {
	return $typography_key . '_custom';
}

/**
 * Body class added when a typography group has an active user override.
 *
 * @param string $group Typography group slug e.g. title.
 * @return string
 */
function teca_get_typography_custom_class( $group ) {
	return 'teca-typo-custom--' . sanitize_key( $group );
}

/**
 * Body class for a single active typography field override.
 *
 * @param string $group Typography group slug.
 * @param string $field Normalized field key e.g. font_size.
 * @return string
 */
function teca_get_typography_field_custom_class( $group, $field ) {
	return 'teca-typo-custom--' . sanitize_key( $group ) . '--' . sanitize_key( str_replace( '_', '-', (string) $field ) );
}

/**
 * Default custom flags (all false).
 *
 * @return array<string, bool>
 */
function teca_get_typography_custom_flag_defaults() {
	$defaults = array();

	foreach ( teca_get_typography_setting_keys() as $key ) {
		$defaults[ teca_get_typography_custom_flag_key( $key ) ] = false;
	}

	return $defaults;
}

/**
 * Whether a typography group has an active user override.
 *
 * @param array  $settings Shortcode settings.
 * @param string $group    Group slug e.g. title, date, cat.
 * @return bool
 */
function teca_is_typography_override_active( $settings, $group ) {
	$group = sanitize_key( (string) $group );

	if ( ! teca_is_free_typography_group( $group ) && ! teca_is_typography_color_controls_pro_feature_available() ) {
		return false;
	}

	$flags = teca_get_typography_field_custom_flags( $settings, $group );

	foreach ( $flags as $is_active ) {
		if ( $is_active ) {
			return true;
		}
	}

	return false;
}

/**
 * Normalize a settings value to an array (handles stdClass from JSON decode).
 *
 * @param mixed $value Raw value.
 * @return array
 */
function teca_settings_to_array( $value ) {
	if ( is_object( $value ) ) {
		return (array) $value;
	}

	return is_array( $value ) ? $value : array();
}

/**
 * Normalize field-level typography custom flags.
 *
 * @param mixed $flags Raw flags.
 * @return array<string, bool>
 */
function teca_normalize_typography_field_custom_flags( $flags ) {
	$normalized = teca_get_typography_field_custom_flag_defaults();

	if ( is_object( $flags ) ) {
		$flags = (array) $flags;
	}

	if ( ! is_array( $flags ) ) {
		return $normalized;
	}

	foreach ( teca_get_typography_custom_field_keys() as $field ) {
		$normalized[ $field ] = teca_is_truthy_setting( $flags[ $field ] ?? false );
	}

	return $normalized;
}

/**
 * Get field-level custom flags for a typography group.
 *
 * @param array  $settings Shortcode settings.
 * @param string $group    Group slug.
 * @return array<string, bool>
 */
function teca_get_typography_field_custom_flags( $settings, $group ) {
	$map = teca_get_typography_group_map();

	if ( empty( $map[ $group ] ) || ! is_array( $settings ) ) {
		return teca_get_typography_field_custom_flag_defaults();
	}

	$group = sanitize_key( (string) $group );
	$typography_custom = teca_settings_to_array( $settings['typography_custom'] ?? array() );

	if ( isset( $typography_custom[ $group ] ) ) {
		$raw = $typography_custom[ $group ];

		if ( is_object( $raw ) ) {
			$raw = (array) $raw;
		}

		if ( is_array( $raw ) ) {
			return teca_normalize_typography_field_custom_flags( $raw );
		}
	}

	$custom_key = teca_get_typography_custom_flag_key( $map[ $group ] );
	$raw        = $settings[ $custom_key ] ?? array();

	if ( is_object( $raw ) ) {
		$raw = (array) $raw;
	}

	if ( is_array( $raw ) ) {
		return teca_normalize_typography_field_custom_flags( $raw );
	}

	// Legacy group-level boolean flags must not activate fields or old saved values.
	return teca_get_typography_field_custom_flag_defaults();
}

/**
 * Whether a single typography field has an active override.
 *
 * @param array  $settings       Shortcode settings.
 * @param string $typography_key Typography setting key.
 * @param string $field_key      Normalized field key.
 * @return bool
 */
function teca_should_apply_typography_field( $settings, $typography_key, $field_key ) {
	$group = array_search( $typography_key, teca_get_typography_group_map(), true );

	if ( false === $group ) {
		return false;
	}

	if ( ! teca_is_free_typography_group( $group ) && ! teca_is_typography_color_controls_pro_feature_available() ) {
		return false;
	}

	$flags = teca_get_typography_field_custom_flags( $settings, $group );

	return ! empty( $flags[ $field_key ] );
}

/**
 * Whether custom typography CSS should be generated for a group.
 *
 * @param array  $settings       Shortcode settings.
 * @param string $typography_key Typography setting key.
 * @return bool
 */
function teca_should_apply_typography( $settings, $typography_key ) {
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return false;
	}

	$group = array_search( $typography_key, teca_get_typography_group_map(), true );

	if ( false === $group ) {
		return false;
	}

	return teca_is_typography_override_active( $settings, $group );
}

/**
 * Body classes for active typography overrides.
 *
 * Typography font overrides are applied via instance-scoped inline CSS only.
 * Do not add group body classes here: _typography.scss group rules also set
 * color/background from global CSS variables and would override cat/tag color typography.
 *
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_typography_custom_body_classes( $settings ) {
	return array();
}

/**
 * Remove typography values that do not have an active field-level custom flag.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_strip_inactive_typography_values( array $settings ) {
	$control_map = teca_get_typography_control_to_custom_field_map();

	foreach ( teca_get_typography_group_map() as $group => $typography_key ) {
		$flags      = teca_get_typography_field_custom_flags( $settings, $group );
		$typography = (array) ( $settings[ $typography_key ] ?? array() );

		foreach ( $typography as $control_key => $value ) {
			$field_key = $control_map[ $control_key ] ?? $control_key;

			if ( '' === $field_key || empty( $flags[ $field_key ] ) ) {
				unset( $typography[ $control_key ] );
			}
		}

		$settings[ $typography_key ] = $typography;
	}

	return $settings;
}

/**
 * Keys that belong to color typography, not font typography.
 *
 * @return string[]
 */
function teca_get_typography_color_keys() {
	return array( 'color', 'backgroundColor', 'hoverColor', 'hoverBgColor' );
}

/**
 * Check whether a typography settings array has any non-empty font values.
 *
 * @param array $setting Typography values.
 * @return bool
 */
function teca_typography_has_values( $setting, $field_custom = null ) {
	$typo = teca_normalize_typography_value( $setting );

	if ( is_array( $field_custom ) ) {
		foreach ( $typo as $field => $value ) {
			if ( empty( $field_custom[ $field ] ) ) {
				continue;
			}

			if ( '' !== $value && null !== $value ) {
				return true;
			}
		}

		return false;
	}

	foreach ( $typo as $value ) {
		if ( '' !== $value && null !== $value ) {
			return true;
		}
	}

	return false;
}

/**
 * Normalize truthy values from shortcode settings (boolean, on/off, 1/0).
 *
 * @param mixed $value Setting value.
 * @return bool
 */
function teca_is_truthy_setting( $value ) {
	if ( is_bool( $value ) ) {
		return $value;
	}

	if ( is_numeric( $value ) ) {
		return (bool) (int) $value;
	}

	if ( is_string( $value ) ) {
		$value = strtolower( trim( $value ) );
		return in_array( $value, array( '1', 'true', 'on', 'yes' ), true );
	}

	return ! empty( $value );
}

/**
 * Ensure typography custom flags exist and legacy values stay inactive.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_normalize_typography_custom_flags( $settings ) {
	if ( ! is_array( $settings ) ) {
		return $settings;
	}

	foreach ( teca_get_typography_group_map() as $group => $key ) {
		$custom_key                 = teca_get_typography_custom_flag_key( $key );
		$settings[ $custom_key ]    = teca_get_typography_field_custom_flags( $settings, $group );
	}

	return $settings;
}

/**
 * Prepare typography settings for frontend/admin render.
 *
 * Legacy saved typography without an explicit custom flag stays inactive.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_prepare_typography_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return $settings;
	}

	$settings = teca_sanitize_typography_pro_settings( $settings );
	$settings = teca_normalize_typography_custom_flags( $settings );

	// Nested compatibility shape: typography_custom.title = true|false.
	$settings['typography_custom'] = teca_settings_to_array( $settings['typography_custom'] ?? array() );

	foreach ( teca_get_typography_group_map() as $group => $typography_key ) {
		$custom_key = teca_get_typography_custom_flag_key( $typography_key );
		$flags      = teca_get_typography_field_custom_flags( $settings, $group );

		$settings['typography_custom'][ $group ] = $flags;
		$settings[ $custom_key ]                 = $flags;
	}

	$settings = teca_strip_typography_color_values( $settings );
	$settings = teca_strip_inactive_typography_values( $settings );

	return $settings;
}

/**
 * Normalize typography field keys from control UI / legacy saves.
 *
 * @param array|object|null $typography Raw typography settings.
 * @return array<string, string>
 */
function teca_normalize_typography_value( $typography ) {
	$empty = array(
		'font_family'     => '',
		'font_size'       => '',
		'font_weight'     => '',
		'line_height'     => '',
		'letter_spacing'  => '',
		'text_transform'  => '',
		'text_decoration' => '',
		'font_style'      => '',
	);

	if ( is_object( $typography ) ) {
		$typography = (array) $typography;
	}

	if ( empty( $typography ) || ! is_array( $typography ) ) {
		return $empty;
	}

	$pick = static function( array $source, array $keys ) {
		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $source ) ) {
				continue;
			}

			$value = $source[ $key ];

			if ( null === $value ) {
				continue;
			}

			if ( is_string( $value ) ) {
				$value = trim( $value );
			}

			if ( '' === $value && '0' !== (string) $value ) {
				continue;
			}

			return $value;
		}

		return '';
	};

	return array(
		'font_family'     => (string) $pick( $typography, array( 'getFonts', 'font_family', 'font-family', 'fontFamily' ) ),
		'font_size'       => $pick( $typography, array( 'size', 'font_size', 'font-size', 'fontSize', 'typography_size' ) ),
		'font_weight'     => (string) $pick( $typography, array( 'weight', 'font_weight', 'font-weight', 'fontWeight' ) ),
		'line_height'     => $pick( $typography, array( 'lineHeight', 'line_height', 'line-height' ) ),
		'letter_spacing'  => $pick( $typography, array( 'letterSpacing', 'letter_spacing', 'letter-spacing' ) ),
		'text_transform'  => (string) $pick( $typography, array( 'transform', 'text_transform', 'text-transform', 'textTransform' ) ),
		'text_decoration' => (string) $pick( $typography, array( 'decoration', 'text_decoration', 'text-decoration', 'textDecoration' ) ),
		'font_style'      => (string) $pick( $typography, array( 'style', 'font_style', 'font-style', 'fontStyle' ) ),
	);
}

/**
 * @param mixed $value Font family value.
 * @return string
 */
function teca_format_typography_font_family( $value ) {
	if ( '' === $value || null === $value ) {
		return '';
	}

	$font = trim( (string) $value );

	if ( strpos( $font, ',' ) !== false ) {
		return $font;
	}

	return '"' . $font . '", sans-serif';
}

/**
 * @param mixed $value Font size value.
 * @return string
 */
function teca_format_typography_size_value( $value ) {
	if ( '' === $value || null === $value ) {
		return '';
	}

	if ( is_array( $value ) ) {
		$size = $value['size'] ?? '';
		$unit = (string) ( $value['unit'] ?? 'px' );

		if ( '' === $size || null === $size ) {
			return '';
		}

		if ( is_numeric( $size ) ) {
			return $size . $unit;
		}

		return (string) $size;
	}

	if ( is_numeric( $value ) ) {
		return $value . 'px';
	}

	return (string) $value;
}

/**
 * @param mixed $value Letter spacing value.
 * @return string
 */
function teca_format_typography_letter_spacing_value( $value ) {
	if ( '' === $value || null === $value ) {
		return '';
	}

	if ( is_array( $value ) ) {
		$size = $value['size'] ?? '';
		$unit = (string) ( $value['unit'] ?? 'px' );

		if ( '' === $size || null === $size ) {
			return '';
		}

		if ( is_numeric( $size ) ) {
			return $size . $unit;
		}

		return (string) $size;
	}

	if ( is_numeric( $value ) ) {
		return $value . 'px';
	}

	return (string) $value;
}

/**
 * @param mixed $value Font weight value.
 * @return string
 */
function teca_format_typography_weight_value( $value ) {
	if ( '' === $value || null === $value ) {
		return '';
	}

	return (string) $value;
}

/**
 * Build CSS declaration list from normalized typography values.
 *
 * @param array|object|null $typography   Raw typography settings.
 * @param array|null        $field_custom Field-level custom flags.
 * @return string[]
 */
function teca_get_typography_css_rules( $typography, $field_custom = null ) {
	$typo  = teca_normalize_typography_value( $typography );
	$rules = array();

	$apply = static function( $field, $rule ) use ( $field_custom, &$rules ) {
		if ( is_array( $field_custom ) && empty( $field_custom[ $field ] ) ) {
			return;
		}

		$rules[] = $rule;
	};

	if ( '' !== $typo['font_family'] ) {
		$apply( 'font_family', 'font-family:' . esc_attr( teca_format_typography_font_family( $typo['font_family'] ) ) );
	}

	if ( '' !== $typo['font_size'] ) {
		$apply( 'font_size', 'font-size:' . esc_attr( teca_format_typography_size_value( $typo['font_size'] ) ) . ' !important' );
	}

	if ( '' !== $typo['font_weight'] ) {
		$apply( 'font_weight', 'font-weight:' . esc_attr( teca_format_typography_weight_value( $typo['font_weight'] ) ) . ' !important' );
	}

	if ( '' !== $typo['line_height'] ) {
		$apply( 'line_height', 'line-height:' . esc_attr( (string) $typo['line_height'] ) );
	}

	if ( '' !== $typo['letter_spacing'] ) {
		$apply( 'letter_spacing', 'letter-spacing:' . esc_attr( teca_format_typography_letter_spacing_value( $typo['letter_spacing'] ) ) );
	}

	if ( '' !== $typo['text_transform'] ) {
		$apply( 'text_transform', 'text-transform:' . esc_attr( $typo['text_transform'] ) );
	}

	if ( array_key_exists( 'text_decoration', $typo ) && '' !== $typo['text_decoration'] ) {
		$apply( 'text_decoration', 'text-decoration:' . esc_attr( $typo['text_decoration'] ) . ' !important' );
	}

	if ( '' !== $typo['font_style'] ) {
		$apply( 'font_style', 'font-style:' . esc_attr( $typo['font_style'] ) );
	}

	return $rules;
}

/**
 * Build a single CSS declaration for one typography field.
 *
 * @param array|object|null $typography   Raw typography settings.
 * @param string            $field        Normalized field key e.g. font_size.
 * @return string
 */
function teca_get_typography_field_css_rule( $typography, $field ) {
	$typo  = teca_normalize_typography_value( $typography );
	$field = sanitize_key( (string) $field );

	switch ( $field ) {
		case 'font_family':
			if ( '' === $typo['font_family'] ) {
				return '';
			}
			return 'font-family:' . esc_attr( teca_format_typography_font_family( $typo['font_family'] ) );

		case 'font_size':
			if ( '' === $typo['font_size'] ) {
				return '';
			}
			return 'font-size:' . esc_attr( teca_format_typography_size_value( $typo['font_size'] ) ) . ' !important';

		case 'font_weight':
			if ( '' === $typo['font_weight'] ) {
				return '';
			}
			return 'font-weight:' . esc_attr( teca_format_typography_weight_value( $typo['font_weight'] ) ) . ' !important';

		case 'line_height':
			if ( '' === $typo['line_height'] ) {
				return '';
			}
			return 'line-height:' . esc_attr( (string) $typo['line_height'] );

		case 'letter_spacing':
			if ( '' === $typo['letter_spacing'] ) {
				return '';
			}
			return 'letter-spacing:' . esc_attr( teca_format_typography_letter_spacing_value( $typo['letter_spacing'] ) );

		case 'text_transform':
			if ( '' === $typo['text_transform'] ) {
				return '';
			}
			return 'text-transform:' . esc_attr( $typo['text_transform'] );

		case 'text_decoration':
			if ( ! array_key_exists( 'text_decoration', $typo ) || '' === $typo['text_decoration'] ) {
				return '';
			}
			return 'text-decoration:' . esc_attr( $typo['text_decoration'] ) . ' !important';

		case 'font_style':
			if ( '' === $typo['font_style'] ) {
				return '';
			}
			return 'font-style:' . esc_attr( $typo['font_style'] );
	}

	return '';
}

/**
 * Base element selectors for a typography/color group (no layout scope prefix).
 *
 * @param string $group Group slug.
 * @return string[]
 */
function teca_get_typography_element_selectors( $group ) {
	$group = sanitize_key( (string) $group );

	$map = array(
		'title'   => array(
			'.teca-event-title',
			'.teca-event-title a',
			'.gs-teca-title',
			'.gs-teca-title a',
			'.teca-accordion-title',
			'.teca-accordion-title a',
			'.teca-timeline-title',
			'.teca-timeline-title a',
		),
		'view_details' => array(
			'.teca-view-details',
			'.teca-view-details a',
			'.teca-view-details button',
			'.gs-teca-view-details',
			'.gs-teca-view-details a',
			'.gs-teca-view-details button',
			'.gs-teca-view-details .teca-event-button',
			'.gs-teca-view-details .gs-teca-btn-popup',
			'.gs-teca-view-details .gs-teca-btn-link',
			'.gs-teca-btn-popup',
			'.gs-teca-btn-link',
			'.gs-teca-action-btn',
			'.teca-single-button',
			'.teca-single-button.teca-event-button',
			'.teca-accordion-button',
			'.teca-accordion-button a',
			'.teca-timeline-button',
			'.teca-timeline-button a',
			'.teca-popup-button.teca-view-details',
			'.teca-popup-actions .teca-view-details',
			'[class*="teca-grid-style-"][class*="-button"]',
			'[class*="teca-style-"][class*="-link"]',
		),
		'google_calendar' => array(
			'.teca-google-calendar-btn',
			'.teca-google-calendar-btn span',
			'.teca-google-calendar-actions .teca-google-calendar-btn',
			'.teca-google-calendar-actions .teca-google-calendar-btn span',
			'.teca-popup-actions .teca-google-calendar-btn',
			'.teca-google-calendar-btn--card',
			'.teca-google-calendar-btn--table',
			'.teca-list-google-calendar-wrap .teca-google-calendar-btn',
		),
		'cat'     => array(
			'.teca-event-category',
			'.teca-event-category a',
			'.teca-event-categories .teca-event-category',
			'.teca-event-categories .teca-event-category a',
			'.teca-event-categories a',
			'.teca-event-categories span',
			'.gs-teca-categories .gs-teca-category',
			'.gs-teca-categories .gs-teca-category a',
			'.gs-teca-categories a',
			'.gs-teca-categories span',
			'.gs-teca-cat',
			'.gs-teca-cat a',
		),
		'tag'     => array(
			'.teca-event-tag',
			'.teca-event-tag a',
			'.teca-event-tags .teca-event-tag',
			'.teca-event-tags .teca-event-tag a',
			'.teca-event-tags a',
			'.gs-teca-tags .gs-teca-tag',
			'.gs-teca-tags .gs-teca-tag a',
			'.gs-teca-tags span.gs-teca-tag',
			'.gs-teca-tag',
			'.gs-teca-tag a',
		),
		'org'     => array(
			'.teca-event-organizer',
			'.teca-event-organizer a',
			'.gs-teca-organizers',
			'.gs-teca-organizers a',
			'.gs-teca-org',
			'.gs-teca-org a',
		),
		'date'    => array(
			'.teca-event-date',
			'.teca-event-date span',
			'.teca-event-time',
			'.teca-event-time-value',
			'.gs-teca-date',
			'.gs-teca-date a',
			'.gs-teca-glass-date',
			'.gs-teca-glass-date a',
		),
		'details' => array(
			'.teca-event-details',
			'.teca-event-details a',
			'.teca-event-excerpt',
			'.teca-event-excerpt a',
			'.teca-event-meta',
			'.teca-event-meta a',
			'.gs-teca-details',
			'.gs-teca-details a',
			'.gs-teca-desc',
			'.gs-teca-desc a',
			'.gs-teca-excerpt',
			'.gs-teca-excerpt a',
		),
		'venue'   => array(
			'.teca-event-venue',
			'.teca-event-venue a',
			'.gs-teca-venue',
			'.gs-teca-venue a',
		),
	);

	return $map[ $group ] ?? array();
}

/**
 * Build instance + layout scoped selectors.
 *
 * @param string   $instance_sel   Instance selector.
 * @param string   $scope_class    Layout scope class.
 * @param string   $modifier_class Custom modifier class.
 * @param string[] $base_selectors Base element selectors.
 * @return string[]
 */
function teca_build_instance_scoped_selectors( $instance_sel, $scope_class, $modifier_class, array $base_selectors ) {
	$instance_sel   = trim( (string) $instance_sel );
	$scope_class    = sanitize_html_class( (string) $scope_class );
	$modifier_class = sanitize_html_class( (string) $modifier_class );
	$selectors      = array();

	if ( '' === $instance_sel || '' === $scope_class || '' === $modifier_class || empty( $base_selectors ) ) {
		return $selectors;
	}

	$root = $instance_sel . '.' . $scope_class . '.' . $modifier_class;

	foreach ( $base_selectors as $selector ) {
		$selectors[] = $root . ' ' . $selector;
	}

	return array_values( array_unique( $selectors ) );
}

/**
 * Build instance + layout scoped selectors without a custom modifier class.
 *
 * @param string   $instance_sel   Instance selector.
 * @param string   $scope_class    Layout scope class.
 * @param string[] $base_selectors Base element selectors.
 * @return string[]
 */
function teca_build_instance_layout_scoped_selectors( $instance_sel, $scope_class, array $base_selectors ) {
	$instance_sel = trim( (string) $instance_sel );
	$scope_class  = sanitize_html_class( (string) $scope_class );
	$selectors    = array();

	if ( '' === $instance_sel || '' === $scope_class || empty( $base_selectors ) ) {
		return $selectors;
	}

	$root = $instance_sel . '.' . $scope_class;

	foreach ( $base_selectors as $selector ) {
		$selectors[] = $root . ' ' . $selector;
	}

	return array_values( array_unique( $selectors ) );
}

/**
 * Element selectors scoped under a layout class (used by color CSS).
 *
 * @param string $group       Group slug.
 * @param string $scope_class Layout scope class.
 * @return string[]
 */
function teca_get_typography_element_targets( $group, $scope_class ) {
	$scope_class = sanitize_html_class( (string) $scope_class );
	$selectors   = teca_get_typography_element_selectors( $group );
	$targets     = array();

	foreach ( $selectors as $selector ) {
		$targets[] = '.' . $scope_class . ' ' . $selector;
	}

	return $targets;
}

/**
 * Active layout/style scope class for typography and color CSS.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_typography_scope_class( $settings ) {
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return '';
	}

	$view_type = (string) ( $settings['view_type'] ?? '' );

	if ( 'accordion' === $view_type ) {
		$map   = array(
			'gs-teca-accordion-1' => 'teca-accordion-1',
			'gs-teca-accordion-2' => 'teca-accordion-2',
			'gs-teca-accordion-3' => 'teca-accordion-3',
		);
		$theme = (string) ( $settings['gs_teca_template'] ?? '' );

		return $map[ $theme ] ?? 'teca-accordion-1';
	}

	if ( 'timeline' === $view_type ) {
		$map   = array(
			'gs-teca-timeline-1' => 'teca-timeline-1',
			'gs-teca-timeline-2' => 'teca-timeline-2',
			'gs-teca-timeline-3' => 'teca-timeline-3',
		);
		$theme = (string) ( $settings['gs_teca_template'] ?? '' );

		return $map[ $theme ] ?? 'teca-timeline-1';
	}

	if ( 'events-section' === $view_type ) {
		$layout = function_exists( 'teca_get_selected_event_layout' )
			? teca_get_selected_event_layout( $settings )
			: (string) ( $settings['event_layout'] ?? 'event-layout-1' );

		if ( preg_match( '/event-layout-(\d+)/', $layout, $matches ) ) {
			return 'teca-events-layout-' . $matches[1];
		}

		return 'teca-events-layout-1';
	}

	if ( 'calendar' === $view_type ) {
		$calendar_layout = (string) ( $settings['calendar_layout'] ?? 'calendar-layout-1' );
		if ( function_exists( 'teca_get_calendar_layout_legacy_slug' ) ) {
			return 'teca-' . teca_get_calendar_layout_legacy_slug( $calendar_layout );
		}
		return 'teca-daily-layout-1';
	}

	$template = (string) ( $settings['gs_teca_template'] ?? '' );
	if ( preg_match( '/gs-teca-style-(\d+)/', $template, $matches ) ) {
		return 'teca-style-' . $matches[1];
	}

	if ( preg_match( '/gs-teca-list-(\d+)/', $template, $matches ) ) {
		return 'teca-list-' . $matches[1];
	}

	if ( preg_match( '/gs-teca-table-(\d+)/', $template, $matches ) ) {
		return 'teca-table-' . $matches[1];
	}

	if ( preg_match( '/gs-teca-filter-(\d+)/', $template, $matches ) ) {
		return 'teca-filter-' . $matches[1];
	}

	return sanitize_html_class( str_replace( 'gs-teca-', 'teca-', $template ) );
}

/**
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_typography_scope_body_classes( $settings ) {
	$scope = teca_get_typography_scope_class( $settings );

	return $scope ? array( $scope ) : array();
}

/**
 * Resolve the effective title font family from layout preset + saved overrides.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_resolved_title_font_family_from_settings( array $settings ) {
	$scope = teca_get_typography_scope_class( $settings );

	if ( '' === $scope ) {
		$scope = 'teca-style-1';
	}

	$map = teca_get_typography_group_map();
	$merged = teca_merge_typography_group_with_preset(
		$scope,
		'title',
		$settings[ $map['title'] ?? 'title_typography' ] ?? array(),
		teca_get_typography_field_custom_flags( $settings, 'title' )
	);

	$font_family = trim( (string) ( $merged['font_family'] ?? '' ) );

	if ( '' === $font_family ) {
		$font_family = teca_get_default_title_font_family();
	}

	return $font_family;
}

/**
 * Render instance + layout scoped default title font-family from presets.
 *
 * @param array  $settings     Shortcode settings.
 * @param string $instance_sel Instance selector e.g. #gs_teca_area_123.
 * @return string
 */
function teca_render_typography_preset_scoped_css( array $settings, $instance_sel ) {
	$instance_sel = trim( (string) $instance_sel );
	$scope_class  = teca_get_typography_scope_class( $settings );

	if ( '' === $instance_sel || '' === $scope_class ) {
		return '';
	}

	$scope_class = sanitize_html_class( $scope_class );
	$font_family = teca_get_resolved_title_font_family_from_settings( $settings );

	if ( '' === $font_family ) {
		return '';
	}

	$selectors = teca_build_instance_layout_scoped_selectors(
		$instance_sel,
		$scope_class,
		teca_get_typography_element_selectors( 'title' )
	);

	if ( empty( $selectors ) ) {
		return '';
	}

	$rule = 'font-family:' . esc_attr( teca_format_typography_font_family( $font_family ) );

	return implode( ',', $selectors ) . '{' . $rule . '}';
}

/**
 * Render instance + layout scoped typography CSS (font properties only).
 *
 * @param array  $settings     Shortcode settings.
 * @param string $instance_sel Instance selector e.g. #gs_teca_area_123.
 * @return string
 */
function teca_render_typography_scoped_css( array $settings, $instance_sel ) {
	$instance_sel = trim( (string) $instance_sel );
	$scope_class  = teca_get_typography_scope_class( $settings );
	$css          = '';

	if ( '' === $instance_sel || '' === $scope_class ) {
		return $css;
	}

	$scope_class = sanitize_html_class( $scope_class );

	foreach ( teca_get_typography_group_map() as $group => $typography_key ) {
		if ( ! teca_is_typography_override_active( $settings, $group ) ) {
			continue;
		}

		$field_custom = teca_get_typography_field_custom_flags( $settings, $group );
		$merged       = teca_merge_typography_group_with_preset(
			$scope_class,
			$group,
			$settings[ $typography_key ] ?? array(),
			$field_custom
		);
		$control      = teca_typography_defaults_to_control_format( $merged );
		$rules        = teca_get_typography_css_rules( $control, $field_custom );
		$selectors    = teca_build_instance_layout_scoped_selectors(
			$instance_sel,
			$scope_class,
			teca_get_typography_element_selectors( $group )
		);

		if ( empty( $rules ) || empty( $selectors ) ) {
			continue;
		}

		$css .= implode( ',', $selectors ) . '{' . implode( ';', $rules ) . '}';
	}

	return $css;
}
