<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$venues       = $venues ?? array();
$venue_count  = count( $venues );
$venue_index  = 0;
?>
<div class="teca-venue-template teca-venue-template-layout-3">
	<section class="teca-venue-l3-hero">
		<div class="teca-venue-l3-hero-content">
			<span class="teca-venue-l3-eyebrow"><?php esc_html_e( 'Signature Venues', 'the-events-calendar-addon2' ); ?></span>
			<h2 class="teca-venue-l3-title"><?php esc_html_e( 'Explore Beautiful Event Spaces', 'the-events-calendar-addon2' ); ?></h2>
			<p class="teca-venue-l3-description"><?php esc_html_e( 'Discover curated venues with location details, contact information, and map access in a refined visual showcase.', 'the-events-calendar-addon2' ); ?></p>
		</div>

		<?php if ( $venue_count > 0 ) : ?>
			<div class="teca-venue-l3-stats" aria-label="<?php esc_attr_e( 'Venue statistics', 'the-events-calendar-addon2' ); ?>">
				<span class="teca-venue-l3-stat-number"><?php echo esc_html( (string) $venue_count ); ?></span>
				<span class="teca-venue-l3-stat-label">
					<?php
					echo esc_html(
						1 === $venue_count
							? __( 'Venue Listed', 'the-events-calendar-addon2' )
							: __( 'Venues Listed', 'the-events-calendar-addon2' )
					);
					?>
				</span>
			</div>
		<?php endif; ?>

		<div class="teca-venue-l3-hero-glow" aria-hidden="true"></div>
	</section>

	<?php if ( empty( $venues ) ) : ?>
		<div class="teca-venue-l3-empty">
			<div class="teca-venue-l3-empty-icon" aria-hidden="true">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
					<path d="M12 21s7-4.35 7-10a7 7 0 1 0-14 0c0 5.65 7 10 7 10Z" stroke="currentColor" stroke-width="1.6"/>
					<circle cx="12" cy="11" r="2.5" stroke="currentColor" stroke-width="1.6"/>
				</svg>
			</div>
			<h3 class="teca-venue-l3-empty-title"><?php esc_html_e( 'No venues found', 'the-events-calendar-addon2' ); ?></h3>
			<p class="teca-venue-l3-empty-text"><?php esc_html_e( 'No venue locations are available right now.', 'the-events-calendar-addon2' ); ?></p>
		</div>
	<?php else : ?>
		<div class="teca-venue-l3-bento">
			<?php foreach ( $venues as $venue ) : ?>
				<?php
				if ( ! is_array( $venue ) ) {
					continue;
				}

				++$venue_index;

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
				$count_chip       = teca_get_venue_upcoming_count_chip_label( $upcoming_count );
				$fallback_initial = teca_get_venue_fallback_initial( $venue );
				$card_title       = $title ?: __( 'Venue', 'the-events-calendar-addon2' );
				$is_featured      = 1 === $venue_index;

				$card_classes = array( 'teca-venue-l3-card' );

				if ( $is_featured ) {
					$card_classes[] = 'teca-venue-l3-card-featured';
				}

				if ( 0 === ( $venue_index % 6 ) ) {
					$card_classes[] = 'teca-venue-l3-card-wide';
				}
				?>
				<article class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>"<?php echo $venue_id ? ' data-venue-id="' . esc_attr( (string) $venue_id ) . '"' : ''; ?>>
					<div class="teca-venue-l3-media">
						<?php if ( $thumbnail ) : ?>
							<img
								class="teca-venue-l3-image"
								src="<?php echo esc_url( $thumbnail ); ?>"
								alt="<?php echo esc_attr( $title ); ?>"
								loading="lazy"
								decoding="async"
							/>
						<?php else : ?>
							<div class="teca-venue-l3-fallback" aria-hidden="true">
								<span class="teca-venue-l3-fallback-orb"></span>
								<span class="teca-venue-l3-fallback-initial"><?php echo esc_html( $fallback_initial ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="teca-venue-l3-overlay" aria-hidden="true"></div>

					<div class="teca-venue-l3-topbar">
						<?php if ( $location_chip ) : ?>
							<span class="teca-venue-l3-location-chip"><?php echo esc_html( $location_chip ); ?></span>
						<?php elseif ( $country ) : ?>
							<span class="teca-venue-l3-location-chip"><?php echo esc_html( $country ); ?></span>
						<?php endif; ?>

						<?php if ( $upcoming_count > 0 ) : ?>
							<span class="teca-venue-l3-count-chip"><?php echo esc_html( $count_chip ); ?></span>
						<?php endif; ?>
					</div>

					<div class="teca-venue-l3-content<?php echo $is_featured ? ' teca-venue-l3-content-featured' : ''; ?>">
						<?php if ( $is_featured ) : ?>
							<span class="teca-venue-l3-spotlight"><?php esc_html_e( 'Venue Spotlight', 'the-events-calendar-addon2' ); ?></span>
						<?php endif; ?>

						<h3 class="teca-venue-l3-card-title">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $card_title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $card_title ); ?>
							<?php endif; ?>
						</h3>

						<?php if ( $full_address ) : ?>
							<p class="teca-venue-l3-address"><?php echo esc_html( $full_address ); ?></p>
						<?php endif; ?>

						<?php if ( $is_featured && $phone ) : ?>
							<p class="teca-venue-l3-phone">
								<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d\+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
							</p>
						<?php endif; ?>

						<?php if ( $permalink || $map_link || $website ) : ?>
							<div class="teca-venue-l3-actions">
								<?php if ( $permalink ) : ?>
									<a class="teca-venue-l3-btn teca-venue-l3-btn-primary" href="<?php echo esc_url( $permalink ); ?>">
										<?php esc_html_e( 'View Venue', 'the-events-calendar-addon2' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $map_link ) : ?>
									<a class="teca-venue-l3-btn teca-venue-l3-btn-secondary" href="<?php echo esc_url( $map_link ); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Open Map', 'the-events-calendar-addon2' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<a class="teca-venue-l3-btn teca-venue-l3-btn-ghost" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
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
