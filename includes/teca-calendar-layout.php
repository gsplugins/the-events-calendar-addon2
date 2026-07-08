<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unified calendar layout registry (Calendar Layout 1–15).
 *
 * @return array<string, array{legacy_slug:string, period:string, render_method:string}>
 */
function teca_get_calendar_layout_registry() {
	static $registry = null;

	if ( null !== $registry ) {
		return $registry;
	 }

	$rows = array(
		array( 'calendar-layout-1', 'daily-layout-1', 'daily', 'render_daily_layout_1' ),
		array( 'calendar-layout-2', 'daily-layout-2', 'daily', 'render_daily_layout_2' ),
		array( 'calendar-layout-3', 'daily-layout-3', 'daily', 'render_daily_layout_3' ),
		array( 'calendar-layout-4', 'weekly-layout-1', 'weekly', 'render_weekly_layout_1' ),
		array( 'calendar-layout-5', 'weekly-layout-2', 'weekly', 'render_weekly_layout_2' ),
		array( 'calendar-layout-6', 'weekly-layout-3', 'weekly', 'render_weekly_layout_3' ),
		array( 'calendar-layout-7', 'monthly-layout-1', 'monthly', 'render_monthly_layout_1' ),
		array( 'calendar-layout-8', 'monthly-layout-2', 'monthly', 'render_monthly_layout_2' ),
		array( 'calendar-layout-9', 'monthly-layout-3', 'monthly', 'render_monthly_layout_3' ),
		array( 'calendar-layout-10', 'quarterly-layout-1', 'quarterly', 'render_quarterly_layout_1' ),
		array( 'calendar-layout-11', 'quarterly-layout-2', 'quarterly', 'render_quarterly_layout_2' ),
		array( 'calendar-layout-12', 'quarterly-layout-3', 'quarterly', 'render_quarterly_layout_3' ),
		array( 'calendar-layout-13', 'yearly-layout-1', 'yearly', 'render_yearly_layout_1' ),
		array( 'calendar-layout-14', 'yearly-layout-2', 'yearly', 'render_yearly_layout_2' ),
		array( 'calendar-layout-15', 'yearly-layout-3', 'yearly', 'render_yearly_layout_3' ),
	);

	$registry = array();

	foreach ( $rows as $row ) {
		$registry[ $row[0] ] = array(
			'legacy_slug'    => $row[1],
			'period'         => $row[2],
			'render_method'  => $row[3],
		);
	}

	return $registry;
}

/**
 * @return string[]
 */
function teca_get_calendar_layout_options() {
	$options = array();

	for ( $i = 1; $i <= 15; $i++ ) {
		$options[] = 'calendar-layout-' . $i;
	}

	return $options;
}

/**
 * @return array<string, string>
 */
function teca_get_legacy_to_unified_calendar_layout_map() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$map = array();

	foreach ( teca_get_calendar_layout_registry() as $unified => $entry ) {
		$map[ $entry['legacy_slug'] ] = $unified;
	}

	return $map;
}

/**
 * @return array<string, string>
 */
function teca_get_calendar_view_type_to_filter_mode_map() {
	return array(
		'daily-calendar'     => 'daily',
		'weekly-calendar'    => 'weekly',
		'monthly-calendar'   => 'monthly',
		'quarterly-calendar' => 'quarterly',
		'yearly-calendar'    => 'yearly',
		'calendar'           => 'daily',
	);
}

/**
 * @return array<int, array{label:string, value:string}>
 */
function teca_get_all_calendar_layout_select_options() {
	$options = array();

	for ( $i = 1; $i <= 15; $i++ ) {
		$options[] = array(
			'label' => sprintf(
				/* translators: %d: calendar layout number */
				__( 'Calendar Layout %d', 'the-events-calendar-addon' ),
				$i
			),
			'value' => 'calendar-layout-' . $i,
		);
	}

	return $options;
}

/**
 * Calendar layout slugs available without Pro.
 *
 * @return string[]
 */
function teca_get_free_calendar_layout_slugs() {
	return apply_filters(
		'gs_teca_free_calendar_layout_slugs',
		array(
			'calendar-layout-1',
			'calendar-layout-2',
		)
	);
}

/**
 * Default free calendar layout slug.
 *
 * @return string
 */
function teca_get_default_free_calendar_layout() {
	return 'calendar-layout-1';
}

/**
 * Whether a calendar layout slug is free.
 *
 * @param string $layout Calendar layout slug.
 * @return bool
 */
function teca_is_free_calendar_layout( $layout ) {
	return in_array( (string) $layout, teca_get_free_calendar_layout_slugs(), true );
}

/**
 * Resolve a calendar layout for the current Pro/free context.
 *
 * @param string $layout Calendar layout slug.
 * @return string
 */
function teca_resolve_calendar_layout_for_context( $layout ) {
	$layout = sanitize_key( (string) $layout );

	if ( '' === $layout || ! teca_is_valid_calendar_layout( $layout ) ) {
		return teca_get_default_free_calendar_layout();
	}

	if ( is_pro_active_and_valid() || teca_is_free_calendar_layout( $layout ) ) {
		return $layout;
	}

	return teca_get_default_free_calendar_layout();
}

/**
 * Free Calendar Layout options for the admin selector.
 *
 * @return array<int, array{label:string, value:string}>
 */
function teca_get_free_calendar_layout_select_options() {
	$free_slugs = teca_get_free_calendar_layout_slugs();
	$all        = teca_get_all_calendar_layout_select_options();
	$indexed    = array();

	foreach ( $all as $option ) {
		$value = $option['value'] ?? '';

		if ( '' !== $value ) {
			$indexed[ $value ] = $option;
		}
	}

	$free_options = array();

	foreach ( $free_slugs as $slug ) {
		if ( isset( $indexed[ $slug ] ) ) {
			$free_options[] = $indexed[ $slug ];
		}
	}

	return $free_options;
}

/**
 * Pro-only Calendar Layout options for the admin selector.
 *
 * @return array<int, array{label:string, value:string}>
 */
function teca_get_pro_calendar_layout_select_options() {
	$free_slugs = teca_get_free_calendar_layout_slugs();

	return array_values(
		array_filter(
			teca_get_all_calendar_layout_select_options(),
			static function( $item ) use ( $free_slugs ) {
				return ! in_array( $item['value'] ?? '', $free_slugs, true );
			}
		)
	);
}

/**
 * @return array<int, array{label:string, value:string}>
 */
function teca_get_calendar_layout_select_options() {
	return teca_get_free_calendar_layout_select_options();
}

/**
 * @return array<int, array{label:string, value:string}>
 */
function teca_get_calendar_select_filter_options() {
	return array(
		array(
			'label' => __( 'Daily', 'the-events-calendar-addon' ),
			'value' => 'daily',
		),
		array(
			'label' => __( 'Weekly', 'the-events-calendar-addon' ),
			'value' => 'weekly',
		),
		array(
			'label' => __( 'Monthly', 'the-events-calendar-addon' ),
			'value' => 'monthly',
		),
		array(
			'label' => __( 'Quarterly', 'the-events-calendar-addon' ),
			'value' => 'quarterly',
		),
		array(
			'label' => __( 'Yearly', 'the-events-calendar-addon' ),
			'value' => 'yearly',
		),
	);
}

function teca_is_valid_calendar_layout( $layout ) {
	return in_array( (string) $layout, teca_get_calendar_layout_options(), true );
}

function teca_map_legacy_calendar_layout_to_unified( $legacy_slug ) {
	$legacy_slug = sanitize_text_field( (string) $legacy_slug );
	$map         = teca_get_legacy_to_unified_calendar_layout_map();

	return $map[ $legacy_slug ] ?? '';
}

function teca_get_calendar_layout_legacy_slug( $calendar_layout ) {
	$registry = teca_get_calendar_layout_registry();
	$layout   = teca_is_valid_calendar_layout( $calendar_layout ) ? $calendar_layout : 'calendar-layout-1';

	return $registry[ $layout ]['legacy_slug'] ?? 'daily-layout-1';
}

function teca_get_calendar_layout_period( $calendar_layout ) {
	$registry = teca_get_calendar_layout_registry();
	$layout   = teca_is_valid_calendar_layout( $calendar_layout ) ? $calendar_layout : 'calendar-layout-1';

	return $registry[ $layout ]['period'] ?? 'daily';
}

function teca_get_calendar_layout_render_method( $calendar_layout ) {
	$registry = teca_get_calendar_layout_registry();
	$layout   = teca_is_valid_calendar_layout( $calendar_layout ) ? $calendar_layout : 'calendar-layout-1';

	return $registry[ $layout ]['render_method'] ?? 'render_daily_layout_1';
}

function teca_resolve_calendar_layout_from_legacy( array $settings ) {
	$legacy_map = teca_get_legacy_to_unified_calendar_layout_map();
	$view_type  = (string) ( $settings['view_type'] ?? '' );

	$period_keys = array(
		'daily-calendar'     => 'daily_calendar_layout',
		'weekly-calendar'    => 'weekly_calendar_layout',
		'monthly-calendar'   => 'monthly_calendar_layout',
		'quarterly-calendar' => 'quarterly_calendar_layout',
		'yearly-calendar'    => 'yearly_calendar_layout',
	);

	if ( isset( $period_keys[ $view_type ] ) ) {
		$key    = $period_keys[ $view_type ];
		$legacy = sanitize_text_field( (string) ( $settings[ $key ] ?? '' ) );

		if ( $legacy && isset( $legacy_map[ $legacy ] ) ) {
			return $legacy_map[ $legacy ];
		}
	}

	foreach ( array_values( $period_keys ) as $setting_key ) {
		$legacy = sanitize_text_field( (string) ( $settings[ $setting_key ] ?? '' ) );

		if ( $legacy && isset( $legacy_map[ $legacy ] ) ) {
			return $legacy_map[ $legacy ];
		}
	}

	return 'calendar-layout-1';
}

function teca_get_selected_calendar_layout( array $settings ) {
	$value = sanitize_key( (string) ( $settings['calendar_layout'] ?? '' ) );

	if ( teca_is_valid_calendar_layout( $value ) ) {
		return teca_resolve_calendar_layout_for_context( $value );
	}

	$legacy = teca_map_legacy_calendar_layout_to_unified( $value );

	if ( $legacy ) {
		return teca_resolve_calendar_layout_for_context( $legacy );
	}

	return teca_resolve_calendar_layout_for_context(
		teca_resolve_calendar_layout_from_legacy( $settings )
	);
}

function teca_get_selected_calendar_filter_mode( array $settings ) {
	$default   = 'daily';
	$valid     = array( 'daily', 'weekly', 'monthly', 'quarterly', 'yearly' );
	$value     = sanitize_key( (string) ( $settings['calendar_select_filter'] ?? '' ) );
	$view_type = (string) ( $settings['view_type'] ?? '' );
	$view_map  = teca_get_calendar_view_type_to_filter_mode_map();

	if ( in_array( $value, $valid, true ) ) {
		return $value;
	}

	if ( isset( $view_map[ $view_type ] ) && 'calendar' !== $view_type ) {
		return $view_map[ $view_type ];
	}

	return $default;
}

function teca_normalize_calendar_settings( array $settings ) {
	$view_type = (string) ( $settings['view_type'] ?? '' );
	$view_map  = teca_get_calendar_view_type_to_filter_mode_map();

	if ( isset( $view_map[ $view_type ] ) && 'calendar' !== $view_type ) {
		if ( empty( $settings['calendar_select_filter'] ) || ! in_array( $settings['calendar_select_filter'], array( 'daily', 'weekly', 'monthly', 'quarterly', 'yearly' ), true ) ) {
			$settings['calendar_select_filter'] = $view_map[ $view_type ];
		}
	}

	if ( empty( $settings['calendar_layout'] ) || ! teca_is_valid_calendar_layout( $settings['calendar_layout'] ) ) {
		$mapped = teca_map_legacy_calendar_layout_to_unified( $settings['calendar_layout'] ?? '' );

		if ( $mapped ) {
			$settings['calendar_layout'] = $mapped;
		} else {
			$settings['calendar_layout'] = teca_resolve_calendar_layout_from_legacy( $settings );
		}
	}

	$settings['calendar_select_filter'] = teca_get_selected_calendar_filter_mode( $settings );

	if ( teca_is_calendar_view_type( $view_type ) || 'calendar' === $view_type ) {
		$settings['view_type'] = 'calendar';
	}

	return $settings;
}

function teca_sanitize_calendar_settings( array $shortcode_settings ) {
	$shortcode_settings = teca_normalize_calendar_settings( $shortcode_settings );
	$shortcode_settings['calendar_layout']        = teca_get_selected_calendar_layout( $shortcode_settings );
	$shortcode_settings['calendar_select_filter'] = teca_get_selected_calendar_filter_mode( $shortcode_settings );

	return teca_sanitize_calendar_sub_layout_settings( $shortcode_settings );
}

function teca_enable_shared_calendar_filter( $enabled = true ) {
	$GLOBALS['teca_shared_calendar_filter'] = (bool) $enabled;
}

function teca_is_shared_calendar_filter_enabled() {
	return ! empty( $GLOBALS['teca_shared_calendar_filter'] );
}
