<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card_partial = Template_Loader::locate_template( 'timeline/partials/event-card-content.php' );
$index        = 0;
?>
<div class="teca-timeline teca-timeline-1">
	<div class="teca-timeline-1-track">
		<?php foreach ( $events as $event ) : ?>
			<?php
			$event_id = (int) ( $event['event_id'] ?? 0 );
			$side     = 0 === $index % 2 ? 'teca-timeline-item-left' : 'teca-timeline-item-right';
			$classes  = array( 'teca-timeline-item', 'teca-timeline-1-item', $side );

			if ( ! empty( $gs_teca_link_type ) && 'popup' === $gs_teca_link_type ) {
				$classes[] = 'single-member-pop';
			}

			$term_classes = gs_teca_get_the_term_classes( $event_id, $view_type, $gs_filters_by ?? '' );
			if ( ! is_wp_error( $term_classes ) && ! empty( $term_classes ) ) {
				$classes[] = $term_classes;
			}

			$date_badge   = teca_get_timeline_date_badge( $event );
			$show_card_date = false;
			?>
			<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo teca_get_events_section_item_attributes_html( $event, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<div class="teca-timeline-1-marker">
					<span class="teca-timeline-1-dot"></span>
					<?php if ( $date_badge && teca_is_card_field_visible( 'event_date', $visibility_settings ?? null ) ) : ?>
						<span class="teca-timeline-1-date-badge gs-teca-date teca-event-date"><?php echo esc_html( $date_badge ); ?></span>
					<?php endif; ?>
				</div>

				<div class="teca-timeline-card teca-timeline-1-card">
					<?php if ( ! is_wp_error( $card_partial ) ) : include $card_partial; endif; ?>
				</div>

				<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>
			</article>
			<?php
			++$index;
		endforeach;
		?>
	</div>
</div>
