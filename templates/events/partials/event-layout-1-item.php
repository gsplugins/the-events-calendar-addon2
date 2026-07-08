<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event         = $event ?? array();
$event_group   = $event_group ?? '';
$event_id      = (int) ( $event['event_id'] ?? 0 );
$permalink     = $event_id ? get_the_permalink( $event_id ) : '';
$date_parts    = teca_get_event_layout_1_date_parts( $event );
$meta_line     = teca_get_event_layout_1_meta_line( $event, $event_id );
$venue_display = teca_get_event_layout_1_venue_name( $event, $event_id );
$excerpt       = $event_id ? teca_get_event_excerpt_text( $event_id, 35 ) : '';
$title         = $event['event_name'] ?? '';
?>
<article class="teca-event-item teca-event-layout-1-item"<?php echo teca_get_events_section_item_attributes_html( $event, $event_group ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $date_parts['day'] || $date_parts['month'] || $date_parts['year'] ) : ?>
		<div class="teca-event-layout-1-date">
			<?php if ( $date_parts['day'] ) : ?>
				<span class="teca-event-layout-1-day"><?php echo esc_html( $date_parts['day'] ); ?></span>
			<?php endif; ?>
			<?php if ( $date_parts['month'] ) : ?>
				<span class="teca-event-layout-1-month"><?php echo esc_html( $date_parts['month'] ); ?></span>
			<?php endif; ?>
			<?php if ( $date_parts['year'] ) : ?>
				<span class="teca-event-layout-1-year"><?php echo esc_html( $date_parts['year'] ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="teca-event-layout-1-content">
		<?php if ( $title && $permalink ) : ?>
			<h3 class="teca-event-layout-1-title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
			</h3>
		<?php elseif ( $title ) : ?>
			<h3 class="teca-event-layout-1-title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<?php if ( $venue_display ) : ?>
			<span class="teca-event-layout-1-venue"><?php echo esc_html( $venue_display ); ?></span>
		<?php endif; ?>

		<?php if ( $meta_line ) : ?>
			<div class="teca-event-layout-1-meta"><?php echo esc_html( $meta_line ); ?></div>
		<?php endif; ?>

		<?php if ( $excerpt ) : ?>
			<div class="teca-event-layout-1-excerpt"><?php echo esc_html( $excerpt ); ?></div>
		<?php endif; ?>
	</div>

	<?php if ( $permalink ) : ?>
		<div class="teca-event-layout-1-action">
			<a class="teca-event-layout-1-button" href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'Learn More', 'the-events-calendar-addon' ); ?>
			</a>
			<?php
			teca_echo_google_calendar_button_actions(
				$event_id,
				'card',
				$visibility_settings ?? null,
				'teca-event-layout-1-google-calendar-wrap',
				array( 'google_calendar_url' => $event['google_calendar_url'] ?? '' )
			);
			?>
		</div>
	<?php endif; ?>
</article>
