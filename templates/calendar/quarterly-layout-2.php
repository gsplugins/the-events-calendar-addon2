<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$events          = $events ?? array();
$layout_data     = $layout_data ?? teca_build_quarterly_layout_2_data( $events );
$schedule_title  = $schedule_title ?? __( 'Events Schedule', 'the-events-calendar-addon' );
$layout_id       = $layout_id ?? 'teca';
$max_cell_events = isset( $max_cell_events ) ? (int) $max_cell_events : 2;
$year            = (int) ( $layout_data['year'] ?? wp_date( 'Y' ) );
$years_label     = teca_get_quarterly_layout_years_label( $layout_data );
$quarters        = $layout_data['quarters'] ?? array();
$weekday_labels  = teca_get_calendar_weekday_labels( 'abbrev' );
$month_accents   = array(
	1  => 'amber',
	2  => 'orange',
	3  => 'coral',
	4  => 'pink',
	5  => 'purple',
	6  => 'indigo',
	7  => 'blue',
	8  => 'cyan',
	9  => 'teal',
	10 => 'lime',
	11 => 'green',
	12 => 'forest',
);
$quarter_accents = array(
	'Q1' => 'gold',
	'Q2' => 'rose',
	'Q3' => 'sky',
	'Q4' => 'mint',
);
$event_index = 0;
?>

<div class="teca-quarterly-layout-2 teca-quarterly-layout-2-triple-month" data-view="quarterly" data-layout="quarterly-layout-2" data-year="<?php echo esc_attr( (string) $year ); ?>">
	<header class="teca-quarterly-layout-2-header">
		<div class="teca-quarterly-layout-2-header-main">
			<?php if ( $schedule_title ) : ?>
				<h2 class="teca-quarterly-layout-2-title"><?php echo esc_html( $schedule_title ); ?></h2>
			<?php endif; ?>
			<p class="teca-quarterly-layout-2-subtitle">
				<span class="teca-quarterly-layout-2-year"><?php echo esc_html( $years_label ); ?></span>
			</p>
		</div>

		<div class="teca-quarterly-layout-2-toolbar">
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
		<div class="teca-quarterly-layout-2-body">
			<?php foreach ( $quarters as $quarter_group ) : ?>
				<?php
				$year_quarter_key = $quarter_group['key'] ?? '';
				$quarter_key      = $quarter_group['quarter_key'] ?? '';
				$quarter_year     = (int) ( $quarter_group['year'] ?? $year );
				$quarter_accent   = $quarter_accents[ $quarter_key ] ?? 'gold';
				?>
				<section
					class="teca-quarterly-layout-2-quarter teca-quarterly-layout-2-quarter-accent-<?php echo esc_attr( $quarter_accent ); ?>"
					data-quarter="<?php echo esc_attr( $year_quarter_key ); ?>"
					data-year="<?php echo esc_attr( (string) $quarter_year ); ?>"
					data-quarter-key="<?php echo esc_attr( $quarter_key ); ?>"
				>
					<div class="teca-quarterly-layout-2-quarter-header">
						<h3 class="teca-quarterly-layout-2-quarter-name"><?php echo esc_html( $quarter_group['label'] ?? '' ); ?></h3>
					</div>

					<div class="teca-quarterly-layout-2-quarter-months">
						<?php foreach ( $quarter_group['months'] as $month_group ) : ?>
							<?php
							$month_number   = (int) ( $month_group['month'] ?? 0 );
							$month_key      = $month_group['month_key'] ?? '';
							$calendar_data  = $month_group['calendar'] ?? teca_build_monthly_calendar_cells( $month_key, $month_group['events'] ?? array() );
							$month_accent   = $month_accents[ $month_number ] ?? 'blue';
							$month_label    = $calendar_data['month_label'] ?? ( $month_group['month_name'] ?? '' );
							?>
							<div
								class="teca-quarterly-layout-2-month teca-quarterly-layout-2-month-accent-<?php echo esc_attr( $month_accent ); ?>"
								data-month="<?php echo esc_attr( sprintf( '%02d', $month_number ) ); ?>"
								data-year="<?php echo esc_attr( (string) $quarter_year ); ?>"
							>
								<div class="teca-quarterly-layout-2-month-header">
									<h4 class="teca-quarterly-layout-2-month-name"><?php echo esc_html( $month_label ); ?></h4>
								</div>

								<div class="teca-quarterly-layout-2-weekdays" aria-hidden="true">
									<?php foreach ( $weekday_labels as $weekday_label ) : ?>
										<div class="teca-quarterly-layout-2-weekday"><?php echo esc_html( strtoupper( $weekday_label ) ); ?></div>
									<?php endforeach; ?>
								</div>

								<div class="teca-quarterly-layout-2-month-grid">
									<?php foreach ( $calendar_data['weeks'] as $week ) : ?>
										<?php foreach ( $week as $cell ) : ?>
											<?php if ( 'empty' === ( $cell['type'] ?? '' ) ) : ?>
												<div class="teca-quarterly-layout-2-day-cell teca-quarterly-layout-2-day-cell-empty" aria-hidden="true"></div>
											<?php else : ?>
												<?php
												$day_events   = $cell['events'] ?? array();
												$visible      = array_slice( $day_events, 0, $max_cell_events );
												$hidden_count = max( 0, count( $day_events ) - count( $visible ) );
												$is_today     = wp_date( 'Y-m-d' ) === ( $cell['date'] ?? '' );
												$cell_class   = 'teca-quarterly-layout-2-day-cell';

												if ( $is_today ) {
													$cell_class .= ' teca-quarterly-layout-2-day-cell-today';
												}
												?>
												<div
													class="<?php echo esc_attr( $cell_class ); ?>"
													data-date="<?php echo esc_attr( $cell['date'] ); ?>"
													data-day="<?php echo esc_attr( (string) $cell['day'] ); ?>"
													data-month="<?php echo esc_attr( sprintf( '%02d', (int) $cell['month'] ) ); ?>"
													data-year="<?php echo esc_attr( (string) $cell['year'] ); ?>"
												>
													<div class="teca-quarterly-layout-2-day-number"><?php echo esc_html( (string) $cell['day'] ); ?></div>

													<?php if ( ! empty( $visible ) ) : ?>
														<div class="teca-quarterly-layout-2-day-events">
															<?php foreach ( $visible as $event ) : ?>
																<?php
																$event_id     = (int) ( $event['event_id'] ?? 0 );
																$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
																																$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
																$venue        = teca_get_event_venue_display( $event );
																$accent_slug  = teca_get_quarterly_layout_2_accent_slug( $event_index );
																$event_index++;
																$event_class  = 'teca-quarterly-layout-2-event teca-quarterly-layout-2-event-accent-' . $accent_slug;
																?>
																<div
																	class="<?php echo esc_attr( $event_class ); ?> teca-calendar-filterable-event"
																	<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
																>
																	<?php if ( ! empty( teca_get_event_category_names( $event ) ) ) : ?>
																		<span class="teca-quarterly-layout-2-event-badge" aria-hidden="true"></span>
																	<?php endif; ?>
																	<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-quarterly-layout-2-event-categories', 'item_class' => 'teca-event-category teca-quarterly-layout-2-event-category' ) ); ?>

																	<div class="teca-quarterly-layout-2-event-title">
																		<?php if ( $permalink ) : ?>
																			<a class="teca-quarterly-layout-2-event-link" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
																		<?php else : ?>
																			<?php echo esc_html( $event['event_name'] ?? '' ); ?>
																		<?php endif; ?>
																	</div>

																	<?php if ( $time_display ) : ?>
																		<div class="teca-quarterly-layout-2-event-time"><?php echo esc_html( $time_display ); ?></div>
																	<?php endif; ?>

																	<?php if ( ! empty( $venue['name'] ) ) : ?>
																		<div class="teca-quarterly-layout-2-event-venue"><?php echo esc_html( $venue['name'] ); ?></div>
																	<?php endif; ?>
																</div>
															<?php endforeach; ?>

															<?php if ( $hidden_count > 0 ) : ?>
																<div class="teca-quarterly-layout-2-more-events">
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

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
