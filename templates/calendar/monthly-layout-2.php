<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$events         = $events ?? array();
$month_groups   = $month_groups ?? teca_group_events_by_month( $events );
$schedule_title = $schedule_title ?? __( 'Events Calendar', 'the-events-calendar-addon2' );
$layout_id      = $layout_id ?? 'teca';
?>

<div class="teca-monthly-layout-2 teca-monthly-layout-2-showcase" data-view="monthly" data-layout="monthly-layout-2">
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
			$month_stamp   = strtotime( $month_group['month'] . '-01' );
			$month_number  = $month_stamp ? date_i18n( 'm', $month_stamp ) : '';
			$year_value    = $month_parts['year'] ?? '';
			$year_top      = strlen( $year_value ) >= 4 ? substr( $year_value, 0, 2 ) : $year_value;
			$year_bottom   = strlen( $year_value ) >= 4 ? substr( $year_value, 2, 2 ) : '';
			$hero_slides   = array();
			$slide_index   = 0;

			foreach ( $month_events as $event ) {
				$event_id = (int) ( $event['event_id'] ?? 0 );
				$url      = $event_id ? get_the_post_thumbnail_url( $event_id, 'large' ) : '';

				if ( ! $url ) {
					continue;
				}

				$hero_slides[] = array(
					'url'      => $url,
					'event_id' => $event_id,
					'index'    => $slide_index,
				);

				$slide_index++;
			}

			$first_event_id = ! empty( $month_events[0]['event_id'] ) ? (int) $month_events[0]['event_id'] : 0;
			?>
			<section class="teca-monthly-layout-2-section" data-month="<?php echo esc_attr( $month_group['month'] ); ?>">
				<header class="teca-monthly-layout-2-header">
					<h2 class="teca-monthly-layout-2-title"><?php echo esc_html( $schedule_title ); ?></h2>
					<?php if ( ! empty( $month_parts['month_name'] ) ) : ?>
						<p class="teca-monthly-layout-2-month"><?php echo esc_html( $month_parts['month_name'] ); ?></p>
					<?php endif; ?>
					<?php if ( $year_value ) : ?>
						<p class="teca-monthly-layout-2-year"><?php echo esc_html( $year_value ); ?></p>
					<?php endif; ?>
				</header>

				<div class="teca-monthly-layout-2-shell">
					<div class="teca-monthly-layout-2-left">
						<div class="teca-monthly-layout-2-feature">
							<div class="teca-monthly-layout-2-feature-header">
								<?php if ( ! empty( $month_parts['month_name'] ) ) : ?>
									<div class="teca-monthly-layout-2-feature-title"><?php echo esc_html( $month_parts['month_name'] ); ?></div>
								<?php endif; ?>
								<?php if ( $month_number ) : ?>
									<div class="teca-monthly-layout-2-feature-date"><?php echo esc_html( $month_number ); ?></div>
								<?php endif; ?>
							</div>

							<div class="teca-monthly-layout-2-feature-media">
								<?php if ( ! empty( $hero_slides ) ) : ?>
									<div class="teca-monthly-layout-2-feature-image-slider" data-interval="5000">
										<?php foreach ( $hero_slides as $slide ) : ?>
											<div
												class="teca-monthly-layout-2-feature-image-slide<?php echo (int) $slide['event_id'] === $first_event_id ? ' is-active' : ''; ?>"
												data-event-id="<?php echo esc_attr( (string) $slide['event_id'] ); ?>"
												data-slide-index="<?php echo esc_attr( (string) $slide['index'] ); ?>"
												style="<?php echo esc_attr( 'background-image:url(' . $slide['url'] . ');' ); ?>"
											></div>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>

								<div class="teca-monthly-layout-2-feature-overlay" aria-hidden="true"></div>

								<?php if ( $year_value ) : ?>
									<div class="teca-monthly-layout-2-feature-badge" aria-hidden="true">
										<?php if ( $year_top ) : ?>
											<span><?php echo esc_html( $year_top ); ?></span>
										<?php endif; ?>
										<?php if ( $year_bottom ) : ?>
											<span><?php echo esc_html( $year_bottom ); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>

							<ul class="teca-monthly-layout-2-feature-event-list" aria-label="<?php esc_attr_e( 'Events', 'the-events-calendar-addon2' ); ?>">
								<?php foreach ( $month_events as $index => $event ) : ?>
									<?php
									$event_id      = (int) ( $event['event_id'] ?? 0 );
									$start_date    = $event['dates']['start'] ?? '';
									$date_label    = $event_id
										? teca_format_event_start_date_text( $event_id )
										: ( $start_date ? teca_format_layout_date_string( $start_date ) : '' );
									$time_display  = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
									$slide_attr    = '';
									$is_active     = $event_id === $first_event_id;
									$item_classes  = 'teca-monthly-layout-2-feature-event-item';

									if ( $is_active ) {
										$item_classes .= ' teca-monthly-layout-2-feature-event-item-active';
									}

									foreach ( $hero_slides as $slide ) {
										if ( (int) $slide['event_id'] === $event_id ) {
											$slide_attr = (string) $slide['index'];
											break;
										}
									}
									?>
									<li
										class="<?php echo esc_attr( $item_classes ); ?> teca-calendar-filterable-event"
										<?php if ( '' !== $slide_attr ) : ?>
											data-slide-index="<?php echo esc_attr( $slide_attr ); ?>"
										<?php endif; ?>
										data-target-event="<?php echo esc_attr( (string) $event_id ); ?>"
										<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									>
										<?php if ( $date_label ) : ?>
											<div class="teca-monthly-layout-2-feature-event-item-date"><?php echo esc_html( strtoupper( $date_label ) ); ?></div>
										<?php endif; ?>
										<div class="teca-monthly-layout-2-feature-event-item-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></div>
										<?php if ( $time_display ) : ?>
											<div class="teca-monthly-layout-2-feature-event-meta"><?php echo esc_html( $time_display ); ?></div>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>

					<div class="teca-monthly-layout-2-right">
						<div class="teca-monthly-layout-2-grid">
							<?php foreach ( $month_events as $index => $event ) : ?>
								<?php
								$event_id     = (int) ( $event['event_id'] ?? 0 );
								$image_url    = $event_id ? get_the_post_thumbnail_url( $event_id, 'medium' ) : '';
								$badge        = teca_get_event_date_badge_parts( $event_id );
																$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
								$time_range   = $event_id ? teca_format_event_time_range( $event_id ) : '';
								$venue        = teca_get_event_venue_display( $event );
								$organizer    = teca_get_event_organizer_name( $event );
								$cost_display = $event_id ? teca_get_event_cost_display( $event_id ) : '';
								$excerpt      = $event_id ? teca_get_event_excerpt_text( $event_id, 18 ) : '';
								$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
								$cta_url      = $event_id ? teca_get_event_cta_url( $event_id ) : '';
								$button_url   = $cta_url ? $cta_url : $permalink;
								$start_date   = $event['dates']['start'] ?? '';
								$date_line    = $event_id
									? teca_format_event_start_date_text( $event_id )
									: ( $start_date ? teca_format_layout_date_string( $start_date ) : '' );
								$is_active    = $event_id === $first_event_id;
								$card_classes = 'teca-monthly-layout-2-card teca-calendar-filterable-event';

								if ( $is_active ) {
									$card_classes .= ' teca-monthly-layout-2-card-active';
								}
								?>
								<article
									class="<?php echo esc_attr( $card_classes ); ?>"
									<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									id="teca-monthly-layout-2-card-<?php echo esc_attr( (string) $event_id ); ?>"
								>
									<div class="teca-monthly-layout-2-card-header">
										<?php if ( ! empty( $badge['month'] ) ) : ?>
											<span class="teca-monthly-layout-2-card-month"><?php echo esc_html( $badge['month'] ); ?></span>
										<?php endif; ?>
										<?php if ( ! empty( $badge['day'] ) ) : ?>
											<span class="teca-monthly-layout-2-card-day"><?php echo esc_html( $badge['day'] ); ?></span>
										<?php endif; ?>
									</div>

									<?php if ( $image_url ) : ?>
										<div class="teca-monthly-layout-2-card-media">
											<?php if ( $permalink ) : ?>
												<a href="<?php echo esc_url( $permalink ); ?>">
													<img class="teca-monthly-layout-2-card-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['event_name'] ?? '' ); ?>" loading="lazy" />
												</a>
											<?php else : ?>
												<img class="teca-monthly-layout-2-card-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['event_name'] ?? '' ); ?>" loading="lazy" />
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<div class="teca-monthly-layout-2-card-body">
										<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-monthly-layout-2-card-categories', 'item_class' => 'teca-event-category teca-monthly-layout-2-card-category', 'transform' => 'uppercase' ) ); ?>

										<h3 class="teca-monthly-layout-2-card-title">
											<?php if ( $permalink ) : ?>
												<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $event['event_name'] ?? '' ); ?>
											<?php endif; ?>
										</h3>

										<?php if ( $time_range ) : ?>
											<div class="teca-monthly-layout-2-card-time"><?php echo esc_html( $time_range ); ?></div>
										<?php elseif ( $time_display ) : ?>
											<div class="teca-monthly-layout-2-card-time"><?php echo esc_html( $time_display ); ?></div>
										<?php endif; ?>

										<?php if ( $date_line ) : ?>
											<div class="teca-monthly-layout-2-card-date"><?php echo esc_html( $date_line ); ?></div>
										<?php endif; ?>

										<?php if ( $excerpt ) : ?>
											<p class="teca-monthly-layout-2-card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
										<?php endif; ?>

										<?php if ( ! empty( $venue['name'] ) ) : ?>
											<div class="teca-monthly-layout-2-card-venue"><?php echo esc_html( $venue['name'] ); ?></div>
										<?php endif; ?>

										<?php if ( $organizer ) : ?>
											<div class="teca-monthly-layout-2-card-organizer"><?php echo esc_html( $organizer ); ?></div>
										<?php endif; ?>

										<?php if ( $cost_display ) : ?>
											<div class="teca-monthly-layout-2-card-cost"><?php echo esc_html( $cost_display ); ?></div>
										<?php endif; ?>

										<?php if ( $button_url ) : ?>
											<a
												class="teca-monthly-layout-2-card-button"
												href="<?php echo esc_url( $button_url ); ?>"
												<?php echo $cta_url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
											>
												<?php echo esc_html( $cta_url ? __( 'Get Tickets', 'the-events-calendar-addon2' ) : __( 'View Event', 'the-events-calendar-addon2' ) ); ?>
											</a>
										<?php endif; ?>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
