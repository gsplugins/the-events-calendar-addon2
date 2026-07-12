<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

final class Builder {

    private $option_name = 'gs_teca_shortcode_prefs';
    private $layout_option_name = 'gs_teca_shortcode_layout';
    private $fields_visibility_option_name = 'gs_teca_visibility_settings';
    private $archive_wp_query = null;

    private function verify_ajax_capability() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => esc_html__( 'Unauthorised Request', 'the-events-calendar-addon2' ),
                ),
                401
            );
        }
    }

    private function verify_ajax_nonce( $action, $query_arg = '_wpnonce' ) {
        if ( ! check_ajax_referer( $action, $query_arg, false ) ) {
            wp_send_json_error(
                array(
                    'message' => esc_html__( 'Invalid nonce.', 'the-events-calendar-addon2' ),
                ),
                403
            );
        }
    }

    /**
     * POST helpers — nonce and capability are verified by callers before use.
     */
    // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    private function get_post_text( $key, $default = '' ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return $default;
        }

        return sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
    }

    private function get_post_absint( $key, $default = 0 ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return $default;
        }

        return absint( wp_unslash( $_POST[ $key ] ) );
    }

    private function get_post_array( $key ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return array();
        }

        return map_deep( (array) wp_unslash( $_POST[ $key ] ), 'sanitize_text_field' );
    }

    private function get_post_ids( $key = 'ids' ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return array();
        }

        $ids = wp_unslash( $_POST[ $key ] );
        $ids = is_array( $ids ) ? $ids : explode( ',', (string) $ids );

        return array_filter( array_map( 'absint', $ids ) );
    }

    private function get_post_shortcode_settings() {
        if ( ! isset( $_POST['shortcode_settings'] ) ) {
            return array();
        }

        $raw = wp_unslash( $_POST['shortcode_settings'] );

        if ( is_string( $raw ) ) {
            $raw = wp_kses_post( $raw );
            $decoded = json_decode( $raw, true );

            return is_array( $decoded ) ? map_deep( $decoded, 'sanitize_text_field' ) : array();
        }

        return is_array( $raw ) ? map_deep( $raw, 'sanitize_text_field' ) : array();
    }
    // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

    public function __construct() {

        add_action('admin_menu', array($this, 'register_sub_menu'));
        add_action('admin_enqueue_scripts', array($this, 'scripts'));
        add_action('wp_enqueue_scripts', array($this, 'preview_scripts'));

        add_action('wp_ajax_gsteca_create_shortcode', array($this, 'create_shortcode'));
        add_action('wp_ajax_gsteca_clone_shortcode', array($this, 'clone_shortcode'));
        add_action('wp_ajax_gsteca_get_shortcode', array($this, 'get_shortcode'));
        add_action('wp_ajax_gsteca_update_shortcode', array($this, 'update_shortcode'));
        add_action('wp_ajax_gsteca_delete_shortcodes', array($this, 'delete_shortcodes'));
        add_action('wp_ajax_gsteca_temp_save_shortcode_settings', array($this, 'temp_save_shortcode_settings'));
        add_action('wp_ajax_gsteca_get_shortcodes', array($this, 'get_shortcodes'));

        add_action('wp_ajax_gsteca_get_shortcode_pref', array($this, 'get_shortcode_pref'));
        add_action('wp_ajax_gsteca_save_shortcode_pref', array($this, 'save_shortcode_pref'));

        add_action('wp_ajax_gsteca_get_shortcode_layout', array($this, 'get_shortcode_layout'));
        add_action('wp_ajax_gsteca_save_shortcode_layout', array($this, 'save_shortcode_layout'));

        add_action( 'wp_ajax_gsteca_get_layout_options', array($this, 'get_layout_options') );
        add_action('template_redirect', array($this, 'override_taxonomy_templates'));

        add_action('template_include', array($this, 'populate_shortcode_preview'));
        add_action('show_admin_bar', array($this, 'hide_admin_bar_from_preview'));

        add_action('wp_ajax_gsteca_get_fields_visibility_settings', array($this, 'get_fields_visibility_settings') );
		add_action('wp_ajax_gsteca_save_fields_visibility_settings', array($this, 'save_fields_visibility_settings') );

        add_action('wp_ajax_update_teca_popup_visibility_order', array( $this, 'update_popup_visibility_order' ) );

        return $this;
    }

    public function hide_admin_bar_from_preview( $visibility ) {
        if ( $this->is_shortcode_preview() ) return false;
        return $visibility;
    }

    public function add_shortcode_body_class( $classes ) {
        if ( $this->is_shortcode_preview() ) return array_merge( $classes, array( 'gs-teca-shortcode-preview--page' ) );
        return $classes;
    }

    public function populate_shortcode_preview( $template ) {

        global $wp, $wp_query;
        
        if ( $this->is_shortcode_preview() ) {

            // Create our fake post
            $post_id = 0;
            $post = new \stdClass();
            $post->ID = $post_id;
            $post->post_author = 1;
            $post->post_date = current_time( 'mysql' );
            $post->post_date_gmt = current_time( 'mysql', 1 );
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            $post->post_title = __('Shortcode Preview', 'the-events-calendar-addon2');
            $preview_id = isset( $_REQUEST['gs_teca_shortcode_preview'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preview query arg is validated for admin preview pages only.
                ? sanitize_key( wp_unslash( $_REQUEST['gs_teca_shortcode_preview'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                : '';
            $post->post_content = '[gs-teca preview="yes" id="' . esc_attr( $preview_id ) . '"]';
            $post->post_status = 'publish';
            $post->comment_status = 'closed';
            $post->ping_status = 'closed';
            $post->post_name = 'fake-page-' . wp_rand( 1, 99999 ); // append random number to avoid clash
            $post->post_type = 'page';
            $post->filter = 'raw'; // important!

            // Convert to WP_Post object
            $wp_post = new \WP_Post( $post );

            // Add the fake post to the cache
            wp_cache_add( $post_id, $wp_post, 'posts' );

            // Update the main query
            $wp_query->post = $wp_post;
            $wp_query->posts = array( $wp_post );
            $wp_query->queried_object = $wp_post;
            $wp_query->queried_object_id = $post_id;
            $wp_query->found_posts = 1;
            $wp_query->post_count = 1;
            $wp_query->max_num_pages = 1; 
            $wp_query->is_page = true;
            $wp_query->is_singular = true; 
            $wp_query->is_single = false; 
            $wp_query->is_attachment = false;
            $wp_query->is_archive = false; 
            $wp_query->is_category = false;
            $wp_query->is_tag = false; 
            $wp_query->is_tax = false;
            $wp_query->is_author = false;
            $wp_query->is_date = false;
            $wp_query->is_year = false;
            $wp_query->is_month = false;
            $wp_query->is_day = false;
            $wp_query->is_time = false;
            $wp_query->is_search = false;
            $wp_query->is_feed = false;
            $wp_query->is_comment_feed = false;
            $wp_query->is_trackback = false;
            $wp_query->is_home = false;
            $wp_query->is_embed = false;
            $wp_query->is_404 = false; 
            $wp_query->is_paged = false;
            $wp_query->is_admin = false; 
            $wp_query->is_preview = false; 
            $wp_query->is_robots = false; 
            $wp_query->is_posts_page = false;
            $wp_query->is_post_type_archive = false;

            $GLOBALS['wp_query'] = $wp_query;
            $wp->register_globals();


            include GS_TECA_PLUGIN_DIR . 'includes/shortcode-builder/preview.php';

            return;

        }

        return $template;

    }

    public static function is_shortcode_preview() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return isset( $_REQUEST['gs_teca_shortcode_preview'] ) && ! empty( $_REQUEST['gs_teca_shortcode_preview'] );
    }

    public function register_sub_menu() {

        add_menu_page(
            __('Events Addon', 'the-events-calendar-addon2'),
            __('Events Addon', 'the-events-calendar-addon2'),
            'manage_options',
            'gs-the-events-calendar-addon',
            array($this, 'view'),
            GS_TECA_PLUGIN_URI . '/assets/img/events.svg',
            GS_TECA_MENU_POSITION
        );

        add_submenu_page(
            'gs-the-events-calendar-addon',
            __('Shortcodes ', 'the-events-calendar-addon2'),
            __('Shortcodes', 'the-events-calendar-addon2'),
            'manage_options',
            'gs-the-events-calendar-addon',
            array($this, 'view'),
            10
        );

        add_submenu_page(
            'gs-the-events-calendar-addon',
            __( 'Preferences', 'the-events-calendar-addon2' ),
            __( 'Preferences', 'the-events-calendar-addon2' ),
            'manage_options',
            'gs-the-events-calendar-addon#/preferences',
            array( $this, 'view' )
        );

        add_submenu_page(
            'gs-the-events-calendar-addon',
            __( 'Layout', 'the-events-calendar-addon2' ),
            __( 'Layout', 'the-events-calendar-addon2' ),
            'manage_options',
            'gs-the-events-calendar-addon#/layout',
            array( $this, 'view' )
        );

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action( 'gsteca_shortcode_submenu' );

    }

    public function view() {

        include_once GS_TECA_PLUGIN_DIR . '/includes/shortcode-builder/page.php';
    }

    public function scripts($hook) {

        if ( 'toplevel_page_gs-the-events-calendar-addon' !== $hook ) {
            return;
        }

        $admin_css = GS_TECA_PLUGIN_DIR . 'assets/admin/css/gs-teca-shortcode.min.css';
        $admin_js  = GS_TECA_PLUGIN_DIR . 'assets/admin/js/gs-teca-shortcode.min.js';
        $css_ver   = file_exists( $admin_css ) ? (string) filemtime( $admin_css ) : GS_TECA_VERSION;
        $js_ver    = file_exists( $admin_js ) ? (string) filemtime( $admin_js ) : GS_TECA_VERSION;

        wp_register_style('gs-zmdi-fonts', GS_TECA_PLUGIN_URI . 'assets/libs/material-design-iconic-font/css/material-design-iconic-font.min.css', '', GS_TECA_VERSION, 'all');
        wp_register_style('gs-font-awesome-5', GS_TECA_PLUGIN_URI . 'assets/libs/font-awesome/css/font-awesome.min.css', '', GS_TECA_VERSION, 'all');

        wp_register_style('gs-teca-shortcode', GS_TECA_PLUGIN_URI . 'assets/admin/css/gs-teca-shortcode.min.css', array('gs-zmdi-fonts', 'gs-font-awesome-5'), $css_ver, 'all');
        wp_register_script('gs-teca-shortcode', GS_TECA_PLUGIN_URI . 'assets/admin/js/gs-teca-shortcode.min.js', array('jquery', 'code-editor'), $js_ver, true);

        wp_enqueue_code_editor(
            array(
                'type'       => 'text/css',
                'codemirror' => array(
                    'lineNumbers' => true,
                ),
            )
        );
        wp_enqueue_script( 'code-editor' );
        wp_enqueue_style( 'code-editor' );

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action('gs_teca_register_scripts');

        wp_localize_script('gs-teca-shortcode', '_gsteca_data', $this->get_localized_data());

        wp_enqueue_style( 'gs-teca-shortcode' );
        wp_enqueue_script( 'gs-teca-shortcode' );

        wp_localize_script(
            'gs-teca-shortcode',
            'GS_TECA_POPUP_ORDER_DATA',
            [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'update_teca_popup_visibility_order' ),
                'action'  => 'update_teca_popup_visibility_order',
            ]
        );

    }

    public function get_localized_data() {

        $data = array(
            "nonce" => array(
                "get_shortcode"                    => wp_create_nonce("_gsteca_get_shortcode_gs_"),
                "create_shortcode"                 => wp_create_nonce("_gsteca_create_shortcode_gs_"),
                "clone_shortcode"                  => wp_create_nonce("_gsteca_clone_shortcode_gs_"),
                "update_shortcode"                 => wp_create_nonce("_gsteca_update_shortcode_gs_"),
                "delete_shortcodes"                => wp_create_nonce("_gsteca_delete_shortcodes_gs_"),
                "temp_save_shortcode_settings"     => wp_create_nonce("_gsteca_temp_save_shortcode_settings_gs_"),
                "save_shortcode_pref"              => wp_create_nonce("_gsteca_save_shortcode_pref_gs_"),
                "save_shortcode_layout" 	       => wp_create_nonce("_gsteca_save_shortcode_layout_gs_"),
                "get_shortcode_layout" 	           => wp_create_nonce("_gsteca_get_shortcode_layout_gs_"),
                "get_shortcode_layout_options" 	   => wp_create_nonce("_gsteca_get_shortcode_layout_options_gs_"),
                "import_gsteca_demo"               => wp_create_nonce("_gsteca_import_gsteca_demo_gs_"),            ),
            "ajaxurl" => admin_url("admin-ajax.php"),
            "adminurl" => admin_url(),
            "siteurl" => home_url(),
        );

        $data['shortcode_settings'] = $this->get_shortcode_default_settings();
        $data['fonts_data']         = $this->get_fonts_list();
        $data['shortcode_options']  = $this->get_shortcode_default_options();
        $data['translations']       = $this->get_translation_strings();
        $data['preference']         = $this->get_shortcode_default_prefs();
        $data['preference_options'] = $this->get_shortcode_prefs_options();
        $data['layout']             = $this->get_shortcode_default_layout();
        $data['layout_options']     = $this->get_shortcode_layout_options();

        $data['demo_data'] = [
            'event_data'      => wp_validate_boolean(get_option('gsteca_dummy_data_created')),
            'shortcode_data'  => wp_validate_boolean(get_option('gsteca_dummy_shortcode_data_created'))
        ];

        return $data;
    }

    public function preview_scripts() {

        if (! $this->is_shortcode_preview()) return;

        $preview_css = GS_TECA_PLUGIN_DIR . 'assets/css/gs-teca-shortcode-preview.min.css';
        $preview_js  = GS_TECA_PLUGIN_DIR . 'assets/js/preview.min.js';
        $css_ver     = file_exists( $preview_css ) ? (string) filemtime( $preview_css ) : GS_TECA_VERSION;
        $js_ver      = file_exists( $preview_js ) ? (string) filemtime( $preview_js ) : GS_TECA_VERSION;

        wp_enqueue_style( 'gs-teca-shortcode-preview', GS_TECA_PLUGIN_URI . 'assets/css/gs-teca-shortcode-preview.min.css', '', $css_ver, 'all' );
        wp_enqueue_script( 'gs-teca-shortcode-preview', GS_TECA_PLUGIN_URI . 'assets/js/preview.min.js', array( 'jquery' ), $js_ver, true );
    }

    public function gsteca_get_wpdb() {

        global $wpdb;

        if (wp_doing_ajax()) $wpdb->show_errors = false;

        return $wpdb;
    }

    public function gsteca_check_db_error() {

        $wpdb = $this->gsteca_get_wpdb();

        if ($wpdb->last_error === '') return false;

        return true;
    }

    public function validate_shortcode_settings($shortcode_settings) {

        if ( is_array( $shortcode_settings ) && array_key_exists( 'popup_show_related_events', $shortcode_settings ) && is_bool( $shortcode_settings['popup_show_related_events'] ) ) {
            $shortcode_settings['popup_show_related_events'] = $shortcode_settings['popup_show_related_events'] ? 'on' : 'off';
        }

        $shortcode_settings = shortcode_atts( $this->get_shortcode_default_settings(), $shortcode_settings );

        $shortcode_settings['visibility_settings'] = $this->validate_shortcode_visibility_settings(
            $shortcode_settings['visibility_settings'] ?? array()
        );
        $shortcode_settings['popup_visibility_settings'] = $this->validate_popup_fields_visibility_settings(
            $shortcode_settings['popup_visibility_settings'] ?? array()
        );
        $shortcode_settings['popup_visibility_order'] = $this->validate_popup_visibility_order(
            $shortcode_settings['popup_visibility_order'] ?? array(),
            $shortcode_settings['popup_visibility_settings']
        );

        $shortcode_settings['gs_teca_template']             = teca_sanitize_theme_template_setting( $shortcode_settings['gs_teca_template'] );
        $shortcode_settings['view_type']                    = teca_sanitize_view_type_setting( $shortcode_settings['view_type'] );
        $shortcode_settings['popup_style']                  = teca_sanitize_popup_style_setting( $shortcode_settings['popup_style'] ?? 'default' );
        $shortcode_settings['pagination_type']            = teca_sanitize_pagination_type_setting( $shortcode_settings['pagination_type'] ?? 'normal-pagination' );
        $shortcode_settings['orderby']                    = teca_sanitize_orderby_setting( $shortcode_settings['orderby'] ?? 'date' );
        $shortcode_settings['cat_order_by']               = teca_sanitize_cat_order_by_setting( $shortcode_settings['cat_order_by'] ?? 'none' );
        $shortcode_settings['columns']                      = sanitize_text_field( $shortcode_settings['columns'] );
        $shortcode_settings['columns_tablet']               = sanitize_text_field( $shortcode_settings['columns_tablet'] );
        $shortcode_settings['columns_mobile_portrait']      = sanitize_text_field( $shortcode_settings['columns_mobile_portrait'] );
        $shortcode_settings['columns_mobile']               = sanitize_text_field( $shortcode_settings['columns_mobile'] );

        $shortcode_settings['details_length_type']          = sanitize_text_field( $shortcode_settings['details_length_type'] ?? 'words' );
        if ( ! in_array( $shortcode_settings['details_length_type'], array( 'words', 'letter' ), true ) ) {
            $shortcode_settings['details_length_type'] = 'words';
        }
        $shortcode_settings['details_length']                 = max( 1, absint( $shortcode_settings['details_length'] ?? 25 ) );

        $shortcode_settings = $this->validate_typography_settings( $shortcode_settings );
        $shortcode_settings = $this->validate_color_settings( $shortcode_settings );
        $shortcode_settings = $this->validate_popup_detail_settings( $shortcode_settings );
        $shortcode_settings = teca_sanitize_calendar_settings( $shortcode_settings );
        $shortcode_settings = teca_sanitize_events_section_settings( $shortcode_settings );
        $shortcode_settings = teca_sanitize_venue_template_settings( $shortcode_settings );
        $shortcode_settings = teca_sanitize_organizer_template_settings( $shortcode_settings );
        $shortcode_settings = teca_sanitize_popup_related_events_settings( $shortcode_settings );
        $shortcode_settings['date_formats'] = teca_sanitize_date_formats_settings( $shortcode_settings['date_formats'] ?? array() );
        $shortcode_settings = teca_sanitize_filters_and_search_by_settings( $shortcode_settings );

        return (array) $shortcode_settings;
    }

    protected function maybe_load_fonts_library() {
        if ( class_exists( __NAMESPACE__ . '\\GS_TECA_Shortcode_Fonts' ) ) {
            return;
        }
        $fonts_file = GS_TECA_PLUGIN_DIR . 'includes/shortcode-builder/shortcode_builder_fonts.php';
        if ( file_exists( $fonts_file ) ) {
            require_once $fonts_file;
        }
    }

    public function get_fonts_list( $include_empty_one = true ) {

        $this->maybe_load_fonts_library();

        if ( ! class_exists( __NAMESPACE__ . '\\GS_TECA_Shortcode_Fonts' ) ) {
            $fonts = array();
        } else {
            $fonts = GS_TECA_Shortcode_Fonts::get_fonts();
            $fonts = array_keys( $fonts );
            $fonts = array_map(
                function ( $item ) {
                    return array(
                        'label' => $item,
                        'value' => $item,
                    );
                },
                $fonts
            );
        }

        if ( $include_empty_one ) {
            array_unshift(
                $fonts,
                array(
                    'label' => __( 'Default', 'the-events-calendar-addon2' ),
                    'value' => '',
                )
            );
        }

        return $fonts;
    }

    public function get_typography_settings_config() {
        return array(
            'free' => array(
                'title_typography',
            ),
            'pro'  => array(
                'cat_typography',
                'tag_typography',
                'org_typography',
                'date_typography',
                'details_typography',
                'venue_typography',
                'view_details_button_typography',
                'google_calendar_button_typography',
            ),
        );
    }

    public function validate_typography_settings( $shortcode_settings ) {
        return teca_prepare_typography_settings( (array) $shortcode_settings );
    }

    public function validate_color_settings( $shortcode_settings ) {
        return teca_prepare_color_settings( (array) $shortcode_settings );
    }

    public function validate_popup_detail_settings( $shortcode_settings ) {
        return teca_prepare_popup_detail_settings( (array) $shortcode_settings );
    }

    protected function get_gsteca_shortcode_db_columns() {

        return array(
            'shortcode_name'     => '%s',
            'shortcode_settings' => '%s',
            'created_at'         => '%s',
            'updated_at'         => '%s'
        );
    }

    private function get_gs_teca_table_name() {
        $wpdb       = $this->gsteca_get_wpdb();
        $table_name = $wpdb->prefix . 'gs_teca';

        return preg_replace( '/[^A-Za-z0-9_]/', '', $table_name );
    }

    private function clear_shortcode_cache( $shortcode_id = 0 ) {
        wp_cache_delete( 'gs_teca_shortcodes' );
        wp_cache_delete( 'gs_teca_shortcodes_minimal' );

        if ( $shortcode_id ) {
            wp_cache_delete( 'gs_teca_shortcodes_' . absint( $shortcode_id ), 'gs_teca' );
        }
    }

    public function _get_shortcode($shortcode_id, $is_ajax = false) {
        if ( is_admin() && ! wp_doing_ajax() ) {
        if ( !current_user_can('manage_options')) {
                wp_send_json_error(__('Unauthorised Request', 'the-events-calendar-addon2'), 401);
            }
        }
        if (empty($shortcode_id)) {
            if ($is_ajax) wp_send_json_error(__('Shortcode ID missing', 'the-events-calendar-addon2'), 400);
            return false;
        }

        $wpdb = $this->gsteca_get_wpdb();

        $shortcode_id = absint( $shortcode_id );
        $cache_key    = 'gs_teca_shortcodes_' . $shortcode_id;
        $shortcode    = wp_cache_get( $cache_key, 'gs_teca' );

        if ( false === $shortcode ) {
            $table_name = $this->get_gs_teca_table_name();
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table query; table name is generated internally and sanitized.
            $shortcode = $wpdb->get_row(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is generated internally and sanitized; value is prepared as integer.
                    "SELECT * FROM {$table_name} WHERE id = %d LIMIT 1",
                    $shortcode_id
                ),
                ARRAY_A
            );

            if ( $shortcode ) {
                $shortcode['shortcode_settings'] = json_decode( $shortcode['shortcode_settings'], true );
                $shortcode['shortcode_settings'] = $this->validate_shortcode_settings( $shortcode['shortcode_settings'] );
                wp_cache_set( $cache_key, $shortcode, 'gs_teca', DAY_IN_SECONDS );
            }
        }

        if ($shortcode) {

            if ($is_ajax) wp_send_json_success($shortcode);

            return $shortcode;
        }

        if ($is_ajax) wp_send_json_error(__('No shortcode found', 'the-events-calendar-addon2'), 404);

        return false;
    }

     public function _update_shortcode($shortcode_id, $nonce, $fields, $is_ajax) {

        if ( is_admin() && ! wp_doing_ajax() ) {
            if ( ! check_admin_referer('_gsteca_update_shortcode_gs_') || !current_user_can('manage_options') ) { 
                if ($is_ajax) wp_send_json_error(__('Unauthorised Request', 'the-events-calendar-addon2'), 401); return false; 
            }
        }
        if (empty($shortcode_id)) {
            if ($is_ajax) wp_send_json_error(__('Shortcode ID missing', 'the-events-calendar-addon2'), 400);
            return false;
        }

        $_shortcode = $this->_get_shortcode($shortcode_id, false);

        if (empty($_shortcode)) {
            if ($is_ajax) wp_send_json_error(__('No shortcode found to update', 'the-events-calendar-addon2'), 404);
            return false;
        }

        $shortcode_name = ! empty( $fields['shortcode_name'] )
            ? sanitize_text_field( wp_unslash( $fields['shortcode_name'] ) )
            : $_shortcode['shortcode_name'];
        $shortcode_settings = ! empty( $fields['shortcode_settings'] )
            ? $fields['shortcode_settings']
            : $_shortcode['shortcode_settings'];

        if ( is_string( $shortcode_settings ) ) {
            $decoded = json_decode( $shortcode_settings, true );
            $shortcode_settings = is_array( $decoded ) ? $decoded : $_shortcode['shortcode_settings'];
        }

        // Remove dummy indicator on update
        if (isset($shortcode_settings['gsteca-demo_data'])) unset($shortcode_settings['gsteca-demo_data']);

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb       = $this->gsteca_get_wpdb();
        $table_name = $this->get_gs_teca_table_name();

        $data = array(
            "shortcode_name"         => $shortcode_name,
            "shortcode_settings"     => json_encode($shortcode_settings),
            "updated_at"             => current_time('mysql')
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table write; values are sanitized and shortcode cache is cleared after write.
        $num_row_updated = $wpdb->update( $table_name, $data, array( 'id' => absint( $shortcode_id ) ), $this->get_gsteca_shortcode_db_columns() );

        $this->clear_shortcode_cache( $shortcode_id );

        if ( $this->gsteca_check_db_error() ) {
            if ( $is_ajax ) {
                wp_send_json_error(
                    sprintf(
                        /* translators: %1$s: database error message. */
                        esc_html__( 'Database Error: %1$s', 'the-events-calendar-addon2' ),
                        esc_html( $wpdb->last_error )
                    ),
                    500
                );
            }

            return false;
        }

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action('gs_teca_shortcode_updated', $num_row_updated);
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action('gsteca_shortcode_updated', $num_row_updated);

        if ($is_ajax) wp_send_json_success(array(
            'message' => __('Shortcode updated', 'the-events-calendar-addon2'),
            'shortcode_id' => $num_row_updated
        ));

        return $num_row_updated;
    }

    public function _get_shortcodes( $shortcode_ids = array(), $is_ajax = false, $minimal = false ) {
        $wpdb       = $this->gsteca_get_wpdb();
        $table_name = $this->get_gs_teca_table_name();
    
        if ( ! empty( $shortcode_ids ) ) {
            $shortcode_ids = is_array( $shortcode_ids ) ? $shortcode_ids : explode( ',', $shortcode_ids );
            $shortcode_ids = array_filter( array_map( 'absint', $shortcode_ids ) );
    
            if ( empty( $shortcode_ids ) ) {
                return array();
            }
    
            $placeholders = implode( ', ', array_fill( 0, count( $shortcode_ids ), '%d' ) );
    
            if ( $minimal ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table query; table name and placeholders are generated internally and IDs are prepared as integers.
                $shortcodes = $wpdb->get_results(
                    $wpdb->prepare(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Table name and placeholders are generated internally and IDs are prepared as integers.
                        "SELECT id, shortcode_name FROM {$table_name} WHERE id IN ({$placeholders})",
                        ...$shortcode_ids
                    ),
                    ARRAY_A
                );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table query; table name and placeholders are generated internally and IDs are prepared as integers.
                $shortcodes = $wpdb->get_results(
                    $wpdb->prepare(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Table name and placeholders are generated internally and IDs are prepared as integers.
                        "SELECT * FROM {$table_name} WHERE id IN ({$placeholders})",
                        ...$shortcode_ids
                    ),
                    ARRAY_A
                );
            }
        } else {
            $cache_key  = $minimal ? 'gs_teca_shortcodes_minimal' : 'gs_teca_shortcodes';
            $shortcodes = wp_cache_get( $cache_key );
    
            if ( false !== $shortcodes && ! empty( $shortcodes ) ) {
                if ( $is_ajax ) {
                    wp_send_json_success( $shortcodes );
                }
    
                return $shortcodes;
            }
    
            if ( $minimal ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table query; table name is generated internally and sanitized.
                $shortcodes = $wpdb->get_results(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is generated internally and sanitized.
                    "SELECT id, shortcode_name FROM {$table_name} ORDER BY id DESC",
                    ARRAY_A
                );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table query; table name is generated internally and sanitized.
                $shortcodes = $wpdb->get_results(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is generated internally and sanitized.
                    "SELECT * FROM {$table_name} ORDER BY id DESC",
                    ARRAY_A
                );
            }
        }
    
        if ( $this->gsteca_check_db_error() ) {
            wp_send_json_error(
                sprintf(
                    /* translators: %s: database error message. */
                    esc_html__( 'Database Error: %s', 'the-events-calendar-addon2' ),
                    esc_html( $wpdb->last_error )
                )
            );
        }
    
        if ( empty( $shortcode_ids ) ) {
            wp_cache_set( $cache_key, $shortcodes, '', DAY_IN_SECONDS );
        }
    
        if ( $is_ajax ) {
            wp_send_json_success( $shortcodes );
        }
    
        return $shortcodes;
    }

    public function create_shortcode() {

        $this->verify_ajax_capability();
        $this->verify_ajax_nonce( '_gsteca_create_shortcode_gs_' );

        $shortcode_settings = $this->get_post_shortcode_settings();
        $shortcode_name     = $this->get_post_text( 'shortcode_name', __( 'Undefined', 'the-events-calendar-addon2' ) );

        if (empty($shortcode_settings) || !is_array($shortcode_settings)) {
            wp_send_json_error(__('Please configure the settings properly', 'the-events-calendar-addon2'), 206);
        }

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb       = $this->gsteca_get_wpdb();
        $table_name = $this->get_gs_teca_table_name();

        $data = array(
            "shortcode_name" => $shortcode_name,
            "shortcode_settings" => json_encode($shortcode_settings),
            "created_at" => current_time('mysql'),
            "updated_at" => current_time('mysql'),
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table write; values are sanitized and shortcode cache is cleared after write.
        $wpdb->insert( $table_name, $data, $this->get_gsteca_shortcode_db_columns() );

        if ( $this->gsteca_check_db_error() ) {
            wp_send_json_error(
                sprintf(
                    /* translators: %s: database error message. */
                    __( 'Database Error: %s', 'the-events-calendar-addon2' ),
                    $wpdb->last_error
                ),
                500
            );
        }

        $this->clear_shortcode_cache();

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action('gs_teca_shortcode_created', $wpdb->insert_id);
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action('gsteca_shortcode_created', $wpdb->insert_id);

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Hook name is kept for backward compatibility with existing integrations.
        do_action('gs-teca-shortcode-fired');

        // send success response with inserted id
        wp_send_json_success(array(
            'message' => __('Shortcode created successfully', 'the-events-calendar-addon2'),
            'shortcode_id' => $wpdb->insert_id
        ));
    }

    public function clone_shortcode() {

        $this->verify_ajax_capability();
        $this->verify_ajax_nonce( '_gsteca_clone_shortcode_gs_' );

        $clone_id = $this->get_post_absint( 'clone_id' );

        if (empty($clone_id)) wp_send_json_error(__('Clone Id not provided', 'the-events-calendar-addon2'), 400);

        $clone_shortcode = $this->_get_shortcode($clone_id, false);

        if (empty($clone_shortcode)) wp_send_json_error(__('Clone shortcode not found', 'the-events-calendar-addon2'), 404);

        $shortcode_settings  = $clone_shortcode['shortcode_settings'];
        $shortcode_name  = $clone_shortcode['shortcode_name'] . ' ' . __('- Cloned', 'the-events-calendar-addon2');

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb       = $this->gsteca_get_wpdb();
        $table_name = $this->get_gs_teca_table_name();

        $data = array(
            "shortcode_name" => $shortcode_name,
            "shortcode_settings" => json_encode($shortcode_settings),
            "created_at" => current_time('mysql'),
            "updated_at" => current_time('mysql'),
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table write; values are sanitized and shortcode cache is cleared after write.
        $wpdb->insert( $table_name, $data, $this->get_gsteca_shortcode_db_columns() );

        if ( $this->gsteca_check_db_error() ) {
            wp_send_json_error(
                sprintf(
                    /* translators: %s: database error message. */
                    __( 'Database Error: %s', 'the-events-calendar-addon2' ),
                    $wpdb->last_error
                ),
                500
            );
        }

        $this->clear_shortcode_cache();

        // Get the cloned shortcode
        $shotcode = $this->_get_shortcode($wpdb->insert_id, false);

        // send success response with inserted id
        wp_send_json_success(array(
            'message' => __('Shortcode cloned successfully', 'the-events-calendar-addon2'),
            'shortcode' => $shotcode,
        ));
    }

    public function get_shortcode() {

        $this->verify_ajax_capability();
        $this->verify_ajax_nonce( '_gsteca_get_shortcode_gs_' );

        $shortcode_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce and capability verified above.

        $this->_get_shortcode( $shortcode_id, wp_doing_ajax() );
    }

    public function update_shortcode( $shortcode_id = null, $nonce = null ) {

        $this->verify_ajax_capability();
        $this->verify_ajax_nonce( '_gsteca_update_shortcode_gs_' );

        $shortcode_id = $this->get_post_absint( 'id' );

        if ( empty( $shortcode_id ) ) {
            wp_send_json_error( esc_html__( 'Shortcode ID missing', 'the-events-calendar-addon2' ), 400 );
        }

        $fields = array(
            'shortcode_name'     => $this->get_post_text( 'shortcode_name' ),
            'shortcode_settings' => $this->get_post_shortcode_settings(),
        );

        $this->_update_shortcode( $shortcode_id, $nonce, $fields, true );
    }

    public function delete_shortcodes() {
        $this->verify_ajax_capability();
        $this->verify_ajax_nonce( '_gsteca_delete_shortcodes_gs_' );

        $ids = $this->get_post_ids( 'ids' );

        if ( empty( $ids ) ) {
            wp_send_json_error( esc_html__( 'No shortcode ids provided', 'the-events-calendar-addon2' ), 400 );
        }

        $wpdb       = $this->gsteca_get_wpdb();
        $count      = count( $ids );
        $table_name = $this->get_gs_teca_table_name();

        $placeholders = implode( ',', array_fill( 0, $count, '%d' ) );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table write; table name is generated internally and sanitized, IDs are prepared as integers, cache is cleared after write.
        $wpdb->query(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Placeholders are generated internally and IDs are prepared as integers.
                "DELETE FROM {$table_name} WHERE ID IN ({$placeholders})",
                ...$ids
            )
        );

        $this->clear_shortcode_cache();

        if ( $this->gsteca_check_db_error() ) {
            wp_send_json_error(
                sprintf(
                    /* translators: %s: database error message. */
                    esc_html__( 'Database Error: %s', 'the-events-calendar-addon2' ),
                    esc_html( $wpdb->last_error )
                ),
                500
            );
        }

        $message = _n(
            'Shortcode has been deleted',
            'Shortcodes have been deleted',
            $count,
            'the-events-calendar-addon2'
        );

        wp_send_json_success(
            array(
                'message' => $message,
            )
        );
    }

    public function get_shortcodes() {

        $this->_get_shortcodes(null, wp_doing_ajax());
    }

    public function temp_save_shortcode_settings() {

        $this->verify_ajax_capability();
        $this->verify_ajax_nonce( '_gsteca_temp_save_shortcode_settings_gs_' );

        $temp_key           = $this->get_post_text( 'temp_key' );
        $shortcode_settings = $this->get_post_shortcode_settings();

        if ( empty($temp_key) ) wp_send_json_error(__('No temp key provided', 'the-events-calendar-addon2'), 400);
        if ( empty($shortcode_settings) ) wp_send_json_error(__('No temp settings provided', 'the-events-calendar-addon2'), 400);

        delete_transient( $temp_key );
        set_transient( $temp_key, $this->validate_shortcode_settings( $shortcode_settings ), 86400 ); // save the transient for 1 day

        wp_send_json_success([
            'message' => __('Temp data saved', 'the-events-calendar-addon2'),
        ]);
    }

    public function get_translation_strings() {
        return [
            'columns'                             => __('Columns', 'the-events-calendar-addon2'),
            'columns_tablet'                      => __('Columns Tablet', 'the-events-calendar-addon2'),
            'columns_mobile_portrait'             => __('Columns Portpait Mobile', 'the-events-calendar-addon2'),
            'columns_mobile'                      => __('Columns Mobile', 'the-events-calendar-addon2'),

            'desktop'                              => __( 'Desktop', 'the-events-calendar-addon2' ),
			'tablet'                               => __( 'Tablet', 'the-events-calendar-addon2' ),
			'mobile_landscape'                     => __( 'Large Mobile', 'the-events-calendar-addon2' ),
			'mobile'                               => __( 'Mobile', 'the-events-calendar-addon2' ),

            'exclude_cats'                        => __('Exclude By Cats', 'the-events-calendar-addon2'),
            'deselect_by_name'                    => __('Exclude Specific Products', 'the-events-calendar-addon2'),
            'deselect_by_tag'                     => __('Exclude By Tags', 'the-events-calendar-addon2'),
            'select_by_tag'                       => __('Include By Tags', 'the-events-calendar-addon2'),
            'select_by_name'                      => __('Display Specific Products', 'the-events-calendar-addon2'),
            'select_products'                     => __('Select Products', 'the-events-calendar-addon2'),
            'select_cats'                         => __('Include By Cats', 'the-events-calendar-addon2'),

            'custom-css'                          => __('Custom CSS', 'the-events-calendar-addon2'),
            'shortcodes'                          => __('Shortcodes', 'the-events-calendar-addon2'),
            'global-settings-for-gs-woo-slider'   => __('Global Settings for Woo Product Views', 'the-events-calendar-addon2'),
            'all-shortcodes-for-gs-woo-slider'    => __('All shortcodes for Woo Product Views', 'the-events-calendar-addon2'),
            'create-shortcode'                    => __('Create Shortcode', 'the-events-calendar-addon2'),
            'create-new-shortcode'                => __('Create New Shortcode', 'the-events-calendar-addon2'),
            'shortcode'                           => __('Shortcode', 'the-events-calendar-addon2'),
            'name'                                => __('Name', 'the-events-calendar-addon2'),
            'action'                              => __('Action', 'the-events-calendar-addon2'),
            'actions'                             => __('Actions', 'the-events-calendar-addon2'),
            'edit'                                => __('Edit', 'the-events-calendar-addon2'),
            'clone'                               => __('Clone', 'the-events-calendar-addon2'),
            'delete'                              => __('Delete', 'the-events-calendar-addon2'),
            'delete-all'                          => __('Delete All', 'the-events-calendar-addon2'),
            'create-a-new-shortcode-and'          => __('Create a new shortcode & save it to use globally in anywhere', 'the-events-calendar-addon2'),
            'edit-shortcode'                      => __('Edit Shortcode', 'the-events-calendar-addon2'),
            'general-settings'                    => __('General', 'the-events-calendar-addon2'),
            'style-settings'                      => __('Style', 'the-events-calendar-addon2'),
            'query-settings'                      => __('Query', 'the-events-calendar-addon2'),
            'visibility-settings'                 => __('Visibility', 'the-events-calendar-addon2'),
            'shortcode-name'                      => __('Shortcode Name', 'the-events-calendar-addon2'),
            'name-of-the-shortcode'               => __('Shortcode Name', 'the-events-calendar-addon2'),
            'save-shortcode'                      => __('Save Shortcode', 'the-events-calendar-addon2'),
            'preview-shortcode'                   => __('Preview Shortcode', 'the-events-calendar-addon2'),


            'gs_teca_template'                    => __('Theme Style', 'the-events-calendar-addon2'),
            'view_type'                           => __('View Type', 'the-events-calendar-addon2'),
            'view_type--help'                     => __('Select Theme Style', 'the-events-calendar-addon2'),
            'daily_calendar_layout'               => __( 'Daily Calendar Layout', 'the-events-calendar-addon2' ),
            'weekly_calendar_layout'              => __( 'Weekly Calendar Layout', 'the-events-calendar-addon2' ),
            'monthly_calendar_layout'             => __( 'Monthly Calendar Layout', 'the-events-calendar-addon2' ),
            'quarterly_calendar_layout'           => __( 'Quarterly Calendar Layout', 'the-events-calendar-addon2' ),
            'yearly_calendar_layout'              => __( 'Yearly Calendar Layout', 'the-events-calendar-addon2' ),
            'calendar_layout'                     => __( 'Calendar Layout', 'the-events-calendar-addon2' ),
            'calendar_select_filter'              => __( 'Select Filter', 'the-events-calendar-addon2' ),
            'events_section'                      => __( 'Events Section', 'the-events-calendar-addon2' ),
            'event_layout'                        => __( 'Event Layout', 'the-events-calendar-addon2' ),
            'venue_template'                      => __( 'Venue Template', 'the-events-calendar-addon2' ),
            'venue_template_layout'               => __( 'Venue Template Name', 'the-events-calendar-addon2' ),
            'organizer_template'                  => __( 'Organizer Template', 'the-events-calendar-addon2' ),
            'organizer_template_layout'           => __( 'Organizer Template Name', 'the-events-calendar-addon2' ),

            'gs_teca_slide_speed' => __('Sliding Speed', 'the-events-calendar-addon2'),
            'gs_teca_slide_speed--help' => __('Set the speed in millisecond. Default 500 ms. To disable autoplay just set the speed 0', 'the-events-calendar-addon2'),
            
            'gs_teca_is_autop' => __('Autoplay', 'the-events-calendar-addon2'),
            'gs-teca-play-pause--help' => __('Enable/Disable Auto play to change the slides automatically after certain time. Default On', 'the-events-calendar-addon2'),

            'gs_teca_autop_pause' => __('Autoplay Delay', 'the-events-calendar-addon2'),
            'gs-teca-autop-pause--help' => __('You can adjust the time (in ms) between each slide. Default 4000 ms', 'the-events-calendar-addon2'),

            'gs_teca_inf_loop' => __('Infinite Loop', 'the-events-calendar-addon2'),
            'gs-teca-inf-loop--help' => __('If ON, clicking on "Next" while on the last slide will start over from first slide and vice-versa', 'the-events-calendar-addon2'),

            'gs_teca_pause_on_hover' => __('Pause on hover', 'the-events-calendar-addon2'),
            'gs-teca-slider-stop--help' => __('Autoplay will pause when mouse hovers over Post. Default On', 'the-events-calendar-addon2'),

            'gs-teca-reverse-direction' => __('Reverse Direction', 'the-events-calendar-addon2'),
            'gs-teca-reverse-direction--help' => __('Reverse the direction of movement. Default Off', 'the-events-calendar-addon2'),

            'gs-teca-slider-navs'                       => __( 'Slider Navs', 'the-events-calendar-addon2' ),
			'gs-teca-slider-navs--help'                 => __( 'Next / Previous control for Portfolio Slider. Default On Controls are not available when Ticker Mode is enabled', 'the-events-calendar-addon2' ),
			'gs-teca-ctrl-pos'                          => __( 'Navs Position', 'the-events-calendar-addon2' ),
			'gs-teca-ctrl-pos--placeholder'             => __( 'Position of Next / Previous control for Portfolio Slider. Default Bottom', 'the-events-calendar-addon2' ),
            'gs-teca-slider-dots'                       => __( 'Slider Dots', 'the-events-calendar-addon2' ),
			'gs-teca-slider-dots--help'                 => __( 'Dots control for Portfolio Slider below the widget. Default Off', 'the-events-calendar-addon2' ),
            'gs_teca_navs_style'                         => __( 'Navs Style', 'the-events-calendar-addon2' ),
			'gs_teca_dots_style'                         => __( 'Dots Style', 'the-events-calendar-addon2' ),
            'gs-filter-by'                           => __( 'Filter By', 'the-events-calendar-addon2' ),
			'gs-filter-cat'                          => __( 'Filter Position', 'the-events-calendar-addon2' ),
            'gs_teca_filter_style'                       => __( 'Filter Styles', 'the-events-calendar-addon2' ),
            'filter_type' => __('Filter Type', 'the-events-calendar-addon2'),
            'filter_type__details' => __('Select filter type', 'the-events-calendar-addon2'),
            'posts' => __('Posts', 'the-events-calendar-addon2'),
            'posts--placeholder' => __('Posts', 'the-events-calendar-addon2'),
            'posts--help' => __('Set max posts numbers you want to show, set -1 for all posts', 'the-events-calendar-addon2'),
            'gs_teca_pagination' => __('Enable Pagination', 'the-events-calendar-addon2'),
            'gs_teca_pagination__details' => __('Enable paginations like number pagination, load more button, On scroll load etc.', 'the-events-calendar-addon2'),
            'pagination_type' => __('Pagination Type', 'the-events-calendar-addon2'),
            'pagination_type__details' => __('Select pagination type.', 'the-events-calendar-addon2'),
            'pagination_type_pro_message'            => __( 'This pagination type is available in the Pro version only.', 'the-events-calendar-addon2' ),
            'typography_color_pro_message'           => __( 'This typography control is available in the Pro version only.', 'the-events-calendar-addon2' ),
            'query_include_exclude_categories_pro_message' => __( 'Categories in Include/Exclude are available in the Pro version only.', 'the-events-calendar-addon2' ),
            'query_include_exclude_tags_pro_message'       => __( 'Tags in Include/Exclude are available in the Pro version only.', 'the-events-calendar-addon2' ),
            'query_custom_order_pro_message'               => __( 'Custom Order is available in the Pro version only.', 'the-events-calendar-addon2' ),

            'initial_items'     => __('Initial Items', 'the-events-calendar-addon2'),
            'initial_items__details'    => __('Set initial number of items that shows on page load (before users interaction)', 'the-events-calendar-addon2'),

            'load_per_click' => __('Per Click', 'the-events-calendar-addon2'),
            'load_per_click__details' => __('Load members per button click', 'the-events-calendar-addon2'),

            'item_per_page' => __('Per Page', 'the-events-calendar-addon2'),
            'item_per_page__details' => __('Display members per page', 'the-events-calendar-addon2'),

            'per_load' => __('Per Load', 'the-events-calendar-addon2'),
            'per_load__details' => __('Display members per load', 'the-events-calendar-addon2'),

            'load_button_text' => __('Button Text', 'the-events-calendar-addon2'),
            'load_button_text__details' => __('Load more button text', 'the-events-calendar-addon2'),

            'popup_style' => __('Popup Style', 'the-events-calendar-addon2'),
            'popup_style__details' => __('Select popup style, this is available for certain theme', 'the-events-calendar-addon2'),
            'gs_teca_name_is_linked' => __('Link Events', 'the-events-calendar-addon2'),
            'gs_teca_name_is_linked__details' => __('Add links to title, description & image to display popup or to single page', 'the-events-calendar-addon2'),
            'gs_teca_link_type' => __('Link Type', 'the-events-calendar-addon2'),
            'gs_teca_link_type__details' => __('Choose the link type of The Events Calendar Addon', 'the-events-calendar-addon2'),
            'pref-more' => __('More', 'the-events-calendar-addon2'),
            'pref-more-details' => __('Replace with preferred text for More', 'the-events-calendar-addon2'),
            'pref-view-details' => __('View Details Button Text', 'the-events-calendar-addon2'),
            'pref-view-details-details' => __('Replace with preferred text for View Details buttons', 'the-events-calendar-addon2'),
            'pref-related-events-title' => __('Related Events Section Title', 'the-events-calendar-addon2'),
            'pref-related-events-title-details' => __('Replace with preferred text for Related Events section titles', 'the-events-calendar-addon2'),
            'pref-event-website' => __('Event Website Button Text', 'the-events-calendar-addon2'),
            'pref-event-website-details' => __('Replace with preferred text for Event Website buttons', 'the-events-calendar-addon2'),
            'pref-add-to-calendar' => __('Add to Calendar Button Text', 'the-events-calendar-addon2'),
            'pref-add-to-calendar-details' => __('Replace with preferred text for Add to Calendar buttons', 'the-events-calendar-addon2'),
            'prev' => __('Prev', 'the-events-calendar-addon2'),
            'prev-details' => __('Replace with preferred text for Prev', 'the-events-calendar-addon2'),
            'next' => __('Next', 'the-events-calendar-addon2'),
            'next-details' => __('Replace with preferred text for Next', 'the-events-calendar-addon2'),
            'link-text' => __('View More', 'the-events-calendar-addon2'),
            'link-text--details' => __('Replace with preferred text for View More', 'the-events-calendar-addon2'),
            'enable-multilingual' => __('Enable Multilingual', 'the-events-calendar-addon2'),
            'enable-multilingual--details' => __('Enable Multilingual mode to translate below strings using any Multilingual plugin like wpml or loco translate.', 'the-events-calendar-addon2'),
            'image_filter'                       => __('Image Filter', 'the-events-calendar-addon2'),
            'image_filter_hover'                 => __('Image Filter Hover', 'the-events-calendar-addon2'),

            'title_typography'                   => __( 'Title Typography', 'the-events-calendar-addon2' ),
            'cat_typography'                     => __( 'Category Typography', 'the-events-calendar-addon2' ),
            'tag_typography'                     => __( 'Tag Typography', 'the-events-calendar-addon2' ),
            'org_typography'                     => __( 'Organizer Typography', 'the-events-calendar-addon2' ),
            'date_typography'                    => __( 'Date Typography', 'the-events-calendar-addon2' ),
            'date_format'                        => __( 'Date Format', 'the-events-calendar-addon2' ),
            'custom_date_format'                 => __( 'Custom Date Format', 'the-events-calendar-addon2' ),
            'date_format__help'                  => __( 'Applies to readable event date text only. Decorative date badges and calendar day numbers are not changed.', 'the-events-calendar-addon2' ),
            'custom_date_format__help'           => __( 'Use WordPress/PHP date format characters, e.g. F j, Y or d M Y.', 'the-events-calendar-addon2' ),
            'details_typography'                 => __( 'Details Typography', 'the-events-calendar-addon2' ),
            'venue_typography'                   => __( 'Venue Typography', 'the-events-calendar-addon2' ),
            'view_details_button_typography'     => __( 'View Details Button Typography', 'the-events-calendar-addon2' ),
            'google_calendar_button_typography'  => __( 'Google Calendar Button Typography', 'the-events-calendar-addon2' ),
            'style_accordion_typography'         => __( 'Typography', 'the-events-calendar-addon2' ),
            'style_accordion_color_typography'   => __( 'Color Typography', 'the-events-calendar-addon2' ),
            'style_accordion_detail_typography'  => __( 'Detail Typography', 'the-events-calendar-addon2' ),
            'style_accordion_detail_color_typography' => __( 'Detail Color Typography', 'the-events-calendar-addon2' ),
            'style_accordion_filters_by'           => __( 'Filters By', 'the-events-calendar-addon2' ),
            'style_accordion_search_by'            => __( 'Search By', 'the-events-calendar-addon2' ),
            'search_by_title'                      => __( 'Title', 'the-events-calendar-addon2' ),
            'search_by_venue'                      => __( 'Venue', 'the-events-calendar-addon2' ),
            'search_by_organizer'                  => __( 'Organizer', 'the-events-calendar-addon2' ),
            'search_by_city'                       => __( 'City', 'the-events-calendar-addon2' ),
            'search_result_limit'                  => __( 'Result Limit', 'the-events-calendar-addon2' ),
            'search_result_limit__details'         => __( 'Maximum number of events returned by AJAX search.', 'the-events-calendar-addon2' ),
            'filter_by_date'                       => __( 'Date', 'the-events-calendar-addon2' ),
            'filter_by_day'                        => __( 'Day', 'the-events-calendar-addon2' ),
            'filter_by_category'                   => __( 'Category', 'the-events-calendar-addon2' ),
            'filter_by_tag'                        => __( 'Tag', 'the-events-calendar-addon2' ),
            'filter_by_venue'                      => __( 'Venue', 'the-events-calendar-addon2' ),
            'filter_by_city'                       => __( 'City', 'the-events-calendar-addon2' ),
            'filter_by_state'                      => __( 'State', 'the-events-calendar-addon2' ),
            'filter_by_country'                    => __( 'Country', 'the-events-calendar-addon2' ),
            'filter_by_organizer'                  => __( 'Organizer', 'the-events-calendar-addon2' ),
            'filter_by_cost'                       => __( 'Cost', 'the-events-calendar-addon2' ),
            'filter_by_time'                       => __( 'Time', 'the-events-calendar-addon2' ),
            'filter_by_featured'                   => __( 'Featured Events', 'the-events-calendar-addon2' ),
            'filter_by_event_status'               => __( 'Event Status', 'the-events-calendar-addon2' ),
            'teca_filter_date_clear'               => __( 'Clear', 'the-events-calendar-addon2' ),
            'teca_filter_all_venues'               => __( 'All Venues', 'the-events-calendar-addon2' ),
            'teca_filter_all_categories'           => __( 'All Categories', 'the-events-calendar-addon2' ),
            'teca_filter_all_tags'                 => __( 'All Tags', 'the-events-calendar-addon2' ),
            'teca_filter_all_organizers'           => __( 'All Organizers', 'the-events-calendar-addon2' ),
            'teca_filter_all_cities'               => __( 'All Cities', 'the-events-calendar-addon2' ),
            'teca_filter_all_states'               => __( 'All States', 'the-events-calendar-addon2' ),
            'teca_filter_all_countries'            => __( 'All Countries', 'the-events-calendar-addon2' ),
            'teca_filter_all_costs'                => __( 'All Costs', 'the-events-calendar-addon2' ),
            'teca_filter_cost_free'                => __( 'Free', 'the-events-calendar-addon2' ),
            'teca_filter_cost_paid'                => __( 'Paid', 'the-events-calendar-addon2' ),
            'teca_filter_all_times'                => __( 'All Times', 'the-events-calendar-addon2' ),
            'teca_filter_time_morning'             => __( 'Morning', 'the-events-calendar-addon2' ),
            'teca_filter_time_afternoon'           => __( 'Afternoon', 'the-events-calendar-addon2' ),
            'teca_filter_time_evening'             => __( 'Evening', 'the-events-calendar-addon2' ),
            'teca_filter_time_night'               => __( 'Night', 'the-events-calendar-addon2' ),
            'teca_filter_all_events'               => __( 'All Events', 'the-events-calendar-addon2' ),
            'teca_filter_featured_only'            => __( 'Featured Only', 'the-events-calendar-addon2' ),
            'teca_filter_not_featured'             => __( 'Non-Featured', 'the-events-calendar-addon2' ),
            'teca_filter_all_statuses'             => __( 'All Statuses', 'the-events-calendar-addon2' ),
            'teca_filter_status_upcoming'          => __( 'Upcoming', 'the-events-calendar-addon2' ),
            'teca_filter_status_ongoing'           => __( 'Ongoing', 'the-events-calendar-addon2' ),
            'teca_filter_status_past'              => __( 'Past', 'the-events-calendar-addon2' ),
            'teca_filters_by_name_empty_message'   => __( 'No events found.', 'the-events-calendar-addon2' ),

            'details-length-type'                => __( 'Details Length Type', 'the-events-calendar-addon2' ),
            'details-length'                     => __( 'Details Length', 'the-events-calendar-addon2' ),
            'words'                              => __( 'Words', 'the-events-calendar-addon2' ),
            'letter'                             => __( 'Letter', 'the-events-calendar-addon2' ),
            'details-length-type--help'          => __( 'Choose whether to limit details by word count or letter count', 'the-events-calendar-addon2' ),
            'details-length--help'               => __( 'Increase or decrease the number of words or letters to display in event details', 'the-events-calendar-addon2' ),

            'gs-teca-title'                          => __('Event Title', 'the-events-calendar-addon2'),
            'gs-teca-cat'                            => __('Event Category', 'the-events-calendar-addon2'),
            'gs-teca-tags'                           => __('Event Tags', 'the-events-calendar-addon2'),
            'gs-teca-date'                           => __('Event Date', 'the-events-calendar-addon2'),
            'gs-teca-details'                        => __('Event Details', 'the-events-calendar-addon2'),
            'gs-teca-thumbnail'                      => __('Event Thumbnail', 'the-events-calendar-addon2'),
            'gs-teca-organizer'                      => __('Event Organizer', 'the-events-calendar-addon2'),
            'gs-teca-venue'                          => __('Event Venue' , 'the-events-calendar-addon2'),
            'gs-teca-map'                            => __('Map', 'the-events-calendar-addon2'),
            'gs-teca-related-events'                 => __('Related Events', 'the-events-calendar-addon2'),
            'show_related_events'                    => __('Show Related Events', 'the-events-calendar-addon2'),
            'show_related_events__details'           => __('Display related upcoming events below the single event page details.', 'the-events-calendar-addon2'),
            'related_events_title'                   => __('Related Events Title', 'the-events-calendar-addon2'),
            'related_events_title__details'          => __('Heading text for the single page related events section.', 'the-events-calendar-addon2'),
            'related_events_limit'                   => __('Related Events Limit', 'the-events-calendar-addon2'),
            'related_events_limit__details'          => __('Maximum number of related events to display on the single page (1-12).', 'the-events-calendar-addon2'),
            'related_events_sources'                 => __('Related Events Based On', 'the-events-calendar-addon2'),
            'related_events_sources__details'        => __('Choose how single page related events are matched. Sources are tried in order until the limit is reached.', 'the-events-calendar-addon2'),
            'popup_show_related_events'                    => __('Show Related Events', 'the-events-calendar-addon2'),
            'popup_show_related_events__details'           => __('Display related upcoming events below the popup event details.', 'the-events-calendar-addon2'),
            'popup_related_events_title'                   => __('Related Events Title', 'the-events-calendar-addon2'),
            'popup_related_events_title__details'          => __('Heading text for the popup related events section.', 'the-events-calendar-addon2'),
            'popup_related_events_limit'                   => __('Related Events Limit', 'the-events-calendar-addon2'),
            'popup_related_events_limit__details'          => __('Maximum number of related events to display in the popup (1-12).', 'the-events-calendar-addon2'),
            'popup_related_events_sources'                 => __('Related Events Based On', 'the-events-calendar-addon2'),
            'popup_related_events_sources__details'        => __('Choose how popup related events are matched. Sources are tried in order until the limit is reached.', 'the-events-calendar-addon2'),

            'gsp-teca-title'                         => __('Event Title', 'the-events-calendar-addon2'),
            'gsp-teca-cat'                           => __('Event Category', 'the-events-calendar-addon2'),
            'gsp-teca-tags'                          => __('Event Tags', 'the-events-calendar-addon2'),
            'gsp-teca-date'                          => __('Event Date', 'the-events-calendar-addon2'),
            'gsp-teca-details'                       => __('Event Details', 'the-events-calendar-addon2'),
            'gsp-teca-organizer'                     => __('Event Organizer', 'the-events-calendar-addon2'),
            'gsp-teca-venue'                         => __('Event Venue', 'the-events-calendar-addon2'),
            'gsp-teca-thumbnail'                     => __('Event Thumbnail' , 'the-events-calendar-addon2'),
            'gsp-teca-time'                          => __('Event Time', 'the-events-calendar-addon2'),
            'gsp-teca-cost'                          => __('Event Cost', 'the-events-calendar-addon2'),
            'gsp-teca-website'                       => __('Event Website', 'the-events-calendar-addon2'),
            'gsp-teca-button'                        => __( 'View Details', 'the-events-calendar-addon2' ),
            'gs-teca-view-details-button'            => __( 'View Details', 'the-events-calendar-addon2' ),
            'gs-teca-google-calendar'                => __( 'Google Calendar', 'the-events-calendar-addon2' ),

            'single_page_style'                      => __('Single Page Style', 'the-events-calendar-addon2'),
            'single_page_style_pro_message'          => __( 'This Single Style is available in the Pro version only.', 'the-events-calendar-addon2' ),
            'single_teca_page'                       => __('Event Single Page Style', 'the-events-calendar-addon2'),
            'event_cat'                              => __('Category Archive Style', 'the-events-calendar-addon2'),
            'event_tag'                              => __('Tag Archive Style', 'the-events-calendar-addon2'),
            'event_select_shortcode'                 => __('Select Shortcode', 'the-events-calendar-addon2'),
            'event_replace_type'                     => __('Way To Retrieve Page', 'the-events-calendar-addon2'),

            'include_tags'                           => __( 'Tags', 'the-events-calendar-addon2' ),
			'exclude_tags'                           => __( 'Tags', 'the-events-calendar-addon2' ),
            'group'                                  => __( 'Categories', 'the-events-calendar-addon2' ),
			'group__help'                            => __( 'Select specific event category to show that specific category events', 'the-events-calendar-addon2' ),
			'exclude_group'                          => __( 'Categories', 'the-events-calendar-addon2' ),
			'exclude_group__help'                    => __( 'Select specific event category to hide that specific category events', 'the-events-calendar-addon2' ),

            'cat-order-by'                           => __( 'Category Order By', 'the-events-calendar-addon2' ),
			'cat_order'                              => __( 'Category Order', 'the-events-calendar-addon2' ),

            'select-by-title'                        => __('Specific Events', 'the-events-calendar-addon2'),
            'deselect-by-title'                      => __('Exclude Specific Events', 'the-events-calendar-addon2'),

            'posts'                                  => __('Posts', 'the-events-calendar-addon2'),
            'posts--placeholder'                     => __('Posts', 'the-events-calendar-addon2'),
            'posts--help'                            => __('Set max posts numbers you want to show, set -1 for all posts', 'the-events-calendar-addon2'),

            'order'                                  => __('Order', 'the-events-calendar-addon2'),
            'order--placeholder'                     => __('Order', 'the-events-calendar-addon2'),

            'order-by'                               => __('Order By', 'the-events-calendar-addon2'),

            'global-settings-for-teca'               => __('Global Settings for The Events Calendar Addon', 'the-events-calendar-addon2'),
            'preference'                             => __('Preference', 'the-events-calendar-addon2'),
            'save-preference'                        => __('Save Preference', 'the-events-calendar-addon2'),
            
            'custom-css'                             => __('Custom CSS', 'the-events-calendar-addon2'),
            'anchor-tag-rel'                         => __('Anchor Tag Rel', 'the-events-calendar-addon2'),
            'anchor_tag_rel--details'                => __( 'Select Anchor Tag rel attribute\'s value, to improve security and SEO, by default the value is dofollow.', 'the-events-calendar-addon2' ),
            
            'install-demo-data'                      => __( 'Install Demo Data', 'the-events-calendar-addon2' ),
			'install-demo-data-description'          => __( 'Quick start with GS Plugins by installing the demo data', 'the-events-calendar-addon2' ),
        ];
    }

    public function get_translation($translation_name) {

        $translations = $this->get_shortcode_default_translations();
    
        if ( ! array_key_exists( $translation_name, $translations ) ) return '';

        $prefs = $this->_get_shortcode_pref( false );

        if ( $prefs['gs_teca_enable_multilingual'] == 'on' ) return $translations[$translation_name];
    
        return $prefs[ $translation_name ] ?? '';
    }

    public function get_filter_cat_options() {
        return [
            [
                'label' => __( 'Left', 'the-events-calendar-addon2' ),
                'value' => 'left',
            ],
            [
                'label' => __( 'Center', 'the-events-calendar-addon2' ),
                'value' => 'center',
            ],
            [
                'label' => __( 'Right', 'the-events-calendar-addon2' ),
                'value' => 'right',
            ],
        ];
    }

    public function get_columns() {

        $columns = [
            [
                'label' => __( '1 Column', 'the-events-calendar-addon2' ),
                'value' => '12'
            ],
            [
                'label' => __( '2 Columns', 'the-events-calendar-addon2' ),
                'value' => '6'
            ],
            [
                'label' => __( '3 Columns', 'the-events-calendar-addon2' ),
                'value' => '4'
            ],
            [
                'label' => __( '4 Columns', 'the-events-calendar-addon2' ),
                'value' => '3'
            ],
            [
                'label' => __( '5 Columns', 'the-events-calendar-addon2' ),
                'value' => '2_4'
            ],
            [
                'label' => __( '6 Columns', 'the-events-calendar-addon2' ),
                'value' => '2'
            ],
        ];

        return $columns;
    }



    public function get_shortcode_default_options() {

        return [

            'columns'                               => $this->get_columns(),
            'columns_tablet'                        => $this->get_columns(),
            'columns_mobile_portrait'               => $this->get_columns(),
            'columns_mobile'                        => $this->get_columns(),
            'gs_teca_template'                      => $this->get_shortcode_templates(),
            'view_type'                             => $this->get_theme_styles(),
            'daily_calendar_layout'                 => $this->get_calendar_sub_layout_options( 'daily' ),
            'weekly_calendar_layout'                => $this->get_calendar_sub_layout_options( 'weekly' ),
            'monthly_calendar_layout'               => $this->get_calendar_sub_layout_options( 'monthly' ),
            'quarterly_calendar_layout'             => $this->get_calendar_sub_layout_options( 'quarterly' ),
            'yearly_calendar_layout'                => $this->get_calendar_sub_layout_options( 'yearly' ),
            'calendar_layout'                       => teca_get_calendar_layout_select_options(),
            'calendar_select_filter'                => teca_get_calendar_select_filter_options(),
            'event_layout'                          => $this->get_events_section_layout_options(),
            'venue_template_layout'                 => $this->get_venue_template_layout_options(),
            'organizer_template_layout'             => $this->get_organizer_template_layout_options(),
            'gs_teca_navs_style'                    => $this->get_navs_style(),
            'gs_teca_dots_style'                    => $this->get_dots_style(),
            'gs_teca_ctrl_pos'                      => $this->get_teca_ctrl_pos_options(),
            'gs_filters_by'                         => $this->get_filters_by_options(),
            'gs_teca_filter_style'                  => $this->get_filter_style(),
            'gs_teca_filter_type'                   => $this->get_filter_type(),
            'gs_filter_cat'                         => $this->get_filter_cat_options(),
            'pagination_type'                       => $this->get_shortcode_options_paginations(),
            'gs_teca_link_type'                     => $this->get_shortcode_options_link_types(),
            'popup_style'                           => $this->get_popup_styles(),
            'image_filter_style'                    => $this->get_image_filter_effects(),
            'image_filter_hover_style'              => $this->get_image_filter_effects(),
            'orderby'                               => $this->get_orderby_options(),
            'cat_order_by'                          => $this->get_cat_orderby_options(),
            'include_cat'                           => $this-> gs_term_options( 'tribe_events_cat' ),
			'exclude_cat'                           => $this-> gs_term_options( 'tribe_events_cat' ),
			'include_tags'                          => $this-> gs_term_options( 'post_tag' ),
			'exclude_tags'                          => $this-> gs_term_options( 'post_tag' ),
            'select_by_title'                       => gs_post_select(),
            'deselect_by_title'                     => gs_post_select(),
            'related_events_sources'                => teca_get_related_events_source_options(),
            'popup_related_events_sources'          => teca_get_related_events_source_options(),
            'details_length_type'                   => $this->get_details_length_type_options(),
            'color_typography_fields'               => teca_get_free_color_typography_select_options(),
            'popup_detail_typography_groups'        => teca_get_free_popup_detail_typography_select_options(),
            'popup_detail_color_fields'             => teca_get_free_popup_detail_color_select_options(),
            'popup_detail_design_registry'          => teca_get_popup_detail_design_registry_for_builder(),
            'style_design_registry'                 => teca_get_style_design_registry_for_builder(),
            'date_format_presets'                   => teca_get_date_format_preset_options(),
            'order' => [
                [
                    'label' => __( 'DESC', 'the-events-calendar-addon2' ),
                    'value' => 'DESC'
                ],
                [
                    'label' => __( 'ASC', 'the-events-calendar-addon2' ),
                    'value' => 'ASC'
                ],
            ],

            'cat_order' => array(
				array(
					'label' => __( 'ASC', 'the-events-calendar-addon2' ),
					'value' => 'asc',
				),
				array(
					'label' => __( 'DESC', 'the-events-calendar-addon2' ),
					'value' => 'desc',
				),
			),
           
        ];
    }

    public function get_details_length_type_options() {
        return array(
            array(
                'label' => __( 'Words', 'the-events-calendar-addon2' ),
                'value' => 'words',
            ),
            array(
                'label' => __( 'Letter', 'the-events-calendar-addon2' ),
                'value' => 'letter',
            ),
        );
    }

    public function get_orderby_options() {

        $options = [
            [
                'label' => __( 'Post ID', 'the-events-calendar-addon2' ),
                'value' => 'ID',
            ],
            [
                'label' => __( 'Post Name', 'the-events-calendar-addon2' ),
                'value' => 'title',
            ],
            [
                'label' => __( 'Date', 'the-events-calendar-addon2' ),
                'value' => 'date',
            ],
            [
                'label' => __( 'Random', 'the-events-calendar-addon2' ),
                'value' => 'rand',
            ],
            [
                'label' => __( 'Custom Order', 'the-events-calendar-addon2' ),
                'value' => 'menu_order',
            ]
        ];

        $pro_slugs = array( 'menu_order' );

        $options = apply_pro_guards( $options, $pro_slugs );

        return $options;
    }

    public function get_cat_orderby_options() {

        $options = [
            [
                'label' => __( 'Default', 'the-events-calendar-addon2' ),
                'value' => 'none',
            ],
            [
                'label' => __( 'Category ID', 'the-events-calendar-addon2' ),
                'value' => 'id',
            ],
            [
                'label' => __( 'Category Name', 'the-events-calendar-addon2' ),
                'value' => 'name',
            ],
            [
                'label' => __( 'Custom Order', 'the-events-calendar-addon2' ),
                'value' => 'term_order',
            ]
        ];

        $pro_slugs = array( 'term_order' );

        $options = apply_pro_guards( $options, $pro_slugs );

        return $options;
    }

    public function get_image_filter_effects() {
        
        $styles = [
            [
                'label' => __('None', 'the-events-calendar-addon2'),
                'value' => 'none'
            ],
            [
                'label' => __('Blur', 'the-events-calendar-addon2'),
                'value' => 'blur'
            ],
            [
                'label' => __('Brightness', 'the-events-calendar-addon2'),
                'value' => 'brightness'
            ],
            [
                'label' => __('Contrast', 'the-events-calendar-addon2'),
                'value' => 'contrast'
            ],
            [
                'label' => __('Grayscale', 'the-events-calendar-addon2'),
                'value' => 'grayscale'
            ],
            [
                'label' => __('Hue Rotate', 'the-events-calendar-addon2'),
                'value' => 'hue_rotate'
            ],
            [
                'label' => __('Invert', 'the-events-calendar-addon2'),
                'value' => 'invert'
            ],
            [
                'label' => __('Opacity', 'the-events-calendar-addon2'),
                'value' => 'opacity'
            ],
            [
                'label' => __('Saturate', 'the-events-calendar-addon2'),
                'value' => 'saturate'
            ],
            [
                'label' => __('Sepia', 'the-events-calendar-addon2'),
                'value' => 'sepia'
            ]
        ];

        $free_slugs = ['none', 'blur'];

        return apply_pro_guards( $styles, $free_slugs, true );
    }

    public function get_popup_styles() {
        return self::get_free_popup_style_options();
    }

    /**
     * All Popup Style options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_all_popup_style_options() {
        return array(
            array(
                'label' => __( 'Default', 'the-events-calendar-addon2' ),
                'value' => 'default',
            ),
            array(
                'label' => __( 'Style One', 'the-events-calendar-addon2' ),
                'value' => 'style-one',
            ),
            array(
                'label' => __( 'Style Two', 'the-events-calendar-addon2' ),
                'value' => 'style-two',
            ),
        );
    }

    /**
     * Free Popup Style options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_free_popup_style_options() {
        $free_slugs = teca_get_free_popup_style_slugs();
        $all        = self::get_all_popup_style_options();
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
     * Pro-only Popup Style options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_pro_popup_style_options() {
        $free_slugs = teca_get_free_popup_style_slugs();

        return array_values(
            array_filter(
                self::get_all_popup_style_options(),
                static function( $item ) use ( $free_slugs ) {
                    return ! in_array( $item['value'] ?? '', $free_slugs, true );
                }
            )
        );
    }


    public function get_shortcode_options_paginations() {
        return self::get_free_pagination_type_options();
    }

    /**
     * All pagination type options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_all_pagination_type_options() {
        return array(
            array(
                'label' => __( 'Normal Pagination', 'the-events-calendar-addon2' ),
                'value' => 'normal-pagination',
            ),
            array(
                'label' => __( 'Ajax Pagination', 'the-events-calendar-addon2' ),
                'value' => 'ajax-pagination',
            ),
            array(
                'label' => __( 'Load More Button', 'the-events-calendar-addon2' ),
                'value' => 'load-more-button',
            ),
            array(
                'label' => __( 'Load More on Scroll', 'the-events-calendar-addon2' ),
                'value' => 'load-more-scroll',
            ),
        );
    }

    /**
     * Free pagination type options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_free_pagination_type_options() {
        $free_slugs = teca_get_free_pagination_type_slugs();
        $all        = self::get_all_pagination_type_options();
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
     * Pro-only pagination type options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_pro_pagination_type_options() {
        $free_slugs = teca_get_free_pagination_type_slugs();

        return array_values(
            array_filter(
                self::get_all_pagination_type_options(),
                static function( $item ) use ( $free_slugs ) {
                    return ! in_array( $item['value'] ?? '', $free_slugs, true );
                }
            )
        );
    }

    public function get_filter_style() {
       
        $styles = array(
				array(
					'label' => __( 'Default', 'the-events-calendar-addon2' ),
					'value' => 'filter--default',
				),
				array(
					'label' => __( 'Style 01', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-01',
				),
				array(
					'label' => __( 'Style 02', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-02',
				),
				array(
					'label' => __( 'Style 03', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-03',
				),
				array(
					'label' => __( 'Style 04', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-04',
				),
				array(
					'label' => __( 'Style 05', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-05',
				),
				array(
					'label' => __( 'Style 06', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-06',
				),
				array(
					'label' => __( 'Style 07', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-07',
				),
				array(
					'label' => __( 'Style 08', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-08',
				),
				array(
					'label' => __( 'Style 09', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-09',
				),
				array(
					'label' => __( 'Style 10', 'the-events-calendar-addon2' ),
					'value' => 'filter--style-10',
				),
            );    

        $free_slugs = ['filter--default'];

        return apply_pro_guards( $styles, $free_slugs, true );
    }

    public function get_teca_ctrl_pos_options() {
        return [
            [
                'label' => __( 'Bottom', 'the-events-calendar-addon2' ),
                'value' => 'carousel-navs-pos--bottom',
            ],
            [
                'label' => __( 'Center', 'the-events-calendar-addon2' ),
                'value' => 'carousel-navs-pos--center',
            ],
            // [
            //     'label' => __( 'Center Outside', 'the-events-calendar-addon2' ),
            //     'value' => 'carousel-navs-pos--center-outside',
            // ],
            // [
            //     'label' => __( 'Center Inside', 'the-events-calendar-addon2' ),
            //     'value' => 'carousel-navs-pos--center-inside',
            // ],
            [
                'label' => __( 'Top Right', 'the-events-calendar-addon2' ),
                'value' => 'carousel-navs-pos--top-right',
            ],
            [
                'label' => __( 'Top Center', 'the-events-calendar-addon2' ),
                'value' => 'carousel-navs-pos--top-center',
            ],
            [
                'label' => __( 'Top Left', 'the-events-calendar-addon2' ),
                'value' => 'carousel-navs-pos--top-left',
            ],
            [
                'label' => __( 'Verticle Right', 'the-events-calendar-addon2' ),
                'value' => 'carousel-navs-pos--verticle-right',
            ],
            [
                'label' => __( 'Verticle Left', 'the-events-calendar-addon2' ),
                'value' => 'carousel-navs-pos--verticle-left',
            ]
        ];
    }

    public function get_navs_style() {

        $styles = array(

				array(
					'label' => __( 'Default', 'the-events-calendar-addon2' ),
					'value' => 'nav--default',
				),
				array(
					'label' => __( 'Style 01', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-01',
				),
				array(
					'label' => __( 'Style 02', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-02',
				),
				array(
					'label' => __( 'Style 03', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-03',
				),
				array(
					'label' => __( 'Style 04', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-04',
				),
				array(
					'label' => __( 'Style 05', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-05',
				),
				array(
					'label' => __( 'Style 06', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-06',
				),
				array(
					'label' => __( 'Style 07', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-07',
				),
				array(
					'label' => __( 'Style 08', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-08',
				),
				array(
					'label' => __( 'Style 09', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-09',
				),
				array(
					'label' => __( 'Style 10', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-10',
				),
				array(
					'label' => __( 'Style 11', 'the-events-calendar-addon2' ),
					'value' => 'nav--style-11',
				),
			);

        $free_slugs = ['nav--default'];

        return apply_pro_guards( $styles, $free_slugs, true);
    }

    public function get_dots_style() {

        $styles = array(
				array(
					'label' => __( 'Default', 'the-events-calendar-addon2' ),
					'value' => 'dot--default',
				),
				array(
					'label' => __( 'Style 01', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-01',
				),
				array(
					'label' => __( 'Style 02', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-02',
				),
				array(
					'label' => __( 'Style 03', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-03',
				),
				array(
					'label' => __( 'Style 04', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-04',
				),
				array(
					'label' => __( 'Style 05', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-05',
				),
				array(
					'label' => __( 'Style 06', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-06',
				),
				array(
					'label' => __( 'Style 07', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-07',
				),
				array(
					'label' => __( 'Style 08', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-08',
				),
				array(
					'label' => __( 'Style 09', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-09',
				),
				array(
					'label' => __( 'Style 10', 'the-events-calendar-addon2' ),
					'value' => 'dot--style-10',
				),
		);

        $free_slugs = ['dot--default'];

        return apply_pro_guards( $styles, $free_slugs, true );

    }

    public function get_theme_styles() {
        return self::get_free_view_types();
    }

    /**
     * All View Type options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_all_view_type_options() {
        return [
            [
                'label' => __('Grid', 'the-events-calendar-addon2'),
                'value' => 'grid'
            ],
            [
                'label' => __('Masonry', 'the-events-calendar-addon2'),
                'value' => 'masonry'
            ],
            [
                'label' => __( 'Calendar', 'the-events-calendar-addon2' ),
                'value' => 'calendar',
            ],
            [
                'label' => __('Slider', 'the-events-calendar-addon2'),
                'value' => 'carousel'
            ],
            [
                'label' => __('Ticker', 'the-events-calendar-addon2'),
                'value' => 'ticker'
            ],
            [
                'label' => __('Filter', 'the-events-calendar-addon2'),
                'value' => 'filter'
            ],
            [
                'label' => __( 'Events Section', 'the-events-calendar-addon2' ),
                'value' => 'events-section',
            ],
            [
                'label' => __( 'Venue Template', 'the-events-calendar-addon2' ),
                'value' => 'venue_template',
            ],
            [
                'label' => __( 'Organizer Template', 'the-events-calendar-addon2' ),
                'value' => 'organizer_template',
            ],
        ];
    }

    /**
     * Free View Type options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_free_view_types() {
        $free_slugs = teca_get_free_view_type_slugs();
        $all_types  = self::get_all_view_type_options();
        $indexed    = array();

        foreach ( $all_types as $view_type ) {
            $value = $view_type['value'] ?? '';

            if ( '' !== $value ) {
                $indexed[ $value ] = $view_type;
            }
        }

        $free_view_types = array();

        foreach ( $free_slugs as $slug ) {
            if ( isset( $indexed[ $slug ] ) ) {
                $free_view_types[] = $indexed[ $slug ];
            }
        }

        return $free_view_types;
    }

    /**
     * Pro-only View Type options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_pro_view_types() {
        $free_slugs = teca_get_free_view_type_slugs();

        return array_values(
            array_filter(
                self::get_all_view_type_options(),
                static function( $item ) use ( $free_slugs ) {
                    return ! in_array( $item['value'] ?? '', $free_slugs, true );
                }
            )
        );
    }

    public function get_calendar_sub_layout_options( $prefix ) {
        $labels = array(
            1 => __( 'Layout 1', 'the-events-calendar-addon2' ),
            2 => __( 'Layout 2', 'the-events-calendar-addon2' ),
            3 => __( 'Layout 3', 'the-events-calendar-addon2' ),
        );

        $names = array(
            'daily'     => __( 'Daily', 'the-events-calendar-addon2' ),
            'weekly'    => __( 'Weekly', 'the-events-calendar-addon2' ),
            'monthly'   => __( 'Monthly', 'the-events-calendar-addon2' ),
            'quarterly' => __( 'Quarterly', 'the-events-calendar-addon2' ),
            'yearly'    => __( 'Yearly', 'the-events-calendar-addon2' ),
        );

        $prefix  = sanitize_key( (string) $prefix );
        $name    = $names[ $prefix ] ?? ucfirst( $prefix );
        $options = array();

        foreach ( $labels as $number => $layout_label ) {
            $options[] = array(
                'label' => sprintf( '%s %s', $name, $layout_label ),
                'value' => $prefix . '-layout-' . $number,
            );
        }

        return $options;
    }

    public function get_organizer_template_layout_options() {
        return array(
            array(
                'label' => __( 'Layout 1', 'the-events-calendar-addon2' ),
                'value' => 'layout-1',
            ),
            array(
                'label' => __( 'Layout 2', 'the-events-calendar-addon2' ),
                'value' => 'layout-2',
            ),
            array(
                'label' => __( 'Layout 3', 'the-events-calendar-addon2' ),
                'value' => 'layout-3',
            ),
        );
    }

    public function get_venue_template_layout_options() {
        return array(
            array(
                'label' => __( 'Layout 1', 'the-events-calendar-addon2' ),
                'value' => 'layout-1',
            ),
            array(
                'label' => __( 'Layout 2', 'the-events-calendar-addon2' ),
                'value' => 'layout-2',
            ),
            array(
                'label' => __( 'Layout 3', 'the-events-calendar-addon2' ),
                'value' => 'layout-3',
            ),
        );
    }

    public function get_events_section_layout_options() {
        return array(
            array(
                'label' => __( 'Event Layout 1', 'the-events-calendar-addon2' ),
                'value' => 'event-layout-1',
            ),
            array(
                'label' => __( 'Event Layout 2', 'the-events-calendar-addon2' ),
                'value' => 'event-layout-2',
            ),
            array(
                'label' => __( 'Event Layout 3', 'the-events-calendar-addon2' ),
                'value' => 'event-layout-3',
            ),
        );
    }

    public function get_shortcode_templates() {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        return apply_filters( 'gs_teca_shortcode_templates', self::get_free_templates() );
    }

    public static function get_all_theme_template_options() {

        return [
            [
                'label' => __('Style 01', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-style-1'
            ],
            [
                'label' => __('Style 02', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-style-2'
            ],
            [
                'label' => __('Style 03', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-style-3'
            ],
            [
                'label' => __('Style 04', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-style-4'
            ],
            [
                'label' => __('Style 05', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-style-5'
            ],
            [
                'label' => __( 'Style 06', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-style-6'
            ],
            [
                'label' => __( 'Style 07', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-style-7'
            ],
            [
                'label' => __( 'Style 08', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-style-8'
            ],
            [
                'label' => __( 'Style 09', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-style-9'
            ],
            [
                'label' => __( 'Style 10', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-style-10'
            ],
            [
                'label' => __('List 01', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-list-style-1'
            ],
            [
                'label' => __('List 02', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-list-style-2'
            ],
            [
                'label' => __('List 03', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-list-style-3'
            ],
            [
                'label' => __('List 04', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-list-style-4'
            ],
            [
                'label' => __('List 05', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-list-style-5'
            ],
            [
                'label' => __('Table 01', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-table-style-1'
            ],
            [
                'label' => __('Table 02', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-table-style-2'
            ],
            [
                'label' => __('Table 03', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-table-style-3'
            ],
            [
                'label' => __('Table 04', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-table-style-4'
            ],
            [
                'label' => __('Table 05', 'the-events-calendar-addon2'),
                'value' => 'gs-teca-table-style-5'
            ],
            [
                'label' => __( 'Timeline 1', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-timeline-1',
            ],
            [
                'label' => __( 'Timeline 2', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-timeline-2',
            ],
            [
                'label' => __( 'Timeline 3', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-timeline-3',
            ],
            [
                'label' => __( 'Accordion 1', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-accordion-1',
            ],
            [
                'label' => __( 'Accordion 2', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-accordion-2',
            ],
            [
                'label' => __( 'Accordion 3', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-accordion-3',
            ],
        ];
    }

    public static function get_free_templates() {
        $free_slugs = teca_get_free_theme_template_slugs();
        $all_themes = self::get_all_theme_template_options();
        $indexed    = array();

        foreach ( $all_themes as $theme ) {
            $value = $theme['value'] ?? '';

            if ( '' !== $value ) {
                $indexed[ $value ] = $theme;
            }
        }

        $free_themes = array();

        foreach ( $free_slugs as $slug ) {
            if ( isset( $indexed[ $slug ] ) ) {
                $free_themes[] = $indexed[ $slug ];
            }
        }

        return $free_themes;
    }

    /**
     * Pro-only theme styles for the admin Theme Style selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_pro_templates() {
        $free_slugs = teca_get_free_theme_template_slugs();

        return array_values(
            array_filter(
                self::get_all_theme_template_options(),
                static function( $item ) use ( $free_slugs ) {
                    return ! in_array( $item['value'] ?? '', $free_slugs, true );
                }
            )
        );
    }

    public function get_filters_by_options() {
        return [
            [
                'label' => __( 'Categories', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-category',
            ],
            [
                'label' => __( 'Tags', 'the-events-calendar-addon2' ),
                'value' => 'gs-teca-tag',
            ]
        ];
    }

    public function get_filter_type() {
        return [
            [
                'label' => __( 'Normal Filter', 'the-events-calendar-addon2' ),
                'value' => 'normal-filter'
            ],
            [
                'label' => __( 'Ajax Filter', 'the-events-calendar-addon2' ),
                'value' => 'ajax-filter'
            ]
        ];

    }

    public function get_shortcode_default_settings() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin shortcode builder reads shortcode ID from the query string on page load.
        $shortcode_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
        return array_merge([
            'posts' 	                       => -1,
            'order'		                       => 'DESC',
            'orderby'                          => 'date',
            'cat_order_by'                     => 'none',
            'cat_order'                        => 'asc',
            'gs_teca_template'                 => 'gs-teca-style-1',
            'view_type'                        => 'grid',
            'calendar_layout'                  => 'calendar-layout-1',
            'calendar_select_filter'           => 'daily',
            'daily_calendar_layout'            => 'daily-layout-1',
            'weekly_calendar_layout'           => 'weekly-layout-1',
            'monthly_calendar_layout'          => 'monthly-layout-1',
            'quarterly_calendar_layout'        => 'quarterly-layout-1',
            'yearly_calendar_layout'           => 'yearly-layout-1',
            'event_layout'                     => 'event-layout-1',
            'venue_template_layout'            => 'layout-1',
            'organizer_template_layout'        => 'layout-1',
            'columns'                          => '3',
            'columns_tablet'                   => '4',
            'columns_mobile_portrait'          => '6',
            'columns_mobile'                   => '12',
            'gs_teca_slide_speed'              => 500,
			'gs_teca_is_autop'                 => 'on',
			'gs_teca_autop_pause'              => 2000,
			'gs_teca_pause_on_hover'           => 'on',
			'gs_teca_inf_loop'                 => 'on',
			'gs_teca_slider_navs'              => 'on',
            'gs_teca_slider_dots'              => 'on',
			'gs_teca_ctrl_pos'                 => 'carousel-navs-pos--bottom',
			'gs_teca_navs_style'               => 'nav--default',
			'gs_teca_filter_style'             => 'filter--default',
			'gs_teca_dots_style'               => 'dot--default',
            'gs_teca_margin'                   => 30,
            'gs_teca_move_item'                => 1,
            'gs_teca_reverse_direction'        => 'off',
            'gs_filters_by'                    => 'gs-teca-category',
            'gs_filter_cat'                    => 'left',
            'gs_teca_filter_type'              => 'normal-filter',
            'gs_teca_enable_clear_filters'     => 'off',
            'gs_teca_enable_multi_select'      => 'off',
            'gs_teca_multi_select_ellipsis'    => 'off',
            'gs_teca_search_all_fields'        => 'off',
            'gs_teca_reset_filters_txt'        => 'off',
            'gs_teca_prev_txt'                 => 'off',
            'gs_teca_next_txt'                 => 'off',
            'gs_teca_pagination'               => 'off',
            'filter_enabled'                   => 'off',
            'carousel_enabled'                 => 'off',
            'pagination_type'                  => 'normal-pagination',
            'load_button_text'                 => 'Load More',
            'initial_items'                    => '6',
            'item_per_page'                    => '6',
            'load_per_click'                   => '3',
            'per_load'                         => '3',
            'paged'                            => 'currentPage',
            'gs_teca_link_type'                => 'single_page',
            'gs_teca_name_is_linked'           => 'on',
            'details_length_type'              => 'words',
            'details_length'                   => 25,
            'popup_style'                      => 'default',
            'image_filter_hover_style'         => 'none',
            'image_filter_style'               => 'none',
            'include_cat'                      => array(),
			'exclude_cat'                      => array(),
			'include_tags'                     => array(),
			'exclude_tags'                     => array(),
            'select_by_title'                  => [],
            'deselect_by_title'                => [],
            'visibility_settings'              => $this->get_visibility_defaults( $this->visibility_settings_exclude() ),
            'popup_visibility_settings'        => $this->get_popup_visibility_defaults(),
            'popup_visibility_order'           => $this->get_safe_popup_order($this->get_popup_visibility_defaults(), $shortcode_id),

            'title_typography'                 => (object) array(),
            'cat_typography'                   => (object) array(),
            'tag_typography'                   => (object) array(),
            'org_typography'                   => (object) array(),
            'date_typography'                  => (object) array(),
            'details_typography'               => (object) array(),
            'venue_typography'                 => (object) array(),
            'view_details_button_typography'   => (object) array(),
            'google_calendar_button_typography' => (object) array(),
            'title_typography_custom'          => (object) teca_get_typography_field_custom_flag_defaults(),
            'cat_typography_custom'            => (object) teca_get_typography_field_custom_flag_defaults(),
            'tag_typography_custom'            => (object) teca_get_typography_field_custom_flag_defaults(),
            'org_typography_custom'            => (object) teca_get_typography_field_custom_flag_defaults(),
            'date_typography_custom'           => (object) teca_get_typography_field_custom_flag_defaults(),
            'details_typography_custom'        => (object) teca_get_typography_field_custom_flag_defaults(),
            'venue_typography_custom'          => (object) teca_get_typography_field_custom_flag_defaults(),
            'view_details_button_typography_custom' => (object) teca_get_typography_field_custom_flag_defaults(),
            'google_calendar_button_typography_custom' => (object) teca_get_typography_field_custom_flag_defaults(),
            'typography_custom'                => (object) array(),

            'date_formats'                     => array(),

            'filter_by_date'                   => 'off',
            'filter_by_day'                    => 'off',
            'filter_by_category'               => 'off',
            'filter_by_tag'                    => 'off',
            'filter_by_venue'                  => 'off',
            'filter_by_city'                   => 'off',
            'filter_by_state'                  => 'off',
            'filter_by_country'                => 'off',
            'filter_by_organizer'              => 'off',
            'filter_by_cost'                   => 'off',
            'filter_by_time'                   => 'off',
            'filter_by_featured'               => 'off',
            'filter_by_event_status'           => 'off',

            'search_by_title'                  => 'off',
            'search_by_venue'                  => 'off',
            'search_by_organizer'              => 'off',
            'search_by_city'                   => 'off',
            'search_result_limit'              => 10,

            'popup_show_related_events'        => 'on',
            'popup_related_events_title'       => __( 'Related Events', 'the-events-calendar-addon2' ),
            'popup_related_events_limit'       => 3,
            'popup_related_events_sources'     => array( 'category', 'tag', 'venue', 'organizer', 'upcoming' ),

            'color_custom'                     => (object) array(),
           
        ], array_merge(
            teca_get_color_typography_default_settings(),
            teca_get_popup_detail_default_settings()
        ) );
    }   

    

    public function gs_term_options( $term_name ) {
        $terms = gs_get_terms( $term_name );
        $options = [];
        foreach ( $terms as $term ) {
            $options[] = [
                'value' => $term->term_id??'',
                'label' => $term->name??'',
            ];
        }
	    return $options;
    }

    public function get_safe_popup_order( $default_settings, $shortcode_id ) {

        $shortcode_id = absint( $shortcode_id );

        if ( ! $shortcode_id ) {
            return array_keys( $default_settings );
        }

        $saved_order = get_option(
            'gs_teca_popup_visibility_order_' . $shortcode_id,
            []
        );


        if ( ! empty( $saved_order ) && is_array( $saved_order ) ) {
            $default_order = array_keys( $default_settings );
            $ordered       = array_values( array_intersect( $saved_order, $default_order ) );
            $remaining     = array_values( array_diff( $default_order, $ordered ) );

            return array_merge( $ordered, $remaining );
        }

        return array_keys( $default_settings );
    }

    

    public function visibility_settings_exclude() {
		return [
		
            'recent_posts',
            'cat_archive',
            'tags_archive',
            'event_map',
            'event_related_section',
            
		];
	}

    public function get_shortcode_options_link_types() {

        $link_types = [
         
            [
                'label' => __( 'Single Page', 'the-events-calendar-addon2' ),
                'value' => 'single_page'
            ],
            [
                'label' => __( 'Popup', 'the-events-calendar-addon2' ),
                'value' => 'popup'
            ]
        ];

        return $link_types;

    }

    public function get_shortcode_default_prefs() {
        $prefs = [
            
            'gs_teca_nxt_prev'                         => 'off',
            'gs_teca_enable_multilingual'              => 'off',
            'gs_teca_custom_css'                       => '',
            'anchor_tag_rel'                           => 'noopener',
            
        ];

        $translations = $this->get_shortcode_default_translations();

        $prefs = array_merge( $prefs, $translations );

        return $prefs;
    }

    public function get_shortcode_default_translations() {
        $translations = [
            'gs_teca_more'                             => __('More', 'the-events-calendar-addon2'),
            'gs_teca_prev_txt'                         => __('Prev', 'the-events-calendar-addon2'),
            'gs_teca_next_txt'                         => __('Next', 'the-events-calendar-addon2'),
            'gs_teca_view_details_text'                => __( 'View Details', 'the-events-calendar-addon2' ),
            'gs_teca_related_events_title'             => __( 'Related Events', 'the-events-calendar-addon2' ),
            'gs_teca_event_website_text'               => __( 'Event Website', 'the-events-calendar-addon2' ),
            'gs_teca_add_to_calendar_text'             => __( 'Add to calendar', 'the-events-calendar-addon2' ),
        ];

        return $translations;
    }



    public function get_shortcode_options() {

        $shortcodes = $this->_get_shortcodes( [], false, true );

        $_shortcodes = [];

        foreach ( $shortcodes as $shortcode ) {
            $_shortcodes[] = [
                'label' => $shortcode['shortcode_name'],
                'value' => $shortcode['id']
            ];
        }

        return $_shortcodes;
    }

    public function get_layout_options() {

    check_ajax_referer('_gsteca_get_shortcode_layout_options_gs_', '_wpnonce');
        wp_send_json_success( $this->get_shortcode_layout_options() );
    }

    public function override_taxonomy_templates() {
        // Check if we're on a taxonomy archive page
        if (is_category() || is_tag() || is_date()) {
            $layout = get_option('gs_teca_shortcode_layout', array());
            
            // Handle category archives
            if (is_category() && !empty($layout['event_cat']) && !empty($layout['event_cat_shortcode'])) {
                $this->render_shortcode_template($layout['event_cat_shortcode'], $layout['event_cat_replace_type']);
            }
            
            // Handle tag archives
            elseif (is_tag() && !empty($layout['event_tag']) && !empty($layout['event_tag_shortcode'])) {
                $this->render_shortcode_template($layout['event_tag_shortcode'], $layout['event_tag_replace_type']);
            }
            
            // Handle author archives
            elseif (is_author() && !empty($layout['event_author']) && !empty($layout['event_author_shortcode'])) {
                $this->render_shortcode_template($layout['event_author_shortcode'], $layout['event_author_replace_type']);
            }
            
            // Handle date archives
            elseif (is_date() && !empty($layout['event_date']) && !empty($layout['event_date_shortcode'])) {
                $this->render_shortcode_template($layout['event_date_shortcode'], $layout['event_date_replace_type']);
            }
        }
    }

    private function render_shortcode_template($shortcode_id, $replace_type) {
        // Get the shortcode
        $shortcode = plugin()->builder->_get_shortcode($shortcode_id, false);
        
        if (!$shortcode) {
            return; // Shortcode not found
        }
        
        // Modify query based on current archive
        global $wp_query;
        $archive_query = $this->get_archive_query();
        
        if ($replace_type === 'change_all') {
            // Completely replace the content with shortcode
            $this->output_shortcode_content($shortcode_id);
            exit;
        } else {
            // Modify the existing query to use shortcode settings
            $this->modify_query_with_shortcode($shortcode, $archive_query);
        }
    }
    
    private function get_archive_query() {
        
        $q = new \WP_Query();
        $q->query_vars;
        
        if (is_category()) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
            $query_vars['tax_query'] = array(
                array(
                    'taxonomy' => 'tribe_events_cat',
                    'field' => 'term_id',
                    'terms' => get_queried_object()->term_id                )
            );
        } elseif (is_tag()) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query	
            $query_vars['tax_query'] = array(
                array(
                    'taxonomy' => 'post_tag',
                    'field' => 'term_id',
                    'terms' => get_queried_object()->term_id
                )
            );
        } elseif (is_date()) {
            if (is_year()) {
                $query_vars['year'] = get_query_var('year');
            } elseif (is_month()) {
                $query_vars['year'] = get_query_var('year');
                $query_vars['monthnum'] = get_query_var('monthnum');
            } elseif (is_day()) {
                $query_vars['year'] = get_query_var('year');
                $query_vars['monthnum'] = get_query_var('monthnum');
                $query_vars['day'] = get_query_var('day');
            }
        }
        
        return $query_vars;
    }
    
    private function output_shortcode_content($shortcode_id) {
        // Output the shortcode directly
        echo do_shortcode('[gs-teca id="' . $shortcode_id . '"]');
    }
    
    private function modify_query_with_shortcode($shortcode, $archive_query) {
        // Get shortcode settings
        $settings = $shortcode['shortcode_settings'];
        
        // Merge archive query with shortcode settings
        $merged_query = wp_parse_args($archive_query, array(
            'posts_per_page' => $settings['posts'],
            'order' => $settings['order'],
            'orderby' => $settings['orderby']
        ));
        
        // Set the global query for the custom archive template fallback.
        $this->archive_wp_query = new \WP_Query( $merged_query );
        global $wp_query;
        $wp_query = $this->archive_wp_query;
        
        // Load a custom template that uses your shortcode layout
        add_filter('template_include', array($this, 'load_custom_template'));
    }
    
    public function load_custom_template($template) {
        // Load your custom template for taxonomy archives
        $custom_template = GS_TECA_PLUGIN_DIR . 'templates/taxonomy-template.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
        
        return $template;
    }

    public function get_shortcode_layout_options() {
        return [
            'shortcodes' => $this->get_shortcode_options(),
            'replace_types' => [
                [
                    'label' => __( 'No Change', 'the-events-calendar-addon2' ),
                    'value' => 'no_change'
                ],
                [
                    'label' => __( 'Change completely (use all options of the shortcode)', 'the-events-calendar-addon2' ),
                    'value' => 'change_all'
                ]
            ],
            'single_page_style' => $this->get_single_page_style(),
            'date_format_presets' => teca_get_date_format_preset_options(),
            'related_events_sources' => teca_get_related_events_source_options(),
        ];
    }

     public function get_single_page_style() {
        return self::get_free_single_page_style_options();
    }

    /**
     * All Single Page Style options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_all_single_page_style_options() {
        return array(
            array(
                'label' => __( 'Default', 'the-events-calendar-addon2' ),
                'value' => 'default',
            ),
            array(
                'label' => __( 'Style One', 'the-events-calendar-addon2' ),
                'value' => 'style-one',
            ),
            array(
                'label' => __( 'Style Two', 'the-events-calendar-addon2' ),
                'value' => 'style-two',
            ),
            array(
                'label' => __( 'Style Three', 'the-events-calendar-addon2' ),
                'value' => 'style-three',
            ),
            array(
                'label' => __( 'Style Four', 'the-events-calendar-addon2' ),
                'value' => 'style-four',
            ),
            array(
                'label' => __( 'Style Five', 'the-events-calendar-addon2' ),
                'value' => 'style-five',
            ),
        );
    }

    /**
     * Free Single Page Style options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_free_single_page_style_options() {
        $free_slugs = teca_get_free_single_page_style_slugs();
        $all        = self::get_all_single_page_style_options();
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
     * Pro-only Single Page Style options for the admin selector.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function get_pro_single_page_style_options() {
        $free_slugs = teca_get_free_single_page_style_slugs();

        return array_values(
            array_filter(
                self::get_all_single_page_style_options(),
                static function( $item ) use ( $free_slugs ) {
                    return ! in_array( $item['value'] ?? '', $free_slugs, true );
                }
            )
        );
    }

    public function get( $option, $default = '' ) {
        $options = $this->_get_shortcode_pref( false );
        if ( isset( $options[ $option ] ) ) {
            return $options[ $option ];
        }
        return $default;
    }

      public function get_shortcode_prefs_options() {
        return [
            'anchor_tag_rel' => [
                [
                    'label' => __( 'nofollow', 'the-events-calendar-addon2' ),
                    'value' => 'nofollow'
                ],
                [
                    'label' => __( 'noopener', 'the-events-calendar-addon2' ),
                    'value' => 'noopener'
                ],
                [
                    'label' => __( 'noreferrer', 'the-events-calendar-addon2' ),
                    'value' => 'noreferrer'
                ],
                [
                    'label' => __( 'nofollow noopener', 'the-events-calendar-addon2' ),
                    'value' => 'nofollow noopener'
                ],
                [
                    'label' => __( 'nofollow noreferrer', 'the-events-calendar-addon2' ),
                    'value' => 'nofollow noreferrer'
                ],
                [
                    'label' => __( 'noopener noreferrer', 'the-events-calendar-addon2' ),
                    'value' => 'noopener noreferrer'
                ],
                [
                    'label' => __( 'nofollow noopener noreferrer', 'the-events-calendar-addon2' ),
                    'value' => 'nofollow noopener noreferrer'
                ],
            ],
        ];
    }

    public function _save_shortcode_pref( $prefs, $is_ajax ) {

        $prefs = $this->validate_preference( $prefs );
        update_option( $this->option_name, $prefs, 'yes' );
        
        // Clean permalink flush
        delete_option( 'GS_Teca_plugin_permalinks_flushed' );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action( 'gs_teca_preference_update' );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action( 'gsteca_preference_update' );
    
        if ( $is_ajax ) wp_send_json_success( __('Preference saved', 'the-events-calendar-addon2') );
    }

    public function _save_shortcode_layout( $layout, $is_ajax ) {

        $layout = $this->validate_layout( $layout );
        update_option( $this->layout_option_name, $layout, 'yes' );
        
        // Clean permalink flush
        delete_option( 'GS_Teca_plugin_permalinks_flushed' );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action( 'gs_teca_layout_update' );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
        do_action( 'gsteca_layout_update' );
    
        if ( $is_ajax ) wp_send_json_success( __('Layout saved', 'the-events-calendar-addon2') );
    }

    public function save_shortcode_layout( $nonce = null ) {

        $this->verify_ajax_capability();
        check_ajax_referer( '_gsteca_save_shortcode_layout_gs_', '_wpnonce' );

        $layout = $this->get_post_array( 'layout' );

        if ( empty( $layout ) ) {
            wp_send_json_error( esc_html__( 'No layout provided', 'the-events-calendar-addon2' ), 400 );
        }

        $this->_save_shortcode_layout( $layout, true );
    }

    public function get_shortcode_default_layout() {
        $layout = [
            'single_page_style' => 'default',
            'date_formats'      => array(),

            'event_cat'  => false,
            'event_cat_shortcode'  => '',
            'event_cat_replace_type'  => 'no_change',

            'event_tag'  => false,
            'event_tag_shortcode'  => '',
            'event_tag_replace_type'  => 'no_change',

            'show_related_events'     => 'on',
            'related_events_title'    => __( 'Related Events', 'the-events-calendar-addon2' ),
            'related_events_limit'    => 3,
            'related_events_sources'  => array( 'category', 'tag', 'venue', 'organizer', 'upcoming' ),
        ];

        return $layout;
    }

    public function validate_layout( $settings ) {

        $defaults = $this->get_shortcode_default_layout();
        
        // if ( ! is_pro_active() ) {
        //     return $defaults;
        // }

        if ( is_array( $settings ) && array_key_exists( 'show_related_events', $settings ) && is_bool( $settings['show_related_events'] ) ) {
            $settings['show_related_events'] = $settings['show_related_events'] ? 'on' : 'off';
        }

        $settings = shortcode_atts( $defaults, $settings );
        $settings['single_page_style']       = teca_sanitize_single_page_style_setting(
            $settings['single_page_style'] ?? 'default'
        );
        $settings['event_cat']               = wp_validate_boolean( $settings['event_cat'] );
        $settings['event_cat_shortcode']     = absint( $settings['event_cat_shortcode'] );
        $settings['event_cat_replace_type']  = sanitize_text_field( $settings['event_cat_replace_type'] );
        $settings['event_tag']               = wp_validate_boolean( $settings['event_tag'] );
        $settings['event_tag_shortcode']     = absint( $settings['event_tag_shortcode'] );
        $settings['event_tag_replace_type']  = sanitize_text_field( $settings['event_tag_replace_type'] );
        $settings['date_formats']            = teca_sanitize_date_formats_settings( $settings['date_formats'] ?? array() );
        $settings                            = teca_sanitize_single_related_events_settings( $settings );

        return $settings;
    }

    public function save_shortcode_pref( $nonce = null ) {

        $this->verify_ajax_capability();
        check_ajax_referer( '_gsteca_save_shortcode_pref_gs_', '_wpnonce' );

        $prefs = $this->get_post_array( 'prefs' );

        if ( empty( $prefs ) ) {
            wp_send_json_error( esc_html__( 'No preference provided', 'the-events-calendar-addon2' ), 400 );
        }

        $this->_save_shortcode_pref( $prefs, true );
    }


    public function validate_preference( $settings ) {

        $defaults = $this->get_shortcode_default_prefs();
        $settings = shortcode_atts( $defaults, $settings );

        $settings['gs_teca_more']                    = sanitize_text_field( $settings['gs_teca_more'] );
        $settings['gs_teca_prev_txt']                = sanitize_text_field( $settings['gs_teca_prev_txt'] );
        $settings['gs_teca_next_txt']                = sanitize_text_field( $settings['gs_teca_next_txt'] );
        $settings['gs_teca_view_details_text']       = sanitize_text_field( $settings['gs_teca_view_details_text'] );
        $settings['gs_teca_related_events_title']    = sanitize_text_field( $settings['gs_teca_related_events_title'] );
        $settings['gs_teca_event_website_text']      = sanitize_text_field( $settings['gs_teca_event_website_text'] );
        $settings['gs_teca_add_to_calendar_text']    = sanitize_text_field( $settings['gs_teca_add_to_calendar_text'] );

        // Legacy installs may have an empty string saved; treat that as unset so the default shows like "More" / "Prev".
        if ( '' === trim( $settings['gs_teca_add_to_calendar_text'] ) ) {
            $settings['gs_teca_add_to_calendar_text'] = $defaults['gs_teca_add_to_calendar_text'];
        }

        $settings['gs_teca_custom_css']        = wp_strip_all_tags( $settings['gs_teca_custom_css'] );

        return $settings;
    }

    public function _get_shortcode_layout( $is_ajax ) {
        $layout = get_option( $this->layout_option_name, [] );

        if ( ! is_array( $layout ) ) {
            $layout = array();
        }

        $layout = $this->validate_layout( $layout );

        if ( $is_ajax ) {
            wp_send_json_success( $layout );
        }

        return $layout;
    }

    public function get_shortcode_layout() {
       
        return $this->_get_shortcode_layout( wp_doing_ajax() );
    }

    public function _get_shortcode_pref( $is_ajax ) {
        $prefs = get_option( $this->option_name, [] );
        $prefs = $this->validate_preference( $prefs );
        if ( $is_ajax ) wp_send_json_success( $prefs );
        return $prefs;
    }

    public function get_shortcode_pref() {
        return $this->_get_shortcode_pref( wp_doing_ajax() );
    }

    static function maybe_create_shortcodes_table() {

        global $wpdb;

        $gs_teca_db_version = '1.0';

        $saved_db_version = get_option("{$wpdb->prefix}gs_teca_db_version");

        if ( $saved_db_version == $gs_teca_db_version ) return; // vail early

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gs_teca (
            id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
            shortcode_name TEXT NOT NULL,
            shortcode_settings LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id)
        )" . $wpdb->get_charset_collate() . ";";

        if ( $saved_db_version < $gs_teca_db_version ) {
            dbDelta($sql);
        }

        update_option("{$wpdb->prefix}gs_teca_db_version", $gs_teca_db_version);

        if ( $saved_db_version === false ) {
            update_option( 'gsteca_install_demo_shortcodes_initially', true );
        }
    }

    public function create_dummy_shortcodes() {

        $request = wp_remote_get( GS_TECA_PLUGIN_URI . '/includes/demo-data/shortcodes.json', array('sslverify' => false) );

        if (is_wp_error($request)) return false;

        $shortcodes = wp_remote_retrieve_body($request);
        $shortcodes = json_decode($shortcodes, true);

        $wpdb       = $this->gsteca_get_wpdb();
        $table_name = $this->get_gs_teca_table_name();

        if ( !$shortcodes || !count($shortcodes) ) return;

        foreach ($shortcodes as $shortcode) {

            $shortcode['shortcode_settings'] = json_decode($shortcode['shortcode_settings'], true);
            $shortcode['shortcode_settings']['gsteca-demo_data'] = true;

            $data = array(
                "shortcode_name"     => $shortcode['shortcode_name'],
                "shortcode_settings" => json_encode($shortcode['shortcode_settings']),
                "created_at"         => current_time('mysql'),
                "updated_at"         => current_time('mysql'),
            );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table write; values are sanitized and shortcode cache is cleared after write.
            $wpdb->insert( $table_name, $data, $this->get_gsteca_shortcode_db_columns() );
        }

        $this->clear_shortcode_cache();
    }

    public function delete_dummy_shortcodes() {
        $wpdb       = $this->gsteca_get_wpdb();
        $table_name = $this->get_gs_teca_table_name();
        $needle     = 'gsteca-demo_data';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table write; table name is generated internally and sanitized, values are prepared, cache is cleared after write.
        $wpdb->query(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is generated internally and sanitized.
                "DELETE FROM {$table_name} WHERE shortcode_settings LIKE %s",
                '%' . $wpdb->esc_like( $needle ) . '%'
            )
        );

        $this->clear_shortcode_cache();
    }

     public function detail_visibility_settings_exclude() {
		return $this->single_page_fields_visibility_exclude();
	}

	/**
	 * Fields excluded from Single Page sort order and visibility only.
	 *
	 * @return string[]
	 */
	public function single_page_fields_visibility_exclude() {
		return array(
			'view_details_button',
			'event_button',
		);
	}


    public function get_fields_visibility_settings() {
		return $this->_get_fields_visibility_settings( wp_doing_ajax() );
	}

    

	public function _get_fields_visibility_settings( $is_ajax ) {

		$settings = (array) get_option( $this->fields_visibility_option_name, [] );
		$defaults = $this->get_visibility_defaults( $this->detail_visibility_settings_exclude() );

		$settings = shortcode_atts( $defaults, $settings );

		$settings = $this->validate_fields_visibility_settings( $settings );

		if ( $is_ajax ) {
			wp_send_json_success( $settings );
		}

		return $settings;

	}

    public function get_sorted_fields_visibility_settings() {

		$fields_visibility = $this->get_visibility_defaults( $this->single_page_fields_visibility_exclude() );

		$fields_visibility_saved = get_option( 'gs_teca_visibility_order', [] );

		if ( !empty($fields_visibility_saved) ) {
			$fields_visibility_merged = array();
			foreach ( $fields_visibility_saved as $field => $values ) {
				if ( ! array_key_exists( $field, $fields_visibility ) ) continue;
				$fields_visibility_merged[ $field ] = array_merge(
					isset( $fields_visibility[ $field ] ) ? $fields_visibility[ $field ] : array(),
					$values
				);
			}
			return array_merge( $fields_visibility_merged, array_diff_key( $fields_visibility, $fields_visibility_merged ) );
		}

		return $fields_visibility;
	}



    function get_scoped_fields( array $keys, array $sorted_fields = [] ) {
		$scoped = array();
        
		if ( empty( $sorted_fields ) ) {
			$sorted_fields = $this->get_sorted_fields_visibility_settings();
		}

		foreach ( $sorted_fields as $field => $settings ) {
			if ( in_array( $field, $keys, true ) ) {
				$scoped[ $field ] = $settings;
			}
		}

		return $scoped;
	}

    public function get_visibility_defaults( $exclude = [] ) {
        $fields = [
            'event_title' => [
				'translation_key' => 'gs-teca-title',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
            ],
            'event_cat' => [
				'translation_key' => 'gs-teca-cat',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
            ],
			'event_tags' => [
				'translation_key' => 'gs-teca-tags',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => false,
				'mobile' => true,
			],
			'event_date' => [
				'translation_key' => 'gs-teca-date',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
            ],
			'event_time' => [
				'translation_key' => 'gsp-teca-time',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'event_cost' => [
				'translation_key' => 'gsp-teca-cost',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'event_website' => [
				'translation_key' => 'gsp-teca-website',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'event_details' => [
				'translation_key' => 'gs-teca-details',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
            'event_organizer' => [
				'translation_key' => 'gs-teca-organizer',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
            'event_thumbnail' => [
				'translation_key' => 'gs-teca-thumbnail',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
            'event_venue' => [
				'translation_key' => 'gs-teca-venue',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
			],
            'event_map' => [
				'translation_key' => 'gs-teca-map',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
			],
            'google_calendar_button' => [
				'translation_key' => 'gs-teca-google-calendar',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
			],
            'view_details_button' => [
				'translation_key' => 'gs-teca-view-details-button',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
			],
            'event_related_section' => [
				'translation_key' => 'gs-teca-related-events',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
			],
            
		];


		foreach ( $exclude as $key ) {
			if ( isset( $fields[ $key ] ) ) {
				unset( $fields[ $key ] );
			}
		}

		$fields = $this->apply_default_visibility_order( $fields );

		return $fields;
	}

    public function apply_default_visibility_order( $fields ) {

		$default_order = array(
			'event_thumbnail',
			'event_title',
			'event_cat',
			'event_tags',
			'event_date',
			'event_time',
			'event_venue',
			'event_organizer',
			'event_cost',
			'event_map',
			'event_details',
			'event_website',
			'view_details_button',
			'google_calendar_button',
			'event_related_section',
		);

		$sorted = array();

		foreach ( $default_order as $key ) {
			if ( isset( $fields[ $key ] ) ) {
				$sorted[ $key ] = $fields[ $key ];
			}
		}

		foreach ( $fields as $key => $value ) {
			if ( ! isset( $sorted[ $key ] ) ) {
				$sorted[ $key ] = $value;
			}
		}

		return $sorted;
	}

    

    /**
     * Merge saved visibility settings with defaults and normalize device flags.
     *
     * @param array $settings Saved settings.
     * @param array $defaults   Default field map.
     * @param bool  $apply_order Whether to apply default field order.
     * @return array
     */
    private function merge_visibility_settings_with_defaults( array $settings, array $defaults, $apply_order = false ) {
        $settings = array_merge( $defaults, $settings );

        foreach ( $settings as $setting_key => &$setting ) {
            if ( ! isset( $defaults[ $setting_key ] ) ) {
                unset( $settings[ $setting_key ] );
                continue;
            }

            if ( ! is_array( $setting ) ) {
                $setting = array();
            }

            $setting = shortcode_atts( $defaults[ $setting_key ], $setting );
            $setting['translation_key'] = $defaults[ $setting_key ]['translation_key'];
            $setting['desktop']           = wp_validate_boolean( $setting['desktop'] );
            $setting['tablet']            = wp_validate_boolean( $setting['tablet'] );
            $setting['mobile_landscape']  = wp_validate_boolean( $setting['mobile_landscape'] );
            $setting['mobile']            = wp_validate_boolean( $setting['mobile'] );
        }
        unset( $setting );

        if ( $apply_order ) {
            $settings = $this->apply_default_visibility_order( $settings );
        }

        return $settings;
    }

    public function validate_shortcode_visibility_settings( $settings ) {
        $defaults = $this->get_visibility_defaults( $this->visibility_settings_exclude() );

        return $this->merge_visibility_settings_with_defaults( (array) $settings, $defaults, true );
    }

    public function validate_fields_visibility_settings( $settings ) {

		$defaults = $this->get_visibility_defaults( $this->detail_visibility_settings_exclude() );

		return $this->merge_visibility_settings_with_defaults( (array) $settings, $defaults, false );

	}

    public function validate_popup_fields_visibility_settings( $settings ) {

		$settings = (array) $settings;

		if ( isset( $settings['event_button'] ) && ! isset( $settings['view_details_button'] ) ) {
			$settings['view_details_button'] = $settings['event_button'];
		}

		unset( $settings['event_button'] );

		$defaults = $this->get_popup_visibility_defaults();

		return $this->merge_visibility_settings_with_defaults( $settings, $defaults, false );

	}

    public function get_popup_visibility_defaults() {
        $fields = [
            'event_thumbnail' => [
				'translation_key' => 'gsp-teca-thumbnail',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
			],
            'event_cat' => [
				'translation_key' => 'gsp-teca-cat',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
            ],
            'event_title' => [
				'translation_key' => 'gsp-teca-title',
                'desktop' => true,
                'tablet' => true,
				'mobile_landscape' => true,
                'mobile' => true,
            ],
			'event_date' => [
				'translation_key' => 'gsp-teca-date',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'event_time' => [
				'translation_key' => 'gsp-teca-time',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
            'event_venue' => [
				'translation_key' => 'gsp-teca-venue',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
            'event_organizer' => [
				'translation_key' => 'gsp-teca-organizer',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'event_tags' => [
				'translation_key' => 'gsp-teca-tags',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'event_cost' => [
				'translation_key' => 'gsp-teca-cost',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'event_details' => [
				'translation_key' => 'gsp-teca-details',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'event_website' => [
				'translation_key' => 'gsp-teca-website',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'view_details_button' => [
				'translation_key' => 'gs-teca-view-details-button',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
			'google_calendar_button' => [
				'translation_key' => 'gs-teca-google-calendar',
				'desktop' => true,
				'tablet' => true,
				'mobile_landscape' => true,
				'mobile' => true,
			],
		];

		return $fields;
	}

    public function validate_popup_visibility_order( $order, $settings = null ) {
        $defaults = is_array( $settings ) && ! empty( $settings )
            ? $settings
            : $this->get_popup_visibility_defaults();
        $default_order = array_keys( $defaults );

        if ( ! is_array( $order ) ) {
            $order = is_object( $order ) ? array_values( (array) $order ) : array();
        }

        $order = array_map(
            static function( $key ) {
                return 'event_button' === $key ? 'view_details_button' : $key;
            },
            $order
        );
        $order = array_values( array_unique( $order ) );

        if ( empty( $order ) ) {
            return $default_order;
        }

        $ordered   = array_values( array_intersect( $order, $default_order ) );
        $remaining = array_values( array_diff( $default_order, $ordered ) );

        return array_merge( $ordered, $remaining );
    }

    
    public function update_popup_visibility_order() {

        check_ajax_referer(
            'update_teca_popup_visibility_order',
            '_ajax_nonce'
        );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => esc_html__( 'Unauthorised Request', 'the-events-calendar-addon2' ),
                ),
                401
            );
        }

        $shortcode_id = isset( $_POST['shortcode_id'] ) ? absint( wp_unslash( $_POST['shortcode_id'] ) ) : 0;

        if ( ! $shortcode_id ) {
            wp_send_json_error(
                array(
                    'message' => esc_html__( 'Missing shortcode id', 'the-events-calendar-addon2' ),
                ),
                400
            );
        }

        $order = $this->get_post_array( 'order' );

        if ( empty( $order ) ) {
            wp_send_json_error(
                array(
                    'message' => esc_html__( 'Invalid order data', 'the-events-calendar-addon2' ),
                ),
                400
            );
        }

        $validated_order = $this->validate_popup_visibility_order( $order );

        update_option(
            'gs_teca_popup_visibility_order_' . $shortcode_id,
            $validated_order
        );

        wp_send_json_success( 'Popup order saved' );
    }

    
}




