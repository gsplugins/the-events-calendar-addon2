<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event_id = isset( $event_id ) ? absint( $event_id ) : 0;
$settings = isset( $settings ) && is_array( $settings ) ? $settings : array();

if ( ! $event_id ) {
	return;
}

$post = get_post( $event_id );

if ( ! $post || Query::CPT_EVENT !== $post->post_type || 'publish' !== $post->post_status ) {
	return;
}

$permalink = get_permalink( $event_id );
$title     = get_the_title( $event_id );
$image_url = get_the_post_thumbnail_url( $event_id, 'medium' );
$date      = teca_get_related_event_date_label( $event_id );

$categories = get_the_terms( $event_id, 'tribe_events_cat' );
$venue_name = '';

if ( class_exists( __NAMESPACE__ . '\\Query' ) ) {
	$venue_id = Query::get_event_venue_id( $event_id );
	if ( $venue_id ) {
		$venue_name = get_the_title( $venue_id );
	}
} elseif ( function_exists( 'tribe_get_venue' ) ) {
	$venue_name = (string) tribe_get_venue( $event_id );
}

if ( ! $permalink || ! $title ) {
	return;
}
?>
<article class="teca-related-events__item">
	<?php if ( $image_url ) : ?>
		<a class="teca-related-events__image" href="<?php echo esc_url( $permalink ); ?>">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
		</a>
	<?php endif; ?>

	<div class="teca-related-events__content">
		<h3 class="teca-related-events__event-title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h3>

		<?php if ( $date || $venue_name || ( is_array( $categories ) && ! empty( $categories ) ) ) : ?>
			<div class="teca-related-events__meta">
				<?php if ( $date ) : ?>
					<span class="teca-related-events__meta-date"><?php echo esc_html( $date ); ?></span>
				<?php endif; ?>

				<?php if ( is_array( $categories ) && ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
					<span class="teca-related-events__meta-category">
						<?php
						$cat_names = wp_list_pluck( $categories, 'name' );
						echo esc_html( implode( ', ', array_map( 'wp_strip_all_tags', $cat_names ) ) );
						?>
					</span>
				<?php endif; ?>

				<?php if ( $venue_name ) : ?>
					<span class="teca-related-events__meta-venue"><?php echo esc_html( $venue_name ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="teca-related-events__actions">
			<a class="teca-related-events__view-details teca-event-button" href="<?php echo esc_url( $permalink ); ?>">
				<?php echo esc_html( teca_get_view_details_text() ); ?>
			</a>
		</div>
	</div>
</article>
