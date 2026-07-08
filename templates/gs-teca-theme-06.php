<?php
namespace GS_TECA;

use GS_TECA\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

foreach ( $events as $event ) :

	$event_id     = (int) ( $event['event_id'] ?? 0 );
	$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';

	$classes = array(
		'gs-teca-single',
		get_col_classes(
			$settings['columns'],
			$settings['columns_tablet'],
			$settings['columns_mobile_portrait'],
			$settings['columns_mobile']
		),
	);

	if ( $gs_teca_link_type === 'popup' ) {
		$classes[] = 'single-member-pop';
	}

	$term_classes = gs_teca_get_the_term_classes( $event['event_id'], $view_type, $gs_filters_by );
	if ( ! is_wp_error( $term_classes ) && ! empty( $term_classes ) ) {
		$classes[] = $term_classes;
	}
	?>

	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<article class="teca-grid-style-6 teca-grid-style-6-card gs-teca-event-main">

			<?php if ( Helpers::is_visible( $visibility_settings['event_thumbnail'] ?? true ) ) : ?>
				<div class="teca-grid-style-6-thumb">
					<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_thumbnail'], 'teca-grid-style-6-thumb-inner gs-teca-thumbnail-wrapper' ); ?>">
						<?php include Template_Loader::locate_template( 'partials/gs-teca-event-thumbnail.php' ); ?>
					</div>
					<span class="teca-grid-style-6-thumb-line" aria-hidden="true"></span>
				</div>
			<?php endif; ?>

			<div class="teca-grid-style-6-content">

				<?php
				$show_meta_date = Helpers::is_visible( $visibility_settings['event_date'] ?? true );
				$show_meta_cat  = Helpers::is_visible( $visibility_settings['event_cat'] ?? true );

				if ( $show_meta_date || $show_meta_cat || $time_display ) :
					?>
					<div class="teca-grid-style-6-meta">
						<?php if ( $show_meta_date || $time_display ) : ?>
							<div class="teca-grid-style-6-meta-schedule teca-event-date">
								<?php if ( $show_meta_date ) : ?>
									<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_date'], 'teca-grid-style-6-meta-date gs-teca-date' ); ?>">
										<?php include Template_Loader::locate_template( 'partials/gs-teca-date.php' ); ?>
									</div>
								<?php endif; ?>

								<?php if ( $time_display ) : ?>
									<span class="teca-grid-style-6-meta-divider" aria-hidden="true"></span>
									<span class="teca-grid-style-6-meta-time teca-event-time"><?php echo esc_html( $time_display ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $show_meta_cat ) : ?>
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_cat'], 'teca-grid-style-6-meta-label gs-teca-categories' ); ?>">
								<?php include Template_Loader::locate_template( 'partials/gs-teca-cat.php' ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( Helpers::is_visible( $visibility_settings['event_title'] ?? true ) ) : ?>
					<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_title'], 'teca-grid-style-6-title teca-event-title' ); ?>">
						<?php include Template_Loader::locate_template( 'partials/gs-teca-title.php' ); ?>
					</div>
				<?php endif; ?>

				<?php
				$has_details_block = Helpers::is_visible( $visibility_settings['event_venue'] ?? true )
					|| Helpers::is_visible( $visibility_settings['event_organizer'] ?? true )
					|| Helpers::is_visible( $visibility_settings['event_details'] ?? true );
				?>

				<?php if ( $has_details_block ) : ?>
					<div class="teca-grid-style-6-details">

						<?php if ( Helpers::is_visible( $visibility_settings['event_venue'] ?? true ) ) : ?>
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_venue'], 'teca-grid-style-6-venue teca-event-venue gs-teca-venue' ); ?>">
								<?php
								include Template_Loader::locate_template(
									'partials/gs-teca-venue.php',
									$venue_props = array(
										'title'   => true,
										'city'    => false,
										'state'   => false,
										'zip'     => false,
										'country' => false,
										'address' => false,
									)
								);
								?>
							</div>
						<?php endif; ?>

						<?php if ( Helpers::is_visible( $visibility_settings['event_organizer'] ?? true ) ) : ?>
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_organizer'], 'teca-grid-style-6-organizer teca-event-organizer gs-teca-organizer' ); ?>">
								<?php
								include Template_Loader::locate_template(
									'partials/gs-teca-organizer.php',
									$organizer_props = array(
										'title' => true,
										'phone' => false,
										'email' => false,
										'url'   => false,
									)
								);
								?>
							</div>
						<?php endif; ?>

						<?php if ( Helpers::is_visible( $visibility_settings['event_details'] ?? true ) ) : ?>
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_details'], 'teca-grid-style-6-excerpt teca-event-excerpt gs-teca-desc' ); ?>">
								<?php include Template_Loader::locate_template( 'partials/gs-teca-details.php' ); ?>
							</div>
						<?php endif; ?>

					</div>
				<?php endif; ?>

				<?php
				teca_echo_google_calendar_button_actions(
					$event_id,
					'card',
					$visibility_settings ?? null,
					'teca-grid-style-6-calendar-action',
					array( 'google_calendar_url' => $event['google_calendar_url'] ?? '' )
				);
				?>
<?php
				$show_footer_tags = Helpers::is_visible( $visibility_settings['event_tags'] ?? true );
				$show_footer_link = teca_should_show_view_details_button( $visibility_settings ?? null, $gs_teca_link_type );

				if ( $show_footer_tags || $show_footer_link ) :
					?>
					<div class="teca-grid-style-6-footer">
						<?php if ( $show_footer_tags ) : ?>
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_tags'], 'teca-grid-style-6-tags teca-event-tags gs-teca-tag' ); ?>">
								<?php include Template_Loader::locate_template( 'partials/gs-teca-tag.php' ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $show_footer_link ) : ?>
							<div class="<?php teca_print_card_visible_classes( 'view_details_button', 'teca-grid-style-6-footer-action gs-teca-view-details', $visibility_settings ?? null ); ?>">
								<?php
								echo wp_kses_post(
									teca_get_view_details_button_html(
										$event_id,
										array(
											'link_context' => teca_build_theme_link_context( $gs_teca_link_type, $atts['id'], $popup_style ?? 'default', $link_target ?? '_blank' ),
											'button_class' => 'teca-grid-style-6-link',
											'inner_html'   => '<span>' . esc_html( teca_get_view_details_text() ) . '</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
										)
									)
								);
								?>
							</div>
						<?php endif; ?>
				</div>
				<?php endif; ?>

			</div>
		</article>

		<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>

	</div>

<?php endforeach; ?>
