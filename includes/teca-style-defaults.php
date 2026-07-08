<?php
/**
 * Per layout/style default typography and color presets.
 *
 * @package GS_TECA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalized typography field keys used for custom flags.
 *
 * @return string[]
 */
function teca_get_typography_custom_field_keys() {
	return array(
		'font_family',
		'font_size',
		'font_weight',
		'line_height',
		'letter_spacing',
		'text_transform',
		'font_style',
		'text_decoration',
	);
}

/**
 * Map builder control keys to normalized custom field keys.
 *
 * @return array<string, string>
 */
function teca_get_typography_control_to_custom_field_map() {
	return array(
		'getFonts'       => 'font_family',
		'size'           => 'font_size',
		'weight'         => 'font_weight',
		'lineHeight'     => 'line_height',
		'letterSpacing'  => 'letter_spacing',
		'transform'      => 'text_transform',
		'style'          => 'font_style',
		'decoration'     => 'text_decoration',
	);
}

/**
 * Map normalized field keys to builder control keys.
 *
 * @return array<string, string>
 */
function teca_get_typography_custom_field_to_control_map() {
	$map = teca_get_typography_control_to_custom_field_map();

	return array_flip( $map );
}

/**
 * Default field-level custom flags (all false).
 *
 * @return array<string, bool>
 */
function teca_get_typography_field_custom_flag_defaults() {
	$defaults = array();

	foreach ( teca_get_typography_custom_field_keys() as $field ) {
		$defaults[ $field ] = false;
	}

	return $defaults;
}

/**
 * Convert rem/em CSS sizes to px numbers for range-slider controls.
 *
 * @param string $value CSS size value.
 * @return string
 */
function teca_style_default_size_for_control( $value ) {
	$value = trim( (string) $value );

	if ( preg_match( '/^([\d.]+)rem$/', $value, $matches ) ) {
		return (string) round( (float) $matches[1] * 16 );
	}

	if ( preg_match( '/^([\d.]+)em$/', $value, $matches ) ) {
		return (string) round( (float) $matches[1] * 16 );
	}

	if ( preg_match( '/^([\d.]+)px$/', $value, $matches ) ) {
		return (string) $matches[1];
	}

	return $value;
}

/**
 * Default title font family for all TECA style/layout presets.
 *
 * @return string
 */
function teca_get_default_title_font_family() {
	return 'Arimo';
}

/**
 * Grid / list / filter / table defaults from gs-teca.scss CSS variables.
 *
 * @return array{typography: array<string, array<string, string>>, colors: array<string, string>}
 */
function teca_get_grid_style_design_defaults() {
	$title = array(
		'font_family'     => teca_get_default_title_font_family(),
		'font_size'       => '20px',
		'font_weight'     => '600',
		'line_height'     => '1.4',
		'letter_spacing'  => '0.2',
		'text_transform'  => 'capitalize',
		'font_style'      => 'normal',
		'text_decoration' => 'none',
	);

	$content = array(
		'font_family'     => 'Arimo',
		'font_size'       => '14px',
		'font_weight'     => '400',
		'line_height'     => '1.5',
		'letter_spacing'  => '0.2',
		'text_transform'  => '',
		'font_style'      => 'normal',
		'text_decoration' => 'none',
	);

	$button = array(
		'font_family'     => 'Arimo',
		'font_size'       => '14px',
		'font_weight'     => '700',
		'line_height'     => '1.3',
		'letter_spacing'  => '0.02em',
		'text_transform'  => '',
		'font_style'      => 'normal',
		'text_decoration' => 'none',
	);

	$colors = array(
		'title_color'              => '#191919',
		'title_background_color'   => '',
		'title_hover_color'        => '#191919',
		'title_hover_background_color' => '',
		'cat_color'                => '#022203',
		'cat_background_color'     => '',
		'cat_hover_color'          => '#ffffff',
		'cat_hover_background_color' => '',
		'tag_color'                => '#b70ae2',
		'tag_background_color'     => '',
		'tag_hover_color'          => '#db6415',
		'tag_hover_background_color' => '',
		'org_color'                => '#777777',
		'org_background_color'     => '',
		'org_hover_color'          => '',
		'org_hover_background_color' => '',
		'date_color'               => '#777777',
		'date_background_color'    => '',
		'date_hover_color'         => '',
		'date_hover_background_color' => '',
		'details_color'            => '#777777',
		'details_background_color' => '',
		'details_hover_color'      => '',
		'details_hover_background_color' => '',
		'venue_color'              => '#777777',
		'venue_background_color'   => '',
		'venue_hover_color'        => '',
		'venue_hover_background_color' => '',
		'view_details_color'                => '#c45c26',
		'view_details_background_color'     => '',
		'view_details_hover_color'          => '#9a471e',
		'view_details_hover_background_color' => '',
		'google_calendar_color'                => '#ffffff',
		'google_calendar_background_color'     => '#111827',
		'google_calendar_hover_color'          => '#ffffff',
		'google_calendar_hover_background_color' => '#000000',
	);

	return array(
		'typography' => array(
			'title'           => $title,
			'cat'             => $content,
			'tag'             => $content,
			'org'             => $content,
			'date'            => $content,
			'details'         => $content,
			'venue'           => $content,
			'view_details'    => $button,
			'google_calendar' => $button,
		),
		'colors'     => $colors,
	);
}

/**
 * Accordion / timeline forced defaults from _teca-accordion-timeline-content.scss.
 *
 * @return array{typography: array<string, array<string, string>>, colors: array<string, string>}
 */
function teca_get_accordion_timeline_design_defaults() {
	$title = array(
		'font_family'     => teca_get_default_title_font_family(),
		'font_size'       => '1rem',
		'font_weight'     => '700',
		'line_height'     => '1.35',
		'letter_spacing'  => '',
		'text_transform'  => '',
		'font_style'      => '',
		'text_decoration' => '',
	);

	$content = array(
		'font_family'     => '',
		'font_size'       => '0.8125rem',
		'font_weight'     => '',
		'line_height'     => '1.4',
		'letter_spacing'  => '',
		'text_transform'  => '',
		'font_style'      => '',
		'text_decoration' => '',
	);

	$button = array(
		'font_family'     => '',
		'font_size'       => '0.8125rem',
		'font_weight'     => '700',
		'line_height'     => '1.3',
		'letter_spacing'  => '',
		'text_transform'  => '',
		'font_style'      => '',
		'text_decoration' => '',
	);

	$colors = array(
		'title_color'              => '#111827',
		'title_background_color'   => '',
		'title_hover_color'        => '',
		'title_hover_background_color' => '',
		'cat_color'                => '#6b7280',
		'cat_background_color'     => '',
		'cat_hover_color'          => '',
		'cat_hover_background_color' => '',
		'tag_color'                => '#6b7280',
		'tag_background_color'     => '',
		'tag_hover_color'          => '',
		'tag_hover_background_color' => '',
		'org_color'                => '#6b7280',
		'org_background_color'     => '',
		'org_hover_color'          => '',
		'org_hover_background_color' => '',
		'date_color'               => '#6b7280',
		'date_background_color'    => '',
		'date_hover_color'         => '',
		'date_hover_background_color' => '',
		'details_color'            => '#6b7280',
		'details_background_color' => '',
		'details_hover_color'      => '',
		'details_hover_background_color' => '',
		'venue_color'              => '#6b7280',
		'venue_background_color'   => '',
		'venue_hover_color'        => '',
		'venue_hover_background_color' => '',
		'view_details_color'                => '#c45c26',
		'view_details_background_color'     => '',
		'view_details_hover_color'          => '#9a471e',
		'view_details_hover_background_color' => '',
		'google_calendar_color'                => '#ffffff',
		'google_calendar_background_color'     => '#111827',
		'google_calendar_hover_color'          => '#ffffff',
		'google_calendar_hover_background_color' => '#000000',
	);

	return array(
		'typography' => array(
			'title'           => $title,
			'cat'             => $content,
			'tag'             => $content,
			'org'             => $content,
			'date'            => $content,
			'details'         => $content,
			'venue'           => $content,
			'view_details'    => $button,
			'google_calendar' => $button,
		),
		'colors'     => $colors,
	);
}

/**
 * Per-style color overrides layered on accordion/timeline or grid base.
 *
 * @return array<string, array<string, string>>
 */
function teca_get_style_color_overrides_registry() {
	return array(
		'teca-accordion-2' => array(
			'cat_color' => '#ffffff',
		),
		'teca-style-4'     => array(
			'date_color' => '#ffffff',
			'cat_color'  => '#ffffff',
		),
		'teca-style-7'     => array(
			'title_color'       => '#ffffff',
			'title_hover_color' => '#ffffff',
			'venue_color'       => '#ffffff',
			'org_color'         => '#ffffff',
		),
	);
}

/**
 * Styles that use accordion/timeline forced typography defaults.
 *
 * @return string[]
 */
function teca_get_accordion_timeline_style_keys() {
	return array(
		'teca-accordion-1',
		'teca-accordion-2',
		'teca-accordion-3',
		'teca-timeline-1',
		'teca-timeline-2',
		'teca-timeline-3',
	);
}

/**
 * All known style scope keys for the builder registry.
 *
 * @return string[]
 */
function teca_get_all_style_scope_keys() {
	$keys = teca_get_accordion_timeline_style_keys();

	$keys = array_merge(
		$keys,
		array(
			'teca-events-layout-1',
			'teca-events-layout-2',
			'teca-events-layout-3',
		)
	);

	for ( $i = 1; $i <= 24; $i++ ) {
		$keys[] = 'teca-style-' . $i;
		$keys[] = 'teca-list-' . $i;
		$keys[] = 'teca-table-' . $i;
		$keys[] = 'teca-filter-' . $i;
	}

	for ( $i = 1; $i <= 12; $i++ ) {
		$keys[] = 'teca-daily-layout-' . $i;
		$keys[] = 'teca-weekly-layout-' . $i;
		$keys[] = 'teca-monthly-layout-' . $i;
	}

	return array_values( array_unique( $keys ) );
}

/**
 * Default design preset for a style/layout scope key.
 *
 * @param string $style_key Scope class e.g. teca-accordion-1.
 * @return array{typography: array<string, array<string, string>>, colors: array<string, string>}
 */
function teca_get_default_design_preset( $style_key ) {
	$style_key = sanitize_html_class( (string) $style_key );

	if ( in_array( $style_key, teca_get_accordion_timeline_style_keys(), true ) ) {
		$design = teca_get_accordion_timeline_design_defaults();
	} else {
		$design = teca_get_grid_style_design_defaults();
	}

	if ( isset( teca_get_style_color_overrides_registry()[ $style_key ] ) ) {
		$design['colors'] = array_merge( $design['colors'], teca_get_style_color_overrides_registry()[ $style_key ] );
	}

	return $design;
}

/**
 * @deprecated Use teca_get_default_design_preset().
 * @param string $style_key Scope class.
 * @return array{typography: array<string, array<string, string>>, colors: array<string, string>}
 */
function teca_get_style_default_design( $style_key ) {
	return teca_get_default_design_preset( $style_key );
}

/**
 * @param string $style_key Scope class.
 * @param string $group     Typography group slug.
 * @return array<string, string>
 */
function teca_get_style_default_typography( $style_key, $group ) {
	$design = teca_get_default_design_preset( $style_key );
	$group  = sanitize_key( (string) $group );

	return (array) ( $design['typography'][ $group ] ?? array() );
}

/**
 * @param string $style_key Scope class.
 * @param string $field_key Color field key.
 * @return string
 */
function teca_get_style_default_color( $style_key, $field_key ) {
	$design = teca_get_default_design_preset( $style_key );

	return (string) ( $design['colors'][ sanitize_key( (string) $field_key ) ] ?? '' );
}

/**
 * Resolve defaults from shortcode settings.
 *
 * @param array $settings Shortcode settings.
 * @return array{typography: array<string, array<string, string>>, colors: array<string, string>}
 */
function teca_get_style_default_design_from_settings( array $settings ) {
	$scope = function_exists( 'teca_get_typography_scope_class' )
		? teca_get_typography_scope_class( $settings )
		: '';

	if ( '' === $scope ) {
		$scope = 'teca-style-1';
	}

	return teca_get_default_design_preset( $scope );
}

/**
 * Convert normalized typography defaults to builder control shape for Vue.
 *
 * @param array<string, string> $normalized Normalized typography defaults.
 * @return array<string, string|float>
 */
function teca_typography_defaults_to_control_format( array $normalized ) {
	$control_map = teca_get_typography_custom_field_to_control_map();
	$output      = array(
		'getFonts'             => '',
		'size'                 => '',
		'size_tablet'          => '',
		'size_mobile'          => '',
		'weight'               => '',
		'transform'            => '',
		'style'                => '',
		'decoration'           => '',
		'lineHeight'           => '',
		'lineHeight_tablet'    => '',
		'lineHeight_mobile'    => '',
		'letterSpacing'        => '',
		'letterSpacing_tablet' => '',
		'letterSpacing_mobile' => '',
	);

	foreach ( $normalized as $field => $value ) {
		if ( empty( $control_map[ $field ] ) ) {
			continue;
		}

		$control_key = $control_map[ $field ];
		$value       = (string) $value;

		if ( '' === $value ) {
			continue;
		}

		if ( 'font_size' === $field ) {
			$value = teca_style_default_size_for_control( $value );
		}

		if ( in_array( $field, array( 'line_height', 'letter_spacing' ), true ) && is_numeric( $value ) ) {
			$output[ $control_key ] = (float) $value;
			continue;
		}

		$output[ $control_key ] = $value;
	}

	return $output;
}

/**
 * Merge saved typography overrides with layout preset for one group.
 *
 * @param string              $style_key        Scope class.
 * @param string              $group            Typography group slug.
 * @param array|object|null   $saved_typography Saved typography (control format).
 * @param array<string, bool> $field_flags      Field-level custom flags.
 * @return array<string, string>
 */
function teca_merge_typography_group_with_preset( $style_key, $group, $saved_typography, array $field_flags ) {
	$preset = teca_get_style_default_typography( $style_key, $group );
	$saved  = teca_normalize_typography_value( $saved_typography );
	$merged = $preset;

	foreach ( teca_get_typography_custom_field_keys() as $field ) {
		if ( empty( $field_flags[ $field ] ) ) {
			continue;
		}

		if ( array_key_exists( $field, $saved ) && '' !== (string) $saved[ $field ] && null !== $saved[ $field ] ) {
			$merged[ $field ] = $saved[ $field ];
		}
	}

	return $merged;
}

/**
 * Merge all saved typography groups with layout presets.
 *
 * @param string $style_key Scope class.
 * @param array  $settings  Shortcode settings.
 * @return array<string, array<string, string>>
 */
function teca_merge_all_typography_with_preset( $style_key, array $settings ) {
	$merged = array();

	foreach ( teca_get_typography_group_map() as $group => $typography_key ) {
		$flags = teca_get_typography_field_custom_flags( $settings, $group );
		$merged[ $group ] = teca_merge_typography_group_with_preset(
			$style_key,
			$group,
			$settings[ $typography_key ] ?? array(),
			$flags
		);
	}

	return $merged;
}

/**
 * Resolve final color value: saved override or layout preset.
 *
 * @param string $style_key  Scope class.
 * @param string $field_key  Color field key.
 * @param array  $settings   Shortcode settings.
 * @return string
 */
function teca_merge_color_field_with_preset( $style_key, $field_key, array $settings ) {
	$field_key = sanitize_key( (string) $field_key );

	if ( teca_should_apply_color( $settings, $field_key ) ) {
		return (string) ( $settings[ $field_key ] ?? '' );
	}

	return teca_get_style_default_color( $style_key, $field_key );
}

/**
 * Merge default preset with saved shortcode settings (full design).
 *
 * @param array $settings Shortcode settings.
 * @return array{typography: array<string, array<string, string>>, colors: array<string, string>}
 */
function teca_merge_default_design_with_saved_values( array $settings ) {
	$scope = function_exists( 'teca_get_typography_scope_class' )
		? teca_get_typography_scope_class( $settings )
		: 'teca-style-1';

	if ( '' === $scope ) {
		$scope = 'teca-style-1';
	}

	$preset     = teca_get_default_design_preset( $scope );
	$typography = teca_merge_all_typography_with_preset( $scope, $settings );
	$colors     = $preset['colors'];

	foreach ( teca_get_color_typography_field_keys() as $field_key ) {
		if ( teca_should_apply_color( $settings, $field_key ) ) {
			$colors[ $field_key ] = (string) ( $settings[ $field_key ] ?? '' );
		}
	}

	return array(
		'typography' => $typography,
		'colors'     => $colors,
	);
}

/**
 * Registry for shortcode builder (all known scope classes).
 *
 * @return array<string, array{typography: array<string, array<string, string>>, colors: array<string, string>}>
 */
function teca_get_style_design_registry_for_builder() {
	$registry = array();

	foreach ( teca_get_all_style_scope_keys() as $key ) {
		$design = teca_get_default_design_preset( $key );

		foreach ( $design['typography'] as $group => $typography ) {
			$design['typography'][ $group ] = teca_typography_defaults_to_control_format( $typography );
		}

		$registry[ $key ] = $design;
	}

	return $registry;
}
