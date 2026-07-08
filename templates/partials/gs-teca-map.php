<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event_id      = isset( $event_id ) ? (int) $event_id : 0;
$map_data      = isset( $map_data ) && is_array( $map_data ) ? $map_data : teca_get_single_event_map_data( $event_id );
$style_key     = isset( $style_key ) ? (string) $style_key : '';
$visible_class = isset( $visible_class ) ? (string) $visible_class : 'teca-single-element teca-single-element-map teca-single-map';

if ( empty( $map_data['has_location'] ) ) {
	return;
}

$embed_url      = teca_get_single_event_map_embed_url( $map_data );
$external_link  = teca_get_single_event_map_external_link( $map_data );
$iframe_title   = __( 'Event location map', 'the-events-calendar-addon' );
$style_class    = '' !== $style_key ? ' teca-single-map--' . sanitize_html_class( $style_key ) : '';
$has_map_output = ! empty( $map_data['embed_html'] ) || '' !== $embed_url;

if ( ! $has_map_output ) {
	return;
}
?>

<section class="<?php echo esc_attr( trim( $visible_class . $style_class ) ); ?>">
	<div class="teca-single-map-card">
		<div class="teca-single-map-frame">
			<?php if ( ! empty( $map_data['embed_html'] ) ) : ?>
				<?php echo wp_kses( $map_data['embed_html'], teca_get_single_event_map_allowed_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php elseif ( '' !== $embed_url ) : ?>
				<iframe
					src="<?php echo esc_url( $embed_url ); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					title="<?php echo esc_attr( $iframe_title ); ?>"
				></iframe>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $map_data['venue_name'] ) || ! empty( $map_data['full_address'] ) || '' !== $external_link ) : ?>
			<div class="teca-single-map-info">
				<?php if ( ! empty( $map_data['venue_name'] ) ) : ?>
					<div class="teca-single-map-venue"><?php echo esc_html( (string) $map_data['venue_name'] ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $map_data['full_address'] ) ) : ?>
					<div class="teca-single-map-address"><?php echo esc_html( (string) $map_data['full_address'] ); ?></div>
				<?php endif; ?>

				<?php if ( '' !== $external_link ) : ?>
					<a class="teca-single-map-link" href="<?php echo esc_url( $external_link ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View on Map', 'the-events-calendar-addon' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
