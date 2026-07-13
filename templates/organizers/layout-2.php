<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$organizers      = $organizers ?? array();
$organizer_count = count( $organizers );
?>

<div class="teca-organizer-template teca-organizer-template-layout-2">
	<section class="teca-organizer-l2-hero">
		<div class="teca-organizer-l2-hero-content">
			<span class="teca-organizer-l2-eyebrow"><?php esc_html_e( 'Organizer Showcase', 'the-events-calendar-addon' ); ?></span>
			<h2 class="teca-organizer-l2-title"><?php esc_html_e( 'Connect With Event Organizers', 'the-events-calendar-addon' ); ?></h2>
			<p class="teca-organizer-l2-description"><?php esc_html_e( 'Browse organizer profiles, contact information, and event connections in a premium editorial layout.', 'the-events-calendar-addon' ); ?></p>
		</div>

		<?php if ( $organizer_count > 0 ) : ?>
			<div class="teca-organizer-l2-stat" aria-label="<?php esc_attr_e( 'Organizer statistics', 'the-events-calendar-addon' ); ?>">
				<span class="teca-organizer-l2-stat-number"><?php echo esc_html( (string) $organizer_count ); ?></span>
				<span class="teca-organizer-l2-stat-label">
					<?php
					echo esc_html(
						1 === $organizer_count
							? __( 'Organizer', 'the-events-calendar-addon' )
							: __( 'Organizers', 'the-events-calendar-addon' )
					);
					?>
				</span>
			</div>
		<?php endif; ?>
	</section>

	<?php if ( empty( $organizers ) ) : ?>
		<div class="teca-organizer-l2-empty">
			<h3 class="teca-organizer-l2-empty-title"><?php esc_html_e( 'No organizers found', 'the-events-calendar-addon' ); ?></h3>
			<p class="teca-organizer-l2-empty-text"><?php esc_html_e( 'No organizer profiles are available right now.', 'the-events-calendar-addon' ); ?></p>
		</div>
	<?php else : ?>
		<div class="teca-organizer-l2-list">
			<?php foreach ( $organizers as $organizer ) : ?>
				<?php
				if ( ! is_array( $organizer ) ) {
					continue;
				}

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
				$row_title        = $title ?: __( 'Organizer', 'the-events-calendar-addon' );
				$phone_digits     = preg_replace( '/[^\d\+]/', '', $phone );
				$has_contact      = ( $phone && $phone_digits ) || $email || $website;
				?>

				<article class="teca-organizer-l2-row"<?php echo $organizer_id ? ' data-organizer-id="' . esc_attr( (string) $organizer_id ) . '"' : ''; ?>>
					<div class="teca-organizer-l2-media">
						<span class="teca-organizer-l2-orb" aria-hidden="true"></span>

						<div class="teca-organizer-l2-avatar">
							<?php if ( $thumbnail ) : ?>
								<img
									class="teca-organizer-l2-avatar-image"
									src="<?php echo esc_url( $thumbnail ); ?>"
									alt="<?php echo esc_attr( $title ); ?>"
									loading="lazy"
									decoding="async"
								/>
							<?php else : ?>
								<div class="teca-organizer-l2-fallback-avatar" aria-hidden="true">
									<span class="teca-organizer-l2-fallback-initial"><?php echo esc_html( $fallback_initial ); ?></span>
								</div>
							<?php endif; ?>
						</div>

						<span class="teca-organizer-l2-media-badge"><?php esc_html_e( 'Organizer', 'the-events-calendar-addon' ); ?></span>
					</div>

					<div class="teca-organizer-l2-content">
						<div class="teca-organizer-l2-top">
							<span class="teca-organizer-l2-role-chip"><?php esc_html_e( 'Organizer', 'the-events-calendar-addon' ); ?></span>

							<?php if ( $upcoming_count > 0 ) : ?>
								<span class="teca-organizer-l2-count-chip"><?php echo esc_html( teca_get_organizer_upcoming_count_label( $upcoming_count ) ); ?></span>
							<?php endif; ?>
						</div>

						<h3 class="teca-organizer-l2-name">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $row_title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $row_title ); ?>
							<?php endif; ?>
						</h3>

						<?php if ( $excerpt_display ) : ?>
							<p class="teca-organizer-l2-excerpt"><?php echo esc_html( $excerpt_display ); ?></p>
						<?php endif; ?>

						<?php if ( $has_contact ) : ?>
							<div class="teca-organizer-l2-contact-grid">
								<?php if ( $phone && $phone_digits ) : ?>
									<div class="teca-organizer-l2-contact-item">
										<span class="teca-organizer-l2-contact-label"><?php esc_html_e( 'Phone', 'the-events-calendar-addon' ); ?></span>
										<a class="teca-organizer-l2-contact-value" href="<?php echo esc_url( 'tel:' . $phone_digits ); ?>">
											<?php echo esc_html( $phone ); ?>
										</a>
									</div>
								<?php elseif ( $phone ) : ?>
									<div class="teca-organizer-l2-contact-item">
										<span class="teca-organizer-l2-contact-label"><?php esc_html_e( 'Phone', 'the-events-calendar-addon' ); ?></span>
										<span class="teca-organizer-l2-contact-value"><?php echo esc_html( $phone ); ?></span>
									</div>
								<?php endif; ?>

								<?php if ( $email ) : ?>
									<div class="teca-organizer-l2-contact-item">
										<span class="teca-organizer-l2-contact-label"><?php esc_html_e( 'Email', 'the-events-calendar-addon' ); ?></span>
										<a class="teca-organizer-l2-contact-value" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
											<?php echo esc_html( antispambot( $email ) ); ?>
										</a>
									</div>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<div class="teca-organizer-l2-contact-item">
										<span class="teca-organizer-l2-contact-label"><?php esc_html_e( 'Website', 'the-events-calendar-addon' ); ?></span>
										<a class="teca-organizer-l2-contact-value" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ?: $website ); ?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $permalink || $email || $website ) : ?>
							<div class="teca-organizer-l2-actions">
								<?php if ( $permalink ) : ?>
									<a class="teca-organizer-l2-btn teca-organizer-l2-btn-primary" href="<?php echo esc_url( $permalink ); ?>">
										<?php esc_html_e( 'View Organizer', 'the-events-calendar-addon' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $email ) : ?>
									<a class="teca-organizer-l2-btn teca-organizer-l2-btn-secondary" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
										<?php esc_html_e( 'Email', 'the-events-calendar-addon' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<a class="teca-organizer-l2-btn teca-organizer-l2-btn-secondary" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
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
