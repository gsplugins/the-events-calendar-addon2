<?php
namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'GS_TECA_Dummy_Data' ) ) {

    final class GS_TECA_Dummy_Data {

        private static $_instance = null;
        const DEMO_DATETIME_REPAIR_VERSION = 2;
        
        public static function get_instance() {

            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
            
        }

        public function __construct() {

            if ( ! is_admin() ) return;

            add_action( 'admin_init', array($this, 'maybe_auto_import_all_data') );
            add_action( 'admin_init', array($this, 'maybe_repair_demo_event_datetimes') );
            
            add_action( 'gsteca_shortcode_submenu', array($this,'register_submenu'), -1 );

            add_action( 'wp_ajax_gsteca_import_teca_data', array($this, 'import_teca_data') );

            add_action( 'wp_ajax_gsteca_remove_teca_data', array($this, 'remove_teca_data') );

            add_action( 'wp_ajax_gsteca_import_shortcode_data', array($this, 'import_shortcode_data') );

            add_action( 'wp_ajax_gsteca_remove_shortcode_data', array($this, 'remove_shortcode_data') );

            add_action( 'wp_ajax_gsteca_import_all_data', array($this, 'import_all_data') );

            add_action( 'wp_ajax_gsteca_remove_all_data', array($this, 'remove_all_data') );

            // Remove dummy indicator
            add_action( 'edit_post_gs_teca', array($this, 'remove_dummy_indicator'), 10 );

            // Import Process
            add_action( 'gsteca_dummy_attachments_process_start', function() {

                // Force delete option if have any
                delete_option( 'gsteca_dummy_data_created' );
                delete_option( 'gsteca_demo_datetime_repaired' );
                delete_option( 'gsteca_demo_datetime_repair_version' );

                // Force update the process
                set_transient( 'gsteca_dummy_data_creating', 1, 3 * MINUTE_IN_SECONDS );

            });
            
            add_action( 'gsteca_dummy_attachments_process_finished', function() {

                $this->create_dummy_terms();

            });
            
            add_action( 'gsteca_dummy_terms_process_finished', function() {

                $this->create_dummy_events();

            });
            
            add_action( 'gsteca_dummy_teca_process_finished', function() {

                // clean the record that we have started a process
                delete_transient( 'gsteca_dummy_data_creating' );

                // Add a track so we never duplicate the process
                update_option( 'gsteca_dummy_data_created', 1 );

            });
            
            // Shortcodes
            add_action( 'gsetca_dummy_shortcodes_process_start', function() {

                // Force delete option if have any
                delete_option( 'gsteca_dummy_shortcode_data_created' );

                // Force update the process
                set_transient( 'gsteca_dummy_shortcode_data_creating', 1, 3 * MINUTE_IN_SECONDS );

            });

            add_action( 'gsteca_dummy_shortcodes_process_finished', function() {

                // clean the record that we have started a process
                delete_transient( 'gsteca_dummy_shortcode_data_creating' );

                // Add a track so we never duplicate the process
                update_option( 'gsteca_dummy_shortcode_data_created', 1 );

            });
            
        }

        public function maybe_auto_import_all_data() {

            // Already auto import done (one time)
            if ( get_option('gs_teca_autoimport_done', false) ) {
                return;
            }

            // If demo already created আগে → skip
            if (
                get_option('gsteca_dummy_data_created') ||
                get_option('gsteca_dummy_shortcode_data_created')
            ) {
                update_option( 'gs_teca_autoimport_done', true );
                return;
            }

            // Check existing real data (not demo)
            $events = get_posts([
                'numberposts' => 1,
                'post_type'   => 'tribe_events',
                'post_status' => 'any',
                'fields'      => 'ids'
            ]);

            $shortcodes = plugin()->builder->get_shortcodes();

            // Only import if totally fresh
            if ( empty($events) && empty($shortcodes) ) {

                $this->_import_teca_data(false);
                $this->_import_shortcode_data(false);
            }

            // Mark as checked (important)
            update_option( 'gs_teca_autoimport_done', true );
        }

        public function register_submenu() {
            add_submenu_page( 'gs-the-events-calendar-addon', 'Demo Data', 'Demo Data', 'manage_options', 'gs-the-events-calendar-addon#/demo-data', array( $this, 'view' ) );
        }

        public function get_taxonomy_list() {

            return ['tribe_events_cat', 'post_tag'];

        }

        public function remove_dummy_indicator( $post_id ) {

            if ( empty( get_post_meta($post_id, 'gsteca-demo_data', true) ) ) return;
            
            $taxonomies = $this->get_taxonomy_list();

            // Remove dummy indicator from texonomies
            $dummy_terms = wp_get_post_terms( $post_id, $taxonomies, [
                'fields' => 'ids',
                'meta_key' => 'gsteca-demo_data',
                'meta_value' => 1,
            ]);

            if ( !empty($dummy_terms) ) {
                foreach( $dummy_terms as $term_id ) {
                    delete_term_meta( $term_id, 'gsteca-demo_data', 1 );
                }
                delete_transient( 'gsteca_dummy_terms' );
            }

            // Remove dummy indicator from attachments
            $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
            if ( !empty($thumbnail_id) ) delete_post_meta( $thumbnail_id, 'gsteca-demo_data', 1 );
            delete_transient( 'gsteca_dummy_attachments' );
            
            // Remove dummy indicator from post
            delete_post_meta( $post_id, 'gsteca-demo_data', 1 );
            delete_transient( 'gsteca_dummy_portfolios' );

        }

        public function import_all_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteca_import_gsteca_demo_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'the-events-calendar-addon'), 401 );

            $response = [
                'event' => $this->_import_teca_data( false ),
                'shortcode' => $this->_import_shortcode_data( false )
            ];

            if ( wp_doing_ajax() ) wp_send_json_success( $response, 200 );

            return $response;

        }

        public function remove_all_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteca_import_gsteca_demo_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'the-events-calendar-addon'), 401 );

            $response = [
                'event' => $this->_remove_teca_data( false ),
                'shortcode' => $this->_remove_shortcode_data( false )
            ];

            if ( wp_doing_ajax() ) wp_send_json_success( $response, 200 );

            return $response;

        }

        public function import_teca_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteca_import_gsteca_demo_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'the-events-calendar-addon'), 401 );

            // Start importing
            $this->_import_teca_data();

        }

        public function remove_teca_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteca_import_gsteca_demo_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'the-events-calendar-addon'), 401 );

            // Remove portfolio data
            $this->_remove_teca_data();

        }

        public function import_shortcode_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteca_import_gsteca_demo_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'the-events-calendar-addon'), 401 );

            // Start importing
            $this->_import_shortcode_data();

        }

        public function remove_shortcode_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteca_import_gsteca_demo_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'the-events-calendar-addon'), 401 );

            // Remove portfolio data
            $this->_remove_shortcode_data();

        }

        public function _import_teca_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            // Data already imported
            if ( get_option('gsteca_dummy_data_created') !== false || get_transient('gsteca_dummy_data_creating') !== false ) {

                $message_202 = __( 'Dummy portfolios already imported', 'the-events-calendar-addon' );

                if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
                
                return [
                    'status' => 202,
                    'message' => $message_202
                ];

            }
            
            // Importing demo data
            $this->create_dummy_attachments();

            $message = __( 'Dummy events imported', 'the-events-calendar-addon' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _remove_teca_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            $this->delete_dummy_attachments();
            $this->delete_dummy_terms();
            $this->delete_dummy_events();

            delete_option( 'gsteca_dummy_data_created' );
            delete_option( 'gsteca_demo_datetime_repaired' );
            delete_option( 'gsteca_demo_datetime_repair_version' );
            delete_transient( 'gsteca_dummy_data_creating' );

            $message = __( 'Dummy events deleted', 'the-events-calendar-addon' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _import_shortcode_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            // Data already imported
            if ( get_option('gsteca_dummy_shortcode_data_created') !== false || get_transient('gsteca_dummy_shortcode_data_creating') !== false ) {

                $message_202 = __( 'Dummy Shortcodes already imported', 'the-events-calendar-addon' );

                if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
                
                return [
                    'status' => 202,
                    'message' => $message_202
                ];

            }
            
            // Importing demo shortcodes
            $this->create_dummy_shortcodes();

            $message = __( 'Dummy Shortcodes imported', 'the-events-calendar-addon' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _remove_shortcode_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            $this->delete_dummy_shortcodes();

            delete_option( 'gsteca_dummy_shortcode_data_created' );
            delete_transient( 'gsteca_dummy_shortcode_data_creating' );

            $message = __( 'Dummy Shortcodes deleted', 'the-events-calendar-addon' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function get_taxonomy_ids_by_slugs( $taxonomy_group, $taxonomy_slugs = [] ) {

            $_terms = $this->get_dummy_terms();

            if ( empty($_terms) ) return [];
            
            $_terms = wp_filter_object_list( $_terms, [ 'taxonomy' => $taxonomy_group ] );
            $_terms = array_values( $_terms );      // reset the keys
            
            if ( empty($_terms) ) return [];
            
            $term_ids = [];
            
            foreach ( $taxonomy_slugs as $slug ) {
                $key = array_search( $slug, array_column($_terms, 'slug') );
                if ( $key !== false ) $term_ids[] = $_terms[$key]['term_id'];
            }

            return $term_ids;

        }

        public function get_attachment_id_by_filename( $filename ) {

            $attachments = $this->get_dummy_attachments();
            
            if ( empty($attachments) ) return '';
            
            $attachments = wp_filter_object_list( $attachments, [ 'post_name' => $filename ] );
            if ( empty($attachments) ) return '';
            
            $attachments = array_values( $attachments );
            
            return $attachments[0]->ID;

        }

        public function get_tax_inputs( $tax_inputs = [] ) {

            if ( empty($tax_inputs) ) return $tax_inputs;

            foreach( $tax_inputs as $tax_input => $tax_params ) {

                $tax_inputs[$tax_input] = $this->get_taxonomy_ids_by_slugs( $tax_input, $tax_params );

            }

            return $tax_inputs;

        }

        public function get_meta_inputs( $meta_inputs = [] ) {

            $meta_inputs['_thumbnail_id'] = $this->get_attachment_id_by_filename( $meta_inputs['_thumbnail_id'] );

            return $meta_inputs;

        }

        public function maybe_repair_demo_event_datetimes() {

            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            if ( (int) get_option( 'gsteca_demo_datetime_repair_version', 0 ) >= self::DEMO_DATETIME_REPAIR_VERSION ) {
                return;
            }

            if ( ! get_option( 'gsteca_dummy_data_created' ) ) {
                return;
            }

            foreach ( $this->get_dummy_events() as $event ) {
                teca_sync_event_via_tec( $event->ID, $event->post_date, true );
            }

            teca_flush_event_caches_after_demo_import();
            update_option( 'gsteca_demo_datetime_repair_version', self::DEMO_DATETIME_REPAIR_VERSION, false );
            delete_option( 'gsteca_demo_datetime_repaired' );
        }

        public function repair_demo_event_datetimes() {

            foreach ( $this->get_dummy_events() as $event ) {
                teca_sync_event_via_tec( $event->ID, $event->post_date, true );
            }
        }

        // portfolios
        public function create_dummy_events() {

            do_action( 'gsteca_dummy_portfolios_process_start' );

            $post_status = 'publish';
            $post_type = 'tribe_events';

            $events = [];

            $events[] = array(
                'post_title'    => 'A Guide to Aesthetic Objects',
                'post_content'  => 'In a world where trends come and go, some aesthetic objects stand the test of time. These classic pieces add a touch of timeless elegance to any space, creating a sophisticated and enduring charm. In this article, we explore the must-have aesthetic objects that never go out of style.

                Vintage mirrors are a staple in classic home decor. They bring an air of refinement and can make any room feel larger and brighter. Their intricate designs and antique finishes add character and elegance to your space.',
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-15 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    'tribe_events_cat' => ['branding', 'beauty'],
                    'post_tag' => ['abstract'],
                ]),
                'meta_input' => [
                    '_thumbnail_id' => 'gs-teca-1',
                ]
            );

            $events[] = array(
                'post_title'    => 'Right Time to Invest',
                'post_content'  => 'In a world where trends come and go, some aesthetic objects stand the test of time. These classic pieces add a touch of timeless elegance to any space, creating a sophisticated and enduring charm. In this article, we explore the must-have aesthetic objects that never go out of style.',
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-15 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    'tribe_events_cat' => ['strategy'],
                    'post_tag' => ['cutest'],
                ]),
                'meta_input' => [
                    '_thumbnail_id' => 'gs-teca-2',
                ]
            );

            $events[] = array(
                'post_title'    => 'The Future is Now',
                'post_content'  => 'In an era where technological advancements are accelerating at an unprecedented pace, the future truly is now. Innovations that once belonged to the realm of science fiction are now becoming an integral part of our daily lives.',
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-15 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    'tribe_events_cat' => ['business'],
                    'post_tag' => ['cutest', 'fashion'],
                ]),
                'meta_input' => [
                    '_thumbnail_id' => 'gs-teca-3',
                    
                ]
            );

            $events[] = array(
                'post_title'    => 'Virtual Reality: A New Frontier of Possibilities',
                'post_content'  => 'In an era where technological advancements are accelerating at an unprecedented pace, the future truly is now. Innovations that once belonged to the realm of science fiction are now becoming an integral part of our daily lives.

                From artificial intelligence and machine learning to the Internet of Things (IoT) and blockchain technology, we are witnessing a transformation that is reshaping industries and societies.

                In this comprehensive article, we delve into the technological revolution that is unfolding before our eyes, exploring its implications and potential to redefine the way we live, work, and interact.',
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-15 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    'tribe_events_cat' => ['planning'],
                    'post_tag' => ['cutest'],
                ]),
                'meta_input' => [
                    '_thumbnail_id' => 'gs-teca-4',
                ],
            );

            $events[] = array(
                'post_title'    => 'Magnetic Rubiks Cube',
                'post_content'  => 'In an era where technological advancements are accelerating at an unprecedented pace, the future truly is now. Innovations that once belonged to the realm of science fiction are now becoming an integral part of our daily lives.

                From artificial intelligence and machine learning to the Internet of Things (IoT) and blockchain technology, we are witnessing a transformation that is reshaping industries and societies.

                In this comprehensive article, we delve into the technological revolution that is unfolding before our eyes, exploring its implications and potential to redefine the way we live, work, and interact.',
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-15 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    'tribe_events_cat' => ['planning', 'business'],
                    'post_tag' => ['cutest', 'fashion'],
                ]),
                'meta_input' => [
                    '_thumbnail_id' => 'gs-teca-5',
                ]
            );

            $events[] = array(
                'post_title'    => 'the Latest Fashion Trends',
                'post_content'  => 'In an era where technological advancements are accelerating at an unprecedented pace, the future truly is now. Innovations that once belonged to the realm of science fiction are now becoming an integral part of our daily lives.

                From artificial intelligence and machine learning to the Internet of Things (IoT) and blockchain technology, we are witnessing a transformation that is reshaping industries and societies.

                In this comprehensive article, we delve into the technological revolution that is unfolding before our eyes, exploring its implications and potential to redefine the way we live, work, and interact.',
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-15 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    'tribe_events_cat' => ['beauty'],
                    'post_tag' => ['fashion'],
                ]),
                'meta_input' => [
                    '_thumbnail_id' => 'gs-teca-6',
                ]
            );

            foreach ( $events as $index => $event ) {
                if ( ! empty( $event['meta_input']['_thumbnail_id'] ) && ! is_numeric( $event['meta_input']['_thumbnail_id'] ) ) {
                    $event['meta_input']['_thumbnail_id'] = $this->get_attachment_id_by_filename( $event['meta_input']['_thumbnail_id'] );
                }

                teca_insert_demo_event( $event, $index );
            }

            $this->repair_demo_event_datetimes();
            teca_flush_event_caches_after_demo_import();
            update_option( 'gsteca_demo_datetime_repair_version', self::DEMO_DATETIME_REPAIR_VERSION, false );
            delete_option( 'gsteca_demo_datetime_repaired' );

            do_action( 'gsteca_dummy_portfolios_process_finished' );

        }

        public function delete_dummy_events() {
            
            $eevnts = $this->get_dummy_events();

            if ( empty($eevnts) ) return;

            foreach ($eevnts as $eevnt) {
                wp_delete_post( $eevnt->ID, true );
            }

            delete_transient( 'gsteca_dummy_events' );

        }

        public function get_dummy_events() {

            $events = get_transient( 'gsteca_dummy_events' );

            if ( false !== $events ) return $events;

            $events = get_posts( array(
                'numberposts' => -1,
                'post_type'   => 'tribe_events',
                'meta_key' => 'gsteca-demo_data',
                'meta_value' => 1,
            ));
            
            if ( is_wp_error($events) || empty($events) ) {
                delete_transient( 'gsteca_dummy_events' );
                return [];
            }
            
            set_transient( 'gsteca_dummy_events', $events, 3 * MINUTE_IN_SECONDS );

            return $events;

        }

        public function http_request_args( $args ) {
            
            $args['sslverify'] = false;

            return $args;

        }

        // Attachments
        public function create_dummy_attachments() {

            do_action( 'gsteca_dummy_attachments_process_start' );

            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            $attachment_files = [
                'gs-teca-1.png',
                'gs-teca-2.png',
                'gs-teca-3.png',
                'gs-teca-4.png',
                'gs-teca-5.png',
                'gs-teca-6.png',
            ];

            add_filter( 'http_request_args', [ $this, 'http_request_args' ] );

            wp_raise_memory_limit( 'image' );

            foreach ( $attachment_files as $file ) {

                $file = GS_TECA_PLUGIN_URI . 'assets/img/dummy-data/' . $file;

                $filename = basename($file);

                $get = wp_remote_get( $file );
                $type = wp_remote_retrieve_header( $get, 'content-type' );
                $mirror = wp_upload_bits( $filename, null, wp_remote_retrieve_body( $get ) );
                
                // Prepare an array of post data for the attachment.
                $attachment = array(
                    'guid'           => $mirror['url'],
                    'post_mime_type' => $type,
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                
                // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $mirror['file'] );
                
                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                add_post_meta( $attach_id, 'gsteca-demo_data', 1 );

            }

            remove_filter( 'http_request_args', [ $this, 'http_request_args' ] );

            do_action( 'gsteca_dummy_attachments_process_finished' );
        }

        public function delete_dummy_attachments() {
            
            $attachments = $this->get_dummy_attachments();

            if ( empty($attachments) ) return;

            foreach ($attachments as $attachment) {
                wp_delete_attachment( $attachment->ID, true );
            }

            delete_transient( 'gsteca_dummy_attachments' );

        }

        public function get_dummy_attachments() {

            $attachments = get_transient( 'gsteca_dummy_attachments' );

            if ( false !== $attachments ) return $attachments;

            $attachments = get_posts( array(
                'numberposts' => -1,
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'meta_key'    => 'gsteca-demo_data',
                'meta_value'  => 1,
            ));
            
            if ( is_wp_error($attachments) || empty($attachments) ) {
                delete_transient( 'gsteca_dummy_attachments' );
                return [];
            }
            
            set_transient( 'gsteca_dummy_attachments', $attachments, 3 * MINUTE_IN_SECONDS );

            return $attachments;

        }
        
        // Terms
        public function create_dummy_terms() {

            do_action( 'gsteca_dummy_terms_process_start' );
            
            $terms = [
                // 3 Groups
                [
                    'name'  => 'Branding',
                    'slug'  => 'branding',
                    'group' => 'tribe_events_cat'
                ],
                [
                    'name'  => 'Strategy',
                    'slug'  => 'strategy',
                    'group' => 'tribe_events_cat'
                ],
                [
                    'name'  => 'Business',
                    'slug'  => 'business',
                    'group' => 'tribe_events_cat'
                ],
                [
                    'name'  => 'Planning',
                    'slug'  => 'planning',
                    'group' => 'tribe_events_cat'
                ],
                [
                    'name'  => 'Beauty',
                    'slug'  => 'beauty',
                    'group' => 'tribe_events_cat'
                ],

                // 3 Tags
                [
                    'name'  => 'Abstract',
                    'slug'  => 'abstract',
                    'group' => 'post_tag'
                ],
                [
                    'name'  => 'Cutest',
                    'slug'  => 'cutest',
                    'group' => 'post_tag'
                ],
                [
                    'name'  => 'Fashion',
                    'slug'  => 'fashion',
                    'group' => 'post_tag'
                ],
            ];

            foreach( $terms as $term ) {

                $response = wp_insert_term( $term['name'], $term['group'], array('slug' => $term['slug']) );
    
                if ( ! is_wp_error($response) ) {
                    add_term_meta( $response['term_id'], 'gsteca-demo_data', 1 );
                }

            }

            do_action( 'gsteca_dummy_terms_process_finished' );

        }
        
        public function delete_dummy_terms() {
            
            $terms = $this->get_dummy_terms();

            if ( empty($terms) ) return;
    
            foreach ( $terms as $term ) {
                wp_delete_term( $term['term_id'], $term['taxonomy'] );
            }

            delete_transient( 'gsteca_dummy_terms' );

        }

        public function get_dummy_terms() {

            $terms = get_transient( 'gsteca_dummy_terms' );

            if ( false !== $terms ) return $terms;

            $taxonomies = $this->get_taxonomy_list();

            $terms = get_terms( array(
                'taxonomy'   => $taxonomies,
                'hide_empty' => false,
                'meta_key'   => 'gsteca-demo_data',
                'meta_value' => 1,
            ));

            $terms = json_decode( json_encode( $terms ), true ); // Object to Array
            
            if ( is_wp_error($terms) || empty($terms) ) {
                delete_transient( 'gsteca_dummy_terms' );
                return [];
            }

            set_transient( 'gsteca_dummy_terms', $terms, 3 * MINUTE_IN_SECONDS );

            return $terms;

        }

        // Shortcode
        public function create_dummy_shortcodes() {

            do_action( 'gsteca_dummy_shortcodes_process_start' );

            plugin()->builder->create_dummy_shortcodes();

            do_action( 'gsteca_dummy_shortcodes_process_finished' );

        }

        public function delete_dummy_shortcodes() {
            
            plugin()->builder->delete_dummy_shortcodes();

        }

    }

}

GS_TECA_Dummy_Data::get_instance();