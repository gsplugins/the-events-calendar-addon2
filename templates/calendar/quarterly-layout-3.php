<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$events          = $events ?? array();
$layout_data     = $layout_data ?? teca_build_quarterly_layout_3_data( $events );
$schedule_title  = $schedule_title ?? __( 'Events Schedule', 'the-events-calendar-addon' );
$layout_id       = $layout_id ?? 'teca';
$max_cell_events = isset( $max_cell_events ) ? (int) $max_cell_events : 2;
$year            = (int) ( $layout_data['year'] ?? wp_date( 'Y' ) );
$years_label     = teca_get_quarterly_layout_years_label( $layout_data );
$quarters        = $layout_data['quarters'] ?? array();
$weekday_labels  = teca_get_calendar_weekday_labels( 'abbrev' );
$event_index     = 0;
?>

<div class="teca-quarterly-layout-3 teca-quarterly-layout-3-triple-calendar teca-quarterly-layout-3-vertical" data-view="quarterly" data-layout="quarterly-layout-3" data-year="<?php echo esc_attr( (string) $year ); ?>">
	<div class="teca-quarterly-layout-3-backdrop" aria-hidden="true"></div>

	<div class="teca-quarterly-layout-3-shell">
		<header class="teca-quarterly-layout-3-header">
			<div class="teca-quarterly-layout-3-header-main">
				<?php if ( $schedule_title ) : ?>
					<h2 class="teca-quarterly-layout-3-title"><?php echo esc_html( $schedule_title ); ?></h2>
				<?php endif; ?>
				<p class="teca-quarterly-layout-3-subtitle">
					<span class="teca-quarterly-layout-3-year"><?php echo esc_html( $years_label ); ?></span>
				</p>
			</div>

			<div class="teca-quarterly-layout-3-toolbar">
				<?php if ( ! empty( $events ) ) : ?>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo teca_render_calendar_date_filter( $events, 'quarterly', $layout_id );
					?>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( empty( $events ) ) : ?>
			<div class="teca-calendar-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></div>
		<?php else : ?>
			<div class="teca-quarterly-layout-3-body">
				<?php foreach ( $quarters as $quarter_group ) : ?>
					<?php
					$year_quarter_key = $quarter_group['key'] ?? '';
					$quarter_key      = $quarter_group['quarter_key'] ?? '';
					$quarter_year     = (int) ( $quarter_group['year'] ?? $year );
					$quarter_range    = teca_get_quarter_month_range_label( $quarter_group['months'] ?? array() );
					?>
					<section
						class="teca-quarterly-layout-3-quarter"
						data-quarter="<?php echo esc_attr( $year_quarter_key ); ?>"
						data-year="<?php echo esc_attr( (string) $quarter_year ); ?>"
						data-quarter-key="<?php echo esc_attr( $quarter_key ); ?>"
					>
						<div class="teca-quarterly-layout-3-quarter-header">
							<h3 class="teca-quarterly-layout-3-quarter-name"><?php echo esc_html( $quarter_group['label'] ?? '' ); ?></h3>
							<?php if ( $quarter_range ) : ?>
								<p class="teca-quarterly-layout-3-quarter-range"><?php echo esc_html( $quarter_range ); ?></p>
							<?php endif; ?>
						</div>

						<div class="teca-quarterly-layout-3-quarter-months">
							<?php foreach ( $quarter_group['months'] as $month_group ) : ?>
								<?php
								$month_number  = (int) ( $month_group['month'] ?? 0 );
								$month_key     = $month_group['month_key'] ?? '';
								$calendar_data = $month_group['calendar'] ?? teca_build_monthly_calendar_cells( $month_key, $month_group['events'] ?? array() );
								$month_name    = $month_group['month_name'] ?? '';
								$month_ts      = strtotime( $month_key . '-01' );
								$month_year    = $month_ts ? date_i18n( 'Y', $month_ts ) : (string) $year;
								?>
								<div
									class="teca-quarterly-layout-3-month"
									data-month="<?php echo esc_attr( sprintf( '%02d', $month_number ) ); ?>"
								data-year="<?php echo esc_attr( (string) $quarter_year ); ?>"
								data-quarter="<?php echo esc_attr( $year_quarter_key ); ?>"
								>
									<div class="teca-quarterly-layout-3-month-header">
										<h4 class="teca-quarterly-layout-3-month-name"><?php echo esc_html( $month_name ); ?></h4>
										<span class="teca-quarterly-layout-3-month-year"><?php echo esc_html( $month_year ); ?></span>
									</div>

									<div class="teca-quarterly-layout-3-weekdays" aria-hidden="true">
										<?php foreach ( $weekday_labels as $weekday_label ) : ?>
											<div class="teca-quarterly-layout-3-weekday"><?php echo esc_html( strtoupper( $weekday_label ) ); ?></div>
										<?php endforeach; ?>
									</div>

									<div class="teca-quarterly-layout-3-month-grid">
										<?php foreach ( $calendar_data['weeks'] as $week ) : ?>
											<?php foreach ( $week as $cell ) : ?>
												<?php if ( 'empty' === ( $cell['type'] ?? '' ) ) : ?>
													<div class="teca-quarterly-layout-3-day-cell teca-quarterly-layout-3-day-cell-empty" aria-hidden="true"></div>
												<?php else : ?>
													<?php
													$day_events   = $cell['events'] ?? array();
													$visible      = array_slice( $day_events, 0, $max_cell_events );
													$hidden_count = max( 0, count( $day_events ) - count( $visible ) );
													$is_today     = wp_date( 'Y-m-d' ) === ( $cell['date'] ?? '' );
													$cell_class   = 'teca-quarterly-layout-3-day-cell';

													if ( $is_today ) {
														$cell_class .= ' teca-quarterly-layout-3-day-cell-today';
													}
													?>
													<div
														class="<?php echo esc_attr( $cell_class ); ?>"
														data-date="<?php echo esc_attr( $cell['date'] ); ?>"
														data-day="<?php echo esc_attr( (string) $cell['day'] ); ?>"
														data-month="<?php echo esc_attr( sprintf( '%02d', (int) $cell['month'] ) ); ?>"
														data-year="<?php echo esc_attr( (string) $cell['year'] ); ?>"
														data-quarter="<?php echo esc_attr( $year_quarter_key ); ?>"
													>
														<div class="teca-quarterly-layout-3-day-number"><?php echo esc_html( sprintf( '%02d', (int) $cell['day'] ) ); ?></div>

														<?php if ( ! empty( $visible ) ) : ?>
															<div class="teca-quarterly-layout-3-day-events">
																<?php foreach ( $visible as $event ) : ?>
																	<?php
																	$event_id     = (int) ( $event['event_id'] ?? 0 );
																	$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
																																		$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
																	$venue        = teca_get_event_venue_display( $event );
																	$accent_slug  = teca_get_quarterly_layout_3_accent_slug( $event_index );
																	$event_index++;
																	$event_class  = 'teca-quarterly-layout-3-event teca-quarterly-layout-3-event-accent-' . $accent_slug;
																	?>
																	<div
																		class="<?php echo esc_attr( $event_class ); ?> teca-calendar-filterable-event"
																		<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
																	>
																		<?php if ( ! empty( teca_get_event_category_names( $event ) ) ) : ?>
																			<span class="teca-quarterly-layout-3-event-badge" aria-hidden="true"></span>
																		<?php endif; ?>

																		<div class="teca-quarterly-layout-3-event-title">
																			<?php if ( $permalink ) : ?>
																				<a class="teca-quarterly-layout-3-event-link" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
																			<?php else : ?>
																				<?php echo esc_html( $event['event_name'] ?? '' ); ?>
																			<?php endif; ?>
																		</div>

																		<?php if ( $time_display ) : ?>
																			<div class="teca-quarterly-layout-3-event-time"><?php echo esc_html( $time_display ); ?></div>
																		<?php endif; ?>

																		<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-quarterly-layout-3-event-categories', 'item_class' => 'teca-event-category teca-quarterly-layout-3-event-category' ) ); ?>

																		<?php if ( ! empty( $venue['name'] ) ) : ?>
																			<div class="teca-quarterly-layout-3-event-venue"><?php echo esc_html( $venue['name'] ); ?></div>
																		<?php endif; ?>
																	</div>
																<?php endforeach; ?>

																<?php if ( $hidden_count > 0 ) : ?>
																	<div class="teca-quarterly-layout-3-more-events">
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
														<?php endif; ?>
													</div>
												<?php endif; ?>
											<?php endforeach; ?>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
