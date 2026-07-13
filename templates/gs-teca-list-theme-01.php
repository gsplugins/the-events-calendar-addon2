<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

use GS_TECA\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and shared with included partial templates.

foreach ( $events as $event ) :

	$event_id     = (int) ( $event['event_id'] ?? 0 );
	$time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';

	$start_date  = '';
	$day_label   = '';
	$month_label = '';
	$year_label  = '';

	if ( $event_id > 0 ) {
		$start_date = (string) get_post_meta( $event_id, '_EventStartDate', true );
	}

	if ( $start_date ) {
		$timestamp   = strtotime( $start_date );
		$day_label   = date_i18n( 'j', $timestamp );
		$month_label = date_i18n( 'M', $timestamp );
		$year_label  = date_i18n( 'Y', $timestamp );
	}

	$classes = array(
		'gs-teca-single',
		'teca-list-style-1-item',
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

	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Filter attributes are generated and escaped inside helper. ?>>

		<article class="gs-teca-event-main teca-list-style-1-card">

			<?php if ( $day_label && Helpers::is_visible( $visibility_settings['event_date'] ?? true ) ) : ?>
				<aside class="teca-list-style-1-date-badge teca-event-date" aria-label="<?php esc_attr_e( 'Event date', 'the-events-calendar-addon' ); ?>">
					<span class="teca-list-style-1-day"><?php echo esc_html( $day_label ); ?></span>
					<span class="teca-list-style-1-month"><?php echo esc_html( $month_label ); ?></span>

					<?php if ( $year_label ) : ?>
						<span class="teca-list-style-1-year"><?php echo esc_html( $year_label ); ?></span>
					<?php endif; ?>
				</aside>
			<?php endif; ?>

			<div class="teca-list-style-1-content">

				<?php if ( Helpers::is_visible( $visibility_settings['event_cat'] ?? true ) ) : ?>
					<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_cat'], 'teca-list-style-1-tax-row teca-event-categories gs-teca-categories' ); ?>">
						<?php include Template_Loader::locate_template( 'partials/gs-teca-cat.php' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( Helpers::is_visible( $visibility_settings['event_title'] ?? true ) ) : ?>
					<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_title'], 'teca-list-style-1-title teca-event-title gs-teca-title' ); ?>">
						<?php include Template_Loader::locate_template( 'partials/gs-teca-title.php' ); ?>
					</div>
				<?php endif; ?>

				<?php
				$has_meta = $time_display
					|| Helpers::is_visible( $visibility_settings['event_venue'] ?? true )
					|| Helpers::is_visible( $visibility_settings['event_organizer'] ?? true );
				?>

				<?php if ( $has_meta ) : ?>
					<div class="teca-list-style-1-meta teca-event-meta">
						<?php if ( $time_display ) : ?>
							<span class="teca-list-style-1-time teca-event-time"><?php echo esc_html( $time_display ); ?></span>
						<?php endif; ?>

						<?php if ( Helpers::is_visible( $visibility_settings['event_venue'] ?? true ) ) : ?>
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_venue'], 'teca-list-style-1-venue teca-event-venue gs-teca-venue' ); ?>">
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
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_organizer'], 'teca-list-style-1-organizer teca-event-organizer gs-teca-organizer' ); ?>">
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
					<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_details'], 'teca-list-style-1-excerpt teca-event-excerpt gs-teca-desc' ); ?>">
						<?php include Template_Loader::locate_template( 'partials/gs-teca-details.php' ); ?>
					</div>
				<?php endif; ?>

				<?php
				$show_footer_tags = Helpers::is_visible( $visibility_settings['event_tags'] ?? true );
				$show_footer_gcal = $event_id > 0 && teca_is_google_calendar_button_visible( $visibility_settings ?? null );
				?>

				<?php if ( $show_footer_tags || $show_footer_gcal ) : ?>
					<div class="teca-list-style-1-footer">
						<?php if ( $show_footer_tags ) : ?>
							<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_tags'], 'teca-list-style-1-tags teca-event-tags gs-teca-tag' ); ?>">
								<?php include Template_Loader::locate_template( 'partials/gs-teca-tag.php' ); ?>
							</div>
						<?php endif; ?>

						<?php
						teca_echo_google_calendar_button_actions(
							$event_id,
							'card',
							$visibility_settings ?? null,
							'teca-list-google-calendar-wrap',
							array( 'google_calendar_url' => $event['google_calendar_url'] ?? '' )
						);
						?>
					</div>
				<?php endif; ?>

			</div>

			<?php if ( Helpers::is_visible( $visibility_settings['event_thumbnail'] ?? true ) ) : ?>
				<div class="teca-list-style-1-thumb-wrap">
					<div class="<?php Helpers::print_visible_classes( $visibility_settings['event_thumbnail'], 'teca-list-style-1-thumb teca-event-thumb gs-teca-thumbnail-wrapper' ); ?>">
						<?php include Template_Loader::locate_template( 'partials/gs-teca-event-thumbnail.php' ); ?>
					</div>
				</div>
			<?php endif; ?>

		</article>

		<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>

	</div>

<?php endforeach; ?>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
