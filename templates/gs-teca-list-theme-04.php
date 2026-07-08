<?php
namespace GS_TECA;

use GS_TECA\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$more_label = ! empty( $settings['gs_teca_more'] ) ? $settings['gs_teca_more'] : __( 'More', 'the-events-calendar-addon' );

foreach ( $events as $event ) :

	$event_id   = (int) ( $event['event_id'] ?? 0 );
	$classes = array(
		'gs-teca-single',
		'teca-list-style-4-item',
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

		<div class="teca-list-style-4-card gs-teca-event-main">

			<?php if ( Helpers::is_visible( $visibility_settings['event_thumbnail'] ?? true ) ) : ?>
				<div class="teca-list-style-4-thumb teca-event-thumb gs-teca-event-img-wrapper">
					<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_thumbnail'], 'gs-teca-thumbnail-wrapper' ); ?>">
						<?php include Template_Loader::locate_template( 'partials/gs-teca-event-thumbnail.php' ); ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="teca-list-style-4-content gs-teca-glass-content">

				<?php if ( Helpers::is_visible( $visibility_settings['event_title'] ?? true ) ) : ?>
					<div class="teca-list-style-4-title teca-event-title">
						<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_title'], 'gs-teca-title' ); ?>">
							<?php include Template_Loader::locate_template( 'partials/gs-teca-title.php' ); ?>
						</div>
					</div>
				<?php endif; ?>

				<?php
				$show_organizer = Helpers::is_visible( $visibility_settings['event_organizer'] ?? true );
				$show_date      = Helpers::is_visible( $visibility_settings['event_date'] ?? true );

				if ( $show_organizer || $show_date ) :
					?>
					<div class="teca-list-style-4-meta">
						<?php if ( $show_organizer ) : ?>
							<div class="teca-list-style-4-organizer teca-event-organizer">
								<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_organizer'], 'gs-teca-organizer' ); ?>">
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
							</div>
						<?php endif; ?>

						<?php if ( $show_date ) : ?>
							<div class="teca-list-style-4-date teca-event-date">
								<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_date'], 'gs-teca-date' ); ?>">
									<?php include Template_Loader::locate_template( 'partials/gs-teca-date.php' ); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( Helpers::is_visible( $visibility_settings['event_details'] ?? true ) ) : ?>
					<div class="teca-list-style-4-description teca-event-description teca-event-excerpt">
						<div class="teca-list-style-4-desc-inner">
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_details'], 'gs-teca-details' ); ?>">
								<?php include Template_Loader::locate_template( 'partials/gs-teca-details.php' ); ?>
							</div>
							<?php if ( 'single_page' === $gs_teca_link_type ) : ?>
								<a href="<?php echo esc_url( get_permalink( $event['event_id'] ) ); ?>" class="teca-list-style-4-more">
									<?php echo esc_html( $more_label ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( Helpers::is_visible( $visibility_settings['event_venue'] ?? true ) ) : ?>
					<div class="teca-list-style-4-venue teca-event-venue gs-teca-event-venue">
						<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_venue'], 'gs-teca-venue' ); ?>">
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
					</div>
				<?php endif; ?>

				<?php
				$show_category = Helpers::is_visible( $visibility_settings['event_cat'] ?? true );
				?>
				<div class="teca-list-meta-action-row">
					<div class="teca-list-category-wrap">
						<?php if ( $show_category ) : ?>
							<div class="teca-list-style-4-cat gs-teca-cat">
								<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_cat'], 'gs-teca-categories' ); ?>">
									<?php include Template_Loader::locate_template( 'partials/gs-teca-cat.php' ); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<?php teca_echo_google_calendar_button_actions( $event_id, 'card', $visibility_settings ?? null, 'teca-list-google-calendar-wrap', array( 'google_calendar_url' => $event['google_calendar_url'] ?? '' ) ); ?>
				</div>

			</div>

		</div>


		<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>

	</div>

<?php endforeach; ?>
