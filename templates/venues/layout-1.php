<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$venues = $venues ?? array();
?>
<div class="teca-venue-template teca-venue-template-layout-1">
	<div class="teca-venue-template-header">
		<span class="teca-venue-template-eyebrow"><?php esc_html_e( 'Event Venues', 'the-events-calendar-addon' ); ?></span>
		<h2 class="teca-venue-template-title"><?php esc_html_e( 'Explore Our Venues', 'the-events-calendar-addon' ); ?></h2>
		<p class="teca-venue-template-description"><?php esc_html_e( 'Find event locations, contact details, and venue information in one place.', 'the-events-calendar-addon' ); ?></p>
	</div>

	<?php if ( empty( $venues ) ) : ?>
		<p class="teca-venue-template-empty"><?php esc_html_e( 'No venues found.', 'the-events-calendar-addon' ); ?></p>
	<?php else : ?>
		<div class="teca-venue-grid">
			<?php foreach ( $venues as $venue ) : ?>
				<?php
				if ( ! is_array( $venue ) ) {
					continue;
				}

				$venue_id        = absint( $venue['id'] ?? 0 );
				$title           = trim( (string) ( $venue['title'] ?? '' ) );
				$permalink       = ! empty( $venue['permalink'] ) ? (string) $venue['permalink'] : '';
				$thumbnail       = ! empty( $venue['thumbnail'] ) ? (string) $venue['thumbnail'] : '';
				$full_address    = trim( (string) ( $venue['full_address'] ?? '' ) );
				$phone           = trim( (string) ( $venue['phone'] ?? '' ) );
				$website         = trim( (string) ( $venue['website'] ?? '' ) );
				$map_link        = trim( (string) ( $venue['map_link'] ?? '' ) );
				$upcoming_count  = absint( $venue['upcoming_count'] ?? 0 );
				$location_chip   = teca_get_venue_location_chip_label( $venue );
				$count_chip      = teca_get_venue_upcoming_count_chip_label( $upcoming_count );
				$fallback_initial = teca_get_venue_fallback_initial( $venue );
				$card_title      = $title ?: __( 'Venue', 'the-events-calendar-addon' );
				?>
				<article class="teca-venue-card"<?php echo $venue_id ? ' data-venue-id="' . esc_attr( (string) $venue_id ) . '"' : ''; ?>>
					<div class="teca-venue-card-media">
						<?php if ( $thumbnail ) : ?>
							<img
								class="teca-venue-card-image"
								src="<?php echo esc_url( $thumbnail ); ?>"
								alt="<?php echo esc_attr( $title ); ?>"
								loading="lazy"
								decoding="async"
							/>
						<?php else : ?>
							<div class="teca-venue-card-fallback" aria-hidden="true">
								<span class="teca-venue-card-fallback-icon">
									<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
										<path d="M12 21s7-4.35 7-10a7 7 0 1 0-14 0c0 5.65 7 10 7 10Z" stroke="currentColor" stroke-width="1.8"/>
										<circle cx="12" cy="11" r="2.5" stroke="currentColor" stroke-width="1.8"/>
									</svg>
								</span>
								<span class="teca-venue-card-fallback-initial"><?php echo esc_html( $fallback_initial ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $location_chip ) : ?>
							<span class="teca-venue-card-media-badge"><?php echo esc_html( $location_chip ); ?></span>
						<?php endif; ?>
					</div>

					<div class="teca-venue-card-body">
						<div class="teca-venue-card-top">
							<?php if ( $location_chip ) : ?>
								<span class="teca-venue-location-chip"><?php echo esc_html( $location_chip ); ?></span>
							<?php endif; ?>
							<span class="teca-venue-count-chip" title="<?php echo esc_attr( teca_get_venue_upcoming_count_label( $upcoming_count ) ); ?>">
								<?php echo esc_html( $count_chip ); ?>
							</span>
						</div>

						<h3 class="teca-venue-card-title">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $card_title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $card_title ); ?>
							<?php endif; ?>
						</h3>

						<?php if ( $full_address ) : ?>
							<div class="teca-venue-address"><?php echo esc_html( $full_address ); ?></div>
						<?php endif; ?>

						<?php if ( $phone || $website ) : ?>
							<div class="teca-venue-contact-list">
								<?php if ( $phone ) : ?>
									<a class="teca-venue-contact-item" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d\+]/', '', $phone ) ); ?>">
										<?php echo esc_html( $phone ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<a class="teca-venue-contact-item teca-venue-contact-item--website" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Website', 'the-events-calendar-addon' ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="teca-venue-card-actions">
							<?php if ( $permalink ) : ?>
								<a class="teca-venue-btn teca-venue-btn-primary" href="<?php echo esc_url( $permalink ); ?>">
									<?php esc_html_e( 'View Venue', 'the-events-calendar-addon' ); ?>
								</a>
							<?php endif; ?>

							<?php if ( $map_link ) : ?>
								<a class="teca-venue-btn teca-venue-btn-secondary" href="<?php echo esc_url( $map_link ); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Map', 'the-events-calendar-addon' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
