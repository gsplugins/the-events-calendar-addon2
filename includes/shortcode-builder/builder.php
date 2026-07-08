<?php

namespace GS_TECA;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

final class Builder {

    private $option_name = 'gs_teca_shortcode_prefs';
    private $layout_option_name = 'gs_teca_shortcode_layout';
    private $fields_visibility_option_name = 'gs_teca_visibility_settings';

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
            $post->post_title = __('Shortcode Preview', 'posts-grid');
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            $post->post_content = '[gs-teca preview="yes" id="'. esc_attr( sanitize_key( $_REQUEST['gs_teca_shortcode_preview'] ) ) .'"]';
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
        return isset( $_REQUEST['gs_teca_shortcode_preview'] ) && !empty($_REQUEST['gs_teca_shortcode_preview']);
    }

    public function register_sub_menu() {

        add_menu_page(
            __('Events Addon', 'the events calendar addon'),
            __('Events Addon', 'the events calendar addon'),
            'manage_options',
            'gs-the-events-calendar-addon',
            array($this, 'view'),
            GS_TECA_PLUGIN_URI . '/assets/img/events.svg',
            GS_TECA_MENU_POSITION
        );

        add_submenu_page(
            'gs-the-events-calendar-addon',
            __('Shortcodes ', 'the events calendar addon'),
            __('Shortcodes', 'the events calendar addon'),
            'manage_options',
            'gs-the-events-calendar-addon',
            array($this, 'view'),
            10
        );

        add_submenu_page(
            'gs-the-events-calendar-addon',
            __( 'Preferences', 'the-events-calendar-addon' ),
            __( 'Preferences', 'the-events-calendar-addon' ),
            'manage_options',
            'gs-the-events-calendar-addon#/preferences',
            array( $this, 'view' )
        );

        add_submenu_page(
            'gs-the-events-calendar-addon',
            __( 'Layout', 'the-events-calendar-addon' ),
            __( 'Layout', 'the-events-calendar-addon' ),
            'manage_options',
            'gs-the-events-calendar-addon#/layout',
            array( $this, 'view' )
        );

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
        wp_register_script('gs-teca-shortcode', GS_TECA_PLUGIN_URI . 'assets/admin/js/gs-teca-shortcode.min.js', array('jquery'), $js_ver, true);

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
                    'label' => __( 'Default', 'the-events-calendar-addon' ),
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

    public function _get_shortcode($shortcode_id, $is_ajax = false) {
        if ( is_admin() && ! wp_doing_ajax() ) {
        if ( !current_user_can('manage_options')) {
                wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);
            }
        }
        if (empty($shortcode_id)) {
            if ($is_ajax) wp_send_json_error(__('Shortcode ID missing', 'gswps'), 400);
            return false;
        }

        $wpdb = $this->gsteca_get_wpdb();

        $shortcode = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gs_teca WHERE id = %d LIMIT 1", absint($shortcode_id)), ARRAY_A);

        if ($shortcode) {

            $shortcode["shortcode_settings"] = json_decode($shortcode["shortcode_settings"], true);
            $shortcode["shortcode_settings"] = $this->validate_shortcode_settings( $shortcode["shortcode_settings"] );

            wp_cache_add( 'gs_teca_shortcodes_'. $shortcode_id, $shortcode, 'gs_teca' );

            if ($is_ajax) wp_send_json_success($shortcode);

            return $shortcode;
        }

        if ($is_ajax) wp_send_json_error(__('No shortcode found', 'gswps'), 404);

        return false;
    }

     public function _update_shortcode($shortcode_id, $nonce, $fields, $is_ajax) {

        if ( is_admin() && ! wp_doing_ajax() ) {
            if ( ! check_admin_referer('_gsteca_update_shortcode_gs_') || !current_user_can('manage_options') ) { 
                if ($is_ajax) wp_send_json_error(__('Unauthorised Request', 'gswps'), 401); return false; 
            }
        }
        if (empty($shortcode_id)) {
            if ($is_ajax) wp_send_json_error(__('Shortcode ID missing', 'gswps'), 400);
            return false;
        }

        $_shortcode = $this->_get_shortcode($shortcode_id, false);

        if (empty($_shortcode)) {
            if ($is_ajax) wp_send_json_error(__('No shortcode found to update', 'gswps'), 404);
            return false;
        }

        $shortcode_name = !empty($fields['shortcode_name']) ? $fields['shortcode_name'] : $_shortcode['shortcode_name'];
        $shortcode_settings  = !empty($fields['shortcode_settings']) ? $fields['shortcode_settings'] : $_shortcode['shortcode_settings'];

        // Remove dummy indicator on update
        if (isset($shortcode_settings['gsteca-demo_data'])) unset($shortcode_settings['gsteca-demo_data']);

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb = $this->gsteca_get_wpdb();

        $data = array(
            "shortcode_name"         => $shortcode_name,
            "shortcode_settings"     => json_encode($shortcode_settings),
            "updated_at"             => current_time('mysql')
        );

        $num_row_updated = $wpdb->update("{$wpdb->prefix}gs_teca", $data, array('id' => absint($shortcode_id)),  $this->get_gsteca_shortcode_db_columns());

        wp_cache_delete('gs_teca_shortcodes');

        if ( $this->gsteca_check_db_error() ) {
            if ($is_ajax) wp_send_json_error(sprintf(__('Database Error: %1$s', 'gswps'), $wpdb->last_error), 500);
            return false;
        }

        do_action('gs_teca_shortcode_updated', $num_row_updated);
        do_action('gsteca_shortcode_updated', $num_row_updated);

        if ($is_ajax) wp_send_json_success(array(
            'message' => __('Shortcode updated', 'gswps'),
            'shortcode_id' => $num_row_updated
        ));

        return $num_row_updated;
    }

    public function _get_shortcodes($shortcode_ids = [], $is_ajax = false, $minimal = false) {

        $wpdb = $this->gsteca_get_wpdb();
        $fields = $minimal ? 'id, shortcode_name' : '*';

        if (!empty($shortcode_ids)) {

            $how_many = count($shortcode_ids);
            $placeholders = array_fill(0, $how_many, '%d');
            $format = implode(', ', $placeholders);
            $query = "SELECT {$fields} FROM {$wpdb->prefix}gs_teca WHERE id IN($format)";

            $shortcodes = $wpdb->get_results($wpdb->prepare($query, $shortcode_ids), ARRAY_A);
        } else {

            $shortcodes = wp_cache_get('gs_teca_shortcodes');

            if (!empty($shortcodes)) {
                if ($is_ajax) wp_send_json_success($shortcodes);
                return $shortcodes;
            }

            $shortcodes = $wpdb->get_results("SELECT {$fields} FROM {$wpdb->prefix}gs_teca ORDER BY id DESC", ARRAY_A);
        }

        // check for database error
        if ($this->gsteca_check_db_error()) wp_send_json_error(sprintf(__('Database Error: %s'), $wpdb->last_error));

        if (empty($shortcode_ids)) wp_cache_set('gs_teca_shortcodes', $shortcodes, '', DAY_IN_SECONDS);

        if ($is_ajax) wp_send_json_success($shortcodes);

        return $shortcodes;
    }

    public function create_shortcode() {

        // validate nonce && check permission
        if ( is_admin() && ! wp_doing_ajax() ) {
            if (!check_admin_referer('_gsteca_create_shortcode_gs_') || !current_user_can('manage_options')) wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);
        }
        $shortcode_settings  = !empty($_POST['shortcode_settings']) ? $_POST['shortcode_settings'] : '';
        $shortcode_name  = !empty($_POST['shortcode_name']) ? $_POST['shortcode_name'] : __('Undefined', 'gswps');

        if (empty($shortcode_settings) || !is_array($shortcode_settings)) {
            wp_send_json_error(__('Please configure the settings properly', 'gswps'), 206);
        }

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb = $this->gsteca_get_wpdb();

        $data = array(
            "shortcode_name" => $shortcode_name,
            "shortcode_settings" => json_encode($shortcode_settings),
            "created_at" => current_time('mysql'),
            "updated_at" => current_time('mysql'),
        );

        $wpdb->insert("{$wpdb->prefix}gs_teca", $data, $this->get_gsteca_shortcode_db_columns());

        // check for database error
        if ($this->gsteca_check_db_error()) wp_send_json_error(sprintf(__('Database Error: %s'), $wpdb->last_error), 500);

        wp_cache_delete('gs_teca_shortcodes');

        do_action('gs_teca_shortcode_created', $wpdb->insert_id);
        do_action('gsteca_shortcode_created', $wpdb->insert_id);

        do_action('gs-teca-shortcode-fired');

        // send success response with inserted id
        wp_send_json_success(array(
            'message' => __('Shortcode created successfully', 'gswps'),
            'shortcode_id' => $wpdb->insert_id
        ));
    }

    public function clone_shortcode() {

        // validate nonce && check permission
        if ( is_admin() && ! wp_doing_ajax() ) {
            if (!check_admin_referer('_gsteca_clone_shortcode_gs_') || !current_user_can('manage_options')) wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);
        }
        $clone_id  = !empty($_POST['clone_id']) ? $_POST['clone_id'] : '';

        if (empty($clone_id)) wp_send_json_error(__('Clone Id not provided', 'gswps'), 400);

        $clone_shortcode = $this->_get_shortcode($clone_id, false);

        if (empty($clone_shortcode)) wp_send_json_error(__('Clone shortcode not found', 'gswps'), 404);

        $shortcode_settings  = $clone_shortcode['shortcode_settings'];
        $shortcode_name  = $clone_shortcode['shortcode_name'] . ' ' . __('- Cloned', 'gswps');

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb = $this->gsteca_get_wpdb();

        $data = array(
            "shortcode_name" => $shortcode_name,
            "shortcode_settings" => json_encode($shortcode_settings),
            "created_at" => current_time('mysql'),
            "updated_at" => current_time('mysql'),
        );

        $wpdb->insert("{$wpdb->prefix}gs_teca", $data, $this->get_gsteca_shortcode_db_columns());

        // check for database error
        if ($this->gsteca_check_db_error()) wp_send_json_error(sprintf(__('Database Error: %s'), $wpdb->last_error), 500);

        wp_cache_delete('gs_teca_shortcodes');

        // Get the cloned shortcode
        $shotcode = $this->_get_shortcode($wpdb->insert_id, false);

        // send success response with inserted id
        wp_send_json_success(array(
            'message' => __('Shortcode cloned successfully', 'gswps'),
            'shortcode' => $shotcode,
        ));
    }

    public function get_shortcode() {

        $shortcode_id = !empty($_GET['id']) ? absint($_GET['id']) : null;

        $this->_get_shortcode($shortcode_id, wp_doing_ajax());
    }

    public function update_shortcode($shortcode_id = null, $nonce = null) {

        $shortcode_id = !empty($_POST['id']) ? absint($_POST['id']) : null;
            
        if ( ! $nonce ) {
            $nonce = $_POST['_wpnonce'] ?: null;
        }

        if (empty($shortcode_id)) {
            wp_send_json_error(__('Shortcode ID missing', 'gswps'), 400);
        }

        $this->_update_shortcode($shortcode_id, $nonce, $_POST, true);
    }

    public function delete_shortcodes() {

        if ( is_admin() && ! wp_doing_ajax() ) {
            if (!check_admin_referer('_gsteca_delete_shortcodes_gs_') || !current_user_can('manage_options')) {
                wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);
            }
        }   
        $ids = isset($_POST['ids']) ? $_POST['ids'] : null;

        if (empty($ids)) {
            wp_send_json_error(__('No shortcode ids provided', 'gswps'), 400);
        }

        $wpdb = $this->gsteca_get_wpdb();

        $count = count($ids);

        $ids = implode(',', array_map('absint', $ids));
        $wpdb->query("DELETE FROM {$wpdb->prefix}gs_teca WHERE ID IN($ids)");

        wp_cache_delete('gs_woo_shortcodes');

        if ($this->gsteca_check_db_error()) wp_send_json_error(sprintf(__('Database Error: %s'), $wpdb->last_error), 500);

        $m = _n("Shortcode has been deleted", "Shortcodes have been deleted", $count, 'gswps');

        wp_send_json_success(['message' => $m]);
    }

    public function get_shortcodes() {

        $this->_get_shortcodes(null, wp_doing_ajax());
    }

    public function temp_save_shortcode_settings() {

        if ( is_admin() && ! wp_doing_ajax() ) {
            if ( ! check_admin_referer('_gsteca_temp_save_shortcode_settings_gs_') || !current_user_can('manage_options') ) {
                wp_send_json_error( __( 'Unauthorised Request', 'gswps' ), 401 );
            }
        }
        $temp_key = isset( $_POST['temp_key'] ) ? $_POST['temp_key'] : null;
        $shortcode_settings = isset( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : [];

        if ( empty($temp_key) ) wp_send_json_error(__('No temp key provided', 'gswps'), 400);
        if ( empty($shortcode_settings) ) wp_send_json_error(__('No temp settings provided', 'gswps'), 400);

        delete_transient( $temp_key );
        set_transient( $temp_key, $this->validate_shortcode_settings( $shortcode_settings ), 86400 ); // save the transient for 1 day

        wp_send_json_success([
            'message' => __('Temp data saved', 'gswps'),
        ]);
    }

    public function get_translation_strings() {
        return [
            'columns'                             => __('Columns', 'the-events-calendar-addon'),
            'columns_tablet'                      => __('Columns Tablet', 'the-events-calendar-addon'),
            'columns_mobile_portrait'             => __('Columns Portpait Mobile', 'the-events-calendar-addon'),
            'columns_mobile'                      => __('Columns Mobile', 'the-events-calendar-addon'),

            'desktop'                              => __( 'Desktop', 'the-events-calendar-addon' ),
			'tablet'                               => __( 'Tablet', 'the-events-calendar-addon' ),
			'mobile_landscape'                     => __( 'Large Mobile', 'the-events-calendar-addon' ),
			'mobile'                               => __( 'Mobile', 'the-events-calendar-addon  ' ),

            'exclude_cats'                        => __('Exclude By Cats', 'gswps'),
            'deselect_by_name'                    => __('Exclude Specific Products', 'gswps'),
            'deselect_by_tag'                     => __('Exclude By Tags', 'gswps'),
            'select_by_tag'                       => __('Include By Tags', 'gswps'),
            'select_by_name'                      => __('Display Specific Products', 'gswps'),
            'select_products'                     => __('Select Products', 'gswps'),
            'select_cats'                         => __('Include By Cats', 'gswps'),

            'custom-css'                          => __('Custom CSS', 'gswps'),
            'shortcodes'                          => __('Shortcodes', 'gswps'),
            'global-settings-for-gs-woo-slider'   => __('Global Settings for Woo Product Views', 'gswps'),
            'all-shortcodes-for-gs-woo-slider'    => __('All shortcodes for Woo Product Views', 'gswps'),
            'create-shortcode'                    => __('Create Shortcode', 'gswps'),
            'create-new-shortcode'                => __('Create New Shortcode', 'gswps'),
            'shortcode'                           => __('Shortcode', 'gswps'),
            'name'                                => __('Name', 'gswps'),
            'action'                              => __('Action', 'gswps'),
            'actions'                             => __('Actions', 'gswps'),
            'edit'                                => __('Edit', 'gswps'),
            'clone'                               => __('Clone', 'gswps'),
            'delete'                              => __('Delete', 'gswps'),
            'delete-all'                          => __('Delete All', 'gswps'),
            'create-a-new-shortcode-and'          => __('Create a new shortcode & save it to use globally in anywhere', 'gswps'),
            'edit-shortcode'                      => __('Edit Shortcode', 'gswps'),
            'general-settings'                    => __('General', 'gswps'),
            'style-settings'                      => __('Style', 'gswps'),
            'query-settings'                      => __('Query', 'gswps'),
            'visibility-settings'                 => __('Visibility', 'the-events-calendar-addon'),
            'shortcode-name'                      => __('Shortcode Name', 'gswps'),
            'name-of-the-shortcode'               => __('Shortcode Name', 'gswps'),
            'save-shortcode'                      => __('Save Shortcode', 'gswps'),
            'preview-shortcode'                   => __('Preview Shortcode', 'gswps'),


            'gs_teca_template'                    => __('Theme Style', 'the-events-calendar-addon'),
            'view_type'                           => __('View Type', 'the-events-calendar-addon'),
            'view_type--help'                     => __('Select Theme Style', 'the-events-calendar-addon'),
            'daily_calendar_layout'               => __( 'Daily Calendar Layout', 'the-events-calendar-addon' ),
            'weekly_calendar_layout'              => __( 'Weekly Calendar Layout', 'the-events-calendar-addon' ),
            'monthly_calendar_layout'             => __( 'Monthly Calendar Layout', 'the-events-calendar-addon' ),
            'quarterly_calendar_layout'           => __( 'Quarterly Calendar Layout', 'the-events-calendar-addon' ),
            'yearly_calendar_layout'              => __( 'Yearly Calendar Layout', 'the-events-calendar-addon' ),
            'calendar_layout'                     => __( 'Calendar Layout', 'the-events-calendar-addon' ),
            'calendar_select_filter'              => __( 'Select Filter', 'the-events-calendar-addon' ),
            'events_section'                      => __( 'Events Section', 'the-events-calendar-addon' ),
            'event_layout'                        => __( 'Event Layout', 'the-events-calendar-addon' ),
            'venue_template'                      => __( 'Venue Template', 'the-events-calendar-addon' ),
            'venue_template_layout'               => __( 'Venue Template Name', 'the-events-calendar-addon' ),
            'organizer_template'                  => __( 'Organizer Template', 'the-events-calendar-addon' ),
            'organizer_template_layout'           => __( 'Organizer Template Name', 'the-events-calendar-addon' ),

            'gs_teca_slide_speed' => __('Sliding Speed', 'posts-grid'),
            'gs_teca_slide_speed--help' => __('Set the speed in millisecond. Default 500 ms. To disable autoplay just set the speed 0', 'posts-grid'),
            
            'gs_teca_is_autop' => __('Autoplay', 'posts-grid'),
            'gs-teca-play-pause--help' => __('Enable/Disable Auto play to change the slides automatically after certain time. Default On', 'posts-grid'),

            'gs_teca_autop_pause' => __('Autoplay Delay', 'posts-grid'),
            'gs-teca-autop-pause--help' => __('You can adjust the time (in ms) between each slide. Default 4000 ms', 'posts-grid'),

            'gs_teca_inf_loop' => __('Infinite Loop', 'posts-grid'),
            'gs-teca-inf-loop--help' => __('If ON, clicking on "Next" while on the last slide will start over from first slide and vice-versa', 'posts-grid'),

            'gs_teca_pause_on_hover' => __('Pause on hover', 'posts-grid'),
            'gs-teca-slider-stop--help' => __('Autoplay will pause when mouse hovers over Post. Default On', 'posts-grid'),

            'gs-teca-reverse-direction' => __('Reverse Direction', 'posts-grid'),
            'gs-teca-reverse-direction--help' => __('Reverse the direction of movement. Default Off', 'posts-grid'),

            'gs-teca-slider-navs'                       => __( 'Slider Navs', 'posts-grid' ),
			'gs-teca-slider-navs--help'                 => __( 'Next / Previous control for Portfolio Slider. Default On Controls are not available when Ticker Mode is enabled', 'posts-grid' ),
			'gs-teca-ctrl-pos'                          => __( 'Navs Position', 'posts-grid' ),
			'gs-teca-ctrl-pos--placeholder'             => __( 'Position of Next / Previous control for Portfolio Slider. Default Bottom', 'posts-grid' ),
            'gs-teca-slider-dots'                       => __( 'Slider Dots', 'posts-grid' ),
			'gs-teca-slider-dots--help'                 => __( 'Dots control for Portfolio Slider below the widget. Default Off', 'posts-grid' ),
            'gs_teca_navs_style'                         => __( 'Navs Style', 'posts-grid' ),
			'gs_teca_dots_style'                         => __( 'Dots Style', 'posts-grid' ),
            'gs-filter-by'                           => __( 'Filter By', 'posts-grid' ),
			'gs-filter-cat'                          => __( 'Filter Position', 'posts-grid' ),
            'gs_teca_filter_style'                       => __( 'Filter Styles', 'posts-grid' ),
            'filter_type' => __('Filter Type', 'posts-grid'),
            'filter_type__details' => __('Select filter type', 'posts-grid'),
            'posts' => __('Posts', 'posts-grid'),
            'posts--placeholder' => __('Posts', 'posts-grid'),
            'posts--help' => __('Set max posts numbers you want to show, set -1 for all posts', 'posts-grid'),
            'gs_teca_pagination' => __('Enable Pagination', 'posts-grid'),
            'gs_teca_pagination__details' => __('Enable paginations like number pagination, load more button, On scroll load etc.', 'posts-grid'),
            'pagination_type' => __('Pagination Type', 'posts-grid'),
            'pagination_type__details' => __('Select pagination type.', 'posts-grid'),
            'pagination_type_pro_message'            => __( 'This pagination type is available in the Pro version only.', 'the-events-calendar-addon' ),
            'typography_color_pro_message'           => __( 'This typography control is available in the Pro version only.', 'the-events-calendar-addon' ),
            'query_include_exclude_categories_pro_message' => __( 'Categories in Include/Exclude are available in the Pro version only.', 'the-events-calendar-addon' ),
            'query_include_exclude_tags_pro_message'       => __( 'Tags in Include/Exclude are available in the Pro version only.', 'the-events-calendar-addon' ),
            'query_custom_order_pro_message'               => __( 'Custom Order is available in the Pro version only.', 'the-events-calendar-addon' ),

            'initial_items'     => __('Initial Items', 'posts-grid'),
            'initial_items__details'    => __('Set initial number of items that shows on page load (before users interaction)', 'posts-grid'),

            'load_per_click' => __('Per Click', 'posts-grid'),
            'load_per_click__details' => __('Load members per button click', 'posts-grid'),

            'item_per_page' => __('Per Page', 'posts-grid'),
            'item_per_page__details' => __('Display members per page', 'posts-grid'),

            'per_load' => __('Per Load', 'posts-grid'),
            'per_load__details' => __('Display members per load', 'posts-grid'),

            'load_button_text' => __('Button Text', 'posts-grid'),
            'load_button_text__details' => __('Load more button text', 'posts-grid'),

            'popup_style' => __('Popup Style', 'posts-grid'),
            'popup_style__details' => __('Select popup style, this is available for certain theme', 'posts-grid'),
            'gs_teca_name_is_linked' => __('Link Events', 'posts-grid'),
            'gs_teca_name_is_linked__details' => __('Add links to title, description & image to display popup or to single page', 'posts-grid'),
            'gs_teca_link_type' => __('Link Type', 'posts-grid'),
            'gs_teca_link_type__details' => __('Choose the link type of The Events Calendar Addon', 'posts-grid'),
            'pref-more' => __('More', 'posts-grid'),
            'pref-more-details' => __('Replace with preferred text for More', 'posts-grid'),
            'pref-view-details' => __('View Details Button Text', 'the-events-calendar-addon'),
            'pref-view-details-details' => __('Replace with preferred text for View Details buttons', 'the-events-calendar-addon'),
            'pref-related-events-title' => __('Related Events Section Title', 'the-events-calendar-addon'),
            'pref-related-events-title-details' => __('Replace with preferred text for Related Events section titles', 'the-events-calendar-addon'),
            'pref-event-website' => __('Event Website Button Text', 'the-events-calendar-addon'),
            'pref-event-website-details' => __('Replace with preferred text for Event Website buttons', 'the-events-calendar-addon'),
            'pref-add-to-calendar' => __('Add to Calendar Button Text', 'the-events-calendar-addon'),
            'pref-add-to-calendar-details' => __('Replace with preferred text for Add to Calendar buttons', 'the-events-calendar-addon'),
            'prev' => __('Prev', 'posts-grid'),
            'prev-details' => __('Replace with preferred text for Prev', 'posts-grid'),
            'next' => __('Next', 'posts-grid'),
            'next-details' => __('Replace with preferred text for Next', 'posts-grid'),
            'link-text' => __('View More', 'posts-grid'),
            'link-text--details' => __('Replace with preferred text for View More', 'posts-grid'),
            'enable-multilingual' => __('Enable Multilingual', 'posts-grid'),
            'enable-multilingual--details' => __('Enable Multilingual mode to translate below strings using any Multilingual plugin like wpml or loco translate.', 'posts-grid'),
            'image_filter'                       => __('Image Filter', 'posts-grid'),
            'image_filter_hover'                 => __('Image Filter Hover', 'posts-grid'),

            'title_typography'                   => __( 'Title Typography', 'the-events-calendar-addon' ),
            'cat_typography'                     => __( 'Category Typography', 'the-events-calendar-addon' ),
            'tag_typography'                     => __( 'Tag Typography', 'the-events-calendar-addon' ),
            'org_typography'                     => __( 'Organizer Typography', 'the-events-calendar-addon' ),
            'date_typography'                    => __( 'Date Typography', 'the-events-calendar-addon' ),
            'date_format'                        => __( 'Date Format', 'the-events-calendar-addon' ),
            'custom_date_format'                 => __( 'Custom Date Format', 'the-events-calendar-addon' ),
            'date_format__help'                  => __( 'Applies to readable event date text only. Decorative date badges and calendar day numbers are not changed.', 'the-events-calendar-addon' ),
            'custom_date_format__help'           => __( 'Use WordPress/PHP date format characters, e.g. F j, Y or d M Y.', 'the-events-calendar-addon' ),
            'details_typography'                 => __( 'Details Typography', 'the-events-calendar-addon' ),
            'venue_typography'                   => __( 'Venue Typography', 'the-events-calendar-addon' ),
            'view_details_button_typography'     => __( 'View Details Button Typography', 'the-events-calendar-addon' ),
            'google_calendar_button_typography'  => __( 'Google Calendar Button Typography', 'the-events-calendar-addon' ),
            'style_accordion_typography'         => __( 'Typography', 'the-events-calendar-addon' ),
            'style_accordion_color_typography'   => __( 'Color Typography', 'the-events-calendar-addon' ),
            'style_accordion_detail_typography'  => __( 'Detail Typography', 'the-events-calendar-addon' ),
            'style_accordion_detail_color_typography' => __( 'Detail Color Typography', 'the-events-calendar-addon' ),
            'style_accordion_filters_by'           => __( 'Filters By', 'the-events-calendar-addon' ),
            'style_accordion_search_by'            => __( 'Search By', 'the-events-calendar-addon' ),
            'search_by_title'                      => __( 'Title', 'the-events-calendar-addon' ),
            'search_by_venue'                      => __( 'Venue', 'the-events-calendar-addon' ),
            'search_by_organizer'                  => __( 'Organizer', 'the-events-calendar-addon' ),
            'search_by_city'                       => __( 'City', 'the-events-calendar-addon' ),
            'search_result_limit'                  => __( 'Result Limit', 'the-events-calendar-addon' ),
            'search_result_limit__details'         => __( 'Maximum number of events returned by AJAX search.', 'the-events-calendar-addon' ),
            'filter_by_date'                       => __( 'Date', 'the-events-calendar-addon' ),
            'filter_by_day'                        => __( 'Day', 'the-events-calendar-addon' ),
            'filter_by_category'                   => __( 'Category', 'the-events-calendar-addon' ),
            'filter_by_tag'                        => __( 'Tag', 'the-events-calendar-addon' ),
            'filter_by_venue'                      => __( 'Venue', 'the-events-calendar-addon' ),
            'filter_by_city'                       => __( 'City', 'the-events-calendar-addon' ),
            'filter_by_state'                      => __( 'State', 'the-events-calendar-addon' ),
            'filter_by_country'                    => __( 'Country', 'the-events-calendar-addon' ),
            'filter_by_organizer'                  => __( 'Organizer', 'the-events-calendar-addon' ),
            'filter_by_cost'                       => __( 'Cost', 'the-events-calendar-addon' ),
            'filter_by_time'                       => __( 'Time', 'the-events-calendar-addon' ),
            'filter_by_featured'                   => __( 'Featured Events', 'the-events-calendar-addon' ),
            'filter_by_event_status'               => __( 'Event Status', 'the-events-calendar-addon' ),
            'teca_filter_date_clear'               => __( 'Clear', 'the-events-calendar-addon' ),
            'teca_filter_all_venues'               => __( 'All Venues', 'the-events-calendar-addon' ),
            'teca_filter_all_categories'           => __( 'All Categories', 'the-events-calendar-addon' ),
            'teca_filter_all_tags'                 => __( 'All Tags', 'the-events-calendar-addon' ),
            'teca_filter_all_organizers'           => __( 'All Organizers', 'the-events-calendar-addon' ),
            'teca_filter_all_cities'               => __( 'All Cities', 'the-events-calendar-addon' ),
            'teca_filter_all_states'               => __( 'All States', 'the-events-calendar-addon' ),
            'teca_filter_all_countries'            => __( 'All Countries', 'the-events-calendar-addon' ),
            'teca_filter_all_costs'                => __( 'All Costs', 'the-events-calendar-addon' ),
            'teca_filter_cost_free'                => __( 'Free', 'the-events-calendar-addon' ),
            'teca_filter_cost_paid'                => __( 'Paid', 'the-events-calendar-addon' ),
            'teca_filter_all_times'                => __( 'All Times', 'the-events-calendar-addon' ),
            'teca_filter_time_morning'             => __( 'Morning', 'the-events-calendar-addon' ),
            'teca_filter_time_afternoon'           => __( 'Afternoon', 'the-events-calendar-addon' ),
            'teca_filter_time_evening'             => __( 'Evening', 'the-events-calendar-addon' ),
            'teca_filter_time_night'               => __( 'Night', 'the-events-calendar-addon' ),
            'teca_filter_all_events'               => __( 'All Events', 'the-events-calendar-addon' ),
            'teca_filter_featured_only'            => __( 'Featured Only', 'the-events-calendar-addon' ),
            'teca_filter_not_featured'             => __( 'Non-Featured', 'the-events-calendar-addon' ),
            'teca_filter_all_statuses'             => __( 'All Statuses', 'the-events-calendar-addon' ),
            'teca_filter_status_upcoming'          => __( 'Upcoming', 'the-events-calendar-addon' ),
            'teca_filter_status_ongoing'           => __( 'Ongoing', 'the-events-calendar-addon' ),
            'teca_filter_status_past'              => __( 'Past', 'the-events-calendar-addon' ),
            'teca_filters_by_name_empty_message'   => __( 'No events found.', 'the-events-calendar-addon' ),

            'details-length-type'                => __( 'Details Length Type', 'the-events-calendar-addon' ),
            'details-length'                     => __( 'Details Length', 'the-events-calendar-addon' ),
            'words'                              => __( 'Words', 'the-events-calendar-addon' ),
            'letter'                             => __( 'Letter', 'the-events-calendar-addon' ),
            'details-length-type--help'          => __( 'Choose whether to limit details by word count or letter count', 'the-events-calendar-addon' ),
            'details-length--help'               => __( 'Increase or decrease the number of words or letters to display in event details', 'the-events-calendar-addon' ),

            'gs-teca-title'                          => __('Event Title', 'posts-grid'),
            'gs-teca-cat'                            => __('Event Category', 'posts-grid'),
            'gs-teca-tags'                           => __('Event Tags', 'posts-grid'),
            'gs-teca-date'                           => __('Event Date', 'posts-grid'),
            'gs-teca-details'                        => __('Event Details', 'posts-grid'),
            'gs-teca-thumbnail'                      => __('Event Thumbnail', 'posts-grid'),
            'gs-teca-organizer'                      => __('Event Organizer', 'posts-grid'),
            'gs-teca-venue'                          => __('Event Venue' , 'posts-grid'),
            'gs-teca-map'                            => __('Map', 'the-events-calendar-addon'),
            'gs-teca-related-events'                 => __('Related Events', 'the-events-calendar-addon'),
            'show_related_events'                    => __('Show Related Events', 'the-events-calendar-addon'),
            'show_related_events__details'           => __('Display related upcoming events below the single event page details.', 'the-events-calendar-addon'),
            'related_events_title'                   => __('Related Events Title', 'the-events-calendar-addon'),
            'related_events_title__details'          => __('Heading text for the single page related events section.', 'the-events-calendar-addon'),
            'related_events_limit'                   => __('Related Events Limit', 'the-events-calendar-addon'),
            'related_events_limit__details'          => __('Maximum number of related events to display on the single page (1-12).', 'the-events-calendar-addon'),
            'related_events_sources'                 => __('Related Events Based On', 'the-events-calendar-addon'),
            'related_events_sources__details'        => __('Choose how single page related events are matched. Sources are tried in order until the limit is reached.', 'the-events-calendar-addon'),
            'popup_show_related_events'                    => __('Show Related Events', 'the-events-calendar-addon'),
            'popup_show_related_events__details'           => __('Display related upcoming events below the popup event details.', 'the-events-calendar-addon'),
            'popup_related_events_title'                   => __('Related Events Title', 'the-events-calendar-addon'),
            'popup_related_events_title__details'          => __('Heading text for the popup related events section.', 'the-events-calendar-addon'),
            'popup_related_events_limit'                   => __('Related Events Limit', 'the-events-calendar-addon'),
            'popup_related_events_limit__details'          => __('Maximum number of related events to display in the popup (1-12).', 'the-events-calendar-addon'),
            'popup_related_events_sources'                 => __('Related Events Based On', 'the-events-calendar-addon'),
            'popup_related_events_sources__details'        => __('Choose how popup related events are matched. Sources are tried in order until the limit is reached.', 'the-events-calendar-addon'),

            'gsp-teca-title'                         => __('Event Title', 'posts-grid'),
            'gsp-teca-cat'                           => __('Event Category', 'posts-grid'),
            'gsp-teca-tags'                          => __('Event Tags', 'posts-grid'),
            'gsp-teca-date'                          => __('Event Date', 'posts-grid'),
            'gsp-teca-details'                       => __('Event Details', 'posts-grid'),
            'gsp-teca-organizer'                     => __('Event Organizer', 'posts-grid'),
            'gsp-teca-venue'                         => __('Event Venue', 'posts-grid'),
            'gsp-teca-thumbnail'                     => __('Event Thumbnail' , 'posts-grid'),
            'gsp-teca-time'                          => __('Event Time', 'the-events-calendar-addon'),
            'gsp-teca-cost'                          => __('Event Cost', 'the-events-calendar-addon'),
            'gsp-teca-website'                       => __('Event Website', 'the-events-calendar-addon'),
            'gsp-teca-button'                        => __( 'View Details', 'the-events-calendar-addon' ),
            'gs-teca-view-details-button'            => __( 'View Details', 'the-events-calendar-addon' ),
            'gs-teca-google-calendar'                => __( 'Google Calendar', 'the-events-calendar-addon' ),

            'single_page_style'                      => __('Single Page Style', 'posts-grid'),
            'single_page_style_pro_message'          => __( 'This Single Style is available in the Pro version only.', 'the-events-calendar-addon' ),
            'single_teca_page'                       => __('Event Single Page Style', 'posts-grid'),
            'event_cat'                              => __('Category Archive Style', 'posts-grid'),
            'event_tag'                              => __('Tag Archive Style', 'posts-grid'),
            'event_select_shortcode'                 => __('Select Shortcode', 'posts-grid'),
            'event_replace_type'                     => __('Way To Retrieve Page', 'posts-grid'),

            'include_tags'                           => __( 'Tags', 'posts-grid' ),
			'exclude_tags'                           => __( 'Tags', 'posts-grid' ),
            'group'                                  => __( 'Categories', 'posts-grid' ),
			'group__help'                            => __( 'Select specific event category to show that specific category events', 'posts-grid' ),
			'exclude_group'                          => __( 'Categories', 'posts-grid' ),
			'exclude_group__help'                    => __( 'Select specific event category to hide that specific category events', 'posts-grid' ),

            'cat-order-by'                           => __( 'Category Order By', 'posts-grid' ),
			'cat_order'                              => __( 'Category Order', 'posts-grid' ),

            'select-by-title'                        => __('Specific Events', 'posts-grid'),
            'deselect-by-title'                      => __('Exclude Specific Events', 'posts-grid'),

            'posts'                                  => __('Posts', 'posts-grid'),
            'posts--placeholder'                     => __('Posts', 'posts-grid'),
            'posts--help'                            => __('Set max posts numbers you want to show, set -1 for all posts', 'posts-grid'),

            'order'                                  => __('Order', 'posts-grid'),
            'order--placeholder'                     => __('Order', 'posts-grid'),

            'order-by'                               => __('Order By', 'posts-grid'),

            'global-settings-for-teca'               => __('Global Settings for The Events Calendar Addon', 'posts-grid'),
            'preference'                             => __('Preference', 'posts-grid'),
            'save-preference'                        => __('Save Preference', 'posts-grid'),
            
            'custom-css'                             => __('Custom CSS', 'posts-grid'),
            'anchor-tag-rel'                         => __('Anchor Tag Rel', 'posts-grid'),
            'anchor_tag_rel--details'                => __( 'Select Anchor Tag rel attribute\'s value, to improve security and SEO, by default the value is dofollow.', 'posts-grid' ),
            
            'install-demo-data'                      => __( 'Install Demo Data', 'gsportfolio' ),
			'install-demo-data-description'          => __( 'Quick start with GS Plugins by installing the demo data', 'gsportfolio' ),
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
                'label' => __( 'Left', 'posts-grid' ),
                'value' => 'left',
            ],
            [
                'label' => __( 'Center', 'posts-grid' ),
                'value' => 'center',
            ],
            [
                'label' => __( 'Right', 'posts-grid' ),
                'value' => 'right',
            ],
        ];
    }

    public function get_columns() {

        $columns = [
            [
                'label' => __( '1 Column', 'gswps' ),
                'value' => '12'
            ],
            [
                'label' => __( '2 Columns', 'gswps' ),
                'value' => '6'
            ],
            [
                'label' => __( '3 Columns', 'gswps' ),
                'value' => '4'
            ],
            [
                'label' => __( '4 Columns', 'gswps' ),
                'value' => '3'
            ],
            [
                'label' => __( '5 Columns', 'gswps' ),
                'value' => '2_4'
            ],
            [
                'label' => __( '6 Columns', 'gswps' ),
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
                    'label' => __( 'DESC', 'posts-grid' ),
                    'value' => 'DESC'
                ],
                [
                    'label' => __( 'ASC', 'posts-grid' ),
                    'value' => 'ASC'
                ],
            ],

            'cat_order' => array(
				array(
					'label' => __( 'ASC', 'posts-grid' ),
					'value' => 'asc',
				),
				array(
					'label' => __( 'DESC', 'posts-grid' ),
					'value' => 'desc',
				),
			),
           
        ];
    }

    public function get_details_length_type_options() {
        return array(
            array(
                'label' => __( 'Words', 'the-events-calendar-addon' ),
                'value' => 'words',
            ),
            array(
                'label' => __( 'Letter', 'the-events-calendar-addon' ),
                'value' => 'letter',
            ),
        );
    }

    public function get_orderby_options() {

        $options = [
            [
                'label' => __( 'Post ID', 'posts-grid' ),
                'value' => 'ID',
            ],
            [
                'label' => __( 'Post Name', 'posts-grid' ),
                'value' => 'title',
            ],
            [
                'label' => __( 'Date', 'posts-grid' ),
                'value' => 'date',
            ],
            [
                'label' => __( 'Random', 'posts-grid' ),
                'value' => 'rand',
            ],
            [
                'label' => __( 'Custom Order', 'posts-grid' ),
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
                'label' => __( 'Default', 'posts-grid' ),
                'value' => 'none',
            ],
            [
                'label' => __( 'Category ID', 'posts-grid' ),
                'value' => 'id',
            ],
            [
                'label' => __( 'Category Name', 'posts-grid' ),
                'value' => 'name',
            ],
            [
                'label' => __( 'Custom Order', 'posts-grid' ),
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
                'label' => __('None', 'posts-grid'),
                'value' => 'none'
            ],
            [
                'label' => __('Blur', 'posts-grid'),
                'value' => 'blur'
            ],
            [
                'label' => __('Brightness', 'posts-grid'),
                'value' => 'brightness'
            ],
            [
                'label' => __('Contrast', 'posts-grid'),
                'value' => 'contrast'
            ],
            [
                'label' => __('Grayscale', 'posts-grid'),
                'value' => 'grayscale'
            ],
            [
                'label' => __('Hue Rotate', 'posts-grid'),
                'value' => 'hue_rotate'
            ],
            [
                'label' => __('Invert', 'posts-grid'),
                'value' => 'invert'
            ],
            [
                'label' => __('Opacity', 'posts-grid'),
                'value' => 'opacity'
            ],
            [
                'label' => __('Saturate', 'posts-grid'),
                'value' => 'saturate'
            ],
            [
                'label' => __('Sepia', 'posts-grid'),
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
                'label' => __( 'Default', 'posts-grid' ),
                'value' => 'default',
            ),
            array(
                'label' => __( 'Style One', 'posts-grid' ),
                'value' => 'style-one',
            ),
            array(
                'label' => __( 'Style Two', 'posts-grid' ),
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
                'label' => __( 'Normal Pagination', 'posts-grid' ),
                'value' => 'normal-pagination',
            ),
            array(
                'label' => __( 'Ajax Pagination', 'posts-grid' ),
                'value' => 'ajax-pagination',
            ),
            array(
                'label' => __( 'Load More Button', 'posts-grid' ),
                'value' => 'load-more-button',
            ),
            array(
                'label' => __( 'Load More on Scroll', 'posts-grid' ),
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
					'label' => __( 'Default', 'posts-grid' ),
					'value' => 'filter--default',
				),
				array(
					'label' => __( 'Style 01', 'posts-grid' ),
					'value' => 'filter--style-01',
				),
				array(
					'label' => __( 'Style 02', 'posts-grid' ),
					'value' => 'filter--style-02',
				),
				array(
					'label' => __( 'Style 03', 'posts-grid' ),
					'value' => 'filter--style-03',
				),
				array(
					'label' => __( 'Style 04', 'posts-grid' ),
					'value' => 'filter--style-04',
				),
				array(
					'label' => __( 'Style 05', 'posts-grid' ),
					'value' => 'filter--style-05',
				),
				array(
					'label' => __( 'Style 06', 'posts-grid' ),
					'value' => 'filter--style-06',
				),
				array(
					'label' => __( 'Style 07', 'posts-grid' ),
					'value' => 'filter--style-07',
				),
				array(
					'label' => __( 'Style 08', 'posts-grid' ),
					'value' => 'filter--style-08',
				),
				array(
					'label' => __( 'Style 09', 'posts-grid' ),
					'value' => 'filter--style-09',
				),
				array(
					'label' => __( 'Style 10', 'posts-grid' ),
					'value' => 'filter--style-10',
				),
            );    

        $free_slugs = ['filter--default'];

        return apply_pro_guards( $styles, $free_slugs, true );
    }

    public function get_teca_ctrl_pos_options() {
        return [
            [
                'label' => __( 'Bottom', 'posts-grid' ),
                'value' => 'carousel-navs-pos--bottom',
            ],
            [
                'label' => __( 'Center', 'posts-grid' ),
                'value' => 'carousel-navs-pos--center',
            ],
            // [
            //     'label' => __( 'Center Outside', 'posts-grid' ),
            //     'value' => 'carousel-navs-pos--center-outside',
            // ],
            // [
            //     'label' => __( 'Center Inside', 'posts-grid' ),
            //     'value' => 'carousel-navs-pos--center-inside',
            // ],
            [
                'label' => __( 'Top Right', 'posts-grid' ),
                'value' => 'carousel-navs-pos--top-right',
            ],
            [
                'label' => __( 'Top Center', 'posts-grid' ),
                'value' => 'carousel-navs-pos--top-center',
            ],
            [
                'label' => __( 'Top Left', 'posts-grid' ),
                'value' => 'carousel-navs-pos--top-left',
            ],
            [
                'label' => __( 'Verticle Right', 'posts-grid' ),
                'value' => 'carousel-navs-pos--verticle-right',
            ],
            [
                'label' => __( 'Verticle Left', 'posts-grid' ),
                'value' => 'carousel-navs-pos--verticle-left',
            ]
        ];
    }

    public function get_navs_style() {

        $styles = array(

				array(
					'label' => __( 'Default', 'posts-grid' ),
					'value' => 'nav--default',
				),
				array(
					'label' => __( 'Style 01', 'posts-grid' ),
					'value' => 'nav--style-01',
				),
				array(
					'label' => __( 'Style 02', 'posts-grid' ),
					'value' => 'nav--style-02',
				),
				array(
					'label' => __( 'Style 03', 'posts-grid' ),
					'value' => 'nav--style-03',
				),
				array(
					'label' => __( 'Style 04', 'posts-grid' ),
					'value' => 'nav--style-04',
				),
				array(
					'label' => __( 'Style 05', 'posts-grid' ),
					'value' => 'nav--style-05',
				),
				array(
					'label' => __( 'Style 06', 'posts-grid' ),
					'value' => 'nav--style-06',
				),
				array(
					'label' => __( 'Style 07', 'posts-grid' ),
					'value' => 'nav--style-07',
				),
				array(
					'label' => __( 'Style 08', 'posts-grid' ),
					'value' => 'nav--style-08',
				),
				array(
					'label' => __( 'Style 09', 'posts-grid' ),
					'value' => 'nav--style-09',
				),
				array(
					'label' => __( 'Style 10', 'posts-grid' ),
					'value' => 'nav--style-10',
				),
				array(
					'label' => __( 'Style 11', 'posts-grid' ),
					'value' => 'nav--style-11',
				),
			);

        $free_slugs = ['nav--default'];

        return apply_pro_guards( $styles, $free_slugs, true);
    }

    public function get_dots_style() {

        $styles = array(
				array(
					'label' => __( 'Default', 'posts-grid' ),
					'value' => 'dot--default',
				),
				array(
					'label' => __( 'Style 01', 'posts-grid' ),
					'value' => 'dot--style-01',
				),
				array(
					'label' => __( 'Style 02', 'posts-grid' ),
					'value' => 'dot--style-02',
				),
				array(
					'label' => __( 'Style 03', 'posts-grid' ),
					'value' => 'dot--style-03',
				),
				array(
					'label' => __( 'Style 04', 'posts-grid' ),
					'value' => 'dot--style-04',
				),
				array(
					'label' => __( 'Style 05', 'posts-grid' ),
					'value' => 'dot--style-05',
				),
				array(
					'label' => __( 'Style 06', 'posts-grid' ),
					'value' => 'dot--style-06',
				),
				array(
					'label' => __( 'Style 07', 'posts-grid' ),
					'value' => 'dot--style-07',
				),
				array(
					'label' => __( 'Style 08', 'posts-grid' ),
					'value' => 'dot--style-08',
				),
				array(
					'label' => __( 'Style 09', 'posts-grid' ),
					'value' => 'dot--style-09',
				),
				array(
					'label' => __( 'Style 10', 'posts-grid' ),
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
                'label' => __('Grid', 'gswps'),
                'value' => 'grid'
            ],
            [
                'label' => __('Masonry', 'gswps'),
                'value' => 'masonry'
            ],
            [
                'label' => __( 'Calendar', 'the-events-calendar-addon' ),
                'value' => 'calendar',
            ],
            [
                'label' => __('Slider', 'gswps'),
                'value' => 'carousel'
            ],
            [
                'label' => __('Ticker', 'gswps'),
                'value' => 'ticker'
            ],
            [
                'label' => __('Filter', 'gswps'),
                'value' => 'filter'
            ],
            [
                'label' => __( 'Events Section', 'the-events-calendar-addon' ),
                'value' => 'events-section',
            ],
            [
                'label' => __( 'Venue Template', 'the-events-calendar-addon' ),
                'value' => 'venue_template',
            ],
            [
                'label' => __( 'Organizer Template', 'the-events-calendar-addon' ),
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
            1 => __( 'Layout 1', 'the-events-calendar-addon' ),
            2 => __( 'Layout 2', 'the-events-calendar-addon' ),
            3 => __( 'Layout 3', 'the-events-calendar-addon' ),
        );

        $names = array(
            'daily'     => __( 'Daily', 'the-events-calendar-addon' ),
            'weekly'    => __( 'Weekly', 'the-events-calendar-addon' ),
            'monthly'   => __( 'Monthly', 'the-events-calendar-addon' ),
            'quarterly' => __( 'Quarterly', 'the-events-calendar-addon' ),
            'yearly'    => __( 'Yearly', 'the-events-calendar-addon' ),
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
                'label' => __( 'Layout 1', 'the-events-calendar-addon' ),
                'value' => 'layout-1',
            ),
            array(
                'label' => __( 'Layout 2', 'the-events-calendar-addon' ),
                'value' => 'layout-2',
            ),
            array(
                'label' => __( 'Layout 3', 'the-events-calendar-addon' ),
                'value' => 'layout-3',
            ),
        );
    }

    public function get_venue_template_layout_options() {
        return array(
            array(
                'label' => __( 'Layout 1', 'the-events-calendar-addon' ),
                'value' => 'layout-1',
            ),
            array(
                'label' => __( 'Layout 2', 'the-events-calendar-addon' ),
                'value' => 'layout-2',
            ),
            array(
                'label' => __( 'Layout 3', 'the-events-calendar-addon' ),
                'value' => 'layout-3',
            ),
        );
    }

    public function get_events_section_layout_options() {
        return array(
            array(
                'label' => __( 'Event Layout 1', 'the-events-calendar-addon' ),
                'value' => 'event-layout-1',
            ),
            array(
                'label' => __( 'Event Layout 2', 'the-events-calendar-addon' ),
                'value' => 'event-layout-2',
            ),
            array(
                'label' => __( 'Event Layout 3', 'the-events-calendar-addon' ),
                'value' => 'event-layout-3',
            ),
        );
    }

    public function get_shortcode_templates() {
        return apply_filters( 'gs_teca_shortcode_templates', self::get_free_templates() );
    }

    public static function get_all_theme_template_options() {

        return [
            [
                'label' => __('Style 01', 'gswps'),
                'value' => 'gs-teca-style-1'
            ],
            [
                'label' => __('Style 02', 'gswps'),
                'value' => 'gs-teca-style-2'
            ],
            [
                'label' => __('Style 03', 'gswps'),
                'value' => 'gs-teca-style-3'
            ],
            [
                'label' => __('Style 04', 'gswps'),
                'value' => 'gs-teca-style-4'
            ],
            [
                'label' => __('Style 05', 'gswps'),
                'value' => 'gs-teca-style-5'
            ],
            [
                'label' => __( 'Style 06', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-style-6'
            ],
            [
                'label' => __( 'Style 07', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-style-7'
            ],
            [
                'label' => __( 'Style 08', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-style-8'
            ],
            [
                'label' => __( 'Style 09', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-style-9'
            ],
            [
                'label' => __( 'Style 10', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-style-10'
            ],
            [
                'label' => __('List 01', 'gswps'),
                'value' => 'gs-teca-list-style-1'
            ],
            [
                'label' => __('List 02', 'gswps'),
                'value' => 'gs-teca-list-style-2'
            ],
            [
                'label' => __('List 03', 'gswps'),
                'value' => 'gs-teca-list-style-3'
            ],
            [
                'label' => __('List 04', 'gswps'),
                'value' => 'gs-teca-list-style-4'
            ],
            [
                'label' => __('List 05', 'gswps'),
                'value' => 'gs-teca-list-style-5'
            ],
            [
                'label' => __('Table 01', 'gswps'),
                'value' => 'gs-teca-table-style-1'
            ],
            [
                'label' => __('Table 02', 'gswps'),
                'value' => 'gs-teca-table-style-2'
            ],
            [
                'label' => __('Table 03', 'gswps'),
                'value' => 'gs-teca-table-style-3'
            ],
            [
                'label' => __('Table 04', 'gswps'),
                'value' => 'gs-teca-table-style-4'
            ],
            [
                'label' => __('Table 05', 'gswps'),
                'value' => 'gs-teca-table-style-5'
            ],
            [
                'label' => __( 'Timeline 1', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-timeline-1',
            ],
            [
                'label' => __( 'Timeline 2', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-timeline-2',
            ],
            [
                'label' => __( 'Timeline 3', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-timeline-3',
            ],
            [
                'label' => __( 'Accordion 1', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-accordion-1',
            ],
            [
                'label' => __( 'Accordion 2', 'the-events-calendar-addon' ),
                'value' => 'gs-teca-accordion-2',
            ],
            [
                'label' => __( 'Accordion 3', 'the-events-calendar-addon' ),
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
                'label' => __( 'Categories', 'posts-grid' ),
                'value' => 'gs-teca-category',
            ],
            [
                'label' => __( 'Tags', 'posts-grid' ),
                'value' => 'gs-teca-tag',
            ]
        ];
    }

    public function get_filter_type() {
        return [
            [
                'label' => __( 'Normal Filter', 'posts-grid' ),
                'value' => 'normal-filter'
            ],
            [
                'label' => __( 'Ajax Filter', 'posts-grid' ),
                'value' => 'ajax-filter'
            ]
        ];

    }

    public function get_shortcode_default_settings() {
        $shortcode_id = absint( $_GET['id'] ?? 0 );
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
            'popup_related_events_title'       => __( 'Related Events', 'the-events-calendar-addon' ),
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
                'label' => __( 'Single Page', 'posts-grid' ),
                'value' => 'single_page'
            ],
            [
                'label' => __( 'Popup', 'posts-grid' ),
                'value' => 'popup'
            ]
        ];

        return $link_types;

    }

    public function get_shortcode_default_prefs() {
        $prefs = [
            
            'gs_teca_nxt_prev'                         => 'off',
            'gs_teca_enable_multilingual'              => 'on',
            'gs_teca_custom_css'                       => '',
            'anchor_tag_rel'                           => 'noopener',
            
        ];

        $translations = $this->get_shortcode_default_translations();

        $prefs = array_merge( $prefs, $translations );

        return $prefs;
    }

    public function get_shortcode_default_translations() {
        $translations = [
            'gs_teca_more'                             => __('More', 'posts-grid'),
            'gs_teca_prev_txt'                         => __('Prev', 'posts-grid'),
            'gs_teca_next_txt'                         => __('Next', 'posts-grid'),
            'gs_teca_view_details_text'                => __( 'View Details', 'the-events-calendar-addon' ),
            'gs_teca_related_events_title'             => __( 'Related Events', 'the-events-calendar-addon' ),
            'gs_teca_event_website_text'               => __( 'Event Website', 'the-events-calendar-addon' ),
            'gs_teca_add_to_calendar_text'             => __( 'Add to calendar', 'the-events-calendar-addon' ),
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
        
        // Set the global query
        query_posts($merged_query);
        
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
                    'label' => __( 'No Change', 'gswps' ),
                    'value' => 'no_change'
                ],
                [
                    'label' => __( 'Change completely (use all options of the shortcode)', 'gswps' ),
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
                'label' => __( 'Default', 'posts-grid' ),
                'value' => 'default',
            ),
            array(
                'label' => __( 'Style One', 'posts-grid' ),
                'value' => 'style-one',
            ),
            array(
                'label' => __( 'Style Two', 'posts-grid' ),
                'value' => 'style-two',
            ),
            array(
                'label' => __( 'Style Three', 'posts-grid' ),
                'value' => 'style-three',
            ),
            array(
                'label' => __( 'Style Four', 'posts-grid' ),
                'value' => 'style-four',
            ),
            array(
                'label' => __( 'Style Five', 'posts-grid' ),
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
                    'label' => __( 'nofollow', 'posts-grid' ),
                    'value' => 'nofollow'
                ],
                [
                    'label' => __( 'noopener', 'posts-grid' ),
                    'value' => 'noopener'
                ],
                [
                    'label' => __( 'noreferrer', 'posts-grid' ),
                    'value' => 'noreferrer'
                ],
                [
                    'label' => __( 'nofollow noopener', 'posts-grid' ),
                    'value' => 'nofollow noopener'
                ],
                [
                    'label' => __( 'nofollow noreferrer', 'posts-grid' ),
                    'value' => 'nofollow noreferrer'
                ],
                [
                    'label' => __( 'noopener noreferrer', 'posts-grid' ),
                    'value' => 'noopener noreferrer'
                ],
                [
                    'label' => __( 'nofollow noopener noreferrer', 'posts-grid' ),
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
        do_action( 'gs_teca_preference_update' );
        do_action( 'gsteca_preference_update' );
    
        if ( $is_ajax ) wp_send_json_success( __('Preference saved', 'posts-grid') );
    }

    public function _save_shortcode_layout( $layout, $is_ajax ) {

        $layout = $this->validate_layout( $layout );
        update_option( $this->layout_option_name, $layout, 'yes' );
        
        // Clean permalink flush
        delete_option( 'GS_Teca_plugin_permalinks_flushed' );
        do_action( 'gs_teca_layout_update' );
        do_action( 'gsteca_layout_update' );
    
        if ( $is_ajax ) wp_send_json_success( __('Layout saved', 'posts-grid') );
    }

    public function save_shortcode_layout( $nonce = null ) {

        check_ajax_referer( '_gsteca_save_shortcode_layout_gs_', '_wpnonce' );

        if ( empty($_POST['layout']) ) {
            wp_send_json_error( __('No layout provided', 'posts-grid'), 400 );
        }

        $this->_save_shortcode_layout( $_POST['layout'], true );
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
            'related_events_title'    => __( 'Related Events', 'the-events-calendar-addon' ),
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

        check_ajax_referer( '_gsteca_save_shortcode_pref_gs_', '_wpnonce' );

        if ( empty($_POST['prefs']) ) {
            wp_send_json_error( __('No preference provided', 'posts-grid'), 400 );
        }

        $this->_save_shortcode_pref( $_POST['prefs'], true );
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

        $wpdb = $this->gsteca_get_wpdb();

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

            $wpdb->insert("{$wpdb->prefix}gs_teca", $data, $this->get_gsteca_shortcode_db_columns());
        }

        wp_cache_delete('gs_teca_shortcodes');
    }

    public function delete_dummy_shortcodes() {

        $wpdb = $this->gsteca_get_wpdb();

        $needle = 'gsteca-demo_data';

        $wpdb->query("DELETE FROM {$wpdb->prefix}gs_teca WHERE shortcode_settings like '%$needle%'");

        wp_cache_delete('gs_teca_shortcodes');
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

        $shortcode_id = absint( $_POST['shortcode_id'] ?? 0 );

        if ( ! $shortcode_id ) {
        wp_send_json_error('Missing shortcode id');
        }

        if ( empty( $_POST['order'] ) || ! is_array( $_POST['order'] ) ) {
            wp_send_json_error( 'Invalid order data' );
        }

        $validated_order = $this->validate_popup_visibility_order(
            array_map( 'sanitize_text_field', wp_unslash( $_POST['order'] ) )
        );

        update_option(
            'gs_teca_popup_visibility_order_' . $shortcode_id,
            $validated_order
        );

        wp_send_json_success( 'Popup order saved' );
    }

    
}




