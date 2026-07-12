<?php 
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

class Plugin {

    private static $_instance;
    public $hooks;
    public $scripts;
    public $shortcode;
    public $builder;
    public $integrations;
    public $template_loader;
    public $single_page_filter;

    public static function get_instance() {

        if ( ! self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct() {
        
        $this->hooks           = new Hooks;
        $this->scripts         = new Scripts;
        $this->template_loader = new Template_Loader;
        $this->shortcode       = new Shortcode;
        $this->builder         = new Builder;
        $this->integrations    = new Integrations;
        require_once GS_TECA_PLUGIN_DIR . 'includes/asset-generator/gs-load-asset-generator.php'; 
        require_once GS_TECA_PLUGIN_DIR . 'includes/gs-common-pages/gs-teca-common-pages.php';
        require_once GS_TECA_PLUGIN_DIR . 'includes/sortable.php';
        require_once GS_TECA_PLUGIN_DIR . 'includes/term-order/term-order.php';
        require_once GS_TECA_PLUGIN_DIR . 'includes/demo-data/dummy-data.php';
        $this->init_typography_fonts();

    }

    protected function init_typography_fonts() {
        require_once GS_TECA_PLUGIN_DIR . 'includes/shortcode-builder/shortcode_builder_fonts.php';
        require_once GS_TECA_PLUGIN_DIR . 'includes/shortcode-builder/shortcode_builder_fonts_loader.php';
        GS_TECA_Shortcode_Fonts_Loader::get_instance();
    }

}

function plugin() {
    return Plugin::get_instance();
}

add_action('plugins_loaded', function() {
    plugin();
}, 0 );