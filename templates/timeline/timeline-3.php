<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card_partial = Template_Loader::locate_template( 'timeline/partials/event-card-content-timeline-3.php' );
?>
<?php if ( empty( $events ) ) : ?>
	<div class="teca-timeline-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></div>
<?php else : ?>
	<div class="teca-timeline teca-timeline-3 teca-timeline-3-agenda">
		<div class="teca-timeline-3-list">
			<?php foreach ( $events as $event ) : ?>
				<?php
				$event_id   = (int) ( $event['event_id'] ?? 0 );
				$classes    = array( 'teca-timeline-item', 'teca-timeline-3-item' );
				$date_parts = teca_get_timeline_date_parts( $event );
				$has_date   = ! empty( $date_parts['day'] ) || ! empty( $date_parts['month'] ) || ! empty( $date_parts['year'] );

				if ( ! empty( $gs_teca_link_type ) && 'popup' === $gs_teca_link_type ) {
					$classes[] = 'single-member-pop';
				}

				$term_classes = gs_teca_get_the_term_classes( $event_id, $view_type, $gs_filters_by ?? '' );
				if ( ! is_wp_error( $term_classes ) && ! empty( $term_classes ) ) {
					$classes[] = $term_classes;
				}
				?>
				<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo teca_get_events_section_item_attributes_html( $event, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php if ( $has_date && teca_is_card_field_visible( 'event_date', $visibility_settings ?? null ) ) : ?>
						<div class="teca-timeline-3-date-column">
							<div class="teca-timeline-3-date-card gs-teca-date teca-event-date">
								<?php if ( ! empty( $date_parts['month'] ) ) : ?>
									<span class="teca-timeline-3-month"><?php echo esc_html( $date_parts['month'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $date_parts['day'] ) ) : ?>
									<span class="teca-timeline-3-day"><?php echo esc_html( $date_parts['day'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $date_parts['year'] ) ) : ?>
									<span class="teca-timeline-3-year"><?php echo esc_html( $date_parts['year'] ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="teca-timeline-3-content-column">
						<div class="teca-timeline-card teca-timeline-3-card">
							<?php if ( ! is_wp_error( $card_partial ) ) : include $card_partial; endif; ?>
						</div>
					</div>

					<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
