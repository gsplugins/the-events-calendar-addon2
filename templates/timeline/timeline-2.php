<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card_partial = Template_Loader::locate_template( 'timeline/partials/event-card-content-timeline-2.php' );
$index        = 0;
?>
<div class="teca-timeline teca-timeline-2 teca-timeline-2-axis">
	<div class="teca-timeline-2-track">
		<?php foreach ( $events as $event ) : ?>
			<?php
			$event_id  = (int) ( $event['event_id'] ?? 0 );
			$position  = 0 === $index % 2 ? 'teca-timeline-2-item-top' : 'teca-timeline-2-item-bottom';
			$classes   = array( 'teca-timeline-item', 'teca-timeline-2-item', $position );
			$date_pill = teca_get_timeline_date_pill( $event );

			if ( ! empty( $gs_teca_link_type ) && 'popup' === $gs_teca_link_type ) {
				$classes[] = 'single-member-pop';
			}

			$term_classes = gs_teca_get_the_term_classes( $event_id, $view_type, $gs_filters_by ?? '' );
			if ( ! is_wp_error( $term_classes ) && ! empty( $term_classes ) ) {
				$classes[] = $term_classes;
			}
			?>
			<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo teca_get_events_section_item_attributes_html( $event, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php if ( 'teca-timeline-2-item-top' === $position ) : ?>
					<div class="teca-timeline-card teca-timeline-2-card">
						<?php if ( ! is_wp_error( $card_partial ) ) : include $card_partial; endif; ?>
					</div>

					<div class="teca-timeline-2-marker-wrap">
						<span class="teca-timeline-2-connector" aria-hidden="true"></span>
						<?php if ( $date_pill && teca_is_card_field_visible( 'event_date', $visibility_settings ?? null ) ) : ?>
							<span class="teca-timeline-2-date-pill gs-teca-date teca-event-date"><?php echo esc_html( $date_pill ); ?></span>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="teca-timeline-2-marker-wrap">
						<?php if ( $date_pill && teca_is_card_field_visible( 'event_date', $visibility_settings ?? null ) ) : ?>
							<span class="teca-timeline-2-date-pill gs-teca-date teca-event-date"><?php echo esc_html( $date_pill ); ?></span>
						<?php endif; ?>
						<span class="teca-timeline-2-connector" aria-hidden="true"></span>
					</div>

					<div class="teca-timeline-card teca-timeline-2-card">
						<?php if ( ! is_wp_error( $card_partial ) ) : include $card_partial; endif; ?>
					</div>
				<?php endif; ?>

				<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>
			</article>
			<?php
			++$index;
		endforeach;
		?>
	</div>
</div>
