<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$panel_partial = Template_Loader::locate_template( 'accordion/partials/panel-content.php' );
$index         = 0;
?>
<?php if ( empty( $events ) ) : ?>
	<div class="teca-accordion-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></div>
<?php else : ?>
	<div class="teca-accordion teca-accordion-1">
		<?php foreach ( $events as $event ) : ?>
			<?php
			$event_id   = (int) ( $event['event_id'] ?? 0 );
			$is_first   = 0 === $index;
			$classes    = array( 'teca-accordion-item', 'teca-accordion-1-item' );
			$date_parts = teca_get_timeline_date_parts( $event );
			$title      = $event['event_name'] ?? ( $event_id ? get_the_title( $event_id ) : '' );
			$meta       = teca_get_accordion_meta_summary( $event, $event_id );
			$panel_id   = 'teca-accordion-1-panel-' . sanitize_key( (string) $id ) . '-' . $event_id;
			$trigger_id = 'teca-accordion-1-trigger-' . sanitize_key( (string) $id ) . '-' . $event_id;

			if ( $is_first ) {
				$classes[] = 'is-active';
			}

			if ( ! empty( $gs_teca_link_type ) && 'popup' === $gs_teca_link_type ) {
				$classes[] = 'single-member-pop';
			}

			$term_classes = gs_teca_get_the_term_classes( $event_id, $view_type, $gs_filters_by ?? '' );
			if ( ! is_wp_error( $term_classes ) && ! empty( $term_classes ) ) {
				$classes[] = $term_classes;
			}
			?>
			<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo teca_get_events_section_item_attributes_html( $event, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<button
					id="<?php echo esc_attr( $trigger_id ); ?>"
					class="teca-accordion-trigger teca-accordion-1-trigger"
					type="button"
					aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $panel_id ); ?>"
				>
					<?php if ( ! empty( $date_parts['day'] ) || ! empty( $date_parts['month'] ) ) : ?>
						<?php if ( teca_is_card_field_visible( 'event_date', $visibility_settings ?? null ) ) : ?>
						<span class="teca-accordion-1-date gs-teca-date teca-event-date">
							<?php if ( ! empty( $date_parts['day'] ) ) : ?>
								<span class="teca-accordion-1-day"><?php echo esc_html( $date_parts['day'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $date_parts['month'] ) ) : ?>
								<span class="teca-accordion-1-month"><?php echo esc_html( $date_parts['month'] ); ?></span>
							<?php endif; ?>
						</span>
						<?php endif; ?>
					<?php endif; ?>

					<span class="teca-accordion-1-summary">
						<?php if ( teca_is_card_field_visible( 'event_title', $visibility_settings ?? null ) && $title ) : ?>
							<span class="teca-accordion-1-title gs-teca-title teca-event-title"><?php echo esc_html( $title ); ?></span>
						<?php endif; ?>
						<?php if ( $meta ) : ?>
							<span class="teca-accordion-1-meta gs-teca-date teca-event-date"><?php echo esc_html( $meta ); ?></span>
						<?php endif; ?>
					</span>

					<span class="teca-accordion-1-icon" aria-hidden="true"></span>
				</button>

				<div
					id="<?php echo esc_attr( $panel_id ); ?>"
					class="teca-accordion-panel teca-accordion-1-panel"
					role="region"
					aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
					<?php echo $is_first ? '' : ' hidden'; ?>
				>
					<?php if ( ! is_wp_error( $panel_partial ) ) : include $panel_partial; endif; ?>
				</div>

				<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>
			</article>
			<?php
			++$index;
		endforeach;
		?>
	</div>
<?php endif; ?>
