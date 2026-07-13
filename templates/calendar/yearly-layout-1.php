<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$events         = $events ?? array();
$layout_data    = $layout_data ?? teca_build_yearly_layout_1_data( $events );
$schedule_title = $schedule_title ?? __( 'Events Schedule', 'the-events-calendar-addon' );
$layout_id      = $layout_id ?? 'teca';
$max_events     = isset( $max_events ) ? (int) $max_events : 5;
$years          = $layout_data['years'] ?? array();
$years_label    = $layout_data['years_label'] ?? (string) wp_date( 'Y' );
$month_accents  = array(
	1  => 'pink',
	2  => 'rose',
	3  => 'coral',
	4  => 'orange',
	5  => 'amber',
	6  => 'sunset',
	7  => 'magenta',
	8  => 'peach',
	9  => 'blush',
	10 => 'ember',
	11 => 'gold',
	12 => 'flame',
);
?>

<div class="teca-calendar-layout teca-calendar-yearly teca-yearly-layout-1 teca-yearly-layout-1-board" data-view="yearly" data-layout="yearly-layout-1">
	<header class="teca-yearly-layout-1-header">
		<div class="teca-yearly-layout-1-header-main">
			<?php if ( $schedule_title ) : ?>
				<h2 class="teca-yearly-layout-1-title"><?php echo esc_html( $schedule_title ); ?></h2>
			<?php endif; ?>
			<p class="teca-yearly-layout-1-subtitle">
				<span class="teca-yearly-layout-1-years-label"><?php echo esc_html( $years_label ); ?></span>
			</p>
		</div>

		<div class="teca-yearly-layout-1-toolbar">
			<?php if ( ! empty( $events ) ) : ?>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo teca_render_calendar_date_filter( $events, 'yearly', $layout_id );
				?>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( empty( $events ) ) : ?>
		<div class="teca-calendar-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></div>
	<?php else : ?>
		<?php foreach ( $years as $year_group ) : ?>
			<?php $board_year = (int) ( $year_group['year'] ?? 0 ); ?>
			<section class="teca-yearly-layout-1-year teca-yearly-year-section" data-year="<?php echo esc_attr( (string) $board_year ); ?>">
				<div class="teca-yearly-layout-1-year-header">
					<h3 class="teca-yearly-layout-1-year-label"><?php echo esc_html( (string) $board_year ); ?></h3>
				</div>

				<div class="teca-yearly-layout-1-body">
					<div class="teca-yearly-layout-1-grid">
						<?php foreach ( $year_group['months'] as $month_group ) : ?>
							<?php
							$month_number   = (int) ( $month_group['month'] ?? 0 );
							$month_events   = $month_group['events'] ?? array();
							$visible_events = array_slice( $month_events, 0, $max_events );
							$hidden_count   = max( 0, count( $month_events ) - count( $visible_events ) );
							$month_accent   = $month_accents[ $month_number ] ?? 'pink';
							?>
							<div
								class="teca-yearly-layout-1-month teca-yearly-layout-1-month-accent-<?php echo esc_attr( $month_accent ); ?>"
								data-month="<?php echo esc_attr( sprintf( '%02d', $month_number ) ); ?>"
								data-year="<?php echo esc_attr( (string) $board_year ); ?>"
							>
								<div class="teca-yearly-layout-1-month-header">
									<span class="teca-yearly-layout-1-month-accent" aria-hidden="true"></span>
									<h4 class="teca-yearly-layout-1-month-name"><?php echo esc_html( $month_group['month_name'] ?? '' ); ?></h4>
								</div>

								<?php if ( ! empty( $visible_events ) ) : ?>
									<div class="teca-yearly-layout-1-month-events">
										<?php foreach ( $visible_events as $event ) : ?>
											<?php
											$event_id     = (int) ( $event['event_id'] ?? 0 );
											$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
											$start_date   = $event['dates']['start'] ?? '';
											$day_number   = $start_date ? wp_date( 'j', strtotime( $start_date ) ) : '';
											$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
																						$venue        = teca_get_event_venue_display( $event );
											$cost_display = $event_id ? teca_get_event_cost_display( $event_id ) : '';
											?>
											<div class="teca-yearly-layout-1-event teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
												<?php if ( $day_number ) : ?>
													<span class="teca-yearly-layout-1-event-day"><?php echo esc_html( (string) $day_number ); ?></span>
												<?php endif; ?>

												<div class="teca-yearly-layout-1-event-content">
													<div class="teca-yearly-layout-1-event-title">
														<?php if ( $permalink ) : ?>
															<a class="teca-yearly-layout-1-event-link" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $event['event_name'] ?? '' ); ?></a>
														<?php else : ?>
															<?php echo esc_html( $event['event_name'] ?? '' ); ?>
														<?php endif; ?>
													</div>

													<?php if ( $time_display ) : ?>
														<div class="teca-yearly-layout-1-event-time"><?php echo esc_html( $time_display ); ?></div>
													<?php endif; ?>

													<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-yearly-layout-1-event-categories', 'item_class' => 'teca-event-category teca-yearly-layout-1-event-category' ) ); ?>

													<?php if ( ! empty( $venue['name'] ) ) : ?>
														<div class="teca-yearly-layout-1-event-venue"><?php echo esc_html( $venue['name'] ); ?></div>
													<?php endif; ?>

													<?php if ( $cost_display ) : ?>
														<div class="teca-yearly-layout-1-event-cost"><?php echo esc_html( $cost_display ); ?></div>
													<?php endif; ?>

												</div>
											</div>
										<?php endforeach; ?>

										<?php if ( $hidden_count > 0 ) : ?>
											<div class="teca-yearly-layout-1-more-events">
												<?php
												printf(
													/* translators: %d: additional event count */
													esc_html( _n( '+%d more event', '+%d more events', $hidden_count, 'the-events-calendar-addon' ) ),
													(int) $hidden_count
												);
												?>
											</div>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
