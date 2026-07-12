<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event        = $event ?? array();
$item_class   = $item_class ?? 'teca-event-layout-1-item';
$event_group  = $event_group ?? '';
$event_id     = (int) ( $event['event_id'] ?? 0 );
$permalink    = $event_id ? get_the_permalink( $event_id ) : '';
$start_date   = $event['dates']['start'] ?? '';
$end_date     = $event['dates']['end'] ?? '';
$date_display = $event_id
	? teca_format_event_start_date_text( $event_id )
	: ( $start_date ? teca_format_layout_date_string( $start_date ) : '' );
$end_date_text = $event_id
	? teca_format_event_end_date_text( $event_id )
	: ( $end_date ? teca_format_layout_date_string( $end_date ) : '' );
$end_display  = $end_date && $end_date_text
	? $end_date_text . ' ' . wp_date( get_option( 'time_format' ), strtotime( $end_date ) )
	: '';
$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
$category_names = teca_get_event_category_names( $event );
$tag_names      = teca_get_event_tag_names( $event );
$venue        = teca_get_event_venue_display( $event );
$cost_display = $event_id ? teca_get_event_cost_display( $event_id ) : '';
$excerpt      = $event_id ? teca_get_event_excerpt_text( $event_id, 35 ) : '';
$has_image    = $event_id && has_post_thumbnail( $event_id );
?>
<article class="teca-event-item <?php echo esc_attr( $item_class ); ?>"<?php echo teca_get_events_section_item_attributes_html( $event, $event_group ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $has_image ) : ?>
		<div class="teca-event-image-wrap">
			<?php if ( $permalink ) : ?>
				<a class="teca-event-image-link" href="<?php echo esc_url( $permalink ); ?>">
					<?php echo get_the_post_thumbnail( $event_id, 'medium', array( 'class' => 'teca-event-image' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php else : ?>
				<?php echo get_the_post_thumbnail( $event_id, 'medium', array( 'class' => 'teca-event-image' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $permalink ) : ?>
		<a class="teca-event-link" href="<?php echo esc_url( $permalink ); ?>">
			<h4 class="teca-event-title"><?php echo esc_html( $event['event_name'] ?? '' ); ?></h4>
		</a>
	<?php elseif ( ! empty( $event['event_name'] ) ) : ?>
		<h4 class="teca-event-title"><?php echo esc_html( $event['event_name'] ); ?></h4>
	<?php endif; ?>

	<?php if ( $date_display || $time_display || $end_display ) : ?>
		<div class="teca-event-meta">
			<?php if ( $date_display ) : ?>
				<span class="teca-event-date"><?php echo esc_html( $date_display ); ?></span>
			<?php endif; ?>
			<?php if ( $time_display ) : ?>
				<span class="teca-event-time"><?php echo esc_html( $time_display ); ?></span>
			<?php endif; ?>
			<?php if ( $end_display ) : ?>
				<span class="teca-event-end"><?php echo esc_html( $end_display ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $category_names ) ) : ?>
		<div class="teca-event-categories teca-event-item-categories">
			<?php foreach ( $category_names as $category_name ) : ?>
				<span class="teca-event-category teca-event-item-category"><?php echo esc_html( $category_name ); ?></span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $tag_names ) ) : ?>
		<div class="teca-event-tags teca-event-item-tags">
			<?php foreach ( $tag_names as $tag_name ) : ?>
				<span class="teca-event-tag teca-event-item-tag"><?php echo esc_html( $tag_name ); ?></span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $venue['name'] ) ) : ?>
		<div class="teca-event-venue"><?php echo esc_html( $venue['name'] ); ?></div>
	<?php endif; ?>

	<?php if ( $cost_display ) : ?>
		<div class="teca-event-cost"><?php echo esc_html( $cost_display ); ?></div>
	<?php endif; ?>

	<?php if ( $excerpt ) : ?>
		<div class="teca-event-excerpt"><?php echo esc_html( $excerpt ); ?></div>
	<?php endif; ?>

	<?php
	teca_echo_google_calendar_button_actions(
		$event_id,
		'card',
		$visibility_settings ?? null,
		'teca-event-item-google-calendar-wrap',
		array( 'google_calendar_url' => $event['google_calendar_url'] ?? '' )
	);
	?>
</article>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
