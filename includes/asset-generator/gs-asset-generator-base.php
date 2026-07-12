<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Shared asset-generator namespace is intentional and kept for backward compatibility.
namespace GSPLUGINS;
/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists('GSPLUGINS\GS_Asset_Generator_Base') ) {

    abstract class GS_Asset_Generator_Base {
        
        private $main_post_id;
    
        private $processed = [];
    
        private $assets = [];
    
        private $generate_hooked = false;
    
        public function __construct() {
            add_action( 'GS_Plugins/Load_Assets/' . $this->get_assets_key(), [ $this, 'enqueue_plugin_assets' ], 10, 2 );
            add_action( 'GS_Plugins/Generate_Assets/' . $this->get_assets_key(), [ $this, 'maybe_generate_assets_data' ], 10, 2 );
            add_action( 'GS_Plugins/Force_Enqueue_Assets/' . $this->get_assets_key(), [ $this, 'maybe_force_enqueue_assets' ], 10 );
            add_action( 'wp_footer', [ $this, 'maybe_save_assets_data' ] );
            add_filter( 'post_updated', [ $this, 'post_updated__purge' ] );
            add_filter( 'widget_update_callback', [ $this, 'widget_updated__purge' ] );
            add_action( 'update_option_sidebars_widgets', [ $this, 'assets_purge_all' ] );
            add_action( 'gsp_shortcode_updated', [ $this, 'assets_purge_all' ] );
            add_action( 'gsp_preference_update', [ $this, 'assets_purge_all' ] );
        }
    
        public function get_assets_model() {
            return [
                'styles' => [],
                'fonts'  => [],
                'scripts' => []
            ];
        }
    
        final public function get_save_key() {
            return 'gsp--' . $this->get_assets_key() . '--assets';
        }
    
        final public function get_save_option_key( $id ) {
            return $this->get_save_key() . '--' . $id;
        }
    
        final public function get_backward_ids() {
            return ['notfound', 'front', 'page', 'home', 'category', 'tag', 'tax', 'archive', 'search'];
        }
    
        final public function get_assets_data( $main_post_id ) {

            if ( in_array( $main_post_id, $this->get_backward_ids() ) ) {
                return get_option( $this->get_save_option_key( $main_post_id ) );
            } else {
                $data = get_post_meta( $main_post_id, $this->get_save_key(), true );
                if ( !empty($data) ) return maybe_unserialize($data);
            }

            return '';
        }

        final public function get_current_page_id() {
    
            $id = get_queried_object_id();

            if ( $id !== 0 ) return $id;
                
            global $wp_query;
    
            if ( $wp_query->is_page ) {
                $id = is_front_page() ? 'front' : 'page';
            } elseif ( $wp_query->is_home ) {
                $id = 'home';
            } elseif ( $wp_query->is_category ) {
                $id = 'category';
            } elseif ( $wp_query->is_tag ) {
                $id = 'tag';
            } elseif ( $wp_query->is_tax ) {
                $id = 'tax';
            } elseif ( $wp_query->is_archive ) {
                $id = 'archive';
            } elseif ( $wp_query->is_search ) {
                $id = 'search';
            } elseif ( $wp_query->is_404 ) {
                $id = 'notfound';
            }
        
            return $id;        
        }
    
        final public function generate( $main_post_id, Array $settings ) {
            if ( !empty($settings) ) {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Shared GS Plugins asset hook name is kept for backward compatibility.
                do_action( 'GS_Plugins/Generate_Assets/' . $this->get_assets_key(),  $main_post_id, $settings );
            }
        }
    
        public function add_item_in_asset_list( $type, $item, $item_data = [] ) {
    
            if ( empty($this->assets) ) $this->assets = $this->get_assets_model();

            if ( ! isset( $this->assets[ $type ] ) || ! is_array( $this->assets[ $type ] ) ) {
                $this->assets[ $type ] = [];
            }
    
            if ( ! array_key_exists($item, $this->assets[$type]) ) {
    
                $this->assets[$type][$item] = $item_data;
    
            } else {
    
                if ( $item == 'google-fonts' ) {

                    $item_data = is_array($item_data) ? $item_data : [$item_data];
                    
                    foreach ( $item_data as $font ) {
                        if ( ! in_array( $font, $this->assets[$type][$item] ) ) {
                            $this->assets[$type][$item][] = $font;
                        }
                    }
    
                } else if ( $item == 'inline' ) {
    
                    $this->assets[$type][$item] = $this->assets[$type][$item] . $item_data;
    
                } else {
                    foreach ( $item_data as $dep ) {
                        if ( ! in_array( $dep, $this->assets[$type][$item] ) ) {
                            $this->assets[$type][$item][] = $dep;
                        }
                    }
                }
            }
        }
    
        final public function maybe_generate_assets_data( $main_post_id, Array $settings ) {
    
            $this->generate_hooked = true;
    
            $this->main_post_id = $main_post_id;
    
            if ( empty($settings) ) return; // @todo
    
            $process_id = $main_post_id .'_'. $settings['id'];
    
            if ( in_array($process_id, $this->processed) ) return; // reduce process for duplicate shortcode in same page
    
            $this->processed[] = $process_id;
    
            $this->generate_assets_data( $settings );
        }
    
        public function maybe_save_assets_data() {
    
            // Do not go below where shortcode is not used
            if ( ! $this->generate_hooked ) return;
    
            if ( empty($this->main_post_id) ) return;
    
            // If already has data return
            if ( !empty( $this->get_assets_data( $this->main_post_id ) ) ) return;
            
            if ( in_array( $this->main_post_id, $this->get_backward_ids() ) ) {
                update_option( $this->get_save_option_key( $this->main_post_id ), $this->assets );
            } else {
                update_post_meta( $this->main_post_id, $this->get_save_key(), maybe_serialize( $this->assets ) );
            }
        }
    
        final public function purge_assets_data_from_post_meta( $post_ID = null ) {
            if ( ! empty( $post_ID ) ) {
                delete_post_meta( absint( $post_ID ), $this->get_assets_key() );
                return;
            }

            global $wpdb;

            $asset_key = $wpdb->esc_like( $this->get_assets_key() );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generated asset cleanup lookup; result should not be cached because cleanup must read current DB state.
            $ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
                    '%' . $asset_key . '%'
                )
            );

            if ( empty( $ids ) ) {
                return;
            }

            $ids = array_filter( array_map( 'absint', $ids ) );

            if ( empty( $ids ) ) {
                return;
            }

            $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generated asset cleanup delete; placeholders are generated internally and IDs are prepared as integers.
            $wpdb->query(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Placeholders are generated internally and IDs are prepared as integers.
                    "DELETE FROM {$wpdb->postmeta} WHERE meta_id IN ({$placeholders})",
                    ...$ids
                )
            );
        }
    
        final public function purge_assets_data_from_options() {
            global $wpdb;
        
            $asset_key = $wpdb->esc_like( $this->get_assets_key() );
        
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generated asset cleanup lookup; result should not be cached because cleanup must read current DB state.
            $ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_id FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '%' . $asset_key . '%'
                )
            );
        
            if ( empty( $ids ) ) {
                return;
            }
        
            $ids = array_filter( array_map( 'absint', $ids ) );
        
            if ( empty( $ids ) ) {
                return;
            }
        
            $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generated asset cleanup delete; placeholders are generated internally and IDs are prepared as integers.
            $wpdb->query(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Placeholders are generated internally and IDs are prepared as integers.
                    "DELETE FROM {$wpdb->options} WHERE option_id IN ({$placeholders})",
                    ...$ids
                )
            );
        }

        public function widget_updated__purge( $instance ) {
            $this->assets_purge_all();
            return $instance;
        }

        public function post_updated__purge( $post_ID ) {
            $this->purge_assets_data_from_post_meta( $post_ID );
        }

        public function assets_purge_all() {
            $this->purge_assets_data_from_post_meta();
            $this->purge_assets_data_from_options();
        }

        final public function enqueue( $main_post_id ) {

            $is_builder_preview = $this->is_builder_preview();

            if ( $is_builder_preview ) {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Shared GS Plugins asset hook name is kept for backward compatibility.
                do_action( 'GS_Plugins/Load_Builder_Preview_Assets/' . $this->get_assets_key() );
                return;
            }
    
            $this->main_post_id = $main_post_id;
    
            $assets = $this->get_assets_data( $main_post_id );
    
            if ( !empty($assets) ) {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Shared GS Plugins asset hook name is kept for backward compatibility.
                do_action( 'GS_Plugins/Load_Assets/' . $this->get_assets_key(), $main_post_id, $assets );
            }
    
        }

        final public function force_enqueue_assets( Array $settings ) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Shared GS Plugins asset hook name is kept for backward compatibility.
            do_action( 'GS_Plugins/Force_Enqueue_Assets/' . $this->get_assets_key(), $settings );
        }

        abstract public function get_assets_key();
        
        abstract public function generate_assets_data( Array $settings );

        abstract public function is_builder_preview();
    
        abstract public function enqueue_plugin_assets( $main_post_id, $assets = [] );

        abstract public function enqueue_builder_preview_assets();
    
        abstract public function maybe_force_enqueue_assets( Array $settings );
    
    }
}