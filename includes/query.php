<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Query' ) ) :

class Query {

    public const CPT_EVENT     = 'tribe_events';
    public const CPT_VENUE     = 'tribe_venue';
    public const CPT_ORGANIZER = 'tribe_organizer';

    /**
     * Preview-safe Events Query
     */
    public static function get_preview_events( int $limit = -1 ) : array {

        if ( ! post_type_exists( self::CPT_EVENT ) ) return [];

        $q = new \WP_Query([
            'post_type'                    => self::CPT_EVENT,
            'post_status'                  => 'publish',
            'posts_per_page'               => $limit,
            'orderby'                      => 'date',
            'order'                        => 'DESC',
            'fields'                       => 'ids',
            'no_found_rows'                => true,
            'eventDisplay'                 => 'custom',
            'tribe_suppress_query_filters' => true,
            'update_post_meta_cache'       => false,
            'update_post_term_cache'       => false,
        ]);

        $events = [];

        foreach ( (array) $q->posts as $event_id ) {
            $events[] = self::get_event_linked_data( (int) $event_id );
        }

        return $events;
    }

    /**
     * Main Event Data (NO global post, NO setup_postdata)
     */
    public static function get_event_linked_data( int $event_id ) : array {

        if ( $event_id <= 0 ) {
            return self::empty_event();
        }

        $post = get_post( $event_id );
        if ( ! $post || $post->post_type !== self::CPT_EVENT ) {
            return self::empty_event();
        }

        $venue_id = self::get_event_venue_id( $event_id );
        $org_ids  = self::get_event_organizer_ids( $event_id );

        return [
            'event_id'    => $event_id,
            'event_name'  => $post->post_title,
            'event_slug'  => $post->post_name,
            'venue_id'    => $venue_id,
            'venue'       => $venue_id ? self::get_venue_details( $venue_id ) : [],
            'organizers'  => array_map( [ self::class, 'get_organizer_details' ], $org_ids ),
            'tags'        => self::get_event_terms_full( $event_id, 'post_tag' ),
            'categories'  => self::get_event_terms_full( $event_id, 'tribe_events_cat' ),
            'dates'       => self::get_event_date_meta( $event_id ),
        ];
    }

    /* ---------- HELPERS ---------- */

    private static function empty_event() : array {
        return [
            'event_id'   => 0,
            'event_name' => '',
            'event_slug' => '',
            'venue_id'   => 0,
            'venue'      => [],
            'organizers' => [],
            'tags'       => [],
            'categories' => [],
            'dates'       => [],
        ];
    }

    private static function get_event_terms_full( int $post_id, string $taxonomy ) : array {

        $terms = get_the_terms( $post_id, $taxonomy );
        if ( ! is_array( $terms ) ) return [];

        return array_map( function( \WP_Term $term ) {
            return [
                'term_id'          => (int) $term->term_id,
                'term_taxonomy_id' => (int) $term->term_taxonomy_id,
                'taxonomy'         => $term->taxonomy,
                'name'             => $term->name,
                'slug'             => $term->slug,
                'parent'           => (int) $term->parent,
                'link'             => get_term_link( $term ),
            ];
        }, $terms );
    }

    public static function get_event_venue_id( int $event_id ) : int {
        if ( function_exists( 'tribe_get_venue_id' ) ) {
            return (int) tribe_get_venue_id( $event_id );
        }
        return (int) get_post_meta( $event_id, '_EventVenueID', true );
    }

    public static function get_event_organizer_ids( int $event_id ) : array {
        if ( function_exists( 'tribe_get_organizer_ids' ) ) {
            return array_map( 'absint', (array) tribe_get_organizer_ids( $event_id ) );
        }
        return [];
    }

    public static function get_venue_details( int $venue_id ) : array {
        if ( ! $venue_id ) {
            return array();
        }

        $state = (string) get_post_meta( $venue_id, '_VenueState', true );

        if ( '' === $state ) {
            $state = (string) get_post_meta( $venue_id, '_VenueProvince', true );
        }

        return [
            'id'      => $venue_id,
            'title'   => get_the_title( $venue_id ),
            'address' => (string) get_post_meta( $venue_id, '_VenueAddress', true ),
            'city'    => (string) get_post_meta( $venue_id, '_VenueCity', true ),
            'state'   => $state,
            'zip'     => (string) get_post_meta( $venue_id, '_VenueZip', true ),
            'country' => (string) get_post_meta( $venue_id, '_VenueCountry', true ),
            'lat'     => (string) get_post_meta( $venue_id, '_VenueLat', true ),
            'lng'     => (string) get_post_meta( $venue_id, '_VenueLng', true ),
        ];
    }

    public static function get_organizer_details( int $id ) : array {
        return [
            'id'    => $id,
            'title' => get_the_title( $id ),
        ];
    }

    public static function get_event_date_meta( int $event_id ) : array {
        $start = (string) get_post_meta( $event_id, '_EventStartDate', true );
        $end   = (string) get_post_meta( $event_id, '_EventEndDate', true );

        $is_demo = ! empty( get_post_meta( $event_id, 'gsteca-demo_data', true ) );
        $needs   = teca_event_datetime_needs_time_fallback( $start )
            || ( '' !== $start && '' === $end );

        if ( $is_demo || $needs ) {
            $fallback   = (string) get_post_field( 'post_date', $event_id );
            $normalized = teca_normalize_event_datetime_pair( $start, $end, $fallback );

            if ( '' !== $normalized['start'] ) {
                $start = $normalized['start'];
            }

            if ( '' !== $normalized['end'] ) {
                $end = $normalized['end'];
            }
        }

        return [
            'start' => $start,
            'end'   => $end,
        ];
    }

    public static function is_event_featured( int $event_id ) : bool {
        if ( function_exists( 'tribe_is_featured_event' ) ) {
            return (bool) tribe_is_featured_event( $event_id );
        }

        $featured = get_post_meta( $event_id, '_tribe_featured', true );

        if ( function_exists( 'tribe_is_truthy' ) ) {
            return tribe_is_truthy( $featured );
        }

        return in_array( (string) $featured, array( '1', 'true', 'yes' ), true ) || (int) $featured === 1;
    }

    public static function get_event_start_timestamp( array $event ) : int {
        $start = $event['dates']['start'] ?? '';

        return $start ? (int) strtotime( $start ) : 0;
    }

    public static function get_event_end_timestamp( array $event ) : int {
        $end   = $event['dates']['end'] ?? '';
        $start = $event['dates']['start'] ?? '';

        if ( $end ) {
            return (int) strtotime( $end );
        }

        return $start ? (int) strtotime( $start ) : 0;
    }

    public static function is_past_event( array $event, $now = '' ) : bool {
        $now = $now ? (string) $now : current_time( 'Y-m-d H:i:s' );
        $end = self::get_event_end_timestamp( $event );

        if ( ! $end ) {
            return false;
        }

        return $end < (int) strtotime( $now );
    }

    public static function is_upcoming_event( array $event, $now = '' ) : bool {
        $now   = $now ? (string) $now : current_time( 'Y-m-d H:i:s' );
        $start = self::get_event_start_timestamp( $event );

        if ( ! $start ) {
            return false;
        }

        return $start > (int) strtotime( $now );
    }

    public static function sort_events_by_start( array $events, $order = 'ASC' ) : array {
        usort(
            $events,
            static function( $a, $b ) use ( $order ) {
                $left  = self::get_event_start_timestamp( $a );
                $right = self::get_event_start_timestamp( $b );

                if ( $left === $right ) {
                    return 0;
                }

                if ( 'DESC' === strtoupper( (string) $order ) ) {
                    return $left < $right ? 1 : -1;
                }

                return $left < $right ? -1 : 1;
            }
        );

        return $events;
    }

    public static function sort_events_by_end( array $events, $order = 'DESC' ) : array {
        usort(
            $events,
            static function( $a, $b ) use ( $order ) {
                $left  = self::get_event_end_timestamp( $a );
                $right = self::get_event_end_timestamp( $b );

                if ( $left === $right ) {
                    return 0;
                }

                if ( 'ASC' === strtoupper( (string) $order ) ) {
                    return $left < $right ? -1 : 1;
                }

                return $left < $right ? 1 : -1;
            }
        );

        return $events;
    }

    /**
     * Resolve posts_per_page for Events Section queries from shortcode settings.
     */
    public static function get_events_section_posts_per_page( array $settings ) : int {
        $posts = intval( $settings['posts'] ?? -1 );

        return $posts <= 0 ? -1 : $posts;
    }

    /**
     * Shared shortcode filter args (categories, tags, include/exclude posts).
     */
    public static function build_events_section_filter_args( array $settings, array $ajax_datas = array() ) : array {
        $args      = array();
        $tax_query = array();

        $include_tags = $settings['include_tags'] ?? array();
        $exclude_tags = $settings['exclude_tags'] ?? array();
        $include_cat  = $settings['include_cat'] ?? array();
        $exclude_cat  = $settings['exclude_cat'] ?? array();

        if ( ! empty( $include_tags ) ) {
            $tax_query[] = array(
                'taxonomy' => 'post_tag',
                'field'    => 'term_id',
                'terms'    => $include_tags,
            );
        }

        if ( ! empty( $exclude_tags ) ) {
            $tax_query[] = array(
                'taxonomy' => 'post_tag',
                'field'    => 'term_id',
                'terms'    => $exclude_tags,
                'operator' => 'NOT IN',
            );
        }

        if ( ! empty( $include_cat ) ) {
            $tax_query[] = array(
                'taxonomy' => 'tribe_events_cat',
                'field'    => 'term_id',
                'terms'    => $include_cat,
            );
        }

        if ( ! empty( $exclude_cat ) ) {
            $tax_query[] = array(
                'taxonomy' => 'tribe_events_cat',
                'field'    => 'term_id',
                'terms'    => $exclude_cat,
                'operator' => 'NOT IN',
            );
        }

        if ( ! empty( $ajax_datas['filters'] ) && is_array( $ajax_datas['filters'] ) ) {
            foreach ( $ajax_datas['filters'] as $taxonomy => $terms ) {
                if ( empty( $terms ) || '*' === $terms ) {
                    continue;
                }

                $terms = is_array( $terms ) ? $terms : array( $terms );

                $tax_query[] = array(
                    'taxonomy' => 'category' === $taxonomy ? 'tribe_events_cat' : 'post_tag',
                    'field'    => 'slug',
                    'terms'    => $terms,
                );
            }
        }

        if ( ! empty( $tax_query ) ) {
            if ( count( $tax_query ) > 1 ) {
                $tax_query = array_merge( array( 'relation' => 'AND' ), $tax_query );
            }

            $args['tax_query'] = $tax_query;
        }

        if ( ! empty( $settings['select_by_title'] ) && is_array( $settings['select_by_title'] ) ) {
            $args['post__in'] = $settings['select_by_title'];
        }

        if ( ! empty( $settings['deselect_by_title'] ) && is_array( $settings['deselect_by_title'] ) ) {
            $args['post__not_in'] = $settings['deselect_by_title'];
        }

        return $args;
    }

    /**
     * Convert queried event post IDs into normalized TECA event arrays.
     *
     * @param array<int> $event_ids Event post IDs.
     */
    public static function normalize_event_ids( array $event_ids ) : array {
        $events = array();

        foreach ( $event_ids as $event_id ) {
            $event_id = (int) $event_id;

            if ( $event_id <= 0 ) {
                continue;
            }

            $events[] = self::get_event_linked_data( $event_id );
        }

        return $events;
    }

    /**
     * Base query args shared by all Events Section group queries.
     */
    private static function get_events_section_base_query_args( array $settings, array $ajax_datas = array() ) : array {
        return array_merge(
            self::build_events_section_filter_args( $settings, $ajax_datas ),
            array(
                'post_type'                    => self::CPT_EVENT,
                'post_status'                  => 'publish',
                'posts_per_page'               => self::get_events_section_posts_per_page( $settings ),
                'fields'                       => 'ids',
                'no_found_rows'                => true,
                'ignore_sticky_posts'          => true,
                'eventDisplay'                 => 'custom',
                'tribe_suppress_query_filters' => true,
            )
        );
    }

    /**
     * Query featured TEC events (_tribe_featured).
     */
    public static function query_featured_events( array $settings = array(), array $ajax_datas = array() ) : array {
        if ( ! post_type_exists( self::CPT_EVENT ) ) {
            return array();
        }

        $query_args = array_merge(
            self::get_events_section_base_query_args( $settings, $ajax_datas ),
            array(
                'meta_key'   => '_EventStartDate',
                'orderby'    => 'meta_value',
                'order'      => 'ASC',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => '_tribe_featured',
                            'value'   => '1',
                            'compare' => '=',
                        ),
                        array(
                            'key'     => '_tribe_featured',
                            'value'   => 1,
                            'compare' => '=',
                        ),
                    ),
                    array(
                        'key'     => '_EventStartDate',
                        'compare' => 'EXISTS',
                    ),
                ),
            )
        );

        return self::normalize_event_ids( get_posts( $query_args ) );
    }

    /**
     * Query past TEC events (_EventEndDate before now).
     */
    public static function query_past_events( array $settings = array(), array $ajax_datas = array() ) : array {
        if ( ! post_type_exists( self::CPT_EVENT ) ) {
            return array();
        }

        $current_datetime = current_time( 'mysql' );

        $query_args = array_merge(
            self::get_events_section_base_query_args( $settings, $ajax_datas ),
            array(
                'meta_key'   => '_EventEndDate',
                'orderby'    => 'meta_value',
                'order'      => 'DESC',
                'meta_query' => array(
                    array(
                        'key'     => '_EventEndDate',
                        'value'   => $current_datetime,
                        'compare' => '<',
                        'type'    => 'DATETIME',
                    ),
                ),
            )
        );

        return self::normalize_event_ids( get_posts( $query_args ) );
    }

    /**
     * Query upcoming TEC events (_EventStartDate after now).
     */
    public static function query_upcoming_events( array $settings = array(), array $ajax_datas = array() ) : array {
        if ( ! post_type_exists( self::CPT_EVENT ) ) {
            return array();
        }

        $current_datetime = current_time( 'mysql' );

        $query_args = array_merge(
            self::get_events_section_base_query_args( $settings, $ajax_datas ),
            array(
                'meta_key'   => '_EventStartDate',
                'orderby'    => 'meta_value',
                'order'      => 'ASC',
                'meta_query' => array(
                    array(
                        'key'     => '_EventStartDate',
                        'value'   => $current_datetime,
                        'compare' => '>',
                        'type'    => 'DATETIME',
                    ),
                ),
            )
        );

        return self::normalize_event_ids( get_posts( $query_args ) );
    }

    /**
     * Group queried TEC events for the Events Section layout.
     *
     * @param array $settings   Shortcode settings.
     * @param array $ajax_datas Optional AJAX/preview context.
     */
    public static function get_events_section_data( array $settings = array(), array $ajax_datas = array() ) : array {
        return array(
            'featured_events' => self::query_featured_events( $settings, $ajax_datas ),
            'past_events'     => self::query_past_events( $settings, $ajax_datas ),
            'upcoming_events' => self::query_upcoming_events( $settings, $ajax_datas ),
        );
    }

    /**
     * Query all published TEC venue posts.
     *
     * @return \WP_Post[]
     */
    public static function query_all_venues() : array {
        if ( ! post_type_exists( self::CPT_VENUE ) ) {
            return array();
        }

        $query_args = array(
            'post_type'      => self::CPT_VENUE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        );

        return get_posts( $query_args );
    }

    /**
     * Count upcoming events grouped by venue ID (single query).
     *
     * @return array<int, int>
     */
    public static function get_upcoming_event_counts_by_venue() : array {
        if ( ! post_type_exists( self::CPT_EVENT ) ) {
            return array();
        }

        $current_datetime = current_time( 'mysql' );

        $query_args = array(
            'post_type'              => self::CPT_EVENT,
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
            'meta_query'             => array(
                'relation' => 'AND',
                array(
                    'key'     => '_EventStartDate',
                    'value'   => $current_datetime,
                    'compare' => '>=',
                    'type'    => 'DATETIME',
                ),
                array(
                    'key'     => '_EventVenueID',
                    'compare' => 'EXISTS',
                ),
            ),
        );

        $event_ids = get_posts( $query_args );
        $counts    = array();

        foreach ( $event_ids as $event_id ) {
            $venue_id = (int) get_post_meta( (int) $event_id, '_EventVenueID', true );

            if ( $venue_id <= 0 ) {
                continue;
            }

            if ( ! isset( $counts[ $venue_id ] ) ) {
                $counts[ $venue_id ] = 0;
            }

            ++$counts[ $venue_id ];
        }

        return $counts;
    }

    /**
     * Build venue template data for all published venues.
     *
     * @param array $settings Optional settings (reserved for future use).
     * @return array<int, array<string, mixed>>
     */
    public static function get_all_venues_template_data( array $settings = array() ) : array {
        unset( $settings );

        $venues       = self::query_all_venues();
        $event_counts = self::get_upcoming_event_counts_by_venue();
        $items        = array();

        foreach ( $venues as $venue_post ) {
            $venue_id = (int) $venue_post->ID;

            if ( $venue_id <= 0 ) {
                continue;
            }

            $items[] = self::build_venue_template_item( $venue_id, $event_counts[ $venue_id ] ?? 0 );
        }

        return $items;
    }

    /**
     * @param int $venue_id       Venue post ID.
     * @param int $upcoming_count Upcoming events at this venue.
     * @return array<string, mixed>
     */
    public static function build_venue_template_item( int $venue_id, int $upcoming_count = 0 ) : array {
        $details = self::get_venue_details( $venue_id );

        $address = $details['address'] ?? '';
        $city    = $details['city'] ?? '';
        $state   = $details['state'] ?? '';
        $zip     = $details['zip'] ?? '';
        $country = $details['country'] ?? '';

        if ( function_exists( 'tribe_get_address' ) && '' === $address ) {
            $address = (string) tribe_get_address( $venue_id );
        }

        if ( function_exists( 'tribe_get_city' ) && '' === $city ) {
            $city = (string) tribe_get_city( $venue_id );
        }

        if ( function_exists( 'tribe_get_stateprovince' ) && '' === $state ) {
            $state = (string) tribe_get_stateprovince( $venue_id );
        }

        if ( function_exists( 'tribe_get_zip' ) && '' === $zip ) {
            $zip = (string) tribe_get_zip( $venue_id );
        }

        if ( function_exists( 'tribe_get_country' ) && '' === $country ) {
            $country = (string) tribe_get_country( $venue_id );
        }

        $phone = '';

        if ( function_exists( 'tribe_get_phone' ) ) {
            $phone = trim( (string) tribe_get_phone( $venue_id ) );
        }

        if ( '' === $phone ) {
            $phone = trim( (string) get_post_meta( $venue_id, '_VenuePhone', true ) );
        }

        $website = '';

        if ( function_exists( 'tribe_get_venue_website_url' ) ) {
            $website = trim( (string) tribe_get_venue_website_url( $venue_id ) );
        }

        if ( '' === $website ) {
            $website = trim( (string) get_post_meta( $venue_id, '_VenueURL', true ) );
        }

        $thumbnail = get_the_post_thumbnail_url( $venue_id, 'large' );
        $thumbnail = $thumbnail ? (string) $thumbnail : '';

        $venue_data = array(
            'id'             => $venue_id,
            'title'          => get_the_title( $venue_id ),
            'permalink'      => get_permalink( $venue_id ) ?: '',
            'thumbnail'      => $thumbnail,
            'address'        => $address,
            'city'           => $city,
            'state'          => $state,
            'zip'            => $zip,
            'country'        => $country,
            'phone'          => $phone,
            'website'        => $website,
            'upcoming_count' => absint( $upcoming_count ),
        );

        $venue_data['full_address'] = teca_build_venue_full_address( $venue_data );
        $venue_data['map_link']     = self::resolve_venue_map_link( $venue_id, $venue_data['full_address'] );

        return $venue_data;
    }

    /**
     * @param int    $venue_id     Venue post ID.
     * @param string $full_address Full address string.
     * @return string
     */
    protected static function resolve_venue_map_link( int $venue_id, string $full_address ) : string {
        if ( function_exists( 'tribe_get_map_link' ) ) {
            $map_link = tribe_get_map_link( $venue_id );

            if ( is_string( $map_link ) && '' !== trim( wp_strip_all_tags( $map_link ) ) ) {
                if ( preg_match( '/href=["\']([^"\']+)["\']/', $map_link, $matches ) ) {
                    return esc_url_raw( $matches[1] );
                }
            }
        }

        if ( '' === trim( $full_address ) ) {
            return '';
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $full_address );
    }

    /**
     * Query all published TEC organizer posts.
     *
     * @return \WP_Post[]
     */
    public static function query_all_organizers() : array {
        if ( ! post_type_exists( self::CPT_ORGANIZER ) ) {
            return array();
        }

        $query_args = array(
            'post_type'      => self::CPT_ORGANIZER,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        );

        return get_posts( $query_args );
    }

    /**
     * Count upcoming events grouped by organizer ID (single query).
     *
     * @return array<int, int>
     */
    public static function get_upcoming_event_counts_by_organizer() : array {
        if ( ! post_type_exists( self::CPT_EVENT ) ) {
            return array();
        }

        $current_datetime = current_time( 'mysql' );

        $query_args = array(
            'post_type'              => self::CPT_EVENT,
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
            'meta_query'             => array(
                array(
                    'key'     => '_EventStartDate',
                    'value'   => $current_datetime,
                    'compare' => '>=',
                    'type'    => 'DATETIME',
                ),
            ),
        );

        $event_ids = get_posts( $query_args );
        $counts    = array();

        foreach ( $event_ids as $event_id ) {
            $organizer_ids = self::get_event_organizer_ids( (int) $event_id );

            foreach ( $organizer_ids as $organizer_id ) {
                if ( $organizer_id <= 0 ) {
                    continue;
                }

                if ( ! isset( $counts[ $organizer_id ] ) ) {
                    $counts[ $organizer_id ] = 0;
                }

                ++$counts[ $organizer_id ];
            }
        }

        return $counts;
    }

    /**
     * Build organizer template data for all published organizers.
     *
     * @param array $settings Optional settings (reserved for future use).
     * @return array<int, array<string, mixed>>
     */
    public static function get_all_organizers_template_data( array $settings = array() ) : array {
        unset( $settings );

        $organizers   = self::query_all_organizers();
        $event_counts = self::get_upcoming_event_counts_by_organizer();
        $items        = array();

        foreach ( $organizers as $organizer_post ) {
            $organizer_id = (int) $organizer_post->ID;

            if ( $organizer_id <= 0 ) {
                continue;
            }

            $items[] = self::build_organizer_template_item( $organizer_id, $event_counts[ $organizer_id ] ?? 0 );
        }

        return $items;
    }

    /**
     * @param int $organizer_id   Organizer post ID.
     * @param int $upcoming_count Upcoming events for this organizer.
     * @return array<string, mixed>
     */
    public static function build_organizer_template_item( int $organizer_id, int $upcoming_count = 0 ) : array {
        $phone = '';

        if ( function_exists( 'tribe_get_organizer_phone' ) ) {
            $phone = trim( (string) tribe_get_organizer_phone( $organizer_id ) );
        }

        if ( '' === $phone ) {
            $phone = trim( (string) get_post_meta( $organizer_id, '_OrganizerPhone', true ) );
        }

        $email = '';

        if ( function_exists( 'tribe_get_organizer_email' ) ) {
            $email = trim( (string) tribe_get_organizer_email( $organizer_id ) );
        }

        if ( '' === $email ) {
            $email = trim( (string) get_post_meta( $organizer_id, '_OrganizerEmail', true ) );
        }

        $email = sanitize_email( $email );

        $website = '';

        if ( function_exists( 'tribe_get_organizer_website_url' ) ) {
            $website = trim( (string) tribe_get_organizer_website_url( $organizer_id ) );
        }

        if ( '' === $website ) {
            $website = trim( (string) get_post_meta( $organizer_id, '_OrganizerWebsite', true ) );
        }

        if ( '' === $website ) {
            $website = trim( (string) get_post_meta( $organizer_id, '_OrganizerURL', true ) );
        }

        $thumbnail = get_the_post_thumbnail_url( $organizer_id, 'medium' );
        $thumbnail = $thumbnail ? (string) $thumbnail : '';

        $excerpt     = get_the_excerpt( $organizer_id );
        $description = get_post_field( 'post_content', $organizer_id );

        return array(
            'id'             => $organizer_id,
            'title'          => get_the_title( $organizer_id ),
            'permalink'      => get_permalink( $organizer_id ) ?: '',
            'thumbnail'      => $thumbnail,
            'phone'          => $phone,
            'email'          => $email,
            'website'        => $website,
            'excerpt'        => is_string( $excerpt ) ? trim( $excerpt ) : '',
            'description'    => is_string( $description ) ? trim( $description ) : '',
            'upcoming_count' => absint( $upcoming_count ),
        );
    }
}

endif;
