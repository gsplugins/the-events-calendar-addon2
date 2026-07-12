<?php
/**
 * Popup detail typography and color controls (admin/settings only in phase 1).
 *
 * @package GS_TECA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Popup detail typography group slugs with admin labels.
 *
 * @return array<string, string>
 */
function teca_get_popup_detail_typography_group_labels() {
	return array(
		'title'              => __( 'Title', 'the-events-calendar-addon2' ),
		'category'           => __( 'Category', 'the-events-calendar-addon2' ),
		'tag'                => __( 'Tag', 'the-events-calendar-addon2' ),
		'venue_title'        => __( 'Venue Title', 'the-events-calendar-addon2' ),
		'venue_value'        => __( 'Venue Value', 'the-events-calendar-addon2' ),
		'organizer_title'    => __( 'Organizer Title', 'the-events-calendar-addon2' ),
		'organizer_value'    => __( 'Organizer Value', 'the-events-calendar-addon2' ),
		'organizer_phone'    => __( 'Organizer Phone', 'the-events-calendar-addon2' ),
		'organizer_website'  => __( 'Organizer Website', 'the-events-calendar-addon2' ),
		'organizer_email'    => __( 'Organizer Email', 'the-events-calendar-addon2' ),
		'excerpt'            => __( 'Excerpt', 'the-events-calendar-addon2' ),
		'date'               => __( 'Date', 'the-events-calendar-addon2' ),
		'time'               => __( 'Time', 'the-events-calendar-addon2' ),
		'cost'               => __( 'Cost', 'the-events-calendar-addon2' ),
		'location'           => __( 'Location', 'the-events-calendar-addon2' ),
		'address'            => __( 'Address', 'the-events-calendar-addon2' ),
		'details'            => __( 'Details', 'the-events-calendar-addon2' ),
		'view_details_button' => __( 'View Details Button', 'the-events-calendar-addon2' ),
		'google_calendar_button' => __( 'Google Calendar Button', 'the-events-calendar-addon2' ),
		'event_website_button' => __( 'Event Website Button', 'the-events-calendar-addon2' ),
	);
}

/**
 * Map popup detail typography group slug to settings key.
 *
 * @return array<string, string>
 */
function teca_get_popup_detail_typography_group_map() {
	$map = array();

	foreach ( array_keys( teca_get_popup_detail_typography_group_labels() ) as $group ) {
		$map[ $group ] = 'popup_detail_' . $group . '_typography';
	}

	return $map;
}

/**
 * Popup detail typography group slugs available without Pro.
 *
 * @return string[]
 */
function teca_get_free_popup_detail_typography_group_slugs() {
	return apply_filters(
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
		'gs_teca_free_popup_detail_typography_group_slugs',
		array(
			'title',
		)
	);
}

/**
 * Whether a popup detail typography group slug is free.
 *
 * @param string $group Group slug.
 * @return bool
 */
function teca_is_free_popup_detail_typography_group( $group ) {
	return in_array( sanitize_key( (string) $group ), teca_get_free_popup_detail_typography_group_slugs(), true );
}

/**
 * Popup detail color field keys available without Pro.
 *
 * @return string[]
 */
function teca_get_free_popup_detail_color_field_keys() {
	return apply_filters(
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
		'gs_teca_free_popup_detail_color_field_keys',
		array(
			'popup_detail_title_color',
		)
	);
}

/**
 * Whether a popup detail color field key is free.
 *
 * @param string $field_key Color field key.
 * @return bool
 */
function teca_is_free_popup_detail_color_field( $field_key ) {
	return in_array( sanitize_key( (string) $field_key ), teca_get_free_popup_detail_color_field_keys(), true );
}

/**
 * Reset a popup detail typography group to inactive defaults.
 *
 * @param array  $settings       Shortcode settings.
 * @param string $group            Group slug.
 * @param string $typography_key   Typography setting key.
 * @return array
 */
function teca_reset_popup_detail_typography_group_settings( array $settings, $group, $typography_key ) {
	$settings[ $typography_key ] = (object) array();
	$settings[ teca_get_popup_detail_typography_custom_flag_key( $group ) ] = (object) teca_get_typography_field_custom_flag_defaults();

	if ( ! isset( $settings['popup_detail_typography_custom'] ) || ! is_array( $settings['popup_detail_typography_custom'] ) ) {
		$settings['popup_detail_typography_custom'] = array();
	}

	$settings['popup_detail_typography_custom'][ $group ] = teca_get_typography_field_custom_flag_defaults();

	return $settings;
}

/**
 * Reset a popup detail color field to inactive defaults.
 *
 * @param array  $settings  Shortcode settings.
 * @param string $field_key Color field key.
 * @return array
 */
function teca_reset_popup_detail_color_field_settings( array $settings, $field_key ) {
	$field_key  = sanitize_key( (string) $field_key );
	$custom_key = teca_get_popup_detail_color_custom_flag_key( $field_key );

	$settings[ $field_key ] = '';

	if ( ! isset( $settings['popup_detail_color_custom'] ) || ! is_array( $settings['popup_detail_color_custom'] ) ) {
		$settings['popup_detail_color_custom'] = array();
	}

	$settings['popup_detail_color_custom'][ $field_key ] = false;
	$settings[ $custom_key ]                             = false;

	return $settings;
}

/**
 * Strip Pro-only popup detail typography overrides when Pro is inactive.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_popup_detail_typography_pro_settings( array $settings ) {
	if ( teca_is_typography_color_controls_pro_feature_available() ) {
		return $settings;
	}

	foreach ( teca_get_popup_detail_typography_group_map() as $group => $typography_key ) {
		if ( teca_is_free_popup_detail_typography_group( $group ) ) {
			continue;
		}

		$settings = teca_reset_popup_detail_typography_group_settings( $settings, $group, $typography_key );
	}

	return $settings;
}

/**
 * Strip Pro-only popup detail color overrides when Pro is inactive.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_popup_detail_color_pro_settings( array $settings ) {
	if ( teca_is_typography_color_controls_pro_feature_available() ) {
		return $settings;
	}

	foreach ( teca_get_popup_detail_color_field_keys() as $field_key ) {
		if ( teca_is_free_popup_detail_color_field( $field_key ) ) {
			continue;
		}

		$settings = teca_reset_popup_detail_color_field_settings( $settings, $field_key );
	}

	return $settings;
}

/**
 * @return string[]
 */
function teca_get_popup_detail_typography_setting_keys() {
	return array_values( teca_get_popup_detail_typography_group_map() );
}

/**
 * Custom flag key for a popup detail typography group.
 *
 * @param string $group Group slug.
 * @return string
 */
function teca_get_popup_detail_typography_custom_flag_key( $group ) {
	$map = teca_get_popup_detail_typography_group_map();

	if ( empty( $map[ $group ] ) ) {
		return 'popup_detail_' . sanitize_key( (string) $group ) . '_typography_custom';
	}

	return $map[ $group ] . '_custom';
}

/**
 * Popup detail color field keys that support hover color.
 *
 * @return string[]
 */
function teca_get_popup_detail_color_link_field_groups() {
	return array(
		'organizer_phone',
		'organizer_website',
		'organizer_email',
	);
}

/**
 * Typography groups whose selectors include anchor descendants.
 *
 * @return string[]
 */
function teca_get_popup_detail_typography_link_groups() {
	return array_merge(
		array( 'category', 'tag' ),
		teca_get_popup_detail_color_link_field_groups()
	);
}

/**
 * Category detail selectors.
 *
 * @return string[]
 */
function teca_get_popup_detail_category_selectors() {
	return array(
		'.teca-popup-detail-category',
		'.teca-popup-detail-category a',
		'.teca-popup-detail-categories',
		'.teca-popup-detail-categories .teca-popup-detail-category',
		'.teca-popup-detail-categories a',
	);
}

/**
 * Tag detail selectors.
 *
 * @return string[]
 */
function teca_get_popup_detail_tag_selectors() {
	return array(
		'.teca-popup-detail-tag',
		'.teca-popup-detail-tag a',
		'.teca-popup-detail-tags',
		'.teca-popup-detail-tags .teca-popup-detail-tag',
		'.teca-popup-detail-tags a',
	);
}

/**
 * All popup detail color field metadata.
 *
 * @return array<string, array{group:string, label:string, property:string, pseudo:string, selectors:string[]}>
 */
function teca_get_popup_detail_color_field_map() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$labels = teca_get_popup_detail_typography_group_labels();

	$map = array(
		'popup_detail_title_color' => array(
			'group'      => 'title',
			'label'      => $labels['title'] . ' ' . __( 'Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => '',
			'selectors'  => array( '.teca-popup-detail-title' ),
		),
		'popup_detail_category_color' => array(
			'group'      => 'category',
			'label'      => $labels['category'] . ' ' . __( 'Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => '',
			'selectors'  => teca_get_popup_detail_category_selectors(),
		),
		'popup_detail_category_background_color' => array(
			'group'      => 'category',
			'label'      => $labels['category'] . ' ' . __( 'Background Color', 'the-events-calendar-addon2' ),
			'property'   => 'background-color',
			'pseudo'     => '',
			'selectors'  => array( '.teca-popup-detail-category', '.teca-popup-detail-categories .teca-popup-detail-category' ),
		),
		'popup_detail_category_hover_color' => array(
			'group'      => 'category',
			'label'      => $labels['category'] . ' ' . __( 'Hover Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => ':hover',
			'selectors'  => array( '.teca-popup-detail-category', '.teca-popup-detail-categories .teca-popup-detail-category' ),
		),
		'popup_detail_tag_color' => array(
			'group'      => 'tag',
			'label'      => $labels['tag'] . ' ' . __( 'Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => '',
			'selectors'  => teca_get_popup_detail_tag_selectors(),
		),
		'popup_detail_tag_background_color' => array(
			'group'      => 'tag',
			'label'      => $labels['tag'] . ' ' . __( 'Background Color', 'the-events-calendar-addon2' ),
			'property'   => 'background-color',
			'pseudo'     => '',
			'selectors'  => array( '.teca-popup-detail-tag', '.teca-popup-detail-tags .teca-popup-detail-tag' ),
		),
		'popup_detail_tag_hover_color' => array(
			'group'      => 'tag',
			'label'      => $labels['tag'] . ' ' . __( 'Hover Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => ':hover',
			'selectors'  => array( '.teca-popup-detail-tag', '.teca-popup-detail-tags .teca-popup-detail-tag' ),
		),
		'popup_detail_view_details_button_color' => array(
			'group'      => 'view_details_button',
			'label'      => $labels['view_details_button'] . ' ' . __( 'Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => '',
			'selectors'  => teca_get_popup_detail_view_details_button_selectors(),
		),
		'popup_detail_view_details_button_background_color' => array(
			'group'      => 'view_details_button',
			'label'      => $labels['view_details_button'] . ' ' . __( 'Background Color', 'the-events-calendar-addon2' ),
			'property'   => 'background-color',
			'pseudo'     => '',
			'selectors'  => teca_get_popup_detail_view_details_button_selectors(),
		),
		'popup_detail_view_details_button_hover_color' => array(
			'group'      => 'view_details_button',
			'label'      => $labels['view_details_button'] . ' ' . __( 'Hover Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => ':hover',
			'selectors'  => teca_get_popup_detail_view_details_button_selectors(),
		),
		'popup_detail_view_details_button_hover_background_color' => array(
			'group'      => 'view_details_button',
			'label'      => $labels['view_details_button'] . ' ' . __( 'Hover Background Color', 'the-events-calendar-addon2' ),
			'property'   => 'background-color',
			'pseudo'     => ':hover',
			'selectors'  => teca_get_popup_detail_view_details_button_selectors(),
		),
		'popup_detail_google_calendar_button_color' => array(
			'group'      => 'google_calendar_button',
			'label'      => $labels['google_calendar_button'] . ' ' . __( 'Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => '',
			'selectors'  => teca_get_popup_detail_google_calendar_button_selectors(),
		),
		'popup_detail_google_calendar_button_background_color' => array(
			'group'      => 'google_calendar_button',
			'label'      => $labels['google_calendar_button'] . ' ' . __( 'Background Color', 'the-events-calendar-addon2' ),
			'property'   => 'background-color',
			'pseudo'     => '',
			'selectors'  => teca_get_popup_detail_google_calendar_button_selectors(),
		),
		'popup_detail_google_calendar_button_hover_color' => array(
			'group'      => 'google_calendar_button',
			'label'      => $labels['google_calendar_button'] . ' ' . __( 'Hover Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => ':hover',
			'selectors'  => teca_get_popup_detail_google_calendar_button_selectors(),
		),
		'popup_detail_google_calendar_button_hover_background_color' => array(
			'group'      => 'google_calendar_button',
			'label'      => $labels['google_calendar_button'] . ' ' . __( 'Hover Background Color', 'the-events-calendar-addon2' ),
			'property'   => 'background-color',
			'pseudo'     => ':hover',
			'selectors'  => teca_get_popup_detail_google_calendar_button_selectors(),
		),
		'popup_detail_event_website_button_color' => array(
			'group'      => 'event_website_button',
			'label'      => $labels['event_website_button'] . ' ' . __( 'Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => '',
			'selectors'  => teca_get_popup_detail_event_website_button_selectors(),
		),
		'popup_detail_event_website_button_background_color' => array(
			'group'      => 'event_website_button',
			'label'      => $labels['event_website_button'] . ' ' . __( 'Background Color', 'the-events-calendar-addon2' ),
			'property'   => 'background-color',
			'pseudo'     => '',
			'selectors'  => teca_get_popup_detail_event_website_button_selectors(),
		),
		'popup_detail_event_website_button_hover_color' => array(
			'group'      => 'event_website_button',
			'label'      => $labels['event_website_button'] . ' ' . __( 'Hover Color', 'the-events-calendar-addon2' ),
			'property'   => 'color',
			'pseudo'     => ':hover',
			'selectors'  => teca_get_popup_detail_event_website_button_selectors(),
		),
		'popup_detail_event_website_button_hover_background_color' => array(
			'group'      => 'event_website_button',
			'label'      => $labels['event_website_button'] . ' ' . __( 'Hover Background Color', 'the-events-calendar-addon2' ),
			'property'   => 'background-color',
			'pseudo'     => ':hover',
			'selectors'  => teca_get_popup_detail_event_website_button_selectors(),
		),
	);

	foreach ( array_keys( $labels ) as $group ) {
		if ( in_array( $group, array( 'title', 'category', 'tag', 'view_details_button', 'google_calendar_button', 'event_website_button' ), true ) ) {
			continue;
		}

		$field_key = 'popup_detail_' . $group . '_color';
		$selectors = teca_get_popup_detail_element_selectors( $group );

		if ( empty( $selectors ) ) {
			continue;
		}

		$color_selectors = $selectors;

		if ( in_array( $group, teca_get_popup_detail_color_link_field_groups(), true ) ) {
			foreach ( $selectors as $selector ) {
				$color_selectors[] = $selector . ' a';
			}
		}

		$map[ $field_key ] = array(
			'group'     => $group,
			'label'     => $labels[ $group ] . ' ' . __( 'Color', 'the-events-calendar-addon2' ),
			'property'  => 'color',
			'pseudo'    => '',
			'selectors' => array_values( array_unique( $color_selectors ) ),
		);

		if ( in_array( $group, teca_get_popup_detail_color_link_field_groups(), true ) ) {
			$hover_key = 'popup_detail_' . $group . '_hover_color';
			$hover_selectors = array();

			foreach ( $selectors as $selector ) {
				$hover_selectors[] = $selector . ' a:hover';
			}

			$map[ $hover_key ] = array(
				'group'     => $group,
				'label'     => $labels[ $group ] . ' ' . __( 'Hover Color', 'the-events-calendar-addon2' ),
				'property'  => 'color',
				'pseudo'    => ':hover',
				'selectors' => $hover_selectors,
			);
		}
	}

	return $map;
}

/**
 * @return string[]
 */
function teca_get_popup_detail_color_field_keys() {
	return array_keys( teca_get_popup_detail_color_field_map() );
}

/**
 * @param string $field_key Color field key.
 * @return string
 */
function teca_get_popup_detail_color_custom_flag_key( $field_key ) {
	return sanitize_key( (string) $field_key ) . '_custom';
}

/**
 * Builder scope key for popup style presets.
 *
 * @param string $popup_style Popup style slug.
 * @return string
 */
function teca_get_popup_detail_design_scope( $popup_style ) {
	return 'popup:' . sanitize_key( (string) $popup_style );
}

/**
 * Supported popup style slugs for detail presets.
 *
 * @return string[]
 */
function teca_get_popup_detail_style_slugs() {
	return array(
		'default',
		'style-one',
		'style-two',
		'style-three',
		'style-four',
		'style-five',
		'style-six',
	);
}

/**
 * Empty typography preset shape.
 *
 * @return array<string, string>
 */
function teca_get_popup_detail_empty_typography() {
	return array(
		'font_family'     => '',
		'font_size'       => '',
		'font_weight'     => '',
		'line_height'     => '',
		'letter_spacing'  => '',
		'text_transform'  => '',
		'font_style'      => '',
		'text_decoration' => '',
	);
}

/**
 * Build a typography preset from partial values.
 *
 * @param array<string, string> $values Partial values.
 * @return array<string, string>
 */
function teca_popup_detail_typography_preset( array $values ) {
	return array_merge( teca_get_popup_detail_empty_typography(), $values );
}

/**
 * Meta label typography preset.
 *
 * @param array<string, string> $overrides Overrides.
 * @return array<string, string>
 */
function teca_popup_detail_meta_label_typography( array $overrides = array() ) {
	$defaults = array(
		'font_size'      => '10px',
		'font_weight'    => '900',
		'letter_spacing' => '0.12em',
		'text_transform' => 'uppercase',
		'line_height'    => '1',
	);

	return teca_popup_detail_typography_preset( array_merge( $defaults, $overrides ) );
}

/**
 * Meta value typography preset.
 *
 * @param array<string, string> $overrides Overrides.
 * @return array<string, string>
 */
function teca_popup_detail_meta_value_typography( array $overrides = array() ) {
	$defaults = array(
		'font_size'   => '14px',
		'font_weight' => '700',
		'line_height' => '1.55',
	);

	return teca_popup_detail_typography_preset( array_merge( $defaults, $overrides ) );
}

/**
 * Body/detail text typography preset.
 *
 * @param array<string, string> $overrides Overrides.
 * @return array<string, string>
 */
function teca_popup_detail_body_typography( array $overrides = array() ) {
	$defaults = array(
		'font_size'   => '15px',
		'line_height' => '1.75',
	);

	return teca_popup_detail_typography_preset( array_merge( $defaults, $overrides ) );
}

/**
 * Link text typography preset.
 *
 * @param array<string, string> $overrides Overrides.
 * @return array<string, string>
 */
function teca_popup_detail_link_typography( array $overrides = array() ) {
	$defaults = array(
		'font_size'       => '14px',
		'line_height'     => '1.55',
		'font_weight'     => '700',
		'text_decoration' => 'none',
	);

	return teca_popup_detail_typography_preset( array_merge( $defaults, $overrides ) );
}

/**
 * Base color map for a popup style.
 *
 * @param string $popup_style Popup style slug.
 * @return array<string, string>
 */
function teca_get_popup_detail_color_defaults_for_style( $popup_style ) {
	$popup_style = sanitize_key( (string) $popup_style );

	$palettes = array(
		'default'    => array(
			'title'              => '#111827',
			'category'           => '#3730a3',
			'category_bg'        => '#eef2ff',
			'tag'                => '#374151',
			'tag_bg'             => '#f3f4f6',
			'meta_label'         => '#6b7280',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Popup color palette key, not a WP_Query meta_value parameter.
			'meta_value'         => '#111827',
			'body'               => '#4b5563',
			'meta_item'          => '#111827',
			'link'               => '#3730a3',
			'link_hover'         => '#3730a3',
		),
		'style-one'  => array(
			'title'              => '#0f172a',
			'category'           => '#5b21b6',
			'category_bg'        => '#ede9fe',
			'tag'                => '#334155',
			'tag_bg'             => '#f1f5f9',
			'meta_label'         => '#64748b',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Popup color palette key, not a WP_Query meta_value parameter.
			'meta_value'         => '#0f172a',
			'body'               => '#475569',
			'meta_item'          => '#334155',
			'link'               => '#7c3aed',
			'link_hover'         => '#7c3aed',
		),
		'style-two'  => array(
			'title'              => '#0f172a',
			'category'           => '#4338ca',
			'category_bg'        => '#eef2ff',
			'tag'                => '#4338ca',
			'tag_bg'             => '#eef2ff',
			'meta_label'         => '#64748b',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Popup color palette key, not a WP_Query meta_value parameter.
			'meta_value'         => '#0f172a',
			'body'               => '#475569',
			'meta_item'          => '#334155',
			'link'               => '#2563eb',
			'link_hover'         => '#2563eb',
		),
	);

	if ( isset( $palettes[ $popup_style ] ) ) {
		return $palettes[ $popup_style ];
	}

	$fallback_style = in_array( $popup_style, array( 'style-three', 'style-five' ), true ) ? 'style-one' : 'style-two';

	return $palettes[ $fallback_style ];
}

/**
 * Typography defaults for a popup style and group.
 *
 * @param string $popup_style Popup style slug.
 * @param string $group       Typography group slug.
 * @return array<string, string>
 */
function teca_get_popup_style_default_detail_typography( $popup_style, $group ) {
	$popup_style = sanitize_key( (string) $popup_style );
	$group       = sanitize_key( (string) $group );

	if ( 'title' === $group ) {
		$overrides = array(
			'font_size'       => '28px',
			'font_weight'     => '700',
			'line_height'     => '1.25',
		);

		if ( 'style-one' === $popup_style || in_array( $popup_style, array( 'style-three', 'style-five' ), true ) ) {
			$overrides = array(
				'font_size'       => '48px',
				'font_weight'     => '900',
				'line_height'     => '1.02',
				'letter_spacing'  => '-0.055em',
			);
		}

		if ( 'style-two' === $popup_style || in_array( $popup_style, array( 'style-four', 'style-six' ), true ) ) {
			$overrides = array(
				'font_family'     => 'Inter',
				'font_size'       => '56px',
				'font_weight'     => '950',
				'line_height'     => '1',
				'letter_spacing'  => '-0.065em',
			);
		}

		return teca_popup_detail_typography_preset( $overrides );
	}

	if ( 'category' === $group || 'tag' === $group ) {
		$overrides = array(
			'font_size'       => '13px',
			'font_weight'     => '600',
		);

		if ( 'style-one' === $popup_style || in_array( $popup_style, array( 'style-three', 'style-five' ), true ) ) {
			$overrides = array(
				'font_size'       => '12px',
				'font_weight'     => '900',
				'letter_spacing'  => '0.06em',
				'text_transform'  => 'uppercase',
			);
		}

		if ( 'style-two' === $popup_style || in_array( $popup_style, array( 'style-four', 'style-six' ), true ) ) {
			$overrides = array(
				'font_family'     => 'Inter',
				'font_size'       => '12px',
				'font_weight'     => '800',
			);
		}

		return teca_popup_detail_typography_preset( $overrides );
	}

	if ( in_array( $group, array( 'view_details_button', 'google_calendar_button', 'event_website_button' ), true ) ) {
		return teca_popup_detail_typography_preset(
			array(
				'font_size'   => '14px',
				'font_weight' => '700',
				'line_height' => '1.3',
			)
		);
	}

	$palette     = teca_get_popup_detail_color_defaults_for_style( $popup_style );

	$meta_label_groups = array( 'venue_title', 'organizer_title' );
	$meta_value_groups = array( 'venue_value', 'organizer_value', 'date', 'time', 'cost', 'location' );
	$body_groups       = array( 'excerpt', 'details', 'address' );
	$link_groups       = teca_get_popup_detail_color_link_field_groups();

	if ( in_array( $group, $meta_label_groups, true ) ) {
		$overrides = array();

		if ( 'default' === $popup_style ) {
			$overrides = array(
				'font_size'      => '11px',
				'font_weight'    => '700',
				'letter_spacing' => '0.08em',
			);
		}

		if ( 'style-two' === $popup_style || in_array( $popup_style, array( 'style-four', 'style-six' ), true ) ) {
			$overrides['font_family'] = 'Inter';
		}

		return teca_popup_detail_meta_label_typography( $overrides );
	}

	if ( in_array( $group, $meta_value_groups, true ) ) {
		$overrides = array();

		if ( 'default' === $popup_style ) {
			$overrides['font_size'] = '15px';
		}

		if ( 'style-two' === $popup_style || in_array( $popup_style, array( 'style-four', 'style-six' ), true ) ) {
			$overrides['font_family'] = 'Inter';
		}

		return teca_popup_detail_meta_value_typography( $overrides );
	}

	if ( in_array( $group, $body_groups, true ) ) {
		$overrides = array();

		if ( 'default' === $popup_style ) {
			$overrides['line_height'] = '1.7';
		}

		if ( 'style-two' === $popup_style || in_array( $popup_style, array( 'style-four', 'style-six' ), true ) ) {
			$overrides['font_family'] = 'Inter';
			$overrides['line_height'] = '1.78';
		}

		return teca_popup_detail_body_typography( $overrides );
	}

	if ( in_array( $group, $link_groups, true ) ) {
		$overrides = array();

		if ( 'default' === $popup_style ) {
			$overrides['font_weight'] = '600';
		}

		if ( 'style-two' === $popup_style || in_array( $popup_style, array( 'style-four', 'style-six' ), true ) ) {
			$overrides['font_family'] = 'Inter';
		}

		return teca_popup_detail_link_typography( $overrides );
	}

	return teca_get_popup_detail_empty_typography();
}

/**
 * Default color for a popup detail color field key.
 *
 * @param string $popup_style Popup style slug.
 * @param string $field_key   Color field key.
 * @return string
 */
function teca_get_popup_style_default_detail_color_value( $popup_style, $field_key ) {
	$palette   = teca_get_popup_detail_color_defaults_for_style( $popup_style );
	$field_key = sanitize_key( (string) $field_key );

	switch ( $field_key ) {
		case 'popup_detail_title_color':
			return $palette['title'];
		case 'popup_detail_category_color':
			return $palette['category'];
		case 'popup_detail_category_background_color':
			return $palette['category_bg'];
		case 'popup_detail_category_hover_color':
			return $palette['category'];
		case 'popup_detail_tag_color':
			return $palette['tag'];
		case 'popup_detail_tag_background_color':
			return $palette['tag_bg'];
		case 'popup_detail_tag_hover_color':
			return $palette['tag'];
		case 'popup_detail_view_details_button_color':
			return '#c45c26';
		case 'popup_detail_view_details_button_background_color':
			return '';
		case 'popup_detail_view_details_button_hover_color':
			return '#9a471e';
		case 'popup_detail_view_details_button_hover_background_color':
			return '';
		case 'popup_detail_google_calendar_button_color':
			return '#ffffff';
		case 'popup_detail_google_calendar_button_background_color':
			return '#111827';
		case 'popup_detail_google_calendar_button_hover_color':
			return '#ffffff';
		case 'popup_detail_google_calendar_button_hover_background_color':
			return '#000000';
		case 'popup_detail_event_website_button_color':
			return '#ffffff';
		case 'popup_detail_event_website_button_background_color':
			return '#111827';
		case 'popup_detail_event_website_button_hover_color':
			return '#ffffff';
		case 'popup_detail_event_website_button_hover_background_color':
			return '#1f2937';
	}

	if ( preg_match( '/^popup_detail_(.+)_hover_color$/', $field_key, $matches ) ) {
		$group = sanitize_key( $matches[1] );
		if ( in_array( $group, teca_get_popup_detail_color_link_field_groups(), true ) ) {
			return $palette['link_hover'];
		}
		return '';
	}

	if ( preg_match( '/^popup_detail_(.+)_color$/', $field_key, $matches ) ) {
		return teca_get_popup_style_default_detail_color( $popup_style, sanitize_key( $matches[1] ) );
	}

	return '';
}

/**
 * Default color for a popup detail group slug.
 *
 * @param string $popup_style Popup style slug.
 * @param string $group       Group slug.
 * @return string
 */
function teca_get_popup_style_default_detail_color( $popup_style, $group ) {
	$palette = teca_get_popup_detail_color_defaults_for_style( $popup_style );
	$group   = sanitize_key( (string) $group );

	if ( in_array( $group, array( 'venue_title', 'organizer_title' ), true ) ) {
		return $palette['meta_label'];
	}

	if ( in_array( $group, array( 'venue_value', 'organizer_value', 'date', 'time', 'cost', 'location' ), true ) ) {
		return $palette['meta_value'];
	}

	if ( in_array( $group, array( 'excerpt', 'details' ), true ) ) {
		return $palette['body'];
	}

	if ( 'address' === $group ) {
		return $palette['meta_item'];
	}

	if ( in_array( $group, teca_get_popup_detail_color_link_field_groups(), true ) ) {
		return $palette['link'];
	}

	if ( 'view_details_button' === $group ) {
		return '#c45c26';
	}

	if ( 'google_calendar_button' === $group ) {
		return '#ffffff';
	}

	if ( 'event_website_button' === $group ) {
		return '#ffffff';
	}

	return '';
}

/**
 * Hover color default for link groups.
 *
 * @param string $popup_style Popup style slug.
 * @param string $group       Group slug.
 * @return string
 */
function teca_get_popup_style_default_detail_hover_color( $popup_style, $group ) {
	$palette = teca_get_popup_detail_color_defaults_for_style( $popup_style );

	if ( in_array( $group, teca_get_popup_detail_color_link_field_groups(), true ) ) {
		return $palette['link_hover'];
	}

	return '';
}

/**
 * Full design preset for one popup style.
 *
 * @param string $popup_style Popup style slug.
 * @return array{typography: array<string, array<string, string>>, colors: array<string, string>}
 */
function teca_get_popup_detail_design_preset( $popup_style ) {
	$typography = array();
	$colors     = array();

	foreach ( array_keys( teca_get_popup_detail_typography_group_labels() ) as $group ) {
		$typography[ $group ] = teca_get_popup_style_default_detail_typography( $popup_style, $group );
	}

	foreach ( teca_get_popup_detail_color_field_keys() as $field_key ) {
		$colors[ $field_key ] = teca_get_popup_style_default_detail_color_value( $popup_style, $field_key );
	}

	return array(
		'typography' => $typography,
		'colors'     => $colors,
	);
}

/**
 * Registry for shortcode builder UI.
 *
 * @return array<string, array{typography: array<string, array<string, string>>, colors: array<string, string>}>
 */
function teca_get_popup_detail_design_registry_for_builder() {
	$registry = array();

	foreach ( teca_get_popup_detail_style_slugs() as $popup_style ) {
		$design = teca_get_popup_detail_design_preset( $popup_style );

		foreach ( $design['typography'] as $group => $typography ) {
			$design['typography'][ $group ] = teca_typography_defaults_to_control_format( $typography );
		}

		$registry[ teca_get_popup_detail_design_scope( $popup_style ) ] = $design;
	}

	return $registry;
}

/**
 * Typography group options for builder UI.
 *
 * @return array<int, array{label:string, value:string, setting_key:string}>
 */
function teca_get_popup_detail_typography_select_options() {
	$options = array();
	$map     = teca_get_popup_detail_typography_group_map();
	$labels  = teca_get_popup_detail_typography_group_labels();

	foreach ( $map as $group => $setting_key ) {
		$options[] = array(
			'label'       => $labels[ $group ] . ' ' . __( 'Typography', 'the-events-calendar-addon2' ),
			'value'       => $group,
			'setting_key' => $setting_key,
			'pro'         => ! teca_is_free_popup_detail_typography_group( $group ),
		);
	}

	return $options;
}

/**
 * Color field options for builder UI.
 *
 * @return array<int, array{label:string, value:string, group:string}>
 */
function teca_get_popup_detail_color_select_options() {
	$options = array();

	foreach ( teca_get_popup_detail_color_field_map() as $field_key => $meta ) {
		$options[] = array(
			'label' => $meta['label'],
			'value' => $field_key,
			'group' => $meta['group'],
			'pro'   => ! teca_is_free_popup_detail_color_field( $field_key ),
		);
	}

	return $options;
}

/**
 * Free-only popup detail typography group options for builder UI.
 *
 * @return array<int, array{label:string, value:string, setting_key:string}>
 */
function teca_get_free_popup_detail_typography_select_options() {
	return array_values(
		array_filter(
			teca_get_popup_detail_typography_select_options(),
			static function( $option ) {
				return teca_is_free_popup_detail_typography_group( $option['value'] ?? '' );
			}
		)
	);
}

/**
 * Free-only popup detail color field options for builder UI.
 *
 * @return array<int, array{label:string, value:string, group:string}>
 */
function teca_get_free_popup_detail_color_select_options() {
	return array_values(
		array_filter(
			teca_get_popup_detail_color_select_options(),
			static function( $option ) {
				return teca_is_free_popup_detail_color_field( $option['value'] ?? '' );
			}
		)
	);
}

/**
 * Get field-level custom flags for a popup detail typography group.
 *
 * @param array  $settings Shortcode settings.
 * @param string $group    Group slug.
 * @return array<string, bool>
 */
function teca_get_popup_detail_typography_field_custom_flags( $settings, $group ) {
	$map = teca_get_popup_detail_typography_group_map();

	if ( empty( $map[ $group ] ) || ! is_array( $settings ) ) {
		return teca_get_typography_field_custom_flag_defaults();
	}

	$group = sanitize_key( (string) $group );

	if ( isset( $settings['popup_detail_typography_custom'][ $group ] ) ) {
		$raw = $settings['popup_detail_typography_custom'][ $group ];

		if ( is_object( $raw ) ) {
			$raw = (array) $raw;
		}

		if ( is_array( $raw ) ) {
			return teca_normalize_typography_field_custom_flags( $raw );
		}
	}

	$custom_key = teca_get_popup_detail_typography_custom_flag_key( $group );
	$raw        = $settings[ $custom_key ] ?? array();

	if ( is_object( $raw ) ) {
		$raw = (array) $raw;
	}

	if ( is_array( $raw ) ) {
		return teca_normalize_typography_field_custom_flags( $raw );
	}

	return teca_get_typography_field_custom_flag_defaults();
}

/**
 * Whether popup detail color should be treated as customized/saved.
 *
 * @param array  $settings  Shortcode settings.
 * @param string $field_key Color field key.
 * @return bool
 */
function teca_should_apply_popup_detail_color( $settings, $field_key ) {
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return false;
	}

	$field_key = sanitize_key( (string) $field_key );

	if ( ! teca_is_free_popup_detail_color_field( $field_key ) && ! teca_is_typography_color_controls_pro_feature_available() ) {
		return false;
	}

	$custom_key = teca_get_popup_detail_color_custom_flag_key( $field_key );
	$is_active  = false;

	if ( isset( $settings['popup_detail_color_custom'][ $field_key ] ) ) {
		$is_active = teca_is_truthy_setting( $settings['popup_detail_color_custom'][ $field_key ] );
	} else {
		$is_active = teca_is_truthy_setting( $settings[ $custom_key ] ?? false );
	}

	if ( ! $is_active ) {
		return false;
	}

	return '' !== (string) ( $settings[ $field_key ] ?? '' );
}

/**
 * Strip inactive popup detail typography values.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_strip_inactive_popup_detail_typography_values( array $settings ) {
	$control_map = teca_get_typography_control_to_custom_field_map();

	foreach ( teca_get_popup_detail_typography_group_map() as $group => $typography_key ) {
		$flags      = teca_get_popup_detail_typography_field_custom_flags( $settings, $group );
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
 * Strip inactive popup detail color values.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_strip_inactive_popup_detail_color_values( array $settings ) {
	foreach ( teca_get_popup_detail_color_field_keys() as $field_key ) {
		if ( ! teca_should_apply_popup_detail_color( $settings, $field_key ) ) {
			$settings[ $field_key ] = '';
		}
	}

	return $settings;
}

/**
 * Prepare popup detail typography settings for save/load.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_prepare_popup_detail_typography_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return $settings;
	}

	$settings = teca_sanitize_popup_detail_typography_pro_settings( $settings );

	if ( ! isset( $settings['popup_detail_typography_custom'] ) || ! is_array( $settings['popup_detail_typography_custom'] ) ) {
		$settings['popup_detail_typography_custom'] = array();
	}

	foreach ( teca_get_popup_detail_typography_group_map() as $group => $typography_key ) {
		$custom_key = teca_get_popup_detail_typography_custom_flag_key( $group );
		$flags      = teca_get_popup_detail_typography_field_custom_flags( $settings, $group );

		$settings['popup_detail_typography_custom'][ $group ] = $flags;
		$settings[ $custom_key ]                              = $flags;
	}

	return teca_strip_inactive_popup_detail_typography_values( $settings );
}

/**
 * Whether a popup detail color field is marked custom.
 *
 * @param array  $settings  Shortcode settings.
 * @param string $field_key Color field key.
 * @return bool
 */
function teca_is_popup_detail_color_custom( $settings, $field_key ) {
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return false;
	}

	$field_key = sanitize_key( (string) $field_key );

	if ( isset( $settings['popup_detail_color_custom'][ $field_key ] ) ) {
		return teca_is_truthy_setting( $settings['popup_detail_color_custom'][ $field_key ] );
	}

	$custom_key = teca_get_popup_detail_color_custom_flag_key( $field_key );

	return teca_is_truthy_setting( $settings[ $custom_key ] ?? false );
}

/**
 * Prepare popup detail color settings for save/load.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_prepare_popup_detail_color_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return $settings;
	}

	$settings = teca_sanitize_popup_detail_color_pro_settings( $settings );

	if ( ! isset( $settings['popup_detail_color_custom'] ) || ! is_array( $settings['popup_detail_color_custom'] ) ) {
		$settings['popup_detail_color_custom'] = array();
	}

	foreach ( teca_get_popup_detail_color_field_keys() as $field_key ) {
		$custom_key = teca_get_popup_detail_color_custom_flag_key( $field_key );
		$is_active  = teca_is_popup_detail_color_custom( $settings, $field_key );

		$settings['popup_detail_color_custom'][ $field_key ] = $is_active;
		$settings[ $custom_key ]                             = $is_active;
	}

	return teca_strip_inactive_popup_detail_color_values( $settings );
}

/**
 * Prepare all popup detail settings.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_prepare_popup_detail_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return $settings;
	}

	$settings = teca_prepare_popup_detail_typography_settings( $settings );
	$settings = teca_prepare_popup_detail_color_settings( $settings );

	return $settings;
}

/**
 * Default shortcode settings for popup detail controls.
 *
 * @return array<string, mixed>
 */
function teca_get_popup_detail_default_settings() {
	$typography_custom = array();
	$color_custom      = array();
	$defaults          = array();

	foreach ( teca_get_popup_detail_typography_group_map() as $group => $typography_key ) {
		$defaults[ $typography_key ] = (object) array();
		$defaults[ teca_get_popup_detail_typography_custom_flag_key( $group ) ] = (object) teca_get_typography_field_custom_flag_defaults();
		$typography_custom[ $group ] = (object) teca_get_typography_field_custom_flag_defaults();
	}

	foreach ( teca_get_popup_detail_color_field_keys() as $field_key ) {
		$defaults[ $field_key ] = '';
		$defaults[ teca_get_popup_detail_color_custom_flag_key( $field_key ) ] = false;
		$color_custom[ $field_key ] = false;
	}

	$defaults['popup_detail_typography_custom'] = (object) $typography_custom;
	$defaults['popup_detail_color_custom']      = (object) $color_custom;

	return $defaults;
}

/**
 * Whether popup detail CSS should be generated for these settings.
 *
 * @param array $settings Shortcode settings.
 * @return bool
 */
function teca_should_render_popup_detail_css( $settings ) {
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		return false;
	}

	$link_type = (string) ( $settings['gs_teca_link_type'] ?? '' );

	return in_array( $link_type, array( 'popup', 'single_page' ), true );
}

/**
 * Popup CSS scope roots (popup markup may live outside #gs_teca_area_*).
 *
 * @param int|string $shortcode_id Shortcode ID.
 * @return string[]
 */
function teca_get_popup_detail_css_scope_roots( $shortcode_id, $settings = null ) {
	$id = is_numeric( $shortcode_id ) ? absint( $shortcode_id ) : sanitize_key( (string) $shortcode_id );

	if ( ! $id ) {
		return array();
	}

	$roots = array();
	$link_type = is_array( $settings ) ? (string) ( $settings['gs_teca_link_type'] ?? '' ) : '';

	if ( 'popup' === $link_type || '' === $link_type ) {
		$popup_root = '.gs_teca_popup_shortcode_' . $id;

		$roots = array(
			$popup_root,
			'.mfp-wrap ' . $popup_root,
			'.mfp-container ' . $popup_root,
			'.mfp-content ' . $popup_root,
			'#gs_teca_area_' . $id . ' ' . $popup_root,
		);
	}

	if ( 'single_page' === $link_type ) {
		$roots[] = '#gs_teca_area_' . $id;
		$roots[] = '#gs_teca_area_' . $id . ' .teca-single-event';
	}

	return array_values( array_unique( $roots ) );
}

/**
 * Popup/single detail View Details button selectors.
 *
 * @return string[]
 */
function teca_get_popup_detail_view_details_button_selectors() {
	return array(
		'.teca-popup-button.teca-view-details',
		'.teca-popup-actions .teca-view-details',
		'.teca-popup-actions .gs-teca-btn-link',
		'.teca-single-button',
		'.teca-single-button.teca-event-button',
	);
}

/**
 * Popup/single detail Google Calendar button selectors.
 *
 * @return string[]
 */
function teca_get_popup_detail_google_calendar_button_selectors() {
	return array(
		'.teca-popup-actions .teca-google-calendar-btn',
		'.teca-google-calendar-btn--popup',
		'.teca-google-calendar-btn--single',
		'.teca-google-calendar-actions--single .teca-google-calendar-btn',
	);
}

/**
 * Popup/single detail Event Website button selectors.
 *
 * @return string[]
 */
function teca_get_popup_detail_event_website_button_selectors() {
	return array(
		'.teca-event-website-btn',
		'.teca-popup-website-link',
		'.teca-popup-actions .teca-event-website-btn',
		'.teca-popup-actions .teca-popup-website-link',
		'.teca-popup-button.teca-event-website-btn',
		'.teca-single-website-link',
		'.teca-single-element-website a',
	);
}

/**
 * Base element selectors for a popup detail typography group.
 *
 * @param string $group Group slug.
 * @return string[]
 */
function teca_get_popup_detail_element_selectors( $group ) {
	$group = sanitize_key( (string) $group );

	$map = array(
		'title'             => array( '.teca-popup-detail-title' ),
		'category'          => teca_get_popup_detail_category_selectors(),
		'tag'               => teca_get_popup_detail_tag_selectors(),
		'venue_title'       => array( '.teca-popup-detail-venue-title' ),
		'venue_value'       => array( '.teca-popup-detail-venue-value' ),
		'organizer_title'   => array( '.teca-popup-detail-organizer-title' ),
		'organizer_value'   => array( '.teca-popup-detail-organizer-value' ),
		'organizer_phone'   => array( '.teca-popup-detail-organizer-phone', '.teca-popup-detail-organizer-phone a' ),
		'organizer_website' => array( '.teca-popup-detail-organizer-website', '.teca-popup-detail-organizer-website a' ),
		'organizer_email'   => array( '.teca-popup-detail-organizer-email', '.teca-popup-detail-organizer-email a' ),
		'excerpt'           => array( '.teca-popup-detail-excerpt' ),
		'date'              => array( '.teca-popup-detail-date' ),
		'time'              => array( '.teca-popup-detail-time' ),
		'cost'              => array( '.teca-popup-detail-cost' ),
		'location'          => array( '.teca-popup-detail-location' ),
		'address'           => array( '.teca-popup-detail-address' ),
		'details'           => array( '.teca-popup-detail-description', '.teca-popup-detail-details' ),
		'view_details_button' => teca_get_popup_detail_view_details_button_selectors(),
		'google_calendar_button' => teca_get_popup_detail_google_calendar_button_selectors(),
		'event_website_button' => teca_get_popup_detail_event_website_button_selectors(),
	);

	return $map[ $group ] ?? array();
}

/**
 * Color selectors for a popup detail color field.
 *
 * @param string $field_key Color field key.
 * @return string[]
 */
function teca_get_popup_detail_color_element_selectors( $field_key ) {
	$field_key = sanitize_key( (string) $field_key );
	$map       = teca_get_popup_detail_color_field_map();

	if ( empty( $map[ $field_key ]['selectors'] ) ) {
		return array();
	}

	return (array) $map[ $field_key ]['selectors'];
}

/**
 * Build popup-scoped selectors for detail typography/color rules.
 *
 * @param int|string $shortcode_id   Shortcode ID.
 * @param string[]   $base_selectors Base element selectors.
 * @return string[]
 */
function teca_build_popup_detail_scoped_selectors( $shortcode_id, array $base_selectors, $settings = null ) {
	$selectors = array();

	if ( empty( $base_selectors ) ) {
		return $selectors;
	}

	foreach ( teca_get_popup_detail_css_scope_roots( $shortcode_id, $settings ) as $root ) {
		foreach ( $base_selectors as $selector ) {
			$selectors[] = $root . ' ' . $selector;
		}
	}

	return array_values( array_unique( $selectors ) );
}

/**
 * Typography CSS rules for popup detail overrides (always important).
 *
 * @param array|object|null $typography   Typography settings.
 * @param array|null        $field_custom Field-level custom flags.
 * @return string[]
 */
function teca_get_popup_detail_typography_css_rules( $typography, $field_custom = null ) {
	$rules = teca_get_typography_css_rules( $typography, $field_custom );

	return array_map(
		static function( $rule ) {
			if ( false !== strpos( $rule, '!important' ) ) {
				return $rule;
			}

			return $rule . ' !important';
		},
		$rules
	);
}

/**
 * Whether a popup detail typography group has any active field override.
 *
 * @param array  $settings Shortcode settings.
 * @param string $group    Group slug.
 * @return bool
 */
function teca_is_popup_detail_typography_override_active( $settings, $group ) {
	$group = sanitize_key( (string) $group );

	if ( ! teca_is_free_popup_detail_typography_group( $group ) && ! teca_is_typography_color_controls_pro_feature_available() ) {
		return false;
	}

	$flags = teca_get_popup_detail_typography_field_custom_flags( $settings, $group );

	foreach ( $flags as $is_active ) {
		if ( $is_active ) {
			return true;
		}
	}

	return false;
}

/**
 * Render popup detail typography CSS scoped to the shortcode popup wrapper.
 *
 * @param array      $settings     Shortcode settings.
 * @param int|string $shortcode_id Shortcode ID.
 * @return string
 */
function teca_render_popup_detail_typography_scoped_css( array $settings, $shortcode_id ) {
	if ( ! teca_should_render_popup_detail_css( $settings ) ) {
		return '';
	}

	$settings = teca_prepare_popup_detail_typography_settings( $settings );
	$css      = '';

	foreach ( teca_get_popup_detail_typography_group_map() as $group => $typography_key ) {
		if ( ! teca_is_popup_detail_typography_override_active( $settings, $group ) ) {
			continue;
		}

		$field_custom = teca_get_popup_detail_typography_field_custom_flags( $settings, $group );
		$typography   = $settings[ $typography_key ] ?? array();

		if ( is_object( $typography ) ) {
			$typography = (array) $typography;
		}

		$rules = teca_get_popup_detail_typography_css_rules( $typography, $field_custom );
		$selectors    = teca_build_popup_detail_scoped_selectors(
			$shortcode_id,
			teca_get_popup_detail_element_selectors( $group ),
			$settings
		);

		if ( empty( $rules ) || empty( $selectors ) ) {
			continue;
		}

		$css .= implode( ',', $selectors ) . '{' . implode( ';', $rules ) . '}';
	}

	return $css;
}

/**
 * Render popup detail color CSS scoped to the shortcode popup wrapper.
 *
 * @param array      $settings     Shortcode settings.
 * @param int|string $shortcode_id Shortcode ID.
 * @return string
 */
function teca_render_popup_detail_color_scoped_css( array $settings, $shortcode_id ) {
	if ( ! teca_should_render_popup_detail_css( $settings ) ) {
		return '';
	}

	$settings = teca_prepare_popup_detail_color_settings( $settings );
	$css      = '';

	foreach ( teca_get_popup_detail_color_field_keys() as $field_key ) {
		if ( ! teca_should_apply_popup_detail_color( $settings, $field_key ) ) {
			continue;
		}

		$meta = teca_get_popup_detail_color_field_map()[ $field_key ] ?? null;

		if ( empty( $meta ) ) {
			continue;
		}

		$value         = (string) ( $settings[ $field_key ] ?? '' );
		$base_selectors = teca_get_popup_detail_color_element_selectors( $field_key );
		$pseudo        = (string) ( $meta['pseudo'] ?? '' );
		$property      = (string) ( $meta['property'] ?? 'color' );

		if ( '' === $value || empty( $base_selectors ) ) {
			continue;
		}

		$output_selectors = array();

		foreach ( teca_build_popup_detail_scoped_selectors( $shortcode_id, $base_selectors, $settings ) as $selector ) {
			$output_selectors[] = '' !== $pseudo ? $selector . $pseudo : $selector;
		}

		if ( empty( $output_selectors ) ) {
			continue;
		}

		$css .= implode( ',', array_unique( $output_selectors ) ) . '{' . $property . ':' . esc_attr( $value ) . ' !important}';
	}

	return $css;
}
