<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$organizers = $organizers ?? array();
?>
<div class="teca-organizer-template teca-organizer-template-layout-1">
	<div class="teca-organizer-l1-header">
		<span class="teca-organizer-l1-eyebrow"><?php esc_html_e( 'Organizer Directory', 'the-events-calendar-addon' ); ?></span>
		<h2 class="teca-organizer-l1-title"><?php esc_html_e( 'Meet the People Behind the Events', 'the-events-calendar-addon' ); ?></h2>
		<p class="teca-organizer-l1-description"><?php esc_html_e( 'Browse organizer profiles, contact information, and event connections.', 'the-events-calendar-addon' ); ?></p>
	</div>

	<?php if ( empty( $organizers ) ) : ?>
		<div class="teca-organizer-template-empty">
			<h3 class="teca-organizer-template-empty-title"><?php esc_html_e( 'No organizers found', 'the-events-calendar-addon' ); ?></h3>
			<p class="teca-organizer-template-empty-text"><?php esc_html_e( 'No organizer profiles are available right now.', 'the-events-calendar-addon' ); ?></p>
		</div>
	<?php else : ?>
		<div class="teca-organizer-l1-grid">
			<?php foreach ( $organizers as $organizer ) : ?>
				<?php
				if ( ! is_array( $organizer ) ) {
					continue;
				}

				$organizer_id    = absint( $organizer['id'] ?? 0 );
				$title           = trim( (string) ( $organizer['title'] ?? '' ) );
				$permalink       = ! empty( $organizer['permalink'] ) ? (string) $organizer['permalink'] : '';
				$thumbnail       = ! empty( $organizer['thumbnail'] ) ? (string) $organizer['thumbnail'] : '';
				$phone           = trim( (string) ( $organizer['phone'] ?? '' ) );
				$email           = sanitize_email( (string) ( $organizer['email'] ?? '' ) );
				$website         = trim( (string) ( $organizer['website'] ?? '' ) );
				$upcoming_count  = absint( $organizer['upcoming_count'] ?? 0 );
				$excerpt_display = teca_get_organizer_excerpt_display( $organizer );
				$fallback_initial = teca_get_organizer_fallback_initial( $organizer );
				$card_title      = $title ?: __( 'Organizer', 'the-events-calendar-addon' );
				$phone_digits    = preg_replace( '/[^\d\+]/', '', $phone );
				?>
				<article class="teca-organizer-l1-card"<?php echo $organizer_id ? ' data-organizer-id="' . esc_attr( (string) $organizer_id ) . '"' : ''; ?>>
					<div class="teca-organizer-l1-card-bg" aria-hidden="true"></div>

					<div class="teca-organizer-l1-avatar-wrap">
						<?php if ( $thumbnail ) : ?>
							<img
								class="teca-organizer-l1-avatar"
								src="<?php echo esc_url( $thumbnail ); ?>"
								alt="<?php echo esc_attr( $title ); ?>"
								loading="lazy"
								decoding="async"
							/>
						<?php else : ?>
							<div class="teca-organizer-l1-avatar-fallback" aria-hidden="true">
								<span class="teca-organizer-l1-avatar-initial"><?php echo esc_html( $fallback_initial ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="teca-organizer-l1-content">
						<?php if ( $upcoming_count > 0 ) : ?>
							<span class="teca-organizer-l1-count-chip"><?php echo esc_html( teca_get_organizer_upcoming_count_label( $upcoming_count ) ); ?></span>
						<?php endif; ?>

						<h3 class="teca-organizer-l1-name">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $card_title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $card_title ); ?>
							<?php endif; ?>
						</h3>

						<?php if ( $excerpt_display ) : ?>
							<p class="teca-organizer-l1-excerpt"><?php echo esc_html( $excerpt_display ); ?></p>
						<?php endif; ?>

						<?php if ( $phone || $email || $website ) : ?>
							<div class="teca-organizer-l1-contact-list">
								<?php if ( $phone && $phone_digits ) : ?>
									<a class="teca-organizer-l1-contact-item" href="<?php echo esc_url( 'tel:' . $phone_digits ); ?>">
										<?php echo esc_html( $phone ); ?>
									</a>
								<?php elseif ( $phone ) : ?>
									<span class="teca-organizer-l1-contact-item"><?php echo esc_html( $phone ); ?></span>
								<?php endif; ?>

								<?php if ( $email ) : ?>
									<a class="teca-organizer-l1-contact-item" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
										<?php echo esc_html( antispambot( $email ) ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<a class="teca-organizer-l1-contact-item" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ?: $website ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $permalink || $email || $website ) : ?>
							<div class="teca-organizer-l1-actions">
								<?php if ( $permalink ) : ?>
									<a class="teca-organizer-l1-btn teca-organizer-l1-btn-primary" href="<?php echo esc_url( $permalink ); ?>">
										<?php esc_html_e( 'View Organizer', 'the-events-calendar-addon' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $email ) : ?>
									<a class="teca-organizer-l1-btn teca-organizer-l1-btn-secondary" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
										<?php esc_html_e( 'Email', 'the-events-calendar-addon' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $website ) : ?>
									<a class="teca-organizer-l1-btn teca-organizer-l1-btn-secondary" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
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
