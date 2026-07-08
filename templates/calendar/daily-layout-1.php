<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$events           = $events ?? array();
$event_groups     = $event_groups ?? array();
$schedule_title   = $schedule_title ?? __( 'Events Schedule', 'the-events-calendar-addon' );
$category_options = $category_options ?? array();
$layout_id        = $layout_id ?? 'teca';
$first_event_id = ! empty( $events[0]['event_id'] ) ? (int) $events[0]['event_id'] : 0;
?>

<div class="teca-daily-layout-1">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo teca_render_calendar_date_filter( $events, 'daily', $layout_id );
	?>
	<div class="teca-daily-layout-1-shell">
		<aside class="teca-daily-layout-1-sidebar" aria-label="<?php esc_attr_e( 'Events', 'the-events-calendar-addon' ); ?>">
			<div class="teca-daily-layout-1-sidebar-label"><?php esc_html_e( 'Events', 'the-events-calendar-addon' ); ?></div>

			<?php if ( ! empty( $events ) ) : ?>
				<ul class="teca-daily-layout-1-event-list">
					<?php foreach ( $events as $event ) : ?>
						<?php
						$event_id   = (int) ( $event['event_id'] ?? 0 );
						$thumb_url  = $event_id ? get_the_post_thumbnail_url( $event_id, 'thumbnail' ) : '';
						$item_class = 'teca-daily-layout-1-event-item';

						if ( $event_id && $event_id === $first_event_id ) {
							$item_class .= ' teca-daily-layout-1-event-item-active';
						}
						?>
						<li class="<?php echo esc_attr( $item_class ); ?> teca-calendar-event teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<button type="button" class="teca-daily-layout-1-event-trigger">
								<?php if ( $thumb_url ) : ?>
									<span class="teca-daily-layout-1-event-thumb">
										<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="lazy" />
									</span>
								<?php endif; ?>
								<span class="teca-daily-layout-1-event-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="teca-daily-layout-1-empty-sidebar"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></p>
			<?php endif; ?>
		</aside>

		<div class="teca-daily-layout-1-content">
			<div class="teca-daily-layout-1-header">
				<h2 class="teca-daily-layout-1-title"><?php echo esc_html( $schedule_title ); ?></h2>

				<?php if ( ! empty( $category_options ) ) : ?>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo teca_render_calendar_event_type_filter(
						$events,
						$layout_id,
						array(
							'wrapper_class' => 'teca-daily-layout-1-toolbar',
							'select_class'  => 'teca-daily-layout-1-type-select',
						)
					);
					?>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $event_groups ) ) : ?>
				<?php foreach ( $event_groups as $group ) : ?>
					<section class="teca-daily-layout-1-group teca-calendar-group" data-calendar-group="month" data-calendar-group-value="<?php echo esc_attr( $group['month'] ); ?>" data-month="<?php echo esc_attr( $group['month'] ); ?>">
						<h3 class="teca-daily-layout-1-group-title"><?php echo esc_html( $group['label'] ); ?></h3>

						<div class="teca-daily-layout-1-grid">
							<?php foreach ( $group['events'] as $event ) : ?>
								<?php
								$event_id    = (int) ( $event['event_id'] ?? 0 );
								$image_url   = $event_id ? get_the_post_thumbnail_url( $event_id, 'large' ) : '';
								$badge       = teca_get_event_date_badge_parts( $event_id );
																$time_range  = teca_format_event_time_range( $event_id );
								$cta_url     = teca_get_event_cta_url( $event_id );
								$permalink   = $event_id ? get_the_permalink( $event_id ) : '';
								$button_url  = $cta_url ? $cta_url : $permalink;
								$button_text = $cta_url
									? __( 'Get Tickets', 'the-events-calendar-addon' )
									: __( 'View Event', 'the-events-calendar-addon' );
								?>
								<article class="teca-daily-layout-1-card teca-calendar-event teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> id="teca-daily-layout-1-card-<?php echo esc_attr( (string) $event_id ); ?>">
									<?php if ( $image_url ) : ?>
										<div class="teca-daily-layout-1-card-media">
											<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['event_name'] ?? '' ); ?>" loading="lazy" />
											<?php if ( ! empty( $badge['month'] ) && ! empty( $badge['day'] ) ) : ?>
												<div class="teca-daily-layout-1-card-date-badge">
													<span class="teca-daily-layout-1-card-date-month"><?php echo esc_html( $badge['month'] ); ?></span>
													<span class="teca-daily-layout-1-card-date-day"><?php echo esc_html( $badge['day'] ); ?></span>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<div class="teca-daily-layout-1-card-body">
										<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-daily-layout-1-card-categories', 'item_class' => 'teca-event-category teca-daily-layout-1-card-category', 'transform' => 'uppercase' ) ); ?>

										<h4 class="teca-daily-layout-1-card-title">
											<?php if ( $permalink ) : ?>
												<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $event['event_name'] ?? '' ); ?>
											<?php endif; ?>
										</h4>

										<?php if ( $time_range ) : ?>
											<div class="teca-daily-layout-1-card-time">
												<svg class="teca-daily-layout-1-card-time-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
													<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"></circle>
													<path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
												</svg>
												<span><?php echo esc_html( $time_range ); ?></span>
											</div>
										<?php endif; ?>

										<?php if ( $button_url ) : ?>
											<a class="teca-daily-layout-1-card-button" href="<?php echo esc_url( $button_url ); ?>"<?php echo $cta_url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
												<?php echo esc_html( $button_text ); ?>
											</a>
										<?php endif; ?>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="teca-daily-layout-1-empty-content"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
