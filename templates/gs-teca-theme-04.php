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
		'teca-style-4-item',
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

	<div class="gs-teca-event-main teca-style-4-card">

		<div class="teca-style-4-media gs-teca-event-img-wrapper">

			<?php if ( Helpers::is_visible( $visibility_settings['event_thumbnail'] ?? true ) ) : ?>
				<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_thumbnail'], 'teca-style-4-thumb gs-teca-thumbnail-wrapper' ); ?>">
					<?php include Template_Loader::locate_template( 'partials/gs-teca-event-thumbnail.php' ); ?>
				</div>
			<?php endif; ?>

			<div class="teca-style-4-media-overlay" aria-hidden="true"></div>

			<div class="teca-style-4-media-meta">
				<?php if ( Helpers::is_visible( $visibility_settings['event_date'] ?? true ) ) : ?>
					<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_date'], 'teca-style-4-date gs-teca-glass-date teca-event-date gs-teca-date' ); ?>">
						<?php include Template_Loader::locate_template( 'partials/gs-teca-date.php' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $time_display ) : ?>
					<span class="teca-style-4-time teca-event-time"><?php echo esc_html( $time_display ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<div class="gs-teca-glass-content teca-style-4-content">

			<?php if ( Helpers::is_visible( $visibility_settings['event_cat'] ?? true ) ) : ?>
				<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_cat'], 'teca-style-4-categories gs-teca-categories teca-event-categories' ); ?>">
					<?php include Template_Loader::locate_template( 'partials/gs-teca-cat.php' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( Helpers::is_visible( $visibility_settings['event_title'] ?? true ) ) : ?>
				<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_title'], 'teca-style-4-title teca-event-title gs-teca-title' ); ?>">
					<?php include Template_Loader::locate_template( 'partials/gs-teca-title.php' ); ?>
				</div>
			<?php endif; ?>

			<?php
			$has_meta = Helpers::is_visible( $visibility_settings['event_venue'] ?? true )
				|| Helpers::is_visible( $visibility_settings['event_organizer'] ?? true );
			?>

			<?php if ( $has_meta ) : ?>
				<div class="teca-style-4-meta gs-teca-glass-meta">
					<?php if ( Helpers::is_visible( $visibility_settings['event_venue'] ?? true ) ) : ?>
						<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_venue'], 'teca-style-4-venue teca-event-venue gs-teca-venue' ); ?>">
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
						<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_organizer'], 'teca-style-4-organizer teca-event-organizer gs-teca-organizer' ); ?>">
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
				</div>
			<?php endif; ?>

			<?php if ( Helpers::is_visible( $visibility_settings['event_details'] ?? true ) ) : ?>
				<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_details'], 'teca-style-4-excerpt teca-event-excerpt gs-teca-desc' ); ?>">
					<?php include Template_Loader::locate_template( 'partials/gs-teca-details.php' ); ?>
				</div>
			<?php endif; ?>

			<?php
			teca_echo_google_calendar_button_actions(
				$event_id,
				'card',
				$visibility_settings ?? null,
				'teca-style-4-google-calendar-wrap',
				array( 'google_calendar_url' => $event['google_calendar_url'] ?? '' )
			);
			?>
<?php
			$show_footer_tags = Helpers::is_visible( $visibility_settings['event_tags'] ?? true );
			$show_footer_link = teca_should_show_view_details_button( $visibility_settings ?? null, $gs_teca_link_type );

			if ( $show_footer_tags || $show_footer_link ) :
				?>
				<div class="teca-style-4-footer">
					<?php if ( $show_footer_tags ) : ?>
						<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_tags'], 'teca-style-4-tags teca-event-tags gs-teca-tag' ); ?>">
							<?php include Template_Loader::locate_template( 'partials/gs-teca-tag.php' ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $show_footer_link ) : ?>
						<div class="<?php teca_print_card_visible_classes( 'view_details_button', 'teca-style-4-action gs-teca-view-details', $visibility_settings ?? null ); ?>">
							<?php
							echo wp_kses_post(
								teca_get_view_details_button_html(
									$event_id,
									array(
										'link_context' => teca_build_theme_link_context( $gs_teca_link_type, $atts['id'], $popup_style ?? 'default', $link_target ?? '_blank' ),
										'button_class' => 'teca-style-4-link',
										'inner_html'   => '<span>' . esc_html( teca_get_view_details_text() ) . '</span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
									)
								)
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>

	<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>

</div>

<?php endforeach; ?>
