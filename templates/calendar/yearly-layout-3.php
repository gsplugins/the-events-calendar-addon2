<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$events         = $events ?? array();
$layout_data    = $layout_data ?? teca_build_yearly_layout_3_data( $events );
$schedule_title = $schedule_title ?? __( 'Events Schedule', 'the-events-calendar-addon' );
$layout_id      = $layout_id ?? 'teca';
$max_events     = isset( $max_events ) ? (int) $max_events : 2;
$years          = $layout_data['years'] ?? array();
$years_label    = $layout_data['years_label'] ?? (string) wp_date( 'Y' );
$month_themes   = array(
	1  => 'sapphire',
	2  => 'ruby',
	3  => 'violet',
	4  => 'aqua',
	5  => 'amber',
	6  => 'forest',
	7  => 'magenta',
	8  => 'olive',
	9  => 'navy',
	10 => 'teal',
	11 => 'sunset',
	12 => 'crimson',
);
?>

<div class="teca-calendar-layout teca-calendar-yearly teca-yearly-layout-3 teca-yearly-layout-3-mini-calendars" data-view="yearly" data-layout="yearly-layout-3">
	<header class="teca-yearly-layout-3-header">
		<div class="teca-yearly-layout-3-header-main">
			<?php if ( $schedule_title ) : ?>
				<h2 class="teca-yearly-layout-3-title"><?php echo esc_html( $schedule_title ); ?></h2>
			<?php endif; ?>
			<p class="teca-yearly-layout-3-subtitle">
				<span class="teca-yearly-layout-3-years-label"><?php echo esc_html( $years_label ); ?></span>
			</p>
		</div>

		<div class="teca-yearly-layout-3-toolbar">
			<?php if ( ! empty( $events ) ) : ?>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo teca_render_calendar_date_filter( $events, 'yearly', $layout_id );
				?>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( empty( $events ) ) : ?>
		<div class="teca-calendar-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></div>
	<?php else : ?>
		<?php foreach ( $years as $year_group ) : ?>
			<?php $board_year = (int) ( $year_group['year'] ?? 0 ); ?>
			<section class="teca-yearly-layout-3-year teca-yearly-year-section" data-year="<?php echo esc_attr( (string) $board_year ); ?>">
				<div class="teca-yearly-layout-3-year-header">
					<h3 class="teca-yearly-layout-3-year-label"><?php echo esc_html( (string) $board_year ); ?></h3>
				</div>

				<div class="teca-yearly-layout-3-body">
					<div class="teca-yearly-layout-3-grid">
						<?php foreach ( $year_group['months'] as $month_group ) : ?>
							<?php
							$month_number   = (int) ( $month_group['month'] ?? 0 );
							$month_events   = $month_group['events'] ?? array();
							$visible_events = array_slice( $month_events, 0, $max_events );
							$hidden_count   = max( 0, count( $month_events ) - count( $visible_events ) );
							$month_theme    = $month_themes[ $month_number ] ?? 'sapphire';
							?>
							<article
								class="teca-yearly-layout-3-month-card teca-yearly-layout-3-month-theme-<?php echo esc_attr( $month_theme ); ?>"
								data-month="<?php echo esc_attr( sprintf( '%02d', $month_number ) ); ?>"
								data-year="<?php echo esc_attr( (string) $board_year ); ?>"
							>
								<div class="teca-yearly-layout-3-month-rings" aria-hidden="true">
									<span></span><span></span><span></span><span></span>
								</div>

								<div class="teca-yearly-layout-3-month-card-inner">
									<div class="teca-yearly-layout-3-month-top">
										<h4 class="teca-yearly-layout-3-month-name"><?php echo esc_html( strtoupper( $month_group['month_name'] ?? '' ) ); ?></h4>
									</div>

									<div class="teca-yearly-layout-3-month-content">
										<?php if ( ! empty( $visible_events ) ) : ?>
											<div class="teca-yearly-layout-3-month-events">
												<?php foreach ( $visible_events as $index => $event ) : ?>
													<?php
													$event_id     = (int) ( $event['event_id'] ?? 0 );
													$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
													$start_date   = $event['dates']['start'] ?? '';
													$day_number   = $start_date ? wp_date( 'j', strtotime( $start_date ) ) : '';
													$date_display = $start_date ? wp_date( 'M j', strtotime( $start_date ) ) : '';
													$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
																										$venue        = teca_get_event_venue_display( $event );
													$cost_display = $event_id ? teca_get_event_cost_display( $event_id ) : '';
													$is_primary   = 0 === $index;
													?>
													<div class="teca-yearly-layout-3-event teca-calendar-filterable-event<?php echo $is_primary ? ' is-primary' : ''; ?>"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
														<div class="teca-yearly-layout-3-event-title">
															<?php if ( $permalink ) : ?>
																<a class="teca-yearly-layout-3-event-link" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
															<?php else : ?>
																<?php echo esc_html( $event['event_name'] ?? '' ); ?>
															<?php endif; ?>
														</div>

														<?php if ( $is_primary && $day_number ) : ?>
															<div class="teca-yearly-layout-3-month-date-badge" aria-hidden="true">
																<span class="teca-yearly-layout-3-event-day"><?php echo esc_html( (string) $day_number ); ?></span>
															</div>
														<?php elseif ( ! $is_primary && $date_display ) : ?>
															<div class="teca-yearly-layout-3-event-date"><?php echo esc_html( $date_display ); ?></div>
														<?php endif; ?>

														<?php if ( $time_display ) : ?>
															<div class="teca-yearly-layout-3-event-time"><?php echo esc_html( $time_display ); ?></div>
														<?php endif; ?>

														<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-yearly-layout-3-event-categories', 'item_class' => 'teca-event-category teca-yearly-layout-3-event-category' ) ); ?>

														<?php if ( ! empty( $venue['name'] ) ) : ?>
															<div class="teca-yearly-layout-3-event-venue"><?php echo esc_html( $venue['name'] ); ?></div>
														<?php endif; ?>

														<?php if ( $cost_display ) : ?>
															<div class="teca-yearly-layout-3-event-cost"><?php echo esc_html( $cost_display ); ?></div>
														<?php endif; ?>

													</div>
												<?php endforeach; ?>

												<?php if ( $hidden_count > 0 ) : ?>
													<div class="teca-yearly-layout-3-more-events">
														<?php
														printf(
															/* translators: %d: additional event count */
															esc_html( _n( '+%d more event', '+%d more events', $hidden_count, 'the-events-calendar-addon' ) ),
															(int) $hidden_count
														);
														?>
													</div>
												<?php endif; ?>
											</div>
										<?php else : ?>
											<p class="teca-yearly-layout-3-month-empty"><?php esc_html_e( 'No events', 'the-events-calendar-addon' ); ?></p>
										<?php endif; ?>
									</div>

									<div class="teca-yearly-layout-3-month-footer" aria-hidden="true"></div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
