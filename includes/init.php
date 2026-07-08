<?php
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
    if ( is_pro_compatible() ) {
        /**
         * Activation redirects
         */
        register_activation_hook( GS_TECA_PLUGIN_FILE, 'GS_TECA\on_activation' );

        /**
         * Init Appsero
         */

        /**
         * Load Main Plugin
         */
        require_once GS_TECA_PLUGIN_DIR . 'includes/plugin.php';
    }
    
    /**
     * Remove Reviews Metadata on plugin Deactivation.
     */
    register_deactivation_hook( GS_TECA_PLUGIN_FILE, 'GS_TECA\on_deactivation' );
    
    /**
     * Plugins action links
     */
    add_filter( 'plugin_action_links_' . plugin_basename( GS_TECA_PLUGIN_FILE ), 'GS_TECA\add_pro_link' );

    /**
     * Plugins Load Text Domain
     */
    add_action( 'init', 'GS_TECA\gs_load_textdomain' );
    add_action('init', function () {
        add_post_type_support('tribe_events', 'page-attributes');
    });

    

}, -20 );

