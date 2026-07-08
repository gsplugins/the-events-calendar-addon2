<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event         = $event ?? array();
$event_group   = $event_group ?? '';
$event_id      = (int) ( $event['event_id'] ?? 0 );
$permalink     = $event_id ? get_the_permalink( $event_id ) : '';
$date_display  = teca_get_event_layout_2_date_display( $event );
$time_display  = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
$venue_display = teca_get_event_layout_1_venue_name( $event, $event_id );
$category_names = teca_get_event_category_names( $event );
$tag_names      = teca_get_event_tag_names( $event );
$cost_display  = $event_id ? teca_get_event_cost_display( $event_id ) : '';
$excerpt       = $event_id ? teca_get_event_excerpt_text( $event_id, 30 ) : '';
$title         = $event['event_name'] ?? '';
$image_url     = $event_id ? get_the_post_thumbnail_url( $event_id, 'medium' ) : '';
$has_meta      = $time_display || $venue_display || ! empty( $category_names ) || ! empty( $tag_names ) || $cost_display;
?>
<article class="teca-event-item teca-event-layout-2-item"<?php echo teca_get_events_section_item_attributes_html( $event, $event_group ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $image_url ) : ?>
		<div class="teca-event-layout-2-thumb">
			<?php if ( $permalink ) : ?>
				<a class="teca-event-layout-2-thumb-link" href="<?php echo esc_url( $permalink ); ?>">
					<img class="teca-event-layout-2-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
				</a>
			<?php else : ?>
				<img class="teca-event-layout-2-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="teca-event-layout-2-content">
		<?php if ( $date_display ) : ?>
			<div class="teca-event-layout-2-date"><?php echo esc_html( $date_display ); ?></div>
		<?php endif; ?>

		<?php if ( $title && $permalink ) : ?>
			<h3 class="teca-event-layout-2-title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
			</h3>
		<?php elseif ( $title ) : ?>
			<h3 class="teca-event-layout-2-title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<?php if ( $has_meta ) : ?>
			<div class="teca-event-layout-2-meta">
				<?php if ( $time_display ) : ?>
					<span class="teca-event-layout-2-time"><?php echo esc_html( $time_display ); ?></span>
				<?php endif; ?>
				<?php if ( $venue_display ) : ?>
					<span class="teca-event-layout-2-venue"><?php echo esc_html( $venue_display ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $category_names ) ) : ?>
					<?php foreach ( $category_names as $category_name ) : ?>
						<span class="teca-event-category teca-event-layout-2-category"><?php echo esc_html( $category_name ); ?></span>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if ( ! empty( $tag_names ) ) : ?>
					<?php foreach ( $tag_names as $tag_name ) : ?>
						<span class="teca-event-tag teca-event-layout-2-tag"><?php echo esc_html( $tag_name ); ?></span>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if ( $cost_display ) : ?>
					<span class="teca-event-layout-2-cost"><?php echo esc_html( $cost_display ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $excerpt ) : ?>
			<div class="teca-event-layout-2-excerpt"><?php echo esc_html( $excerpt ); ?></div>
		<?php endif; ?>
	</div>

	<?php if ( $permalink ) : ?>
		<div class="teca-event-layout-2-action">
			<a class="teca-event-layout-2-button" href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'Learn More', 'the-events-calendar-addon' ); ?>
			</a>
			<?php
			teca_echo_google_calendar_button_actions(
				$event_id,
				'card',
				$visibility_settings ?? null,
				'teca-event-layout-2-google-calendar-wrap',
				array( 'google_calendar_url' => $event['google_calendar_url'] ?? '' )
			);
			?>
		</div>
	<?php endif; ?>
</article>
