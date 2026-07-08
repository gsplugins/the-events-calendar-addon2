<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$events           = $events ?? array();
$month_groups     = $month_groups ?? array();
$week_range       = $week_range ?? teca_get_week_range_for_date();
$category_options = $category_options ?? array();
$layout_id        = $layout_id ?? 'teca';
$first_event_id = $first_event_id ?? 0;
?>

<div
	class="teca-daily-layout-2"
	data-week-start="<?php echo esc_attr( $week_range['start'] ); ?>"
	data-week-end="<?php echo esc_attr( $week_range['end'] ); ?>"
	data-today="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"
	data-start-of-week="<?php echo esc_attr( (string) (int) get_option( 'start_of_week', 0 ) ); ?>"
>
	<div class="teca-daily-layout-2-background" aria-hidden="true">
		<span class="teca-daily-layout-2-bg-blob teca-daily-layout-2-bg-blob--blue"></span>
		<span class="teca-daily-layout-2-bg-blob teca-daily-layout-2-bg-blob--gold"></span>
		<span class="teca-daily-layout-2-bg-blob teca-daily-layout-2-bg-blob--amber"></span>
	</div>
	<div class="teca-daily-layout-2-shell">
		<aside class="teca-daily-layout-2-sidebar" aria-label="<?php esc_attr_e( 'Events', 'the-events-calendar-addon' ); ?>">
			<div class="teca-daily-layout-2-sidebar-label"><?php esc_html_e( 'Events', 'the-events-calendar-addon' ); ?></div>

			<?php if ( ! empty( $events ) ) : ?>
				<ul class="teca-daily-layout-2-event-list">
					<?php foreach ( $events as $event ) : ?>
						<?php
						$event_id   = (int) ( $event['event_id'] ?? 0 );
						$thumb_url  = $event_id ? get_the_post_thumbnail_url( $event_id, 'thumbnail' ) : '';
						$item_class = 'teca-daily-layout-2-event-item';

						if ( $event_id && $event_id === $first_event_id ) {
							$item_class .= ' teca-daily-layout-2-event-item-active';
						}
						?>
						<li class="<?php echo esc_attr( $item_class ); ?> teca-calendar-event teca-calendar-filterable-event teca-daily-layout-2-filterable teca-daily-layout-2-sidebar-item"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<button type="button" class="teca-daily-layout-2-event-trigger">
								<?php if ( $thumb_url ) : ?>
									<span class="teca-daily-layout-2-event-thumb">
										<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="lazy" />
									</span>
								<?php endif; ?>
								<span class="teca-daily-layout-2-event-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="teca-daily-layout-2-empty-sidebar"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></p>
			<?php endif; ?>
		</aside>

		<div class="teca-daily-layout-2-content">
			<div class="teca-daily-layout-2-glass-panel">
				<div class="teca-daily-layout-2-header">
					<div class="teca-daily-layout-2-search-row">
						<label class="teca-daily-layout-2-search" for="teca-daily-layout-2-search-<?php echo esc_attr( $layout_id ); ?>">
							<svg class="teca-daily-layout-2-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
								<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"></circle>
								<path d="M20 20l-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
							</svg>
							<input
								type="search"
								id="teca-daily-layout-2-search-<?php echo esc_attr( $layout_id ); ?>"
								class="teca-daily-layout-2-search-input"
								placeholder="<?php esc_attr_e( 'Search for events', 'the-events-calendar-addon' ); ?>"
								autocomplete="off"
							/>
						</label>
						<div class="teca-daily-layout-2-header-actions">
							<button type="button" class="teca-daily-layout-2-find-btn"><?php esc_html_e( 'Find Events', 'the-events-calendar-addon' ); ?></button>
							<?php if ( ! empty( $category_options ) ) : ?>
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo teca_render_calendar_event_type_filter(
									$events,
									$layout_id,
									array(
										'wrapper_class' => 'teca-daily-layout-2-type-filter',
										'select_class'  => 'teca-daily-layout-2-type-select',
									)
								);
								?>
							<?php endif; ?>
							<?php if ( ! empty( $events ) ) : ?>
								<div class="teca-daily-layout-2-filter">
									<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo teca_render_calendar_date_filter(
										$events,
										'daily',
										$layout_id,
										array(
											'date_label_format' => 'F j, Y',
										)
									);
									?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="teca-daily-layout-2-nav-row">
						<div class="teca-daily-layout-2-nav-controls">
							<button type="button" class="teca-daily-layout-2-nav-btn teca-daily-layout-2-nav-prev" aria-label="<?php esc_attr_e( 'Previous week', 'the-events-calendar-addon' ); ?>">&lsaquo;</button>
							<button type="button" class="teca-daily-layout-2-nav-btn teca-daily-layout-2-nav-next" aria-label="<?php esc_attr_e( 'Next week', 'the-events-calendar-addon' ); ?>">&rsaquo;</button>
							<button type="button" class="teca-daily-layout-2-today-btn"><?php esc_html_e( 'Today', 'the-events-calendar-addon' ); ?></button>
						</div>
						<h2 class="teca-daily-layout-2-title teca-daily-layout-2-date-label"><?php esc_html_e( 'All Events', 'the-events-calendar-addon' ); ?></h2>
					</div>
				</div>

				<?php if ( ! empty( $month_groups ) ) : ?>
					<?php foreach ( $month_groups as $month_group ) : ?>
						<section class="teca-daily-layout-2-group teca-calendar-group" data-calendar-group="month" data-calendar-group-value="<?php echo esc_attr( $month_group['month'] ); ?>" data-month="<?php echo esc_attr( $month_group['month'] ); ?>">
							<h3 class="teca-daily-layout-2-group-title"><?php echo esc_html( $month_group['label'] ); ?></h3>

							<?php foreach ( $month_group['days'] as $day_group ) : ?>
								<div class="teca-daily-layout-2-day-row teca-calendar-group" data-calendar-group="day" data-calendar-group-value="<?php echo esc_attr( $day_group['day_key'] ); ?>" data-day="<?php echo esc_attr( $day_group['day_key'] ); ?>">
									<div class="teca-daily-layout-2-day-label">
										<span class="teca-daily-layout-2-day-week"><?php echo esc_html( $day_group['day_label'] ); ?></span>
										<span class="teca-daily-layout-2-day-number"><?php echo esc_html( $day_group['day_number'] ); ?></span>
									</div>

									<div class="teca-daily-layout-2-grid">
										<?php foreach ( $day_group['events'] as $event ) : ?>
											<?php
											$event_id    = (int) ( $event['event_id'] ?? 0 );
											$image_url   = $event_id ? get_the_post_thumbnail_url( $event_id, 'medium_large' ) : '';
											$datetime    = teca_format_event_card_datetime_line( $event_id );
											$venue       = teca_get_event_venue_display( $event );
											$excerpt     = teca_get_event_excerpt_text( $event_id );
											$permalink   = $event_id ? get_the_permalink( $event_id ) : '';
											$is_recurring = teca_is_recurring_event( $event_id );
											$search_text = strtolower( trim( ( $event['event_name'] ?? '' ) . ' ' . $venue['name'] . ' ' . $venue['address'] ) );
											?>
											<article
												class="teca-daily-layout-2-card teca-calendar-event teca-calendar-filterable-event teca-daily-layout-2-filterable teca-daily-layout-2-main-item"
												data-day="<?php echo esc_attr( $day_group['day_key'] ); ?>"
												data-search="<?php echo esc_attr( $search_text ); ?>"
												<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												id="teca-daily-layout-2-card-<?php echo esc_attr( (string) $event_id ); ?>"
											>
												<div class="teca-daily-layout-2-card-body">
													<?php if ( $datetime ) : ?>
														<div class="teca-daily-layout-2-card-time">
															<span><?php echo esc_html( $datetime ); ?></span>
															<?php if ( $is_recurring ) : ?>
																<svg class="teca-daily-layout-2-card-recurring" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true" title="<?php esc_attr_e( 'Recurring event', 'the-events-calendar-addon' ); ?>">
																	<path d="M17 2l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
																	<path d="M3 11v-1a4 4 0 014-4h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></path>
																	<path d="M7 22l-4-4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
																	<path d="M21 13v1a4 4 0 01-4 4H3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></path>
																</svg>
															<?php endif; ?>
														</div>
													<?php endif; ?>

													<h4 class="teca-daily-layout-2-card-title">
														<?php if ( $permalink ) : ?>
															<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
														<?php else : ?>
															<?php echo esc_html( $event['event_name'] ?? '' ); ?>
														<?php endif; ?>
													</h4>

													<?php if ( $venue['name'] || $venue['address'] ) : ?>
														<div class="teca-daily-layout-2-card-venue">
															<?php if ( $venue['name'] ) : ?>
																<strong><?php echo esc_html( $venue['name'] ); ?></strong>
															<?php endif; ?>
															<?php if ( $venue['address'] ) : ?>
																<span><?php echo esc_html( $venue['address'] ); ?></span>
															<?php endif; ?>
														</div>
													<?php endif; ?>

													<?php if ( $excerpt ) : ?>
														<div class="teca-daily-layout-2-card-excerpt"><?php echo esc_html( $excerpt ); ?></div>
													<?php endif; ?>
												</div>

												<?php if ( $image_url ) : ?>
													<div class="teca-daily-layout-2-card-media">
														<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['event_name'] ?? '' ); ?>" loading="lazy" />
													</div>
												<?php endif; ?>
											</article>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</section>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="teca-daily-layout-2-empty-content"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
