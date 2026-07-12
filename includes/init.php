<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('plugins_loaded', function() {
    
    /**
     * Single event template override must always register.
     * TEC single pages are not shortcode-scoped, so this reads global layout settings.
     */
    require_once GS_TECA_PLUGIN_DIR . 'includes/single-page-filter.php';
    new Single_Page_Filter();

    /**
     * Compatibility check with Pro plugin
     */
    register_activation_hook( GS_TECA_PLUGIN_FILE, 'GS_TECA\on_activation' );

    require_once GS_TECA_PLUGIN_DIR . 'includes/plugin.php';
    
    /**
     * Remove Reviews Metadata on plugin Deactivation.
     */
    register_deactivation_hook( GS_TECA_PLUGIN_FILE, 'GS_TECA\on_deactivation' );
    
    /**
     * Text domain loading is handled by WordPress.org language packs.
     */
    add_action('init', function () {
        add_post_type_support('tribe_events', 'page-attributes');
    });

    

}, -20 );

