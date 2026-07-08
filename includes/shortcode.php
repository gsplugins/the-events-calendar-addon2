<?php 
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcode {
	
	public function __construct() {
		
		add_shortcode( 'gs-teca', [ $this, 'shortcode' ] );

		add_action('wp_ajax_gs_teca_filter', [ $this, 'filter_teca' ]);
		add_action('wp_ajax_nopriv_gs_teca_filter', [ $this, 'filter_teca' ]);

		add_action('wp_ajax_gs_teca_ajax_pagination', [ $this, 'ajax_pagination' ]);
		add_action('wp_ajax_nopriv_gs_teca_ajax_pagination', [ $this, 'ajax_pagination' ]);

		add_action('wp_ajax_gs_teca_load_more', [ $this, 'load_more_posts' ]);
		add_action('wp_ajax_nopriv_gs_teca_load_more', [ $this, 'load_more_posts' ]);

		add_action( 'wp_ajax_teca_ajax_search_events', [ $this, 'ajax_search_events' ] );
		add_action( 'wp_ajax_nopriv_teca_ajax_search_events', [ $this, 'ajax_search_events' ] );

	}


    public function query_events( $settings, $ajax_datas = [] ) {
        return $this->get_events( $settings, $ajax_datas );
    }

    protected function get_events( $settings, $ajax_datas = [] ) {
        
        $pagination_type = teca_resolve_pagination_type_for_context( $settings['pagination_type'] ?? 'normal-pagination' );

        $posts = intval( $settings['posts'] ?? -1 );
		$order   = $settings['order'];
		$orderby = teca_resolve_orderby_for_context( $settings['orderby'] ?? 'date' );

        if ( $settings['gs_teca_pagination'] === 'on' ) {

            if ( in_array( $pagination_type, ['normal-pagination','ajax-pagination'], true ) ) {

                $posts = intval( $settings['item_per_page'] ?? $posts );

                if ( wp_doing_ajax() && isset( $ajax_datas['posts_per_page'] ) ) {
                    $posts = intval( $ajax_datas['posts_per_page'] );
                }

            }

            elseif ( in_array( $pagination_type, ['load-more-button','load-more-scroll'], true ) ) {

                $posts = intval( $settings['initial_items'] ?? $posts );

                if ( wp_doing_ajax() && isset( $ajax_datas['posts_per_page'] ) ) {
                    $posts = intval( $ajax_datas['posts_per_page'] );
                }
            }
        }

        if ( $posts <= 0 ) {
            $posts = -1;
        }

        $param_name = 'paged' . $settings['id'];

        if ( isset( $ajax_datas['paged'] ) ) {
            $paged = max( 1, intval( $ajax_datas['paged'] ) );
        } elseif ( isset( $_GET[$param_name] ) ) {
            $paged = max( 1, intval( $_GET[$param_name] ) );
        } else {
            $paged = 1;
        }


        $offset = isset( $ajax_datas['offset'] )
            ? max(0, intval($ajax_datas['offset']))
            : 0;

        if ( in_array( $pagination_type, ['normal-pagination','ajax-pagination'], true ) ) {

            $args = [
                'post_type'      => 'tribe_events',
                'post_status'    => 'publish',
                'posts_per_page' => $posts,
                'paged'          => $paged,
                'fields'         => 'ids',
                'no_found_rows'  => false
            ];

        } else {

            $args = [
                'post_type'      => 'tribe_events',
                'post_status'    => 'publish',
                'posts_per_page' => $posts,
                'offset'         => $offset,
                'fields'         => 'ids',
                'no_found_rows'  => false
            ];
        }

		$include_tags = $settings['include_tags'] ?? [];
		$exclude_tags = $settings['exclude_tags'] ?? [];
		$include_cat  = $settings['include_cat'] ?? [];
		$exclude_cat  = $settings['exclude_cat'] ?? [];
		$cat_order    = $settings['cat_order'] ?? [];
		$cat_order_by  = teca_resolve_cat_order_by_for_context( $settings['cat_order_by'] ?? 'none' );
		$args['order']   = $order;
		$args['orderby'] = $orderby;
		$args['eventDisplay'] = 'custom';
		$args['ignore_sticky_posts'] = true;
		$args['tribe_suppress_query_filters'] = true;

		if ( !empty($settings['select_by_title']) && is_array($settings['select_by_title'])) {
			$args['post__in'] =  $settings['select_by_title'];
			$args['orderby']  = 'post__in';
		}

		if ( !empty($settings['deselect_by_title']) && is_array($settings['deselect_by_title'])) {
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in	
			$args['post__not_in'] =  $settings['deselect_by_title'];
		}

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query	
		$tax_query = [];

		if ( !empty( $include_tags ) ) {
			$tax_query[] = array(
				'taxonomy'  => 'post_tag',
				'field'     => 'term_id',
				'terms'     => $include_tags,
				// 'operator'  => 'IN'
			);
		}
		
		if ( !empty( $exclude_tags ) ) {
			$tax_query[] = array(
				'taxonomy'  => 'post_tag',
				'field'     => 'term_id',
				'terms'     => $exclude_tags,
				'operator'  => 'NOT IN'
			);
		}

		if ( !empty( $include_cat ) ) {
			$tax_query[] = array(
				'taxonomy' => 'tribe_events_cat',
				'field'    => 'term_id',
				'terms'    => $include_cat,
				// 'operator' => 'IN'
			);
		}

		if( !empty( $exclude_cat ) ) {
			$tax_query[] = array(
				'taxonomy'  => 'tribe_events_cat',
				'field'     => 'term_id',
				'terms'     => $exclude_cat,
				'operator'  => 'NOT IN'
			);
		}


        if ( ! empty( $ajax_datas['filters'] ) ) {

			$ajax_tax_query = $this->build_tax_query( $ajax_datas['filters'] );

			if ( ! empty( $ajax_tax_query ) ) {
				$tax_query = array_merge( $tax_query, $ajax_tax_query );
			}

		}

		if ( ! empty($tax_query) ) {

			if ( count($tax_query) > 1 ) {
				$tax_query = array_merge(['relation'=>'AND'], $tax_query);
			}

			$args['tax_query'] = $tax_query;

		}

		$cat_options = [
			'taxonomy'   => 'tribe_events_cat',
			'hide_empty' => false,
		];

		if ( ! empty($include_cat) ) {
			$cat_options['include'] = $include_cat;
			$cat_options['orderby'] = 'include';
		}

		if ( ! empty($exclude_cat) ) {
			$cat_options['exclude'] = $exclude_cat;
		}

		if ( ! empty($cat_order_by) && $cat_order_by !== 'none' ) {
			$cat_options['orderby'] = $cat_order_by;
		}

		if ( ! empty($cat_order) ) {
			$cat_options['order'] = strtoupper($cat_order);
		}

		$tag_options = [
			'taxonomy'   => 'tribe_events_cat',
			'hide_empty' => false,
		];

		if ( ! empty($include_tags) ) {
			$tag_options['include'] = $include_tags;
			$tag_options['orderby'] = 'include';
		}

		if ( ! empty($exclude_tags) ) {
			$tag_options['exclude'] = $exclude_tags;
		}

		if ( ! empty($cat_order_by) && $cat_order_by !== 'none' ) {
			$tag_options['orderby'] = $cat_order_by;
		}

		if ( ! empty($cat_order) ) {
			$tag_options['order'] = strtoupper($cat_order);
		}
		

		if (isset($settings['show_empty_terms']) && $settings['show_empty_terms'] == 'off') {
			$cat_options['hide_empty'] = true;
			$tag_options['hide_empty'] = true;
		} else {
			$cat_options['hide_empty'] = false;
			$tag_options['hide_empty'] = false;
		}

        $q = new \WP_Query( $args );

        $events = [];

        foreach ( $q->posts as $eid ) {
            $events[] = Query::get_event_linked_data( (int) $eid );
        }

        return [
            'events' => $events,
            'found'  => (int) $q->found_posts,
        ];
    }


	protected function build_tax_query( $filters ) {

		$tax_query = [];

		foreach ( $filters as $taxonomy => $terms ) {

			if ( empty( $terms ) || $terms === '*' ) {
				continue;
			}

			$terms = is_array( $terms ) ? $terms : [ $terms ];

			$tax_query[] = [
				'taxonomy' => $taxonomy === 'category'
					? 'tribe_events_cat'
					: 'post_tag',
				'field'    => 'slug',
				'terms'    => $terms,
			];
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		return $tax_query;
	}

	public function ajax_pagination() {
		if ( ! check_ajax_referer('gs_teca_user_action') ) wp_send_json_error( __('Unauthorised Request', 'posts-grid'), 401 );

		$shortcode_id   = sanitize_text_field( $_POST['shortcode_id'] );
		$posts_per_page = isset($_POST['posts_per_page'])
			? intval($_POST['posts_per_page'])
			: -1;

		if ( $posts_per_page === 0 ) {
			$posts_per_page = -1;
		}

		$filters = $_POST['filters'] ?? [];

		$is_preview = ! is_numeric( $shortcode_id );

		$settings = $this->get_shortcode_settings( $shortcode_id, $is_preview );

		$data = $this->get_events( $settings, [
			'filters'        => $filters,
			'posts_per_page' => $posts_per_page,
            'paged'          => intval($_POST['paged']),
		]);


		$html = $this->render( $settings, [
			'filters'        => $filters,
			'posts_per_page' => $posts_per_page,
			'events'         => $data['events'], 
			'found'          => $data['found'],
			'paged'          => intval( $_POST['paged'] ),
		]);

		$paged = $_POST['paged'];

		$pagination = get_ajax_pagination( $shortcode_id, $posts_per_page, $paged, $data['found'] );

		wp_send_json_success(array( 'posts' => $html, 'pagination' => $pagination, 'foundPosts' => $data['found'] ), 200 );
		wp_die();
	}

	public function load_more_posts() {
		if ( ! check_ajax_referer('gs_teca_user_action') ) wp_send_json_error( __('Unauthorised Request', 'posts-grid'), 401 );

		$shortcode_id   = sanitize_text_field( $_POST['shortcode_id'] );
		$posts_per_page = isset($_POST['posts_per_page'])
			? intval($_POST['posts_per_page'])
			: -1;

		if ( $posts_per_page === 0 ) {
			$posts_per_page = -1;
		}

		$filters = $_POST['filters'] ?? [];

		$is_preview = ! is_numeric( $shortcode_id );

		$settings = $this->get_shortcode_settings( $shortcode_id, $is_preview );

		$data = $this->get_events( $settings, [
			'filters'        => $filters,
			'posts_per_page' => $posts_per_page,
            'offset'         => intval( $_POST['offset'] )
		]);


		$html = $this->render( $settings, [
			'filters'        => $filters,
			'posts_per_page' => $posts_per_page,
			'events'         => $data['events'], 
			'found'          => $data['found'],
			'offset'         => intval( $_POST['offset'] )
		]);


		wp_send_json_success(array( 'posts' => $html, 'foundPosts' => $data['found'] ), 200 );
		wp_die();
	}

	public function filter_teca() {

		if ( ! check_ajax_referer('gs_teca_user_action') ) wp_send_json_error( __('Unauthorised Request', 'posts-grid'), 401 );

		$shortcode_id   = sanitize_text_field( $_POST['shortcode_id'] );
		$posts_per_page = isset($_POST['posts_per_page'])
			? intval($_POST['posts_per_page'])
			: -1;

		if ( $posts_per_page === 0 ) {
			$posts_per_page = -1;
		}

		$filters = $_POST['filters'] ?? [];

		$is_preview = ! is_numeric( $shortcode_id );

		$settings = $this->get_shortcode_settings( $shortcode_id, $is_preview );

		$data = $this->get_events( $settings, [
			'filters'        => $filters,
			'posts_per_page' => $posts_per_page,
		]);


		$html = $this->render( $settings, [
			'filters'        => $filters,
			'posts_per_page' => $posts_per_page,
			'events'         => $data['events'], 
			'found'          => $data['found']
		]);

		$pagination = get_ajax_pagination(
			$shortcode_id,
			$posts_per_page,
			1,
            $data['found']
		);

		wp_send_json_success([
			'posts'       => $html,
			'foundPosts'  => $data['found'], 
			'pagination'  => $pagination,
		],200);
		wp_die();
	}

	public function ajax_search_events() {
		if ( ! check_ajax_referer( 'gs_teca_user_action' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Unauthorised Request', 'the-events-calendar-addon' ),
				),
				401
			);
		}

		$shortcode_id = sanitize_text_field( wp_unslash( $_POST['instance_id'] ?? $_POST['shortcode_id'] ?? '' ) );
		$view_type    = sanitize_key( wp_unslash( $_POST['view_type'] ?? '' ) );

		if ( '' === $shortcode_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing shortcode instance.', 'the-events-calendar-addon' ),
				),
				400
			);
		}

		if ( ! in_array( $view_type, array( 'grid', 'masonry', 'filter' ), true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Unsupported view type.', 'the-events-calendar-addon' ),
				),
				400
			);
		}

		$is_preview = ! is_numeric( $shortcode_id );
		$settings   = $this->get_shortcode_settings( $shortcode_id, $is_preview );

		if ( empty( $settings ) || ! teca_should_render_search_by_bar( $settings ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Search is not enabled for this instance.', 'the-events-calendar-addon' ),
				),
				400
			);
		}

		$search_params = array(
			'search_title'     => sanitize_text_field( wp_unslash( $_POST['search_title'] ?? '' ) ),
			'search_venue'     => sanitize_text_field( wp_unslash( $_POST['search_venue'] ?? '' ) ),
			'search_organizer' => sanitize_text_field( wp_unslash( $_POST['search_organizer'] ?? '' ) ),
			'search_city'      => sanitize_text_field( wp_unslash( $_POST['search_city'] ?? '' ) ),
			'result_limit'     => teca_get_search_result_limit( $settings, isset( $_POST['result_limit'] ) ? (int) $_POST['result_limit'] : null ),
		);

		if ( ! teca_search_query_has_active_terms( $search_params ) ) {
			wp_send_json_success(
				array(
					'html'    => '',
					'count'   => 0,
					'message' => '',
					'cleared' => true,
				)
			);
		}

		$data           = $this->get_events( $settings, array( 'posts_per_page' => -1 ) );
		$matched_events = teca_filter_events_by_search_query( $data['events'] ?? array(), $settings, $search_params );
		$html           = $this->render_event_items_html( $settings, $matched_events );
		$count          = count( $matched_events );
		$message        = $count ? '' : __( 'No events found.', 'the-events-calendar-addon' );

		wp_send_json_success(
			array(
				'html'    => $html,
				'count'   => $count,
				'message' => $message,
				'cleared' => false,
			)
		);
	}

	protected function get_theme_template_file( $theme ) {
		$template = 'gs-teca-theme-01.php';

		switch ( $theme ) {
			case 'gs-teca-style-2':
				$template = 'gs-teca-theme-02.php';
				break;
			case 'gs-teca-style-3':
				$template = 'gs-teca-theme-03.php';
				break;
			case 'gs-teca-style-4':
				$template = 'gs-teca-theme-04.php';
				break;
			case 'gs-teca-style-5':
				$template = 'gs-teca-theme-05.php';
				break;
			case 'gs-teca-style-6':
				$template = 'gs-teca-theme-06.php';
				break;
			case 'gs-teca-style-7':
				$template = 'gs-teca-theme-07.php';
				break;
			case 'gs-teca-style-8':
				$template = 'gs-teca-theme-08.php';
				break;
			case 'gs-teca-style-9':
				$template = 'gs-teca-theme-09.php';
				break;
			case 'gs-teca-style-10':
				$template = 'gs-teca-theme-10.php';
				break;
			case 'gs-teca-list-style-1':
				$template = 'gs-teca-list-theme-01.php';
				break;
			case 'gs-teca-list-style-2':
				$template = 'gs-teca-list-theme-02.php';
				break;
			case 'gs-teca-list-style-3':
				$template = 'gs-teca-list-theme-03.php';
				break;
			case 'gs-teca-list-style-4':
				$template = 'gs-teca-list-theme-04.php';
				break;
			case 'gs-teca-list-style-5':
				$template = 'gs-teca-list-theme-05.php';
				break;
			case 'gs-teca-table-style-1':
				$template = 'gs-teca-table-theme-01.php';
				break;
			case 'gs-teca-table-style-2':
				$template = 'gs-teca-table-theme-02.php';
				break;
			case 'gs-teca-table-style-3':
				$template = 'gs-teca-table-theme-03.php';
				break;
			case 'gs-teca-table-style-4':
				$template = 'gs-teca-table-theme-04.php';
				break;
			case 'gs-teca-table-style-5':
				$template = 'gs-teca-table-theme-05.php';
				break;
			case 'gs-teca-timeline-1':
				$template = 'timeline/timeline-1.php';
				break;
			case 'gs-teca-timeline-2':
				$template = 'timeline/timeline-2.php';
				break;
			case 'gs-teca-timeline-3':
				$template = 'timeline/timeline-3.php';
				break;
			case 'gs-teca-accordion-1':
				$template = 'accordion/accordion-1.php';
				break;
			case 'gs-teca-accordion-2':
				$template = 'accordion/accordion-2.php';
				break;
			case 'gs-teca-accordion-3':
				$template = 'accordion/accordion-3.php';
				break;
		}

		return $template;
	}

	public function render_event_items_html( $settings, $events ) {
		if ( empty( $events ) || ! is_array( $events ) ) {
			return '';
		}

		$atts      = $settings;
		$view_type = teca_resolve_view_type_for_context( $settings['view_type'] ?? 'grid' );
		$theme     = teca_resolve_theme_template_for_context( $settings['gs_teca_template'] ?? 'gs-teca-style-1' );

		teca_set_shortcode_date_format_context( $settings );
		teca_set_date_format_context(
			teca_resolve_shortcode_layout_date_key( $settings ),
			$settings
		);

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $atts );

		ob_start();
		include Template_Loader::locate_template( $this->get_theme_template_file( $theme ) );
		teca_clear_shortcode_date_format_context();

		return ob_get_clean();
	}



    public function render( $settings, $ajax_datas = array() ) {
		if ( ! is_array( $ajax_datas ) ) {
			$ajax_datas = array();
		}

		$gs_teca_paged = isset( $ajax_datas['paged'] )
            ? intval( $ajax_datas['paged'] )
            : 1;


        $settings['view_type'] = teca_resolve_view_type_for_context( $settings['view_type'] ?? 'grid' );
        $view_type            = $settings['view_type'];
        $settings['pagination_type'] = teca_resolve_pagination_type_for_context( $settings['pagination_type'] ?? 'normal-pagination' );
        $pagination_type      = $settings['pagination_type'];
		$is_calendar_view     = teca_is_calendar_view_type( $view_type );
		$is_events_section    = teca_is_events_section_view_type( $view_type );
		$is_venue_template    = teca_is_venue_template_view_type( $view_type );
		$is_organizer_template = teca_is_organizer_template_view_type( $view_type );
		$is_special_layout    = $is_calendar_view || $is_events_section || $is_venue_template || $is_organizer_template;

		if ( $settings['gs_teca_pagination'] == 'on' ) {
			if ( $settings['view_type'] == 'carousel' || ( $settings['view_type'] == 'filter' && $settings['gs_teca_filter_type'] == 'normal-filter' ) || $is_special_layout ) {
				$settings['gs_teca_pagination'] = 'off';
			}
		}

		$atts = $settings;

		// $_filter_enabled = $filter_enabled == 'on';

		if ( $is_calendar_view ) {
			$event_details = array();
			$found_events  = 0;
		} elseif ( $is_events_section ) {
			$event_details = array();
			$found_events  = 0;
		} elseif ( $is_venue_template ) {
			$event_details = array();
			$found_events  = 0;
		} elseif ( $is_organizer_template ) {
			$event_details = array();
			$found_events  = 0;
		} elseif ( ! empty( $ajax_datas['events'] ) ) {
			$event_details = $ajax_datas['events'];
			$found_events  = $ajax_datas['found'] ?? count( $event_details );
		} else {
			$data = $this->get_events( $atts, $ajax_datas );
			$event_details = $data['events'];
			$found_events  = $data['found'];
		}

		$events = $event_details;

		extract($atts);

		teca_set_shortcode_date_format_context( $settings );
		teca_set_date_format_context(
			teca_resolve_shortcode_layout_date_key( $settings ),
			$settings
		);

        if ( empty( $event_details ) && ! $is_special_layout ) {
			teca_clear_shortcode_date_format_context();
			return '';
		}

		$theme = teca_resolve_theme_template_for_context( $settings['gs_teca_template'] ?? '' );
        $id = sanitize_key( $settings['id'] );
        $classes = ['gs_teca_area',"gs_teca_area_$id", $theme, 'view_type_' . $view_type];
        $classes = array_merge( $classes, teca_get_typography_custom_body_classes( $settings ) );
        $classes = array_merge( $classes, teca_get_color_custom_body_classes( $settings ) );
        $classes = array_merge( $classes, teca_get_typography_scope_body_classes( $settings ) );
        $calendar_area_class = teca_get_calendar_area_layout_class( $settings );
        $events_section_area_class = teca_get_events_section_area_layout_class( $settings );
        $venue_template_area_class = teca_get_venue_template_area_layout_class( $settings );
        $organizer_template_area_class = teca_get_organizer_template_area_layout_class( $settings );

        if ( $calendar_area_class ) {
            $classes[] = $calendar_area_class;
        }

        if ( $events_section_area_class ) {
            $classes[] = $events_section_area_class;
        }

        if ( $venue_template_area_class ) {
            $classes[] = $venue_template_area_class;
        }

        if ( $organizer_template_area_class ) {
            $classes[] = $organizer_template_area_class;
        }

        if ( 'gs-teca-style-1' === $theme && function_exists( 'teca_get_style_1_column_body_classes' ) ) {
            $classes = array_merge( $classes, teca_get_style_1_column_body_classes( $settings ) );
        }

        if ( 'gs-teca-style-3' === $theme && function_exists( 'teca_get_style_3_column_body_classes' ) ) {
            $classes = array_merge( $classes, teca_get_style_3_column_body_classes( $settings ) );
        }

        if ( 'gs-teca-style-2' === $theme && function_exists( 'teca_get_style_2_column_body_classes' ) ) {
            $classes = array_merge( $classes, teca_get_style_2_column_body_classes( $settings ) );
        }

        if ( 'gs-teca-style-7' === $theme && function_exists( 'teca_get_style_7_column_body_classes' ) ) {
            $classes = array_merge( $classes, teca_get_style_7_column_body_classes( $settings ) );
        }

        if ( 'gs-teca-style-8' === $theme && function_exists( 'teca_get_style_8_column_body_classes' ) ) {
            $classes = array_merge( $classes, teca_get_style_8_column_body_classes( $settings ) );
        }

        if ( 'gs-teca-style-9' === $theme && function_exists( 'teca_get_style_9_column_body_classes' ) ) {
            $classes = array_merge( $classes, teca_get_style_9_column_body_classes( $settings ) );
        }

        if ( 'gs-teca-style-10' === $theme && function_exists( 'teca_get_style_10_column_body_classes' ) ) {
            $classes = array_merge( $classes, teca_get_style_10_column_body_classes( $settings ) );
        }

        $theme_area_style = '';
        if ( 'gs-teca-style-1' === $theme && function_exists( 'teca_get_style_1_column_css_vars' ) ) {
            $theme_area_style .= teca_get_style_1_column_css_vars( $settings );
        }
        if ( 'gs-teca-style-3' === $theme && function_exists( 'teca_get_style_3_column_css_vars' ) ) {
            $theme_area_style .= teca_get_style_3_column_css_vars( $settings );
        }
        if ( 'gs-teca-style-2' === $theme && function_exists( 'teca_get_style_2_column_css_vars' ) ) {
            $theme_area_style .= teca_get_style_2_column_css_vars( $settings );
        }
        if ( 'gs-teca-style-7' === $theme && function_exists( 'teca_get_style_7_column_css_vars' ) ) {
            $theme_area_style .= teca_get_style_7_column_css_vars( $settings );
        }
        if ( 'gs-teca-style-8' === $theme && function_exists( 'teca_get_style_8_column_css_vars' ) ) {
            $theme_area_style .= teca_get_style_8_column_css_vars( $settings );
        }
        if ( 'gs-teca-style-9' === $theme && function_exists( 'teca_get_style_9_column_css_vars' ) ) {
            $theme_area_style .= teca_get_style_9_column_css_vars( $settings );
        }
        if ( 'gs-teca-style-10' === $theme && function_exists( 'teca_get_style_10_column_css_vars' ) ) {
            $theme_area_style .= teca_get_style_10_column_css_vars( $settings );
        }

        $gs_row_classes = ['gs-roow', 'gs-teca'];
        if ( 'gs-teca-style-1' === $theme ) {
            $gs_row_classes[] = 'teca-grid-style-1-wrapper';
        }
        if ( 'gs-teca-style-3' === $theme ) {
            $gs_row_classes[] = 'teca-grid-style-3-wrapper';
        }
        if ( 'gs-teca-style-2' === $theme ) {
            $gs_row_classes[] = 'teca-s2-events-parent';
            $gs_row_classes[] = 'teca-grid-style-2-wrapper';
        }
        if ( 'gs-teca-style-7' === $theme ) {
            $gs_row_classes[] = 'teca-grid-style-7-wrapper';
        }
        if ( 'gs-teca-style-8' === $theme ) {
            $gs_row_classes[] = 'teca-grid-style-8-wrapper';
        }
        if ( 'gs-teca-style-9' === $theme ) {
            $gs_row_classes[] = 'teca-grid-style-9-wrapper';
        }
        if ( 'gs-teca-style-10' === $theme ) {
            $gs_row_classes[] = 'teca-grid-style-10-wrapper';
        }
		$img_effect_class = '';
		$img_effect_class = "gs-teca--img-efect_$image_filter_style gs-teca--img-hover-efect_$image_filter_hover_style";

		
		if ( ! empty( $ajax_datas['filters'] ) ) {

			if ( wp_doing_ajax() && !empty($ajax_datas) ) {

				if ( 'on' === $gs_teca_pagination && empty($ajax_datas['posts_per_page']) ) {
					if ( in_array( $pagination_type, ['load-more-button', 'load-more-scroll'], true ) ) {
						$posts = (int) $initial_items;
					}
				}

			} else {

				if ( in_array( $pagination_type, ['load-more-button', 'load-more-scroll'], true ) ) {
					$posts = (int) $initial_items;
				}

			}

		}

		$shouldLoadPagination = false;
		if ( $gs_teca_pagination === 'on' && $view_type !== 'carousel' && ! $is_special_layout && $found_events >= $posts * $gs_teca_paged ) {
			$shouldLoadPagination = true;
		}

		$_popup_enabled 	            = false;
		$gs_teca_more 				    = get_translation( 'gs_teca_more' );
		$gs_teca_nxt_prev 			    = getoption( 'gs_teca_nxt_prev', 'off' );
		
		if ( $view_type == 'carousel' ) {
			$sliding_effect = '';	
			$gs_teca_slider_navs = $gs_teca_slider_navs == 'on' ? true :false;
			$gs_teca_is_autop = $gs_teca_is_autop == 'on' ? true :false;
			$gs_teca_reverse_direction = $gs_teca_reverse_direction == 'on' ? true :false;
			$gs_teca_pause_on_hover = $gs_teca_pause_on_hover == 'on' ? true :false;
			$gs_teca_inf_loop = $gs_teca_inf_loop == 'on' ? true :false;
			$gs_teca_slider_dots = $gs_teca_slider_dots == 'on' ? true :false;

			$options = array(
				'effect'			      =>  $sliding_effect,
				'speed' 			      =>  (int) $gs_teca_slide_speed,
				'isAutoplay' 		      =>  wp_validate_boolean( $gs_teca_is_autop ),
				'autoplayDelay' 	      =>  (int) $gs_teca_autop_pause,
				'loop' 				      =>  wp_validate_boolean( $gs_teca_inf_loop ),
				'pauseOnHover' 		      =>  wp_validate_boolean( $gs_teca_pause_on_hover ),
				'reverseDirection'        =>  wp_validate_boolean( $gs_teca_reverse_direction ),
				'navs' 				      =>  wp_validate_boolean( $gs_teca_slider_navs ),
				'navs_pos' 			      =>  sanitize_key( $gs_teca_ctrl_pos ),
				'dots' 				      =>  wp_validate_boolean( $gs_teca_slider_dots ),
	
				'spaceBetween' 		      =>  (int) $gs_teca_margin,
				'desktop_columns'         =>  $columns,
				'tablet_columns'          =>  $columns_tablet,
				'mobile_columns'          =>  $columns_mobile_portrait,
				'columns_small_mobile'    =>  $columns_mobile,
				'slidesPerGroup' 	      =>  max(1, (int) $gs_teca_move_item),
				'gs_teca_navs_style'	  =>  $gs_teca_navs_style,
				'gs_teca_dots_style'	  =>  $gs_teca_dots_style,
			);

			$options = json_encode( $options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );

		}

		if ($view_type == 'ticker') {
			$gs_teca_pause_on_hover = $gs_teca_pause_on_hover == 'on' ? true :false;
			$gs_teca_reverse_direction = $gs_teca_reverse_direction == 'on' ? true :false;

			$options = array(
				'mode' 				      =>  'horizontal',
				'speed' 			      =>  (int) $gs_teca_slide_speed,
				'pauseOnHover' 		      =>  wp_validate_boolean( $gs_teca_pause_on_hover ),
				'spaceBetween' 		      =>  (int) $gs_teca_margin,
				'desktop_columns'         =>  $columns,
				'tablet_columns'          =>  $columns_tablet,
				'mobile_columns'          =>  $columns_mobile_portrait,
				'columns_small_mobile'    =>  $columns_mobile,
				'reverseDirection'        =>  wp_validate_boolean( $gs_teca_reverse_direction ),
			);

			$options = json_encode($options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
		}

		$data_options = [];

		
		if ( $view_type == 'filter' ) {
			$data_options = [
				'search_through_all_fields' => $gs_teca_search_all_fields,
				'enable_clear_filters' => $gs_teca_enable_clear_filters,
				'reset_filters_text' => $gs_teca_reset_filters_txt,
				'enable_multi_select' => $gs_teca_enable_multi_select,
				'multi_select_ellipsis' => $gs_teca_multi_select_ellipsis,
				'next_txt' => $gs_teca_next_txt,
				'prev_txt' => $gs_teca_prev_txt,
				'filter_type' => $gs_teca_filter_type,
				'posts_per_page' => $posts
			];
		}
		
		if ( $settings['gs_teca_pagination'] == 'on' ) {

			if ( $pagination_type === 'ajax-pagination' ) {
				$classes[] = 'gs-teca-has-ajax-pagination';
			} elseif ( $pagination_type === 'load-more-button' ) {
				$classes[] = 'gs-teca-has-load-more';
			} elseif ( $pagination_type === 'load-more-scroll' ) {
				$classes[] = 'gs-teca-has-load-more-scroll';
			}

			if ( 'ajax-pagination' === $pagination_type ) {
				$data_options['posts_per_page'] = (int) $item_per_page;
			} elseif ( 'load-more-button' === $pagination_type ) {
				$data_options['posts_per_page'] = (int) $initial_items;
                $data_options['posts_per_load'] = $load_per_click;
			} elseif ( 'load-more-scroll' === $pagination_type ) {
				$data_options['posts_per_page'] = (int) $initial_items;
                $data_options['posts_per_load'] = $per_load;
			}

		}

		if( $gs_teca_slider_navs && $view_type =='carousel' ) {
			$classes[] = "carousel--has-navs {$gs_teca_navs_style} {$gs_teca_ctrl_pos}";
		}

		if( $gs_teca_slider_dots && $view_type == 'carousel' ) {
			$classes[] = "carousel--has-dots {$gs_teca_dots_style}";
		}

		if($view_type == 'masonry') {
			$gs_row_classes[] = 'gs_masonry_wrapper';
		}

		if ( $view_type == 'filter') {
			$classes[] = 'gs-filter-by-'.$gs_filters_by;
		}

		if( $view_type == 'filter' ) {
			$classes[] = $gs_teca_filter_style;
			$gs_row_classes[] = 'gs-teca-filter-wrapper';
			$classes[] = teca_get_filter_position_class( $settings );
		}

		if ( teca_is_timeline_theme( $theme ) ) {
			$gs_row_classes[] = 'teca-timeline-row';
			$classes[]        = Timeline_Renderer::get_timeline_layout_class( $theme );
		}

		if ( teca_is_accordion_theme( $theme ) ) {
			$gs_row_classes[] = 'teca-accordion-row';
			$classes[]        = Accordion_Renderer::get_accordion_layout_class( $theme );
		}

        ob_start(); ?>

			<div id="<?php echo 'gs_teca_area_' . esc_attr( $id ); ?>" data-shortcode-id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?> <?php echo esc_attr($img_effect_class); ?>"<?php echo $theme_area_style ? ' style="' . esc_attr( $theme_area_style ) . '"' : ''; ?> data-carousel-config = '<?php echo esc_attr($options ?? ''); ?>' data-options='<?php echo json_encode($data_options) ?? ''; ?>'>

				<div class="gs_teca_area--inner">
					<div class="gs-containeer">

						<?php
						$orderby = teca_resolve_cat_order_by_for_context( $settings['cat_order_by'] ?? 'none' );
						$order   = $settings['cat_order'] ?? 'ASC';
						$all_categories = get_terms([
							'taxonomy'   => 'tribe_events_cat',
							'hide_empty' => true,
							'orderby'    => $orderby === 'term_order' ? 'term_order' : $orderby,
							'order'      => $order
						]);

						$all_tags = get_terms([
							'taxonomy'   => 'post_tag',
							'hide_empty' => true,
							'orderby'    => $orderby === 'term_order' ? 'term_order' : $orderby,
							'order'      => $order
						]);

						?>

						<?php if ( $view_type === 'filter' ) : ?>

							<ul class="<?php echo esc_attr( 'gsteca-filter-' . $gs_filters_by ); ?>">

								<li class="filter active" data-filter="*">
									<a href="javascript:void(0)">
										<span><?php esc_html_e( 'All', 'gs-teca' ); ?></span>
									</a>
								</li>

								<?php
								
								if ( $gs_filters_by === 'gs-teca-category' && ! empty( $all_categories ) ) :

									foreach ( $all_categories as $category ) :

										// Only parent terms
										if ( (int) $category->parent !== 0 ) {
											continue;
										}

										$subcategories = get_terms([
											'taxonomy'   => 'tribe_events_cat',
											'parent'     => $category->term_id,
											'hide_empty' => true,
										]);
										?>

										<?php if ( ! empty( $subcategories ) ) : ?>

											<li class="filter has-submenu" data-filter=".<?php echo esc_attr( $category->slug ); ?>">
												<a href="javascript:void(0)">
													<span><?php echo esc_html( $category->name ); ?></span>
													<i class="dropdown-icon"></i>
												</a>

												<ul class="submenu">
													<?php foreach ( $subcategories as $sub ) : ?>
														<li class="filter" data-filter=".<?php echo esc_attr( $sub->slug ); ?>">
															<a href="javascript:void(0)"><?php echo esc_html( $sub->name ); ?></a>
														</li>
													<?php endforeach; ?>
												</ul>
											</li>

										<?php else : ?>

											<li class="filter" data-filter=".<?php echo esc_attr( $category->slug ); ?>">
												<a href="javascript:void(0)">
													<span><?php echo esc_html( $category->name ); ?></span>
												</a>
											</li>

										<?php endif; ?>

									<?php endforeach; endif; ?>

								<?php
								if ( $gs_filters_by === 'gs-teca-tag' && ! empty( $all_tags ) ) :
									foreach ( $all_tags as $tag ) :
										?>
										<li class="filter" data-filter=".<?php echo esc_attr( $tag->slug ); ?>">
											<a href="javascript:void(0)">
												<span><?php echo esc_html( $tag->name ); ?></span>
											</a>
										</li>
									<?php endforeach; endif; ?>

							</ul>

							<?php include Template_Loader::locate_template( 'partials/gs-teca-layout-filters.php' ); ?>	

						<?php endif; ?>

						<?php if ( teca_should_render_filters_by_name_bar( $settings ) ) : ?>
							<?php include Template_Loader::locate_template( 'partials/gs-teca-filters-by-name.php' ); ?>
						<?php endif; ?>

						<?php if ( teca_should_render_search_by_bar( $settings ) ) : ?>
							<?php include Template_Loader::locate_template( 'partials/gs-teca-search-by.php' ); ?>
						<?php endif; ?>

						<div class="<?php echo esc_attr( implode( ' ', array_merge( $gs_row_classes, array( 'teca-events-list-target' ) ) ) ); ?>">
							<?php
							if ( $is_calendar_view ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo teca_render_calendar_layout( $view_type, $settings );
							} elseif ( $is_events_section ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo teca_render_events_section_layout( $settings, $ajax_datas );
							} elseif ( $is_venue_template ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo teca_render_venue_template_layout( $settings, $ajax_datas );
							} elseif ( $is_organizer_template ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo teca_render_organizer_template_layout( $settings, $ajax_datas );
							} else {
								$template = 'gs-teca-theme-01.php';

								switch ( $theme ) {
									case 'gs-teca-style-2': $template = 'gs-teca-theme-02.php'; break;
									case 'gs-teca-style-3': $template = 'gs-teca-theme-03.php'; break;
									case 'gs-teca-style-4': $template = 'gs-teca-theme-04.php'; break;
									case 'gs-teca-style-5': $template = 'gs-teca-theme-05.php'; break;
									case 'gs-teca-style-6': $template = 'gs-teca-theme-06.php'; break;
									case 'gs-teca-style-7': $template = 'gs-teca-theme-07.php'; break;
									case 'gs-teca-style-8': $template = 'gs-teca-theme-08.php'; break;
									case 'gs-teca-style-9': $template = 'gs-teca-theme-09.php'; break;
									case 'gs-teca-style-10': $template = 'gs-teca-theme-10.php'; break;
									case 'gs-teca-list-style-1': $template = 'gs-teca-list-theme-01.php'; break;
									case 'gs-teca-list-style-2': $template = 'gs-teca-list-theme-02.php'; break;
									case 'gs-teca-list-style-3': $template = 'gs-teca-list-theme-03.php'; break;
									case 'gs-teca-list-style-4': $template = 'gs-teca-list-theme-04.php'; break;
									case 'gs-teca-list-style-5': $template = 'gs-teca-list-theme-05.php'; break;
									case 'gs-teca-table-style-1': $template = 'gs-teca-table-theme-01.php'; break;
									case 'gs-teca-table-style-2': $template = 'gs-teca-table-theme-02.php'; break;
									case 'gs-teca-table-style-3': $template = 'gs-teca-table-theme-03.php'; break;
									case 'gs-teca-table-style-4': $template = 'gs-teca-table-theme-04.php'; break;
									case 'gs-teca-table-style-5': $template = 'gs-teca-table-theme-05.php'; break;
									case 'gs-teca-timeline-1': $template = 'timeline/timeline-1.php'; break;
									case 'gs-teca-timeline-2': $template = 'timeline/timeline-2.php'; break;
									case 'gs-teca-timeline-3': $template = 'timeline/timeline-3.php'; break;
									case 'gs-teca-accordion-1': $template = 'accordion/accordion-1.php'; break;
									case 'gs-teca-accordion-2': $template = 'accordion/accordion-2.php'; break;
									case 'gs-teca-accordion-3': $template = 'accordion/accordion-3.php'; break;
								}

								include Template_Loader::locate_template( $template );
							}

							wp_reset_postdata();
							?>
						</div>

						<?php if ( $shouldLoadPagination == true ) :
                                $gs_teca_found_events = $found_events;
                            ?>
							<?php include Template_Loader::locate_template( 'partials/gs-teca-layout-pagination.php') ?>
							
						<?php endif; ?>

					</div>

				</div>

			</div>

        <?php	

		teca_clear_shortcode_date_format_context();

        return ob_get_clean();

    }

	public function shortcode( $atts, $ajax_datas = array()) {
	
        if ( empty( $atts['id'] ) ) {
            return __( 'No shortcode ID found', 'gs-teca' );
        }

		
        $atts['id'] = sanitize_text_field( $atts['id'] );

        $is_preview = ! empty( $atts['preview'] );	
        $settings = $this->get_shortcode_settings( $atts['id'], $is_preview );
        if ( empty( $settings ) ) return '';

		$_settings = $settings;
	
		// By default force mode
		$force_asset_load = true;
	
		if ( ! $is_preview ) {
		
			// For Asset Generator
			$main_post_id = gsTecaAssetGenerator()->get_current_page_id();
	
			$asset_data = gsTecaAssetGenerator()->get_assets_data( $main_post_id );

			// Always collect deps for every shortcode on the page.
			gsTecaAssetGenerator()->generate( $main_post_id, $settings );
	
			if ( empty($asset_data) ) {
				// Saved assets not found — force load everything on first visit.
			} else {
				// Cached page load — enqueue this shortcode's view-specific assets now.
				gsTecaAssetGenerator()->enqueue_view_assets( $settings );
				$force_asset_load = false;
			}
	
		}
	
		$html = $this->render($settings, $ajax_datas);
	
		if ( $force_asset_load ) {
	
			gsTecaAssetGenerator()->force_enqueue_assets( $_settings );
			wp_add_inline_script( 'gs-teca-public', "jQuery(document).trigger( 'gsteca:scripts:reprocess' );jQuery(function() { jQuery(document).trigger( 'gsteca:scripts:reprocess' ) })" );

			// Shortcode Custom CSS
			$css = gsTecaAssetGenerator()->get_shortcode_custom_css( $settings );
			if ( !empty($css) ) $html .= sprintf( "<style>%s</style>" , wp_kses_post(minimize_css_simple($css) ));
			
			// Prefs Custom CSS
			$css = gsTecaAssetGenerator()->get_prefs_custom_css();
			if ( !empty($css) ) $html .= sprintf( "<style>%s</style>" , wp_kses_post(minimize_css_simple($css) ));
	
		}
	
		$settings = null; // Free up the memory
	
		return $html;
	}	

	public function get_shortcode_settings($id, $is_preview = false) {

		$default_settings = array_merge(
			['id' => $id, 'is_preview' => $is_preview],
			plugin()->builder->get_shortcode_default_settings()
		);

		if ( $is_preview ) {
			$settings = plugin()->builder->validate_shortcode_settings( get_transient($id) );
		} else {
			$shortcode = plugin()->builder->_get_shortcode($id);
			if ( empty($shortcode) ) return false;
			$settings = (array) $shortcode['shortcode_settings'];
		}

		// 🔥 ENSURE VISIBILITY ALWAYS EXISTS (THIS IS THE FIX)
		$visibility_defaults = [
			'post_thumbnail' => [
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => true,
				'mobile_landscape' => true,
			],
			'post_authors' => [
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => true,
				'mobile_landscape' => true,
			],
			'post_cat' => [
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => true,
				'mobile_landscape' => true,
			],
			'post_tags' => [
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => true,
				'mobile_landscape' => true,
			],
			'post_title' => [
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => true,
				'mobile_landscape' => true,
			],
			'post_details' => [
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => true,
				'mobile_landscape' => true,
			],
			'post_excerpt' => [
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => true,
				'mobile_landscape' => true,
			],
		];

		$settings['visibility'] = array_merge(
			$visibility_defaults,
			$settings['visibility'] ?? []
		);

		return teca_prepare_shortcode_settings_for_use(
			teca_prepare_typography_settings( shortcode_atts( $default_settings, teca_sanitize_calendar_settings( (array) $settings ) ) )
		);
	}


	
}
