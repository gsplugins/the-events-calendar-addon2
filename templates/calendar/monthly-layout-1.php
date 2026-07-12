<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$events           = $events ?? array();
$month_groups     = $month_groups ?? teca_group_events_by_month( $events );
$schedule_title   = $schedule_title ?? __( 'Events Calendar', 'the-events-calendar-addon2' );
$category_options = $category_options ?? array();
$layout_id        = $layout_id ?? 'teca';
$accent_slugs     = array( 'gold', 'red', 'green', 'black' );
?>

<div class="teca-monthly-layout-1 teca-monthly-layout-1-infographic" data-view="monthly" data-layout="monthly-layout-1">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo teca_render_calendar_date_filter( $events, 'monthly', $layout_id );
	?>
	<?php if ( empty( $month_groups ) ) : ?>
		<div class="teca-calendar-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon2' ); ?></div>
	<?php else : ?>
		<?php foreach ( $month_groups as $month_group ) : ?>
			<?php
			$month_parts   = teca_get_month_infographic_label_parts( $month_group['month'] );
			$month_events  = teca_sort_events_by_start_date( $month_group['events'] );
			$highlight_events = array_slice( $month_events, 0, 4 );
			?>
			<section class="teca-monthly-layout-1-month" data-month="<?php echo esc_attr( $month_group['month'] ); ?>">
				<div class="teca-monthly-layout-1-infographic-shell">
					<div class="teca-monthly-layout-1-decor teca-monthly-layout-1-decor-top" aria-hidden="true"></div>

					<header class="teca-monthly-layout-1-header">
						<h2 class="teca-monthly-layout-1-title"><?php echo esc_html( strtoupper( $schedule_title ) ); ?></h2>
						<p class="teca-monthly-layout-1-subtitle">
							<?php if ( ! empty( $month_parts['month_name'] ) ) : ?>
								<span class="teca-monthly-layout-1-month"><?php echo esc_html( $month_parts['month_name'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $month_parts['year'] ) ) : ?>
								<span class="teca-monthly-layout-1-year"><?php echo esc_html( $month_parts['year'] ); ?></span>
							<?php endif; ?>
						</p>
					</header>

					<div class="teca-monthly-layout-1-body">
						<?php if ( ! empty( $category_options ) ) : ?>
							<div class="teca-monthly-layout-1-toolbar">
								<label class="teca-monthly-layout-1-filter-label" for="teca-monthly-layout-1-type-<?php echo esc_attr( $layout_id ); ?>-<?php echo esc_attr( $month_group['month'] ); ?>">
									<?php esc_html_e( 'Event Type', 'the-events-calendar-addon2' ); ?>
								</label>
								<select
									id="teca-monthly-layout-1-type-<?php echo esc_attr( $layout_id ); ?>-<?php echo esc_attr( $month_group['month'] ); ?>"
									class="teca-monthly-layout-1-type-select"
								>
									<option value="all"><?php esc_html_e( 'All Types', 'the-events-calendar-addon2' ); ?></option>
									<?php foreach ( $category_options as $term_id => $label ) : ?>
										<option value="<?php echo esc_attr( (string) $term_id ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $highlight_events ) ) : ?>
							<div class="teca-monthly-layout-1-highlights">
								<?php foreach ( $highlight_events as $index => $event ) : ?>
									<?php
									$event_id   = (int) ( $event['event_id'] ?? 0 );
																		$start_date = $event['dates']['start'] ?? '';
									$date_label = $event_id
										? teca_format_event_start_date_text( $event_id )
										: ( $start_date ? teca_format_layout_date_string( $start_date ) : '' );
									$accent_slug = $accent_slugs[ $index % count( $accent_slugs ) ];
									?>
									<div class="teca-monthly-layout-1-highlight-item teca-monthly-layout-1-highlight-accent-<?php echo esc_attr( $accent_slug ); ?> teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php if ( $date_label ) : ?>
											<div class="teca-monthly-layout-1-highlight-date"><?php echo esc_html( strtoupper( $date_label ) ); ?></div>
										<?php endif; ?>
										<div class="teca-monthly-layout-1-highlight-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></div>
										<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-monthly-layout-1-highlight-categories', 'item_class' => 'teca-event-category teca-monthly-layout-1-highlight-category' ) ); ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="teca-monthly-layout-1-timeline-header">
							<h3 class="teca-monthly-layout-1-timeline-title"><?php esc_html_e( 'Timeline', 'the-events-calendar-addon2' ); ?></h3>
						</div>

						<div class="teca-monthly-layout-1-timeline">
							<div class="teca-monthly-layout-1-timeline-line" aria-hidden="true"></div>

							<?php foreach ( $month_events as $index => $event ) : ?>
								<?php
								$event_id      = (int) ( $event['event_id'] ?? 0 );
								$image_url     = $event_id ? get_the_post_thumbnail_url( $event_id, 'medium' ) : '';
																$category_ids  = teca_get_event_category_ids( $event );
								$time_display  = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
								$venue         = teca_get_event_venue_display( $event );
								$organizer     = teca_get_event_organizer_name( $event );
								$cost_display  = $event_id ? teca_get_event_cost_display( $event_id ) : '';
								$excerpt       = $event_id ? teca_get_event_excerpt_text( $event_id, 30 ) : '';
								$permalink     = $event_id ? get_the_permalink( $event_id ) : '';
								$cta_url       = $event_id ? teca_get_event_cta_url( $event_id ) : '';
								$button_url    = $cta_url ? $cta_url : $permalink;
								$start_date    = $event['dates']['start'] ?? '';
								$end_date      = $event['dates']['end'] ?? '';
								$date_label    = $event_id
									? teca_format_event_start_date_text( $event_id )
									: ( $start_date ? teca_format_layout_date_string( $start_date ) : '' );
								$accent_slug   = $accent_slugs[ $index % count( $accent_slugs ) ];
								$item_side     = 0 === $index % 2 ? 'left' : 'right';
								$item_classes  = 'teca-monthly-layout-1-timeline-item teca-monthly-layout-1-timeline-item-' . $item_side . ' teca-monthly-layout-1-timeline-accent-' . $accent_slug . ' teca-calendar-filterable-event';
								?>
								<article
									class="<?php echo esc_attr( $item_classes ); ?>"
									data-categories="<?php echo esc_attr( implode( ',', $category_ids ) ); ?>"
									<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									id="teca-monthly-layout-1-event-<?php echo esc_attr( (string) $event_id ); ?>"
								>
									<div class="teca-monthly-layout-1-timeline-dot" aria-hidden="true"></div>

									<?php if ( $date_label ) : ?>
										<div class="teca-monthly-layout-1-timeline-date"><?php echo esc_html( $date_label ); ?></div>
									<?php endif; ?>

									<div class="teca-monthly-layout-1-timeline-content">
										<div class="teca-monthly-layout-1-event">
											<?php if ( $image_url ) : ?>
												<div class="teca-monthly-layout-1-event-image">
													<?php if ( $permalink ) : ?>
														<a class="teca-monthly-layout-1-event-link" href="<?php echo esc_url( $permalink ); ?>">
															<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['event_name'] ?? '' ); ?>" loading="lazy" />
														</a>
													<?php else : ?>
														<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['event_name'] ?? '' ); ?>" loading="lazy" />
													<?php endif; ?>
												</div>
											<?php endif; ?>

											<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-monthly-layout-1-event-categories', 'item_class' => 'teca-event-category teca-monthly-layout-1-event-category', 'transform' => 'uppercase' ) ); ?>

											<h4 class="teca-monthly-layout-1-event-title">
												<?php if ( $permalink ) : ?>
													<a class="teca-monthly-layout-1-event-link" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
												<?php else : ?>
													<?php echo esc_html( $event['event_name'] ?? '' ); ?>
												<?php endif; ?>
											</h4>

											<?php if ( $time_display ) : ?>
												<div class="teca-monthly-layout-1-event-time"><?php echo esc_html( $time_display ); ?></div>
											<?php endif; ?>

											<?php if ( $excerpt ) : ?>
												<p class="teca-monthly-layout-1-event-excerpt"><?php echo esc_html( $excerpt ); ?></p>
											<?php endif; ?>

											<?php if ( ! empty( $venue['name'] ) ) : ?>
												<div class="teca-monthly-layout-1-event-venue"><?php echo esc_html( $venue['name'] ); ?></div>
											<?php endif; ?>

											<?php if ( $organizer ) : ?>
												<div class="teca-monthly-layout-1-event-organizer"><?php echo esc_html( $organizer ); ?></div>
											<?php endif; ?>

											<?php if ( $cost_display ) : ?>
												<div class="teca-monthly-layout-1-event-cost"><?php echo esc_html( $cost_display ); ?></div>
											<?php endif; ?>

											<?php if ( $button_url ) : ?>
												<a
													class="teca-monthly-layout-1-event-button"
													href="<?php echo esc_url( $button_url ); ?>"
													<?php echo $cta_url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
												>
													<?php echo esc_html( $cta_url ? __( 'Get Tickets', 'the-events-calendar-addon2' ) : __( 'View Event', 'the-events-calendar-addon2' ) ); ?>
												</a>
											<?php endif; ?>
										</div>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="teca-monthly-layout-1-decor teca-monthly-layout-1-decor-bottom" aria-hidden="true"></div>
				</div>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
