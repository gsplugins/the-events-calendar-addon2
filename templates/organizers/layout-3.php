<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$organizers      = $organizers ?? array();
$organizer_count = count( $organizers );
$organizer_index = 0;
?>

<div class="teca-organizer-template teca-organizer-template-layout-3">
	<section class="teca-organizer-l3-hero">
		<div class="teca-organizer-l3-hero-content">
			<span class="teca-organizer-l3-eyebrow"><?php esc_html_e( 'Organizer Network', 'the-events-calendar-addon' ); ?></span>
			<h2 class="teca-organizer-l3-title"><?php esc_html_e( 'Meet the People Behind the Events', 'the-events-calendar-addon' ); ?></h2>
			<p class="teca-organizer-l3-description"><?php esc_html_e( 'Explore organizer profiles, contact details, and event connections in a refined visual showcase.', 'the-events-calendar-addon' ); ?></p>
		</div>

		<?php if ( $organizer_count > 0 ) : ?>
			<div class="teca-organizer-l3-stat" aria-label="<?php esc_attr_e( 'Organizer statistics', 'the-events-calendar-addon' ); ?>">
				<span class="teca-organizer-l3-stat-number"><?php echo esc_html( (string) $organizer_count ); ?></span>
				<span class="teca-organizer-l3-stat-label">
					<?php
					echo esc_html(
						1 === $organizer_count
							? __( 'Organizer Listed', 'the-events-calendar-addon' )
							: __( 'Organizers Listed', 'the-events-calendar-addon' )
					);
					?>
				</span>
			</div>
		<?php endif; ?>

		<div class="teca-organizer-l3-hero-glow" aria-hidden="true"></div>
	</section>

	<?php if ( empty( $organizers ) ) : ?>
		<div class="teca-organizer-l3-empty">
			<div class="teca-organizer-l3-empty-icon" aria-hidden="true">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
					<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.6"/>
					<path d="M6 20a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
				</svg>
			</div>

			<h3 class="teca-organizer-l3-empty-title"><?php esc_html_e( 'No organizers found', 'the-events-calendar-addon' ); ?></h3>
			<p class="teca-organizer-l3-empty-text"><?php esc_html_e( 'No organizer profiles are available right now.', 'the-events-calendar-addon' ); ?></p>
		</div>
	<?php else : ?>
		<div class="teca-organizer-l3-bento">
			<?php foreach ( $organizers as $organizer ) : ?>
				<?php
				if ( ! is_array( $organizer ) ) {
					continue;
				}

				++$organizer_index;

				$organizer_id     = absint( $organizer['id'] ?? 0 );
				$title            = trim( (string) ( $organizer['title'] ?? '' ) );
				$permalink        = ! empty( $organizer['permalink'] ) ? (string) $organizer['permalink'] : '';
				$thumbnail        = ! empty( $organizer['thumbnail'] ) ? (string) $organizer['thumbnail'] : '';
				$phone            = trim( (string) ( $organizer['phone'] ?? '' ) );
				$email            = sanitize_email( (string) ( $organizer['email'] ?? '' ) );
				$website          = trim( (string) ( $organizer['website'] ?? '' ) );
				$upcoming_count   = absint( $organizer['upcoming_count'] ?? 0 );
				$excerpt_display  = teca_get_organizer_excerpt_display( $organizer );
				$fallback_initial = teca_get_organizer_fallback_initial( $organizer );
				$card_title       = $title ?: __( 'Organizer', 'the-events-calendar-addon' );
				$is_featured      = 1 === $organizer_index;
				$count_chip       = teca_get_organizer_upcoming_count_chip_label( $upcoming_count );
				$phone_digits     = preg_replace( '/[^\d\+]/', '', $phone );

				$card_classes = array( 'teca-organizer-l3-card' );

				if ( $is_featured ) {
					$card_classes[] = 'teca-organizer-l3-card-featured';
				}

				if ( 0 === ( $organizer_index % 6 ) ) {
					$card_classes[] = 'teca-organizer-l3-card-wide';
				}
				?>

				<article class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>"<?php echo $organizer_id ? ' data-organizer-id="' . esc_attr( (string) $organizer_id ) . '"' : ''; ?>>
					<div class="teca-organizer-l3-media">
						<?php if ( $thumbnail ) : ?>
							<img
								class="teca-organizer-l3-image"
								src="<?php echo esc_url( $thumbnail ); ?>"
								alt="<?php echo esc_attr( $title ); ?>"
								loading="lazy"
								decoding="async"
							/>
						<?php else : ?>
							<div class="teca-organizer-l3-fallback" aria-hidden="true">
								<span class="teca-organizer-l3-fallback-orb"></span>
								<span class="teca-organizer-l3-fallback-initial"><?php echo esc_html( $fallback_initial ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="teca-organizer-l3-overlay" aria-hidden="true"></div>

					<div class="teca-organizer-l3-topbar">
						<span class="teca-organizer-l3-role-chip"><?php esc_html_e( 'Organizer', 'the-events-calendar-addon' ); ?></span>

						<?php if ( $upcoming_count > 0 ) : ?>
							<span class="teca-organizer-l3-count-chip"><?php echo esc_html( $count_chip ); ?></span>
						<?php endif; ?>
					</div>

					<div class="teca-organizer-l3-content<?php echo $is_featured ? ' teca-organizer-l3-content-featured' : ''; ?>">
						<?php if ( $is_featured ) : ?>
							<span class="teca-organizer-l3-spotlight"><?php esc_html_e( 'Organizer Spotlight', 'the-events-calendar-addon' ); ?></span>
						<?php endif; ?>

						<h3 class="teca-organizer-l3-name">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $card_title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $card_title ); ?>
							<?php endif; ?>
						</h3>

						<?php if ( $excerpt_display ) : ?>
							<p class="teca-organizer-l3-excerpt"><?php echo esc_html( $excerpt_display ); ?></p>
						<?php endif; ?>

						<?php if ( $is_featured && ( ( $phone && $phone_digits ) || $email ) ) : ?>
							<div class="teca-organizer-l3-meta">
								<?php if ( $phone && $phone_digits ) : ?>
									<a class="teca-organizer-l3-meta-item" href="<?php echo esc_url( 'tel:' . $phone_digits ); ?>">
										<?php echo esc_html( $phone ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $email ) : ?>
									<a class="teca-organizer-l3-meta-item" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
										<?php echo esc_html( antispambot( $email ) ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $permalink || $email || $website ) : ?>
							<div class="teca-organizer-l3-actions">
								<?php if ( $permalink ) : ?>
									<a class="teca-organizer-l3-btn teca-organizer-l3-btn-primary" href="<?php echo esc_url( $permalink ); ?>">
										<?php esc_html_e( 'View Organizer', 'the-events-calendar-addon' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $email ) : ?>
									<a class="teca-organizer-l3-btn teca-organizer-l3-btn-secondary" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
										<?php esc_html_e( 'Email', 'the-events-calendar-addon' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<a class="teca-organizer-l3-btn teca-organizer-l3-btn-ghost" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Website', 'the-events-calendar-addon' ); ?>
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
