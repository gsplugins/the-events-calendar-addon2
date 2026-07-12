<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$panel_partial = Template_Loader::locate_template( 'accordion/partials/panel-content.php' );
$index         = 0;
?>
<?php if ( empty( $events ) ) : ?>
	<div class="teca-accordion-empty"><?php esc_html_e( 'No events found.', 'the-events-calendar-addon2' ); ?></div>
<?php else : ?>
	<div class="teca-accordion teca-accordion-2">
		<?php foreach ( $events as $event ) : ?>
			<?php
			$event_id    = (int) ( $event['event_id'] ?? 0 );
			$is_first    = 0 === $index;
			$classes     = array( 'teca-accordion-item', 'teca-accordion-2-item' );
			$title       = $event['event_name'] ?? ( $event_id ? get_the_title( $event_id ) : '' );
			$date_display = teca_get_timeline_date_pill( $event );
			$category_names = teca_get_event_category_names( $event );
			$tag_names      = teca_get_event_tag_names( $event );
			$image_url   = $event_id ? get_the_post_thumbnail_url( $event_id, 'medium_large' ) : '';
			$panel_id    = 'teca-accordion-2-panel-' . sanitize_key( (string) $id ) . '-' . $event_id;
			$trigger_id  = 'teca-accordion-2-trigger-' . sanitize_key( (string) $id ) . '-' . $event_id;

			if ( $is_first ) {
				$classes[] = 'is-active';
			}

			if ( ! empty( $gs_teca_link_type ) && 'popup' === $gs_teca_link_type ) {
				$classes[] = 'single-member-pop';
			}

			$term_classes = gs_teca_get_the_term_classes( $event_id, $view_type, $gs_filters_by ?? '' );
			if ( ! is_wp_error( $term_classes ) && ! empty( $term_classes ) ) {
				$classes[] = $term_classes;
			}

			$teca_link_context = teca_build_card_link_context(
				array(
					'link_type'    => $gs_teca_link_type ?? 'none',
					'shortcode_id' => $id ?? '',
					'popup_style'  => $popup_style ?? 'default',
					'link_target'  => $link_target ?? '_blank',
				)
			);
			?>
			<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo teca_get_events_section_item_attributes_html( $event, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<button
					id="<?php echo esc_attr( $trigger_id ); ?>"
					class="teca-accordion-trigger teca-accordion-2-trigger"
					type="button"
					aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $panel_id ); ?>"
				>
					<span class="teca-accordion-2-index"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>

					<span class="teca-accordion-2-title-wrap">
						<?php if ( teca_is_card_field_visible( 'event_title', $visibility_settings ?? null ) && $title ) : ?>
							<span class="teca-accordion-2-title gs-teca-title teca-event-title"><?php echo esc_html( $title ); ?></span>
						<?php endif; ?>
						<?php if ( teca_is_card_field_visible( 'event_tags', $visibility_settings ?? null ) && ! empty( $tag_names ) ) : ?>
							<span class="teca-event-tags gs-teca-tags teca-accordion-2-tags">
								<?php foreach ( $tag_names as $tag_name ) : ?>
									<span class="teca-event-tag gs-teca-tag teca-accordion-2-tag"><?php echo esc_html( $tag_name ); ?></span>
								<?php endforeach; ?>
							</span>
						<?php endif; ?>
					</span>

					<?php if ( teca_is_card_field_visible( 'event_date', $visibility_settings ?? null ) && $date_display ) : ?>
						<span class="teca-accordion-2-date gs-teca-date teca-event-date"><?php echo esc_html( $date_display ); ?></span>
					<?php endif; ?>
					<?php if ( teca_is_card_field_visible( 'event_cat', $visibility_settings ?? null ) && ! empty( $category_names ) ) : ?>
						<span class="teca-event-categories gs-teca-categories teca-accordion-2-categories">
							<?php foreach ( $category_names as $category_name ) : ?>
								<span class="teca-event-category gs-teca-category teca-accordion-2-category"><?php echo esc_html( $category_name ); ?></span>
							<?php endforeach; ?>
						</span>
					<?php endif; ?>
				</button>

				<div
					id="<?php echo esc_attr( $panel_id ); ?>"
					class="teca-accordion-panel teca-accordion-2-panel"
					role="region"
					aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
					<?php echo $is_first ? '' : ' hidden'; ?>
				>
					<?php if ( $image_url && teca_is_card_field_visible( 'event_thumbnail', $visibility_settings ?? null ) ) : ?>
						<div class="teca-accordion-2-image gs-teca-thumbnail-wrapper teca-event-thumb">
							<?php
							echo wp_kses_post( teca_get_card_link_html(
								$event_id,
								'<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy" />',
								$teca_link_context,
								'teca-event-image-link'
							) );
							?>
						</div>
					<?php endif; ?>

					<div class="teca-accordion-2-content">
						<?php
						$accordion_hide_panel_image = true;
						$accordion_hide_panel_tags  = true;
						$accordion_hide_panel_cat   = true;
						if ( ! is_wp_error( $panel_partial ) ) :
							include $panel_partial;
						endif;
						unset( $accordion_hide_panel_image, $accordion_hide_panel_tags, $accordion_hide_panel_cat );
						?>
					</div>
				</div>

				<?php include Template_Loader::locate_template( 'popups/gs-teca-layout-popup.php' ); ?>
			</article>
			<?php
			++$index;
		endforeach;
		?>
	</div>
<?php endif; ?>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
