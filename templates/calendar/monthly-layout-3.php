<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$events          = $events ?? array();
$month_groups    = $month_groups ?? teca_group_events_by_month( $events );
$schedule_title  = $schedule_title ?? __( 'Events Calendar', 'the-events-calendar-addon' );
$layout_id       = $layout_id ?? 'teca';
$max_cell_events = isset( $max_cell_events ) ? (int) $max_cell_events : 3;
$weekday_labels  = teca_get_calendar_weekday_labels( 'abbrev' );
?>

<div class="teca-monthly-layout-3 teca-monthly-layout-3-calendar-board" data-view="monthly" data-layout="monthly-layout-3">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo teca_render_calendar_date_filter( $events, 'monthly', $layout_id );
	?>
	<?php if ( empty( $month_groups ) ) : ?>
		<div class="teca-calendar-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></div>
	<?php else : ?>
		<?php foreach ( $month_groups as $month_group ) : ?>
			<?php
			$month_parts    = teca_get_month_infographic_label_parts( $month_group['month'] );
			$month_events   = teca_sort_events_by_start_date( $month_group['events'] );
			$calendar_data  = teca_build_monthly_calendar_cells( $month_group['month'], $month_events );
			$hero_images    = teca_get_events_hero_images( $month_events, 'large' );
			$feature_events = array_slice( $month_events, 0, 6 );
			$event_index    = 0;
			?>
			<section class="teca-monthly-layout-3-section" data-month="<?php echo esc_attr( $month_group['month'] ); ?>">
				<header class="teca-monthly-layout-3-header">
					<h2 class="teca-monthly-layout-3-title"><?php echo esc_html( $schedule_title ); ?></h2>
					<?php if ( ! empty( $month_parts['label'] ) ) : ?>
						<p class="teca-monthly-layout-3-subtitle"><?php echo esc_html( $month_parts['label'] ); ?></p>
					<?php endif; ?>
					<div class="teca-monthly-layout-3-header-meta">
						<?php if ( ! empty( $month_parts['month_name'] ) ) : ?>
							<span class="teca-monthly-layout-3-month"><?php echo esc_html( $month_parts['month_name'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $month_parts['year'] ) ) : ?>
							<span class="teca-monthly-layout-3-year"><?php echo esc_html( $month_parts['year'] ); ?></span>
						<?php endif; ?>
					</div>
				</header>

				<div class="teca-monthly-layout-3-body">
					<div class="teca-monthly-layout-3-left">
						<div class="teca-monthly-layout-3-feature">
							<div class="teca-monthly-layout-3-feature-media"<?php echo ! empty( $hero_images ) ? ' data-interval="5000"' : ''; ?>>
								<?php if ( ! empty( $hero_images ) ) : ?>
									<?php foreach ( $hero_images as $index => $hero_image ) : ?>
										<div
											class="teca-monthly-layout-3-feature-image<?php echo 0 === $index ? ' is-active' : ''; ?>"
											style="<?php echo esc_attr( 'background-image:url(' . $hero_image['url'] . ');' ); ?>"
											role="img"
											aria-label="<?php echo esc_attr( $hero_image['alt'] ?? '' ); ?>"
										></div>
									<?php endforeach; ?>
								<?php endif; ?>
								<div class="teca-monthly-layout-3-feature-overlay" aria-hidden="true"></div>
							</div>

							<div class="teca-monthly-layout-3-feature-content">
								<?php if ( ! empty( $month_parts['label'] ) ) : ?>
									<h3 class="teca-monthly-layout-3-feature-title"><?php echo esc_html( $month_parts['label'] ); ?></h3>
								<?php endif; ?>

								<?php if ( ! empty( $feature_events ) ) : ?>
									<ul class="teca-monthly-layout-3-feature-event-list" aria-label="<?php esc_attr_e( 'Events', 'the-events-calendar-addon' ); ?>">
										<?php foreach ( $feature_events as $event ) : ?>
											<?php
											$event_id     = (int) ( $event['event_id'] ?? 0 );
											$start_date   = $event['dates']['start'] ?? '';
											$date_label   = $event_id
												? teca_format_event_start_date_text( $event_id )
												: ( $start_date ? teca_format_layout_date_string( $start_date ) : '' );
											$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
											$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
											?>
											<li class="teca-monthly-layout-3-feature-event-item teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
												<?php if ( $date_label ) : ?>
													<div class="teca-monthly-layout-3-feature-event-date"><?php echo esc_html( strtoupper( $date_label ) ); ?></div>
												<?php endif; ?>
												<div class="teca-monthly-layout-3-feature-event-title">
													<?php if ( $permalink ) : ?>
														<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
													<?php else : ?>
														<?php echo esc_html( $event['event_name'] ?? '' ); ?>
													<?php endif; ?>
												</div>
												<?php if ( $time_display ) : ?>
													<div class="teca-monthly-layout-3-feature-meta"><?php echo esc_html( $time_display ); ?></div>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="teca-monthly-layout-3-right">
						<div class="teca-monthly-layout-3-calendar">
							<?php if ( ! empty( $calendar_data['month_label'] ) ) : ?>
								<div class="teca-monthly-layout-3-calendar-header">
									<span class="teca-monthly-layout-3-calendar-month-label"><?php echo esc_html( $calendar_data['month_label'] ); ?></span>
								</div>
							<?php endif; ?>

							<div class="teca-monthly-layout-3-weekdays" aria-hidden="true">
								<?php foreach ( $weekday_labels as $weekday_label ) : ?>
									<div class="teca-monthly-layout-3-weekday"><?php echo esc_html( strtoupper( $weekday_label ) ); ?></div>
								<?php endforeach; ?>
							</div>

							<div class="teca-monthly-layout-3-grid">
								<?php foreach ( $calendar_data['weeks'] as $week ) : ?>
									<?php foreach ( $week as $cell ) : ?>
										<?php if ( 'empty' === ( $cell['type'] ?? '' ) ) : ?>
											<div class="teca-monthly-layout-3-cell teca-monthly-layout-3-cell-empty" aria-hidden="true"></div>
										<?php else : ?>
											<?php
											$day_events   = $cell['events'] ?? array();
											$visible      = array_slice( $day_events, 0, $max_cell_events );
											$hidden_count = max( 0, count( $day_events ) - count( $visible ) );
											?>
											<div
												class="teca-monthly-layout-3-cell"
												data-date="<?php echo esc_attr( $cell['date'] ); ?>"
												data-day="<?php echo esc_attr( (string) $cell['day'] ); ?>"
												data-month="<?php echo esc_attr( (string) $cell['month'] ); ?>"
												data-year="<?php echo esc_attr( (string) $cell['year'] ); ?>"
											>
												<div class="teca-monthly-layout-3-cell-header">
													<span class="teca-monthly-layout-3-day-number"><?php echo esc_html( (string) $cell['day'] ); ?></span>
												</div>

												<div class="teca-monthly-layout-3-cell-events">
													<?php foreach ( $visible as $event ) : ?>
														<?php
														$event_id     = (int) ( $event['event_id'] ?? 0 );
														$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
																												$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
														$venue        = teca_get_event_venue_display( $event );
														$start_date   = $event['dates']['start'] ?? '';
														$end_date     = $event['dates']['end'] ?? '';
														$accent_slug  = teca_get_monthly_layout_3_accent_slug( $event_index );
														$event_index++;
														$event_class  = 'teca-monthly-layout-3-event teca-monthly-layout-3-event-accent-' . $accent_slug;
														?>
														<div
															class="<?php echo esc_attr( $event_class ); ?> teca-calendar-filterable-event"
															<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
														>
															<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-monthly-layout-3-event-categories', 'item_class' => 'teca-event-category teca-monthly-layout-3-event-category', 'transform' => 'uppercase' ) ); ?>

															<div class="teca-monthly-layout-3-event-title">
																<?php if ( $permalink ) : ?>
																	<a class="teca-monthly-layout-3-event-link" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
																<?php else : ?>
																	<?php echo esc_html( $event['event_name'] ?? '' ); ?>
																<?php endif; ?>
															</div>

															<?php if ( $time_display ) : ?>
																<div class="teca-monthly-layout-3-event-time"><?php echo esc_html( $time_display ); ?></div>
															<?php endif; ?>

															<?php if ( ! empty( $venue['name'] ) ) : ?>
																<div class="teca-monthly-layout-3-event-venue"><?php echo esc_html( $venue['name'] ); ?></div>
															<?php endif; ?>

															<?php if ( ! empty( teca_get_event_category_names( $event ) ) ) : ?>
																<span class="teca-monthly-layout-3-event-badge" aria-hidden="true"></span>
															<?php endif; ?>
														</div>
													<?php endforeach; ?>

													<?php if ( $hidden_count > 0 ) : ?>
														<div class="teca-monthly-layout-3-more-events">
															<?php
															printf(
																/* translators: %d: additional event count */
																esc_html__( '+%d more', 'the-events-calendar-addon' ),
																(int) $hidden_count
															);
															?>
														</div>
													<?php endif; ?>
												</div>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
