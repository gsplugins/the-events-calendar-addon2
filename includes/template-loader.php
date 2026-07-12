<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;
use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Template_Loader {

    private static $plugin_template_path = '';

    private static $pro_plugin_template_path = '';

    private static $theme_path = '';

    private static $child_theme_path = '';

    public function __construct() {

        self::$plugin_template_path = GS_TECA_PLUGIN_DIR . 'templates/';

        add_action( 'init', [$this, 'set_theme_template_path'] );

    }

    public function set_theme_template_path() {

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        $dir = apply_filters( 'gs_teca_templates_folder', 'the-events-calendar-addon2' );

        if ( $dir ) {
            $dir = '/' . trailingslashit( ltrim( $dir, '/\\' ) );
            self::$theme_path = get_template_directory() . $dir;

            if ( is_child_theme() ) {
                self::$child_theme_path = get_stylesheet_directory() . $dir;
            }
        }

    }

    public static function locate_template( $template_file ) {
        
        // Default path
        $path = self::$plugin_template_path;
        
        // Check if requested file exist in plugin
        if ( ! empty(self::$pro_plugin_template_path) && file_exists( self::$pro_plugin_template_path . $template_file ) ) {
            $path = self::$pro_plugin_template_path;
        } else {
            if ( ! file_exists( $path . $template_file ) ) {
                return new WP_Error( 'gs_teca_template_not_found', __( 'Template file not found - GS Plugins', 'the-events-calendar-addon2' ) );
            }
        }

        // Override default template if exist from theme
        if ( file_exists( self::$theme_path . $template_file ) ) $path = self::$theme_path;
        
        if ( is_child_theme() ) {
            // Override default template if exist from child theme
            if ( file_exists( self::$child_theme_path . $template_file ) ) $path = self::$child_theme_path;
        }

        // Return template path, it can be default or overridden by theme
        return $path . $template_file;

    }

    public static function load_template( $template_file, $args = [] ) {

        if ( isset( $args['display'] ) && ! $args['display'] ) return;
        
        $template = self::locate_template( $template_file );

        if ( is_wp_error( $template ) ) return;

        if ( is_array( $args ) && isset( $args ) ) extract( $args );

        include $template;

    }

}