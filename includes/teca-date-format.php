<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preset date format options for layout controls.
 *
 * @return array<int, array{label:string,value:string}>
 */
function teca_get_date_format_preset_options() {
	return array(
		array(
			'label' => __( 'Default Format', 'the-events-calendar-addon' ),
			'value' => 'default',
		),
		array(
			'label' => 'F j, Y',
			'value' => 'F j, Y',
		),
		array(
			'label' => 'M j, Y',
			'value' => 'M j, Y',
		),
		array(
			'label' => 'M d, Y',
			'value' => 'M d, Y',
		),
		array(
			'label' => 'd M Y',
			'value' => 'd M Y',
		),
		array(
			'label' => 'd/m/Y',
			'value' => 'd/m/Y',
		),
		array(
			'label' => 'm/d/Y',
			'value' => 'm/d/Y',
		),
		array(
			'label' => 'Y-m-d',
			'value' => 'Y-m-d',
		),
		array(
			'label' => 'l, F j, Y',
			'value' => 'l, F j, Y',
		),
		array(
			'label' => __( 'Custom', 'the-events-calendar-addon' ),
			'value' => 'custom',
		),
	);
}

/**
 * Default date format config for a layout key.
 *
 * @return array{format:string,custom:string}
 */
function teca_get_default_layout_date_format_config() {
	return array(
		'format' => 'default',
		'custom' => '',
	);
}

/**
 * Sanitize stored date format config.
 *
 * @param mixed $config Raw config.
 * @return array{format:string,custom:string}
 */
function teca_sanitize_layout_date_format_config( $config ) {
	$defaults = teca_get_default_layout_date_format_config();
	$config   = is_array( $config ) ? $config : array();

	$format = isset( $config['format'] ) ? sanitize_text_field( (string) $config['format'] ) : $defaults['format'];
	$custom = isset( $config['custom'] ) ? sanitize_text_field( (string) $config['custom'] ) : $defaults['custom'];

	$allowed = array_column( teca_get_date_format_preset_options(), 'value' );

	if ( ! in_array( $format, $allowed, true ) ) {
		$format = 'default';
	}

	return array(
		'format' => $format,
		'custom' => $custom,
	);
}

/**
 * Sanitize date format settings array.
 *
 * @param mixed $date_formats Raw settings.
 * @return array<string, array{format:string,custom:string}>
 */
function teca_sanitize_date_formats_settings( $date_formats ) {
	if ( ! is_array( $date_formats ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $date_formats as $layout_key => $config ) {
		$key = sanitize_key( (string) $layout_key );

		if ( '' === $key ) {
			continue;
		}

		$sanitized[ $key ] = teca_sanitize_layout_date_format_config( $config );
	}

	return $sanitized;
}

/**
 * Set active date format context for current render pass.
 *
 * @param string $layout_key Layout key.
 * @param array  $settings   Settings source.
 * @return void
 */
function teca_set_date_format_context( $layout_key, array $settings = array() ) {
	$GLOBALS['teca_date_format_context'] = array(
		'layout_key' => sanitize_text_field( (string) $layout_key ),
		'settings'   => is_array( $settings ) ? $settings : array(),
	);
}

/**
 * Store immutable shortcode date format context for the full render pass.
 *
 * Popup includes inside the event loop must not overwrite this context.
 *
 * @param array $settings Shortcode settings.
 * @return void
 */
function teca_set_shortcode_date_format_context( array $settings ) {
	$GLOBALS['teca_shortcode_date_format_context'] = array(
		'layout_key' => teca_resolve_shortcode_layout_date_key( $settings ),
		'settings'   => $settings,
	);
}

/**
 * Clear shortcode date format context after render.
 *
 * @return void
 */
function teca_clear_shortcode_date_format_context() {
	unset( $GLOBALS['teca_shortcode_date_format_context'] );
}

/**
 * Get active date format context.
 *
 * @param bool $prefer_shortcode Whether shortcode context should win over mutable context.
 * @return array{layout_key:string,settings:array}
 */
function teca_get_date_format_context( $prefer_shortcode = true ) {
	if ( $prefer_shortcode && ! empty( $GLOBALS['teca_shortcode_date_format_context'] ) && is_array( $GLOBALS['teca_shortcode_date_format_context'] ) ) {
		$context = $GLOBALS['teca_shortcode_date_format_context'];

		return array(
			'layout_key' => isset( $context['layout_key'] ) ? (string) $context['layout_key'] : '',
			'settings'   => isset( $context['settings'] ) && is_array( $context['settings'] ) ? $context['settings'] : array(),
		);
	}

	$context = $GLOBALS['teca_date_format_context'] ?? array();

	return array(
		'layout_key' => isset( $context['layout_key'] ) ? (string) $context['layout_key'] : '',
		'settings'   => isset( $context['settings'] ) && is_array( $context['settings'] ) ? $context['settings'] : array(),
	);
}

/**
 * Resolve date format context for a template include.
 *
 * @param array $args {
 *     Optional context args.
 *
 *     @type string $layout_key  Explicit layout key override.
 *     @type array  $settings    Settings source.
 *     @type array  $event       Event payload.
 *     @type string $popup_style Popup style slug.
 * }
 * @return array{layout_key:string,settings:array}
 */
function teca_resolve_date_format_render_context( array $args = array() ) {
	$layout_key  = isset( $args['layout_key'] ) ? sanitize_text_field( (string) $args['layout_key'] ) : '';
	$settings    = isset( $args['settings'] ) && is_array( $args['settings'] ) ? $args['settings'] : array();
	$popup_style = isset( $args['popup_style'] ) ? sanitize_key( (string) $args['popup_style'] ) : '';
	$scope       = isset( $args['scope'] ) ? sanitize_key( (string) $args['scope'] ) : '';

	$has_shortcode_settings = ! empty( $settings['gs_teca_template'] ) || ! empty( $settings['view_type'] );

	// Card/list/table dates in the event loop must always use layout format.
	if ( 'popup' !== $scope && $has_shortcode_settings ) {
		return array(
			'layout_key' => teca_resolve_shortcode_layout_date_key( $settings ),
			'settings'   => $settings,
		);
	}

	if ( '' !== $layout_key ) {
		return array(
			'layout_key' => $layout_key,
			'settings'   => $settings,
		);
	}

	if ( '' !== $popup_style ) {
		return array(
			'layout_key' => teca_get_popup_layout_date_key( $popup_style ),
			'settings'   => $settings,
		);
	}

	return teca_get_date_format_context();
}

/**
 * Begin popup-only date format scope for a partial include.
 *
 * @param string $popup_style Popup style slug.
 * @param array  $settings    Settings source.
 * @return void
 */
function teca_begin_popup_date_format_scope( $popup_style, array $settings = array() ) {
	$GLOBALS['teca_date_format_scope']        = 'popup';
	$GLOBALS['teca_date_format_layout_key']   = teca_get_popup_layout_date_key( $popup_style );
	$GLOBALS['teca_date_format_settings']     = $settings;
	$GLOBALS['teca_date_format_popup_style'] = sanitize_key( (string) $popup_style );
}

/**
 * End popup-only date format scope.
 *
 * @return void
 */
function teca_end_popup_date_format_scope() {
	unset(
		$GLOBALS['teca_date_format_scope'],
		$GLOBALS['teca_date_format_layout_key'],
		$GLOBALS['teca_date_format_settings'],
		$GLOBALS['teca_date_format_popup_style']
	);
}

/**
 * Whether current date partial render is inside popup scope.
 *
 * @return bool
 */
function teca_is_popup_date_format_scope() {
	return isset( $GLOBALS['teca_date_format_scope'] ) && 'popup' === $GLOBALS['teca_date_format_scope'];
}

/**
 * Build popup layout date key.
 *
 * @param string $popup_style Popup style slug.
 * @return string
 */
function teca_get_popup_layout_date_key( $popup_style = 'default' ) {
	$popup_style = sanitize_key( (string) $popup_style );

	if ( '' === $popup_style ) {
		$popup_style = 'default';
	}

	return 'popup:' . $popup_style;
}

/**
 * Build single page layout date key.
 *
 * @param string|null $style_key Single page style key.
 * @return string
 */
function teca_get_single_layout_date_key( $style_key = null ) {
	if ( null === $style_key ) {
		$style_key = function_exists( __NAMESPACE__ . '\\teca_get_single_page_style_key' )
			? teca_get_single_page_style_key()
			: 'default';
	} else {
		$style_key = function_exists( __NAMESPACE__ . '\\teca_normalize_single_page_style_key' )
			? teca_normalize_single_page_style_key( $style_key )
			: sanitize_key( (string) $style_key );
	}

	return 'single:' . $style_key;
}

/**
 * Resolve layout date key from shortcode settings.
 *
 * Mirrors shortcode builder scope keys (teca-style-1, teca-list-2, etc.).
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_resolve_shortcode_layout_date_key( array $settings ) {
	$view_type = isset( $settings['view_type'] ) ? (string) $settings['view_type'] : 'grid';
	$template  = isset( $settings['gs_teca_template'] ) ? (string) $settings['gs_teca_template'] : '';

	$accordion_map = array(
		'gs-teca-accordion-1' => 'teca-accordion-1',
		'gs-teca-accordion-2' => 'teca-accordion-2',
		'gs-teca-accordion-3' => 'teca-accordion-3',
	);

	$timeline_map = array(
		'gs-teca-timeline-1' => 'teca-timeline-1',
		'gs-teca-timeline-2' => 'teca-timeline-2',
		'gs-teca-timeline-3' => 'teca-timeline-3',
	);

	if ( 'accordion' === $view_type || isset( $accordion_map[ $template ] ) ) {
		return $accordion_map[ $template ] ?? 'teca-accordion-1';
	}

	if ( 'timeline' === $view_type || isset( $timeline_map[ $template ] ) ) {
		return $timeline_map[ $template ] ?? 'teca-timeline-1';
	}

	if ( 'events-section' === $view_type ) {
		$layout = isset( $settings['event_layout'] ) ? (string) $settings['event_layout'] : 'event-layout-1';

		if ( preg_match( '/event-layout-(\d+)/', $layout, $matches ) ) {
			return 'teca-events-layout-' . $matches[1];
		}

		return 'teca-events-layout-1';
	}

	if ( 'venue_template' === $view_type ) {
		$layout = isset( $settings['venue_template_layout'] ) ? (string) $settings['venue_template_layout'] : 'layout-1';

		if ( preg_match( '/layout-(\d+)/', $layout, $matches ) ) {
			return 'teca-venue-template-layout-' . $matches[1];
		}

		return 'teca-venue-template-layout-1';
	}

	if ( 'organizer_template' === $view_type ) {
		$layout = isset( $settings['organizer_template_layout'] ) ? (string) $settings['organizer_template_layout'] : 'layout-1';

		if ( preg_match( '/layout-(\d+)/', $layout, $matches ) ) {
			return 'teca-organizer-template-layout-' . $matches[1];
		}

		return 'teca-organizer-template-layout-1';
	}

	if ( 'calendar' === $view_type ) {
		$filter     = isset( $settings['calendar_select_filter'] ) ? sanitize_key( (string) $settings['calendar_select_filter'] ) : 'daily';
		$layout_key = $filter . '_calendar_layout';
		$layout     = isset( $settings[ $layout_key ] ) ? sanitize_key( (string) $settings[ $layout_key ] ) : $filter . '-layout-1';

		if ( preg_match( '/(\w+)-layout-(\d+)/', $layout, $matches ) ) {
			return 'teca-' . $matches[1] . '-layout-' . $matches[2];
		}

		$calendar_layout = isset( $settings['calendar_layout'] ) ? (string) $settings['calendar_layout'] : 'calendar-layout-1';

		if ( preg_match( '/calendar-layout-(\d+)/', $calendar_layout, $matches ) ) {
			return 'teca-daily-layout-' . $matches[1];
		}

		return 'teca-daily-layout-1';
	}

	if ( preg_match( '/gs-teca-style-(\d+)/', $template, $matches ) ) {
		return 'teca-style-' . $matches[1];
	}

	if ( preg_match( '/gs-teca-list-style-(\d+)/', $template, $matches ) ) {
		return 'teca-list-' . $matches[1];
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

	if ( '' !== $template ) {
		return sanitize_key( str_replace( 'gs-teca-', 'teca-', $template ) );
	}

	return 'teca-style-1';
}

/**
 * Read date format config for a layout key.
 *
 * @param string $layout_key Layout key.
 * @param array  $settings   Settings source.
 * @return array{format:string,custom:string}
 */
function teca_get_layout_date_format_config( $layout_key, array $settings = array() ) {
	$layout_key = sanitize_key( (string) $layout_key );
	$defaults   = teca_get_default_layout_date_format_config();

	if ( '' === $layout_key ) {
		return $defaults;
	}

	if ( 0 === strpos( $layout_key, 'single:' ) ) {
		$layout_settings = teca_get_single_page_layout_settings();
		$date_formats    = isset( $layout_settings['date_formats'] ) && is_array( $layout_settings['date_formats'] )
			? $layout_settings['date_formats']
			: array();

		if ( isset( $date_formats[ $layout_key ] ) ) {
			return teca_sanitize_layout_date_format_config( $date_formats[ $layout_key ] );
		}

		return $defaults;
	}

	$date_formats = isset( $settings['date_formats'] ) && is_array( $settings['date_formats'] )
		? $settings['date_formats']
		: array();

	if ( isset( $date_formats[ $layout_key ] ) ) {
		return teca_sanitize_layout_date_format_config( $date_formats[ $layout_key ] );
	}

	return $defaults;
}

/**
 * Resolve PHP date format string for a layout key.
 *
 * @param string     $layout_key Layout key.
 * @param array|null $settings   Settings source.
 * @return string
 */
function teca_get_layout_date_format( $layout_key = '', array $settings = null ) {
	if ( null === $settings ) {
		$context  = teca_get_date_format_context();
		$settings = $context['settings'];

		if ( '' === $layout_key ) {
			$layout_key = $context['layout_key'];
		}
	}

	$settings = is_array( $settings ) ? $settings : array();

	if ( '' === $layout_key && ( ! empty( $settings['gs_teca_template'] ) || ! empty( $settings['view_type'] ) ) ) {
		$layout_key = teca_resolve_shortcode_layout_date_key( $settings );
	}

	$config    = teca_get_layout_date_format_config( $layout_key, $settings );
	$wp_format      = (string) get_option( 'date_format' );
	$selected       = $config['format'];
	$custom         = $config['custom'];

	if ( 'custom' === $selected ) {
		return '' !== $custom ? $custom : $wp_format;
	}

	if ( 'default' === $selected || '' === $selected ) {
		return $wp_format;
	}

	return $selected;
}

/**
 * Get event date timestamp.
 *
 * @param int    $event_id Event ID.
 * @param string $which    start|end.
 * @return int
 */
function teca_get_event_date_timestamp( $event_id, $which = 'start' ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return 0;
	}

	$meta_key = ( 'end' === $which ) ? '_EventEndDate' : '_EventStartDate';
	$date     = get_post_meta( $event_id, $meta_key, true );

	if ( empty( $date ) ) {
		return 0;
	}

	$timestamp = strtotime( (string) $date );

	return $timestamp ? (int) $timestamp : 0;
}

/**
 * Format event date text using layout-specific format.
 *
 * @param int         $event_id   Event ID.
 * @param string|null $layout_key Layout key.
 * @param array|null  $settings   Settings source.
 * @param int|null    $timestamp  Optional timestamp override.
 * @return string
 */
function teca_format_event_date_text( $event_id, $layout_key = null, $settings = null, $timestamp = null ) {
	$event_id = (int) $event_id;

	if ( ! $event_id && null === $timestamp ) {
		return '';
	}

	$format = teca_get_layout_date_format( (string) $layout_key, is_array( $settings ) ? $settings : null );

	if ( $event_id && null === $timestamp && function_exists( 'tribe_get_start_date' ) ) {
		return (string) tribe_get_start_date( $event_id, false, $format );
	}

	if ( null === $timestamp ) {
		$timestamp = teca_get_event_date_timestamp( $event_id, 'start' );
	}

	if ( ! $timestamp ) {
		return '';
	}

	if ( function_exists( 'wp_date' ) ) {
		return (string) wp_date( $format, (int) $timestamp );
	}

	return (string) date_i18n( $format, (int) $timestamp );
}

/**
 * Format event start date text.
 *
 * @param int         $event_id   Event ID.
 * @param string|null $layout_key Layout key.
 * @param array|null  $settings   Settings source.
 * @return string
 */
function teca_format_event_start_date_text( $event_id, $layout_key = null, $settings = null ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	$format = teca_get_layout_date_format( (string) $layout_key, is_array( $settings ) ? $settings : null );

	if ( function_exists( 'tribe_get_start_date' ) ) {
		return (string) tribe_get_start_date( $event_id, false, $format );
	}

	$timestamp = teca_get_event_date_timestamp( $event_id, 'start' );

	return teca_format_event_date_text( $event_id, $layout_key, $settings, $timestamp );
}

/**
 * Format event end date text.
 *
 * @param int         $event_id   Event ID.
 * @param string|null $layout_key Layout key.
 * @param array|null  $settings   Settings source.
 * @return string
 */
function teca_format_event_end_date_text( $event_id, $layout_key = null, $settings = null ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	$format = teca_get_layout_date_format( (string) $layout_key, is_array( $settings ) ? $settings : null );

	if ( function_exists( 'tribe_get_end_date' ) ) {
		return (string) tribe_get_end_date( $event_id, false, $format );
	}

	$timestamp = teca_get_event_date_timestamp( $event_id, 'end' );

	if ( ! $timestamp ) {
		return '';
	}

	if ( function_exists( 'wp_date' ) ) {
		return (string) wp_date( $format, $timestamp );
	}

	return (string) date_i18n( $format, $timestamp );
}

/**
 * Format arbitrary date string using layout format.
 *
 * @param string      $date_string Date string.
 * @param string|null $layout_key  Layout key.
 * @param array|null  $settings    Settings source.
 * @return string
 */
function teca_format_layout_date_string( $date_string, $layout_key = null, $settings = null ) {
	$date_string = (string) $date_string;

	if ( '' === $date_string ) {
		return '';
	}

	$timestamp = strtotime( $date_string );

	if ( ! $timestamp ) {
		return '';
	}

	$format = teca_get_layout_date_format( (string) $layout_key, is_array( $settings ) ? $settings : null );

	if ( function_exists( 'wp_date' ) ) {
		return (string) wp_date( $format, $timestamp );
	}

	return (string) date_i18n( $format, $timestamp );
}
