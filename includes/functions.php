<?php 

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;
if ( ! defined( 'ABSPATH' ) ) exit;

require_once GS_TECA_PLUGIN_DIR . 'includes/calendar-renderer.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-calendar-layout.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/events-section-renderer.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/venue-template-renderer.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-venue-template.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/organizer-template-renderer.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-organizer-template.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/timeline-renderer.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/accordion-renderer.php';

function is_pro_compatible() {
    return true;
}

function is_pro_active() {
    return false;
}

function is_pro_active_and_valid() {
    return false;
}

/**
 * Activation redirects
 */
function on_activation() {
    add_option('gs_teca_activation_redirect', true);
}

/**
 * Remove Reviews Metadata on plugin Deactivation.
 */
function on_deactivation() {
    delete_option('gs_teca_active_time');
    delete_option('gs_teca_maybe_later');
    delete_option('gsadmin_maybe_later');
}

/**
 * Legacy textdomain loader — WordPress.org loads translations automatically.
 */
function gs_load_textdomain() {
    // Intentionally empty for WordPress.org distribution.
}

function gs_update_plugin_version() {
    if ( GS_TECA_VERSION !==  get_option('the_events_calendar_addon_version') ) {
        update_option( 'the_events_calendar_addon_version', GS_TECA_VERSION );
        return true;
    }
    return false;
}

/**
 * Get saved TECA shortcodes for integrations and admin UI.
 *
 * @return array<int, array<string, mixed>>
 */
function get_shortcodes() {
    if ( ! function_exists( __NAMESPACE__ . '\\plugin' ) || ! plugin()->builder ) {
        return [];
    }

    $shortcodes = plugin()->builder->_get_shortcodes( null, false, true );

    return is_array( $shortcodes ) ? $shortcodes : [];
}

/**
 * Whether a TECA shortcode exists.
 *
 * @param int $shortcode_id Shortcode ID.
 * @return bool
 */
function teca_shortcode_exists( $shortcode_id ) {
    $shortcode_id = absint( $shortcode_id );

    if ( ! $shortcode_id ) {
        return false;
    }

    foreach ( get_shortcodes() as $shortcode ) {
        if ( ! is_array( $shortcode ) || empty( $shortcode['id'] ) ) {
            continue;
        }

        if ( absint( $shortcode['id'] ) === $shortcode_id ) {
            return true;
        }
    }

    return false;
}

/**
 * Get saved TECA shortcodes as ID => title pairs for builder integrations.
 *
 * @return array<int, string>
 */
function teca_get_saved_shortcodes_for_builder() {
    $options = [];

    foreach ( get_shortcodes() as $shortcode ) {
        if ( ! is_array( $shortcode ) || empty( $shortcode['id'] ) ) {
            continue;
        }

        $shortcode_id = absint( $shortcode['id'] );

        if ( ! $shortcode_id ) {
            continue;
        }

        $title = isset( $shortcode['shortcode_name'] ) ? trim( (string) $shortcode['shortcode_name'] ) : '';

        if ( '' === $title ) {
            $title = sprintf(
                /* translators: %d: shortcode ID */
                __( 'Shortcode #%d', 'the-events-calendar-addon2' ),
                $shortcode_id
            );
        }

        $options[ $shortcode_id ] = $title;
    }

    return $options;
}

/**
 * Get saved TECA shortcodes as title => ID pairs for WPBakery dropdowns.
 *
 * @return array<string, string>
 */
function teca_get_saved_shortcodes_for_wpbakery() {
    $options = [];

    foreach ( teca_get_saved_shortcodes_for_builder() as $shortcode_id => $title ) {
        $options[ $title ] = (string) absint( $shortcode_id );
    }

    return $options;
}

/**
 * Get saved TECA shortcodes as title => ID pairs for tagDiv dropdowns.
 *
 * @return array<string|int, int|string>
 */
function teca_get_saved_shortcodes_for_tagdiv() {
    $shortcodes = get_shortcodes();

    if ( empty( $shortcodes ) ) {
        return [];
    }

    return wp_list_pluck( $shortcodes, 'id', 'shortcode_name' );
}

/**
 * Render a saved TECA shortcode by ID.
 *
 * @param int $shortcode_id Shortcode ID.
 * @return string
 */
function teca_render_saved_shortcode( $shortcode_id ) {
    $shortcode_id = absint( $shortcode_id );

    if ( ! $shortcode_id || ! teca_shortcode_exists( $shortcode_id ) ) {
        return '';
    }

    return do_shortcode( sprintf( '[gs-teca id="%d"]', $shortcode_id ) );
}

/**
 * Get saved TECA shortcodes for Oxygen dropdown controls.
 *
 * @return array<int|string, string>
 */
function teca_get_saved_shortcodes_for_oxygen() {
    $shortcodes = get_shortcodes();

    if ( empty( $shortcodes ) ) {
        return [];
    }

    return wp_list_pluck( $shortcodes, 'shortcode_name', 'id' );
}

/**
 * Get saved TECA shortcodes as ID => title pairs for UX Builder dropdowns.
 *
 * @return array<string, string>
 */
function teca_get_saved_shortcodes_for_ux_builder() {
    $options = [];

    foreach ( teca_get_saved_shortcodes_for_builder() as $shortcode_id => $title ) {
        $options[ (string) absint( $shortcode_id ) ] = $title;
    }

    if ( empty( $options ) ) {
        return [
            '' => esc_html__( 'No TECA shortcode found', 'the-events-calendar-addon2' ),
        ];
    }

    return $options;
}

/**
 * Get saved TECA shortcodes as ID => title pairs for Beaver Builder dropdowns.
 *
 * @return array<int|string, string>
 */
function teca_get_saved_shortcodes_for_beaver() {
    $options = teca_get_saved_shortcodes_for_builder();

    if ( empty( $options ) ) {
        return [
            '' => esc_html__( 'No TECA shortcode found', 'the-events-calendar-addon2' ),
        ];
    }

    return $options;
}

/**
 * Purge stale per-page asset caches when dependency rules change.
 */
function gs_teca_maybe_purge_assets_cache() {
    $assets_cache_version = '20';

    if ( get_option( 'gsteca_assets_cache_version' ) === $assets_cache_version ) {
        return;
    }

    if ( function_exists( 'GS_TECA\gsTecaAssetGenerator' ) ) {
        gsTecaAssetGenerator()->assets_purge_all();
    }

    update_option( 'gsteca_assets_cache_version', $assets_cache_version );
}

function get_shortcode_settings($id, $is_preview = false) {

    $default_settings = array_merge( ['id' => $id, 'is_preview' => $is_preview], plugin()->builder->get_shortcode_default_settings() );

    if ( $is_preview ) {
        $preview_settings = plugin()->builder->validate_shortcode_settings( get_transient($id) );
        return teca_prepare_shortcode_settings_for_use(
            teca_prepare_popup_detail_settings(
                teca_prepare_color_settings(
                    teca_prepare_typography_settings( shortcode_atts( $default_settings, $preview_settings ) )
                )
            )
        );
    }

    $shortcode = plugin()->builder->_get_shortcode($id);
    
    if ( empty($shortcode) ) return false;

    return teca_prepare_shortcode_settings_for_use(
        teca_prepare_popup_detail_settings(
            teca_prepare_color_settings(
                teca_prepare_typography_settings( shortcode_atts( $default_settings, (array) $shortcode['shortcode_settings'] ) )
            )
        )
    );
}

function teca_prepare_shortcode_settings_for_use( array $settings ) {
    if ( isset( $settings['gs_teca_template'] ) ) {
        $settings['gs_teca_template'] = teca_resolve_theme_template_for_context( $settings['gs_teca_template'] );
    }

    if ( isset( $settings['view_type'] ) ) {
        $settings['view_type'] = teca_resolve_view_type_for_context( $settings['view_type'] );
    }

    if ( isset( $settings['calendar_layout'] ) ) {
        $settings['calendar_layout'] = teca_resolve_calendar_layout_for_context( $settings['calendar_layout'] );
    }

    if ( isset( $settings['popup_style'] ) ) {
        $settings['popup_style'] = teca_resolve_popup_style_for_context( $settings['popup_style'] );
    }

    if ( isset( $settings['pagination_type'] ) ) {
        $settings['pagination_type'] = teca_resolve_pagination_type_for_context( $settings['pagination_type'] );
    }

    if ( isset( $settings['orderby'] ) ) {
        $settings['orderby'] = teca_resolve_orderby_for_context( $settings['orderby'] );
    }

    if ( isset( $settings['cat_order_by'] ) ) {
        $settings['cat_order_by'] = teca_resolve_cat_order_by_for_context( $settings['cat_order_by'] );
    }

    return teca_sanitize_filters_and_search_by_settings( $settings );
}

/**
 * Pagination type slugs available without Pro.
 *
 * @return string[]
 */
function teca_get_free_pagination_type_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_free_pagination_type_slugs',
        array(
            'normal-pagination',
        )
    );
}

/**
 * All pagination type slugs.
 *
 * @return string[]
 */
function teca_get_all_pagination_type_slugs() {
    return array(
        'normal-pagination',
        'ajax-pagination',
        'load-more-button',
        'load-more-scroll',
    );
}

/**
 * Default free pagination type slug.
 *
 * @return string
 */
function teca_get_default_free_pagination_type() {
    return 'normal-pagination';
}

/**
 * Whether a pagination type slug is free.
 *
 * @param string $pagination_type Pagination type slug.
 * @return bool
 */
function teca_is_free_pagination_type( $pagination_type ) {
    return in_array( (string) $pagination_type, teca_get_free_pagination_type_slugs(), true );
}

/**
 * Resolve a pagination type for the current Pro/free context.
 *
 * @param string $pagination_type Pagination type slug.
 * @return string
 */
function teca_resolve_pagination_type_for_context( $pagination_type ) {
    $pagination_type = sanitize_key( (string) $pagination_type );

    if ( '' === $pagination_type || ! in_array( $pagination_type, teca_get_all_pagination_type_slugs(), true ) ) {
        return teca_get_default_free_pagination_type();
    }

    if ( is_pro_active_and_valid() || teca_is_free_pagination_type( $pagination_type ) ) {
        return $pagination_type;
    }

    return teca_get_default_free_pagination_type();
}

/**
 * Sanitize saved pagination type setting against free/pro availability.
 *
 * @param string $pagination_type Pagination type slug.
 * @return string
 */
function teca_sanitize_pagination_type_setting( $pagination_type ) {
    return teca_resolve_pagination_type_for_context( $pagination_type );
}

/**
 * Pro-only Order By slugs.
 *
 * @return string[]
 */
function teca_get_pro_orderby_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_pro_orderby_slugs',
        array(
            'menu_order',
        )
    );
}

/**
 * Pro-only Category Order By slugs.
 *
 * @return string[]
 */
function teca_get_pro_cat_order_by_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_pro_cat_order_by_slugs',
        array(
            'term_order',
        )
    );
}

/**
 * All Order By slugs.
 *
 * @return string[]
 */
function teca_get_all_orderby_slugs() {
    return array(
        'ID',
        'title',
        'date',
        'rand',
        'menu_order',
    );
}

/**
 * All Category Order By slugs.
 *
 * @return string[]
 */
function teca_get_all_cat_order_by_slugs() {
    return array(
        'none',
        'id',
        'name',
        'term_order',
    );
}

/**
 * Default free Order By slug.
 *
 * @return string
 */
function teca_get_default_free_orderby() {
    return 'date';
}

/**
 * Default free Category Order By slug.
 *
 * @return string
 */
function teca_get_default_free_cat_order_by() {
    return 'none';
}

/**
 * Resolve Order By for the current Pro/free context.
 *
 * @param string $orderby Order By slug.
 * @return string
 */
function teca_resolve_orderby_for_context( $orderby ) {
    $orderby = is_string( $orderby ) ? trim( $orderby ) : '';

    if ( '' === $orderby || ! in_array( $orderby, teca_get_all_orderby_slugs(), true ) ) {
        return teca_get_default_free_orderby();
    }

    if ( is_pro_active_and_valid() || ! in_array( $orderby, teca_get_pro_orderby_slugs(), true ) ) {
        return $orderby;
    }

    return teca_get_default_free_orderby();
}

/**
 * Resolve Category Order By for the current Pro/free context.
 *
 * @param string $cat_order_by Category Order By slug.
 * @return string
 */
function teca_resolve_cat_order_by_for_context( $cat_order_by ) {
    $cat_order_by = is_string( $cat_order_by ) ? trim( $cat_order_by ) : '';

    if ( '' === $cat_order_by || ! in_array( $cat_order_by, teca_get_all_cat_order_by_slugs(), true ) ) {
        return teca_get_default_free_cat_order_by();
    }

    if ( is_pro_active_and_valid() || ! in_array( $cat_order_by, teca_get_pro_cat_order_by_slugs(), true ) ) {
        return $cat_order_by;
    }

    return teca_get_default_free_cat_order_by();
}

/**
 * Sanitize saved Order By setting against free/pro availability.
 *
 * @param string $orderby Order By slug.
 * @return string
 */
function teca_sanitize_orderby_setting( $orderby ) {
    return teca_resolve_orderby_for_context( $orderby );
}

/**
 * Sanitize saved Category Order By setting against free/pro availability.
 *
 * @param string $cat_order_by Category Order By slug.
 * @return string
 */
function teca_sanitize_cat_order_by_setting( $cat_order_by ) {
    return teca_resolve_cat_order_by_for_context( $cat_order_by );
}

/**
 * Sanitize Query tab order settings for the current license context.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_query_order_settings( array $settings ) {
    if ( isset( $settings['orderby'] ) ) {
        $settings['orderby'] = teca_sanitize_orderby_setting( $settings['orderby'] );
    }

    if ( isset( $settings['cat_order_by'] ) ) {
        $settings['cat_order_by'] = teca_sanitize_cat_order_by_setting( $settings['cat_order_by'] );
    }

    return $settings;
}

function get_col_classes( $desktop = '3', $tablet = '4', $mobile_portrait = '6', $mobile = '12' ) {
    return sprintf( 'gs-col-lg-%s gs-col-md-%s gs-col-sm-%s gs-col-xs-%s', $desktop, $tablet, $mobile_portrait, $mobile );
}

function gs_cols_to_number($cols) {
    return (12 / (float) str_replace('_', '.', $cols));
}

/**
 * Style 2 column counts per breakpoint (1–6 columns).
 *
 * @param array $settings Shortcode settings.
 * @return int[] Keys: xs, sm, md, lg.
 */
function teca_get_style_2_column_counts( array $settings ) {
	$map = array(
		'xs' => 'columns_mobile',
		'sm' => 'columns_mobile_portrait',
		'md' => 'columns_tablet',
		'lg' => 'columns',
	);

	$defaults = array(
		'columns'                 => '4',
		'columns_tablet'          => '4',
		'columns_mobile_portrait' => '6',
		'columns_mobile'          => '12',
	);

	$counts = array();

	foreach ( $map as $breakpoint => $setting_key ) {
		$cols_value           = (string) ( $settings[ $setting_key ] ?? $defaults[ $setting_key ] );
		$counts[ $breakpoint ] = (int) round( gs_cols_to_number( $cols_value ) );
		$counts[ $breakpoint ] = max( 1, min( 6, $counts[ $breakpoint ] ) );
	}

	return $counts;
}

/**
 * Style 2 grid column classes for responsive CSS (1–6 columns per breakpoint).
 *
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_style_2_column_body_classes( array $settings ) {
	$counts  = teca_get_style_2_column_counts( $settings );
	$classes = array( 'teca-grid-style-2' );

	foreach ( $counts as $breakpoint => $cols ) {
		$classes[] = 'teca-s2-cols-' . $breakpoint . '-' . $cols;

		if ( 'lg' === $breakpoint ) {
			$classes[] = 'teca-columns-' . $cols;
		}
	}

	return $classes;
}

/**
 * Inline CSS custom properties for Style 2 grid column counts.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_style_2_column_css_vars( array $settings ) {
	$counts = teca_get_style_2_column_counts( $settings );
	$parts  = array();

	foreach ( $counts as $breakpoint => $cols ) {
		$parts[] = '--teca-s2-cols-' . $breakpoint . ':' . $cols;
	}

	return implode( ';', $parts ) . ';';
}

/**
 * Style 3 grid column classes for responsive CSS (1–6 columns per breakpoint).
 *
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_style_3_column_body_classes( array $settings ) {
	$counts  = teca_get_style_2_column_counts( $settings );
	$classes = array( 'teca-grid-style-3' );

	foreach ( $counts as $breakpoint => $cols ) {
		$classes[] = 'teca-g3-cols-' . $breakpoint . '-' . $cols;

		if ( 'lg' === $breakpoint ) {
			$classes[] = 'teca-g3-columns-' . $cols;
		}
	}

	return $classes;
}

/**
 * Inline CSS custom properties for Style 3 grid column counts.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_style_3_column_css_vars( array $settings ) {
	$counts = teca_get_style_2_column_counts( $settings );
	$parts  = array();

	foreach ( $counts as $breakpoint => $cols ) {
		$parts[] = '--teca-g3-cols-' . $breakpoint . ':' . $cols;
	}

	return implode( ';', $parts ) . ';';
}

/**
 * Style 1 grid column classes for responsive CSS (1–6 columns per breakpoint).
 *
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_style_1_column_body_classes( array $settings ) {
	$counts  = teca_get_style_2_column_counts( $settings );
	$classes = array( 'teca-grid-style-1' );

	foreach ( $counts as $breakpoint => $cols ) {
		$classes[] = 'teca-g1-cols-' . $breakpoint . '-' . $cols;

		if ( 'lg' === $breakpoint ) {
			$classes[] = 'teca-g1-columns-' . $cols;
		}
	}

	return $classes;
}

/**
 * Inline CSS custom properties for Style 1 grid column counts.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_style_1_column_css_vars( array $settings ) {
	$counts = teca_get_style_2_column_counts( $settings );
	$parts  = array();

	foreach ( $counts as $breakpoint => $cols ) {
		$parts[] = '--teca-g1-cols-' . $breakpoint . ':' . $cols;
	}

	return implode( ';', $parts ) . ';';
}

/**
 * Style 7 grid column classes for responsive CSS (1–6 columns per breakpoint).
 *
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_style_7_column_body_classes( array $settings ) {
	$counts  = teca_get_style_2_column_counts( $settings );
	$classes = array( 'teca-grid-style-7' );

	foreach ( $counts as $breakpoint => $cols ) {
		$classes[] = 'teca-g7-cols-' . $breakpoint . '-' . $cols;

		if ( 'lg' === $breakpoint ) {
			$classes[] = 'teca-g7-columns-' . $cols;
		}
	}

	return $classes;
}

/**
 * Inline CSS custom properties for Style 7 grid column counts.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_style_7_column_css_vars( array $settings ) {
	$counts = teca_get_style_2_column_counts( $settings );
	$parts  = array();

	foreach ( $counts as $breakpoint => $cols ) {
		$parts[] = '--teca-g7-cols-' . $breakpoint . ':' . $cols;
	}

	return implode( ';', $parts ) . ';';
}

/**
 * Style 8 grid column classes for responsive CSS (1–6 columns per breakpoint).
 *
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_style_8_column_body_classes( array $settings ) {
	$counts  = teca_get_style_2_column_counts( $settings );
	$classes = array( 'teca-grid-style-8' );

	foreach ( $counts as $breakpoint => $cols ) {
		$classes[] = 'teca-g8-cols-' . $breakpoint . '-' . $cols;

		if ( 'lg' === $breakpoint ) {
			$classes[] = 'teca-g8-columns-' . $cols;
		}
	}

	return $classes;
}

/**
 * Inline CSS custom properties for Style 8 grid column counts.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_style_8_column_css_vars( array $settings ) {
	$counts = teca_get_style_2_column_counts( $settings );
	$parts  = array();

	foreach ( $counts as $breakpoint => $cols ) {
		$parts[] = '--teca-g8-cols-' . $breakpoint . ':' . $cols;
	}

	return implode( ';', $parts ) . ';';
}

/**
 * Style 9 grid column classes for responsive CSS (1–6 columns per breakpoint).
 *
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_style_9_column_body_classes( array $settings ) {
	$counts  = teca_get_style_2_column_counts( $settings );
	$classes = array( 'teca-grid-style-9' );

	foreach ( $counts as $breakpoint => $cols ) {
		$classes[] = 'teca-g9-cols-' . $breakpoint . '-' . $cols;

		if ( 'lg' === $breakpoint ) {
			$classes[] = 'teca-g9-columns-' . $cols;
		}
	}

	return $classes;
}

/**
 * Inline CSS custom properties for Style 9 grid column counts.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_style_9_column_css_vars( array $settings ) {
	$counts = teca_get_style_2_column_counts( $settings );
	$parts  = array();

	foreach ( $counts as $breakpoint => $cols ) {
		$parts[] = '--teca-g9-cols-' . $breakpoint . ':' . $cols;
	}

	return implode( ';', $parts ) . ';';
}

/**
 * Style 10 grid column classes for responsive CSS (1–6 columns per breakpoint).
 *
 * @param array $settings Shortcode settings.
 * @return string[]
 */
function teca_get_style_10_column_body_classes( array $settings ) {
	$counts  = teca_get_style_2_column_counts( $settings );
	$classes = array( 'teca-grid-style-10' );

	foreach ( $counts as $breakpoint => $cols ) {
		$classes[] = 'teca-g10-cols-' . $breakpoint . '-' . $cols;

		if ( 'lg' === $breakpoint ) {
			$classes[] = 'teca-g10-columns-' . $cols;
		}
	}

	return $classes;
}

/**
 * Inline CSS custom properties for Style 10 grid column counts.
 *
 * @param array $settings Shortcode settings.
 * @return string
 */
function teca_get_style_10_column_css_vars( array $settings ) {
	$counts = teca_get_style_2_column_counts( $settings );
	$parts  = array();

	foreach ( $counts as $breakpoint => $cols ) {
		$parts[] = '--teca-g10-cols-' . $breakpoint . ':' . $cols;
	}

	return implode( ';', $parts ) . ';';
}

function gs_echo_return($content, $echo = false) {

    if ($echo) {
        echo wp_kses_post(gs_wp_kses($content));
    } else {
        return $content;
    }
}

function minimize_css_simple($css) {
    // https://datayze.com/howto/minify-css-with-php
    $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
    $css = preg_replace('/\s{2,}/', ' ', $css);
    $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return $css;
}

function gs_wp_kses($content) {

    $allowed_tags = wp_kses_allowed_html('post');

    $input_common_atts = ['class' => true, 'id' => true, 'style' => true, 'novalidate' => true, 'name' => true, 'width' => true, 'height' => true, 'data' => true, 'title' => true, 'placeholder' => true, 'value' => true];

    $allowed_tags = array_merge_recursive($allowed_tags, [
        'select' => $input_common_atts,
        'input' => array_merge($input_common_atts, ['type' => true, 'checked' => true]),
        'option' => ['class' => true, 'id' => true, 'selected' => true, 'data' => true, 'value' => true]
    ]);

    return wp_kses(stripslashes_deep($content), $allowed_tags);
}

function gs_allowed_tags($tags) {
    return $tags;
}

function gs_validate_boolean( $var ) {

    if (empty($var)) return false;

    if (gettype($var) == 'string' && strtolower($var) == 'on') return true;
    if (gettype($var) == 'string' && strtolower($var) == 'off') return false;

    return wp_validate_boolean($var);
}

function disable_pro_items( $free_items, $pro_items ) {
    return $free_items;
}

/**
 * Theme template slugs available without Pro.
 *
 * @return string[]
 */
function teca_get_free_theme_template_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_free_theme_template_slugs',
        array(
            'gs-teca-style-1',
            'gs-teca-style-2',
            'gs-teca-style-3',
            'gs-teca-list-style-1',
            'gs-teca-table-style-1',
        )
    );
}

/**
 * Default free theme template slug.
 *
 * @return string
 */
function teca_get_default_free_theme_template() {
    return 'gs-teca-style-1';
}

/**
 * Whether a theme template slug is free.
 *
 * @param string $template Theme template slug.
 * @return bool
 */
function teca_is_free_theme_template( $template ) {
    return in_array( (string) $template, teca_get_free_theme_template_slugs(), true );
}

/**
 * Resolve a theme template for the current Pro/free context.
 *
 * @param string $template Theme template slug.
 * @return string
 */
function teca_resolve_theme_template_for_context( $template ) {
    $template = sanitize_text_field( (string) $template );

    if ( '' === $template ) {
        return teca_get_default_free_theme_template();
    }

    if ( is_pro_active_and_valid() || teca_is_free_theme_template( $template ) ) {
        return $template;
    }

    return teca_get_default_free_theme_template();
}

/**
 * Sanitize saved theme template setting against free/pro availability.
 *
 * @param string $template Theme template slug.
 * @return string
 */
function teca_sanitize_theme_template_setting( $template ) {
    return teca_resolve_theme_template_for_context( $template );
}

/**
 * View type slugs available without Pro.
 *
 * @return string[]
 */
function teca_get_free_view_type_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_free_view_type_slugs',
        array(
            'grid',
            'masonry',
            'calendar',
            'carousel',
        )
    );
}

/**
 * Default free view type slug.
 *
 * @return string
 */
function teca_get_default_free_view_type() {
    return 'grid';
}

/**
 * Whether a view type slug is free.
 *
 * @param string $view_type View type slug.
 * @return bool
 */
function teca_is_free_view_type( $view_type ) {
    return in_array( (string) $view_type, teca_get_free_view_type_slugs(), true );
}

/**
 * Resolve a view type for the current Pro/free context.
 *
 * @param string $view_type View type slug.
 * @return string
 */
function teca_resolve_view_type_for_context( $view_type ) {
    $view_type = sanitize_key( (string) $view_type );

    if ( '' === $view_type ) {
        return teca_get_default_free_view_type();
    }

    if ( is_pro_active_and_valid() || teca_is_free_view_type( $view_type ) ) {
        return $view_type;
    }

    return teca_get_default_free_view_type();
}

/**
 * Sanitize saved view type setting against free/pro availability.
 *
 * @param string $view_type View type slug.
 * @return string
 */
function teca_sanitize_view_type_setting( $view_type ) {
    return teca_resolve_view_type_for_context( $view_type );
}

/**
 * Popup style slugs available without Pro.
 *
 * @return string[]
 */
function teca_get_free_popup_style_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_free_popup_style_slugs',
        array(
            'default',
        )
    );
}

/**
 * Popup style slugs exposed in the admin selector.
 *
 * @return string[]
 */
function teca_get_admin_popup_style_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_admin_popup_style_slugs',
        array(
            'default',
            'style-one',
            'style-two',
        )
    );
}

/**
 * Popup style slugs removed from the admin selector.
 *
 * @return string[]
 */
function teca_get_removed_popup_style_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_removed_popup_style_slugs',
        array(
            'style-three',
            'style-four',
            'style-five',
            'style-six',
        )
    );
}

/**
 * Default free popup style slug.
 *
 * @return string
 */
function teca_get_default_free_popup_style() {
    return 'default';
}

/**
 * Whether a popup style slug is free.
 *
 * @param string $popup_style Popup style slug.
 * @return bool
 */
function teca_is_free_popup_style( $popup_style ) {
    return in_array( (string) $popup_style, teca_get_free_popup_style_slugs(), true );
}

/**
 * Resolve a popup style for the current Pro/free context.
 *
 * @param string $popup_style Popup style slug.
 * @return string
 */
function teca_resolve_popup_style_for_context( $popup_style ) {
    $popup_style = sanitize_key( (string) $popup_style );

    if ( '' === $popup_style ) {
        return teca_get_default_free_popup_style();
    }

    if ( in_array( $popup_style, teca_get_removed_popup_style_slugs(), true ) ) {
        return teca_get_default_free_popup_style();
    }

    if ( ! in_array( $popup_style, teca_get_admin_popup_style_slugs(), true ) ) {
        return teca_get_default_free_popup_style();
    }

    if ( is_pro_active_and_valid() || teca_is_free_popup_style( $popup_style ) ) {
        return $popup_style;
    }

    return teca_get_default_free_popup_style();
}

/**
 * Sanitize saved popup style setting against free/pro availability.
 *
 * @param string $popup_style Popup style slug.
 * @return string
 */
function teca_sanitize_popup_style_setting( $popup_style ) {
    return teca_resolve_popup_style_for_context( $popup_style );
}

/**
 * Single page style slugs available without Pro.
 *
 * @return string[]
 */
function teca_get_free_single_page_style_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_free_single_page_style_slugs',
        array(
            'default',
        )
    );
}

/**
 * Single page style slugs exposed in the admin selector.
 *
 * @return string[]
 */
function teca_get_admin_single_page_style_slugs() {
    return apply_filters(
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        'gs_teca_admin_single_page_style_slugs',
        array(
            'default',
            'style-one',
            'style-two',
            'style-three',
            'style-four',
            'style-five',
        )
    );
}

/**
 * Default free single page style slug.
 *
 * @return string
 */
function teca_get_default_free_single_page_style() {
    return 'default';
}

/**
 * Whether a normalized single page style key is free.
 *
 * @param string $style_key Normalized style key.
 * @return bool
 */
function teca_is_free_single_page_style_key( $style_key ) {
    return 'default' === teca_normalize_single_page_style_key( $style_key );
}

/**
 * Resolve a normalized single page style key for the current Pro/free context.
 *
 * @param string $style_key Normalized style key.
 * @return string
 */
function teca_resolve_single_page_style_key_for_context( $style_key ) {
    $style_key = teca_normalize_single_page_style_key( $style_key );

    if ( 'default' === $style_key ) {
        return 'default';
    }

    if ( is_pro_active_and_valid() ) {
        return $style_key;
    }

    return teca_get_default_free_single_page_style();
}

/**
 * Map a normalized single page style key to the admin option slug.
 *
 * @param string $style_key Normalized style key.
 * @return string
 */
function teca_get_single_page_style_admin_slug( $style_key ) {
    $style_key = teca_normalize_single_page_style_key( $style_key );

    $map = array(
        'default' => 'default',
        'style-1' => 'style-one',
        'style-2' => 'style-two',
        'style-3' => 'style-three',
        'style-4' => 'style-four',
        'style-5' => 'style-five',
    );

    return $map[ $style_key ] ?? 'default';
}

/**
 * Sanitize saved single page style setting against free/pro availability.
 *
 * @param mixed $single_page_style Single page style value.
 * @return string
 */
function teca_sanitize_single_page_style_setting( $single_page_style ) {
    $style_key = teca_resolve_single_page_style_key_for_context(
        teca_normalize_single_page_style_key( $single_page_style )
    );

    return teca_get_single_page_style_admin_slug( $style_key );
}

/**
 * Order theme template options by slug list.
 *
 * @param array<int, array{label: string, value: string}> $options  Theme options.
 * @param string[]                                        $slug_order Preferred slug order.
 * @return array<int, array{label: string, value: string}>
 */
function teca_order_theme_template_options( array $options, array $slug_order ) {
    $indexed = array();

    foreach ( $options as $option ) {
        $value = $option['value'] ?? '';

        if ( '' !== $value ) {
            $indexed[ $value ] = $option;
        }
    }

    $ordered = array();

    foreach ( $slug_order as $slug ) {
        if ( isset( $indexed[ $slug ] ) ) {
            $ordered[] = $indexed[ $slug ];
            unset( $indexed[ $slug ] );
        }
    }

    foreach ( $indexed as $option ) {
        $ordered[] = $option;
    }

    return $ordered;
}

function apply_pro_guards( array $options, array $values, bool $reverse = false ): array {
    $checker = $reverse
        ? static fn( $val ) => in_array( $val, $values, true )
        : static fn( $val ) => ! in_array( $val, $values, true );

    return array_values(
        array_filter(
            $options,
            static function( $item ) use ( $checker ) {
                return $checker( $item['value'] ?? null );
            }
        )
    );
}


function get_pagination( $shortcode_id, $items_per_page = 6,$found_events = 0 ) {

    // Generate page parameter name
    $param_name = 'paged' . $shortcode_id;
    
    // Current Page Number — public pagination query var; sanitized on read.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public pagination parameter for frontend shortcodes.
    $current = max( 1, isset( $_GET[ $param_name ] ) ? absint( wp_unslash( $_GET[ $param_name ] ) ) : 1 );

    // Calculate total pages
    $total_pages = $items_per_page > 0
        ? ceil( $found_events / $items_per_page )
        : 1;

    // Generate the current URL with the page placeholder
    $current_url = get_current_full_url();
    $current_url = remove_query_arg( $param_name, $current_url );
    $current_url = add_query_arg( $param_name, '%#%', $current_url );
    
    // Print the pagination links
    $pagination = "<div class='gs-teca-pagination'>";
    $pagination .= paginate_links( array(
        'base' => $current_url,
        'current' => $current,
        'total' => $total_pages,
        'prev_next' => true,
        'next_text' => '<i class="fa fa-angle-right"></i>',
        'prev_text' => '<i class="fa fa-angle-left"></i>',
    ));
    $pagination .= "</div>";

    return $pagination;
}

function get_current_full_url() {
    $protocol = is_ssl() ? 'https://' : 'http://';
    $host     = isset( $_SERVER['HTTP_HOST'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) )
        : '';
    $request  = isset( $_SERVER['REQUEST_URI'] )
        ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
        : '';

    return $protocol . $host . $request;
}

function get_ajax_pagination( $shortcode_id, $items_per_page = 6, $paged = 1, $found_events = 0 ) {
    $param_name = 'paged' . $shortcode_id;

    $current = max( 1, $paged ?? 1 );

    // 🔥 dynamic total pages
    $total_pages = $items_per_page > 0
        ? ceil( $found_events / $items_per_page )
        : 1;

    $current_url = get_current_full_url();
    $current_url = remove_query_arg( $param_name, $current_url );
    $current_url = add_query_arg( $param_name, '%#%', $current_url );

    $pagination = "<div class='gs-teca-pagination gs-teca-ajax-pagination-link'>";
    $pagination .= paginate_links( array(
        'base'      => $current_url,
        'current'   => $current,
        'total'     => $total_pages,
        'prev_next' => true,
        'next_text' => '<i class="fa fa-angle-right"></i>',
        'prev_text' => '<i class="fa fa-angle-left"></i>',
    ));
    $pagination .= "</div>";

    return $pagination;
}


function getoption($option, $default = '') {
    $prefs = plugin()->builder->_get_shortcode_pref( false );
    return isset($prefs[$option]) ? $prefs[$option] : $default;
}

function getlayoutoption($option, $default = '') {
    $layout = teca_get_single_page_layout_settings();
    if ( ! isset( $layout[ $option ] ) ) {
        return $default;
    }

    return teca_extract_single_page_setting_value( $layout[ $option ], $default );
}

function gs_teca_get_item_terms_slugs( $event_id, $taxonomy, $separator = ' ' ) {

	if ( empty( $event_id ) || empty( $taxonomy ) ) {
		return '';
	}

	$terms = get_the_terms( $event_id, $taxonomy );

	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		return implode( $separator, wp_list_pluck( $terms, 'slug' ) );
	}

	return '';
}

function gs_teca_get_the_term_classes( $event_id, $view_type, $gs_filters_by ) {

	// only for filter view
	if ( $view_type !== 'filter' ) {
		return '';
	}

	// Event Categories
	if ( $gs_filters_by === 'gs-teca-category' ) {
		return gs_teca_get_item_terms_slugs( $event_id, 'tribe_events_cat', ' ' );
	}

	// Event Tags (WordPress post_tag)
	if ( $gs_filters_by === 'gs-teca-tag' ) {
		return gs_teca_get_item_terms_slugs( $event_id, 'post_tag', ' ' );
	}

	return '';
}

function is_display_pagination( $carousel_enabled, $filter_enabled, $filter_type ) {

    if( $carousel_enabled === 'on' ) {
        return false;
    }
    
    if( 'on' === $filter_enabled && $filter_type === 'normal-filter' ){
        return false;
    }

    return true;
    
}

function get_translation($translation_name) {
    return plugin()->builder->get_translation($translation_name);
}

function get_popup_media($post_id) {

    $event_id = 0;

    if ( isset( $event['event_id'] ) && $event['event_id'] > 0 ) {
        $event_id = (int) $event['event_id'];
    }

    if ( ! $event_id ) {
        $post = get_post();
        if ( $post instanceof \WP_Post ) {
            $event_id = $post->ID;
        }
    }

    $attachment_id = get_post_thumbnail_id( $event_id );

    $media_options = [
        'video'     => get_post_meta( $event_id, '_video_file', true),
        'audio'     => get_post_meta( $event_id, '_audio_file', true ),
        'gallery'   => get_post_meta( $event_id, '_gallery_ids', true),
        'thumbnail' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true )  
    ];

    $user_choice = get_post_meta($event_id, 'gs_teca_media', true);

    if (isset($media_options[$user_choice]) && !empty($media_options[$user_choice])) {
        return $user_choice;
    }

    foreach ($media_options as $media_type => $data) {
        if (!empty($data)) {
            return $media_type;
        }
    }

    return 'thumbnail';
}

function get_the_popup_link( $shortcode_id, $event_id ) {
    $event_id = 0;

    if ( isset( $event['event_id'] ) && $event['event_id'] > 0 ) {
        $event_id = (int) $event['event_id'];
    }

    if ( ! $event_id ) {
        $post = get_post();
        if ( $post instanceof \WP_Post ) {
            $event_id = $post->ID;
        }
    }
	return sprintf( '#gs_post_popup_%s_%s', esc_attr( $shortcode_id ), esc_attr( $event_id ) );
}

function gs_get_terms( $term_name, array $options = array() ) {

	$options = shortcode_atts([
		'taxonomy'   => $term_name,
		'order'      => 'asc',
		'orderby'    => 'none',
		'include'    => [],
        // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude	
		'exclude'    => [],
		'hide_empty' => false,
        'ignore_term_order' => false,
	], $options );

	return get_terms( $options );
}

function gs_post_select() {

    // if ( ! is_pro_active_and_valid() ) {
    //     return [];
    // }

    $posts = get_posts(array(
        'post_type'                    => 'tribe_events',
        'posts_per_page'               => -1,
        'post_status'                  => 'publish',
        'eventDisplay'                 => 'custom',
        'tribe_suppress_query_filters' => true,
    ));

    $_posts = [];

    foreach ( $posts as $post ) {


        $_posts[] = [
            'label' => substr($post->post_title, 0, 20).'...',
            'value' => $post->ID
        ];
        
    }

    return $_posts;

}

function teca_get_calendar_view_types() {
    return Calendar_Renderer::get_view_types();
}

function teca_is_calendar_view_type( $view_type ) {
    return Calendar_Renderer::is_calendar_view_type( $view_type );
}

function teca_is_events_section_view_type( $view_type ) {
    return Events_Section_Renderer::is_events_section_view_type( $view_type );
}

function teca_get_event_layout_options() {
    return array(
        'event-layout-1',
        'event-layout-2',
        'event-layout-3',
    );
}

function teca_get_selected_event_layout( array $settings ) {
    $default = 'event-layout-1';
    $value   = sanitize_key( (string) ( $settings['event_layout'] ?? $default ) );
    $valid   = teca_get_event_layout_options();

    if ( ! in_array( $value, $valid, true ) ) {
        return $default;
    }

    return $value;
}

function teca_sanitize_events_section_settings( array $shortcode_settings ) {
    $shortcode_settings['event_layout'] = teca_get_selected_event_layout( $shortcode_settings );

    return $shortcode_settings;
}

function teca_get_events_section_data( array $settings, array $ajax_datas = array() ) {
    return Query::get_events_section_data( $settings, $ajax_datas );
}

function teca_get_events_section_wrapper_attributes( array $settings ) {
    $layout     = teca_get_selected_event_layout( $settings );
    $layout_num = str_replace( 'event-layout-', '', $layout );

    $classes = array(
        'teca-events-section',
        'teca-events-layout-' . $layout_num,
    );

    return sprintf(
        'class="%1$s" data-event-layout="%2$s"',
        esc_attr( implode( ' ', $classes ) ),
        esc_attr( $layout )
    );
}

function teca_get_events_section_item_attributes_html( array $event, $event_group ) {
    $html = teca_get_event_filter_attributes_html( $event );

    if ( $event_group ) {
        $html .= sprintf( ' data-event-group="%s"', esc_attr( (string) $event_group ) );
    }

    $categories = array();

    if ( ! empty( $event['categories'] ) && is_array( $event['categories'] ) ) {
        foreach ( $event['categories'] as $category ) {
            if ( ! empty( $category['slug'] ) ) {
                $categories[] = (string) $category['slug'];
            }
        }
    }

    if ( ! empty( $categories ) && false === strpos( $html, 'data-event-categories=' ) ) {
        $html .= sprintf( ' data-event-categories="%s"', esc_attr( implode( ',', $categories ) ) );
    }

    $tags = array();

    if ( ! empty( $event['tags'] ) && is_array( $event['tags'] ) ) {
        foreach ( $event['tags'] as $tag ) {
            if ( ! empty( $tag['slug'] ) ) {
                $tags[] = (string) $tag['slug'];
            }
        }
    }

    if ( ! empty( $tags ) && false === strpos( $html, 'data-event-tags=' ) ) {
        $html .= sprintf( ' data-event-tags="%s"', esc_attr( implode( ',', $tags ) ) );
    }

    return $html;
}

function teca_render_events_section_layout( array $settings, array $ajax_datas = array() ) {
    return Events_Section_Renderer::render_layout( $settings, $ajax_datas );
}

function teca_get_calendar_sub_layout_setting_key( $view_type ) {
    $map = array(
        'daily-calendar'     => 'daily_calendar_layout',
        'weekly-calendar'    => 'weekly_calendar_layout',
        'monthly-calendar'   => 'monthly_calendar_layout',
        'quarterly-calendar' => 'quarterly_calendar_layout',
        'yearly-calendar'    => 'yearly_calendar_layout',
    );

    return $map[ (string) $view_type ] ?? '';
}

function teca_get_default_calendar_sub_layout( $view_type ) {
    $map = array(
        'daily-calendar'     => 'daily-layout-1',
        'weekly-calendar'    => 'weekly-layout-1',
        'monthly-calendar'   => 'monthly-layout-1',
        'quarterly-calendar' => 'quarterly-layout-1',
        'yearly-calendar'    => 'yearly-layout-1',
    );

    return $map[ (string) $view_type ] ?? '';
}

function teca_get_calendar_sub_layout_options( $view_type ) {
    $prefix_map = array(
        'daily-calendar'     => 'daily',
        'weekly-calendar'    => 'weekly',
        'monthly-calendar'   => 'monthly',
        'quarterly-calendar' => 'quarterly',
        'yearly-calendar'    => 'yearly',
    );

    $prefix = $prefix_map[ (string) $view_type ] ?? '';

    if ( '' === $prefix ) {
        return array();
    }

    return array(
        $prefix . '-layout-1',
        $prefix . '-layout-2',
        $prefix . '-layout-3',
    );
}

function teca_get_selected_calendar_sub_layout( array $settings ) {
    $settings = teca_normalize_calendar_settings( $settings );

    return teca_get_calendar_layout_legacy_slug( teca_get_selected_calendar_layout( $settings ) );
}

function teca_get_calendar_layout_class( $view_type, $sub_layout ) {
    $sub_layout = sanitize_text_field( (string) $sub_layout );

    if ( '' === $sub_layout || ! in_array( $sub_layout, teca_get_calendar_sub_layout_options( $view_type ), true ) ) {
        $sub_layout = teca_get_default_calendar_sub_layout( $view_type );
    }

    if ( '' === $sub_layout ) {
        return '';
    }

    return 'teca-' . $sub_layout;
}

function teca_get_calendar_area_layout_class( array $settings ) {
    $settings = teca_normalize_calendar_settings( $settings );

    if ( ! teca_is_calendar_view_type( $settings['view_type'] ?? '' ) ) {
        return '';
    }

    if ( 'calendar-layout-7' === teca_get_selected_calendar_layout( $settings ) ) {
        return 'teca-has-monthly-layout-1';
    }

    return '';
}

function teca_get_events_section_area_layout_class( array $settings ) {
    $view_type = $settings['view_type'] ?? '';

    if ( 'events-section' !== $view_type ) {
        return '';
    }

    if ( 'event-layout-1' === teca_get_selected_event_layout( $settings ) ) {
        return 'teca-has-events-layout-1';
    }

    return '';
}

function teca_get_calendar_view_slug( $view_type ) {
    $map = array(
        'daily-calendar'     => 'daily',
        'weekly-calendar'    => 'weekly',
        'monthly-calendar'   => 'monthly',
        'quarterly-calendar' => 'quarterly',
        'yearly-calendar'    => 'yearly',
    );

    return $map[ (string) $view_type ] ?? '';
}

function teca_sanitize_calendar_sub_layout_settings( array $shortcode_settings ) {
    foreach ( teca_get_calendar_view_types() as $view_type ) {
        if ( 'calendar' === $view_type ) {
            continue;
        }

        $setting_key = teca_get_calendar_sub_layout_setting_key( $view_type );
        $default     = teca_get_default_calendar_sub_layout( $view_type );
        $value       = sanitize_text_field( (string) ( $shortcode_settings[ $setting_key ] ?? $default ) );

        if ( ! in_array( $value, teca_get_calendar_sub_layout_options( $view_type ), true ) ) {
            $value = $default;
        }

        $shortcode_settings[ $setting_key ] = $value;
    }

    $calendar_layout = teca_get_selected_calendar_layout( $shortcode_settings );
    $legacy_slug     = teca_get_calendar_layout_legacy_slug( $calendar_layout );
    $period          = teca_get_calendar_layout_period( $calendar_layout );
    $period_keys     = array(
        'daily'     => 'daily_calendar_layout',
        'weekly'    => 'weekly_calendar_layout',
        'monthly'   => 'monthly_calendar_layout',
        'quarterly' => 'quarterly_calendar_layout',
        'yearly'    => 'yearly_calendar_layout',
    );

    if ( isset( $period_keys[ $period ] ) ) {
        $shortcode_settings[ $period_keys[ $period ] ] = $legacy_slug;
    }

    return $shortcode_settings;
}

function teca_query_events( array $settings, array $ajax_datas = array() ) {
    return plugin()->shortcode->query_events( $settings, $ajax_datas );
}

function teca_get_shortcode_display_title( array $settings ) {
    $id = $settings['id'] ?? '';

    if ( is_numeric( $id ) ) {
        $shortcode = plugin()->builder->_get_shortcode( absint( $id ) );

        if ( ! empty( $shortcode['shortcode_name'] ) ) {
            return $shortcode['shortcode_name'];
        }
    }

    return __( 'Events Schedule', 'the-events-calendar-addon2' );
}

function teca_group_events_by_month( array $events ) {
    $groups = array();

    foreach ( $events as $event ) {
        $start = $event['dates']['start'] ?? '';

        if ( ! $start ) {
            continue;
        }

        $timestamp = strtotime( $start );
        $key       = gmdate( 'Y-m', $timestamp );

        if ( ! isset( $groups[ $key ] ) ) {
            $groups[ $key ] = array(
                'label'  => date_i18n( 'F Y', $timestamp ),
                'month'  => $key,
                'events' => array(),
            );
        }

        $groups[ $key ]['events'][] = $event;
    }

    uksort( $groups, 'strcmp' );

    return $groups;
}

function teca_get_event_date_badge_parts( $event_id ) {
    $start = get_post_meta( (int) $event_id, '_EventStartDate', true );

    if ( ! $start ) {
        return array(
            'month' => '',
            'day'   => '',
        );
    }

    $timestamp = strtotime( $start );

    return array(
        'month' => strtoupper( date_i18n( 'M', $timestamp ) ),
        'day'   => date_i18n( 'j', $timestamp ),
    );
}

function teca_format_event_time_range( $event_id ) {
    $event_id = (int) $event_id;
    $all_day  = (bool) get_post_meta( $event_id, '_EventAllDay', true );

    if ( $all_day ) {
        return __( 'All Day', 'the-events-calendar-addon2' );
    }

    $start = get_post_meta( $event_id, '_EventStartDate', true );
    $end   = get_post_meta( $event_id, '_EventEndDate', true );

    if ( ! $start ) {
        return '';
    }

    $time_format = get_option( 'time_format' );
    $start_time  = date_i18n( $time_format, strtotime( $start ) );
    $end_time    = $end ? date_i18n( $time_format, strtotime( $end ) ) : '';

    if ( $end_time && $end_time !== $start_time ) {
        return $start_time . ' - ' . $end_time;
    }

    return $start_time;
}

function teca_get_event_cta_url( $event_id ) {
    $event_id = (int) $event_id;

    if ( function_exists( 'tribe_get_event_website_url' ) ) {
        $url = tribe_get_event_website_url( $event_id );

        if ( ! empty( $url ) ) {
            return esc_url( $url );
        }
    }

    $url = get_post_meta( $event_id, '_EventURL', true );

    return ! empty( $url ) ? esc_url( $url ) : '';
}

function teca_get_event_primary_category_name( array $event ) {
    $names = teca_get_event_category_names( $event );

    return $names[0] ?? '';
}

function teca_get_event_category_terms( array $event ) {
    $event_id = (int) ( $event['event_id'] ?? 0 );

    if ( ! $event_id ) {
        return array();
    }

    $terms = get_the_terms( $event_id, 'tribe_events_cat' );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return array();
    }

    return array_values( $terms );
}

function teca_get_event_category_names( array $event ) {
    if ( ! empty( $event['categories'] ) && is_array( $event['categories'] ) ) {
        $names = array();

        foreach ( $event['categories'] as $category ) {
            if ( ! empty( $category['name'] ) ) {
                $names[] = (string) $category['name'];
            }
        }

        if ( ! empty( $names ) ) {
            return array_values( array_unique( $names ) );
        }
    }

    $names = array();

    foreach ( teca_get_event_category_terms( $event ) as $term ) {
        if ( ! empty( $term->name ) ) {
            $names[] = (string) $term->name;
        }
    }

    return array_values( array_unique( $names ) );
}

function teca_get_event_category_slugs( array $event ) {
    if ( ! empty( $event['categories'] ) && is_array( $event['categories'] ) ) {
        $slugs = array();

        foreach ( $event['categories'] as $category ) {
            if ( ! empty( $category['slug'] ) ) {
                $slugs[] = (string) $category['slug'];
            }
        }

        if ( ! empty( $slugs ) ) {
            return array_values( array_unique( $slugs ) );
        }
    }

    $slugs = array();

    foreach ( teca_get_event_category_terms( $event ) as $term ) {
        if ( ! empty( $term->slug ) ) {
            $slugs[] = (string) $term->slug;
        }
    }

    return array_values( array_unique( $slugs ) );
}

function teca_get_event_tag_terms( array $event ) {
    $event_id = (int) ( $event['event_id'] ?? 0 );

    if ( ! $event_id ) {
        return array();
    }

    $terms = get_the_terms( $event_id, 'post_tag' );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return array();
    }

    return array_values( $terms );
}

function teca_get_event_tag_names( array $event ) {
    if ( ! empty( $event['tags'] ) && is_array( $event['tags'] ) ) {
        $names = array();

        foreach ( $event['tags'] as $tag ) {
            if ( ! empty( $tag['name'] ) ) {
                $names[] = (string) $tag['name'];
            }
        }

        if ( ! empty( $names ) ) {
            return array_values( array_unique( $names ) );
        }
    }

    $names = array();

    foreach ( teca_get_event_tag_terms( $event ) as $term ) {
        if ( ! empty( $term->name ) ) {
            $names[] = (string) $term->name;
        }
    }

    return array_values( array_unique( $names ) );
}

function teca_get_event_tag_slugs( array $event ) {
    if ( ! empty( $event['tags'] ) && is_array( $event['tags'] ) ) {
        $slugs = array();

        foreach ( $event['tags'] as $tag ) {
            if ( ! empty( $tag['slug'] ) ) {
                $slugs[] = (string) $tag['slug'];
            }
        }

        if ( ! empty( $slugs ) ) {
            return array_values( array_unique( $slugs ) );
        }
    }

    $slugs = array();

    foreach ( teca_get_event_tag_terms( $event ) as $term ) {
        if ( ! empty( $term->slug ) ) {
            $slugs[] = (string) $term->slug;
        }
    }

    return array_values( array_unique( $slugs ) );
}

function teca_render_event_categories( array $args ) {
    $event          = $args['event'] ?? array();
    $wrapper_class  = $args['wrapper_class'] ?? 'teca-event-categories';
    $item_class     = $args['item_class'] ?? 'teca-event-category';
    $transform      = $args['transform'] ?? '';
    $category_names = teca_get_event_category_names( $event );

    if ( empty( $category_names ) ) {
        return;
    }

    echo '<div class="' . esc_attr( $wrapper_class ) . '">';

    foreach ( $category_names as $category_name ) {
        if ( 'uppercase' === $transform ) {
            $category_name = strtoupper( $category_name );
        }

        echo '<span class="' . esc_attr( $item_class ) . '">' . esc_html( $category_name ) . '</span>';
    }

    echo '</div>';
}

function teca_render_event_tags( array $args ) {
    $event         = $args['event'] ?? array();
    $wrapper_class = $args['wrapper_class'] ?? 'teca-event-tags';
    $item_class    = $args['item_class'] ?? 'teca-event-tag';
    $transform     = $args['transform'] ?? '';
    $tag_names     = teca_get_event_tag_names( $event );

    if ( empty( $tag_names ) ) {
        return;
    }

    echo '<div class="' . esc_attr( $wrapper_class ) . '">';

    foreach ( $tag_names as $tag_name ) {
        if ( 'uppercase' === $transform ) {
            $tag_name = strtoupper( $tag_name );
        }

        echo '<span class="' . esc_attr( $item_class ) . '">' . esc_html( $tag_name ) . '</span>';
    }

    echo '</div>';
}

function teca_group_events_by_day( array $events ) {
    $groups = array();

    foreach ( $events as $event ) {
        $start = $event['dates']['start'] ?? '';

        if ( ! $start ) {
            continue;
        }

        $timestamp = strtotime( $start );
        $day_key   = gmdate( 'Y-m-d', $timestamp );

        if ( ! isset( $groups[ $day_key ] ) ) {
            $groups[ $day_key ] = array(
                'day_key'    => $day_key,
                'day_label'  => strtoupper( date_i18n( 'D', $timestamp ) ),
                'day_number' => date_i18n( 'j', $timestamp ),
                'month_key'  => gmdate( 'Y-m', $timestamp ),
                'month_label'=> date_i18n( 'F Y', $timestamp ),
                'events'     => array(),
            );
        }

        $groups[ $day_key ]['events'][] = $event;
    }

    uksort( $groups, 'strcmp' );

    return $groups;
}

function teca_group_day_groups_by_month( array $day_groups ) {
    $months = array();

    foreach ( $day_groups as $day_group ) {
        $month_key = $day_group['month_key'];

        if ( ! isset( $months[ $month_key ] ) ) {
            $months[ $month_key ] = array(
                'month' => $month_key,
                'label' => $day_group['month_label'],
                'days'  => array(),
            );
        }

        $months[ $month_key ]['days'][] = $day_group;
    }

    uksort( $months, 'strcmp' );

    return $months;
}

function teca_sort_events_by_start_date( array $events ) {
    usort(
        $events,
        static function ( $a, $b ) {
            $start_a = $a['dates']['start'] ?? '';
            $start_b = $b['dates']['start'] ?? '';

            return strcmp( $start_a, $start_b );
        }
    );

    return $events;
}

function teca_get_month_infographic_label_parts( $month_key ) {
    $timestamp = strtotime( (string) $month_key . '-01' );

    if ( ! $timestamp ) {
        return array(
            'month_name' => '',
            'year'       => '',
            'label'      => '',
        );
    }

    return array(
        'month_name' => strtoupper( date_i18n( 'F', $timestamp ) ),
        'year'       => date_i18n( 'Y', $timestamp ),
        'label'      => date_i18n( 'F Y', $timestamp ),
    );
}

function teca_get_calendar_weekday_labels( $style = 'abbrev' ) {
    $start_of_week = (int) get_option( 'start_of_week', 0 );
    $labels        = array();
    $format        = 'full' === $style ? 'l' : 'D';
    $sunday_base   = strtotime( '2024-01-07' );

    for ( $i = 0; $i < 7; $i++ ) {
        $day_index   = ( $start_of_week + $i ) % 7;
        $timestamp   = strtotime( '+' . $day_index . ' days', $sunday_base );
        $labels[]    = date_i18n( $format, $timestamp );
    }

    return $labels;
}

function teca_get_monthly_layout_3_accent_slugs() {
    return array( 'teal', 'coral', 'sand', 'mustard', 'sage', 'blue' );
}

function teca_get_monthly_layout_3_accent_slug( $index ) {
    $slugs = teca_get_monthly_layout_3_accent_slugs();

    return $slugs[ (int) $index % count( $slugs ) ];
}

function teca_build_monthly_calendar_cells( $month_key, array $events ) {
    $timestamp = strtotime( (string) $month_key . '-01' );

    if ( ! $timestamp ) {
        return array(
            'weeks'         => array(),
            'events_by_day' => array(),
            'month_label'   => '',
        );
    }

    $year          = (int) gmdate( 'Y', $timestamp );
    $month         = (int) gmdate( 'm', $timestamp );
    $days_in_month = (int) gmdate( 't', $timestamp );
    $start_of_week = (int) get_option( 'start_of_week', 0 );
    $first_weekday = (int) gmdate( 'w', $timestamp );
    $offset        = ( $first_weekday - $start_of_week + 7 ) % 7;
    $events_by_day = array();

    foreach ( $events as $event ) {
        $start = $event['dates']['start'] ?? '';

        if ( ! $start ) {
            continue;
        }

        $day_key = gmdate( 'Y-m-d', strtotime( $start ) );

        if ( substr( $day_key, 0, 7 ) !== $month_key ) {
            continue;
        }

        if ( ! isset( $events_by_day[ $day_key ] ) ) {
            $events_by_day[ $day_key ] = array();
        }

        $events_by_day[ $day_key ][] = $event;
    }

    foreach ( $events_by_day as $day_key => $day_events ) {
        $events_by_day[ $day_key ] = teca_sort_events_by_start_date( $day_events );
    }

    $cells = array();

    for ( $i = 0; $i < $offset; $i++ ) {
        $cells[] = array(
            'type' => 'empty',
        );
    }

    for ( $day = 1; $day <= $days_in_month; $day++ ) {
        $date = sprintf( '%s-%02d', $month_key, $day );

        $cells[] = array(
            'type'   => 'day',
            'date'   => $date,
            'day'    => $day,
            'month'  => $month,
            'year'   => $year,
            'events' => $events_by_day[ $date ] ?? array(),
        );
    }

    while ( count( $cells ) % 7 !== 0 ) {
        $cells[] = array(
            'type' => 'empty',
        );
    }

    return array(
        'weeks'         => array_chunk( $cells, 7 ),
        'events_by_day' => $events_by_day,
        'month_label'   => date_i18n( 'F Y', $timestamp ),
    );
}

function teca_group_events_by_week( array $events ) {
    $weeks = array();

    foreach ( $events as $event ) {
        $start = $event['dates']['start'] ?? '';

        if ( ! $start ) {
            continue;
        }

        $range = teca_get_week_range_for_date( gmdate( 'Y-m-d', strtotime( $start ) ) );
        $key   = $range['start'];

        if ( ! isset( $weeks[ $key ] ) ) {
            $weeks[ $key ] = array(
                'start'  => $range['start'],
                'end'    => $range['end'],
                'label'  => $range['label'],
                'events' => array(),
            );
        }

        $weeks[ $key ]['events'][] = $event;
    }

    uksort( $weeks, 'strcmp' );

    return $weeks;
}

function teca_build_week_day_slots( array $events, $week_start ) {
    $week_start_ts = strtotime( (string) $week_start );
    $slots         = array();

    if ( ! $week_start_ts ) {
        return $slots;
    }

    for ( $i = 0; $i < 7; $i++ ) {
        $day_ts      = strtotime( '+' . $i . ' days', $week_start_ts );
        $day_key     = wp_date( 'Y-m-d', $day_ts );
        $day_of_week = (int) wp_date( 'w', $day_ts );
        $day_events  = array();

        foreach ( $events as $event ) {
            $event_start = $event['dates']['start'] ?? '';

            if ( ! $event_start ) {
                continue;
            }

            if ( gmdate( 'Y-m-d', strtotime( $event_start ) ) === $day_key ) {
                $day_events[] = $event;
            }
        }

        $slots[] = array(
            'day_key'       => $day_key,
            'day_label'     => date_i18n( 'l', $day_ts ),
            'day_number'    => date_i18n( 'j', $day_ts ),
            'is_weekend'    => in_array( $day_of_week, array( 0, 6 ), true ),
            'events'        => $day_events,
            'primary_event' => $day_events[0] ?? null,
        );
    }

    return $slots;
}

function teca_get_event_organizer_name( array $event ) {
    if ( empty( $event['organizers'] ) || ! is_array( $event['organizers'] ) ) {
        return '';
    }

    return $event['organizers'][0]['title'] ?? '';
}

function teca_get_event_cost_display( $event_id ) {
    $event_id = (int) $event_id;

    if ( $event_id <= 0 ) {
        return '';
    }

    if ( function_exists( 'tribe_get_cost' ) ) {
        $cost = tribe_get_cost( $event_id, true );

        return ! empty( $cost ) ? (string) $cost : '';
    }

    return '';
}

function teca_get_week_range_for_date( $date = '' ) {
    $timestamp     = $date ? strtotime( $date ) : (int) current_time( 'timestamp' );
    $start_of_week = (int) get_option( 'start_of_week', 0 );
    $day_of_week   = (int) wp_date( 'w', $timestamp );
    $diff          = ( $day_of_week - $start_of_week + 7 ) % 7;
    $week_start_ts = strtotime( '-' . $diff . ' days', $timestamp );
    $week_end_ts   = strtotime( '+6 days', $week_start_ts );

    return array(
        'start' => wp_date( 'Y-m-d', $week_start_ts ),
        'end'   => wp_date( 'Y-m-d', $week_end_ts ),
        'label' => sprintf(
            '%1$s - %2$s',
            date_i18n( 'F j', $week_start_ts ),
            date_i18n( 'F j', $week_end_ts )
        ),
    );
}

function teca_format_event_card_datetime_line( $event_id ) {
    $event_id = (int) $event_id;
    $start    = get_post_meta( $event_id, '_EventStartDate', true );

    if ( ! $start ) {
        return '';
    }

    $date_part = teca_format_event_start_date_text( $event_id );
    $all_day   = (bool) get_post_meta( $event_id, '_EventAllDay', true );

    if ( $all_day ) {
        return $date_part;
    }

    $time_range = teca_format_event_time_range( $event_id );

    if ( ! $time_range ) {
        return $date_part;
    }

    return $date_part . ' @ ' . $time_range;
}

function teca_get_event_venue_display( array $event ) {
    $venue_id = (int) ( $event['venue_id'] ?? 0 );

    if ( ! $venue_id ) {
        return array(
            'name'    => '',
            'address' => '',
        );
    }

    $parts = array_filter(
        array(
            get_post_meta( $venue_id, '_VenueAddress', true ),
            get_post_meta( $venue_id, '_VenueCity', true ),
            get_post_meta( $venue_id, '_VenueState', true ),
            get_post_meta( $venue_id, '_VenueZip', true ),
        )
    );

    return array(
        'name'    => get_the_title( $venue_id ),
        'address' => implode( ', ', $parts ),
    );
}

function teca_get_event_excerpt_text( $event_id, $word_count = 35 ) {
    $event_id = (int) $event_id;
    $post     = get_post( $event_id );

    if ( ! $post ) {
        return '';
    }

    $text = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
    $text = wp_strip_all_tags( $text );

    return Helpers::trim_event_details( $text, 'words', $word_count );
}

function teca_is_recurring_event( $event_id ) {
    return function_exists( 'tribe_is_recurring_event' ) && tribe_is_recurring_event( (int) $event_id );
}

function teca_format_event_start_time_display( $event_id ) {
    $event_id = (int) $event_id;
    $all_day  = (bool) get_post_meta( $event_id, '_EventAllDay', true );

    if ( $all_day ) {
        return __( 'All Day', 'the-events-calendar-addon2' );
    }

    $start = get_post_meta( $event_id, '_EventStartDate', true );

    if ( ! $start ) {
        return '';
    }

    return date_i18n( get_option( 'time_format' ), strtotime( $start ) );
}

function teca_format_event_table_time( $event_id ) {
    $event_id = (int) $event_id;
    $all_day  = (bool) get_post_meta( $event_id, '_EventAllDay', true );

    if ( $all_day ) {
        return __( 'All Day', 'the-events-calendar-addon2' );
    }

    $start = get_post_meta( $event_id, '_EventStartDate', true );

    if ( ! $start ) {
        return '';
    }

    return date_i18n( 'H:i', strtotime( $start ) );
}

function teca_get_event_details_line( array $event, $event_id = 0 ) {
    $event_id = (int) ( $event_id ?: ( $event['event_id'] ?? 0 ) );
    $parts      = array();
    $categories = teca_get_event_category_names( $event );

    if ( ! empty( $categories ) ) {
        $parts[] = implode( ', ', $categories );
    }

    $venue = teca_get_event_venue_display( $event );

    if ( ! empty( $venue['name'] ) ) {
        $parts[] = $venue['name'];
    }

    $organizer = teca_get_event_organizer_name( $event );

    if ( $organizer ) {
        $parts[] = $organizer;
    }

    if ( $event_id ) {
        $cost = teca_get_event_cost_display( $event_id );

        if ( $cost ) {
            $parts[] = $cost;
        }
    }

    return implode( ' · ', $parts );
}

function teca_format_event_start_time_short( $event_id ) {
    $event_id = (int) $event_id;
    $all_day  = (bool) get_post_meta( $event_id, '_EventAllDay', true );

    if ( $all_day ) {
        return __( 'All Day', 'the-events-calendar-addon2' );
    }

    $start = get_post_meta( $event_id, '_EventStartDate', true );

    if ( ! $start ) {
        return '';
    }

    $timestamp = strtotime( $start );
    $hour      = (int) date_i18n( 'g', $timestamp );
    $meridiem  = strtoupper( date_i18n( 'a', $timestamp ) );

    return $hour . substr( $meridiem, 0, 1 );
}

function teca_get_event_day_abbrev( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return '';
    }

    return strtoupper( date_i18n( 'D', strtotime( $start ) ) );
}

function teca_get_event_card_subtitle_line( array $event, $event_id = 0 ) {
    $event_id = (int) ( $event_id ?: ( $event['event_id'] ?? 0 ) );
    $parts    = array();
    $venue    = teca_get_event_venue_display( $event );

    if ( ! empty( $venue['name'] ) ) {
        $parts[] = $venue['name'];
    }

    if ( $event_id ) {
        $cost = teca_get_event_cost_display( $event_id );

        if ( $cost ) {
            $parts[] = $cost;
        }
    }

    $organizer = teca_get_event_organizer_name( $event );

    if ( $organizer && empty( $venue['name'] ) ) {
        $parts[] = $organizer;
    }

    if ( empty( $parts ) ) {
        return '';
    }

    return '(' . implode( ', ', $parts ) . ')';
}

function teca_get_week_primary_venue_name( array $events ) {
    foreach ( $events as $event ) {
        $venue = teca_get_event_venue_display( $event );

        if ( ! empty( $venue['name'] ) ) {
            return $venue['name'];
        }
    }

    return '';
}

function teca_get_events_hero_images( array $events, $size = 'large' ) {
    $images = array();

    foreach ( $events as $event ) {
        $event_id = (int) ( $event['event_id'] ?? 0 );

        if ( ! $event_id ) {
            continue;
        }

        $url = get_the_post_thumbnail_url( $event_id, $size );

        if ( ! $url ) {
            continue;
        }

        $images[] = array(
            'url' => $url,
            'alt' => $event['event_name'] ?? '',
        );
    }

    return $images;
}

function teca_get_event_layout_1_date_parts( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return array(
            'day'   => '',
            'month' => '',
            'year'  => '',
        );
    }

    $timestamp = strtotime( $start );

    return array(
        'day'   => date_i18n( 'd', $timestamp ),
        'month' => strtoupper( date_i18n( 'M', $timestamp ) ),
        'year'  => date_i18n( 'Y', $timestamp ),
    );
}

function teca_get_event_layout_1_meta_line( array $event, $event_id = 0 ) {
    $event_id = (int) ( $event_id ?: ( $event['event_id'] ?? 0 ) );
    $parts      = array();
    $categories = teca_get_event_category_names( $event );

    if ( ! empty( $categories ) ) {
        $parts[] = implode( ', ', $categories );
    }

    if ( $event_id ) {
        $time = teca_format_event_start_time_display( $event_id );

        if ( $time ) {
            $parts[] = $time;
        }
    }

    return implode( ' · ', $parts );
}

function teca_get_event_layout_1_venue_name( array $event, $event_id = 0 ) {
    $event_id = (int) ( $event_id ?: ( $event['event_id'] ?? 0 ) );
    $venue    = teca_get_event_venue_display( $event );

    if ( ! empty( $venue['name'] ) ) {
        return (string) $venue['name'];
    }

    if ( ! empty( $event['venue']['title'] ) ) {
        return (string) $event['venue']['title'];
    }

    if ( $event_id && function_exists( 'tribe_get_venue' ) ) {
        $name = tribe_get_venue( $event_id );

        return $name ? (string) $name : '';
    }

    return '';
}

function teca_get_events_layout_1_groups_config() {
    return teca_get_events_section_groups_config();
}

function teca_get_events_section_groups_config() {
    return array(
        'featured'  => array(
            'label'      => __( 'Featured Events', 'the-events-calendar-addon2' ),
            'tab_class'  => 'teca-tab-featured',
            'panel_class'=> 'teca-events-panel-featured',
            'empty_class'=> 'teca-events-empty-featured',
            'empty_text' => __( 'No featured events found.', 'the-events-calendar-addon2' ),
        ),
        'past'      => array(
            'label'      => __( 'Past Events', 'the-events-calendar-addon2' ),
            'tab_class'  => 'teca-tab-past',
            'panel_class'=> 'teca-events-panel-past',
            'empty_class'=> 'teca-events-empty-past',
            'empty_text' => __( 'No past events found.', 'the-events-calendar-addon2' ),
        ),
        'upcoming'  => array(
            'label'      => __( 'Upcoming Events', 'the-events-calendar-addon2' ),
            'tab_class'  => 'teca-tab-upcoming',
            'panel_class'=> 'teca-events-panel-upcoming',
            'empty_class'=> 'teca-events-empty-upcoming',
            'empty_text' => __( 'No upcoming events found.', 'the-events-calendar-addon2' ),
        ),
    );
}

function teca_get_events_layout_2_tab_order() {
    return array( 'past', 'upcoming', 'featured' );
}

function teca_get_events_layout_3_tab_order() {
    return teca_get_events_layout_2_tab_order();
}

function teca_get_event_layout_3_date_parts( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return array(
            'month' => '',
            'day'   => '',
        );
    }

    $timestamp = strtotime( $start );

    return array(
        'month' => strtoupper( date_i18n( 'M', $timestamp ) ),
        'day'   => date_i18n( 'd', $timestamp ),
    );
}

function teca_get_event_layout_2_date_display( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return '';
    }

    return strtoupper( date_i18n( 'd F Y', strtotime( $start ) ) );
}

function teca_get_events_section_group_events( $group_key, array $featured_events, array $past_events, array $upcoming_events ) {
    $map = array(
        'featured'  => $featured_events,
        'past'      => $past_events,
        'upcoming'  => $upcoming_events,
    );

    return $map[ (string) $group_key ] ?? array();
}

function teca_is_timeline_theme( $theme ) {
    return Timeline_Renderer::is_timeline_theme( $theme );
}

function teca_is_accordion_theme( $theme ) {
    return Accordion_Renderer::is_accordion_theme( $theme );
}

function teca_get_accordion_theme_template( $theme ) {
    return Accordion_Renderer::get_template_file( $theme );
}

function teca_get_accordion_meta_summary( array $event, $event_id = 0 ) {
    $event_id = (int) ( $event_id ?: ( $event['event_id'] ?? 0 ) );
    $parts    = array();

    if ( $event_id ) {
        $time = teca_format_event_start_time_display( $event_id );
        if ( $time ) {
            $parts[] = $time;
        }
    }

    $venue = teca_get_event_venue_display( $event );
    if ( ! empty( $venue['name'] ) ) {
        $parts[] = $venue['name'];
    }

    return implode( ' / ', $parts );
}

/**
 * Resolve filter bar position slug for View Type = Filter.
 *
 * @param array $settings Render settings.
 * @return string left|center|right
 */
function teca_get_filter_position( $settings ) {
	$settings = is_array( $settings ) ? $settings : array();

	$position = ! empty( $settings['gs_filter_cat'] )
		? $settings['gs_filter_cat']
		: 'left';

	$position = sanitize_key( (string) $position );

	return in_array( $position, array( 'left', 'center', 'right' ), true ) ? $position : 'left';
}

/**
 * CSS class for filter bar alignment (View Type = Filter only).
 *
 * @param array $settings Render settings.
 * @return string
 */
function teca_get_filter_position_class( $settings ) {
	return 'gs-teca-filter-pos-' . teca_get_filter_position( $settings );
}

function teca_get_timeline_theme_template( $theme ) {
    return Timeline_Renderer::get_template_file( $theme );
}

function teca_get_timeline_date_badge( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return '';
    }

    return date_i18n( 'M j', strtotime( $start ) );
}

function teca_get_timeline_date_pill( array $event ) {
	$start    = $event['dates']['start'] ?? '';
	$event_id = (int) ( $event['event_id'] ?? 0 );

	if ( $event_id ) {
		return teca_format_event_start_date_text( $event_id );
	}

	if ( ! $start ) {
		return '';
	}

	return teca_format_layout_date_string( $start );
}

function teca_get_timeline_date_parts( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return array(
            'day'   => '',
            'month' => '',
            'year'  => '',
        );
    }

    $timestamp = strtotime( $start );

    return array(
        'day'   => date_i18n( 'd', $timestamp ),
        'month' => date_i18n( 'M', $timestamp ),
        'year'  => date_i18n( 'Y', $timestamp ),
    );
}

function teca_get_events_category_filter_options( array $events ) {
    $options = array();

    foreach ( $events as $event ) {
        if ( empty( $event['categories'] ) || ! is_array( $event['categories'] ) ) {
            continue;
        }

        foreach ( $event['categories'] as $category ) {
            $term_id = (int) ( $category['term_id'] ?? 0 );

            if ( $term_id <= 0 ) {
                continue;
            }

            $options[ $term_id ] = $category['name'] ?? '';
        }
    }

    asort( $options, SORT_NATURAL | SORT_FLAG_CASE );

    return $options;
}

function teca_get_events_category_filter_slug_options( array $events ) {
    $options = array();

    foreach ( $events as $event ) {
        if ( empty( $event['categories'] ) || ! is_array( $event['categories'] ) ) {
            continue;
        }

        foreach ( $event['categories'] as $category ) {
            $slug = sanitize_title( (string) ( $category['slug'] ?? '' ) );

            if ( '' === $slug ) {
                continue;
            }

            $options[ $slug ] = $category['name'] ?? $slug;
        }
    }

    asort( $options, SORT_NATURAL | SORT_FLAG_CASE );

    return $options;
}

function teca_render_calendar_event_type_filter( array $events, $layout_id = '', array $args = array() ) {
    $options = teca_get_events_category_filter_slug_options( $events );

    if ( empty( $options ) ) {
        return '';
    }

    $layout_id      = sanitize_key( (string) $layout_id );
    $select_id      = 'teca-calendar-event-type-' . ( $layout_id ? $layout_id : 'teca' );
    $wrapper_class  = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'teca-calendar-event-type-filter-wrap';
    $label          = isset( $args['label'] ) ? (string) $args['label'] : __( 'Event Type', 'the-events-calendar-addon2' );
    $select_classes = 'teca-calendar-event-type-filter teca-calendar-select';

    if ( ! empty( $args['select_class'] ) ) {
        $select_classes .= ' ' . sanitize_html_class( (string) $args['select_class'] );
    }

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $wrapper_class ); ?>">
        <label class="teca-calendar-filter-label" for="<?php echo esc_attr( $select_id ); ?>">
            <?php echo esc_html( $label ); ?>
        </label>
        <select id="<?php echo esc_attr( $select_id ); ?>" class="<?php echo esc_attr( $select_classes ); ?>">
            <option value="all"><?php esc_html_e( 'All Event Types', 'the-events-calendar-addon2' ); ?></option>
            <?php foreach ( $options as $slug => $name ) : ?>
                <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php

    return (string) ob_get_clean();
}

function teca_get_event_category_ids( array $event ) {
    if ( empty( $event['categories'] ) || ! is_array( $event['categories'] ) ) {
        return array();
    }

    return array_values(
        array_filter(
            array_map(
                static function ( $category ) {
                    return (int) ( $category['term_id'] ?? 0 );
                },
                $event['categories']
            )
        )
    );
}

function teca_get_event_tag_ids( array $event ) {
    if ( ! empty( $event['tags'] ) && is_array( $event['tags'] ) ) {
        return array_values(
            array_filter(
                array_map(
                    static function ( $tag ) {
                        return (int) ( $tag['term_id'] ?? 0 );
                    },
                    $event['tags']
                )
            )
        );
    }

    $ids = array();

    foreach ( teca_get_event_tag_terms( $event ) as $term ) {
        if ( ! empty( $term->term_id ) ) {
            $ids[] = (int) $term->term_id;
        }
    }

    return array_values( array_unique( $ids ) );
}

function teca_get_event_date_key( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return '';
    }

    return wp_date( 'Y-m-d', strtotime( $start ) );
}

function teca_get_event_week_key( array $event ) {
    $date_key = teca_get_event_date_key( $event );

    if ( ! $date_key ) {
        return '';
    }

    $range = teca_get_week_range_for_date( $date_key );

    return $range['start'] ?? '';
}

function teca_get_event_month_key( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return '';
    }

    return wp_date( 'Y-m', strtotime( $start ) );
}

function teca_get_event_year( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return 0;
    }

    return (int) wp_date( 'Y', strtotime( $start ) );
}

function teca_get_event_quarter_key( array $event ) {
    $start = $event['dates']['start'] ?? '';

    if ( ! $start ) {
        return '';
    }

    $month = (int) wp_date( 'n', strtotime( $start ) );

    if ( $month <= 3 ) {
        return 'Q1';
    }

    if ( $month <= 6 ) {
        return 'Q2';
    }

    if ( $month <= 9 ) {
        return 'Q3';
    }

    return 'Q4';
}

function teca_parse_year_quarter_key( $year_quarter_key ) {
    $year_quarter_key = (string) $year_quarter_key;

    if ( preg_match( '/^(\d{4})-(Q[1-4])$/', $year_quarter_key, $matches ) ) {
        return array(
            'year'        => (int) $matches[1],
            'quarter_key' => $matches[2],
        );
    }

    if ( preg_match( '/^(Q[1-4])$/', $year_quarter_key, $matches ) ) {
        return array(
            'year'        => (int) wp_date( 'Y' ),
            'quarter_key' => $matches[1],
        );
    }

    return array(
        'year'        => 0,
        'quarter_key' => '',
    );
}

function teca_get_event_year_quarter_key( array $event ) {
    $year        = teca_get_event_year( $event );
    $quarter_key = teca_get_event_quarter_key( $event );

    if ( ! $year || ! $quarter_key ) {
        return '';
    }

    return $year . '-' . $quarter_key;
}

function teca_get_year_quarter_label( $year_quarter_key ) {
    $parsed = teca_parse_year_quarter_key( $year_quarter_key );

    if ( ! $parsed['quarter_key'] || ! $parsed['year'] ) {
        return '';
    }

    $quarter_label = teca_get_event_quarter_label( $parsed['quarter_key'] );

    if ( ! $quarter_label ) {
        return '';
    }

    return $quarter_label . ' ' . $parsed['year'];
}

function teca_get_event_year_quarter_label( array $event ) {
    return teca_get_year_quarter_label( teca_get_event_year_quarter_key( $event ) );
}

function teca_compare_year_quarter_keys( $left_key, $right_key ) {
    $left  = teca_parse_year_quarter_key( $left_key );
    $right = teca_parse_year_quarter_key( $right_key );

    if ( $left['year'] !== $right['year'] ) {
        return $left['year'] <=> $right['year'];
    }

    $left_quarter  = (int) substr( $left['quarter_key'], 1 );
    $right_quarter = (int) substr( $right['quarter_key'], 1 );

    return $left_quarter <=> $right_quarter;
}

function teca_group_events_by_year_quarter( array $events ) {
    $groups = array();

    foreach ( $events as $event ) {
        $year_quarter_key = teca_get_event_year_quarter_key( $event );

        if ( ! $year_quarter_key ) {
            continue;
        }

        if ( ! isset( $groups[ $year_quarter_key ] ) ) {
            $groups[ $year_quarter_key ] = array();
        }

        $groups[ $year_quarter_key ][] = $event;
    }

    uksort( $groups, __NAMESPACE__ . '\\teca_compare_year_quarter_keys' );

    return $groups;
}

function teca_get_quarter_filter_options( array $events ) {
    $options = array(
        'all' => __( 'All Events', 'the-events-calendar-addon2' ),
    );
    $labels  = array();

    foreach ( $events as $event ) {
        $year_quarter_key = teca_get_event_year_quarter_key( $event );

        if ( $year_quarter_key ) {
            $labels[ $year_quarter_key ] = teca_get_year_quarter_label( $year_quarter_key );
        }
    }

    uksort( $labels, __NAMESPACE__ . '\\teca_compare_year_quarter_keys' );

    return array_merge( $options, $labels );
}

function teca_get_quarter_definitions() {
    return array(
        'Q1' => array(
            'ordinal' => '1st',
            'label'   => __( '1st Quarter', 'the-events-calendar-addon2' ),
            'months'  => array( 1, 2, 3 ),
        ),
        'Q2' => array(
            'ordinal' => '2nd',
            'label'   => __( '2nd Quarter', 'the-events-calendar-addon2' ),
            'months'  => array( 4, 5, 6 ),
        ),
        'Q3' => array(
            'ordinal' => '3rd',
            'label'   => __( '3rd Quarter', 'the-events-calendar-addon2' ),
            'months'  => array( 7, 8, 9 ),
        ),
        'Q4' => array(
            'ordinal' => '4th',
            'label'   => __( '4th Quarter', 'the-events-calendar-addon2' ),
            'months'  => array( 10, 11, 12 ),
        ),
    );
}

function teca_get_event_quarter_label( $quarter_key ) {
    $definitions = teca_get_quarter_definitions();

    return $definitions[ (string) $quarter_key ]['label'] ?? '';
}

function teca_get_quarterly_filter_options( array $events = array() ) {
    return teca_get_quarter_filter_options( $events );
}

function teca_get_quarterly_layout_year( array $events ) {
    $current_year = (int) wp_date( 'Y' );
    $year_counts  = array();

    foreach ( $events as $event ) {
        $start = $event['dates']['start'] ?? '';

        if ( ! $start ) {
            continue;
        }

        $event_year = (int) wp_date( 'Y', strtotime( $start ) );
        $year_counts[ $event_year ] = ( $year_counts[ $event_year ] ?? 0 ) + 1;
    }

    if ( empty( $year_counts ) ) {
        return $current_year;
    }

    if ( ! empty( $year_counts[ $current_year ] ) ) {
        return $current_year;
    }

    krsort( $year_counts );

    return (int) array_key_first( $year_counts );
}

function teca_build_quarterly_year_quarter_section( $year_quarter_key, array $quarter_events ) {
    $parsed       = teca_parse_year_quarter_key( $year_quarter_key );
    $year         = (int) $parsed['year'];
    $quarter_key  = $parsed['quarter_key'];
    $definitions  = teca_get_quarter_definitions();
    $definition   = $definitions[ $quarter_key ] ?? null;

    if ( ! $definition || ! $year ) {
        return null;
    }

    $months = array();

    foreach ( $definition['months'] as $month_number ) {
        $month_key = sprintf( '%d-%02d', $year, $month_number );
        $month_ts  = strtotime( $month_key . '-01' );

        $months[] = array(
            'month'      => $month_number,
            'month_key'  => $month_key,
            'month_abbr' => strtoupper( date_i18n( 'M', $month_ts ) ),
            'month_name' => date_i18n( 'F', $month_ts ),
            'events'     => array(),
        );
    }

    foreach ( $quarter_events as $event ) {
        $start = $event['dates']['start'] ?? '';

        if ( ! $start ) {
            continue;
        }

        $month_number = (int) wp_date( 'n', strtotime( $start ) );

        foreach ( $months as $month_index => $month_data ) {
            if ( (int) $month_data['month'] !== $month_number ) {
                continue;
            }

            $months[ $month_index ]['events'][] = $event;
            break;
        }
    }

    $has_events = false;

    foreach ( $months as $month_index => $month_data ) {
        $month_events = teca_sort_events_by_start_date( $month_data['events'] );
        $months[ $month_index ]['events'] = $month_events;

        if ( ! empty( $month_events ) ) {
            $has_events = true;
        }
    }

    return array(
        'key'         => $year_quarter_key,
        'year'        => $year,
        'quarter_key' => $quarter_key,
        'ordinal'     => $definition['ordinal'],
        'label'       => teca_get_year_quarter_label( $year_quarter_key ),
        'months'      => $months,
        'has_events'  => $has_events,
    );
}

function teca_get_quarterly_layout_years_label( array $layout_data ) {
    $years = $layout_data['years'] ?? array();

    if ( empty( $years ) ) {
        return (string) wp_date( 'Y' );
    }

    $years = array_map( 'absint', $years );
    sort( $years );

    if ( 1 === count( $years ) ) {
        return (string) $years[0];
    }

    $first = (int) $years[0];
    $last  = (int) $years[ count( $years ) - 1 ];

    if ( $first === $last ) {
        return (string) $first;
    }

    return $first . ' – ' . $last;
}

function teca_build_quarterly_layout_1_data( array $events, $year = null ) {
    $definitions           = teca_get_quarter_definitions();
    $year_quarter_groups   = teca_group_events_by_year_quarter( $events );
    $quarters              = array();
    $years                 = array();

    foreach ( $year_quarter_groups as $year_quarter_key => $quarter_events ) {
        $section = teca_build_quarterly_year_quarter_section( $year_quarter_key, $quarter_events );

        if ( ! $section ) {
            continue;
        }

        $quarters[ $year_quarter_key ] = $section;
        $years[ $section['year'] ]      = $section['year'];
    }

    if ( empty( $quarters ) && null !== $year ) {
        $fallback_year = (int) $year;

        foreach ( $definitions as $quarter_key => $definition ) {
            $year_quarter_key = $fallback_year . '-' . $quarter_key;
            $section          = teca_build_quarterly_year_quarter_section( $year_quarter_key, array() );

            if ( $section ) {
                $quarters[ $year_quarter_key ] = $section;
                $years[ $fallback_year ]       = $fallback_year;
            }
        }
    }

    uksort( $quarters, __NAMESPACE__ . '\\teca_compare_year_quarter_keys' );
    sort( $years );

    $primary_year = ! empty( $years ) ? (int) $years[0] : teca_get_quarterly_layout_year( $events );

    return array(
        'year'     => $primary_year,
        'years'    => array_values( $years ),
        'quarters' => $quarters,
    );
}

function teca_get_quarterly_layout_2_accent_slugs() {
    return array( 'indigo', 'rose', 'teal', 'amber', 'violet', 'sky' );
}

function teca_get_quarterly_layout_2_accent_slug( $index ) {
    $slugs = teca_get_quarterly_layout_2_accent_slugs();

    return $slugs[ (int) $index % count( $slugs ) ];
}

function teca_build_quarterly_layout_2_data( array $events, $year = null ) {
    $layout_data = teca_build_quarterly_layout_1_data( $events, $year );

    foreach ( $layout_data['quarters'] as $quarter_key => $quarter_data ) {
        foreach ( $quarter_data['months'] as $month_index => $month_data ) {
            $layout_data['quarters'][ $quarter_key ]['months'][ $month_index ]['calendar'] = teca_build_monthly_calendar_cells(
                $month_data['month_key'],
                $month_data['events']
            );
        }
    }

    return $layout_data;
}

function teca_get_quarterly_layout_3_accent_slugs() {
    return array( 'violet', 'plum', 'midnight', 'amethyst', 'sapphire', 'orchid' );
}

function teca_get_quarterly_layout_3_accent_slug( $index ) {
    $slugs = teca_get_quarterly_layout_3_accent_slugs();

    return $slugs[ (int) $index % count( $slugs ) ];
}

function teca_build_quarterly_layout_3_data( array $events, $year = null ) {
    return teca_build_quarterly_layout_2_data( $events, $year );
}

function teca_get_event_year_string( array $event ) {
    $year = teca_get_event_year( $event );

    return $year ? (string) $year : '';
}

function teca_normalize_year_filter_options( array $options ) {
    $normalized = array();

    foreach ( $options as $value => $label ) {
        if ( 'all' === (string) $value ) {
            $normalized['all'] = is_array( $label ) && isset( $label['label'] ) ? $label['label'] : $label;
            continue;
        }

        $year_label = is_array( $label ) && isset( $label['label'] ) ? (string) $label['label'] : (string) $label;

        if ( preg_match( '/^\d{4}$/', $year_label ) ) {
            $normalized[ $year_label ] = $year_label;
            continue;
        }

        $normalized[ (string) $value ] = $label;
    }

    return $normalized;
}

function teca_get_year_filter_options( array $events ) {
    $options = array(
        'all' => __( 'All Events', 'the-events-calendar-addon2' ),
    );
    $years   = array();

    foreach ( $events as $event ) {
        $year = teca_get_event_year_string( $event );

        if ( $year ) {
            $years[ $year ] = $year;
        }
    }

    ksort( $years, SORT_NUMERIC );

    foreach ( $years as $year ) {
        $options[ $year ] = $year;
    }

    return $options;
}

function teca_group_events_by_year( array $events ) {
    $groups = array();

    foreach ( $events as $event ) {
        $year = teca_get_event_year( $event );

        if ( ! $year ) {
            continue;
        }

        if ( ! isset( $groups[ $year ] ) ) {
            $groups[ $year ] = array();
        }

        $groups[ $year ][] = $event;
    }

    ksort( $groups, SORT_NUMERIC );

    return $groups;
}

function teca_build_yearly_year_months( $year, array $year_events ) {
    $year   = (int) $year;
    $months = array();

    for ( $month_number = 1; $month_number <= 12; $month_number++ ) {
        $month_key = sprintf( '%d-%02d', $year, $month_number );
        $month_ts  = strtotime( $month_key . '-01' );

        $months[ $month_number ] = array(
            'month'      => $month_number,
            'month_key'  => $month_key,
            'month_name' => date_i18n( 'F', $month_ts ),
            'events'     => array(),
        );
    }

    foreach ( $year_events as $event ) {
        $start = $event['dates']['start'] ?? '';

        if ( ! $start ) {
            continue;
        }

        $event_year   = (int) wp_date( 'Y', strtotime( $start ) );
        $month_number = (int) wp_date( 'n', strtotime( $start ) );

        if ( $event_year !== $year || ! isset( $months[ $month_number ] ) ) {
            continue;
        }

        $months[ $month_number ]['events'][] = $event;
    }

    foreach ( $months as $month_number => $month_data ) {
        $months[ $month_number ]['events'] = teca_sort_events_by_start_date( $month_data['events'] );
    }

    return array_values( $months );
}

function teca_get_yearly_layout_years_label( array $years ) {
    if ( empty( $years ) ) {
        return (string) wp_date( 'Y' );
    }

    $year_keys = array_map( 'absint', array_keys( $years ) );
    sort( $year_keys, SORT_NUMERIC );

    if ( 1 === count( $year_keys ) ) {
        return (string) $year_keys[0];
    }

    $first = (int) $year_keys[0];
    $last  = (int) $year_keys[ count( $year_keys ) - 1 ];

    if ( $first === $last ) {
        return (string) $first;
    }

    return $first . ' – ' . $last;
}

function teca_build_yearly_layout_2_data( array $events ) {
    return teca_build_yearly_layout_1_data( $events );
}

function teca_build_yearly_layout_3_data( array $events ) {
    return teca_build_yearly_layout_1_data( $events );
}

function teca_build_yearly_layout_1_data( array $events ) {
    $year_groups = teca_group_events_by_year( $events );
    $years       = array();

    foreach ( $year_groups as $year => $year_events ) {
        $year = (int) $year;

        $years[ (string) $year ] = array(
            'year'       => $year,
            'label'      => (string) $year,
            'months'     => teca_build_yearly_year_months( $year, $year_events ),
            'has_events' => ! empty( $year_events ),
        );
    }

    ksort( $years, SORT_NUMERIC );

    $year_keys    = array_keys( $years );
    $primary_year = ! empty( $year_keys ) ? (int) $year_keys[ count( $year_keys ) - 1 ] : (int) wp_date( 'Y' );

    return array(
        'year'        => $primary_year,
        'years'       => $years,
        'years_label' => teca_get_yearly_layout_years_label( $years ),
    );
}

function teca_get_quarter_month_range_label( array $months ) {
    if ( empty( $months ) ) {
        return '';
    }

    $first = reset( $months );
    $last  = end( $months );
    $start = $first['month_name'] ?? '';
    $end   = $last['month_name'] ?? '';

    if ( ! $start || ! $end ) {
        return '';
    }

    if ( $start === $end ) {
        return $start;
    }

    return $start . ' – ' . $end;
}

function teca_get_event_week_label( $week_key ) {
    $week_key = (string) $week_key;

    if ( '' === $week_key ) {
        return '';
    }

    $range = teca_get_week_range_for_date( $week_key );

    return $range['label'] ?? '';
}

function teca_get_event_month_label( $month_key ) {
    $timestamp = strtotime( (string) $month_key . '-01' );

    if ( ! $timestamp ) {
        return '';
    }

    return date_i18n( 'F Y', $timestamp );
}

function teca_get_event_filter_data( array $event ) {
    $start    = $event['dates']['start'] ?? '';
    $end      = $event['dates']['end'] ?? '';
    $event_id = (int) ( $event['event_id'] ?? 0 );
    $date_key          = teca_get_event_date_key( $event );
    $week_key          = teca_get_event_week_key( $event );
    $month_key         = teca_get_event_month_key( $event );
    $quarter_key       = teca_get_event_quarter_key( $event );
    $event_year        = teca_get_event_year( $event );
    $year_quarter_key  = teca_get_event_year_quarter_key( $event );
    $end_date = $date_key;

    if ( $end ) {
        $parsed_end = wp_date( 'Y-m-d', strtotime( $end ) );
        if ( $parsed_end ) {
            $end_date = $parsed_end;
        }
    }

    return array(
        'event_id'        => $event_id,
        'event_date'      => $date_key,
        'event_week'      => $week_key,
        'event_month'       => $month_key,
        'event_year'        => $event_year ? (string) $event_year : '',
        'event_quarter'     => $year_quarter_key ? $year_quarter_key : $quarter_key,
        'event_quarter_key' => $quarter_key,
        'event_year_quarter'=> $year_quarter_key,
        'start_date'        => $date_key,
        'start_datetime'  => $start,
        'end_date'        => $end_date,
        'end_datetime'    => $end,
    );
}

function teca_get_event_filter_attributes_html( array $event ) {
    $data = teca_get_event_filter_data( $event );
    $html = '';

    $map = array(
        'data-event-id'         => (string) $data['event_id'],
        'data-event-date'       => $data['event_date'],
        'data-event-week'       => $data['event_week'],
        'data-event-month'        => $data['event_month'],
        'data-event-year'         => $data['event_year'],
        'data-event-quarter'      => $data['event_quarter'],
        'data-event-quarter-key'  => $data['event_quarter_key'],
        'data-event-year-quarter' => $data['event_year_quarter'],
        'data-event-start-date' => $data['start_date'],
        'data-event-end-date'   => $data['end_date'],
        'data-start-date'         => $data['start_date'],
        'data-start-datetime'   => $data['start_datetime'],
        'data-end-date'         => $data['end_date'],
        'data-end-datetime'     => $data['end_datetime'],
    );

    foreach ( $map as $attribute => $value ) {
        if ( '' !== (string) $value ) {
            $html .= sprintf( ' %s="%s"', esc_attr( $attribute ), esc_attr( (string) $value ) );
        }
    }

    $category_slugs = teca_get_event_category_slugs( $event );
    if ( ! empty( $category_slugs ) ) {
        $html .= sprintf( ' data-category-slugs="%s"', esc_attr( implode( ',', $category_slugs ) ) );
        $html .= sprintf( ' data-event-categories="%s"', esc_attr( implode( ',', $category_slugs ) ) );
        $html .= sprintf( ' data-event-category-slugs="%s"', esc_attr( implode( ',', $category_slugs ) ) );
    }

    $category_ids = teca_get_event_category_ids( $event );
    if ( ! empty( $category_ids ) ) {
        $html .= sprintf( ' data-event-category-ids="%s"', esc_attr( implode( ',', array_map( 'strval', $category_ids ) ) ) );
    }

    $tag_slugs = teca_get_event_tag_slugs( $event );
    if ( ! empty( $tag_slugs ) ) {
        $html .= sprintf( ' data-tags="%s"', esc_attr( implode( ',', $tag_slugs ) ) );
        $html .= sprintf( ' data-event-tags="%s"', esc_attr( implode( ',', $tag_slugs ) ) );
        $html .= sprintf( ' data-event-tag-slugs="%s"', esc_attr( implode( ',', $tag_slugs ) ) );
    }

    $tag_ids = teca_get_event_tag_ids( $event );
    if ( ! empty( $tag_ids ) ) {
        $html .= sprintf( ' data-event-tag-ids="%s"', esc_attr( implode( ',', array_map( 'strval', $tag_ids ) ) ) );
    }

    $venue_data = teca_get_event_venue_filter_data( $event );
    if ( $venue_data['venue_id'] ) {
        $html .= sprintf( ' data-event-venue-id="%s"', esc_attr( (string) $venue_data['venue_id'] ) );
    }
    if ( $venue_data['venue_slug'] ) {
        $html .= sprintf( ' data-event-venue-name="%s"', esc_attr( $venue_data['venue_slug'] ) );
    }

    $organizer_data = teca_get_event_organizer_filter_data( $event );
    if ( ! empty( $organizer_data['organizer_ids'] ) ) {
        $html .= sprintf(
            ' data-event-organizer-ids="%s"',
            esc_attr( implode( ',', array_map( 'strval', $organizer_data['organizer_ids'] ) ) )
        );
    }
    if ( ! empty( $organizer_data['organizer_slugs'] ) ) {
        $html .= sprintf(
            ' data-event-organizer-names="%s"',
            esc_attr( implode( ',', $organizer_data['organizer_slugs'] ) )
        );
    }

    $location_data = teca_get_event_location_filter_data( $event );
    if ( $location_data['city_slug'] ) {
        $html .= sprintf( ' data-event-city="%s"', esc_attr( $location_data['city_slug'] ) );
    }
    if ( $location_data['state_slug'] ) {
        $html .= sprintf( ' data-event-state="%s"', esc_attr( $location_data['state_slug'] ) );
    }
    if ( $location_data['country_slug'] ) {
        $html .= sprintf( ' data-event-country="%s"', esc_attr( $location_data['country_slug'] ) );
    }

    $cost_data = teca_get_event_cost_filter_data( $event );
    $html     .= sprintf( ' data-event-cost="%s"', esc_attr( $cost_data['cost_type'] ) );
    if ( '' !== (string) $cost_data['cost_value'] ) {
        $html .= sprintf( ' data-event-cost-value="%s"', esc_attr( (string) $cost_data['cost_value'] ) );
    }

    $time_data = teca_get_event_time_filter_data( $event );
    if ( $time_data['start_time'] ) {
        $html .= sprintf( ' data-event-start-time="%s"', esc_attr( $time_data['start_time'] ) );
    }
    if ( $time_data['end_time'] ) {
        $html .= sprintf( ' data-event-end-time="%s"', esc_attr( $time_data['end_time'] ) );
    }

    $featured_data = teca_get_event_featured_filter_data( $event );
    $html         .= sprintf( ' data-event-featured="%s"', esc_attr( (string) $featured_data['featured'] ) );

    $status_data = teca_get_event_status_filter_data( $event );
    if ( $status_data['status'] ) {
        $html .= sprintf( ' data-event-status="%s"', esc_attr( $status_data['status'] ) );
    }

    $day_data = teca_get_event_day_filter_data( $event );
    if ( $day_data['start_day'] ) {
        $html .= sprintf( ' data-event-start-day="%s"', esc_attr( $day_data['start_day'] ) );
    }
    if ( $day_data['end_day'] ) {
        $html .= sprintf( ' data-event-end-day="%s"', esc_attr( $day_data['end_day'] ) );
    }
    if ( $day_data['days'] ) {
        $html .= sprintf( ' data-event-days="%s"', esc_attr( $day_data['days'] ) );
    }

    return $html;
}

function teca_get_day_slug_map() {
    return array(
        '0' => 'sunday',
        '1' => 'monday',
        '2' => 'tuesday',
        '3' => 'wednesday',
        '4' => 'thursday',
        '5' => 'friday',
        '6' => 'saturday',
    );
}

function teca_get_date_key_timestamp( $date_key ) {
    $date_key = trim( (string) $date_key );

    if ( '' === $date_key ) {
        return 0;
    }

    $datetime = date_create_immutable( $date_key . ' 12:00:00', wp_timezone() );

    return $datetime ? $datetime->getTimestamp() : 0;
}

function teca_get_day_slug_from_timestamp( $timestamp ) {
    if ( ! $timestamp ) {
        return '';
    }

    $map     = teca_get_day_slug_map();
    $day_num = wp_date( 'w', $timestamp );

    return $map[ $day_num ] ?? '';
}

function teca_get_day_slug_from_date_key( $date_key ) {
    return teca_get_day_slug_from_timestamp( teca_get_date_key_timestamp( $date_key ) );
}

function teca_build_event_day_slugs_between_dates( $start_date, $end_date ) {
    $start_date = trim( (string) $start_date );
    $end_date   = trim( (string) $end_date );

    if ( '' === $start_date ) {
        return array();
    }

    if ( '' === $end_date ) {
        $end_date = $start_date;
    }

    if ( $end_date < $start_date ) {
        $end_date = $start_date;
    }

    $days       = array();
    $current_ts = teca_get_date_key_timestamp( $start_date );
    $end_ts     = teca_get_date_key_timestamp( $end_date );

    if ( ! $current_ts || ! $end_ts ) {
        return array();
    }

    while ( $current_ts <= $end_ts ) {
        $day_slug = teca_get_day_slug_from_timestamp( $current_ts );

        if ( $day_slug && ! in_array( $day_slug, $days, true ) ) {
            $days[] = $day_slug;
        }

        $next = ( new \DateTimeImmutable( '@' . $current_ts ) )
            ->setTimezone( wp_timezone() )
            ->modify( '+1 day' );

        if ( ! $next ) {
            break;
        }

        $current_ts = $next->getTimestamp();
    }

    return $days;
}

/**
 * Calculate weekday filter data from TEC event start/end dates.
 *
 * Day names are derived from date/month/year — not from any stored day meta.
 *
 * @param int|array $event_or_id Event post ID or event array containing event_id.
 * @return array{start_date:string,end_date:string,start_day:string,end_day:string,days:string}
 */
function teca_get_event_day_filter_data( $event_or_id ) {
    if ( is_array( $event_or_id ) ) {
        $event_id = (int) ( $event_or_id['event_id'] ?? 0 );
        $event    = $event_or_id;
    } else {
        $event_id = (int) $event_or_id;
        $event    = array( 'event_id' => $event_id );
    }

    $start_date = '';
    $end_date   = '';

    if ( $event_id && function_exists( 'tribe_get_start_date' ) ) {
        $start_date = (string) tribe_get_start_date( $event_id, false, 'Y-m-d' );
    }

    if ( $event_id && function_exists( 'tribe_get_end_date' ) ) {
        $end_date = (string) tribe_get_end_date( $event_id, false, 'Y-m-d' );
    }

    if ( '' === $start_date ) {
        $start_date = teca_get_event_date_key( $event );
    }

    if ( '' === $end_date ) {
        $end = $event['dates']['end'] ?? '';

        if ( $end ) {
            $parsed_end = wp_date( 'Y-m-d', strtotime( (string) $end ) );

            if ( $parsed_end ) {
                $end_date = $parsed_end;
            }
        }
    }

    if ( '' === $start_date && $event_id ) {
        $meta = Query::get_event_date_meta( $event_id );

        if ( ! empty( $meta['start'] ) ) {
            $start_date = wp_date( 'Y-m-d', strtotime( (string) $meta['start'] ) );
        }

        if ( ! empty( $meta['end'] ) ) {
            $end_date = wp_date( 'Y-m-d', strtotime( (string) $meta['end'] ) );
        }
    }

    if ( '' === $start_date && $event_id ) {
        $timestamp = get_post_time( 'U', true, $event_id );

        if ( $timestamp ) {
            $start_date = wp_date( 'Y-m-d', $timestamp );
        }
    }

    if ( '' === $end_date ) {
        $end_date = $start_date;
    }

    if ( $end_date < $start_date ) {
        $end_date = $start_date;
    }

    if ( '' === $start_date ) {
        return array(
            'start_date' => '',
            'end_date'   => '',
            'start_day'  => '',
            'end_day'    => '',
            'days'       => '',
        );
    }

    $day_slugs = teca_build_event_day_slugs_between_dates( $start_date, $end_date );

    return array(
        'start_date' => $start_date,
        'end_date'   => $end_date,
        'start_day'  => ! empty( $day_slugs ) ? $day_slugs[0] : teca_get_day_slug_from_date_key( $start_date ),
        'end_day'    => ! empty( $day_slugs ) ? $day_slugs[ count( $day_slugs ) - 1 ] : teca_get_day_slug_from_date_key( $end_date ),
        'days'       => implode( ',', $day_slugs ),
    );
}

function teca_get_filter_slug_from_name( $name ) {
    $name = trim( (string) $name );

    if ( '' === $name ) {
        return '';
    }

    return sanitize_title( $name );
}

function teca_get_event_venue_filter_data( array $event ) {
    $venue_id   = (int) ( $event['venue_id'] ?? 0 );
    $venue_name = '';

    if ( ! empty( $event['venue']['title'] ) ) {
        $venue_name = (string) $event['venue']['title'];
    } elseif ( $venue_id ) {
        $venue_name = get_the_title( $venue_id );
    }

    return array(
        'venue_id'   => $venue_id,
        'venue_name' => $venue_name,
        'venue_slug' => teca_get_filter_slug_from_name( $venue_name ),
    );
}

function teca_get_event_organizer_filter_data( array $event ) {
    $organizer_ids   = array();
    $organizer_names = array();
    $organizer_slugs = array();

    if ( empty( $event['organizers'] ) || ! is_array( $event['organizers'] ) ) {
        return array(
            'organizer_ids'   => $organizer_ids,
            'organizer_names' => $organizer_names,
            'organizer_slugs' => $organizer_slugs,
        );
    }

    foreach ( $event['organizers'] as $organizer ) {
        $organizer_id   = (int) ( $organizer['id'] ?? 0 );
        $organizer_name = trim( (string) ( $organizer['title'] ?? '' ) );

        if ( ! $organizer_id && '' === $organizer_name ) {
            continue;
        }

        if ( $organizer_id ) {
            $organizer_ids[] = $organizer_id;
        }

        if ( '' !== $organizer_name ) {
            $organizer_names[] = $organizer_name;
            $organizer_slugs[] = teca_get_filter_slug_from_name( $organizer_name );
        }
    }

    return array(
        'organizer_ids'   => array_values( array_unique( $organizer_ids ) ),
        'organizer_names' => $organizer_names,
        'organizer_slugs' => $organizer_slugs,
    );
}

function teca_build_venue_filter_options( array $events ) {
    $venues = array();

    foreach ( $events as $event ) {
        if ( ! is_array( $event ) ) {
            continue;
        }

        $venue_data = teca_get_event_venue_filter_data( $event );

        if ( ! $venue_data['venue_id'] && '' === $venue_data['venue_name'] ) {
            continue;
        }

        $option_key = $venue_data['venue_id'] ? 'id:' . $venue_data['venue_id'] : 'slug:' . $venue_data['venue_slug'];

        if ( isset( $venues[ $option_key ] ) ) {
            continue;
        }

        $venues[ $option_key ] = array(
            'value' => $venue_data['venue_id'] ? (string) $venue_data['venue_id'] : $venue_data['venue_slug'],
            'label' => $venue_data['venue_name'],
        );
    }

    $options = array_values( $venues );

    usort(
        $options,
        static function ( $left, $right ) {
            return strcasecmp( (string) $left['label'], (string) $right['label'] );
        }
    );

    return $options;
}

function teca_build_organizer_filter_options( array $events ) {
    $organizers = array();

    foreach ( $events as $event ) {
        if ( ! is_array( $event ) ) {
            continue;
        }

        $organizer_data = teca_get_event_organizer_filter_data( $event );

        foreach ( $organizer_data['organizer_ids'] as $organizer_id ) {
            $organizer_name = '';

            if ( ! empty( $event['organizers'] ) && is_array( $event['organizers'] ) ) {
                foreach ( $event['organizers'] as $organizer ) {
                    if ( (int) ( $organizer['id'] ?? 0 ) === (int) $organizer_id ) {
                        $organizer_name = trim( (string) ( $organizer['title'] ?? '' ) );
                        break;
                    }
                }
            }

            if ( '' === $organizer_name ) {
                $organizer_name = get_the_title( $organizer_id );
            }

            if ( '' === $organizer_name ) {
                continue;
            }

            $option_key = 'id:' . $organizer_id;

            if ( isset( $organizers[ $option_key ] ) ) {
                continue;
            }

            $organizers[ $option_key ] = array(
                'value' => (string) $organizer_id,
                'label' => $organizer_name,
            );
        }
    }

    $options = array_values( $organizers );

    usort(
        $options,
        static function ( $left, $right ) {
            return strcasecmp( (string) $left['label'], (string) $right['label'] );
        }
    );

    return $options;
}

function teca_get_event_location_filter_data( array $event ) {
    $venue_id = (int) ( $event['venue_id'] ?? 0 );
    $event_id = (int) ( $event['event_id'] ?? 0 );
    $city     = '';
    $state    = '';
    $country  = '';

    if ( $venue_id ) {
        $city    = trim( (string) get_post_meta( $venue_id, '_VenueCity', true ) );
        $state   = trim( (string) get_post_meta( $venue_id, '_VenueState', true ) );
        $country = trim( (string) get_post_meta( $venue_id, '_VenueCountry', true ) );
    }

    if ( $event_id ) {
        if ( '' === $city && function_exists( 'tribe_get_city' ) ) {
            $city = trim( (string) tribe_get_city( $event_id ) );
        }

        if ( '' === $state && function_exists( 'tribe_get_state' ) ) {
            $state = trim( (string) tribe_get_state( $event_id ) );
        }

        if ( '' === $country && function_exists( 'tribe_get_country' ) ) {
            $country = trim( (string) tribe_get_country( $event_id ) );
        }
    }

    return array(
        'city'         => $city,
        'city_slug'    => teca_get_filter_slug_from_name( $city ),
        'state'        => $state,
        'state_slug'   => teca_get_filter_slug_from_name( $state ),
        'country'      => $country,
        'country_slug' => teca_get_filter_slug_from_name( $country ),
    );
}

function teca_build_location_filter_options( array $events, $location_key ) {
    $options_map = array();
    $slug_key    = $location_key . '_slug';

    foreach ( $events as $event ) {
        if ( ! is_array( $event ) ) {
            continue;
        }

        $location_data = teca_get_event_location_filter_data( $event );
        $label         = trim( (string) ( $location_data[ $location_key ] ?? '' ) );
        $slug          = trim( (string) ( $location_data[ $slug_key ] ?? '' ) );

        if ( '' === $label || '' === $slug || isset( $options_map[ $slug ] ) ) {
            continue;
        }

        $options_map[ $slug ] = array(
            'value' => $slug,
            'label' => $label,
        );
    }

    $options = array_values( $options_map );

    usort(
        $options,
        static function ( $left, $right ) {
            return strcasecmp( (string) $left['label'], (string) $right['label'] );
        }
    );

    return $options;
}

function teca_build_city_filter_options( array $events ) {
    return teca_build_location_filter_options( $events, 'city' );
}

function teca_build_state_filter_options( array $events ) {
    return teca_build_location_filter_options( $events, 'state' );
}

function teca_build_country_filter_options( array $events ) {
    return teca_build_location_filter_options( $events, 'country' );
}

function teca_get_event_cost_filter_data( array $event ) {
    $event_id     = (int) ( $event['event_id'] ?? 0 );
    $cost_display = '';
    $cost_type    = 'free';
    $cost_value   = '0';

    if ( $event_id <= 0 ) {
        return array(
            'cost_type'  => $cost_type,
            'cost_value' => $cost_value,
        );
    }

    if ( function_exists( 'tribe_get_cost' ) ) {
        $cost_display = trim( (string) tribe_get_cost( $event_id, true ) );
    }

    if ( '' === $cost_display ) {
        $cost_display = trim( (string) get_post_meta( $event_id, '_EventCost', true ) );
    }

    if ( '' === $cost_display ) {
        return array(
            'cost_type'  => $cost_type,
            'cost_value' => $cost_value,
        );
    }

    $normalized = strtolower( $cost_display );
    $numeric    = '';

    if ( preg_match( '/[\d]+(?:\.\d+)?/', $cost_display, $matches ) ) {
        $numeric = (string) (float) $matches[0];
    }

    if ( false !== strpos( $normalized, 'free' ) || '0' === $normalized || '0.00' === $normalized ) {
        return array(
            'cost_type'  => $cost_type,
            'cost_value' => $cost_value,
        );
    }

    if ( '' !== $numeric && (float) $numeric > 0 ) {
        return array(
            'cost_type'  => 'paid',
            'cost_value' => $numeric,
        );
    }

    return array(
        'cost_type'  => 'paid',
        'cost_value' => $numeric,
    );
}

function teca_get_event_time_filter_data( array $event ) {
    $event_id   = (int) ( $event['event_id'] ?? 0 );
    $start_time = '';
    $end_time   = '';

    if ( $event_id <= 0 ) {
        return array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
            'has_time'   => false,
        );
    }

    $all_day = (bool) get_post_meta( $event_id, '_EventAllDay', true );

    if ( $all_day ) {
        return array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
            'has_time'   => false,
        );
    }

    $start = $event['dates']['start'] ?? get_post_meta( $event_id, '_EventStartDate', true );
    $end   = $event['dates']['end'] ?? get_post_meta( $event_id, '_EventEndDate', true );

    if ( $start ) {
        $start_time = wp_date( 'H:i', strtotime( (string) $start ) );
    }

    if ( $end ) {
        $end_time = wp_date( 'H:i', strtotime( (string) $end ) );
    }

    return array(
        'start_time' => $start_time,
        'end_time'   => $end_time,
        'has_time'   => '' !== $start_time,
    );
}

function teca_get_event_featured_filter_data( array $event ) {
    $event_id = (int) ( $event['event_id'] ?? 0 );
    $featured = 0;

    if ( $event_id > 0 && class_exists( __NAMESPACE__ . '\\Query' ) ) {
        $featured = Query::is_event_featured( $event_id ) ? 1 : 0;
    }

    return array(
        'featured' => $featured,
    );
}

function teca_get_event_status_filter_data( array $event ) {
    $event_id = (int) ( $event['event_id'] ?? 0 );
    $now      = (int) current_time( 'timestamp' );
    $start    = $event['dates']['start'] ?? '';
    $end      = $event['dates']['end'] ?? '';

    if ( $event_id > 0 ) {
        if ( '' === $start ) {
            $start = (string) get_post_meta( $event_id, '_EventStartDate', true );
        }

        if ( '' === $end ) {
            $end = (string) get_post_meta( $event_id, '_EventEndDate', true );
        }
    }

    if ( '' === $end ) {
        $end = $start;
    }

    $start_ts = $start ? (int) strtotime( (string) $start ) : 0;
    $end_ts   = $end ? (int) strtotime( (string) $end ) : $start_ts;
    $status   = 'upcoming';

    if ( $end_ts > 0 && $end_ts < $now ) {
        $status = 'past';
    } elseif ( $start_ts > 0 && $start_ts <= $now && ( $end_ts <= 0 || $end_ts >= $now ) ) {
        $status = 'ongoing';
    } elseif ( $start_ts > $now ) {
        $status = 'upcoming';
    }

    return array(
        'status' => $status,
    );
}

function teca_build_taxonomy_term_filter_options( array $events, $taxonomy_type ) {
    $options_map = array();
    $items_key   = 'category' === $taxonomy_type ? 'categories' : 'tags';

    foreach ( $events as $event ) {
        if ( ! is_array( $event ) ) {
            continue;
        }

        $terms = array();

        if ( ! empty( $event[ $items_key ] ) && is_array( $event[ $items_key ] ) ) {
            foreach ( $event[ $items_key ] as $term ) {
                $slug = trim( (string) ( $term['slug'] ?? '' ) );
                $name = trim( (string) ( $term['name'] ?? '' ) );

                if ( '' === $slug || '' === $name ) {
                    continue;
                }

                $terms[] = array(
                    'slug' => $slug,
                    'name' => $name,
                );
            }
        }

        if ( empty( $terms ) ) {
            $wp_terms = 'category' === $taxonomy_type
                ? teca_get_event_category_terms( $event )
                : teca_get_event_tag_terms( $event );

            foreach ( $wp_terms as $term ) {
                $slug = trim( (string) ( $term->slug ?? '' ) );
                $name = trim( (string) ( $term->name ?? '' ) );

                if ( '' === $slug || '' === $name ) {
                    continue;
                }

                $terms[] = array(
                    'slug' => $slug,
                    'name' => $name,
                );
            }
        }

        foreach ( $terms as $term ) {
            if ( isset( $options_map[ $term['slug'] ] ) ) {
                continue;
            }

            $options_map[ $term['slug'] ] = array(
                'value' => $term['slug'],
                'label' => $term['name'],
            );
        }
    }

    $options = array_values( $options_map );

    usort(
        $options,
        static function ( $left, $right ) {
            return strcasecmp( (string) $left['label'], (string) $right['label'] );
        }
    );

    return $options;
}

function teca_build_category_filter_options( array $events ) {
    return teca_build_taxonomy_term_filter_options( $events, 'category' );
}

function teca_build_tag_filter_options( array $events ) {
    return teca_build_taxonomy_term_filter_options( $events, 'tag' );
}

/**
 * Whether Filters By settings are available for the current license.
 *
 * @return bool
 */
function teca_is_filters_by_feature_available() {
    return is_pro_active_and_valid();
}

/**
 * Whether Search By settings are available for the current license.
 *
 * @return bool
 */
function teca_is_search_by_feature_available() {
    return is_pro_active_and_valid();
}

/**
 * Default Search By result limit.
 *
 * @return int
 */
function teca_get_default_search_result_limit() {
    return 10;
}

/**
 * Reset Filters By settings to the free defaults.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_filters_by_settings( array $settings ) {
    if ( teca_is_filters_by_feature_available() ) {
        return $settings;
    }

    foreach ( teca_get_filters_by_name_keys() as $filter_key ) {
        $settings[ $filter_key ] = 'off';
    }

    return $settings;
}

/**
 * Reset Search By settings to the free defaults.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_search_by_settings( array $settings ) {
    if ( teca_is_search_by_feature_available() ) {
        return $settings;
    }

    foreach ( teca_get_search_by_keys() as $search_key ) {
        $settings[ $search_key ] = 'off';
    }

    $settings['search_result_limit'] = teca_get_default_search_result_limit();

    return $settings;
}

/**
 * Reset Filters By and Search By settings for the current license context.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_filters_and_search_by_settings( array $settings ) {
    $settings = teca_sanitize_filters_by_settings( $settings );
    $settings = teca_sanitize_search_by_settings( $settings );
    $settings = teca_sanitize_query_include_exclude_cat_tag_settings( $settings );

    return $settings;
}

/**
 * Query tab Include/Exclude setting keys for categories and tags.
 *
 * @return string[]
 */
function teca_get_query_include_exclude_cat_tag_keys() {
    return array(
        'include_cat',
        'exclude_cat',
        'include_tags',
        'exclude_tags',
    );
}

/**
 * Whether Query tab Include/Exclude Categories and Tags are available.
 *
 * @return bool
 */
function teca_is_query_include_exclude_cat_tag_feature_available() {
    return is_pro_active_and_valid();
}

/**
 * Reset Query tab Include/Exclude category and tag filters for free context.
 *
 * @param array $settings Shortcode settings.
 * @return array
 */
function teca_sanitize_query_include_exclude_cat_tag_settings( array $settings ) {
    if ( teca_is_query_include_exclude_cat_tag_feature_available() ) {
        return $settings;
    }

    foreach ( teca_get_query_include_exclude_cat_tag_keys() as $setting_key ) {
        $settings[ $setting_key ] = array();
    }

    return $settings;
}

function teca_should_render_filters_by_name_bar( $settings ) {
    if ( ! teca_is_filters_by_feature_available() ) {
        return false;
    }

    if ( ! is_array( $settings ) || ! teca_supports_filters_by_name( $settings['view_type'] ?? 'grid' ) ) {
        return false;
    }

    foreach ( array( 'filter_by_date', 'filter_by_day', 'filter_by_category', 'filter_by_tag', 'filter_by_venue', 'filter_by_city', 'filter_by_state', 'filter_by_country', 'filter_by_organizer', 'filter_by_cost', 'filter_by_time', 'filter_by_featured', 'filter_by_event_status' ) as $filter_key ) {
        if ( ( $settings[ $filter_key ] ?? 'off' ) === 'on' ) {
            return true;
        }
    }

    return false;
}

function teca_supports_filters_by_name( $view_type ) {
    return in_array( (string) $view_type, array( 'grid', 'masonry', 'filter' ), true );
}

function teca_is_filter_by_name_enabled( $settings, $filter_key ) {
    if ( ! teca_is_filters_by_feature_available() ) {
        return false;
    }

    if ( ! is_array( $settings ) ) {
        return false;
    }

    $view_type = $settings['view_type'] ?? 'grid';

    if ( ! teca_supports_filters_by_name( $view_type ) ) {
        return false;
    }

    return ( $settings[ $filter_key ] ?? 'off' ) === 'on';
}

function teca_get_filters_by_name_keys() {
    return array(
        'filter_by_date',
        'filter_by_day',
        'filter_by_category',
        'filter_by_tag',
        'filter_by_venue',
        'filter_by_city',
        'filter_by_state',
        'filter_by_country',
        'filter_by_organizer',
        'filter_by_cost',
        'filter_by_time',
        'filter_by_featured',
        'filter_by_event_status',
    );
}

function teca_supports_search_by( $view_type ) {
    return teca_supports_filters_by_name( $view_type );
}

function teca_get_search_by_keys() {
    return array(
        'search_by_title',
        'search_by_venue',
        'search_by_organizer',
        'search_by_city',
    );
}

function teca_should_render_search_by_bar( $settings ) {
    if ( ! teca_is_search_by_feature_available() ) {
        return false;
    }

    if ( ! is_array( $settings ) || ! teca_supports_search_by( $settings['view_type'] ?? 'grid' ) ) {
        return false;
    }

    foreach ( teca_get_search_by_keys() as $search_key ) {
        if ( ( $settings[ $search_key ] ?? 'off' ) === 'on' ) {
            return true;
        }
    }

    return false;
}

function teca_is_search_by_enabled( $settings, $search_key ) {
    if ( ! teca_is_search_by_feature_available() ) {
        return false;
    }

    if ( ! is_array( $settings ) ) {
        return false;
    }

    $view_type = $settings['view_type'] ?? 'grid';

    if ( ! teca_supports_search_by( $view_type ) ) {
        return false;
    }

    return ( $settings[ $search_key ] ?? 'off' ) === 'on';
}

function teca_get_search_result_limit( $settings, $requested_limit = null ) {
    $limit = null !== $requested_limit ? (int) $requested_limit : (int) ( $settings['search_result_limit'] ?? 10 );

    if ( $limit < 1 ) {
        $limit = 10;
    }

    if ( $limit > 100 ) {
        $limit = 100;
    }

    return $limit;
}

function teca_text_contains_search( $haystack, $needle ) {
    $haystack = strtolower( trim( (string) $haystack ) );
    $needle   = strtolower( trim( (string) $needle ) );

    if ( '' === $needle ) {
        return true;
    }

    if ( '' === $haystack ) {
        return false;
    }

    return false !== strpos( $haystack, $needle );
}

function teca_search_query_has_active_terms( array $search_params ) {
    foreach ( array( 'search_title', 'search_venue', 'search_organizer', 'search_city' ) as $key ) {
        if ( strlen( trim( (string) ( $search_params[ $key ] ?? '' ) ) ) >= 2 ) {
            return true;
        }
    }

    return false;
}

function teca_event_matches_search_query( array $event, array $search_params, array $settings ) {
    $event_id = (int) ( $event['event_id'] ?? 0 );

    if ( ! $event_id ) {
        return false;
    }

    if ( teca_is_search_by_enabled( $settings, 'search_by_title' ) ) {
        $title_query = trim( (string) ( $search_params['search_title'] ?? '' ) );

        if ( strlen( $title_query ) >= 2 ) {
            $title = (string) ( $event['event_name'] ?? get_the_title( $event_id ) );

            if ( ! teca_text_contains_search( $title, $title_query ) ) {
                return false;
            }
        }
    }

    if ( teca_is_search_by_enabled( $settings, 'search_by_venue' ) ) {
        $venue_query = trim( (string) ( $search_params['search_venue'] ?? '' ) );

        if ( strlen( $venue_query ) >= 2 ) {
            $venue_name = (string) ( $event['venue']['title'] ?? '' );

            if ( '' === $venue_name && ! empty( $event['venue_id'] ) ) {
                $venue_name = (string) get_the_title( (int) $event['venue_id'] );
            }

            if ( ! teca_text_contains_search( $venue_name, $venue_query ) ) {
                return false;
            }
        }
    }

    if ( teca_is_search_by_enabled( $settings, 'search_by_organizer' ) ) {
        $organizer_query = trim( (string) ( $search_params['search_organizer'] ?? '' ) );

        if ( strlen( $organizer_query ) >= 2 ) {
            $organizer_names = array();

            if ( ! empty( $event['organizers'] ) && is_array( $event['organizers'] ) ) {
                foreach ( $event['organizers'] as $organizer ) {
                    $name = trim( (string) ( $organizer['title'] ?? '' ) );

                    if ( $name ) {
                        $organizer_names[] = $name;
                    }
                }
            }

            if ( empty( $organizer_names ) && function_exists( 'tribe_get_organizer_ids' ) ) {
                foreach ( (array) tribe_get_organizer_ids( $event_id ) as $organizer_id ) {
                    $name = trim( (string) get_the_title( (int) $organizer_id ) );

                    if ( $name ) {
                        $organizer_names[] = $name;
                    }
                }
            }

            if ( ! teca_text_contains_search( implode( ' ', $organizer_names ), $organizer_query ) ) {
                return false;
            }
        }
    }

    if ( teca_is_search_by_enabled( $settings, 'search_by_city' ) ) {
        $city_query = trim( (string) ( $search_params['search_city'] ?? '' ) );

        if ( strlen( $city_query ) >= 2 ) {
            $city = (string) ( $event['venue']['city'] ?? '' );

            if ( '' === $city && ! empty( $event['venue_id'] ) ) {
                $city = (string) get_post_meta( (int) $event['venue_id'], '_VenueCity', true );
            }

            if ( ! teca_text_contains_search( $city, $city_query ) ) {
                return false;
            }
        }
    }

    return true;
}

function teca_filter_events_by_search_query( array $events, array $settings, array $search_params ) {
    if ( ! teca_search_query_has_active_terms( $search_params ) ) {
        return $events;
    }

    $limit   = teca_get_search_result_limit( $settings, $search_params['result_limit'] ?? null );
    $matched = array();

    foreach ( $events as $event ) {
        if ( ! is_array( $event ) ) {
            continue;
        }

        if ( ! teca_event_matches_search_query( $event, $search_params, $settings ) ) {
            continue;
        }

        $matched[] = $event;

        if ( count( $matched ) >= $limit ) {
            break;
        }
    }

    return $matched;
}

function teca_get_calendar_filter_config( $filter_type ) {
    $configs = array(
        'daily'   => array(
            'wrapper_class' => 'teca-daily-date-filter',
            'select_class'  => 'teca-daily-date-select',
            'label'         => __( 'Select Day', 'the-events-calendar-addon2' ),
            'message'       => __( 'No events found for this day.', 'the-events-calendar-addon2' ),
            'message_class' => 'teca-daily-empty-message',
        ),
        'weekly'  => array(
            'wrapper_class' => 'teca-weekly-date-filter',
            'select_class'  => 'teca-weekly-date-select',
            'label'         => __( 'Select Week', 'the-events-calendar-addon2' ),
            'message'       => __( 'No events found for this week.', 'the-events-calendar-addon2' ),
            'message_class' => 'teca-weekly-empty-message',
        ),
        'monthly' => array(
            'wrapper_class' => 'teca-monthly-date-filter',
            'select_class'  => 'teca-monthly-date-select',
            'label'         => __( 'Select Month', 'the-events-calendar-addon2' ),
            'message'       => __( 'No events found for this month.', 'the-events-calendar-addon2' ),
            'message_class' => 'teca-monthly-empty-message',
        ),
        'quarterly' => array(
            'wrapper_class'  => 'teca-quarterly-date-filter',
            'select_class'   => 'teca-quarterly-date-select',
            'label'          => __( 'Select Quarter', 'the-events-calendar-addon2' ),
            'message'        => __( 'No events found for this quarter.', 'the-events-calendar-addon2' ),
            /* translators: %s: event category, date range, or selected filter label. */
            'empty_template' => __( 'No events found for %s.', 'the-events-calendar-addon2' ),
            'message_class'  => 'teca-quarterly-empty-message',
        ),
        'yearly' => array(
            'wrapper_class'  => 'teca-yearly-date-filter',
            'select_class'   => 'teca-yearly-date-select',
            'label'          => __( 'Select Year', 'the-events-calendar-addon2' ),
            'message'        => __( 'No events found for this year.', 'the-events-calendar-addon2' ),
            /* translators: %s: event category, date range, or selected filter label. */
            'empty_template' => __( 'No events found for %s.', 'the-events-calendar-addon2' ),
            'message_class'  => 'teca-yearly-empty-message',
        ),
    );

    return $configs[ (string) $filter_type ] ?? $configs['daily'];
}

function teca_get_event_date_label( $date_key, $format = '' ) {
	$date_key = (string) $date_key;
	$format   = (string) $format;

	if ( '' === $date_key ) {
		return '';
	}

	$timestamp = strtotime( $date_key );

	if ( ! $timestamp ) {
		return '';
	}

	if ( '' === $format ) {
		$format = teca_get_layout_date_format();
	}

	if ( function_exists( 'wp_date' ) ) {
		return (string) wp_date( $format, $timestamp );
	}

	return (string) date_i18n( $format, $timestamp );
}

function teca_get_calendar_filter_options( array $events, $filter_type, $date_label_format = '' ) {
    if ( 'quarterly' === (string) $filter_type ) {
        return teca_get_quarter_filter_options( $events );
    }

    if ( 'yearly' === (string) $filter_type ) {
        return teca_get_year_filter_options( $events );
    }

    $options = array(
        'all' => __( 'All Events', 'the-events-calendar-addon2' ),
    );
    $labels  = array();

    foreach ( $events as $event ) {
        switch ( (string) $filter_type ) {
            case 'weekly':
                $week_key = teca_get_event_week_key( $event );

                if ( $week_key ) {
                    $labels[ $week_key ] = teca_get_event_week_label( $week_key );
                }
                break;

            case 'monthly':
                $month_key = teca_get_event_month_key( $event );

                if ( $month_key ) {
                    $labels[ $month_key ] = teca_get_event_month_label( $month_key );
                }
                break;

            case 'daily':
            default:
                $date_key = teca_get_event_date_key( $event );

                if ( $date_key ) {
                    $labels[ $date_key ] = teca_get_event_date_label( $date_key, $date_label_format );
                }
                break;
        }
    }

    ksort( $labels );

    return array_merge( $options, $labels );
}

function teca_render_calendar_date_filter( array $events, $filter_type, $layout_id = '', array $args = array() ) {
    if ( teca_is_shared_calendar_filter_enabled() ) {
        return '';
    }

    if ( empty( $events ) ) {
        return '';
    }

    $filter_type       = sanitize_key( (string) $filter_type );
    $layout_id         = sanitize_key( (string) $layout_id );
    $date_label_format = isset( $args['date_label_format'] ) ? (string) $args['date_label_format'] : '';
    $options           = teca_get_calendar_filter_options( $events, $filter_type, $date_label_format );
    $config            = teca_get_calendar_filter_config( $filter_type );

    if ( 'yearly' === $filter_type ) {
        $options = teca_normalize_year_filter_options( $options );
    }

    if ( empty( $options ) ) {
        return '';
    }

    ob_start();

    $template = Template_Loader::locate_template( 'partials/teca-calendar-date-filter.php' );

    if ( ! is_wp_error( $template ) ) {
        include $template;
    }

    return (string) ob_get_clean();
}

function teca_render_calendar_filter_bar( array $events, $filter_mode, $layout_id = '', array $settings = array() ) {
    $filter_mode = sanitize_key( (string) $filter_mode );
    $layout_id   = sanitize_key( (string) $layout_id );

    if ( ! in_array( $filter_mode, array( 'daily', 'weekly', 'monthly', 'quarterly', 'yearly' ), true ) ) {
        $filter_mode = 'daily';
    }

    ob_start();

    $template = Template_Loader::locate_template( 'partials/teca-calendar-filter-bar.php' );

    if ( ! is_wp_error( $template ) ) {
        include $template;
    }

    return (string) ob_get_clean();
}

function teca_render_calendar_layout( $view_type, array $settings ) {
    Calendar_Renderer::mark_enqueue_assets();
    return Calendar_Renderer::render_layout( $view_type, $settings );
}

/**
 * Extract a scalar value from saved admin select settings.
 *
 * @param mixed  $value   Raw saved value.
 * @param string $default Default fallback.
 * @return string
 */
function teca_extract_single_page_setting_value( $value, $default = '' ) {
	if ( is_array( $value ) ) {
		if ( isset( $value['value'] ) ) {
			$value = $value['value'];
		} elseif ( isset( $value['single_page_style'] ) ) {
			$value = $value['single_page_style'];
		} else {
			return $default;
		}
	}

	if ( is_object( $value ) ) {
		if ( isset( $value->value ) ) {
			$value = $value->value;
		} else {
			return $default;
		}
	}

	$value = trim( (string) $value );

	return '' !== $value ? $value : $default;
}

/**
 * Read saved single page layout settings.
 *
 * @return array
 */
function teca_get_single_page_layout_settings() {
	$layout = get_option( 'gs_teca_shortcode_layout', array() );

	if ( ! is_array( $layout ) ) {
		return array();
	}

	return $layout;
}

/**
 * Repair legacy layout saves where select values were stored as objects/arrays.
 *
 * @return void
 */
function teca_repair_single_page_layout_option() {
	$layout = get_option( 'gs_teca_shortcode_layout', array() );

	if ( ! is_array( $layout ) || ! isset( $layout['single_page_style'] ) ) {
		return;
	}

	$raw = $layout['single_page_style'];

	if ( ! is_array( $raw ) && ! is_object( $raw ) ) {
		return;
	}

	$layout['single_page_style'] = teca_extract_single_page_setting_value( $raw, 'default' );
	update_option( 'gs_teca_shortcode_layout', $layout, 'yes' );
}

add_action(
	'init',
	function () {
		teca_repair_single_page_layout_option();
	},
	1
);

/**
 * Build map/location data for a single event page.
 *
 * @param int $event_id Event post ID.
 * @return array<string, mixed>
 */
function teca_get_single_event_map_data( $event_id ) {
	$event_id = (int) $event_id;

	$data = array(
		'venue_id'     => 0,
		'venue_name'   => '',
		'address'      => '',
		'city'         => '',
		'state'        => '',
		'zip'          => '',
		'country'      => '',
		'full_address' => '',
		'lat'          => '',
		'lng'          => '',
		'map_link'     => '',
		'embed_html'   => '',
		'has_location' => false,
	);

	if ( ! $event_id ) {
		return $data;
	}

	$venue_id = 0;

	if ( class_exists( '\GS_TECA\Query' ) ) {
		$venue_id = \GS_TECA\Query::get_event_venue_id( $event_id );
	} elseif ( function_exists( 'tribe_get_venue_id' ) ) {
		$venue_id = (int) tribe_get_venue_id( $event_id );
	}

	if ( ! $venue_id ) {
		$venue_id = (int) get_post_meta( $event_id, '_EventVenueID', true );
	}

	$data['venue_id'] = $venue_id;

	if ( function_exists( 'tribe_get_venue' ) ) {
		$data['venue_name'] = trim( (string) tribe_get_venue( $event_id ) );
	}

	if ( function_exists( 'tribe_get_address' ) ) {
		$data['address'] = trim( (string) tribe_get_address( $event_id ) );
	}

	if ( function_exists( 'tribe_get_city' ) ) {
		$data['city'] = trim( (string) tribe_get_city( $event_id ) );
	}

	if ( function_exists( 'tribe_get_stateprovince' ) ) {
		$data['state'] = trim( (string) tribe_get_stateprovince( $event_id ) );
	}

	if ( function_exists( 'tribe_get_zip' ) ) {
		$data['zip'] = trim( (string) tribe_get_zip( $event_id ) );
	}

	if ( function_exists( 'tribe_get_country' ) ) {
		$data['country'] = trim( (string) tribe_get_country( $event_id ) );
	}

	if ( $venue_id ) {
		$venue_details = class_exists( '\GS_TECA\Query' ) ? \GS_TECA\Query::get_venue_details( $venue_id ) : array();

		if ( empty( $data['venue_name'] ) && ! empty( $venue_details['title'] ) ) {
			$data['venue_name'] = trim( (string) $venue_details['title'] );
		}

		if ( '' === $data['address'] && ! empty( $venue_details['address'] ) ) {
			$data['address'] = trim( (string) $venue_details['address'] );
		}

		if ( '' === $data['city'] && ! empty( $venue_details['city'] ) ) {
			$data['city'] = trim( (string) $venue_details['city'] );
		}

		if ( '' === $data['state'] && ! empty( $venue_details['state'] ) ) {
			$data['state'] = trim( (string) $venue_details['state'] );
		}

		if ( '' === $data['zip'] && ! empty( $venue_details['zip'] ) ) {
			$data['zip'] = trim( (string) $venue_details['zip'] );
		}

		if ( '' === $data['country'] && ! empty( $venue_details['country'] ) ) {
			$data['country'] = trim( (string) $venue_details['country'] );
		}

		if ( '' === $data['lat'] && ! empty( $venue_details['lat'] ) ) {
			$data['lat'] = trim( (string) $venue_details['lat'] );
		}

		if ( '' === $data['lng'] && ! empty( $venue_details['lng'] ) ) {
			$data['lng'] = trim( (string) $venue_details['lng'] );
		}
	}

	if ( function_exists( 'tribe_get_full_address' ) ) {
		$data['full_address'] = trim( wp_strip_all_tags( (string) tribe_get_full_address( $event_id ) ) );
	}

	if ( '' === $data['full_address'] ) {
		$address_parts = array_filter(
			array_map(
				'trim',
				array(
					$data['address'],
					$data['city'],
					trim( $data['state'] . ( $data['zip'] ? ' ' . $data['zip'] : '' ) ),
					$data['country'],
				)
			)
		);
		$data['full_address'] = implode( ', ', $address_parts );
	}

	if ( function_exists( 'tribe_get_embedded_map' ) ) {
		$data['embed_html'] = trim( (string) tribe_get_embedded_map( $event_id ) );
	}

	if ( function_exists( 'tribe_get_map_link' ) ) {
		$data['map_link'] = trim( (string) tribe_get_map_link( $event_id ) );
	}

	$data['has_location'] = teca_single_event_map_has_location( $data );

	return $data;
}

/**
 * Whether map data contains a renderable location.
 *
 * @param array<string, mixed> $map_data Map data array.
 * @return bool
 */
function teca_single_event_map_has_location( array $map_data ) {
	if ( ! empty( $map_data['embed_html'] ) ) {
		return true;
	}

	$lat = trim( (string) ( $map_data['lat'] ?? '' ) );
	$lng = trim( (string) ( $map_data['lng'] ?? '' ) );

	if ( '' !== $lat && '' !== $lng && is_numeric( $lat ) && is_numeric( $lng ) ) {
		return true;
	}

	if ( ! empty( $map_data['full_address'] ) ) {
		return true;
	}

	if ( ! empty( $map_data['map_link'] ) ) {
		return true;
	}

	return false;
}

/**
 * Resolve iframe embed URL for a single event map.
 *
 * @param array<string, mixed> $map_data Map data array.
 * @return string
 */
function teca_get_single_event_map_embed_url( array $map_data ) {
	if ( ! empty( $map_data['embed_html'] ) ) {
		return '';
	}

	$lat = trim( (string) ( $map_data['lat'] ?? '' ) );
	$lng = trim( (string) ( $map_data['lng'] ?? '' ) );

	if ( '' !== $lat && '' !== $lng && is_numeric( $lat ) && is_numeric( $lng ) ) {
		return 'https://maps.google.com/maps?q=' . rawurlencode( $lat . ',' . $lng ) . '&output=embed';
	}

	$full_address = trim( (string) ( $map_data['full_address'] ?? '' ) );

	if ( '' !== $full_address ) {
		return 'https://maps.google.com/maps?q=' . rawurlencode( $full_address ) . '&output=embed';
	}

	return '';
}

/**
 * Resolve external map link for a single event.
 *
 * @param array<string, mixed> $map_data Map data array.
 * @return string
 */
function teca_get_single_event_map_external_link( array $map_data ) {
	$map_link = trim( (string) ( $map_data['map_link'] ?? '' ) );

	if ( '' !== $map_link ) {
		if ( false !== strpos( $map_link, '<a' ) && preg_match( '/href=[\'"]([^\'"]+)[\'"]/', $map_link, $matches ) ) {
			return esc_url_raw( $matches[1] );
		}

		return esc_url_raw( $map_link );
	}

	$lat = trim( (string) ( $map_data['lat'] ?? '' ) );
	$lng = trim( (string) ( $map_data['lng'] ?? '' ) );

	if ( '' !== $lat && '' !== $lng && is_numeric( $lat ) && is_numeric( $lng ) ) {
		return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $lat . ',' . $lng );
	}

	$full_address = trim( (string) ( $map_data['full_address'] ?? '' ) );

	if ( '' !== $full_address ) {
		return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $full_address );
	}

	return '';
}

/**
 * Allowed HTML for TEC embedded map markup.
 *
 * @return array<string, array<string, bool>>
 */
function teca_get_single_event_map_allowed_html() {
	return array(
		'iframe' => array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'style'           => true,
			'allowfullscreen' => true,
			'loading'         => true,
			'title'           => true,
			'referrerpolicy'  => true,
			'class'           => true,
			'id'              => true,
			'aria-hidden'     => true,
			'tabindex'        => true,
		),
		'div'    => array(
			'class' => true,
			'style' => true,
			'id'    => true,
		),
	);
}

require_once GS_TECA_PLUGIN_DIR . 'includes/teca-date-format.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-card-controls.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-google-calendar.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-view-details.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-preference-text.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-table-render.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-related-events.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-popup-render.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-single-event-status.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-single-render.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-typography-controls.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-style-defaults.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-color-controls.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/teca-popup-detail-controls.php';