<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

use GS_TECA\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$show_image     = Helpers::is_visible( $visibility_settings['event_thumbnail'] ?? true );
$show_title     = Helpers::is_visible( $visibility_settings['event_title'] ?? true );
$show_organizer = Helpers::is_visible( $visibility_settings['event_organizer'] ?? true );
$show_venue     = Helpers::is_visible( $visibility_settings['event_venue'] ?? true );
$show_links     = teca_is_google_calendar_button_visible( $visibility_settings );

$table_grid_style = teca_build_table_grid_style( '4', $show_image, $show_title, $show_organizer, $show_venue, $show_links );
?>

<div class="teca-table-style-4">
<div class="teca-table-style-4-table"<?php echo $table_grid_style ? ' style="' . esc_attr( $table_grid_style ) . '"' : ''; ?>>

	<div class="teca-table-style-4-header" role="row">
		<div class="teca-table-style-4-row teca-table-style-4-row--header">
			<?php if ( $show_image ) : ?>
				<div class="teca-table-style-4-cell teca-table-style-4-image teca-table-event-image" role="columnheader">
					<?php esc_html_e( 'Image', 'the-events-calendar-addon' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_title ) : ?>
				<div class="teca-table-style-4-cell teca-table-style-4-title teca-table-event-title" role="columnheader">
					<?php esc_html_e( 'Title', 'the-events-calendar-addon' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_organizer ) : ?>
				<div class="teca-table-style-4-cell teca-table-style-4-organizer teca-table-event-organizer" role="columnheader">
					<?php esc_html_e( 'Organizer', 'the-events-calendar-addon' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_venue ) : ?>
				<div class="teca-table-style-4-cell teca-table-style-4-venue teca-table-event-venue" role="columnheader">
					<?php esc_html_e( 'Venue', 'the-events-calendar-addon' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_links ) : ?>
				<div class="teca-table-style-4-cell teca-table-style-4-links teca-table-event-links" role="columnheader">
					<?php esc_html_e( 'Links', 'the-events-calendar-addon' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="teca-table-style-4-body">
		<?php
		foreach ( $events as $event ) :

			$event_id = (int) ( $event['event_id'] ?? 0 );

			$classes = array(
				'gs-teca-single',
				'teca-table-style-4-item',
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

				<div class="teca-table-style-4-row teca-table-style-4-card">

					<?php if ( $show_image ) : ?>
						<div class="teca-table-style-4-cell teca-table-style-4-image teca-table-event-image">
							<div class="teca-table-style-4-image-wrap teca-event-thumb">
								<?php if ( $event_id ) : ?>
									<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_thumbnail'], 'gs-teca-thumbnail-wrapper' ); ?>">
										<?php include Template_Loader::locate_template( 'partials/gs-teca-event-thumbnail.php' ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $show_title ) : ?>
						<div class="teca-table-style-4-cell teca-table-style-4-title teca-event-title">
							<?php if ( $event_id ) : ?>
								<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_title'], 'gs-teca-title' ); ?>">
									<?php include Template_Loader::locate_template( 'partials/gs-teca-title.php' ); ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( $show_organizer ) : ?>
						<div class="teca-table-style-4-cell teca-table-style-4-organizer teca-event-organizer">
							<?php if ( $event_id ) : ?>
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
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( $show_venue ) : ?>
						<div class="teca-table-style-4-cell teca-table-style-4-venue teca-event-venue">
							<?php if ( $event_id ) : ?>
								<?php include Template_Loader::locate_template( 'partials/teca-table-venue.php' ); ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( $show_links ) : ?>
						<div class="teca-table-style-4-cell teca-table-style-4-links teca-table-event-links">
							<?php if ( $event_id ) : ?>
							<?php
							teca_echo_google_calendar_button_actions(
								$event_id,
								'table',
								$visibility_settings ?? null,
								'',
								array(
									'google_calendar_url' => $event['google_calendar_url'] ?? '',
									'class'               => 'teca-google-calendar-btn--table',
								)
							);
							?>
							<?php endif; ?>
						</div>
					<?php endif; ?>

				</div>

				<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>

			</div>

		<?php endforeach; ?>
	</div>

</div>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
