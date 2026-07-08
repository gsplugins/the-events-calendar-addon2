<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$events           = $events ?? array();
$week_groups      = $week_groups ?? teca_group_events_by_week( $events );
$schedule_title   = $schedule_title ?? __( 'Key Events', 'the-events-calendar-addon' );
$category_options = $category_options ?? array();
$layout_id        = $layout_id ?? 'teca';
$first_event_id   = ! empty( $events[0]['event_id'] ) ? (int) $events[0]['event_id'] : 0;
?>

<div class="teca-weekly-layout-3">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo teca_render_calendar_date_filter( $events, 'weekly', $layout_id );
	?>
	<div class="teca-weekly-layout-3-shell">
		<aside class="teca-weekly-layout-3-sidebar" aria-label="<?php esc_attr_e( 'Events', 'the-events-calendar-addon' ); ?>">
			<div class="teca-weekly-layout-3-sidebar-label"><?php esc_html_e( 'Events', 'the-events-calendar-addon' ); ?></div>

			<?php if ( ! empty( $events ) ) : ?>
				<ul class="teca-weekly-layout-3-event-list">
					<?php foreach ( $events as $event ) : ?>
						<?php
						$event_id   = (int) ( $event['event_id'] ?? 0 );
						$thumb_url  = $event_id ? get_the_post_thumbnail_url( $event_id, 'thumbnail' ) : '';
						$item_class = 'teca-weekly-layout-3-event-item';

						if ( $event_id && $event_id === $first_event_id ) {
							$item_class .= ' teca-weekly-layout-3-event-item-active';
						}
						?>
						<li class="<?php echo esc_attr( $item_class ); ?> teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<button type="button" class="teca-weekly-layout-3-event-trigger">
								<?php if ( $thumb_url ) : ?>
									<span class="teca-weekly-layout-3-event-thumb">
										<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="lazy" />
									</span>
								<?php endif; ?>
								<span class="teca-weekly-layout-3-event-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="teca-weekly-layout-3-empty-sidebar"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></p>
			<?php endif; ?>
		</aside>

		<div class="teca-weekly-layout-3-content">
			<div class="teca-weekly-layout-3-header">
				<div class="teca-weekly-layout-3-heading">
					<h2 class="teca-weekly-layout-3-title"><?php echo esc_html( strtoupper( $schedule_title ) ); ?></h2>
					<p class="teca-weekly-layout-3-subtitle"><?php esc_html_e( 'For The Week', 'the-events-calendar-addon' ); ?></p>
				</div>

				<?php if ( ! empty( $category_options ) ) : ?>
					<div class="teca-weekly-layout-3-toolbar">
						<label class="teca-weekly-layout-3-filter-label" for="teca-weekly-layout-3-type-<?php echo esc_attr( $layout_id ); ?>">
							<?php esc_html_e( 'Event Type', 'the-events-calendar-addon' ); ?>
						</label>
						<select
							id="teca-weekly-layout-3-type-<?php echo esc_attr( $layout_id ); ?>"
							class="teca-weekly-layout-3-type-select"
						>
							<option value="all"><?php esc_html_e( 'All Types', 'the-events-calendar-addon' ); ?></option>
							<?php foreach ( $category_options as $term_id => $label ) : ?>
								<option value="<?php echo esc_attr( (string) $term_id ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $week_groups ) ) : ?>
				<?php foreach ( $week_groups as $week_group ) : ?>
					<?php $day_groups = teca_group_events_by_day( $week_group['events'] ); ?>
					<section class="teca-weekly-layout-3-week" data-week-start="<?php echo esc_attr( $week_group['start'] ); ?>">
						<div class="teca-weekly-layout-3-week-header">
							<h3 class="teca-weekly-layout-3-week-range"><?php echo esc_html( $week_group['label'] ); ?></h3>
						</div>

						<div class="teca-weekly-layout-3-table-head" aria-hidden="true">
							<span class="teca-weekly-layout-3-table-head-date"><?php esc_html_e( 'Date', 'the-events-calendar-addon' ); ?></span>
							<span class="teca-weekly-layout-3-table-head-time"><?php esc_html_e( 'Time', 'the-events-calendar-addon' ); ?></span>
							<span class="teca-weekly-layout-3-table-head-event"><?php esc_html_e( 'Event', 'the-events-calendar-addon' ); ?></span>
							<span class="teca-weekly-layout-3-table-head-details"><?php esc_html_e( 'Details', 'the-events-calendar-addon' ); ?></span>
						</div>

						<div class="teca-weekly-layout-3-week-events">
							<?php foreach ( $day_groups as $day_group ) : ?>
								<?php
								$day_ts     = strtotime( $day_group['day_key'] );
								$month_name = date_i18n( 'F', $day_ts );
								?>
								<div class="teca-weekly-layout-3-group" data-day="<?php echo esc_attr( $day_group['day_key'] ); ?>">
									<div class="teca-weekly-layout-3-card-date-badge">
										<span class="teca-weekly-layout-3-card-date-day"><?php echo esc_html( $day_group['day_number'] ); ?></span>
										<span class="teca-weekly-layout-3-card-date-month"><?php echo esc_html( $month_name ); ?></span>
									</div>

									<div class="teca-weekly-layout-3-grid">
										<?php foreach ( $day_group['events'] as $event ) : ?>
											<?php
											$event_id     = (int) ( $event['event_id'] ?? 0 );
											$image_url    = $event_id ? get_the_post_thumbnail_url( $event_id, 'thumbnail' ) : '';
																						$category_ids = teca_get_event_category_ids( $event );
											$time_display = $event_id ? teca_format_event_table_time( $event_id ) : '';
											$details_line = teca_get_event_details_line( $event, $event_id );
											$venue        = teca_get_event_venue_display( $event );
											$organizer    = teca_get_event_organizer_name( $event );
											$cost_display = $event_id ? teca_get_event_cost_display( $event_id ) : '';
											$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
											$cta_url      = $event_id ? teca_get_event_cta_url( $event_id ) : '';
											$button_url   = $cta_url ? $cta_url : $permalink;
											?>
											<article
												class="teca-weekly-layout-3-card teca-calendar-filterable-event"
												data-categories="<?php echo esc_attr( implode( ',', $category_ids ) ); ?>"
												<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												id="teca-weekly-layout-3-card-<?php echo esc_attr( (string) $event_id ); ?>"
											>
												<?php if ( $image_url ) : ?>
													<div class="teca-weekly-layout-3-card-media">
														<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" />
													</div>
												<?php endif; ?>

												<div class="teca-weekly-layout-3-card-time">
													<?php echo esc_html( $time_display ); ?>
												</div>

												<div class="teca-weekly-layout-3-card-body">
													<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-weekly-layout-3-card-categories', 'item_class' => 'teca-event-category teca-weekly-layout-3-card-category' ) ); ?>

													<h4 class="teca-weekly-layout-3-card-title">
														<?php if ( $permalink ) : ?>
															<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
														<?php else : ?>
															<?php echo esc_html( $event['event_name'] ?? '' ); ?>
														<?php endif; ?>
													</h4>

													<?php if ( ! empty( $venue['name'] ) && ! $details_line ) : ?>
														<div class="teca-weekly-layout-3-card-venue"><?php echo esc_html( $venue['name'] ); ?></div>
													<?php endif; ?>

													<?php if ( $organizer && ! $details_line ) : ?>
														<div class="teca-weekly-layout-3-card-organizer"><?php echo esc_html( $organizer ); ?></div>
													<?php endif; ?>

													<?php if ( $cost_display && ! $details_line ) : ?>
														<div class="teca-weekly-layout-3-card-cost"><?php echo esc_html( $cost_display ); ?></div>
													<?php endif; ?>

													<?php if ( $button_url ) : ?>
														<a
															class="teca-weekly-layout-3-card-button"
															href="<?php echo esc_url( $button_url ); ?>"
															<?php echo $cta_url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
														>
															<?php echo esc_html( $cta_url ? __( 'Get Tickets', 'the-events-calendar-addon' ) : __( 'View Event', 'the-events-calendar-addon' ) ); ?>
														</a>
													<?php endif; ?>
												</div>

												<?php if ( $details_line ) : ?>
													<div class="teca-weekly-layout-3-card-details"><?php echo esc_html( $details_line ); ?></div>
												<?php endif; ?>
											</article>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="teca-weekly-layout-3-empty-content"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></p>
			<?php endif; ?>

			<div class="teca-weekly-layout-3-decor" aria-hidden="true"></div>
		</div>
	</div>
</div>
