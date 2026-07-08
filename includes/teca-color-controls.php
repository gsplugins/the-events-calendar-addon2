<?php
/**
 * Color typography controls — separate from font typography.
 *
 * @package GS_TECA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color field suffixes per typography group.
 *
 * @return array<string, array{property:string, pseudo:string}>
 */
function teca_get_color_field_types() {
	return array(
		'color'             => array(
			'property' => 'color',
			'pseudo'   => '',
		),
		'background_color'  => array(
			'property' => 'background-color',
			'pseudo'   => '',
		),
		'hover_color'       => array(
			'property' => 'color',
			'pseudo'   => ':hover',
		),
		'hover_background_color' => array(
			'property' => 'background-color',
			'pseudo'   => ':hover',
		),
	);
}

/**
 * All color field keys mapped to group + CSS property metadata.
 *
 * @return array<string, array{group:string, property:string, pseudo:string, label:string}>
 */
function teca_get_color_typography_field_map() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$map   = array();
	$types = teca_get_color_field_types();

	foreach ( teca_get_typography_group_map() as $group => $typography_key ) {
		foreach ( $types as $suffix => $type ) {
			$field_key = $group . '_' . $suffix;
			$label     = ucwords( str_replace( '_', ' ', $group ) ) . ' ' . ucwords( str_replace( '_', ' ', $suffix ) );

			$map[ $field_key ] = array(
				'group'    => $group,
				'property' => $type['property'],
				'pseudo'   => $type['pseudo'],
				'label'    => $label,
			);
		}
	}

	return $map;
}

/**
 * @return string[]
 */
function teca_get_color_typography_field_keys() {
	return array_keys( teca_get_color_typography_field_map() );
}

/**
 * Color typography field keys available without Pro.
 *
 * @return string[]
 */
function teca_get_free_color_typography_field_keys() {
	return apply_filters(
		'gs_teca_free_color_typography_field_keys',
		array(
			'title_color',
			'title_background_color',
			'title_hover_color',
			'title_hover_background_color',
		)
	);
}

/**
 * Whether a color typography field key is free.
 *
 * @param string $field_key Color field key.
 * @return bool
 */
function teca_is_free_color_typography_field( $field_key ) {
	return in_array( sanitize_key( (string) $field_key ), teca_get_free_color_typography_field_keys(), true );
}

/**
 * Reset a color typography field to inactive defaults.
 *
 * @param array  $settings  Shortcode settings.
 * @param string $field_key Color field key.
 * @return array
 */
function teca_reset_color_typography_field_settings( array $settings, $field_key ) {
	$field_key  = sanitize_key( (string) $field_key );
	$custom_key = teca_get_color_custom_flag_key( $field_key );

	$settings[ $field_key ] = '';

	if ( ! isset( $settings['color_custom'] ) || ! is_array( $settings['color_custom'] ) ) {
		$settings['color_custom'] = array();
	}

	$settings['color_custom'][ $field_key ] = false;
	$settings[ $custom_key ]                = false;

	return $settings;
}

/**
 * Strip Pro-only color typography overrides when Pro is inactive.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_color_typography_pro_settings( array $settings ) {
	if ( teca_is_typography_color_controls_pro_feature_available() ) {
		return $settings;
	}

	foreach ( teca_get_color_typography_field_keys() as $field_key ) {
		if ( teca_is_free_color_typography_field( $field_key ) ) {
			continue;
		}

		$settings = teca_reset_color_typography_field_settings( $settings, $field_key );
	}

	return $settings;
}

function teca_get_color_custom_flag_key( $field_key ) {
	return sanitize_key( (string) $field_key ) . '_custom';
}

function teca_get_color_custom_class( $field_key ) {
	return 'teca-color-custom--' . sanitize_key( (string) $field_key );
}

function teca_is_color_field_custom_active( $settings, $field_key ) {
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return false;
	}

	$field_key  = sanitize_key( (string) $field_key );
	$custom_key = teca_get_color_custom_flag_key( $field_key );

	if ( isset( $settings['color_custom'][ $field_key ] ) ) {
		return teca_is_truthy_setting( $settings['color_custom'][ $field_key ] );
	}

	return teca_is_truthy_setting( $settings[ $custom_key ] ?? false );
}

function teca_should_apply_color( $settings, $field_key ) {
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return false;
	}

	$field_key = sanitize_key( (string) $field_key );

	if ( ! teca_is_free_color_typography_field( $field_key ) && ! teca_is_typography_color_controls_pro_feature_available() ) {
		return false;
	}

	if ( ! teca_is_color_field_custom_active( $settings, $field_key ) ) {
		return false;
	}

	$value = (string) ( $settings[ $field_key ] ?? '' );

	return '' !== $value;
}

/**
 * Element selectors for a color field (text vs background may differ).
 *
 * @param string $group     Typography group slug.
 * @param string $field_key Color field key e.g. cat_color.
 * @return string[]
 */
function teca_get_color_element_selectors( $group, $field_key ) {
	$group     = sanitize_key( (string) $group );
	$field_key = sanitize_key( (string) $field_key );
	$is_background = false !== strpos( $field_key, 'background' );

	if ( 'cat' === $group ) {
		if ( $is_background ) {
			return array( '.teca-event-category' );
		}

		return array(
			'.teca-event-category',
			'.teca-event-category a',
			'.teca-event-categories .teca-event-category',
			'.teca-event-categories a',
		);
	}

	if ( 'tag' === $group ) {
		if ( $is_background ) {
			return array( '.teca-event-tag' );
		}

		return array(
			'.teca-event-tag',
			'.teca-event-tag a',
			'.teca-event-tags .teca-event-tag',
			'.teca-event-tags a',
		);
	}

	if ( 'view_details' === $group ) {
		$button_selectors = array(
			'.gs-teca-view-details .gs-teca-btn-popup',
			'.gs-teca-view-details .gs-teca-btn-link',
			'.gs-teca-view-details .teca-event-button',
			'.gs-teca-btn-popup',
			'.gs-teca-btn-link',
			'.gs-teca-action-btn',
			'.teca-accordion-button',
			'.teca-accordion-button a',
			'.teca-timeline-button',
			'.teca-timeline-button a',
			'.teca-single-button',
			'.teca-single-button.teca-event-button',
			'[class*="teca-grid-style-"][class*="-button"]',
			'[class*="teca-style-"][class*="-link"]',
		);

		if ( $is_background ) {
			return $button_selectors;
		}

		$text_selectors = $button_selectors;
		$text_selectors[] = '.gs-teca-view-details .gs-teca-btn-popup span';
		$text_selectors[] = '.gs-teca-view-details .gs-teca-btn-link span';

		return array_values( array_unique( $text_selectors ) );
	}

	if ( 'google_calendar' === $group ) {
		$button_selectors = array(
			'.teca-google-calendar-btn',
			'.teca-google-calendar-actions .teca-google-calendar-btn',
			'.teca-google-calendar-btn--card',
			'.teca-google-calendar-btn--table',
			'.teca-list-google-calendar-wrap .teca-google-calendar-btn',
		);

		if ( $is_background ) {
			return $button_selectors;
		}

		$text_selectors = $button_selectors;
		$text_selectors[] = '.teca-google-calendar-btn span';
		$text_selectors[] = '.teca-google-calendar-actions .teca-google-calendar-btn span';

		return array_values( array_unique( $text_selectors ) );
	}

	$selectors = teca_get_typography_element_selectors( $group );

	if ( ! $is_background ) {
		return $selectors;
	}

	$background_selectors = array();

	foreach ( $selectors as $selector ) {
		if ( false === strpos( $selector, ' a' ) ) {
			$background_selectors[] = $selector;
		}
	}

	return ! empty( $background_selectors ) ? $background_selectors : $selectors;
}

/**
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_color_custom_body_classes( $settings ) {
	$classes = array();

	foreach ( teca_get_color_typography_field_keys() as $field_key ) {
		if ( teca_should_apply_color( $settings, $field_key ) ) {
			$classes[] = teca_get_color_custom_class( $field_key );
		}
	}

	return $classes;
}

/**
 * Migrate legacy color values stored inside typography objects.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_migrate_legacy_typography_colors( array $settings ) {
	// Legacy group-level typography flags must not activate color overrides.
	return $settings;
}

/**
 * Strip color keys from typography setting objects.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_strip_typography_color_values( array $settings ) {
	$strip = array( 'color', 'backgroundColor', 'hoverColor', 'hoverBgColor' );

	foreach ( teca_get_typography_setting_keys() as $typography_key ) {
		$typography = (array) ( $settings[ $typography_key ] ?? array() );

		foreach ( $strip as $key ) {
			unset( $typography[ $key ] );
		}

		$settings[ $typography_key ] = $typography;
	}

	return $settings;
}

function teca_normalize_color_custom_flags( array $settings ) {
	foreach ( teca_get_color_typography_field_keys() as $field_key ) {
		$custom_key              = teca_get_color_custom_flag_key( $field_key );
		$settings[ $custom_key ] = teca_is_color_field_custom_active( $settings, $field_key );
	}

	return $settings;
}

/**
 * Remove color values that do not have an active field-level custom flag.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_strip_inactive_color_values( array $settings ) {
	foreach ( teca_get_color_typography_field_keys() as $field_key ) {
		if ( ! teca_should_apply_color( $settings, $field_key ) ) {
			$settings[ $field_key ] = '';
		}
	}

	return $settings;
}

/**
 * Prepare color settings for frontend/admin.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_prepare_color_settings( array $settings ) {
	if ( ! is_array( $settings ) ) {
		return $settings;
	}

	$settings = teca_sanitize_color_typography_pro_settings( $settings );
	$settings = teca_migrate_legacy_typography_colors( $settings );
	$settings = teca_strip_typography_color_values( $settings );
	$settings = teca_normalize_color_custom_flags( $settings );
	$settings = teca_strip_inactive_color_values( $settings );

	if ( ! isset( $settings['color_custom'] ) || ! is_array( $settings['color_custom'] ) ) {
		$settings['color_custom'] = array();
	}

	foreach ( teca_get_color_typography_field_keys() as $field_key ) {
		$custom_key = teca_get_color_custom_flag_key( $field_key );
		$is_active  = teca_is_color_field_custom_active( $settings, $field_key );

		$settings['color_custom'][ $field_key ] = $is_active;
		$settings[ $custom_key ]                = $is_active;
	}

	return $settings;
}

/**
 * Render instance + layout scoped color CSS.
 *
 * @param array  $settings     Shortcode settings.
 * @param string $instance_sel Instance selector.
 * @return string
 */
function teca_render_color_scoped_css( array $settings, $instance_sel ) {
	$instance_sel = trim( (string) $instance_sel );
	$scope_class  = teca_get_typography_scope_class( $settings );
	$css          = '';

	if ( '' === $instance_sel || '' === $scope_class ) {
		return $css;
	}

	$scope_class = sanitize_html_class( $scope_class );
	$field_map   = teca_get_color_typography_field_map();

	foreach ( $field_map as $field_key => $meta ) {
		if ( ! teca_should_apply_color( $settings, $field_key ) ) {
			continue;
		}

		$value     = (string) ( $settings[ $field_key ] ?? '' );
		$group     = $meta['group'];
		$property  = $meta['property'];
		$pseudo    = (string) ( $meta['pseudo'] ?? '' );
		$selectors = teca_build_instance_layout_scoped_selectors(
			$instance_sel,
			$scope_class,
			teca_get_color_element_selectors( $group, $field_key )
		);

		if ( empty( $selectors ) || '' === $value ) {
			continue;
		}

		$output_selectors = array();

		foreach ( $selectors as $base ) {
			if ( '' !== $pseudo ) {
				$output_selectors[] = $base . $pseudo;
			} else {
				$output_selectors[] = $base;
			}
		}

		$css .= implode( ',', array_unique( $output_selectors ) ) . '{' . $property . ':' . esc_attr( $value ) . ' !important}';
	}

	return $css;
}

/**
 * Color field options for shortcode builder UI.
 *
 * @return array<int, array{label:string, value:string, group:string}>
 */
function teca_get_color_typography_select_options() {
	$options = array();

	foreach ( teca_get_color_typography_field_map() as $field_key => $meta ) {
		$options[] = array(
			'label' => $meta['label'],
			'value' => $field_key,
			'group' => $meta['group'],
			'pro'   => ! teca_is_free_color_typography_field( $field_key ),
		);
	}

	return $options;
}

/**
 * Free-only color field options for shortcode builder UI.
 *
 * @return array<int, array{label:string, value:string, group:string}>
 */
function teca_get_free_color_typography_select_options() {
	return array_values(
		array_filter(
			teca_get_color_typography_select_options(),
			static function( $option ) {
				return teca_is_free_color_typography_field( $option['value'] ?? '' );
			}
		)
	);
}

/**
 * Default color field settings for new shortcodes.
 *
 * @return array<string, mixed>
 */
function teca_get_color_typography_default_settings() {
	$defaults = array(
		'color_custom' => array(),
	);

	foreach ( teca_get_color_typography_field_keys() as $field_key ) {
		$defaults[ $field_key ]                         = '';
		$defaults[ teca_get_color_custom_flag_key( $field_key ) ] = false;
		$defaults['color_custom'][ $field_key ]           = false;
	}

	return $defaults;
}
