<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$events           = $events ?? array();
$event_groups     = $event_groups ?? array();
$schedule_title   = $schedule_title ?? __( 'Events Schedule', 'the-events-calendar-addon2' );
$category_options = $category_options ?? array();
$hero_images      = $hero_images ?? teca_get_events_hero_images( $events );
$layout_id        = $layout_id ?? 'teca';
$card_color_slugs = array( 'blue', 'green', 'forest', 'coral', 'gold' );
$card_color_index = 0;
?>

<div class="teca-daily-layout-3">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo teca_render_calendar_date_filter( $events, 'daily', $layout_id );
	?>
	<div class="teca-daily-layout-3-frame">
		<div class="teca-daily-layout-3-shell">
			<?php if ( ! empty( $hero_images ) ) : ?>
				<div class="teca-daily-layout-3-hero" data-interval="5000" aria-hidden="true">
					<div class="teca-daily-layout-3-hero-slides">
						<?php foreach ( $hero_images as $index => $hero_image ) : ?>
							<div
								class="teca-daily-layout-3-hero-slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
								style="<?php echo esc_attr( 'background-image:url(' . $hero_image['url'] . ');' ); ?>"
							></div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="teca-daily-layout-3-body">
				<div class="teca-daily-layout-3-header">
					<h2 class="teca-daily-layout-3-title"><?php echo esc_html( $schedule_title ); ?></h2>

					<?php if ( ! empty( $category_options ) ) : ?>
						<div class="teca-daily-layout-3-toolbar">
							<label class="teca-daily-layout-3-filter-label" for="teca-daily-layout-3-type-<?php echo esc_attr( $layout_id ); ?>">
								<?php esc_html_e( 'Event Type', 'the-events-calendar-addon2' ); ?>
							</label>
							<select
								id="teca-daily-layout-3-type-<?php echo esc_attr( $layout_id ); ?>"
								class="teca-daily-layout-3-type-select"
							>
								<option value="all"><?php esc_html_e( 'All Types', 'the-events-calendar-addon2' ); ?></option>
								<?php foreach ( $category_options as $term_id => $label ) : ?>
									<option value="<?php echo esc_attr( (string) $term_id ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $event_groups ) ) : ?>
					<?php foreach ( $event_groups as $group ) : ?>
						<section class="teca-daily-layout-3-group" data-month="<?php echo esc_attr( $group['month'] ); ?>">
							<h3 class="teca-daily-layout-3-group-title"><?php echo esc_html( $group['label'] ); ?></h3>

							<div class="teca-daily-layout-3-grid">
								<?php foreach ( $group['events'] as $event ) : ?>
									<?php
									$event_id     = (int) ( $event['event_id'] ?? 0 );
									$image_url    = $event_id ? get_the_post_thumbnail_url( $event_id, 'large' ) : '';
									$badge        = teca_get_event_date_badge_parts( $event_id );
																		$category_ids = teca_get_event_category_ids( $event );
									$venue        = teca_get_event_venue_display( $event );
									$time_display = teca_format_event_start_time_display( $event_id );
									$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
									$color_slug   = $card_color_slugs[ $card_color_index % count( $card_color_slugs ) ];
									$card_color_index++;
									$card_classes = 'teca-daily-layout-3-card teca-daily-layout-3-card-accent-' . $color_slug . ' teca-calendar-filterable-event';
									?>
									<article
										class="<?php echo esc_attr( $card_classes ); ?>"
										data-categories="<?php echo esc_attr( implode( ',', $category_ids ) ); ?>"
										<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										id="teca-daily-layout-3-card-<?php echo esc_attr( (string) $event_id ); ?>"
									>
										<?php if ( $image_url ) : ?>
											<div class="teca-daily-layout-3-card-media">
												<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['event_name'] ?? '' ); ?>" loading="lazy" />
												<?php if ( ! empty( $badge['month'] ) && ! empty( $badge['day'] ) ) : ?>
													<div class="teca-daily-layout-3-card-date-badge">
														<span class="teca-daily-layout-3-card-date-month"><?php echo esc_html( $badge['month'] ); ?></span>
														<span class="teca-daily-layout-3-card-date-day"><?php echo esc_html( $badge['day'] ); ?></span>
													</div>
												<?php endif; ?>
											</div>
										<?php endif; ?>

										<div class="teca-daily-layout-3-card-body">
											<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-daily-layout-3-card-categories', 'item_class' => 'teca-event-category teca-daily-layout-3-card-category', 'transform' => 'uppercase' ) ); ?>

											<h4 class="teca-daily-layout-3-card-title">
												<?php if ( $permalink ) : ?>
													<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
												<?php else : ?>
													<?php echo esc_html( $event['event_name'] ?? '' ); ?>
												<?php endif; ?>
											</h4>

											<?php if ( $venue['name'] ) : ?>
												<div class="teca-daily-layout-3-card-venue"><?php echo esc_html( $venue['name'] ); ?></div>
											<?php endif; ?>

											<?php if ( $time_display ) : ?>
												<div class="teca-daily-layout-3-card-time">
													<svg class="teca-daily-layout-3-card-time-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
														<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"></circle>
														<path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
													</svg>
													<span><?php echo esc_html( $time_display ); ?></span>
												</div>
											<?php endif; ?>
										</div>
									</article>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="teca-daily-layout-3-empty-content"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon2' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
