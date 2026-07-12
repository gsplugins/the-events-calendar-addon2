<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$venues = $venues ?? array();
?>
<div class="teca-venue-template teca-venue-template-layout-2">
	<div class="teca-venue-l2-hero">
		<div class="teca-venue-l2-hero-copy">
			<span class="teca-venue-l2-eyebrow"><?php esc_html_e( 'Curated Event Spaces', 'the-events-calendar-addon2' ); ?></span>
			<h2 class="teca-venue-l2-title"><?php esc_html_e( 'Discover Premium Venues', 'the-events-calendar-addon2' ); ?></h2>
		</div>
		<p class="teca-venue-l2-description"><?php esc_html_e( 'Explore venue locations, contact details, maps, and event spaces in a refined directory layout.', 'the-events-calendar-addon2' ); ?></p>
		<div class="teca-venue-l2-hero-accent" aria-hidden="true"></div>
	</div>

	<?php if ( empty( $venues ) ) : ?>
		<div class="teca-venue-l2-empty">
			<h3 class="teca-venue-l2-empty-title"><?php esc_html_e( 'No venues found', 'the-events-calendar-addon2' ); ?></h3>
			<p class="teca-venue-l2-empty-text"><?php esc_html_e( 'No venue locations are available right now.', 'the-events-calendar-addon2' ); ?></p>
		</div>
	<?php else : ?>
		<div class="teca-venue-l2-list">
			<?php foreach ( $venues as $venue ) : ?>
				<?php
				if ( ! is_array( $venue ) ) {
					continue;
				}

				$venue_id         = absint( $venue['id'] ?? 0 );
				$title            = trim( (string) ( $venue['title'] ?? '' ) );
				$permalink        = ! empty( $venue['permalink'] ) ? (string) $venue['permalink'] : '';
				$thumbnail        = ! empty( $venue['thumbnail'] ) ? (string) $venue['thumbnail'] : '';
				$full_address     = trim( (string) ( $venue['full_address'] ?? '' ) );
				$phone            = trim( (string) ( $venue['phone'] ?? '' ) );
				$website          = trim( (string) ( $venue['website'] ?? '' ) );
				$map_link         = trim( (string) ( $venue['map_link'] ?? '' ) );
				$country          = trim( (string) ( $venue['country'] ?? '' ) );
				$upcoming_count   = absint( $venue['upcoming_count'] ?? 0 );
				$location_chip    = teca_get_venue_location_chip_label( $venue );
				$count_label      = teca_get_venue_upcoming_count_label( $upcoming_count );
				$fallback_initial = teca_get_venue_fallback_initial( $venue );
				$card_title       = $title ?: __( 'Venue', 'the-events-calendar-addon2' );
				$show_country     = '' !== $country;
				?>
				<article class="teca-venue-l2-card"<?php echo $venue_id ? ' data-venue-id="' . esc_attr( (string) $venue_id ) . '"' : ''; ?>>
					<div class="teca-venue-l2-media">
						<?php if ( $thumbnail ) : ?>
							<img
								class="teca-venue-l2-image"
								src="<?php echo esc_url( $thumbnail ); ?>"
								alt="<?php echo esc_attr( $title ); ?>"
								loading="lazy"
								decoding="async"
							/>
							<span class="teca-venue-l2-media-overlay" aria-hidden="true"></span>
						<?php else : ?>
							<div class="teca-venue-l2-fallback" aria-hidden="true">
								<span class="teca-venue-l2-fallback-icon">
									<svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
										<path d="M12 21s7-4.35 7-10a7 7 0 1 0-14 0c0 5.65 7 10 7 10Z" stroke="currentColor" stroke-width="1.6"/>
										<circle cx="12" cy="11" r="2.5" stroke="currentColor" stroke-width="1.6"/>
									</svg>
								</span>
								<span class="teca-venue-l2-fallback-initial"><?php echo esc_html( $fallback_initial ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="teca-venue-l2-content">
						<?php if ( $location_chip || $upcoming_count > 0 || $show_country ) : ?>
							<div class="teca-venue-l2-meta">
								<?php if ( $location_chip ) : ?>
									<span class="teca-venue-l2-chip teca-venue-l2-chip--location"><?php echo esc_html( $location_chip ); ?></span>
								<?php endif; ?>

								<?php if ( $show_country ) : ?>
									<span class="teca-venue-l2-chip teca-venue-l2-chip--country"><?php echo esc_html( $country ); ?></span>
								<?php endif; ?>

								<?php if ( $upcoming_count > 0 ) : ?>
									<span class="teca-venue-l2-chip teca-venue-l2-chip--count"><?php echo esc_html( $count_label ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<h3 class="teca-venue-l2-card-title">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $card_title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $card_title ); ?>
							<?php endif; ?>
						</h3>

						<?php if ( $full_address ) : ?>
							<p class="teca-venue-l2-address"><?php echo esc_html( $full_address ); ?></p>
						<?php endif; ?>

						<?php if ( $phone || $website ) : ?>
							<div class="teca-venue-l2-details">
								<?php if ( $phone ) : ?>
									<div class="teca-venue-l2-detail-row">
										<span class="teca-venue-l2-detail-label"><?php esc_html_e( 'Phone', 'the-events-calendar-addon2' ); ?></span>
										<a class="teca-venue-l2-detail-value" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d\+]/', '', $phone ) ); ?>">
											<?php echo esc_html( $phone ); ?>
										</a>
									</div>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<div class="teca-venue-l2-detail-row">
										<span class="teca-venue-l2-detail-label"><?php esc_html_e( 'Website', 'the-events-calendar-addon2' ); ?></span>
										<a class="teca-venue-l2-detail-value" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ?: $website ); ?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $permalink || $map_link || $website ) : ?>
							<div class="teca-venue-l2-actions">
								<?php if ( $permalink ) : ?>
									<a class="teca-venue-l2-btn teca-venue-l2-btn-primary" href="<?php echo esc_url( $permalink ); ?>">
										<?php esc_html_e( 'View Venue', 'the-events-calendar-addon2' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $map_link ) : ?>
									<a class="teca-venue-l2-btn teca-venue-l2-btn-secondary" href="<?php echo esc_url( $map_link ); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Open Map', 'the-events-calendar-addon2' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<a class="teca-venue-l2-btn teca-venue-l2-btn-ghost" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Website', 'the-events-calendar-addon2' ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
