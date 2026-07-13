<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$events           = $events ?? array();
$week_groups      = $week_groups ?? teca_group_events_by_week( $events );
$schedule_title   = $schedule_title ?? __( 'Weekly Program', 'the-events-calendar-addon' );
$category_options = $category_options ?? array();
$layout_id        = $layout_id ?? 'teca';
$first_event_id   = ! empty( $events[0]['event_id'] ) ? (int) $events[0]['event_id'] : 0;
$accent_slugs     = array( 'teal', 'purple', 'orange', 'blue' );
?>

<div class="teca-weekly-layout-2">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo teca_render_calendar_date_filter( $events, 'weekly', $layout_id );
	?>
	<div class="teca-weekly-layout-2-shell">
		<aside class="teca-weekly-layout-2-sidebar" aria-label="<?php esc_attr_e( 'Events', 'the-events-calendar-addon' ); ?>">
			<div class="teca-weekly-layout-2-sidebar-label"><?php esc_html_e( 'Events', 'the-events-calendar-addon' ); ?></div>

			<?php if ( ! empty( $events ) ) : ?>
				<ul class="teca-weekly-layout-2-event-list">
					<?php foreach ( $events as $event ) : ?>
						<?php
						$event_id   = (int) ( $event['event_id'] ?? 0 );
						$thumb_url  = $event_id ? get_the_post_thumbnail_url( $event_id, 'thumbnail' ) : '';
						$item_class = 'teca-weekly-layout-2-event-item';

						if ( $event_id && $event_id === $first_event_id ) {
							$item_class .= ' teca-weekly-layout-2-event-item-active';
						}
						?>
						<li class="<?php echo esc_attr( $item_class ); ?> teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<button type="button" class="teca-weekly-layout-2-event-trigger">
								<?php if ( $thumb_url ) : ?>
									<span class="teca-weekly-layout-2-event-thumb">
										<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="lazy" />
									</span>
								<?php endif; ?>
								<span class="teca-weekly-layout-2-event-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="teca-weekly-layout-2-empty-sidebar"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></p>
			<?php endif; ?>
		</aside>

		<div class="teca-weekly-layout-2-content">
			<div class="teca-weekly-layout-2-header">
				<div class="teca-weekly-layout-2-title-wrap">
					<h2 class="teca-weekly-layout-2-title"><?php echo esc_html( strtoupper( $schedule_title ) ); ?></h2>
					<?php if ( ! empty( $week_groups ) ) : ?>
						<?php $first_week = reset( $week_groups ); ?>
						<div class="teca-weekly-layout-2-title-divider" aria-hidden="true"></div>
						<p class="teca-weekly-layout-2-title-sub"><?php echo esc_html( strtoupper( $first_week['label'] ?? '' ) ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $category_options ) ) : ?>
					<div class="teca-weekly-layout-2-toolbar">
						<label class="teca-weekly-layout-2-filter-label" for="teca-weekly-layout-2-type-<?php echo esc_attr( $layout_id ); ?>">
							<?php esc_html_e( 'Event Type', 'the-events-calendar-addon' ); ?>
						</label>
						<select
							id="teca-weekly-layout-2-type-<?php echo esc_attr( $layout_id ); ?>"
							class="teca-weekly-layout-2-type-select"
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
					<?php
					$week_venue = teca_get_week_primary_venue_name( $week_group['events'] );
					$card_index = 0;
					?>
					<section class="teca-weekly-layout-2-week" data-week-start="<?php echo esc_attr( $week_group['start'] ); ?>">
						<div class="teca-weekly-layout-2-week-header">
							<h3 class="teca-weekly-layout-2-week-range"><?php echo esc_html( $week_group['label'] ); ?></h3>
						</div>

						<div class="teca-weekly-layout-2-week-events">
							<div class="teca-weekly-layout-2-grid">
								<?php foreach ( $week_group['events'] as $event ) : ?>
									<?php
									$event_id     = (int) ( $event['event_id'] ?? 0 );
									$image_url    = $event_id ? get_the_post_thumbnail_url( $event_id, 'large' ) : '';
									$day_abbrev   = teca_get_event_day_abbrev( $event );
																		$category_ids = teca_get_event_category_ids( $event );
									$subtitle     = teca_get_event_card_subtitle_line( $event, $event_id );
									$time_short   = $event_id ? teca_format_event_start_time_short( $event_id ) : '';
									$venue        = teca_get_event_venue_display( $event );
									$organizer    = teca_get_event_organizer_name( $event );
									$cost_display = $event_id ? teca_get_event_cost_display( $event_id ) : '';
									$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
									$cta_url      = $event_id ? teca_get_event_cta_url( $event_id ) : '';
									$button_url   = $cta_url ? $cta_url : $permalink;
									$accent_slug  = $accent_slugs[ $card_index % count( $accent_slugs ) ];
									$card_index++;
									$card_classes = 'teca-weekly-layout-2-card teca-weekly-layout-2-card-accent-' . $accent_slug . ' teca-calendar-filterable-event';
									?>
									<article
										class="<?php echo esc_attr( $card_classes ); ?>"
										data-categories="<?php echo esc_attr( implode( ',', $category_ids ) ); ?>"
										<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										id="teca-weekly-layout-2-card-<?php echo esc_attr( (string) $event_id ); ?>"
									>
										<?php if ( $image_url ) : ?>
											<div class="teca-weekly-layout-2-card-media" style="<?php echo esc_attr( 'background-image:url(' . $image_url . ');' ); ?>"></div>
										<?php else : ?>
											<div class="teca-weekly-layout-2-card-media teca-weekly-layout-2-card-media-empty"></div>
										<?php endif; ?>

										<div class="teca-weekly-layout-2-card-overlay"></div>

										<div class="teca-weekly-layout-2-card-inner">
											<?php if ( $day_abbrev ) : ?>
												<div class="teca-weekly-layout-2-card-date-badge"><?php echo esc_html( $day_abbrev ); ?></div>
											<?php endif; ?>

											<div class="teca-weekly-layout-2-card-body">
												<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-weekly-layout-2-card-categories', 'item_class' => 'teca-event-category teca-weekly-layout-2-card-category', 'transform' => 'uppercase' ) ); ?>

												<h4 class="teca-weekly-layout-2-card-title">
													<?php if ( $permalink ) : ?>
														<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( strtoupper( $event['event_name'] ?? '' ) ); ?></a>
													<?php else : ?>
														<?php echo esc_html( strtoupper( $event['event_name'] ?? '' ) ); ?>
													<?php endif; ?>
												</h4>

												<?php if ( $subtitle ) : ?>
													<p class="teca-weekly-layout-2-card-meta"><?php echo esc_html( strtoupper( $subtitle ) ); ?></p>
												<?php endif; ?>

												<?php if ( ! empty( $venue['name'] ) && ! $subtitle ) : ?>
													<div class="teca-weekly-layout-2-card-venue"><?php echo esc_html( $venue['name'] ); ?></div>
												<?php endif; ?>

												<?php if ( $organizer && ! $subtitle ) : ?>
													<div class="teca-weekly-layout-2-card-organizer"><?php echo esc_html( $organizer ); ?></div>
												<?php endif; ?>

												<?php if ( $cost_display && ! $subtitle ) : ?>
													<div class="teca-weekly-layout-2-card-cost"><?php echo esc_html( $cost_display ); ?></div>
												<?php endif; ?>

												<?php if ( $button_url ) : ?>
													<a
														class="teca-weekly-layout-2-card-button"
														href="<?php echo esc_url( $button_url ); ?>"
														<?php echo $cta_url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
													>
														<?php echo esc_html( $cta_url ? __( 'Get Tickets', 'the-events-calendar-addon' ) : __( 'View Event', 'the-events-calendar-addon' ) ); ?>
													</a>
												<?php endif; ?>
											</div>

											<?php if ( $time_short ) : ?>
												<div class="teca-weekly-layout-2-card-time">
													<span><?php echo esc_html( $time_short ); ?></span>
												</div>
											<?php endif; ?>
										</div>
									</article>
								<?php endforeach; ?>
							</div>
						</div>

						<?php if ( $week_venue ) : ?>
							<div class="teca-weekly-layout-2-footer-venue"><?php echo esc_html( strtoupper( $week_venue ) ); ?></div>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="teca-weekly-layout-2-empty-content"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
