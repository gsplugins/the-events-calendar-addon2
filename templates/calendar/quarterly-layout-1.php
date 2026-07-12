<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$events         = $events ?? array();
$layout_data    = $layout_data ?? teca_build_quarterly_layout_1_data( $events );
$schedule_title = $schedule_title ?? __( 'Events Schedule', 'the-events-calendar-addon2' );
$layout_id      = $layout_id ?? 'teca';
$max_events     = isset( $max_events ) ? (int) $max_events : 3;
$year           = (int) ( $layout_data['year'] ?? wp_date( 'Y' ) );
$years_label    = teca_get_quarterly_layout_years_label( $layout_data );
$quarters       = $layout_data['quarters'] ?? array();
$month_accents  = array(
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
?>

<div class="teca-quarterly-layout-1 teca-quarterly-layout-1-infographic" data-view="quarterly" data-layout="quarterly-layout-1" data-year="<?php echo esc_attr( (string) $year ); ?>">
	<header class="teca-quarterly-layout-1-header">
		<div class="teca-quarterly-layout-1-header-inner">
			<?php if ( $schedule_title ) : ?>
				<h2 class="teca-quarterly-layout-1-title"><?php echo esc_html( $schedule_title ); ?></h2>
			<?php endif; ?>
			<p class="teca-quarterly-layout-1-subtitle">
				<span class="teca-quarterly-layout-1-year"><?php echo esc_html( $years_label ); ?></span>
			</p>
			<div class="teca-quarterly-layout-1-header-accent" aria-hidden="true">
				<span></span><span></span><span></span><span></span><span></span>
			</div>
		</div>

		<?php if ( ! empty( $events ) ) : ?>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo teca_render_calendar_date_filter( $events, 'quarterly', $layout_id );
			?>
		<?php endif; ?>
	</header>

	<?php if ( empty( $events ) ) : ?>
		<div class="teca-calendar-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon2' ); ?></div>
	<?php else : ?>
		<div class="teca-quarterly-layout-1-body">
			<div class="teca-quarterly-layout-1-quarter-list">
				<?php foreach ( $quarters as $quarter_group ) : ?>
					<?php
					$year_quarter_key = $quarter_group['key'] ?? '';
					$quarter_key      = $quarter_group['quarter_key'] ?? '';
					$quarter_year     = (int) ( $quarter_group['year'] ?? $year );
					$quarter_accent   = $quarter_accents[ $quarter_key ] ?? 'gold';
					?>
					<section
						class="teca-quarterly-layout-1-quarter teca-quarterly-layout-1-quarter-accent-<?php echo esc_attr( $quarter_accent ); ?>"
						data-quarter="<?php echo esc_attr( $year_quarter_key ); ?>"
						data-year="<?php echo esc_attr( (string) $quarter_year ); ?>"
						data-quarter-key="<?php echo esc_attr( $quarter_key ); ?>"
					>
						<div class="teca-quarterly-layout-1-quarter-label">
							<span class="teca-quarterly-layout-1-quarter-ordinal"><?php echo esc_html( $quarter_group['ordinal'] ?? '' ); ?></span>
							<span class="teca-quarterly-layout-1-quarter-name"><?php echo esc_html( $quarter_group['label'] ?? '' ); ?></span>
						</div>

						<div class="teca-quarterly-layout-1-quarter-track">
							<div class="teca-quarterly-layout-1-track-line" aria-hidden="true"></div>

							<div class="teca-quarterly-layout-1-quarter-months">
								<?php foreach ( $quarter_group['months'] as $month_group ) : ?>
									<?php
									$month_number   = (int) ( $month_group['month'] ?? 0 );
									$month_events   = $month_group['events'] ?? array();
									$visible_events = array_slice( $month_events, 0, $max_events );
									$hidden_count   = max( 0, count( $month_events ) - count( $visible_events ) );
									$month_accent   = $month_accents[ $month_number ] ?? 'blue';
									?>
									<article
										class="teca-quarterly-layout-1-month teca-quarterly-layout-1-month-accent-<?php echo esc_attr( $month_accent ); ?>"
										data-month="<?php echo esc_attr( $month_group['month_key'] ?? '' ); ?>"
									>
										<div class="teca-quarterly-layout-1-month-badge">
											<span class="teca-quarterly-layout-1-month-name"><?php echo esc_html( $month_group['month_abbr'] ?? '' ); ?></span>
										</div>

										<div class="teca-quarterly-layout-1-month-content">
											<?php if ( ! empty( $visible_events ) ) : ?>
												<div class="teca-quarterly-layout-1-month-events">
													<?php foreach ( $visible_events as $event ) : ?>
														<?php
														$event_id     = (int) ( $event['event_id'] ?? 0 );
														$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
														$start_date   = $event['dates']['start'] ?? '';
														$date_label   = $event_id
															? teca_format_event_start_date_text( $event_id )
															: ( $start_date ? teca_format_layout_date_string( $start_date ) : '' );
														$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
																												$venue        = teca_get_event_venue_display( $event );
														$organizer    = teca_get_event_organizer_name( $event );
														$cost_display = $event_id ? teca_get_event_cost_display( $event_id ) : '';
														?>
														<div class="teca-quarterly-layout-1-event teca-calendar-filterable-event"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
															<?php if ( $permalink ) : ?>
																<a class="teca-quarterly-layout-1-event-link" href="<?php echo esc_url( $permalink ); ?>">
																	<span class="teca-quarterly-layout-1-event-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></span>
																</a>
															<?php else : ?>
																<div class="teca-quarterly-layout-1-event-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></div>
															<?php endif; ?>

															<?php if ( $date_label ) : ?>
																<div class="teca-quarterly-layout-1-event-date"><?php echo esc_html( $date_label ); ?></div>
															<?php endif; ?>

															<?php if ( $time_display ) : ?>
																<div class="teca-quarterly-layout-1-event-time"><?php echo esc_html( $time_display ); ?></div>
															<?php endif; ?>

															<?php teca_render_event_categories( array( 'event' => $event, 'wrapper_class' => 'teca-event-categories teca-quarterly-layout-1-event-categories', 'item_class' => 'teca-event-category teca-quarterly-layout-1-event-category' ) ); ?>

															<?php if ( ! empty( $venue['name'] ) ) : ?>
																<div class="teca-quarterly-layout-1-event-venue"><?php echo esc_html( $venue['name'] ); ?></div>
															<?php endif; ?>

															<?php if ( $organizer ) : ?>
																<div class="teca-quarterly-layout-1-event-organizer"><?php echo esc_html( $organizer ); ?></div>
															<?php endif; ?>

															<?php if ( $cost_display ) : ?>
																<div class="teca-quarterly-layout-1-event-cost"><?php echo esc_html( $cost_display ); ?></div>
															<?php endif; ?>

														</div>
													<?php endforeach; ?>

													<?php if ( $hidden_count > 0 ) : ?>
														<div class="teca-quarterly-layout-1-more-events">
															<?php
															printf(
																/* translators: %d: additional event count */
																esc_html( _n( '+%d more event', '+%d more events', $hidden_count, 'the-events-calendar-addon2' ) ),
																(int) $hidden_count
															);
															?>
														</div>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										</div>
									</article>
								<?php endforeach; ?>
							</div>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
