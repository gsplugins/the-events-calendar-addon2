<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$events           = $events ?? array();
$week_groups      = $week_groups ?? teca_group_events_by_week( $events );
$schedule_title   = $schedule_title ?? __( 'Weekly Calendar', 'the-events-calendar-addon2' );
$category_options = $category_options ?? array();
$layout_id        = $layout_id ?? 'teca';
$first_event_id   = ! empty( $events[0]['event_id'] ) ? (int) $events[0]['event_id'] : 0;
?>

<div class="teca-weekly-layout-1">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo teca_render_calendar_date_filter( $events, 'weekly', $layout_id );
	?>
	<div class="teca-weekly-layout-1-shell">
		<aside class="teca-weekly-layout-1-sidebar" aria-label="<?php esc_attr_e( 'Events', 'the-events-calendar-addon2' ); ?>">
			<div class="teca-weekly-layout-1-sidebar-label"><?php esc_html_e( 'Events', 'the-events-calendar-addon2' ); ?></div>

			<?php if ( ! empty( $events ) ) : ?>
				<ul class="teca-weekly-layout-1-event-list">
					<?php foreach ( $events as $event ) : ?>
						<?php
						$event_id   = (int) ( $event['event_id'] ?? 0 );
						$thumb_url  = $event_id ? get_the_post_thumbnail_url( $event_id, 'thumbnail' ) : '';
						$item_class = 'teca-weekly-layout-1-event-item';

						if ( $event_id && $event_id === $first_event_id ) {
							$item_class .= ' teca-weekly-layout-1-event-item-active';
						}
						?>
						<li class="<?php echo esc_attr( $item_class ); ?> teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<button type="button" class="teca-weekly-layout-1-event-trigger">
								<?php if ( $thumb_url ) : ?>
									<span class="teca-weekly-layout-1-event-thumb">
										<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="lazy" />
									</span>
								<?php endif; ?>
								<span class="teca-weekly-layout-1-event-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="teca-weekly-layout-1-empty-sidebar"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon2' ); ?></p>
			<?php endif; ?>
		</aside>

		<div class="teca-weekly-layout-1-content">
			<?php if ( ! empty( $category_options ) ) : ?>
				<div class="teca-weekly-layout-1-header">
					<div class="teca-weekly-layout-1-toolbar">
						<label class="teca-weekly-layout-1-filter-label" for="teca-weekly-layout-1-type-<?php echo esc_attr( $layout_id ); ?>">
							<?php esc_html_e( 'Event Type', 'the-events-calendar-addon2' ); ?>
						</label>
						<select
							id="teca-weekly-layout-1-type-<?php echo esc_attr( $layout_id ); ?>"
							class="teca-weekly-layout-1-type-select"
						>
							<option value="all"><?php esc_html_e( 'All Types', 'the-events-calendar-addon2' ); ?></option>
							<?php foreach ( $category_options as $term_id => $label ) : ?>
								<option value="<?php echo esc_attr( (string) $term_id ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $week_groups ) ) : ?>
				<?php foreach ( $week_groups as $week_group ) : ?>
					<?php $day_slots = teca_build_week_day_slots( $week_group['events'], $week_group['start'] ); ?>
					<section class="teca-weekly-layout-1-week" data-week-start="<?php echo esc_attr( $week_group['start'] ); ?>">
						<div class="teca-weekly-layout-1-week-header">
							<h3 class="teca-weekly-layout-1-week-range"><?php echo esc_html( $week_group['label'] ); ?></h3>
						</div>

						<div class="teca-weekly-layout-1-week-events">
							<div class="teca-weekly-layout-1-grid">
								<div class="teca-weekly-layout-1-intro">
									<h2 class="teca-weekly-layout-1-title"><?php echo esc_html( $schedule_title ); ?></h2>
									<p class="teca-weekly-layout-1-intro-range"><?php echo esc_html( $week_group['label'] ); ?></p>
								</div>

								<?php foreach ( $day_slots as $day_slot ) : ?>
									<?php
									$primary_event = $day_slot['primary_event'];
									$event_id      = $primary_event ? (int) ( $primary_event['event_id'] ?? 0 ) : 0;
									$image_url     = $event_id ? get_the_post_thumbnail_url( $event_id, 'large' ) : '';
									$badge         = $event_id ? teca_get_event_date_badge_parts( $event_id ) : array( 'month' => '', 'day' => '' );
																		$category_ids  = $primary_event ? teca_get_event_category_ids( $primary_event ) : array();
									$venue         = $primary_event ? teca_get_event_venue_display( $primary_event ) : array( 'name' => '', 'address' => '' );
									$organizer     = $primary_event ? teca_get_event_organizer_name( $primary_event ) : '';
									$time_display  = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
									$cost_display  = $event_id ? teca_get_event_cost_display( $event_id ) : '';
									$permalink     = $event_id ? get_the_permalink( $event_id ) : '';
									$cta_url       = $event_id ? teca_get_event_cta_url( $event_id ) : '';
									$button_url    = $cta_url ? $cta_url : $permalink;
									$card_classes  = 'teca-weekly-layout-1-card';

									if ( ! empty( $day_slot['is_weekend'] ) ) {
										$card_classes .= ' teca-weekly-layout-1-card-weekend';
									}

									if ( ! $primary_event ) {
										$card_classes .= ' teca-weekly-layout-1-card-empty';
									} elseif ( $primary_event ) {
										$card_classes .= ' teca-calendar-filterable-event';
									}
									?>
									<article
										class="<?php echo esc_attr( $card_classes ); ?>"
										data-day="<?php echo esc_attr( $day_slot['day_key'] ); ?>"
										data-categories="<?php echo esc_attr( implode( ',', $category_ids ) ); ?>"
										<?php echo $primary_event ? teca_get_event_filter_attributes_html( $primary_event ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										id="teca-weekly-layout-1-card-<?php echo esc_attr( $day_slot['day_key'] ); ?>"
									>
										<div class="teca-weekly-layout-1-card-header">
											<h4 class="teca-weekly-layout-1-card-day"><?php echo esc_html( $day_slot['day_label'] ); ?></h4>
											<?php if ( $primary_event ) : ?>
												<div class="teca-weekly-layout-1-card-title">
													<?php if ( $permalink ) : ?>
														<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $primary_event['event_name'] ?? '' ); ?></a>
													<?php else : ?>
														<?php echo esc_html( $primary_event['event_name'] ?? '' ); ?>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										</div>

										<?php if ( $image_url ) : ?>
											<div class="teca-weekly-layout-1-card-media">
												<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $primary_event['event_name'] ?? $day_slot['day_label'] ); ?>" loading="lazy" />
												<?php if ( ! empty( $badge['month'] ) && ! empty( $badge['day'] ) ) : ?>
													<div class="teca-weekly-layout-1-card-date-badge">
														<span class="teca-weekly-layout-1-card-date-month"><?php echo esc_html( $badge['month'] ); ?></span>
														<span class="teca-weekly-layout-1-card-date-day"><?php echo esc_html( $badge['day'] ); ?></span>
													</div>
												<?php endif; ?>
											</div>
										<?php else : ?>
											<div class="teca-weekly-layout-1-card-media teca-weekly-layout-1-card-media-empty"></div>
										<?php endif; ?>

										<div class="teca-weekly-layout-1-card-body">
											<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-weekly-layout-1-card-categories', 'item_class' => 'teca-event-category teca-weekly-layout-1-card-category', 'transform' => 'uppercase' ) ); ?>

											<?php if ( $time_display ) : ?>
												<div class="teca-weekly-layout-1-card-time">
													<svg class="teca-weekly-layout-1-card-time-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
														<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"></circle>
														<path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
													</svg>
													<span><?php echo esc_html( $time_display ); ?></span>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $venue['name'] ) ) : ?>
												<div class="teca-weekly-layout-1-card-venue"><?php echo esc_html( $venue['name'] ); ?></div>
											<?php endif; ?>

											<?php if ( $organizer ) : ?>
												<div class="teca-weekly-layout-1-card-organizer"><?php echo esc_html( $organizer ); ?></div>
											<?php endif; ?>

											<?php if ( $cost_display ) : ?>
												<div class="teca-weekly-layout-1-card-cost"><?php echo esc_html( $cost_display ); ?></div>
											<?php endif; ?>

											<?php if ( $button_url ) : ?>
												<a
													class="teca-weekly-layout-1-card-button"
													href="<?php echo esc_url( $button_url ); ?>"
													<?php echo $cta_url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
													aria-label="<?php echo esc_attr( $primary_event['event_name'] ?? $day_slot['day_label'] ); ?>"
												>
													<span aria-hidden="true">+</span>
												</a>
											<?php else : ?>
												<span class="teca-weekly-layout-1-card-button teca-weekly-layout-1-card-button-disabled" aria-hidden="true">+</span>
											<?php endif; ?>
										</div>
									</article>
								<?php endforeach; ?>
							</div>
						</div>
					</section>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="teca-weekly-layout-1-empty-content"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon2' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
