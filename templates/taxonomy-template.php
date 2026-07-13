<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.
/**
 * Custom template for taxonomy archives using GS Posts Grid
 */

get_header(); 

// Get the layout settings
$layout = get_option( GS_TECA_SHORTCODE_LAYOUT_OPTION, array() );
$shortcode_id = '';

if (is_category() && !empty($layout['event_cat_shortcode'])) {
    $shortcode_id = $layout['event_cat_shortcode'];
} elseif (is_tag() && !empty($layout['event_tag_shortcode'])) {
    $shortcode_id = $layout['event_tag_shortcode'];
} elseif (is_date() && !empty($layout['event_date_shortcode'])) {
    $shortcode_id = $layout['event_date_shortcode'];
}

if (!empty($shortcode_id)) {
    echo do_shortcode('[gs-teca id="' . $shortcode_id . '"]');
} else {
    // Fallback to default archive display
    if (have_posts()) {
        echo '<div class="gs-teca-archive">';
        while (have_posts()) {
            the_post();
            // Your default post display here
            the_title('<h2>', '</h2>');
            the_excerpt();
        }
        echo '</div>';
        
        // Pagination
        the_posts_pagination();
    } else {
        echo '<p>No posts found.</p>';
    }
}

get_footer();
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

