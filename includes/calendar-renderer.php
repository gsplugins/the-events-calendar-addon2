<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Calendar_Renderer {

	/**
	 * Whether TEC view assets should load on this request.
	 *
	 * @var bool
	 */
	protected static $enqueue_assets = false;

	/**
	 * @return string[]
	 */
	public static function get_view_types() {
		return array(
			'calendar',
			'daily-calendar',
			'weekly-calendar',
			'monthly-calendar',
			'quarterly-calendar',
			'yearly-calendar',
		);
	}

	public static function is_calendar_view_type( $view_type ) {
		return in_array( (string) $view_type, self::get_view_types(), true );
	}

	public static function mark_enqueue_assets() {
		self::$enqueue_assets = true;

		if ( did_action( 'wp_enqueue_scripts' ) ) {
			self::enqueue_assets();
		}
	}

	public static function is_views_v2_enabled() {
		return function_exists( 'tribe_events_views_v2_is_enabled' ) && tribe_events_views_v2_is_enabled();
	}

	public static function is_calendar_embed_renderer_available() {
		return class_exists( '\TEC\Events\Calendar_Embeds\Render' );
	}

	public static function is_week_view_available() {
		if ( ! self::is_views_v2_enabled() || ! function_exists( 'tribe' ) ) {
			return false;
		}

		$manager = tribe( \Tribe\Events\Views\V2\Manager::class );
		$views   = (array) $manager->get_registered_views();

		return isset( $views['week'] );
	}

	/**
	 * Render the selected TECA calendar layout using native TEC views.
	 *
	 * @param string $view_type TECA calendar layout slug.
	 * @param array  $settings  Shortcode settings.
	 */
	public static function render_layout( $view_type, array $settings ) {
		if ( ! self::is_views_v2_enabled() ) {
			return self::render_notice(
				__( 'The Events Calendar Views V2 must be enabled to display calendar layouts.', 'the-events-calendar-addon2' ),
				$view_type,
				$settings
			);
		}

		$settings = teca_normalize_calendar_settings( $settings );

		if ( self::is_calendar_view_type( $view_type ) || 'calendar' === (string) $view_type ) {
			return self::render_unified_calendar( $settings );
		}

		return '';
	}

	/**
	 * Render the selected unified calendar layout (Calendar Layout 1–15).
	 */
	public static function render_unified_calendar( array $settings ) {
		$calendar_layout = teca_get_selected_calendar_layout( $settings );
		$render_method   = teca_get_calendar_layout_render_method( $calendar_layout );

		if ( ! method_exists( self::class, $render_method ) ) {
			return self::render_notice(
				__( 'The selected calendar layout could not be rendered.', 'the-events-calendar-addon2' ),
				'calendar',
				$settings
			);
		}

		teca_enable_shared_calendar_filter( true );
		$html = (string) self::{$render_method}( $settings );
		teca_enable_shared_calendar_filter( false );

		return self::wrap_output( $html, 'calendar', $settings );
	}

	public static function render_daily_calendar( array $settings ) {
		$sub_layout = teca_get_selected_calendar_sub_layout( $settings );

		if ( 'daily-layout-1' === $sub_layout ) {
			return self::wrap_output(
				self::render_daily_layout_1( $settings ),
				'daily-calendar',
				$settings
			);
		}

		if ( 'daily-layout-2' === $sub_layout ) {
			return self::wrap_output(
				self::render_daily_layout_2( $settings ),
				'daily-calendar',
				$settings
			);
		}

		if ( 'daily-layout-3' === $sub_layout ) {
			return self::wrap_output(
				self::render_daily_layout_3( $settings ),
				'daily-calendar',
				$settings
			);
		}

		if ( ! self::is_views_v2_enabled() ) {
			return self::render_notice(
				__( 'The Events Calendar Views V2 must be enabled to display calendar layouts.', 'the-events-calendar-addon2' ),
				'daily-calendar',
				$settings
			);
		}

		$html = self::render_native_view(
			'day',
			self::build_render_args(
				$settings,
				'day',
				wp_date( 'Y-m-d' ),
				'daily'
			)
		);

		return self::wrap_output( $html, 'daily-calendar', $settings );
	}

	public static function render_daily_layout_1( array $settings ) {
		$events_data      = teca_query_events( $settings );
		$events           = $events_data['events'] ?? array();
		$event_groups     = teca_group_events_by_month( $events );
		$schedule_title   = teca_get_shortcode_display_title( $settings );
		$category_options = teca_get_events_category_filter_slug_options( $events );
		$layout_id        = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/daily-layout-1.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_daily_layout_2( array $settings ) {
		$events_data      = teca_query_events( $settings );
		$events           = $events_data['events'] ?? array();
		$day_groups       = teca_group_events_by_day( $events );
		$month_groups     = teca_group_day_groups_by_month( $day_groups );
		$week_range       = teca_get_week_range_for_date();
		$category_options = teca_get_events_category_filter_slug_options( $events );
		$layout_id        = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$first_event_id   = ! empty( $events[0]['event_id'] ) ? (int) $events[0]['event_id'] : 0;

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/daily-layout-2.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_daily_layout_3( array $settings ) {
		$events_data      = teca_query_events( $settings );
		$events           = $events_data['events'] ?? array();
		$event_groups     = teca_group_events_by_month( $events );
		$schedule_title   = teca_get_shortcode_display_title( $settings );
		$category_options = teca_get_events_category_filter_options( $events );
		$hero_images      = teca_get_events_hero_images( $events );
		$layout_id        = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/daily-layout-3.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_weekly_calendar( array $settings ) {
		$sub_layout = teca_get_selected_calendar_sub_layout( $settings );

		if ( 'weekly-layout-1' === $sub_layout ) {
			return self::wrap_output(
				self::render_weekly_layout_1( $settings ),
				'weekly-calendar',
				$settings
			);
		}

		if ( 'weekly-layout-2' === $sub_layout ) {
			return self::wrap_output(
				self::render_weekly_layout_2( $settings ),
				'weekly-calendar',
				$settings
			);
		}

		if ( 'weekly-layout-3' === $sub_layout ) {
			return self::wrap_output(
				self::render_weekly_layout_3( $settings ),
				'weekly-calendar',
				$settings
			);
		}

		if ( ! self::is_week_view_available() ) {
			return self::wrap_output(
				'<div class="teca-calendar-notice teca-calendar-week-unavailable">' .
				esc_html__( 'Weekly calendar view requires The Events Calendar Pro.', 'the-events-calendar-addon2' ) .
				'</div>',
				'weekly-calendar',
				$settings
			);
		}

		$html = self::render_native_view(
			'week',
			self::build_render_args(
				$settings,
				'week',
				wp_date( 'Y-m-d' ),
				'weekly'
			)
		);

		return self::wrap_output( $html, 'weekly-calendar', $settings );
	}

	public static function render_weekly_layout_1( array $settings ) {
		$events_data      = teca_query_events( $settings );
		$events           = $events_data['events'] ?? array();
		$week_groups      = teca_group_events_by_week( $events );
		$schedule_title   = teca_get_shortcode_display_title( $settings );
		$category_options = teca_get_events_category_filter_options( $events );
		$layout_id        = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/weekly-layout-1.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_weekly_layout_2( array $settings ) {
		$events_data      = teca_query_events( $settings );
		$events           = $events_data['events'] ?? array();
		$week_groups      = teca_group_events_by_week( $events );
		$schedule_title   = teca_get_shortcode_display_title( $settings );
		$category_options = teca_get_events_category_filter_options( $events );
		$layout_id        = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/weekly-layout-2.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_weekly_layout_3( array $settings ) {
		$events_data      = teca_query_events( $settings );
		$events           = $events_data['events'] ?? array();
		$week_groups      = teca_group_events_by_week( $events );
		$schedule_title   = teca_get_shortcode_display_title( $settings );
		$category_options = teca_get_events_category_filter_options( $events );
		$layout_id        = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/weekly-layout-3.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_monthly_calendar( array $settings ) {
		$sub_layout = teca_get_selected_calendar_sub_layout( $settings );

		if ( 'monthly-layout-1' === $sub_layout ) {
			return self::wrap_output(
				self::render_monthly_layout_1( $settings ),
				'monthly-calendar',
				$settings
			);
		}

		if ( 'monthly-layout-2' === $sub_layout ) {
			return self::wrap_output(
				self::render_monthly_layout_2( $settings ),
				'monthly-calendar',
				$settings
			);
		}

		if ( 'monthly-layout-3' === $sub_layout ) {
			return self::wrap_output(
				self::render_monthly_layout_3( $settings ),
				'monthly-calendar',
				$settings
			);
		}

		$html = self::render_native_view(
			'month',
			self::build_render_args(
				$settings,
				'month',
				wp_date( 'Y-m' ),
				'monthly'
			)
		);

		return self::wrap_output( $html, 'monthly-calendar', $settings );
	}

	public static function render_monthly_layout_1( array $settings ) {
		$events_data      = teca_query_events( $settings );
		$events           = $events_data['events'] ?? array();
		$month_groups     = teca_group_events_by_month( $events );
		$schedule_title   = teca_get_shortcode_display_title( $settings );
		$category_options = teca_get_events_category_filter_options( $events );
		$layout_id        = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/monthly-layout-1.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_monthly_layout_2( array $settings ) {
		$events_data    = teca_query_events( $settings );
		$events         = $events_data['events'] ?? array();
		$month_groups   = teca_group_events_by_month( $events );
		$schedule_title = teca_get_shortcode_display_title( $settings );
		$layout_id      = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/monthly-layout-2.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_monthly_layout_3( array $settings ) {
		$events_data    = teca_query_events( $settings );
		$events         = $events_data['events'] ?? array();
		$month_groups   = teca_group_events_by_month( $events );
		$schedule_title = teca_get_shortcode_display_title( $settings );
		$layout_id      = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$max_cell_events = 3;

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/monthly-layout-3.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_quarterly_calendar( array $settings ) {
		$sub_layout = teca_get_selected_calendar_sub_layout( $settings );

		if ( 'quarterly-layout-1' === $sub_layout ) {
			return self::wrap_output(
				self::render_quarterly_layout_1( $settings ),
				'quarterly-calendar',
				$settings
			);
		}

		if ( 'quarterly-layout-2' === $sub_layout ) {
			return self::wrap_output(
				self::render_quarterly_layout_2( $settings ),
				'quarterly-calendar',
				$settings
			);
		}

		if ( 'quarterly-layout-3' === $sub_layout ) {
			return self::wrap_output(
				self::render_quarterly_layout_3( $settings ),
				'quarterly-calendar',
				$settings
			);
		}

		$year          = (int) wp_date( 'Y' );
		$current_month = (int) wp_date( 'n' );
		$quarter       = (int) ceil( $current_month / 3 );
		$start_month   = ( ( $quarter - 1 ) * 3 ) + 1;

		ob_start();
		printf(
			'<div class="teca-calendar-quarter" data-quarter="%s">',
			esc_attr( $year . '-Q' . $quarter )
		);
		printf(
			'<div class="teca-calendar-quarter-header">%s</div>',
			esc_html(
				sprintf(
					/* translators: 1: year, 2: quarter number */
					__( '%1$s - Quarter %2$s', 'the-events-calendar-addon2' ),
					$year,
					$quarter
				)
			)
		);
		echo '<div class="teca-calendar-quarter-months">';

		for ( $month = $start_month; $month < $start_month + 3; $month++ ) {
			$month_key = sprintf( '%d-%02d', $year, $month );
			$month_ts  = strtotime( $month_key . '-01' );

			printf(
				'<div class="teca-calendar-quarter-month" data-month="%s">',
				esc_attr( $month_key )
			);
			printf(
				'<div class="teca-calendar-month-header">%s</div>',
				esc_html( date_i18n( 'F Y', $month_ts ) )
			);
			echo '<div class="teca-calendar-quarter-events">';
			echo wp_kses_post(
				self::render_native_view(
					'month',
					self::build_render_args( $settings, 'month', $month_key, 'quarterly-' . $month_key )
				)
			);
			echo '</div></div>';
		}

		echo '</div></div>';

		return self::wrap_output( ob_get_clean(), 'quarterly-calendar', $settings );
	}

	public static function render_quarterly_layout_1( array $settings ) {
		$events_data    = teca_query_events( $settings );
		$events         = $events_data['events'] ?? array();
		$layout_data    = teca_build_quarterly_layout_1_data( $events );
		$schedule_title = teca_get_shortcode_display_title( $settings );
		$layout_id      = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$max_events     = 3;

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/quarterly-layout-1.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_quarterly_layout_2( array $settings ) {
		$events_data    = teca_query_events( $settings );
		$events         = $events_data['events'] ?? array();
		$layout_data    = teca_build_quarterly_layout_2_data( $events );
		$schedule_title = teca_get_shortcode_display_title( $settings );
		$layout_id      = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$max_cell_events = 2;

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/quarterly-layout-2.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_quarterly_layout_3( array $settings ) {
		$events_data     = teca_query_events( $settings );
		$events          = $events_data['events'] ?? array();
		$layout_data     = teca_build_quarterly_layout_3_data( $events );
		$schedule_title  = teca_get_shortcode_display_title( $settings );
		$layout_id       = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$max_cell_events = 2;

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/quarterly-layout-3.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_yearly_calendar( array $settings ) {
		$sub_layout = teca_get_selected_calendar_sub_layout( $settings );

		if ( 'yearly-layout-1' === $sub_layout ) {
			return self::render_yearly_layout_1( $settings );
		}

		if ( 'yearly-layout-2' === $sub_layout ) {
			return self::render_yearly_layout_2( $settings );
		}

		if ( 'yearly-layout-3' === $sub_layout ) {
			return self::render_yearly_layout_3( $settings );
		}

		$year = (int) wp_date( 'Y' );

		ob_start();
		printf(
			'<div class="teca-calendar-year" data-year="%d">',
			absint( $year )
		);
		printf(
			'<div class="teca-calendar-year-header">%s</div>',
			esc_html( (string) $year )
		);
		echo '<div class="teca-calendar-year-months">';

		for ( $month = 1; $month <= 12; $month++ ) {
			$month_key = sprintf( '%d-%02d', $year, $month );
			$month_ts  = strtotime( $month_key . '-01' );

			printf(
				'<div class="teca-calendar-year-month" data-month="%s">',
				esc_attr( $month_key )
			);
			printf(
				'<div class="teca-calendar-month-header">%s</div>',
				esc_html( date_i18n( 'F Y', $month_ts ) )
			);
			echo '<div class="teca-calendar-year-events">';
			echo wp_kses_post( self::render_native_view(
				'month',
				self::build_render_args( $settings, 'month', $month_key, 'yearly-' . $month_key )
			) );
			echo '</div></div>';
		}

		echo '</div></div>';

		return self::wrap_output( ob_get_clean(), 'yearly-calendar', $settings );
	}

	public static function render_yearly_layout_1( array $settings ) {
		$events_data    = teca_query_events( $settings );
		$events         = $events_data['events'] ?? array();
		$layout_data    = teca_build_yearly_layout_1_data( $events );
		$schedule_title = teca_get_shortcode_display_title( $settings );
		$layout_id      = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$max_events     = 5;

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/yearly-layout-1.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_yearly_layout_2( array $settings ) {
		$events_data    = teca_query_events( $settings );
		$events         = $events_data['events'] ?? array();
		$layout_data    = teca_build_yearly_layout_2_data( $events );
		$schedule_title = teca_get_shortcode_display_title( $settings );
		$layout_id      = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$max_events     = 3;

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/yearly-layout-2.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	public static function render_yearly_layout_3( array $settings ) {
		$events_data    = teca_query_events( $settings );
		$events         = $events_data['events'] ?? array();
		$layout_data    = teca_build_yearly_layout_3_data( $events );
		$schedule_title = teca_get_shortcode_display_title( $settings );
		$layout_id      = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$max_events     = 2;

		ob_start();

		$template = Template_Loader::locate_template( 'calendar/yearly-layout-3.php' );

		if ( ! is_wp_error( $template ) ) {
			include $template;
		}

		return (string) ob_get_clean();
	}

	/**
	 * Build arguments for TEC Calendar Embeds renderer.
	 */
	protected static function build_render_args( array $settings, $tec_view, $date, $suffix ) {
		$shortcode_id = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$args         = array(
			'id'                => 'teca_' . $shortcode_id . '_' . sanitize_key( $suffix ),
			'view'              => $tec_view,
			'date'              => $date,
			'tribe-bar'         => false,
			'hide-export'       => true,
			'filter-bar'        => false,
			'should_manage_url' => false,
		);

		if ( ! empty( $settings['gs_post_cats'] ) ) {
			$categories = $settings['gs_post_cats'];
			if ( is_string( $categories ) ) {
				$categories = array_filter( array_map( 'trim', explode( ',', $categories ) ) );
			}
			$categories = array_map( 'absint', (array) $categories );
			$args['category']                      = $categories;
			$args[ \Tribe__Events__Main::TAXONOMY ] = $categories;
		}

		if ( ! empty( $settings['gs_post_tags'] ) ) {
			$tags = $settings['gs_post_tags'];
			if ( is_string( $tags ) ) {
				$tags = array_filter( array_map( 'trim', explode( ',', $tags ) ) );
			}
			$args['tag'] = array_map( 'absint', (array) $tags );
		}

		return $args;
	}

	/**
	 * Render a native TEC view.
	 */
	protected static function render_native_view( $tec_view_slug, array $args ) {
		if ( self::is_calendar_embed_renderer_available() ) {
			$render = new \TEC\Events\Calendar_Embeds\Render();
			$render->setup( $args );

			return (string) $render->get_html();
		}

		return self::render_native_view_fallback( $tec_view_slug, $args );
	}

	/**
	 * Fallback for TEC versions without Calendar Embeds API.
	 */
	protected static function render_native_view_fallback( $tec_view_slug, array $args ) {
		if ( ! class_exists( '\Tribe\Events\Views\V2\View' ) || ! function_exists( 'tribe_context' ) ) {
			return '';
		}

		$context = tribe_context();

		$alter = array(
			'view'               => $tec_view_slug,
			'event_display_mode' => $tec_view_slug,
		);

		if ( ! empty( $args['date'] ) ) {
			$alter['event_date'] = $args['date'];
		}

		if ( ! empty( $args[ \Tribe__Events__Main::TAXONOMY ] ) ) {
			$alter[ \Tribe__Events__Main::TAXONOMY ] = $args[ \Tribe__Events__Main::TAXONOMY ];
		}

		if ( ! empty( $args['tag'] ) ) {
			$alter['post_tag'] = $args['tag'];
		}

		$context = $context->alter( $alter );
		$context->disable_read_from(
			array(
				\Tribe__Context::REQUEST_VAR,
				\Tribe__Context::QUERY_VAR,
				\Tribe__Context::WP_MATCHED_QUERY,
				\Tribe__Context::WP_PARSED,
			)
		);

		return (string) \Tribe\Events\Views\V2\View::make( $tec_view_slug, $context )->get_html();
	}

	protected static function wrap_output( $html, $view_type, array $settings ) {
		$settings        = teca_normalize_calendar_settings( $settings );
		$calendar_layout = teca_get_selected_calendar_layout( $settings );
		$filter_mode     = teca_get_selected_calendar_filter_mode( $settings );
		$legacy_slug     = teca_get_calendar_layout_legacy_slug( $calendar_layout );
		$period          = teca_get_calendar_layout_period( $calendar_layout );
		$layout_id       = sanitize_key( (string) ( $settings['id'] ?? 'teca' ) );
		$events_data     = teca_query_events( $settings );
		$events          = $events_data['events'] ?? array();
		$filter_bar      = teca_render_calendar_filter_bar( $events, $filter_mode, $layout_id, $settings );
		$layout_class    = 'teca-' . $legacy_slug;
		$classes         = array_filter(
			array(
				'teca-calendar-wrapper',
				'teca-calendar-layout',
				'teca-calendar-' . $period,
				$layout_class,
			)
		);

		return sprintf(
			'<div class="%1$s" data-calendar-layout="%2$s" data-filter-mode="%3$s" data-view="%4$s" data-layout="%5$s">%6$s<div class="teca-calendar-layout-inner">%7$s</div></div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $calendar_layout ),
			esc_attr( $filter_mode ),
			esc_attr( $period ),
			esc_attr( $legacy_slug ),
			$filter_bar,
			$html
		);
	}

	protected static function render_notice( $message, $view_type, array $settings = array() ) {
		return self::wrap_output(
			'<div class="teca-calendar-notice">' . esc_html( $message ) . '</div>',
			$view_type,
			$settings
		);
	}

	public static function enqueue_assets() {
		if ( ! self::$enqueue_assets || ! self::is_views_v2_enabled() ) {
			return;
		}

		add_filter( 'tribe_events_views_v2_assets_should_enqueue_frontend', '__return_true' );

		if ( function_exists( 'tribe_asset_enqueue_group' ) && class_exists( '\Tribe\Events\Views\V2\Assets' ) ) {
			tribe_asset_enqueue_group( \Tribe\Events\Views\V2\Assets::$group_key );
		}

		/**
		 * Fires when TECA requests native calendar assets.
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- The Events Calendar hook name is required for native calendar asset integration.
		do_action( 'tec_events_calendar_embeds_enqueue_scripts' );
	}
}

add_action( 'wp_enqueue_scripts', array( Calendar_Renderer::class, 'enqueue_assets' ), 25 );
